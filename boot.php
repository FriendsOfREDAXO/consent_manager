<?php

use FriendsOfRedaxo\ConsentManager\Api\ConsentManager;
use FriendsOfRedaxo\ConsentManager\Cache;
use FriendsOfRedaxo\ConsentManager\CLang;
use FriendsOfRedaxo\ConsentManager\Cronjob\LogDelete;
use FriendsOfRedaxo\ConsentManager\Cronjob\ThumbnailCleanup;
use FriendsOfRedaxo\ConsentManager\Frontend;
use FriendsOfRedaxo\ConsentManager\GoogleConsentMode;
use FriendsOfRedaxo\ConsentManager\OEmbedParser;
use FriendsOfRedaxo\ConsentManager\RexFormSupport;

$addon = rex_addon::get('consent_manager');

// Nur im Backend
if (rex::isBackend()) {
    rex_perm::register('consent_manager[texteditonly]');
    rex_perm::register('consent_manager[editor]');

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
            $debug_count = (int) $result[0]['debug_count'];

            if ($debug_count > 0) {
                $page = rex_be_controller::getPageObject('consent_manager');
                if ($page) {
                    $title = $page->getTitle();
                    $page->setTitle($title . ' <i class="fa fa-bug" style="color: #f0ad4e; margin-left: 8px;" title="Debug-Modus aktiv"></i>');
                }
            }
        }
    });
    rex_extension::register('REX_FORM_SAVED', CLang::formSaved(...));
    rex_extension::register('REX_FORM_SAVED', Cache::write(...));
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
    rex_extension::register('FE_OUTPUT', static function (rex_extension_point $ep) {
        if (true === rex_get('consent_manager_outputjs', 'bool', false)) {
            $consent_manager = new Frontend(0);
            $consent_manager->outputJavascript();
            exit;
        }
    });

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
                $debugScript = '<script>window.consentManagerDebugConfig = ' . json_encode($googleConsentModeConfig) . ';</script>' . PHP_EOL;
            } catch (Exception $e) {
                $debugScript = '<script>window.consentManagerDebugConfig = {"mode": "unknown", "enabled": false};</script>' . PHP_EOL;
            }

            $debugScript .= '<script src="' . $consentDebugUrl . '"></script>' . PHP_EOL;

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
