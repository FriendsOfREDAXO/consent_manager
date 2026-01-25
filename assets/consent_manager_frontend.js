// Issue #317: Remove domain parameter to prevent wildcard cookies (.example.com)
// Cookies must be domain-specific for GDPR compliance
// Cookie settings are now configurable via backend
const cmCookieSameSite = consent_manager_parameters.cookieSameSite || 'Lax';
const cmCookieSecure = consent_manager_parameters.cookieSecure || false;
const cmCookieName = consent_manager_parameters.cookieName || 'consentmanager';
const cmCookieAPI = Cookies.withAttributes({ expires: cmCookieExpires, path: '/', sameSite: cmCookieSameSite, secure: cmCookieSecure });

if (window.consentManagerDebugConfig && window.consentManagerDebugConfig.debug_enabled) {
    console.log('Consent Manager: Script loaded');
}

function debugLog(message, data) {
    if (window.consentManagerDebugConfig && window.consentManagerDebugConfig.debug_enabled) {
        if (data !== undefined) {
            console.log('Consent Manager: ' + message, data);
        } else {
            console.log('Consent Manager: ' + message);
        }
    }
}

// Helper: safe JSON parse for potentially-malformed external sources (cookies, attributes etc.)
// Must be global scope so consent_manager_hasconsent() and consent_manager_showBox() can access it
function safeJSONParse(input, fallback) {
    try {
        if (typeof input === 'string') return JSON.parse(input);
        if (typeof input === 'object' && input !== null) return input;
    } catch (e) {
        console.warn('consent_manager: safeJSONParse failed for input', input, e);
    }
    return fallback;
}

(function () {
    'use strict';
    var show = 0,
        cookieData = {},
        consents = [],
        addonVersion = -1,
        cachelogid = -1,
        cookieVersion = -1,
        cookieCachelogid = -1,
        consent_managerBox;

    consent_manager_parameters.no_cookie_set = false;
    
    // Auto-Inject Options (from backend configuration)
    var autoInjectOptions = window.consentManagerOptions || {};
    var reloadOnConsent = autoInjectOptions.reloadOnConsent || false;
    var showDelay = parseInt(autoInjectOptions.showDelay, 10) || 0;
    var autoFocus = autoInjectOptions.autoFocus !== false; // default true

    // Es gibt keinen Datenschutzcookie, Consent zeigen
    if (typeof cmCookieAPI.get(cmCookieName) === 'undefined') {
        cmCookieAPI.set(cmCookieName + '_test', 'test');
        // Test-Cookie konnte nicht gesetzt werden, kein Consent anzeigen
        if (typeof cmCookieAPI.get(cmCookieName + '_test') === 'undefined') {
            show = 0;
            consent_manager_parameters.no_cookie_set = true;
            console.warn('Addon consent_manager: Es konnte kein Cookie für die Domain ' + consent_manager_parameters.domain + ' gesetzt werden!');
        } else {
            cmCookieAPI.remove(cmCookieName + '_test');
            show = 1;
        }
    } else {
        cookieData = safeJSONParse(cmCookieAPI.get(cmCookieName), {});
        // Cookie-Version auslesen. Cookie-Version = Major-Version des AddOns zum Zeitpunkt des speicherns
        if (cookieData.hasOwnProperty('version')) {
            consents = cookieData.consents;
            cookieVersion = parseInt(cookieData.version);
            cookieCachelogid = parseInt(cookieData.cachelogid);
        }
    }

    if (consent_manager_box_template === '') {
        console.warn('Addon consent_manager: Keine Cookie-Gruppen / Cookies ausgewählt bzw. keine Domain zugewiesen! (' + location.hostname + ')');
        return;
    }
    consent_managerBox = new DOMParser().parseFromString(consent_manager_box_template, 'text/html');
    consent_managerBox = consent_managerBox.getElementById('consent_manager-background');
    document.querySelectorAll('body')[0].appendChild(consent_managerBox);

    // aktuelle Major-AddOn-Version auslesen
    addonVersion = parseInt(consent_manager_parameters.version);
    cachelogid = parseInt(consent_manager_parameters.cachelogid);
    // Cookie wurde mit einer aelteren Major-Version gesetzt, alle Consents loeschen und Consent anzeigen
    // - treat cookies older than v4 as incompatible
    // Treat cookies as incompatible when:
    // - cookie version is missing/invalid
    // - cookie major version is different from current addon major
    // - cachelogid mismatch
    if (isNaN(cookieVersion) || cookieVersion !== addonVersion || cachelogid !== cookieCachelogid) {
        show = 1;
        consents = [];
        deleteCookies();
    }

    // on startup trigger scripts of enabled consents
    debugLog('Startup: Triggering scripts for enabled consents', consents);
    consents.forEach(function (uid) {
        debugLog('Startup: Processing consent UID', uid);
        var scriptElement = consent_managerBox.querySelector('[data-uid="script-' + uid + '"]');
        var unselectElement = consent_managerBox.querySelector('[data-uid="script-unselect-' + uid + '"]');
        debugLog('Startup: Elements found', {
            scriptElement: !!scriptElement,
            unselectElement: !!unselectElement
        });
        addScript(scriptElement);
        removeScript(unselectElement);
    });

    // on startup trigger Google Consent Mode v2 update if consents exist
    if (consents.length > 0 && typeof window.GoogleConsentModeV2 !== 'undefined' && typeof window.GoogleConsentModeV2.setConsent === 'function') {
        var googleConsentFlags = mapConsentsToGoogleFlags(consents);
        debugLog('Auto-mapping Google Consent Mode flags', consents, googleConsentFlags);
        window.GoogleConsentModeV2.setConsent(googleConsentFlags);
    } else {
        debugLog('Auto-mapping skipped', {consents: consents, hasGCM: typeof window.GoogleConsentModeV2 !== 'undefined', hasSetConsent: typeof window.GoogleConsentModeV2?.setConsent === 'function'});
    }

    // on startup trigger unselect-scripts of disabled consents
    consent_managerBox.querySelectorAll('[data-cookie-uids]').forEach(function (el) {
        // array mit cookie uids
        var cookieUids = safeJSONParse(el.getAttribute('data-cookie-uids'), []);

        var consentsSet = new Set(consents);
        cookieUids.forEach(function (uid) {
            if(!consentsSet.has(uid)) {
                removeScript(consent_managerBox.querySelector('[data-uid="script-' + uid + '"]'));
                addScript(consent_managerBox.querySelector('[data-uid="script-unselect-' + uid + '"]'));
            }
        });
    });

    if (consent_manager_parameters.initially_hidden || consent_manager_parameters.no_cookie_set) {
        show = 0;
    }

    if (show) {
        // Apply delay if configured
        if (showDelay > 0) {
            setTimeout(function() {
                showBox();
            }, showDelay * 1000);
        } else {
            showBox();
        }
    }

    consent_managerBox.querySelectorAll('.consent_manager-close').forEach(function (el) {
        el.addEventListener('click', function () {
            if (el.classList.contains('consent_manager-save-selection')) {
                saveConsent('selection');
            } else if (el.classList.contains('consent_manager-accept-all')) {
                saveConsent('all');
            } else if (el.classList.contains('consent_manager-accept-none')) {
                saveConsent('none');
            } else if (el.classList.contains('consent_manager-close')) {
                if (!document.getElementById('consent_manager-detail').classList.contains('consent_manager-hidden')) {
                    document.getElementById('consent_manager-detail').classList.toggle('consent_manager-hidden');
                }
            }
            if (consent_manager_parameters.hidebodyscrollbar) {
                document.querySelector('body').style.overflow = 'auto';
            }
            document.getElementById('consent_manager-background').classList.add('consent_manager-hidden');
            document.getElementById('consent_manager-background').setAttribute('aria-hidden', 'true');
            
            // Trigger close event (Issue #156)
            document.dispatchEvent(new CustomEvent('consent_manager-close'));
            
            return false;
        });
    });

    var toggleDetailsBtn = document.getElementById('consent_manager-toggle-details');
    if (toggleDetailsBtn) {
        var detailElement = document.getElementById('consent_manager-detail');
        
        var toggleDetails = function() {
            detailElement.classList.toggle('consent_manager-hidden');
            var isExpanded = !detailElement.classList.contains('consent_manager-hidden');
            toggleDetailsBtn.setAttribute('aria-expanded', isExpanded);
        };
        
        toggleDetailsBtn.addEventListener('click', function () {
            toggleDetails();
            return false;
        });
        
        toggleDetailsBtn.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                toggleDetails();
                return false;
            }
        });
    }

    // Combined keyboard handler: ESC key + Focus Trap (Issue #326)
    document.addEventListener('keydown', function(event) {
        var consentBox = document.getElementById('consent_manager-background');
        if (!consentBox || consentBox.classList.contains('consent_manager-hidden')) {
            return;
        }
        
        // ESC key to close
        if (event.key === 'Escape' || event.key === 'Esc') {
            event.preventDefault();
            event.stopPropagation();
            if (consent_manager_parameters.hidebodyscrollbar) {
                document.querySelector('body').style.overflow = 'auto';
            }
            consentBox.classList.add('consent_manager-hidden');
            consentBox.setAttribute('aria-hidden', 'true');
            document.dispatchEvent(new CustomEvent('consent_manager-close'));
            return;
        }

        // Focus Trap: Keep focus within modal dialog
        if (event.key === 'Tab') {
            var wrapper = document.getElementById('consent_manager-wrapper');
            if (!wrapper || !wrapper.contains(document.activeElement)) {
                return;
            }
            
            var focusableElements = wrapper.querySelectorAll(
                'button:not([disabled]), input:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])'
            );
            var focusableArray = Array.from(focusableElements);
            var firstFocusable = focusableArray[0];
            var lastFocusable = focusableArray[focusableArray.length - 1];

            if (event.shiftKey) {
                if (document.activeElement === firstFocusable) {
                    event.preventDefault();
                    event.stopPropagation();
                    lastFocusable.focus();
                }
            } else {
                if (document.activeElement === lastFocusable) {
                    event.preventDefault();
                    event.stopPropagation();
                    firstFocusable.focus();
                }
            }
        }
    }, true); // Use capture phase for priority

    // Kombinierter Click-Handler für alle Consent-Box-Trigger
    // Verwendet Event-Delegation statt mehrere querySelectorAll
    document.addEventListener('click', function(e) {
        var target = e.target;
        
        // Bubble up durch DOM-Tree bis wir einen passenden Link/Element finden
        while (target && target !== document) {
            // Legacy-Klassen für bestehende Implementierungen
            if (target.classList && (target.classList.contains('consent_manager-show-box') || 
                target.classList.contains('consent_manager-show-box-reload'))) {
                e.preventDefault();
                showBox();
                return false;
            }
            
            // Neue Klasse für vereinfachtes Handling
            if (target.tagName === 'A' && target.classList && target.classList.contains('consent-settings-link')) {
                e.preventDefault();
                consent_manager_showBox();
                return false;
            }
            
            // Data-Attribut Alternative
            if (target.tagName === 'A' && target.getAttribute('data-consent-action') === 'settings') {
                e.preventDefault();
                consent_manager_showBox();
                return false;
            }
            
            target = target.parentElement;
        }
    });

    function saveConsent(toSave) {
        debugLog('saveConsent: Start', toSave);
        
        // Safety check: consent box must exist
        if (!consent_managerBox) {
            console.warn('Consent Manager: saveConsent called but consent_managerBox not initialized');
            return;
        }
        
        consents = [];
        var consentsSet = new Set(); // Verwende Set für schnellere Lookups
        var hasOptionalConsent = false;
        cookieData = {
            consents: [],
            version: addonVersion,
            consentid: consent_manager_parameters.consentid,
            cachelogid: consent_manager_parameters.cachelogid
        };
        // checkboxen
        if (toSave !== 'none') {
            consent_managerBox.querySelectorAll('[data-cookie-uids]').forEach(function (el) {
                // array mit cookie uids
                var cookieUids = safeJSONParse(el.getAttribute('data-cookie-uids'), []);
                if (el.checked || toSave === 'all') {
                    if (!el.disabled) {
                        hasOptionalConsent = true;
                    }
                    debugLog('saveConsent: Consent erteilt für', cookieUids);
                    cookieUids.forEach(function (uid) {
                        consents.push(uid);
                        consentsSet.add(uid);
                        debugLog('saveConsent: Führe Script aus für UID', uid);
                        var scriptElement = consent_managerBox.querySelector('[data-uid="script-' + uid + '"]');
                        var unselectElement = consent_managerBox.querySelector('[data-uid="script-unselect-' + uid + '"]');
                        debugLog('saveConsent: Elements gefunden', {
                            scriptElement: !!scriptElement,
                            unselectElement: !!unselectElement,
                            hasDataScript: scriptElement ? !!scriptElement.getAttribute('data-script') : false
                        });
                        addScript(scriptElement);
                        removeScript(unselectElement);
                    });
                } else {
                    debugLog('saveConsent: Consent verweigert für', cookieUids);
                    cookieUids.forEach(function (uid) {
                        removeScript(consent_managerBox.querySelector('[data-uid="script-' + uid + '"]'));
                        addScript(consent_managerBox.querySelector('[data-uid="script-unselect-' + uid + '"]'));
                    });
                }
            });
        } else {
            debugLog('saveConsent: Keine Consents (none)');
            consent_managerBox.querySelectorAll('[data-cookie-uids]').forEach(function (el) {
                // array mit cookie uids
                var cookieUids = safeJSONParse(el.getAttribute('data-cookie-uids'), []);
                if (el.disabled) {
                    cookieUids.forEach(function (uid) {
                        consents.push(uid);
                        addScript(consent_managerBox.querySelector('[data-uid="script-' + uid + '"]'));
                        removeScript(consent_managerBox.querySelector('[data-uid="script-unselect-' + uid + '"]'));
                    });
                } else {
                    el.checked = false;
                    cookieUids.forEach(function (uid) {
                        removeScript(consent_managerBox.querySelector('[data-uid="script-' + uid + '"]'));
                        addScript(consent_managerBox.querySelector('[data-uid="script-unselect-' + uid + '"]'));
                    });
                }
            });
        }

        cookieData.consents = consents;
        debugLog('saveConsent: Finale Consents', consents);

        // Remove potential old/stale consent cookies before setting a new one.
        try {
            deleteCookies();
        } catch (e) {
            // defensive - deleteCookies may fail in some environments
            console.warn('Consent Manager: deleteCookies() failed before setting cookie', e);
        }

        if (!hasOptionalConsent) {
            debugLog('saveConsent: Minimal consent only - setting short cookie lifetime (14 days)');
            cmCookieAPI.set(cmCookieName, JSON.stringify(cookieData), { expires: 14 });
        } else {
            cmCookieAPI.set(cmCookieName, JSON.stringify(cookieData));
        }
        
        // Google Consent Mode v2 Update
        if (typeof window.GoogleConsentModeV2 !== 'undefined' && typeof window.GoogleConsentModeV2.setConsent === 'function') {
            var googleConsentFlags = mapConsentsToGoogleFlags(consents);
            debugLog('Mapping consents to Google flags', consents, googleConsentFlags);
            window.GoogleConsentModeV2.setConsent(googleConsentFlags);
        } else {
            debugLog('Google Consent Mode not available for mapping');
        }
        
        if (typeof cmCookieAPI.get(cmCookieName) === 'undefined') {
            consent_manager_parameters.no_cookie_set = true;
            console.warn('Addon consent_manager: Es konnte kein Cookie für die Domain ' + document.domain + ' gesetzt werden!');
        } else {
            // Async logging to avoid blocking UI (replaced deprecated synchronous XHR)
            var url = consent_manager_parameters.fe_controller + '?rex-api-call=consent_manager&buster=' + new Date().getTime();
            var params = 'domain=' + encodeURIComponent(document.domain) + '&consentid=' + encodeURIComponent(consent_manager_parameters.consentid) + '&buster=' + new Date().getTime();
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Cache-Control': 'no-cache, no-store, max-age=0'
                },
                body: params
            }).catch(function(error) {
                console.error('Addon consent_manager: Fehler beim speichern des Consent!', error);
                debugLog('Consent logging failed', error);
            });
        }

        if (document.querySelectorAll('.consent_manager-show-box-reload').length || consent_manager_parameters.forcereload === 1 || reloadOnConsent) {
            location.reload();
        } else {
            document.dispatchEvent(new CustomEvent('consent_manager-saved', { detail: JSON.stringify(consents) }));
        }
    }

    function deleteCookies() {
        var domain = consent_manager_parameters.domain;
        var hostname = window.location.hostname;
        
        // Liste aller möglichen Cookie-Namen (alt + neu) für komplette Bereinigung
        var cookieNamesToDelete = ['consent_manager', 'consent_manager_test', 'consentmanager', 'consentmanager_test', cmCookieName, cmCookieName + '_test'];
        
        // Explizit alle Cookie-Namen bereinigen (alt + neu, alle Domain-Varianten)
        cookieNamesToDelete.forEach(function(name) {
            // Ohne Domain
            Cookies.remove(name);
            Cookies.remove(name, { 'path': '/' });
            
            // Mit Domain-Varianten
            Cookies.remove(name, { 'domain': domain });
            Cookies.remove(name, { 'domain': domain, 'path': '/' });
            Cookies.remove(name, { 'domain': ('.' + domain) });
            Cookies.remove(name, { 'domain': ('.' + domain), 'path': '/' });
            
            if (hostname !== domain) {
                Cookies.remove(name, { 'domain': hostname });
                Cookies.remove(name, { 'domain': hostname, 'path': '/' });
                Cookies.remove(name, { 'domain': ('.' + hostname) });
                Cookies.remove(name, { 'domain': ('.' + hostname), 'path': '/' });
            }
        });
    }

    function addScript(el) {
        if (!el) {
            debugLog('addScript: Element ist null/undefined');
            return;
        }
        
        var uid = el.getAttribute('data-uid') || 'unknown';
        debugLog('addScript: Processing element', {uid: uid, element: el});
        
        if (!el.children.length) {
            var encodedScript = el.getAttribute('data-script');
            debugLog('addScript: Encoded script data', {
                length: encodedScript ? encodedScript.length : 0,
                preview: encodedScript ? encodedScript.substring(0, 50) + '...' : 'empty'
            });
            
            if (!encodedScript) {
                debugLog('addScript: Kein data-script Attribut gefunden');
                return;
            }
            
            var scriptContent = '';
            try {
                scriptContent = window.atob(encodedScript);
                debugLog('addScript: Script erfolgreich dekodiert', {
                    length: scriptContent.length,
                    preview: scriptContent.substring(0, 100)
                });
            } catch (e) {
                console.error('addScript: Fehler beim Base64-Dekodieren', e);
                debugLog('addScript: Base64-Dekodierung fehlgeschlagen', e);
                return;
            }
            
            if (!scriptContent) {
                debugLog('addScript: Script-Inhalt ist leer nach Dekodierung');
                return;
            }
            
            // Validierung: Prüfe auf ungültige Zeichen oder fehlerhafte Kodierung
            if (scriptContent.includes('\ufffd') || scriptContent.includes('�')) {
                console.error('addScript: Script enthält ungültige Zeichen (fehlerhaftes Encoding)', {
                    uid: uid,
                    preview: scriptContent.substring(0, 100)
                });
                debugLog('addScript: Ungültiges Encoding erkannt, überspringe Script');
                return;
            }
            
            var scriptDom = null;
            try {
                scriptDom = new DOMParser().parseFromString(scriptContent, 'text/html');
                debugLog('addScript: DOM geparst, Scripts gefunden:', scriptDom.scripts.length);
            } catch (e) {
                console.error('addScript: Fehler beim Parsen des HTML', e);
                debugLog('addScript: HTML-Parsing fehlgeschlagen', e);
                return;
            }
            
            if (!scriptDom || !scriptDom.scripts || scriptDom.scripts.length === 0) {
                debugLog('addScript: Keine Scripts im geparsten DOM gefunden');
                return;
            }
            
            // Get nonce from consent_manager_parameters (passed from PHP)
            var nonce = consent_manager_parameters.cspNonce || null;
            
            // CSP-compatible: Create script elements properly
            for (var i = 0; i < scriptDom.scripts.length; i++) {
                var originalScript = scriptDom.scripts[i];
                var scriptNode = document.createElement('script');
                
                debugLog('addScript: Processing script #' + (i + 1), {
                    hasSrc: !!originalScript.src,
                    src: originalScript.src || 'inline',
                    hasContent: !!originalScript.textContent,
                    contentLength: originalScript.textContent ? originalScript.textContent.length : 0,
                    attributes: originalScript.attributes.length
                });
                
                // Apply nonce if available
                if (nonce) {
                    scriptNode.setAttribute('nonce', nonce);
                    debugLog('addScript: Nonce gesetzt', nonce);
                }
                
                // Copy other attributes
                for (var j = 0; j < originalScript.attributes.length; j++) {
                    var attr = originalScript.attributes[j];
                    if (attr.name !== 'nonce') { // Don't override nonce
                        scriptNode.setAttribute(attr.name, attr.value);
                        debugLog('addScript: Attribut kopiert', {name: attr.name, value: attr.value});
                    }
                }
                
                // Set src or inline content
                if (originalScript.src) {
                    // Duplikat-Check für externe Scripts
                    var scriptSrc = originalScript.src;
                    var existingScript = document.querySelector('script[src="' + scriptSrc + '"]');
                    
                    if (existingScript) {
                        debugLog('addScript: Script bereits geladen, überspringe', scriptSrc);
                        console.warn('Consent Manager: Script bereits geladen - Duplikat verhindert: ' + scriptSrc);
                        continue; // Überspringe dieses Script
                    }
                    
                    scriptNode.src = scriptSrc;
                    debugLog('addScript: External script wird geladen', scriptSrc);
                } else {
                    // Set inline script content
                    var inlineContent = originalScript.textContent;
                    if (inlineContent) {
                        scriptNode.textContent = inlineContent;
                        debugLog('addScript: Inline script gesetzt', {
                            contentLength: inlineContent.length,
                            preview: inlineContent.substring(0, 100)
                        });
                    } else {
                        debugLog('addScript: Inline script ist leer');
                        continue;
                    }
                }
                
                // Append to document body
                try {
                    document.body.appendChild(scriptNode);
                    debugLog('addScript: Script erfolgreich zum DOM hinzugefügt');
                } catch (e) {
                    // Enhanced error logging with CSP/CORS hints
                    var errorInfo = {
                        error: e.message,
                        uid: uid,
                        hasSrc: !!scriptNode.src,
                        hasContent: !!scriptNode.textContent,
                        src: scriptNode.src || 'inline'
                    };
                    
                    // Check for common CSP/CORS issues
                    if (e.message && (e.message.includes('Content Security Policy') || e.message.includes('CSP'))) {
                        errorInfo.hint = 'CSP violation - check Content-Security-Policy header';
                    } else if (scriptNode.src && e.message && e.message.includes('CORS')) {
                        errorInfo.hint = 'CORS error - external script may be blocked';
                    }
                    
                    console.error('addScript: Fehler beim Hinzufügen des Scripts', errorInfo);
                    debugLog('addScript: Fehler beim appendChild', e);
                }
            }
        } else {
            debugLog('addScript: Element hat bereits children, wird übersprungen');
        }
    }

    function removeScript(el) {
        if (!el) {
            return;
        }
        el.innerHTML = '';
    }

    function showBox() {
        // Safety check: consent box must exist
        if (!consent_managerBox) {
            console.warn('Consent Manager: showBox called but consent_managerBox not initialized');
            return;
        }
        
        var consentsSet = new Set(consents);
        consent_managerBox.querySelectorAll('[data-cookie-uids]').forEach(function (el) {
            var cookieUids = safeJSONParse(el.getAttribute('data-cookie-uids'), []);
            var check = cookieUids.every(function(uid) {
                return consentsSet.has(uid);
            });
            if (check) {
                el.checked = true;
            }
        });
        if (consent_manager_parameters.hidebodyscrollbar) {
            document.querySelector('body').style.overflow = 'hidden';
        }
        var consentBg = document.getElementById('consent_manager-background');
        consentBg.classList.remove('consent_manager-hidden');
        consentBg.setAttribute('aria-hidden', 'false');
        
        // Initialize aria-expanded for toggle button (A11y)
        var toggleBtn = document.getElementById('consent_manager-toggle-details');
        if (toggleBtn) {
            toggleBtn.setAttribute('aria-expanded', 'false');
        }
        
        // Focus the dialog wrapper for better accessibility (WCAG 2.1)
        // This allows screen readers to announce the dialog and users can tab to interactive elements
        // Focusing a button directly would bias user choice and is not recommended
        var dialogWrapper = document.getElementById('consent_manager-wrapper');
        if (dialogWrapper && autoFocus) {
            // Use setTimeout to ensure DOM is ready
            setTimeout(function() {
                dialogWrapper.focus();
            }, 100);
        }
        
        // Trigger show event (Issue #156)
        document.dispatchEvent(new CustomEvent('consent_manager-show'));
    }

})();

function mapConsentsToGoogleFlags(consents) {
    var flags = {
        'ad_storage': false,
        'ad_user_data': false,
        'ad_personalization': false,
        'analytics_storage': false,
        'personalization_storage': false,
        'functionality_storage': false,
        'security_storage': false
    };

    consents.forEach(function(uid) {
        var lowerUid = uid.toLowerCase();
        debugLog('Mapping UID', uid, lowerUid);
        
        // Standard Consent Manager Gruppen
        if (lowerUid === 'analytics') {
            flags['analytics_storage'] = true;
            debugLog('Mapped analytics to analytics_storage');
        }
        if (lowerUid === 'marketing') {
            flags['ad_storage'] = true;
            flags['ad_user_data'] = true;
            flags['ad_personalization'] = true;
            debugLog('Mapped marketing to ad_*');
        }
        if (lowerUid === 'functional') {
            flags['functionality_storage'] = true;
            debugLog('Mapped functional to functionality_storage');
        }
        if (lowerUid === 'preferences') {
            flags['personalization_storage'] = true;
            debugLog('Mapped preferences to personalization_storage');
        }
        if (lowerUid === 'necessary') {
            flags['security_storage'] = true;
            debugLog('Mapped necessary to security_storage');
        }
        
        // Google Analytics
        if (lowerUid.includes('google-analytics') || lowerUid.includes('analytics') || lowerUid.includes('ga')) {
            flags['analytics_storage'] = true;
        }
        
        // Google Tag Manager
        if (lowerUid.includes('google-tag-manager') || lowerUid.includes('gtm') || lowerUid.includes('tag-manager')) {
            flags['analytics_storage'] = true;
            flags['ad_storage'] = true;
            flags['ad_user_data'] = true;
            flags['ad_personalization'] = true;
        }
        
        // Google Ads
        if (lowerUid.includes('google-ads') || lowerUid.includes('adwords') || lowerUid.includes('google-adwords')) {
            flags['ad_storage'] = true;
            flags['ad_user_data'] = true;
            flags['ad_personalization'] = true;
        }
        
        // Facebook Pixel
        if (lowerUid.includes('facebook-pixel') || lowerUid.includes('facebook') || lowerUid.includes('meta-pixel')) {
            flags['ad_storage'] = true;
            flags['ad_user_data'] = true;
            flags['ad_personalization'] = true;
        }
        
        // YouTube
        if (lowerUid.includes('youtube') || lowerUid.includes('yt')) {
            flags['ad_storage'] = true;
            flags['personalization_storage'] = true;
        }
        
        // Google Maps
        if (lowerUid.includes('google-maps') || lowerUid.includes('maps') || lowerUid.includes('gmaps')) {
            flags['functionality_storage'] = true;
            flags['personalization_storage'] = true;
        }
        
        // Matomo
        if (lowerUid.includes('matomo') || lowerUid.includes('piwik')) {
            flags['analytics_storage'] = true;
        }
        
        // Hotjar
        if (lowerUid.includes('hotjar')) {
            flags['analytics_storage'] = true;
        }
        
        // Microsoft Clarity
        if (lowerUid.includes('microsoft-clarity') || lowerUid.includes('clarity')) {
            flags['analytics_storage'] = true;
        }
        
        // LinkedIn
        if (lowerUid.includes('linkedin')) {
            flags['ad_storage'] = true;
            flags['ad_user_data'] = true;
            flags['ad_personalization'] = true;
        }
        
        // TikTok
        if (lowerUid.includes('tiktok')) {
            flags['ad_storage'] = true;
            flags['ad_user_data'] = true;
            flags['ad_personalization'] = true;
        }
        
        // Pinterest
        if (lowerUid.includes('pinterest')) {
            flags['ad_storage'] = true;
            flags['ad_user_data'] = true;
            flags['ad_personalization'] = true;
        }
        
        // Booking.com
        if (lowerUid.includes('booking')) {
            flags['ad_storage'] = true;
            flags['ad_user_data'] = true;
            flags['ad_personalization'] = true;
        }
        
        // HubSpot
        if (lowerUid.includes('hubspot')) {
            flags['analytics_storage'] = true;
            flags['ad_storage'] = true;
            flags['ad_user_data'] = true;
            flags['ad_personalization'] = true;
        }
        
        // WhatsApp Business
        if (lowerUid.includes('whatsapp')) {
            flags['functionality_storage'] = true;
        }
    });

    debugLog('Final mapped flags', flags);
    return flags;
}

function consent_manager_showBox() {
    var consentBox = document.getElementById('consent_manager-background');
    
    // Safety check: box must exist in DOM
    if (!consentBox) {
        console.warn('Consent Manager: Consent box element not found in DOM');
        return;
    }
    
    var consents = [];
    var cookieValue = cmCookieAPI.get(cmCookieName);
    if (typeof cookieValue !== 'undefined') {
        var cookieData = safeJSONParse(cookieValue, {});
        if (cookieData.hasOwnProperty('version')) {
            consents = cookieData.consents;
        }
    }
    
    var consentsSet = new Set(consents);
    consentBox.querySelectorAll('[data-cookie-uids]').forEach(function (el) {
        var cookieUids = safeJSONParse(el.getAttribute('data-cookie-uids'), []);
        var check = cookieUids.every(function(uid) {
            return consentsSet.has(uid);
        });
        if (check) {
            el.checked = true;
        }
    });
    
    if (consent_manager_parameters.hidebodyscrollbar) {
        document.querySelector('body').style.overflow = 'hidden';
    }
    
    consentBox.classList.remove('consent_manager-hidden');
    consentBox.setAttribute('aria-hidden', 'false');
    
    // Focus the dialog wrapper for better accessibility (WCAG 2.1)
    var dialogWrapper = document.getElementById('consent_manager-wrapper');
    if (dialogWrapper) {
        setTimeout(function() {
            dialogWrapper.focus();
        }, 100);
    }
}

function consent_manager_hasconsent(id) {
    var cookieValue = cmCookieAPI.get(cmCookieName);
    if (typeof cookieValue === 'undefined') {
        return false;
    }
    var cookieData = safeJSONParse(cookieValue, {consents: []});
    return cookieData.consents.indexOf(id) !== -1;
}
