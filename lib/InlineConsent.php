<?php

/**
 * Consent Manager Inline Consent.
 *
 * Ermöglicht Consent nur bei Bedarf für einzelne Medien/Services
 *
 * @package FriendsOfRedaxo\ConsentManager
 * @author Friends Of REDAXO
 */

namespace FriendsOfRedaxo\ConsentManager;

use rex;
use rex_clang;
use rex_consent_manager_thumbnail_mediamanager;
use rex_fragment;
use rex_sql;
use rex_sql_exception;
use rex_url;

use function is_array;
use function strlen;

class InlineConsent
{
    private static $cssOutputted = false;
    private static $jsOutputted = false;

    /**
     * Generiert Inline-Consent für externen Content.
     *
     * @param string $serviceKey Service-Schlüssel aus Consent Manager
     * @param string $content Original Content (iframe, script, etc.)
     * @param array $options Zusätzliche Optionen
     * @return string HTML-Output
     */
    public static function doConsent($serviceKey, $content, $options = [])
    {
        // Service aus DB laden
        $service = self::getService($serviceKey);
        if (!$service) {
            if (rex::isDebugMode()) {
                return '<div class="alert alert-warning">Consent Manager: Service "' . $serviceKey . '" nicht gefunden</div>';
            }
            return '<!-- Consent Manager: Service "' . $serviceKey . '" not found -->';
        }

        // Bereits zugestimmt?
        if (Utility::has_consent($serviceKey)) {
            return self::renderContent($content, $options);
        }

        // Consent ID generieren
        $consentId = uniqid('consent_', true);

        // Standard-Optionen (nur wenn nicht explizit übergeben)
        $serviceName = !empty($service['service_name']) ? $service['service_name'] : ucfirst($serviceKey);
        $defaultOptions = [
            'title' => $serviceName,
            'width' => 'auto',
            'height' => 'auto',
            'thumbnail' => 'auto',
        ];

        // Optionen mergen, aber nur defaults setzen wenn nicht bereits vorhanden
        $options = array_merge($defaultOptions, $options);

        // Spezielle Handler für bekannte Services
        switch (strtolower($serviceKey)) {
            case 'youtube':
                return self::renderYouTubePlaceholder($serviceKey, $content, $options, $consentId, $service);
            case 'vimeo':
                return self::renderVimeoPlaceholder($serviceKey, $content, $options, $consentId, $service);
            case 'google-maps':
                return self::renderGoogleMapsPlaceholder($serviceKey, $content, $options, $consentId, $service);
            default:
                return self::renderGenericPlaceholder($serviceKey, $content, $options, $consentId, $service);
        }
    }

    /**
     * Service aus Datenbank laden.
     */
    private static function getService($serviceKey)
    {
        $sql = rex_sql::factory();

        // Erst prüfen welche Spalten verfügbar sind
        try {
            // Versuche moderne Struktur mit cookiegroup_id
            $sql->setQuery('
                SELECT c.pid, c.id, c.clang_id, c.uid, c.service_name, c.provider, c.provider_link_privacy, 
                       c.definition, c.script, c.script_unselect, c.placeholder_text, c.placeholder_image,
                       c.createuser, c.updateuser, c.createdate, c.updatedate,
                       cg.name as group_name, cg.required as group_required
                FROM ' . rex::getTable('consent_manager_cookie') . ' c
                LEFT JOIN ' . rex::getTable('consent_manager_cookiegroup') . ' cg ON c.cookiegroup_id = cg.id
                WHERE c.uid = ? AND c.clang_id = ?
            ', [$serviceKey, rex_clang::getCurrentId()]);
        } catch (rex_sql_exception $e) {
            // Fallback für ältere Struktur ohne cookiegroup_id
            try {
                $sql->setQuery('
                    SELECT c.pid, c.id, c.clang_id, c.uid, c.service_name, c.provider, c.provider_link_privacy, 
                           c.definition, c.script, c.script_unselect, c.placeholder_text, c.placeholder_image,
                           c.createuser, c.updateuser, c.createdate, c.updatedate,
                           NULL as group_name, 0 as group_required
                    FROM ' . rex::getTable('consent_manager_cookie') . ' c
                    WHERE c.uid = ? AND c.clang_id = ?
                ', [$serviceKey, rex_clang::getCurrentId()]);
            } catch (rex_sql_exception $e2) {
                // Weitere Fallback-Versuche für verschiedene Consent Manager Versionen
                try {
                    $sql->setQuery('
                        SELECT pid, id, clang_id, uid, service_name, provider, provider_link_privacy, 
                               definition, script, script_unselect, placeholder_text, placeholder_image,
                               createuser, updateuser, createdate, updatedate,
                               NULL as group_name, 0 as group_required
                        FROM ' . rex::getTable('consent_manager_cookie') . '
                        WHERE uid = ? AND clang_id = ?
                    ', [$serviceKey, rex_clang::getCurrentId()]);
                } catch (rex_sql_exception $e3) {
                    return null;
                }
            }
        }

        if ($sql->getRows() > 0) {
            $service = $sql->getRow();

            // Normalisiere Service-Daten: Entferne Tabellen-Prefixe
            $normalizedService = [];
            foreach ($service as $key => $value) {
                // Entferne c. und andere Prefixe
                $cleanKey = preg_replace('/^[a-zA-Z_]+\./', '', $key);
                $normalizedService[$cleanKey] = $value;

                // Behalte auch Original-Key für Kompatibilität
                $normalizedService[$key] = $value;
            }

            return $normalizedService;
        }

        return null;
    }

    /**
     * YouTube Platzhalter.
     */
    private static function renderYouTubePlaceholder($serviceKey, $videoId, $options, $consentId, $service)
    {
        // Video ID extrahieren falls komplette URL übergeben wurde
        if (str_contains($videoId, 'youtube.com') || str_contains($videoId, 'youtu.be')) {
            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $videoId, $matches);
            $videoId = $matches[1] ?? $videoId;
        }

        // Thumbnail über Mediamanager generieren (falls verfügbar)
        $thumbnail = $options['thumbnail'];
        if ('auto' === $thumbnail) {
            if (class_exists('rex_consent_manager_thumbnail_mediamanager')) {
                $thumbnail = rex_consent_manager_thumbnail_mediamanager::getThumbnailUrl('youtube', $videoId, $options);
            } else {
                // Fallback zur direkten YouTube-URL
                $thumbnail = 'https://img.youtube.com/vi/' . $videoId . '/maxresdefault.jpg';
            }
        }

        // Build attributes string from options
        $attributesString = '';
        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $key => $value) {
                if ('' === $value) {
                    $attributesString .= ' ' . rex_escape($key);
                } else {
                    $attributesString .= ' ' . rex_escape($key) . '="' . rex_escape($value) . '"';
                }
            }
        }

        $iframe = '<iframe width="' . ($options['width'] ?: '560') . '" height="' . ($options['height'] ?: '315') . '" 
                   src="https://www.youtube.com/embed/' . rex_escape($videoId) . '" 
                   frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                   allowfullscreen' . $attributesString . '></iframe>';

        return self::renderPlaceholderHTML($serviceKey, $iframe, $options, $consentId, $service, [
            'thumbnail' => $thumbnail,
            'icon' => 'uk-icon:play-circle',
            'icon_label' => 'YouTube Video',
            'service_name' => 'YouTube',
        ]);
    }

    /**
     * Vimeo Platzhalter.
     */
    private static function renderVimeoPlaceholder($serviceKey, $videoId, $options, $consentId, $service)
    {
        // Video ID extrahieren
        if (str_contains($videoId, 'vimeo.com')) {
            preg_match('/vimeo\.com\/(\d+)/', $videoId, $matches);
            $videoId = $matches[1] ?? $videoId;
        }

        // Thumbnail über Mediamanager generieren (falls verfügbar)
        $thumbnail = $options['thumbnail'];
        if ('auto' === $thumbnail) {
            if (class_exists('rex_consent_manager_thumbnail_mediamanager')) {
                $thumbnail = rex_consent_manager_thumbnail_mediamanager::getThumbnailUrl('vimeo', $videoId, $options);
            } else {
                // Fallback zu generischem Vimeo-Placeholder
                $thumbnail = 'data:image/svg+xml;base64,' . base64_encode(
                    '<svg width="640" height="360" xmlns="http://www.w3.org/2000/svg">' .
                    '<rect width="100%" height="100%" fill="#1ab7ea"/>' .
                    '<text x="50%" y="50%" fill="white" text-anchor="middle" dy=".3em" font-family="Arial" font-size="24">Vimeo Video</text>' .
                    '</svg>',
                );
            }
        }

        // Build attributes string from options
        $attributesString = '';
        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $key => $value) {
                if ('' === $value) {
                    $attributesString .= ' ' . rex_escape($key);
                } else {
                    $attributesString .= ' ' . rex_escape($key) . '="' . rex_escape($value) . '"';
                }
            }
        }

        $iframe = '<iframe src="https://player.vimeo.com/video/' . rex_escape($videoId) . '" 
                   width="' . ($options['width'] ?: '640') . '" height="' . ($options['height'] ?: '360') . '" 
                   frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen' . $attributesString . '></iframe>';

        return self::renderPlaceholderHTML($serviceKey, $iframe, $options, $consentId, $service, [
            'thumbnail' => $thumbnail,
            'icon' => '🎬',
            'service_name' => 'Vimeo',
        ]);
    }

    /**
     * Google Maps Platzhalter.
     */
    private static function renderGoogleMapsPlaceholder($serviceKey, $embedUrl, $options, $consentId, $service)
    {
        $iframe = '<iframe src="' . rex_escape($embedUrl) . '" 
                   width="' . ($options['width'] ?: '100%') . '" height="' . ($options['height'] ?: '450') . '" 
                   style="border:0;" allowfullscreen="" loading="lazy"></iframe>';

        return self::renderPlaceholderHTML($serviceKey, $iframe, $options, $consentId, $service, [
            'thumbnail' => null,
            'icon' => 'uk-icon:location',
            'icon_label' => 'Map Location',
            'service_name' => 'Google Maps',
        ]);
    }

    /**
     * Generischer Platzhalter.
     */
    private static function renderGenericPlaceholder($serviceKey, $content, $options, $consentId, $service)
    {
        return self::renderPlaceholderHTML($serviceKey, $content, $options, $consentId, $service, [
            'thumbnail' => 'auto' !== $options['thumbnail'] ? $options['thumbnail'] : null,
            'icon' => 'fa fa-external-link-alt',
            'icon_label' => 'External Content',
            'service_name' => $service['service_name'],
        ]);
    }

    /**
     * Platzhalter HTML generieren.
     */
    private static function renderPlaceholderHTML($serviceKey, $content, $options, $consentId, $service, $placeholderData)
    {
        $debug = rex::isDebugMode();

        // Fragment verwenden für bessere Anpassbarkeit
        $fragment = new rex_fragment();
        $fragment->setVar('serviceKey', $serviceKey);
        $fragment->setVar('content', $content);
        $fragment->setVar('options', $options);
        $fragment->setVar('consentId', $consentId);
        $fragment->setVar('service', $service);
        $fragment->setVar('placeholderData', $placeholderData);

        if ($debug) {
            echo "<!-- DEBUG renderPlaceholderHTML: serviceKey=$serviceKey -->\n";
            echo '<!-- DEBUG options: ' . print_r($options, true) . " -->\n";
        }

        // Alle Button-Texte für Fragment hinzufügen
        $fragment->setVar('button_inline_details_text', self::getButtonText('button_inline_details', 'Einstellungen'));
        $fragment->setVar('inline_placeholder_text', self::getButtonText('inline_placeholder_text', 'Einmal laden'));
        $fragment->setVar('button_inline_allow_all_text', self::getButtonText('button_inline_allow_all', 'Alle erlauben'));
        $fragment->setVar('inline_action_text', self::getButtonText('inline_action_text', 'Was möchten Sie tun?'));
        $fragment->setVar('show_allow_all', $options['show_allow_all'] ?? false);
        $privacyNotice = self::getButtonText('inline_privacy_notice', 'Für die Anzeige werden Cookies benötigt.');
        $fragment->setVar('inline_privacy_notice', $privacyNotice);
        $fragment->setVar('inline_title_fallback', self::getButtonText('inline_title_fallback', 'Externes Medium'));
        $fragment->setVar('inline_privacy_link_text', self::getButtonText('inline_privacy_link_text', 'Datenschutzerklärung von'));

        if ($debug) {
            echo "<!-- DEBUG inline_privacy_notice from DB: $privacyNotice -->\n";
        }

        // Icon-Konfiguration
        $fragment->setVar('privacy_icon', $options['privacy_icon'] ?? 'uk-icon:shield');

        $result = $fragment->parse('consent_inline_placeholder.php');

        return $result;
    }

    /**
     * Content direkt rendern (wenn bereits Consent vorhanden).
     */
    private static function renderContent($content, $options)
    {
        // Build attributes string from options
        $attributesString = '';
        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $key => $value) {
                if ('' === $value) {
                    $attributesString .= ' ' . rex_escape($key);
                } else {
                    $attributesString .= ' ' . rex_escape($key) . '="' . rex_escape($value) . '"';
                }
            }
        }

        // Für YouTube URLs oder Video-IDs
        if (str_contains($content, 'youtube.com') || str_contains($content, 'youtu.be')
            || (11 === strlen($content) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $content))) {
            // Video-ID extrahieren
            if (11 === strlen($content) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $content)) {
                $videoId = $content;
            } else {
                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $content, $matches);
                $videoId = $matches[1] ?? '';
            }

            if ($videoId) {
                // Standard YouTube iframe
                $width = $options['width'] ?: '560';
                $height = $options['height'] ?: '315';
                return '<iframe width="' . $width . '" height="' . $height . '" 
                        src="https://www.youtube.com/embed/' . rex_escape($videoId) . '" 
                        frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen' . $attributesString . '></iframe>';
            }
        }

        // Für Vimeo URLs oder Video-IDs
        if (str_contains($content, 'vimeo.com') || preg_match('/^\d{6,}$/', $content)) {
            // Video-ID extrahieren
            if (preg_match('/^\d{6,}$/', $content)) {
                $videoId = $content;
            } else {
                preg_match('/vimeo\.com\/(\d+)/', $content, $matches);
                $videoId = $matches[1] ?? '';
            }

            if ($videoId) {
                // Standard Vimeo iframe
                $width = $options['width'] ?: '640';
                $height = $options['height'] ?: '360';
                return '<iframe src="https://player.vimeo.com/video/' . rex_escape($videoId) . '" 
                        width="' . $width . '" height="' . $height . '" 
                        frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen' . $attributesString . '></iframe>';
            }
        }

        // Für Google Maps Embed URLs: In iframe umwandeln
        if (str_contains($content, 'google.com/maps/embed')) {
            return '<iframe src="' . rex_escape($content) . '" 
                    width="' . ($options['width'] ?: '100%') . '" height="' . ($options['height'] ?: '450') . '" 
                    style="border:0;" allowfullscreen="" loading="lazy"' . $attributesString . '></iframe>';
        }

        // Für andere Inhalte: Direkt zurückgeben
        return $content;
    }

    /**
     * JavaScript für Inline-Consent generieren.
     */
    public static function getJavaScript()
    {
        if (self::$jsOutputted) {
            return '<!-- JavaScript bereits ausgegeben -->';
        }
        self::$jsOutputted = true;

        // JavaScript-Datei laden
        $jsPath = rex_url::addonAssets('consent_manager', 'consent_inline.js');
        return '<script defer src="' . $jsPath . '"></script>';
    }

    /**
     * Button-Text aus Texte-Verwaltung laden.
     */
    private static function getButtonText($key, $fallback)
    {
        $debug = rex::isDebugMode();

        try {
            $sql = rex_sql::factory();
            $sql->setQuery('SELECT text FROM ' . rex::getTable('consent_manager_text') . ' WHERE uid = ? AND clang_id = ?',
                [$key, rex_clang::getCurrentId()]);

            if ($sql->getRows() > 0) {
                $value = $sql->getValue('text');
                if ($debug) {
                    echo "<!-- DEBUG getButtonText: key=$key, clang=" . rex_clang::getCurrentId() . ", value=$value -->\n";
                }
                return $value;
            }
            if ($debug) {
                echo "<!-- DEBUG getButtonText: key=$key NOT FOUND in DB, using fallback=$fallback -->\n";
            }
        } catch (rex_sql_exception $e) {
            if ($debug) {
                echo "<!-- DEBUG getButtonText: key=$key, SQL ERROR: " . $e->getMessage() . " -->\n";
            }
        }

        return $fallback;
    }

    /**
     * CSS für Inline-Consent generieren.
     */
    public static function getCSS()
    {
        if (self::$cssOutputted) {
            return '<!-- CSS bereits ausgegeben -->';
        }
        self::$cssOutputted = true;

        // CSS-Datei laden
        $cssPath = rex_url::addonAssets('consent_manager', 'consent_inline.css');
        return '<link rel="stylesheet" href="' . $cssPath . '">';
    }
}

/**
 * Globale Helper-Funktion für einfache Nutzung in Templates.
 */
function doConsent($serviceKey, $content, $options = [])
{
    return InlineConsent::doConsent($serviceKey, $content, $options);
}
