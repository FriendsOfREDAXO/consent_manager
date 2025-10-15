<?php

/**
 * Consent Manager Inline Consent
 * 
 * Ermöglicht Consent nur bei Bedarf für einzelne Medien/Services
 * 
 * @package redaxo\consent-manager
 * @author Friends Of REDAXO
 */

class consent_manager_inline
{
    private static $cssOutputted = false;
    private static $jsOutputted = false;
    
    /**
     * Generiert Inline-Consent für externen Content
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
                return '<div class="alert alert-warning">Consent Manager: Service "'.$serviceKey.'" nicht gefunden</div>';
            }
            return '<!-- Consent Manager: Service "'.$serviceKey.'" not found -->';
        }

        // Bereits zugestimmt?
        if (consent_manager_util::has_consent($serviceKey)) {
            return self::renderContent($content, $options);
        }

        // Consent ID generieren
        $consentId = uniqid('consent_', true);
        
        // Standard-Optionen
        $serviceName = !empty($service['service_name']) ? $service['service_name'] : ucfirst($serviceKey);
        $options = array_merge([
            'title' => $serviceName,
            'placeholder_text' => $serviceName . ' laden',
            'privacy_notice' => 'Für die Anzeige von ' . $serviceName . ' werden Cookies benötigt.',
            'width' => 'auto',
            'height' => 'auto',
            'thumbnail' => 'auto',
        ], $options);

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
     * Service aus Datenbank laden
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
                FROM '.rex::getTable('consent_manager_cookie').' c
                LEFT JOIN '.rex::getTable('consent_manager_cookiegroup').' cg ON c.cookiegroup_id = cg.id
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
                    FROM '.rex::getTable('consent_manager_cookie').' c
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
                        FROM '.rex::getTable('consent_manager_cookie').'
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
     * YouTube Platzhalter
     */
    private static function renderYouTubePlaceholder($serviceKey, $videoId, $options, $consentId, $service)
    {
        // Video ID extrahieren falls komplette URL übergeben wurde
        if (strpos($videoId, 'youtube.com') !== false || strpos($videoId, 'youtu.be') !== false) {
            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $videoId, $matches);
            $videoId = $matches[1] ?? $videoId;
        }

        // Thumbnail lokal cachen für Datenschutz
        $thumbnail = $options['thumbnail'] === 'auto' 
            ? consent_manager_thumbnail_cache::cacheYouTubeThumbnail($videoId)
            : $options['thumbnail'];

        $iframe = '<iframe width="'.($options['width'] ?: '560').'" height="'.($options['height'] ?: '315').'" 
                   src="https://www.youtube.com/embed/'.$videoId.'" 
                   frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                   allowfullscreen></iframe>';

        return self::renderPlaceholderHTML($serviceKey, $iframe, $options, $consentId, $service, [
            'thumbnail' => $thumbnail,
            'icon' => 'uk-icon:play-circle',
            'icon_label' => 'YouTube Video',
            'service_name' => 'YouTube'
        ]);
    }

    /**
     * Vimeo Platzhalter
     */
    private static function renderVimeoPlaceholder($serviceKey, $videoId, $options, $consentId, $service)
    {
        // Video ID extrahieren
        if (strpos($videoId, 'vimeo.com') !== false) {
            preg_match('/vimeo\.com\/(\d+)/', $videoId, $matches);
            $videoId = $matches[1] ?? $videoId;
        }

        // Thumbnail lokal cachen
        $thumbnail = $options['thumbnail'] === 'auto' 
            ? consent_manager_thumbnail_cache::cacheVimeoThumbnail($videoId)
            : $options['thumbnail'];

        $iframe = '<iframe src="https://player.vimeo.com/video/'.$videoId.'" 
                   width="'.($options['width'] ?: '640').'" height="'.($options['height'] ?: '360').'" 
                   frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';

        return self::renderPlaceholderHTML($serviceKey, $iframe, $options, $consentId, $service, [
            'thumbnail' => $thumbnail,
            'icon' => '🎬',
            'service_name' => 'Vimeo'
        ]);
    }

    /**
     * Google Maps Platzhalter
     */
    private static function renderGoogleMapsPlaceholder($serviceKey, $embedUrl, $options, $consentId, $service)
    {
        $iframe = '<iframe src="'.$embedUrl.'" 
                   width="'.($options['width'] ?: '100%').'" height="'.($options['height'] ?: '450').'" 
                   style="border:0;" allowfullscreen="" loading="lazy"></iframe>';

        return self::renderPlaceholderHTML($serviceKey, $iframe, $options, $consentId, $service, [
            'thumbnail' => null,
            'icon' => 'uk-icon:location',
            'icon_label' => 'Map Location',
            'service_name' => 'Google Maps'
        ]);
    }

    /**
     * Generischer Platzhalter
     */
    private static function renderGenericPlaceholder($serviceKey, $content, $options, $consentId, $service)
    {
        return self::renderPlaceholderHTML($serviceKey, $content, $options, $consentId, $service, [
            'thumbnail' => $options['thumbnail'] !== 'auto' ? $options['thumbnail'] : null,
            'icon' => 'fa fa-external-link-alt',
            'icon_label' => 'External Content',
            'service_name' => $service['service_name']
        ]);
    }

    /**
     * Platzhalter HTML generieren
     */
    private static function renderPlaceholderHTML($serviceKey, $content, $options, $consentId, $service, $placeholderData)
    {
        // Debug im Debug-Modus
        if (rex::isDebugMode()) {
            $debug = '<div style="background:#f8d7da;border:1px solid #f5c6cb;padding:10px;margin:10px 0;font-size:12px;">';
            $debug .= '<strong>renderPlaceholderHTML Debug:</strong><br>';
            $debug .= 'serviceKey: ' . var_export($serviceKey, true) . '<br>';
            $debug .= 'consentId: ' . var_export($consentId, true) . '<br>';
            $debug .= 'options: ' . var_export($options, true) . '<br>';
            $debug .= 'placeholderData: ' . var_export($placeholderData, true) . '<br>';
            $debug .= 'content length: ' . strlen($content) . ' chars<br>';
            $debug .= '</div>';
        }
        
        // Fragment verwenden für bessere Anpassbarkeit
        $fragment = new rex_fragment();
        $fragment->setVar('serviceKey', $serviceKey);
        $fragment->setVar('content', $content);
        $fragment->setVar('options', $options);
        $fragment->setVar('consentId', $consentId);
        $fragment->setVar('service', $service);
        $fragment->setVar('placeholderData', $placeholderData);
        
        // Alle Button-Texte für Fragment hinzufügen
        $fragment->setVar('button_inline_details_text', self::getButtonText('button_inline_details', 'Einstellungen'));
        $fragment->setVar('inline_placeholder_text', self::getButtonText('inline_placeholder_text', 'Einmal laden'));
        $fragment->setVar('button_inline_allow_all_text', self::getButtonText('button_inline_allow_all', 'Alle erlauben'));
        $fragment->setVar('inline_action_text', self::getButtonText('inline_action_text', 'Was möchten Sie tun?'));
        $fragment->setVar('show_allow_all', $options['show_allow_all'] ?? false);
        $fragment->setVar('inline_privacy_notice', self::getButtonText('inline_privacy_notice', 'Für die Anzeige werden Cookies benötigt.'));
        $fragment->setVar('inline_title_fallback', self::getButtonText('inline_title_fallback', 'Externes Medium'));
        $fragment->setVar('inline_privacy_link_text', self::getButtonText('inline_privacy_link_text', 'Datenschutzerklärung von'));
        
        // Icon-Konfiguration
        $fragment->setVar('privacy_icon', $options['privacy_icon'] ?? 'uk-icon:shield');
        
        $result = $fragment->parse('consent_inline_placeholder.php');
        
        // Debug-Output voranstellen
        if (rex::isDebugMode() && isset($debug)) {
            $result = $debug . $result;
        }
        
        return $result;
    }

    /**
     * Content direkt rendern (wenn bereits Consent vorhanden)
     */
    private static function renderContent($content, $options)
    {
        // Für YouTube URLs: In iframe umwandeln
        if (strpos($content, 'youtube.com') !== false || strpos($content, 'youtu.be') !== false) {
            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $content, $matches);
            $videoId = $matches[1] ?? '';
            if ($videoId) {
                return '<iframe width="'.($options['width'] ?: '560').'" height="'.($options['height'] ?: '315').'" 
                        src="https://www.youtube.com/embed/'.$videoId.'" 
                        frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen></iframe>';
            }
        }
        
        // Für Vimeo URLs: In iframe umwandeln
        if (strpos($content, 'vimeo.com') !== false) {
            preg_match('/vimeo\.com\/(\d+)/', $content, $matches);
            $videoId = $matches[1] ?? '';
            if ($videoId) {
                return '<iframe src="https://player.vimeo.com/video/'.$videoId.'" 
                        width="'.($options['width'] ?: '640').'" height="'.($options['height'] ?: '360').'" 
                        frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
            }
        }
        
        // Für Google Maps Embed URLs: In iframe umwandeln
        if (strpos($content, 'google.com/maps/embed') !== false) {
            return '<iframe src="'.$content.'" 
                    width="'.($options['width'] ?: '100%').'" height="'.($options['height'] ?: '450').'" 
                    style="border:0;" allowfullscreen="" loading="lazy"></iframe>';
        }
        
        // Für andere Inhalte: Direkt zurückgeben
        return $content;
    }

    /**
     * JavaScript für Inline-Consent generieren
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
     * Button-Text aus Texte-Verwaltung laden
     */
    private static function getButtonText($key, $fallback)
    {
        try {
            $sql = rex_sql::factory();
            $sql->setQuery('SELECT text FROM ' . rex::getTable('consent_manager_text') . ' WHERE uid = ? AND clang_id = ?', 
                [$key, rex_clang::getCurrentId()]);
            
            if ($sql->getRows() > 0) {
                return $sql->getValue('text');
            }
        } catch (rex_sql_exception $e) {
            // Fallback falls Datenbankfehler
        }
        
        return $fallback;
    }

    /**
     * CSS für Inline-Consent generieren
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
 * Globale Helper-Funktion für einfache Nutzung in Templates
 */
function doConsent($serviceKey, $content, $options = [])
{
    return consent_manager_inline::doConsent($serviceKey, $content, $options);
}