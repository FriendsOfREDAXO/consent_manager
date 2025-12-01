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
        
        // Debug-Funktion - nur aktiv wenn consentManagerDebug verfügbar
        debug: function(message, data) {
            // Sichere Prüfung ohne Seiteneffekte
            try {
                if (typeof window !== 'undefined' && 
                    window.consentManagerDebug && 
                    typeof window.consentManagerDebug.log === 'function') {
                    window.consentManagerDebug.log(message, data);
                }
            } catch (e) {
                // Fehler ignorieren - Debug-Funktion soll nie andere Skripte stören
            }
        },
        
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
            var lastCookieValue = self.getCookie('consentmanager');
            setInterval(function() {
                var currentCookieValue = self.getCookie('consentmanager');
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
            
            // Event-Handler für Buttons mit spezifischer Priorität
            document.addEventListener('click', function(e) {
                // Eindeutig nur "Einmal laden" Button - Lädt NUR diesen einen Container
                if (e.target.matches('.consent-inline-once') && !e.target.matches('.consent-inline-allow-all')) {
                    e.preventDefault();
                    e.stopPropagation();
                    self.debug('🎯 Individual "Einmal laden" clicked');
                    var button = e.target;
                    var consentId = button.getAttribute('data-consent-id');
                    var serviceKey = button.getAttribute('data-service');
                    self.acceptIndividual(consentId, serviceKey, button);
                    return;
                }
                
                // "Alle erlauben" Button - Lädt alle Container vom gleichen Service
                if (e.target.matches('.consent-inline-allow-all')) {
                    e.preventDefault();
                    e.stopPropagation();
                    self.debug('🔄 "Alle erlauben" clicked');
                    var serviceKey = e.target.getAttribute('data-service');
                    self.allowAllForService(serviceKey);
                    return;
                }
                
                // Details Button
                if (e.target.matches('.consent-inline-details')) {
                    e.preventDefault();
                    e.stopPropagation();
                    var serviceKey = e.target.getAttribute('data-service');
                    self.showDetails(serviceKey);
                    return;
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
            var containers = document.querySelectorAll('.consent-inline-container[data-service]:not([data-loaded])');
            var self = this;
            
            if (containers.length === 0) {
                return;
            }
            
            var cookieData = self.getCookieData();
            
            // NUR Container laden, wenn GLOBALES Consent für Service vorhanden ist
            // (individual accepts setzen kein globales Consent)
            for (var i = 0; i < containers.length; i++) {
                var container = containers[i];
                var serviceKey = container.getAttribute('data-service');
                
                // Nur laden wenn globales Consent vorhanden (durch "Alle erlauben" gesetzt)
                if (cookieData.consents && cookieData.consents.indexOf(serviceKey) !== -1) {
                    self.debug('🔄 Auto-loading container due to global consent for:', serviceKey);
                    self.loadContent(container);
                }
            }
        },
        
        // Neue Funktion: Lädt nur den individuellen Container (NICHT alle!)
        acceptIndividual: function(consentId, serviceKey, button) {
            this.debug('🎯 acceptIndividual: Loading ONLY this container for service:', serviceKey);
            var container = button.closest('.consent-inline-container');
            
            // WICHTIG: Consent NICHT global setzen - nur diesen Container laden
            this.loadContent(container);
            this.logConsent(consentId, serviceKey, 'accepted_individual');
            
            // Custom Event für diesen einzelnen Container
            document.dispatchEvent(new CustomEvent('consent-inline-individual-accepted', {
                detail: { 
                    service: serviceKey, 
                    consentId: consentId,
                    container: container
                }
            }));
        },

        // Alte accept Funktion bleibt für Kompatibilität
        accept: function(consentId, serviceKey, button) {
            // Redirect to individual accept for safety
            this.acceptIndividual(consentId, serviceKey, button);
        },
        
        allowAllForService: function(serviceKey) {
            this.debug('🔄 allowAllForService: Loading ALL containers for service:', serviceKey);
            // Alle Platzhalter für diesen Service laden
            var containers = document.querySelectorAll('.consent-inline-container[data-service="' + serviceKey + '"]');
            var self = this;
            
            this.debug('🔄 Found ' + containers.length + ' containers to load');
            
            // Consent für Service GLOBAL setzen (damit zukünftige auch direkt laden)
            self.saveConsent(serviceKey);
            
            // Alle Container dieses Services laden
            for (var i = 0; i < containers.length; i++) {
                this.debug('🔄 Loading container ' + (i+1) + ' of ' + containers.length);
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
            this.debug('🎬 loadContent called for container:', container);
            
            // Markiere als geladen damit nicht erneut verarbeitet
            container.setAttribute('data-loaded', 'true');
            
            var contentScript = container.querySelector('.consent-content-data');
            if (!contentScript) {
                console.error('❌ No content script found in container');
                return;
            }
            
            this.debug('📜 Content script found:', contentScript);
            
            var code = contentScript.innerHTML;
            this.debug('📝 Raw code:', code.substring(0, 100));
            
            var tempTextArea = document.createElement('textarea');
            tempTextArea.innerHTML = code;
            var decodedCode = tempTextArea.value;
            
            this.debug('🔓 Decoded code:', decodedCode.substring(0, 100));
            
            var wrapper = document.createElement('div');
            wrapper.innerHTML = decodedCode;
            
            this.debug('📦 Wrapper children:', {count: wrapper.children.length, children: wrapper.children});
            
            // Inhalte vor Container einfügen
            var insertedCount = 0;
            while (wrapper.firstChild) {
                this.debug('➡️ Inserting child:', wrapper.firstChild);
                container.parentNode.insertBefore(wrapper.firstChild, container);
                insertedCount++;
            }
            
            this.debug('✅ Inserted ' + insertedCount + ' elements');
            
            // Container jetzt entfernen
            container.remove();
            this.debug('🗑️ Container removed');
        },
        
        logConsent: function(consentId, serviceKey, action) {
            // Globales Consent Manager Logging nutzen
            var cookieData = this.getCookieData();
            if (cookieData.consentid) {
                var formData = new FormData();
                formData.append('rex-api-call', 'consent_manager');
                formData.append('domain', window.location.hostname);
                formData.append('consentid', cookieData.consentid);
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                }).catch(function(error) {
                    console.warn('Consent logging failed:', error);
                });
            }
        },
        
        getCookieData: function() {
            // resolve the current expected major version from global params when available
            var currentMajorVersion = 4;
            try {
                if (typeof consent_manager_parameters !== 'undefined' && consent_manager_parameters && consent_manager_parameters.version) {
                    currentMajorVersion = parseInt(consent_manager_parameters.version) || currentMajorVersion;
                }
            } catch (e) {
                // ignore and fallback to default
            }
            var cookieValue = this.getCookie('consentmanager');
            
        if (!cookieValue) {
            return {
                consents: [],
                version: currentMajorVersion,
                cachelogid: Date.now(),
                consentid: this.generateConsentId()
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
                    // consentid hinzufügen falls nicht vorhanden
                    if (!data.consentid) {
                        data.consentid = this.generateConsentId();
                    }
                    return data;
                } else if (Array.isArray(data)) {
                    // Altes Format: direkt Array
                    return {
                        consents: data,
                        version: currentMajorVersion,
                        cachelogid: Date.now(),
                        consentid: this.generateConsentId()
                    };
                } else if (typeof data === 'object' && data.cookies) {
                    // Anderes Format mit 'cookies' Property
                    return {
                        consents: data.cookies || [],
                        version: data.version || currentMajorVersion,
                        cachelogid: data.cachelogid || Date.now(),
                        consentid: data.consentid || this.generateConsentId()
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
                        version: currentMajorVersion,
                cachelogid: Date.now(),
                consentid: this.generateConsentId()
            };
        }
            return {
                consents: [],
                version: currentMajorVersion,
            cachelogid: Date.now(),
            consentid: this.generateConsentId()
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
        
        // Lösche alte/malformed Consent-Cookies (inkl. ggf. anderer consent_manager-Namen)
        clearOldConsentCookies: function() {
            try {
                var cookies = document.cookie ? document.cookie.split('; ') : [];
                var hostname = window.location.hostname;
                var configuredDomain = null;
                try { configuredDomain = (typeof consent_manager_parameters !== 'undefined' && consent_manager_parameters && consent_manager_parameters.domain) ? consent_manager_parameters.domain : null; } catch (e) { configuredDomain = null; }

                var useCookiesApi = (typeof Cookies !== 'undefined' && typeof Cookies.remove === 'function');

                cookies.forEach(function (c) {
                    var name = c.split('=')[0];
                    if (!name) return;

                    // Prüfe auf consent_manager-Präfix ODER bekannte alte Namen
                    if (name.indexOf('consent_manager') === 0 || name.indexOf('consentmanager') === 0) {
                        if (useCookiesApi) {
                            try { Cookies.remove(name); } catch (e) {}
                            try { Cookies.remove(name, { path: '/' }); } catch (e) {}

                            if (configuredDomain) {
                                try { Cookies.remove(name, { path: '/', domain: configuredDomain }); } catch (e) {}
                                try { Cookies.remove(name, { path: '/', domain: '.' + configuredDomain }); } catch (e) {}
                            }

                            if (hostname) {
                                try { Cookies.remove(name, { path: '/', domain: hostname }); } catch (e) {}
                                try { Cookies.remove(name, { path: '/', domain: '.' + hostname }); } catch (e) {}
                            }
                        } else {
                            // fallback to document.cookie
                            document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; SameSite=Lax";
                            if (configuredDomain) {
                                try { document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; domain=" + configuredDomain + "; SameSite=Lax"; } catch (e) {}
                                try { document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; domain=." + configuredDomain + "; SameSite=Lax"; } catch (e) {}
                            }
                            try { document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; domain=" + hostname + "; SameSite=Lax"; } catch (e) {}
                            try { document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; domain=." + hostname + "; SameSite=Lax"; } catch (e) {}
                        }
                    }
                });
            } catch (e) {
                this.debug('clearOldConsentCookies failed', e);
            }
        },

        setCookieData: function(data) {
            // Vor dem Setzen: alte / invalide Cookies entfernen
            var shouldClear = false;
            try {
                var raw = this.getCookie('consentmanager');
                if (raw !== null) {
                    try {
                        var existing = JSON.parse(raw);

                        // Wenn existierendes Cookie keine oder inkompatible Struktur hat -> löschen
                        // If existing cookie is missing/invalid or doesn't match expected major version -> clear
                        var expectedVersion = parseInt(data.version || (typeof consent_manager_parameters !== 'undefined' ? consent_manager_parameters.version : currentMajorVersion)) || currentMajorVersion;
                        if (!existing || typeof existing !== 'object' || !existing.hasOwnProperty('consents') || parseInt(existing.version || 0) !== expectedVersion) {
                            shouldClear = true;
                        }
                    } catch (e) {
                        // nicht-JSON / malformed -> löschen
                        shouldClear = true;
                    }
                }
            } catch (e) {
                this.debug('pre-set validation failed', e);
            }
            var expires = new Date();
            expires.setTime(expires.getTime() + (365 * 24 * 60 * 60 * 1000));
            
            if (shouldClear) {
                // Clear once right before setting the new consent cookie
                this.clearOldConsentCookies();
            }

            // Setze das neue Cookie (neu und sauber)
            document.cookie = 'consent_manager=' + JSON.stringify(data) + 
                             '; expires=' + expires.toUTCString() + 
                             '; path=/; SameSite=Lax';
        },
        
        getCookie: function(name) {
            var value = '; ' + document.cookie;
            var parts = value.split('; ' + name + '=');
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        },
        
        generateConsentId: function() {
            return 'inline_' + Date.now() + '_' + Math.floor(Math.random() * 10000);
        }
    };

    // Auto-Init mit Verzögerung und sicherer DOM-Prüfung
    function safeInit() {
        try {
            if (typeof document !== 'undefined' && document.body) {
                window.consentManagerInline.init();
            }
        } catch (e) {
            // Init-Fehler ignorieren um andere Skripte nicht zu stören
            console.warn('ConsentManager Inline Init Error:', e);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', safeInit);
    } else if (document.readyState === 'interactive' || document.readyState === 'complete') {
        // Kleine Verzögerung für bessere Kompatibilität
        setTimeout(safeInit, 100);
    }
}
