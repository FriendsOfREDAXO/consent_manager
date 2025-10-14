/**
 * Consent Manager Inline JavaScript
 * Handles inline consent placeholders and user interactions
 */

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
            if (typeof consent_manager_showBox === 'function') {
                consent_manager_showBox();
                
                // Nach kurzer Verzögerung Details aufklappen und zum Service scrollen
                setTimeout(function() {
                    var detailsBtn = document.getElementById('consent_manager-toggle-details');
                    if (detailsBtn && !document.getElementById('consent_manager-detail').classList.contains('consent_manager-hidden')) {
                        detailsBtn.click();
                    }
                    
                    var serviceElements = document.querySelectorAll('[data-uid*="' + serviceKey + '"]');
                    if (serviceElements.length > 0) {
                        serviceElements[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
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
            
            document.dispatchEvent(new CustomEvent('consent-inline-accepted', {
                detail: { service: serviceKey }
            }));
        },
        
        loadContent: function(container) {
            var contentScript = container.querySelector('.consent-content-data');
            if (!contentScript) {
                console.error('No content script found in container');
                return;
            }
            
            var code = contentScript.innerHTML;
            
            var tempTextArea = document.createElement('textarea');
            tempTextArea.innerHTML = code;
            var decodedCode = tempTextArea.value;
            
            var wrapper = document.createElement('div');
            wrapper.innerHTML = decodedCode;
            
            while (wrapper.firstChild) {
                container.parentNode.insertBefore(wrapper.firstChild, container);
            }
            
            container.remove();
        },
        
        logConsent: function(consentId, serviceKey, action) {
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    'rex-api-call': 'consent_manager_inline_log',
                    consent_id: consentId,
                    service: serviceKey,
                    action: action
                })
            }).catch(function(error) {
                console.warn('Consent logging failed:', error);
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
                console.warn('Consent Manager: Invalid cookie JSON format:', e, 'Raw:', cookieValue);
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
            
            document.cookie = 'consent_manager=' + JSON.stringify(data) + 
                             '; expires=' + expires.toUTCString() + 
                             '; path=/; SameSite=Lax';
        },
        
        getCookie: function(name) {
            var value = '; ' + document.cookie;
            var parts = value.split('; ' + name + '=');
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }
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