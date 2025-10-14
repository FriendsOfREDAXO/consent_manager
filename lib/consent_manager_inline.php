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
        $fragment->setVar('button_inline_allow_all', self::getButtonText('button_inline_allow_all', 'Alle erlauben'));
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
        
        // Für andere Inhalte: Direkt zurückgeben
        return $content;
    }

    /**
     * JavaScript für Inline-Consent generieren
     */
    public static function getJavaScript()
    {
        return "
<script>
// Verhindere doppelte Initialisierung
if (typeof window.consentManagerInline !== 'undefined') {
    // JavaScript bereits geladen, nichts tun
} else {
window.consentManagerInline = {
    initialized: false,
    init: function() {
        if (this.initialized) return; // Bereits initialisiert
        this.initialized = true;
        
        var self = this;
        // Alle möglichen Event-Listener für verschiedene Consent Manager Versionen
        var eventNames = [
            'consent_manager_consent_given',
            'consent_manager_updated', 
            'consent-manager-consent-given',
            'consent-manager-updated',
            'consentGiven',
            'consentUpdated'
        ];
        
        eventNames.forEach(function(eventName) {
            document.addEventListener(eventName, function(event) {
                setTimeout(function() { self.updateAllPlaceholders(); }, 100);
                setTimeout(function() { self.updateAllPlaceholders(); }, 1000);
            });
        });
        
        // Cookie-Änderungen überwachen
        var lastCookieValue = self.getCookie('consent_manager');
        setInterval(function() {
            var currentCookieValue = self.getCookie('consent_manager');
            if (currentCookieValue !== lastCookieValue) {
                lastCookieValue = currentCookieValue;
                self.updateAllPlaceholders();
            }
        }, 1000);
        
        // MutationObserver für DOM-Änderungen
        if (typeof MutationObserver !== 'undefined') {
            var observer = new MutationObserver(function(mutations) {
                var shouldUpdate = false;
                mutations.forEach(function(mutation) {
                    var target = mutation.target;
                    if (target.className && typeof target.className === 'string') {
                        if (target.className.indexOf('consent') !== -1 || 
                            target.className.indexOf('cookie') !== -1) {
                            shouldUpdate = true;
                        }
                    }
                });
                if (shouldUpdate) {
                    setTimeout(function() { self.updateAllPlaceholders(); }, 500);
                }
            });
            
            observer.observe(document.body, { 
                attributes: true, 
                childList: true, 
                subtree: true,
                attributeFilter: ['style', 'class', 'data-consent']
            });
        }
        
        // Event-Handler für Buttons
        document.addEventListener('click', function(e) {
            if (e.target.matches('.consent-inline-once')) {
                e.preventDefault();
                var button = e.target;
                var consentId = button.getAttribute('data-consent-id');
                var serviceKey = button.getAttribute('data-service');
                self.accept(consentId, serviceKey, button);
            }
            
            if (e.target.matches('.consent-inline-allow-all')) {
                e.preventDefault();
                var serviceKey = e.target.getAttribute('data-service');
                self.allowAllForService(serviceKey);
            }
            
            if (e.target.matches('.consent-inline-details')) {
                e.preventDefault();
                var serviceKey = e.target.getAttribute('data-service');
                self.showDetails(serviceKey);
            }
        });
        
        // Fallback: Regelmäßige Prüfung
        setInterval(function() { 
            self.updateAllPlaceholders(); 
        }, 5000);
        
        // Initial checks
        setTimeout(function() { self.updateAllPlaceholders(); }, 500);
        setTimeout(function() { self.updateAllPlaceholders(); }, 2000);
        setTimeout(function() { self.updateAllPlaceholders(); }, 5000);
    },
    
    updateAllPlaceholders: function() {
        var containers = document.querySelectorAll('.consent-inline-container[data-service]');
        var self = this;
        
        if (containers.length === 0) {
            return;
        }
        
        var cookieData = self.getCookieData();
        
        for (var i = 0; i < containers.length; i++) {
            var container = containers[i];
            var serviceKey = container.getAttribute('data-service');
            
            if (cookieData.consents && cookieData.consents.indexOf(serviceKey) !== -1) {
                self.loadContent(container);
            }
        }
    },
    
    accept: function(consentId, serviceKey, button) {
        var container = button.closest('.consent-inline-container');
        // Consent direkt ohne Bestätigung setzen - User hat bereits bewusst geklickt
        this.saveConsent(serviceKey);
        this.loadContent(container);
        this.logConsent(consentId, serviceKey, 'accepted');
    },
    
    allowAllForService: function(serviceKey) {
        // Alle Platzhalter für diesen Service laden
        var containers = document.querySelectorAll('.consent-inline-container[data-service="' + serviceKey + '"]');
        var self = this;
        
        // Consent für Service setzen
        self.saveConsent(serviceKey);
        
        // Alle Container dieses Services laden
        for (var i = 0; i < containers.length; i++) {
            self.loadContent(containers[i]);
        }
        
        // Log für alle Container
        for (var i = 0; i < containers.length; i++) {
            var consentId = containers[i].getAttribute('data-consent-id');
            if (consentId) {
                self.logConsent(consentId, serviceKey, 'allowed_all');
            }
        }
    },
    
    showDetails: function(serviceKey) {
        // Consent Manager Box öffnen falls verfügbar
        if (typeof consent_manager_showBox === "function") {
            consent_manager_showBox();
            
            // Nach kurzer Verzögerung Details aufklappen und zum Service scrollen
            setTimeout(function() {
                var detailsBtn = document.getElementById("consent_manager-toggle-details");
                if (detailsBtn && !document.getElementById("consent_manager-detail").classList.contains("consent_manager-hidden")) {
                    detailsBtn.click();
                }
                
                var serviceElements = document.querySelectorAll("[data-uid*='" + serviceKey + "']");
                if (serviceElements.length > 0) {
                    serviceElements[0].scrollIntoView({ behavior: "smooth", block: "center" });
                }
            }, 300);
        }
        // Kein Alert oder Fallback mehr - nur Consent Manager Box
    },
    
    saveConsent: function(serviceKey) {
        var cookieData = this.getCookieData();
        
        if (!cookieData.consents.includes(serviceKey)) {
            cookieData.consents.push(serviceKey);
            this.setCookieData(cookieData);
        }
        
        document.dispatchEvent(new CustomEvent(\"consent-inline-accepted\", {
            detail: { service: serviceKey }
        }));
    },
    
    loadContent: function(container) {
        var contentScript = container.querySelector(\".consent-content-data\");
        if (!contentScript) {
            console.error('No content script found in container');
            return;
        }
        
        var code = contentScript.innerHTML;
        
        var tempTextArea = document.createElement(\"textarea\");
        tempTextArea.innerHTML = code;
        var decodedCode = tempTextArea.value;
        
        var wrapper = document.createElement(\"div\");
        wrapper.innerHTML = decodedCode;
        
        while (wrapper.firstChild) {
            container.parentNode.insertBefore(wrapper.firstChild, container);
        }
        
        container.remove();
    },
    
    logConsent: function(consentId, serviceKey, action) {
        fetch(window.location.href, {
            method: \"POST\",
            headers: {
                \"Content-Type\": \"application/json\",
                \"X-Requested-With\": \"XMLHttpRequest\"
            },
            body: JSON.stringify({
                \"rex-api-call\": \"consent_manager_inline_log\",
                consent_id: consentId,
                service: serviceKey,
                action: action
            })
        }).catch(function(error) {
            console.warn(\"Consent logging failed:\", error);
        });
    },
    
    getCookieData: function() {
        var cookieValue = this.getCookie('consent_manager');
        
        if (!cookieValue) {
            return {
                consents: [],
                version: 4,
                cachelogid: Date.now()
            };
        }
        
        // URL-Dekodierung falls nötig
        try {
            cookieValue = decodeURIComponent(cookieValue);
        } catch (e) {
            // Cookie decoding failed, using raw value
        }
        
        try {
            var data = JSON.parse(cookieValue);
            
            // Verschiedene Cookie-Formate unterstützen
            if (data.consents) {
                return data;
            } else if (Array.isArray(data)) {
                // Altes Format: direkt Array
                return {
                    consents: data,
                    version: 4,
                    cachelogid: Date.now()
                };
            } else if (typeof data === 'object' && data.cookies) {
                // Anderes Format mit 'cookies' Property
                return {
                    consents: data.cookies || [],
                    version: data.version || 4,
                    cachelogid: data.cachelogid || Date.now()
                };
            }
        } catch (e) {
            console.warn(\"Consent Manager: Invalid cookie JSON format:\", e, 'Raw:', cookieValue);
        }
        
        // Fallback: String-basierte Suche nach Service-Keys
        var serviceKeys = this.extractServiceKeysFromString(cookieValue);
        if (serviceKeys.length > 0) {
            return {
                consents: serviceKeys,
                version: 4,
                cachelogid: Date.now()
            };
        }
        
        return {
            consents: [],
            version: 4,
            cachelogid: Date.now()
        };
    },
    
    extractServiceKeysFromString: function(cookieString) {
        var services = [];
        var knownServices = ['youtube', 'vimeo', 'google-maps', 'facebook', 'twitter', 'instagram'];
        
        for (var i = 0; i < knownServices.length; i++) {
            if (cookieString.indexOf(knownServices[i]) !== -1) {
                services.push(knownServices[i]);
            }
        }
        
        return services;
    },
    
    setCookieData: function(data) {
        var expires = new Date();
        expires.setTime(expires.getTime() + (365 * 24 * 60 * 60 * 1000));
        
        document.cookie = \"consent_manager=\" + JSON.stringify(data) + 
                         \"; expires=\" + expires.toUTCString() + 
                         \"; path=/; SameSite=Lax\";
    },
    
    getCookie: function(name) {
        var value = \"; \" + document.cookie;
        var parts = value.split(\"; \" + name + \"=\");
        if (parts.length === 2) return parts.pop().split(\";\").shift();
        return null;
    }
};

if (document.readyState === \"loading\") {
    document.addEventListener(\"DOMContentLoaded\", function() {
        consentManagerInline.init();
    });
};

// Auto-Init nur einmal ausführen
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() { 
        window.consentManagerInline.init(); 
    });
} else {
    window.consentManagerInline.init();
}
}
</script>";
    }

    /**
     * CSS für Inline-Consent ausgeben
     * 
     * @param bool $useCustom Eigenes CSS verwenden (deaktiviert Standard-CSS)
     * @param string $customPath Pfad zu eigenem CSS
     */
    public static function getCSS($useCustom = false, $customPath = '')
    {
        // Prüfe auf eigenes CSS-Fragment oder -Datei
        if ($useCustom) {
            if (!empty($customPath) && file_exists($customPath)) {
                return '<link rel="stylesheet" href="' . rex_escape($customPath) . '">';
            }
            
            // Prüfe Fragment-Override
            $customFragment = rex_path::frontend('templates/consent_inline_styles.css');
            if (file_exists($customFragment)) {
                return '<link rel="stylesheet" href="' . rex_url::frontend('templates/consent_inline_styles.css') . '">';
            }
            
            // Kein eigenes CSS gefunden - verwende Standard
        }
        return '
        <style>
        .consent-inline-placeholder {
            /* CSS Custom Properties für individuelle Anpassung */
            --consent-bg-color: #f8f9fa;
            --consent-border-color: #dee2e6;
            --consent-border-radius: 8px;
            --consent-border-width: 2px;
            --consent-margin: 1rem 0;
            --consent-min-height: 300px;
            
            position: relative;
            background: var(--consent-bg-color);
            border: var(--consent-border-width) dashed var(--consent-border-color);
            border-radius: var(--consent-border-radius);
            overflow: hidden;
            margin: var(--consent-margin);
        }
        
        .consent-inline-content {
            /* Content Layout Variablen */
            --consent-content-min-height: var(--consent-min-height, 300px);
            
            position: relative;
            min-height: var(--consent-content-min-height);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .consent-inline-thumbnail {
            /* Thumbnail Variablen */
            --consent-thumbnail-opacity: 0.3;
            
            width: 100%;
            height: 100%;
            opacity: var(--consent-thumbnail-opacity);
            position: absolute;
            top: 0;
            left: 0;
            object-fit: cover;
            min-height: var(--consent-content-min-height);
        }
        
        .consent-inline-overlay {
            /* Overlay Design Variablen */
            --consent-overlay-bg: rgba(255, 255, 255, 0.95);
            --consent-overlay-padding: 2rem;
            --consent-overlay-border-radius: 8px;
            --consent-overlay-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --consent-overlay-max-width: 90%;
            
            position: relative;
            z-index: 2;
            background: var(--consent-overlay-bg);
            padding: var(--consent-overlay-padding);
            border-radius: var(--consent-overlay-border-radius);
            text-align: center;
            box-shadow: var(--consent-overlay-shadow);
            max-width: var(--consent-overlay-max-width);
            width: auto;
        }
        
        .consent-inline-icon {
            /* Icon Variablen */
            --consent-icon-size: 3rem;
            --consent-icon-color: #6c757d;
            --consent-icon-margin: 1rem;
            
            font-size: var(--consent-icon-size);
            margin-bottom: var(--consent-icon-margin);
            color: var(--consent-icon-color);
            line-height: 1;
        }
        
        .consent-inline-icon i {
            font-size: inherit;
        }
        
        .consent-inline-icon [uk-icon] {
            color: inherit;
        }
        
        .consent-inline-title {
            /* Title Variablen */
            --consent-title-size: 1.25rem;
            --consent-title-color: inherit;
            --consent-title-margin: 0 0 0.5rem 0;
            --consent-title-weight: 600;
            
            margin: var(--consent-title-margin);
            font-size: var(--consent-title-size);
            font-weight: var(--consent-title-weight);
            color: var(--consent-title-color);
        }
        
        .consent-inline-notice {
            /* Notice Text Variablen */
            --consent-notice-color: #6c757d;
            --consent-notice-size: 0.9rem;
            --consent-notice-margin: 0 0 1rem 0;
            
            margin: var(--consent-notice-margin);
            color: var(--consent-notice-color);
            font-size: var(--consent-notice-size);
        }
        
        .consent-inline-privacy-link [uk-icon] {
            margin-right: 0.3rem;
        }
        
        .consent-inline-privacy-link i {
            margin-right: 0.3rem;
        }
        
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
        
        .consent-inline-title {
            margin: 0 0 0.5rem 0;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .consent-inline-notice {
            margin: 0 0 1rem 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .consent-inline-privacy-link {
            margin: 0 0 1.5rem 0;
        }
        
        .consent-inline-privacy-link a {
            color: #007bff;
            text-decoration: none;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .consent-inline-privacy-link a:hover {
            color: #0056b3;
            text-decoration: underline;
        }
        
        .consent-inline-action-text {
            font-size: 0.9rem;
            color: var(--consent-notice-color, #6c757d);
            margin: 0.5rem 0 1rem 0;
            text-align: center;
            font-weight: 500;
        }
        
        .consent-inline-actions {
            display: flex;
            gap: 0.4rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        /* Basis Button Variablen */
        .consent-inline-actions {
            --consent-btn-padding: 0.6rem 0.8rem;
            --consent-btn-border-radius: 4px;
            --consent-btn-font-size: 0.85rem;
            --consent-btn-transition: all 0.2s;
            --consent-btn-min-width: 100px;
        }
        
        .btn-consent-once {
            --consent-btn-once-bg: #17a2b8;
            --consent-btn-once-hover-bg: #138496;
            --consent-btn-once-color: white;
            
            background: var(--consent-btn-once-bg);
            color: var(--consent-btn-once-color);
            border: none;
            padding: var(--consent-btn-padding);
            border-radius: var(--consent-btn-border-radius);
            cursor: pointer;
            font-size: var(--consent-btn-font-size);
            transition: var(--consent-btn-transition);
            min-width: var(--consent-btn-min-width);
            text-align: center;
        }
        
        .btn-consent-once:hover {
            background: var(--consent-btn-once-hover-bg);
        }
        
        .btn-consent-allow-all {
            --consent-btn-allow-bg: #28a745;
            --consent-btn-allow-hover-bg: #218838;
            --consent-btn-allow-color: white;
            
            background: var(--consent-btn-allow-bg);
            color: var(--consent-btn-allow-color);
            border: none;
            padding: var(--consent-btn-padding);
            border-radius: var(--consent-btn-border-radius);
            cursor: pointer;
            font-size: var(--consent-btn-font-size);
            transition: var(--consent-btn-transition);
            min-width: var(--consent-btn-min-width);
            text-align: center;
        }
        
        .btn-consent-allow-all:hover {
            background: var(--consent-btn-allow-hover-bg);
        }
        
        .btn-consent-details {
            --consent-btn-details-bg: #6c757d;
            --consent-btn-details-hover-bg: #5a6268;
            --consent-btn-details-color: white;
            
            background: var(--consent-btn-details-bg);
            color: var(--consent-btn-details-color);
            border: none;
            padding: var(--consent-btn-padding);
            border-radius: var(--consent-btn-border-radius);
            cursor: pointer;
            font-size: var(--consent-btn-font-size);
            transition: var(--consent-btn-transition);
            min-width: var(--consent-btn-min-width);
            text-align: center;
        }
        
        .btn-consent-details:hover {
            background: var(--consent-btn-details-hover-bg);
        }
        
        @media (max-width: 768px) {
            .consent-inline-placeholder {
                /* Mobile Variablen */
                --consent-min-height: 250px;
                --consent-overlay-padding: 1.5rem;
                --consent-overlay-max-width: 95%;
            }
            
            .consent-inline-content {
                min-height: var(--consent-min-height);
            }
            
            .consent-inline-thumbnail {
                min-height: var(--consent-min-height);
            }
            
            .consent-inline-overlay {
                padding: var(--consent-overlay-padding);
                max-width: var(--consent-overlay-max-width);
            }
            
            .consent-inline-actions {
                flex-direction: column;
                gap: 0.6rem;
            }
            
            .consent-inline-actions button {
                width: 100%;
                min-width: auto;
            }
        }
        </style>';
    }
    
    /**
     * Button-Text aus der Texte-Verwaltung abrufen
     * 
     * @param string $textKey
     * @param string $fallback
     * @return string
     */
    private static function getButtonText($textKey, $fallback = '')
    {
        $clang = rex_clang::getCurrentId();
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT text FROM ' . rex::getTable('consent_manager_text') . ' WHERE uid = :uid AND clang_id = :clang_id', [
            'uid' => $textKey,
            'clang_id' => $clang
        ]);
        
        if ($sql->getRows() > 0) {
            return $sql->getValue('text');
        }
        
        return $fallback;
    }
}

/**
 * Globale Helper-Funktion für einfache Nutzung in Templates
 */
function doConsent($serviceKey, $content, $options = [])
{
    return consent_manager_inline::doConsent($serviceKey, $content, $options);
}
