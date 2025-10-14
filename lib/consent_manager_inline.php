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
        // Debug: Start
        $debug = '<div style="background:#d4edda;border:1px solid #c3e6cb;padding:5px;margin:5px 0;">DEBUG START: serviceKey='.$serviceKey.'</div>';
        
        // Service aus DB laden
        $service = self::getService($serviceKey);
        if (!$service) {
            // Debug: Immer Fehlermeldung anzeigen (temporär)
            return $debug . '<div style="background:#f8d7da;border:1px solid #f5c6cb;padding:10px;margin:10px 0;">Consent Manager: Service "'.$serviceKey.'" nicht gefunden</div>';
        }
        
        $debug .= '<div style="background:#d4edda;border:1px solid #c3e6cb;padding:5px;margin:5px 0;">DEBUG: Service gefunden</div>';

        // Bereits zugestimmt?
        $hasConsent = false;
        try {
            $hasConsent = consent_manager_util::has_consent($serviceKey);
            $debug .= '<div style="background:#d4edda;border:1px solid #c3e6cb;padding:5px;margin:5px 0;">DEBUG: has_consent=' . ($hasConsent ? 'true' : 'false') . '</div>';
        } catch (Exception $e) {
            $debug .= '<div style="background:#f8d7da;border:1px solid #f5c6cb;padding:5px;margin:5px 0;">DEBUG: has_consent ERROR: ' . $e->getMessage() . '</div>';
            return $debug . $content; // Fallback
        }
        
        if ($hasConsent) {
            return $debug . self::renderContent($content, $options);
        }

        // Consent ID generieren
        $consentId = uniqid('consent_', true);
        $debug .= '<div style="background:#d4edda;border:1px solid #c3e6cb;padding:5px;margin:5px 0;">DEBUG: consentId=' . $consentId . '</div>';
        
        // Standard-Optionen
        $options = array_merge([
            'title' => $service['service_name'],
            'placeholder_text' => $service['service_name'] . ' laden',
            'privacy_notice' => 'Für ' . $service['service_name'] . ' werden Cookies benötigt.',
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
                SELECT c.*, cg.name as group_name, cg.required as group_required
                FROM '.rex::getTable('consent_manager_cookie').' c
                LEFT JOIN '.rex::getTable('consent_manager_cookiegroup').' cg ON c.cookiegroup_id = cg.id
                WHERE c.uid = ? AND c.clang_id = ?
            ', [$serviceKey, rex_clang::getCurrentId()]);
        } catch (rex_sql_exception $e) {
            // Fallback für ältere Struktur ohne cookiegroup_id
            try {
                $sql->setQuery('
                    SELECT c.*, NULL as group_name, 0 as group_required
                    FROM '.rex::getTable('consent_manager_cookie').' c
                    WHERE c.uid = ? AND c.clang_id = ?
                ', [$serviceKey, rex_clang::getCurrentId()]);
            } catch (rex_sql_exception $e2) {
                // Weitere Fallback-Versuche für verschiedene Consent Manager Versionen
                try {
                    $sql->setQuery('
                        SELECT *, NULL as group_name, 0 as group_required
                        FROM '.rex::getTable('consent_manager_cookie').'
                        WHERE uid = ? AND clang_id = ?
                    ', [$serviceKey, rex_clang::getCurrentId()]);
                } catch (rex_sql_exception $e3) {
                    return null;
                }
            }
        }

        return $sql->getRows() > 0 ? $sql->getRow() : null;
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

        $thumbnail = $options['thumbnail'] === 'auto' 
            ? "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg"
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

        $iframe = '<iframe src="https://player.vimeo.com/video/'.$videoId.'" 
                   width="'.($options['width'] ?: '640').'" height="'.($options['height'] ?: '360').'" 
                   frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';

        return self::renderPlaceholderHTML($serviceKey, $iframe, $options, $consentId, $service, [
            'thumbnail' => $options['thumbnail'] !== 'auto' ? $options['thumbnail'] : null,
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
        $thumbnailHtml = '';
        if ($placeholderData['thumbnail']) {
            $thumbnailHtml = '<img src="'.$placeholderData['thumbnail'].'" alt="'.rex_escape($options['title']).'" class="consent-inline-thumbnail" />';
        }

        return '
        <div class="consent-inline-placeholder" data-consent-id="'.$consentId.'" data-service="'.$serviceKey.'">
            <div class="consent-inline-content">
                '.$thumbnailHtml.'
                <div class="consent-inline-overlay">
                    <div class="consent-inline-info">
                        <div class="consent-inline-icon">'.$placeholderData['icon'].'</div>
                        <h4 class="consent-inline-title">'.rex_escape($options['title']).'</h4>
                        <p class="consent-inline-notice">'.rex_escape($options['privacy_notice']).'</p>
                        
                        <div class="consent-inline-actions">
                            <button type="button" class="btn btn-consent-accept" 
                                    onclick="consentManagerInline.accept(\''.$consentId.'\', \''.$serviceKey.'\', this)">
                                <i class="fa fa-check"></i> '.$options['placeholder_text'].'
                            </button>
                            
                            <button type="button" class="btn btn-consent-details" 
                                    onclick="consentManagerInline.showDetails(\''.$serviceKey.'\')">
                                <i class="fa fa-info-circle"></i> Cookie-Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <script type="text/plain" data-consent-code="'.$serviceKey.'">
                '.$content.'
            </script>
        </div>';
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
        return '
        <script>
        window.consentManagerInline = {
            accept: function(consentId, serviceKey, button) {
                var serviceName = button.closest(".consent-inline-placeholder").querySelector(".consent-inline-title").textContent;
                
                if (confirm("Cookies für " + serviceName + " akzeptieren?\\n\\nDadurch werden externe Inhalte geladen und Cookies gesetzt.")) {
                    // Consent speichern
                    this.saveConsent(serviceKey);
                    
                    // Content laden
                    this.loadContent(button.closest(".consent-inline-placeholder"));
                    
                    // Logging
                    this.logConsent(consentId, serviceKey, "accepted");
                }
            },
            
            showDetails: function(serviceKey) {
                // Consent Manager Box öffnen falls vorhanden
                if (typeof consent_manager_showBox === "function") {
                    consent_manager_showBox();
                    
                    setTimeout(function() {
                        // Details aufklappen
                        var detailsBtn = document.getElementById("consent_manager-toggle-details");
                        if (detailsBtn && !document.getElementById("consent_manager-detail").classList.contains("consent_manager-hidden")) {
                            detailsBtn.click();
                        }
                        
                        // Zum Service scrollen
                        var serviceElements = document.querySelectorAll("[data-uid*=\"" + serviceKey + "\"]");
                        if (serviceElements.length > 0) {
                            serviceElements[0].scrollIntoView({ behavior: "smooth", block: "center" });
                        }
                    }, 300);
                } else {
                    // Fallback: Info-Dialog
                    alert("Weitere Cookie-Informationen finden Sie in unserer Datenschutzerklärung.");
                }
            },
            
            saveConsent: function(serviceKey) {
                var cookieData = this.getCookieData();
                
                if (!cookieData.consents.includes(serviceKey)) {
                    cookieData.consents.push(serviceKey);
                    this.setCookieData(cookieData);
                }
                
                // Event für andere Scripts
                document.dispatchEvent(new CustomEvent("consent-manager-inline-accepted", {
                    detail: { service: serviceKey }
                }));
            },
            
            loadContent: function(placeholder) {
                var code = placeholder.querySelector("[data-consent-code]").innerHTML;
                
                // HTML-Entitäten dekodieren
                var tempTextArea = document.createElement("textarea");
                tempTextArea.innerHTML = code;
                var decodedCode = tempTextArea.value;
                
                // Platzhalter durch den dekodierten HTML-Code ersetzen
                var wrapper = document.createElement("div");
                wrapper.innerHTML = decodedCode;
                
                // Alle Child-Nodes übertragen
                while (wrapper.firstChild) {
                    placeholder.parentNode.insertBefore(wrapper.firstChild, placeholder);
                }
                
                placeholder.remove();
            },
            
            logConsent: function(consentId, serviceKey, action) {
                fetch(window.location.href, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: JSON.stringify({
                        "rex-api-call": "consent_manager_inline_log",
                        consent_id: consentId,
                        service: serviceKey,
                        action: action
                    })
                }).catch(function(error) {
                    console.warn("Consent logging failed:", error);
                });
            },
            
            getCookieData: function() {
                var cookieValue = this.getCookie("consent_manager");
                if (!cookieValue) {
                    return {
                        consents: [],
                        version: 4,
                        cachelogid: Date.now()
                    };
                }
                
                try {
                    return JSON.parse(cookieValue);
                } catch (e) {
                    console.warn("Consent Manager: Invalid cookie data, resetting");
                    return {
                        consents: [],
                        version: 4,
                        cachelogid: Date.now()
                    };
                }
            },
            
            setCookieData: function(data) {
                var expires = new Date();
                expires.setTime(expires.getTime() + (365 * 24 * 60 * 60 * 1000)); // 1 Jahr
                
                document.cookie = "consent_manager=" + JSON.stringify(data) + 
                                 "; expires=" + expires.toUTCString() + 
                                 "; path=/; SameSite=Lax";
            },
            
            getCookie: function(name) {
                var value = "; " + document.cookie;
                var parts = value.split("; " + name + "=");
                if (parts.length === 2) return parts.pop().split(";").shift();
                return null;
            }
        };
        </script>';
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
            margin: 0 0 1.5rem 0;
            color: #6c757d;
            font-size: 0.9rem;
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
}

/**
 * Globale Helper-Funktion für einfache Nutzung in Templates
 */
function doConsent($serviceKey, $content, $options = [])
{
    return consent_manager_inline::doConsent($serviceKey, $content, $options);
}