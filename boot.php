<?php

use FriendsOfRedaxo\ConsentManager\Api\ConsentManager;
use FriendsOfRedaxo\ConsentManager\Api\ConsentStatsApi;
use FriendsOfRedaxo\ConsentManager\Cache;
use FriendsOfRedaxo\ConsentManager\CLang;
use FriendsOfRedaxo\ConsentManager\Cronjob\LogDelete;
use FriendsOfRedaxo\ConsentManager\Cronjob\ThumbnailCleanup;
use FriendsOfRedaxo\ConsentManager\Frontend;
use FriendsOfRedaxo\ConsentManager\GoogleConsentMode;
use FriendsOfRedaxo\ConsentManager\InlineConsent;
use FriendsOfRedaxo\ConsentManager\OEmbedParser;
use FriendsOfRedaxo\ConsentManager\RexFormSupport;

$addon = rex_addon::get('consent_manager');

// Nur im Backend
if (rex::isBackend()) {
    // Berechtigungen registrieren
    rex_perm::register('consent_manager[texteditonly]');
    rex_perm::register('consent_manager[editor]');
    rex_perm::register('consent_manager[editorial]');
    rex_perm::register('consent_manager[config]');

    if (null !== rex::getUser()) {
        // Eingeschränkter Zugriff für Nur-Text-Bearbeiter
        if (!rex::getUser()->isAdmin() && rex::getUser()->hasPerm('consent_manager[texteditonly]') && !rex::getUser()->hasPerm('consent_manager[editor]')) {
            $page = (array) $addon->getProperty('page', []);
            if ([] !== $page) {
                /** @var array<int, string> */
                $rarray = ['cookiegroup', 'cookie', 'domain', 'config', 'setup', 'changelog', 'help'];
                foreach ($rarray as $removepage) {
                    unset($page['subpages'][$removepage]); /** @phpstan-ignore-line */
                }
                $addon->setProperty('page', $page);
            }
        }
    }

    rex_extension::register('PACKAGES_INCLUDED', static function () {
        $addon = rex_addon::get('consent_manager');
        if (null !== rex::getUser()) {
            if ('consent_manager' === rex_be_controller::getCurrentPagePart(1)) {
                rex_view::addCssFile($addon->getAssetsUrl('consent_manager_backend.css'));
                rex_view::addJsFile($addon->getAssetsUrl('consent_manager_backend.js'));

                // Quickstart Modal CSS für config-Seite
                $currentPage = rex_be_controller::getCurrentPagePart(2);
                if ('config' === $currentPage || '' === $currentPage) {
                    rex_view::addCssFile($addon->getAssetsUrl('consent_quickstart.css'));
                }

                // Google Consent Mode Helper für Cookie-Seiten
                if ('cookie' === $currentPage) {
                    rex_view::addJsFile($addon->getAssetsUrl('google_consent_helper.js'));
                }
            }
        }
    });

    if ('consent_manager' === rex_be_controller::getCurrentPagePart(1)) {
        rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) {
            if (1 === rex_clang::count()) {
                $s = '</head>';
                $r = '<style>.rex-page-nav .navbar{display:none}</style></head>';
                if (is_string($ep->getSubject())) {
                    $ep->setSubject(str_replace($s, $r, $ep->getSubject()));
                }
            }
        });
    }

    rex_extension::register('REX_FORM_CONTROL_FIELDS', RexFormSupport::removeDeleteButton(...));
    rex_extension::register('PAGES_PREPARED', CLang::addLangNav(...));
    rex_extension::register('PAGES_PREPARED', static function () {
        // Debug-Indikator im Menü hinzufügen
        if (rex_backend_login::hasSession() && null !== rex::getUser()) {
            $sql = 'SELECT COUNT(*) as debug_count FROM ' . rex::getTable('consent_manager_domain') . ' WHERE google_consent_mode_debug = 1';
            $result = rex_sql::factory()->getArray($sql);
            $debug_count = $result[0]['debug_count'];

            if ($debug_count > 0) {
                $page = rex_be_controller::getPageObject('consent_manager');
                if (null !== $page) {
                    $title = $page->getTitle();
                    $page->setTitle($title . ' <i class="fa fa-bug" style="color: #f0ad4e; margin-left: 8px;" title="Debug-Modus aktiv"></i>');
                }
            }
        }
    });
    rex_extension::register('REX_FORM_SAVED', CLang::formSaved(...));
    rex_extension::register('REX_FORM_SAVED', Cache::write(...));

    // Domain-Theme: Keine Kompilierung nötig, Theme wird direkt referenziert
    // Die Frontend-Klasse lädt das passende Theme basierend auf der Domain-Config

    rex_extension::register('CLANG_ADDED', CLang::clangAdded(...));
    rex_extension::register('CLANG_DELETED', CLang::clangDeleted(...));

    if ('consent_manager' === rex_be_controller::getCurrentPagePart(1) && true === $addon->getConfig('justInstalled')) {
        $addon->setConfig('justInstalled', false);
        CLang::addonJustInstalled();
    }
    if (true === $addon->getConfig('forceCache')) {
        $addon->setConfig('forceCache', false);
        Cache::forceWrite();
    }
}

// Nur im Frontend
if (rex::isFrontend()) {
    // Auto-Blocking: Scannt HTML und ersetzt Scripts/iframes mit data-consent-Attributen
    rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) {
        $content = $ep->getSubject();

        // Nur wenn Auto-Blocking aktiviert ist
        if (rex_addon::get('consent_manager')->getConfig('auto_blocking_enabled', false)) {
            if (class_exists('\FriendsOfRedaxo\ConsentManager\InlineConsent')) {
                $content = InlineConsent::scanAndReplaceConsentElements($content);

                // CSS und JavaScript für Inline-Consent vor </head> einfügen
                if (false !== stripos($content, '</head>')) {
                    $assets = InlineConsent::getCSS();
                    $assets .= InlineConsent::getJavaScript();
                    $content = str_ireplace('</head>', $assets . '</head>', $content);
                }

                $ep->setSubject($content);
            }
        }
    }, rex_extension::EARLY);

    // One-time server-side cookie migration / cleanup for malformed or old cookies (v < 4)
    // This runs only once per visitor (per browser) thanks to the sentinel cookie 'consent_migrated_sent'.
    rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) {
        // Only attempt migration for real HTTP requests
        if (PHP_SAPI === 'cli') {
            return;
        }

        // Skip for backend sessions
        if (rex_backend_login::hasSession()) {
            return;
        }

        $cookieName = 'consent_manager';
        $sentinel = 'consent_migrated_sent';

        // If user already went through migration, skip
        if (!empty($_COOKIE[$sentinel])) {
            return;
        }

        $raw = $_COOKIE[$cookieName] ?? null;
        $mustDelete = false;

        // determine current major version of the add-on (fallback to 4)
        $addonMajor = 4;
        try {
            $addonObj = rex_addon::get('consent_manager');
            $ver = (string) $addonObj->getVersion();
            if (preg_match('/^([0-9]+)/', $ver, $m)) {
                $addonMajor = (int) $m[1];
            }
        } catch (Throwable $e) {
            // ignore and fallback to default major
        }

        if ($raw) {
            $data = json_decode($raw, true);
            // malformed or missing required keys OR cookie major version not equal to current add-on major
            if (!is_array($data) || empty($data['consents']) || !isset($data['version']) || (int) $data['version'] !== $addonMajor) {
                $mustDelete = true;
            }
        }

        // delete server-side (works also for HttpOnly cookies)
        if ($mustDelete) {
            $host = $_SERVER['HTTP_HOST'] ?? '';
            $secure = (!empty($_SERVER['HTTPS']) && 'off' !== $_SERVER['HTTPS']);
            // expire for current host/path
            setcookie($cookieName, '', time() - 3600, '/', $host, $secure, true);
            // also try shorter flags (some older cookies may differ)
            setcookie($cookieName, '', time() - 3600, '/', $host, $secure, false);
        }

        // set sentinel to avoid repeating this check for this visitor
        /*
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie($sentinel, '1', time() + 60 * 60 * 24 * 30, '/', $_SERVER['HTTP_HOST'] ?? '', $secure, false);
        */
    });
    rex_extension::register('FE_OUTPUT', static function (rex_extension_point $ep) {
        if (true === rex_request::get('consent_manager_outputjs', 'bool', false)) {
            $consent_manager = new Frontend(0);
            $consent_manager->outputJavascript();
            exit;
        }
    });

    // Automatische Einbindung im Frontend (wenn pro Domain aktiviert)
    rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) {
        $content = $ep->getSubject();

        // Nur im echten Frontend, nicht im Backend
        if (rex::isBackend()) {
            return $content;
        }

        // Nur wenn HTML-Inhalt (</head> Tag vorhanden)
        if (!is_string($content) || !str_contains($content, '</head>')) {
            return $content;
        }

        // Domain-Konfiguration prüfen
        $domain = rex_request::server('HTTP_HOST', 'string', '');
        if ('' === $domain) {
            return $content;
        }

        $domain = strtolower($domain);

        $sql = rex_sql::factory();
        $sql->setQuery(
            'SELECT auto_inject, auto_inject_reload_on_consent, auto_inject_delay, auto_inject_focus, auto_inject_include_templates 
             FROM ' . rex::getTable('consent_manager_domain') . ' 
             WHERE uid = ?',
            [$domain],
        );

        // Nur einbinden wenn explizit aktiviert
        if (0 === $sql->getRows() || 1 !== (int) $sql->getValue('auto_inject')) {
            return $content;
        }

        // Template-Whitelist prüfen (Positivliste)
        $includeTemplates = $sql->getValue('auto_inject_include_templates');
        if (null !== $includeTemplates && '' !== trim((string) $includeTemplates)) {
            // Template-IDs aus kommagetrennte Liste in Array umwandeln
            $includedIds = array_map('intval', array_filter(
                array_map('trim', explode(',', (string) $includeTemplates)),
                static fn ($val) => '' !== $val,
            ));

            // Aktuelles Template-ID ermitteln
            $article = rex_article::getCurrent();
            if (null !== $article) {
                $currentTemplateId = $article->getTemplateId();

                // Nur einbinden wenn aktuelles Template in der Positivliste ist
                if (!in_array($currentTemplateId, $includedIds, true)) {
                    return $content;
                }
            } else {
                // Wenn kein Artikel gefunden wurde (z.B. bei Fehlern), nicht einbinden
                return $content;
            }
        }
        // Wenn Positivliste leer ist, in allen Templates einbinden

        // Auto-Inject Optionen auslesen
        $reloadOnConsent = 1 === (int) $sql->getValue('auto_inject_reload_on_consent');
        $delay = (int) $sql->getValue('auto_inject_delay');
        $focus = 1 === (int) $sql->getValue('auto_inject_focus');

        // Consent Manager CSS und JS generieren
        $frontend = new Frontend(0);
        $frontend->setDomain($domain);

        // CSS/JS Fragmente rendern
        $cssFragment = new rex_fragment();
        $cssFragment->setVar('consent_manager', $frontend);
        $css = $cssFragment->parse('ConsentManager/box_cssjs.php');

        // JavaScript-Optionen als data-Attribute oder Inline-Script hinzufügen
        $optionsScript = '';
        if ($reloadOnConsent || $delay > 0 || !$focus) {
            $optionsScript = '<script nonce="' . rex_response::getNonce() . '">
    window.consentManagerOptions = {
        reloadOnConsent: ' . ($reloadOnConsent ? 'true' : 'false') . ',
        showDelay: ' . $delay . ',
        autoFocus: ' . ($focus ? 'true' : 'false') . '
    };
</script>';
        }

        // Vor </head> einbinden
        $content = str_replace('</head>', $optionsScript . $css . '</head>', $content);

        return $content;
    }, rex_extension::LATE);

    // Debug Helper über OUTPUT_FILTER - einfach und zuverlässig
    rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) {
        // User für Frontend initialisieren
        rex_backend_login::createUser();

        // Nur für eingeloggte Backend-Benutzer
        if (!rex_backend_login::hasSession() || null === rex::getUser()) {
            return;
        }

        // Domain-Konfiguration prüfen
        $domain = rex_request::server('HTTP_HOST', 'string', '');
        $domain = strtolower($domain);

        $sql = rex_sql::factory();
        $sql->setQuery(
            'SELECT google_consent_mode_debug FROM ' . rex::getTable('consent_manager_domain') . ' WHERE uid = ?',
            [$domain],
        );

        $debugEnabled = false;
        if ($sql->getRows() > 0) {
            $debugEnabled = (bool) $sql->getValue('google_consent_mode_debug');
        }

        // Debug-Script direkt in HTML einfügen
        if ($debugEnabled) {
            $addon = rex_addon::get('consent_manager');
            $consentDebugUrl = $addon->getAssetsUrl('consent_debug.js');

            try {
                $googleConsentModeConfig = GoogleConsentMode::getDomainConfig($domain);
                $debugScript = '<script nonce="' . rex_response::getNonce() . '">window.consentManagerDebugConfig = ' . json_encode($googleConsentModeConfig) . ';</script>' . PHP_EOL;
            } catch (Exception $e) {
                $debugScript = '<script nonce="' . rex_response::getNonce() . '">window.consentManagerDebugConfig = {"mode": "unknown", "enabled": false};</script>' . PHP_EOL;
            }

            $debugScript .= '<script nonce="' . rex_response::getNonce() . '" src="' . $consentDebugUrl . '"></script>' . PHP_EOL;

            // Debug-Script vor </head> einfügen
            $content = $ep->getSubject();
            if (is_string($content)) {
                $content = str_replace('</head>', $debugScript . '</head>', $content);
                $ep->setSubject($content);
            }
        }
    });
}

rex_api_function::register('consent_manager', ConsentManager::class);
rex_api_function::register('consent_manager_stats', ConsentStatsApi::class);

// CKE5 oEmbed Parser automatisch im Frontend registrieren - nur wenn CKE5 verfügbar ist
if (rex::isFrontend() && rex_addon::exists('cke5') && rex_addon::get('cke5')->isAvailable()) {
    // Automatisch registrieren für alle Domains
    OEmbedParser::register();
}

// CKE5 oEmbed Parser automatisch im Frontend registrieren - nur wenn CKE5 verfügbar ist
if (rex::isFrontend() && rex_addon::exists('cke5') && rex_addon::get('cke5')->isAvailable()) {
    // Automatisch registrieren für alle Domains
    OEmbedParser::register();
}

// Mediamanager Effect für externe Thumbnails registrieren
if (rex_addon::get('media_manager')->isAvailable()) {
    // Effect direkt registrieren
    rex_media_manager::addEffect(rex_effect_external_thumbnail::class);
}

// Cronjobs registrieren
if (rex_addon::get('cronjob')->isAvailable() && !rex::isSafeMode()) {
    rex_cronjob_manager::registerType(LogDelete::class);
    rex_cronjob_manager::registerType(ThumbnailCleanup::class);
}
