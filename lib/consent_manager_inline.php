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
            
            // Debug: Service-Daten loggen
            if (rex::isDebugMode() || true) {
                error_log('Consent Manager Inline - Service loaded for: ' . $serviceKey);
                error_log('Provider Link Privacy: ' . ($service['provider_link_privacy'] ?? 'NULL'));
                error_log('Provider: ' . ($service['provider'] ?? 'NULL'));
            }
            
            return $service;
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
            'icon' => '🎥',
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
            'icon' => '📍',
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
            'icon' => '🔗',
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
        $fragment->setVar('button_inline_details_text', self::getButtonText('button_inline_details', 'Alle Einstellungen'));
        $fragment->setVar('inline_placeholder_text', self::getButtonText('inline_placeholder_text', 'Inhalt laden'));
        $fragment->setVar('inline_privacy_notice', self::getButtonText('inline_privacy_notice', 'Für die Anzeige werden Cookies benötigt.'));
        $fragment->setVar('inline_title_fallback', self::getButtonText('inline_title_fallback', 'Externes Medium'));
        $fragment->setVar('inline_privacy_link_text', self::getButtonText('inline_privacy_link_text', 'Datenschutzerklärung von'));
        
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
            if (e.target.matches('.consent-inline-accept')) {
                e.preventDefault();
                var button = e.target;
                var consentId = button.getAttribute('data-consent-id');
                var serviceKey = button.getAttribute('data-service');
                self.accept(consentId, serviceKey, button);
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
    
    showDetails: function(serviceKey) {
        // Consent Manager Box öffnen falls verfügbar
        if (typeof consent_manager_showBox === \"function\") {
            consent_manager_showBox();
            
            // Nach kurzer Verzögerung Details aufklappen und zum Service scrollen
            setTimeout(function() {
                var detailsBtn = document.getElementById(\"consent_manager-toggle-details\");
                if (detailsBtn && !document.getElementById(\"consent_manager-detail\").classList.contains(\"consent_manager-hidden\")) {
                    detailsBtn.click();
                }
                
                var serviceElements = document.querySelectorAll(\"[data-uid*='\" + serviceKey + \"']\");
                if (serviceElements.length > 0) {
                    serviceElements[0].scrollIntoView({ behavior: \"smooth\", block: \"center\" });
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
     * CSS für Inline-Consent generieren
     */
    public static function getCSS()
    {
        return '
        <style>
        .consent-inline-placeholder {
            position: relative;
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            margin: 1rem 0;
        }
        
        .consent-inline-content {
            position: relative;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .consent-inline-thumbnail {
            width: 100%;
            height: auto;
            opacity: 0.3;
            position: absolute;
            top: 0;
            left: 0;
            object-fit: cover;
        }
        
        .consent-inline-overlay {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .consent-inline-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
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
        
        .consent-inline-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-consent-accept {
            background: #28a745;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.2s;
        }
        
        .btn-consent-accept:hover {
            background: #218838;
        }
        
        .btn-consent-details {
            background: #6c757d;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.2s;
        }
        
        .btn-consent-details:hover {
            background: #5a6268;
        }
        
        @media (max-width: 768px) {
            .consent-inline-overlay {
                padding: 1rem;
            }
            
            .consent-inline-actions {
                flex-direction: column;
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
