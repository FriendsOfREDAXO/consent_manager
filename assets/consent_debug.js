/**
 * Consent Manager Debug Helper
 * Zeigt Cookie-Daten und Consent-Status an
 */

(function() {
    'use strict';
    
    let debugPanel = null;
    let isVisible = false;
    
    /**
     * HTML-Escape für sichere Ausgabe von User-Daten
     * Verhindert XSS bei manipulierten Cookies/LocalStorage
     */
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    
    // Panel HTML erstellen
    function createDebugPanel() {
        const panel = document.createElement('div');
        panel.id = 'consent-debug-panel';
        panel.innerHTML = `
            <div id="consent-debug-header">
                <span>🔍 Consent Debug</span>
                <div id="consent-debug-controls">
                    <button id="consent-refresh-btn" title="Aktualisieren">🔄</button>
                    <button id="consent-close-btn" title="Schließen">✕</button>
                </div>
            </div>
            <div id="consent-debug-content">
                <div id="issues-section">
                    <h4>⚠️ Probleme & Warnungen</h4>
                    <div id="issues-content">Lade...</div>
                </div>
                
                <div id="consent-status-section">
                    <h4>✅ Consent Status</h4>
                    <div id="consent-status-content">Lade...</div>
                </div>
                
                <div id="services-section">
                    <h4>🔧 Services</h4>
                    <div id="services-content">Lade...</div>
                </div>
                
                <div id="cookies-section">
                    <h4>🍪 Cookies</h4>
                    <div id="cookies-content">Lade...</div>
                </div>
                
                <div id="localstorage-section">
                    <h4>💾 LocalStorage</h4>
                    <div id="localstorage-content">Lade...</div>
                </div>
            </div>
        `;
        
        // Styling hinzufügen
        const style = document.createElement('style');
        style.textContent = `
            #consent-debug-panel {
                position: fixed;
                top: 20px;
                left: 20px;
                width: 400px;
                max-height: 600px;
                background: #fff;
                border: 2px solid #007bff;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                z-index: 9999999;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
                font-size: 13px;
                line-height: 1.4;
            }
            
            #consent-debug-header {
                background: #007bff;
                color: white;
                padding: 10px 15px;
                border-radius: 6px 6px 0 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
                cursor: move;
                font-weight: bold;
            }
            
            #consent-debug-controls button {
                background: rgba(255,255,255,0.2);
                border: 1px solid rgba(255,255,255,0.3);
                color: white;
                padding: 2px 6px;
                margin-left: 4px;
                border-radius: 3px;
                cursor: pointer;
                font-size: 11px;
            }
            
            #consent-debug-controls button:hover {
                background: rgba(255,255,255,0.3);
            }
            
            #consent-debug-content {
                padding: 15px;
                max-height: 500px;
                overflow-y: auto;
            }
            
            #consent-debug-content h4 {
                margin: 0 0 8px 0;
                padding: 8px 0 4px 0;
                border-bottom: 1px solid #eee;
                color: #333;
                font-size: 14px;
            }
            
            .cookie-item, .storage-item {
                padding: 8px 10px;
                margin: 4px 0;
                background: #f8f9fa;
                border-radius: 6px;
                border-left: 3px solid #007bff;
                font-family: monospace;
                font-size: 11px;
            }
            
            .consent-status-item {
                display: flex;
                justify-content: space-between;
                padding: 6px 10px;
                margin: 2px 0;
                border-radius: 4px;
                font-family: monospace;
                font-size: 11px;
                font-weight: bold;
            }
            
            .consent-granted {
                background: #d4edda;
                color: #155724;
                border-left: 3px solid #28a745;
            }
            
            .consent-denied {
                background: #f8d7da;
                color: #721c24;
                border-left: 3px solid #dc3545;
            }
            
            .consent-unknown {
                background: #fff3cd;
                color: #856404;
                border-left: 3px solid #ffc107;
            }
            
            .service-item {
                padding: 8px 12px;
                margin: 4px 0;
                background: #e8f5e8;
                border-radius: 6px;
                border-left: 3px solid #28a745;
                font-size: 12px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .service-name {
                font-weight: bold;
                color: #155724;
            }
            
            .service-group {
                font-size: 10px;
                color: #6c757d;
                background: #f8f9fa;
                padding: 2px 6px;
                border-radius: 10px;
            }
            
            .service-disabled {
                background: #f8d7da;
                border-left-color: #dc3545;
            }
            
            .service-disabled .service-name {
                color: #721c24;
            }
            
            .cookie-name, .storage-key {
                font-weight: bold;
                color: #0056b3;
                margin-bottom: 4px;
                font-size: 12px;
            }
            
            .cookie-value, .storage-value {
                color: #6c757d;
                word-break: break-word;
                line-height: 1.3;
            }
            
            .json-preview {
                background: #f1f3f4;
                padding: 8px;
                border-radius: 4px;
                font-size: 10px;
                margin: 0;
                max-height: 150px;
                overflow-y: auto;
                white-space: pre-wrap;
            }
            
            .no-data {
                padding: 15px;
                text-align: center;
                color: #6c757d;
                font-style: italic;
                background: #f8f9fa;
                border-radius: 6px;
            }
        `;
        
        document.head.appendChild(style);
        document.body.appendChild(panel);
        return panel;
    }
    
    // Cookies der aktuellen Domain abrufen
    function getCurrentDomainCookies() {
        const cookies = [];
        const cookieString = document.cookie;
        
        if (cookieString) {
            cookieString.split(';').forEach(cookie => {
                const [name, value] = cookie.trim().split('=');
                if (name) {
                    let decodedValue = value || '';
                    let parsedValue = null;
                    
                    // URL-Dekodierung versuchen
                    try {
                        decodedValue = decodeURIComponent(value || '');
                    } catch (e) {
                        decodedValue = value || '';
                    }
                    
                    // JSON-Parsing versuchen
                    try {
                        parsedValue = JSON.parse(decodedValue);
                    } catch (e) {
                        parsedValue = null;
                    }
                    
                    // Cookie-Größe berechnen
                    const sizeInBytes = new Blob([cookie]).size;
                    
                    // Cookie-Attribute extrahieren (aus parsedValue falls Consent-Cookie)
                    let consentTime = null;
                    let consentAge = null;
                    if (parsedValue && parsedValue.consentTime) {
                        consentTime = parsedValue.consentTime;
                        consentAge = Date.now() - consentTime;
                    }
                    
                    cookies.push({
                        name: name,
                        value: value || '',
                        decodedValue: decodedValue,
                        parsedValue: parsedValue,
                        domain: window.location.hostname,
                        size: sizeInBytes,
                        consentTime: consentTime,
                        consentAge: consentAge
                    });
                }
            });
        }
        
        return cookies;
    }
    
    // LocalStorage Daten abrufen
    function getLocalStorageData() {
        const storageData = [];
        
        try {
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                const value = localStorage.getItem(key);
                
                storageData.push({
                    key: key,
                    value: value
                });
            }
        } catch (e) {
            // localStorage nicht verfügbar
            console.warn('LocalStorage nicht verfügbar:', e);
        }
        
        return storageData;
    }
    
    // Google Consent Mode Status aus der Domain-Konfiguration ermitteln
    function getGoogleConsentModeStatus() {
        // Erste Priorität: Eingebettete Debug-Konfiguration (direkt von PHP)
        if (window.consentManagerDebugConfig) {
            const config = window.consentManagerDebugConfig;
            return {
                mode: config.mode || 'disabled',
                autoMapping: config.auto_mapping || false,
                debugEnabled: config.debug_enabled || false,
                domain: config.domain || window.location.hostname,
                cacheLogId: config.cache_log_id || null,
                version: config.version || null,
                status: 'configured'
            };
        }
        
        // Zweite Priorität: Google Consent Mode JavaScript verfügbar
        if (window.consentManagerGoogleConsentMode && 
            window.consentManagerGoogleConsentMode.getDomainConfig) {
            const config = window.consentManagerGoogleConsentMode.getDomainConfig();
            if (config && config.mode) {
                return {
                    mode: config.mode,
                    autoMapping: config.auto_mapping || false,
                    debugEnabled: false,
                    domain: config.domain || window.location.hostname,
                    cacheLogId: null,
                    version: null,
                    status: 'configured'
                };
            }
        }
        
        // Fallback: Prüfen ob Google Consent Mode Skript vorhanden ist
        const hasGoogleConsentMode = !!document.querySelector('script[src*="google-consent-mode"]') ||
                                    !!window.gtag ||
                                    !!window.dataLayer;
        
        if (hasGoogleConsentMode) {
            return {
                mode: 'unknown',
                autoMapping: false,
                debugEnabled: false,
                domain: window.location.hostname,
                cacheLogId: null,
                version: null,
                status: 'detected'
            };
        }
        
        return {
            mode: 'disabled',
            autoMapping: false,
            debugEnabled: false,
            domain: window.location.hostname,
            cacheLogId: null,
            version: null,
            status: 'disabled'
        };
    }

    // Consent Status aus Consent Manager Cookie ermitteln
    function getConsentManagerStatus() {
        const cookies = getCurrentDomainCookies();
        // Hole konfigurierten Cookie-Namen (aus Backend oder Default)
        const cookieName = (typeof consent_manager_parameters !== 'undefined' && consent_manager_parameters.cookieName) 
            ? consent_manager_parameters.cookieName 
            : 'consentmanager';
        const consentCookie = cookies.find(cookie => cookie.name === cookieName);
        
        // Google Consent Mode aus Runtime-Daten laden (kein localStorage mehr)
        let googleConsentMode = null;
        if (window.GoogleConsentModeV2 && window.GoogleConsentModeV2.getCurrentSettings) {
            googleConsentMode = window.GoogleConsentModeV2.getCurrentSettings();
        } else if (window.currentConsentSettings) {
            // Fallback: direkt auf currentConsentSettings zugreifen
            googleConsentMode = window.currentConsentSettings;
        }
        
        // Wenn Google Consent Mode vorhanden ist, verwende das als primäre Quelle
        if (googleConsentMode) {
            // Consent Manager Cookie-Daten hinzufügen falls vorhanden
            let combinedConsents = { ...googleConsentMode };
            
            if (consentCookie && consentCookie.parsedValue && consentCookie.parsedValue.consents) {
                const consentData = consentCookie.parsedValue;
                
                // Nur die wirklich akzeptierten Consent Manager Gruppen hinzufügen
                if (Array.isArray(consentData.consents)) {
                    consentData.consents.forEach(consentGroup => {
                        combinedConsents[consentGroup] = 'granted';
                    });
                }
                
                return {
                    status: 'combined',
                    data: consentData,
                    consents: combinedConsents,
                    services: getServicesFromCombinedConsent(combinedConsents, consentData),
                    version: `Consent Manager v${consentData.version || 'unbekannt'} + Google Consent Mode v2`,
                    timestamp: consentData.consentTime || 'localStorage + Cookie',
                    googleConsentMode: googleConsentMode
                };
            }
            
            // Nur Google Consent Mode
            return {
                status: 'google_consent_only',
                data: null,
                consents: googleConsentMode,
                services: getServicesFromGoogleConsent(googleConsentMode),
                message: 'Google Consent Mode aktiv',
                version: 'Google Consent Mode v2',
                timestamp: 'localStorage',
                googleConsentMode: googleConsentMode
            };
        }
        
        // Nur Consent Manager Cookie vorhanden
        if (consentCookie && consentCookie.parsedValue) {
            const consentData = consentCookie.parsedValue;
            const consentStatus = {};
            
            // Nur die akzeptierten Consent-Gruppen anzeigen
            if (consentData.consents && Array.isArray(consentData.consents)) {
                consentData.consents.forEach(consentGroup => {
                    consentStatus[consentGroup] = 'granted';
                });
            }
            
            return {
                status: 'consent_manager_only',
                data: consentData,
                consents: consentStatus,
                services: getAcceptedServices(consentData),
                version: consentData.version || 'unbekannt',
                timestamp: consentData.consentTime || 'unbekannt'
            };
        }
        
        // Gar kein Consent vorhanden
        return {
            status: 'no_consent',
            data: null,
            consents: getDefaultConsentStatus(),
            services: getDefaultServices(),
            message: 'Noch kein Consent erteilt - Default-Werte angezeigt',
            version: 'unbekannt',
            timestamp: 'noch nicht gesetzt'
        };
    }
    
    // Services aus kombiniertem Consent ermitteln
    function getServicesFromCombinedConsent(combinedConsents, consentData) {
        const services = [];
        
        // Google Consent Mode Services
        if (combinedConsents.analytics_storage === 'granted') {
            services.push({ name: 'Google Analytics', group: 'Analytics', enabled: true, consentGroup: 'analytics_storage' });
        }
        if (combinedConsents.ad_storage === 'granted') {
            services.push({ name: 'Google Ads Storage', group: 'Marketing', enabled: true, consentGroup: 'ad_storage' });
        }
        if (combinedConsents.ad_user_data === 'granted') {
            services.push({ name: 'Google Ads User Data', group: 'Marketing', enabled: true, consentGroup: 'ad_user_data' });
        }
        if (combinedConsents.ad_personalization === 'granted') {
            services.push({ name: 'Google Ads Personalisierung', group: 'Marketing', enabled: true, consentGroup: 'ad_personalization' });
        }
        if (combinedConsents.personalization_storage === 'granted') {
            services.push({ name: 'Personalisierung', group: 'Präferenzen', enabled: true, consentGroup: 'personalization_storage' });
        }
        if (combinedConsents.functionality_storage === 'granted') {
            services.push({ name: 'Funktionalität', group: 'Funktional', enabled: true, consentGroup: 'functionality_storage' });
        }
        if (combinedConsents.security_storage === 'granted') {
            services.push({ name: 'Sicherheit', group: 'Notwendig', enabled: true, consentGroup: 'security_storage' });
        }
        
        // Consent Manager Services
        if (combinedConsents.tags === 'granted') {
            services.push({ name: 'Tag Manager', group: 'Marketing', enabled: true, consentGroup: 'tags' });
        }
        if (combinedConsents.analytics === 'granted') {
            services.push({ name: 'Analytics Tracking', group: 'Analytics', enabled: true, consentGroup: 'analytics' });
        }
        if (combinedConsents.marketing === 'granted') {
            services.push({ name: 'Marketing Tools', group: 'Marketing', enabled: true, consentGroup: 'marketing' });
        }
        if (combinedConsents.functional === 'granted') {
            services.push({ name: 'Funktionale Cookies', group: 'Funktional', enabled: true, consentGroup: 'functional' });
        }
        
        // Immer aktiv
        services.push({ name: 'Consent Manager', group: 'Notwendig', enabled: true, consentGroup: 'necessary' });
        
        return services;
    }

    // Services aus Google Consent Mode ermitteln
    function getServicesFromGoogleConsent(googleConsentMode) {
        const services = [];
        
        // Google Consent Mode Mappings
        if (googleConsentMode.analytics_storage === 'granted') {
            services.push({ name: 'Google Analytics', group: 'Analytics', enabled: true, consentGroup: 'analytics_storage' });
        }
        if (googleConsentMode.ad_storage === 'granted') {
            services.push({ name: 'Google Ads Storage', group: 'Marketing', enabled: true, consentGroup: 'ad_storage' });
        }
        if (googleConsentMode.ad_user_data === 'granted') {
            services.push({ name: 'Google Ads User Data', group: 'Marketing', enabled: true, consentGroup: 'ad_user_data' });
        }
        if (googleConsentMode.ad_personalization === 'granted') {
            services.push({ name: 'Google Ads Personalisierung', group: 'Marketing', enabled: true, consentGroup: 'ad_personalization' });
        }
        if (googleConsentMode.personalization_storage === 'granted') {
            services.push({ name: 'Personalisierung', group: 'Präferenzen', enabled: true, consentGroup: 'personalization_storage' });
        }
        if (googleConsentMode.functionality_storage === 'granted') {
            services.push({ name: 'Funktionalität', group: 'Funktional', enabled: true, consentGroup: 'functionality_storage' });
        }
        if (googleConsentMode.security_storage === 'granted') {
            services.push({ name: 'Sicherheit', group: 'Notwendig', enabled: true, consentGroup: 'security_storage' });
        }
        
        // Immer aktiv
        services.push({ name: 'Consent Manager', group: 'Notwendig', enabled: true, consentGroup: 'necessary' });
        
        return services;
    }
    
    // Default Consent Status (vor User-Entscheidung)
    function getDefaultConsentStatus() {
        return {
            'consent_manager': 'granted', // Immer aktiv
            'necessary': 'granted',       // Technisch notwendig
            'tags': 'denied',            // Default: nicht akzeptiert
            'analytics': 'denied',       // Default: nicht akzeptiert
            'marketing': 'denied',       // Default: nicht akzeptiert
            'preferences': 'denied',     // Default: nicht akzeptiert
            'functional': 'denied'       // Default: nicht akzeptiert
        };
    }
    
    // Default Services (vor User-Entscheidung)
    function getDefaultServices() {
        return [
            // Immer aktiv
            { name: 'Consent Manager', group: 'Notwendig', enabled: true, consentGroup: 'necessary' },
            { name: 'Session Cookies', group: 'Notwendig', enabled: true, consentGroup: 'necessary' },
            
            // Standardmäßig deaktiviert
            { name: 'Google Analytics', group: 'Analytics', enabled: false, consentGroup: 'analytics' },
            { name: 'Google Tag Manager', group: 'Marketing', enabled: false, consentGroup: 'tags' },
            { name: 'Facebook Pixel', group: 'Marketing', enabled: false, consentGroup: 'marketing' },
            { name: 'Matomo Analytics', group: 'Analytics', enabled: false, consentGroup: 'analytics' },
            { name: 'Chat Widget', group: 'Funktional', enabled: false, consentGroup: 'functional' }
        ];
    }
    
    // Akzeptierte Services aus Consent-Daten ermitteln
    function getAcceptedServices(consentData) {
        const services = [];
        
        // Bekannte Service-Mappings
        const serviceMap = {
            'consent_manager': [
                { name: 'Consent Manager', group: 'Notwendig', enabled: true }
            ],
            'tags': [
                { name: 'Google Tag Manager', group: 'Marketing', enabled: false },
                { name: 'Google Analytics', group: 'Analytics', enabled: false },
                { name: 'Facebook Pixel', group: 'Marketing', enabled: false }
            ],
            'analytics': [
                { name: 'Google Analytics', group: 'Analytics', enabled: false },
                { name: 'Matomo Analytics', group: 'Analytics', enabled: false }
            ],
            'marketing': [
                { name: 'Google Ads', group: 'Marketing', enabled: false },
                { name: 'Facebook Ads', group: 'Marketing', enabled: false }
            ],
            'preferences': [
                { name: 'Benutzereinstellungen', group: 'Präferenzen', enabled: false }
            ],
            'functional': [
                { name: 'Chat Widget', group: 'Funktional', enabled: false },
                { name: 'Video Player', group: 'Funktional', enabled: false }
            ]
        };
        
        // Durch akzeptierte Consents gehen
        if (consentData.consents && Array.isArray(consentData.consents)) {
            consentData.consents.forEach(consentGroup => {
                if (serviceMap[consentGroup]) {
                    serviceMap[consentGroup].forEach(service => {
                        services.push({
                            ...service,
                            enabled: true,
                            consentGroup: consentGroup
                        });
                    });
                }
            });
        }
        
        // Auch nicht-akzeptierte Services anzeigen (als disabled)
        Object.keys(serviceMap).forEach(group => {
            if (!consentData.consents || !consentData.consents.includes(group)) {
                serviceMap[group].forEach(service => {
                    services.push({
                        ...service,
                        enabled: false,
                        consentGroup: group
                    });
                });
            }
        });
        
        return services;
    }

    // Probleme erkennen und Warnungen generieren
    function detectIssues() {
        const issues = [];
        const googleConsentModeStatus = getGoogleConsentModeStatus();
        const consentManagerStatus = getConsentManagerStatus();
        
        // Cookie-Namen einmal zentral ermitteln
        const cookieName = (typeof consent_manager_parameters !== 'undefined' && consent_manager_parameters.cookieName) 
            ? consent_manager_parameters.cookieName 
            : 'consentmanager';
        const cookies = getCurrentDomainCookies();
        const consentCookie = cookies.find(c => c.name === cookieName);
        
        // Problem 1: Google Consent Mode aktiviert aber Script nicht geladen
        if (googleConsentModeStatus.mode !== 'disabled' && !window.GoogleConsentModeV2) {
            issues.push({
                type: 'error',
                title: 'Google Consent Mode Script fehlt',
                message: 'Google Consent Mode ist aktiviert, aber das erforderliche Script wurde nicht geladen.',
                solution: 'Überprüfen Sie, ob das google_consent_mode_v2.min.js Script korrekt eingebunden ist.'
            });
        }
        
        // Problem 2: Auto-Mapping aktiviert aber keine Services konfiguriert
        if (googleConsentModeStatus.autoMapping && consentManagerStatus.services && 
            consentManagerStatus.services.filter(s => s.enabled).length === 0) {
            issues.push({
                type: 'warning',
                title: 'Auto-Mapping ohne Services',
                message: 'Auto-Mapping ist aktiviert, aber keine Services sind konfiguriert oder akzeptiert.',
                solution: 'Konfigurieren Sie Services im Backend oder prüfen Sie die Consent-Gruppen.'
            });
        }
        
        // Problem 3: Consent Manager Cookie fehlt bei erteiltem Consent
        const cookiePattern = cookieName + '=';
        if (consentManagerStatus.status !== 'no_consent' && !document.cookie.includes(cookiePattern)) {
            issues.push({
                type: 'warning',
                title: 'Consent Cookie fehlt',
                message: `Consent wurde erteilt, aber das Consent-Manager Cookie (${cookieName}) ist nicht vorhanden.`,
                solution: 'Überprüfen Sie Cookie-Einstellungen und SameSite-Attribute.'
            });
        }
        
        // Problem 4: Google Consent Mode Runtime-Daten fehlen bei aktiviertem Modus
        if (googleConsentModeStatus.mode !== 'disabled' && !window.GoogleConsentModeV2 && !window.currentConsentSettings) {
            issues.push({
                type: 'warning',
                title: 'Google Consent Mode Runtime-Daten fehlen',
                message: 'Google Consent Mode ist aktiviert, aber keine Runtime-Daten gefunden.',
                solution: 'Stellen Sie sicher, dass das google_consent_mode_v2.js Script korrekt geladen und initialisiert wurde.'
            });
        }
        
        // Problem 5: Auto-Mapping aktiviert aber keine Google Consent Mode Updates
        if (googleConsentModeStatus.autoMapping && consentManagerStatus.status !== 'no_consent') {
            let googleConsent = null;
            if (window.GoogleConsentModeV2 && window.GoogleConsentModeV2.getCurrentSettings) {
                googleConsent = window.GoogleConsentModeV2.getCurrentSettings();
            } else if (window.currentConsentSettings) {
                googleConsent = window.currentConsentSettings;
            }
            
            if (googleConsent) {
                const hasGrantedFlags = Object.values(googleConsent).some(value => value === 'granted');
                
                if (!hasGrantedFlags) {
                    issues.push({
                        type: 'warning',
                        title: 'Auto-Mapping ohne Effekt',
                        message: 'Auto-Mapping ist aktiviert und Consent erteilt, aber keine Google Consent Flags wurden auf "granted" gesetzt.',
                        solution: 'Überprüfen Sie die Service-UIDs (z.B. google-analytics) und die Mapping-Funktion.'
                    });
                }
            }
        }
        
        // Problem 6: Cookie-Größe über 4KB
        if (consentCookie && consentCookie.size > 4096) {
            issues.push({
                type: 'error',
                title: 'Cookie zu groß',
                message: `Das Consent-Cookie (${consentCookie.size} Bytes) überschreitet die Browser-Grenze von 4KB (4096 Bytes).`,
                solution: 'Reduzieren Sie die Anzahl der Services oder kürzen Sie Service-UIDs.'
            });
        }
        
        // Problem 7: Consent älter als 1 Jahr (möglicher Renewal-Bedarf)
        if (consentCookie && consentCookie.consentAge) {
            const oneYearInMs = 365 * 24 * 60 * 60 * 1000;
            if (consentCookie.consentAge > oneYearInMs) {
                const ageInDays = Math.floor(consentCookie.consentAge / (24 * 60 * 60 * 1000));
                issues.push({
                    type: 'info',
                    title: 'Alter Consent',
                    message: `Der Consent ist ${ageInDays} Tage alt. DSGVO empfiehlt regelmäßige Erneuerung.`,
                    solution: 'Erwägen Sie, den Consent nach 1 Jahr automatisch erneut abzufragen.'
                });
            }
        }
        
        // Problem 8: Mehrere Consent Manager Frontend Scripts geladen
        const consentManagerScripts = document.querySelectorAll('script[src*="consent_manager_frontend"]');
        // Zähle .js und .min.js als ein Script
        const uniqueScripts = new Set();
        consentManagerScripts.forEach(script => {
            const baseName = script.src.replace(/\.min\.js$/, '.js');
            uniqueScripts.add(baseName);
        });
        if (uniqueScripts.size > 1) {
            issues.push({
                type: 'warning',
                title: 'Mehrere Consent Manager Scripts',
                message: `${uniqueScripts.size} verschiedene Consent-Manager Frontend Scripts wurden geladen. Dies kann zu Konflikten führen.`,
                solution: 'Überprüfen Sie die Template-Integration und entfernen Sie doppelte Einbindungen.'
            });
        }
        
        // Problem 9: Duplicate External Scripts (Google Analytics, GTM, etc.)
        const externalScripts = {
            'Google Analytics': document.querySelectorAll('script[src*="google-analytics.com/analytics.js"], script[src*="googletagmanager.com/gtag/js"]'),
            'Google Tag Manager': document.querySelectorAll('script[src*="googletagmanager.com/gtm.js"]'),
            'Facebook Pixel': document.querySelectorAll('script[src*="connect.facebook.net"]'),
            'Matomo': document.querySelectorAll('script[src*="matomo.js"], script[src*="piwik.js"]')
        };
        
        Object.keys(externalScripts).forEach(scriptName => {
            const scripts = externalScripts[scriptName];
            if (scripts.length > 1) {
                issues.push({
                    type: 'warning',
                    title: `Duplikat: ${scriptName}`,
                    message: `${scriptName} wurde ${scripts.length}x geladen. Dies kann zu doppelten Tracking-Events führen.`,
                    solution: `Überprüfen Sie Ihre Service-Konfiguration und entfernen Sie doppelte ${scriptName} Einbindungen.`
                });
            }
        });
        
        return issues;
    }
    
    // Panel Inhalt aktualisieren
    function updatePanelContent() {
        if (!debugPanel) return;
        
        // Probleme erkennen
        const issues = detectIssues();
        let issuesHtml = '';
        
        if (issues.length > 0) {
            issuesHtml = issues.map(issue => {
                const icon = issue.type === 'error' ? '❌' : 
                           issue.type === 'warning' ? '⚠️' : 'ℹ️';
                const bgColor = issue.type === 'error' ? '#f8d7da' : 
                              issue.type === 'warning' ? '#fff3cd' : '#d1ecf1';
                const borderColor = issue.type === 'error' ? '#dc3545' : 
                                  issue.type === 'warning' ? '#ffc107' : '#17a2b8';
                const textColor = issue.type === 'error' ? '#721c24' : 
                                issue.type === 'warning' ? '#856404' : '#0c5460';
                
                return `<div style="margin: 8px 0; padding: 12px; background: ${bgColor}; border-left: 4px solid ${borderColor}; border-radius: 6px; font-size: 12px;">
                    <div style="font-weight: bold; margin-bottom: 6px; color: ${textColor};">${icon} ${escapeHtml(issue.title)}</div>
                    <div style="margin-bottom: 6px; color: ${textColor};">${escapeHtml(issue.message)}</div>
                    <div style="font-size: 11px; color: #6c757d;"><strong>Lösung:</strong> ${escapeHtml(issue.solution)}</div>
                </div>`;
            }).join('');
        } else {
            issuesHtml = '<div style="padding: 15px; text-align: center; color: #28a745; font-style: italic; background: #d4edda; border-radius: 6px;">✅ Keine Probleme erkannt</div>';
        }
        
        document.getElementById('issues-content').innerHTML = issuesHtml;
        
        // Google Consent Mode Status anzeigen
        const googleConsentModeStatus = getGoogleConsentModeStatus();
        let googleConsentModeHtml = '';
        
        if (googleConsentModeStatus.status === 'configured') {
            const modeLabels = {
                'disabled': 'Deaktiviert',
                'auto': 'Automatisch (Auto-Mapping)',
                'manual': 'Manuell'
            };
            const modeLabel = modeLabels[googleConsentModeStatus.mode] || googleConsentModeStatus.mode;
            const modeIcon = googleConsentModeStatus.mode === 'disabled' ? '❌' : 
                            googleConsentModeStatus.mode === 'auto' ? '🔄' : '⚙️';
            
            googleConsentModeHtml = `<div style="margin-bottom: 15px; padding: 10px; background: #e8f4fd; border-left: 4px solid #007bff; border-radius: 4px;">
                <h4 style="margin: 0 0 8px 0; font-size: 12px; color: #007bff;">🎯 Google Consent Mode v2 Status</h4>
                <div style="font-size: 11px; color: #495057;">
                    <div><strong>Modus:</strong> <span style="color: #007bff;">${modeIcon} ${modeLabel}</span></div>
                    <div><strong>Domain:</strong> ${googleConsentModeStatus.domain}</div>
                    ${googleConsentModeStatus.autoMapping ? '<div><strong>Auto-Mapping:</strong> <span style="color: #28a745;">✅ Aktiv</span></div>' : '<div><strong>Auto-Mapping:</strong> <span style="color: #6c757d;">❌ Inaktiv</span></div>'}
                    ${googleConsentModeStatus.cacheLogId ? '<div><strong>Cache Log ID:</strong> ' + googleConsentModeStatus.cacheLogId + '</div>' : ''}
                    ${googleConsentModeStatus.version ? '<div><strong>Version:</strong> ' + googleConsentModeStatus.version + '</div>' : ''}
                </div>
            </div>`;
        } else if (googleConsentModeStatus.status === 'detected') {
            googleConsentModeHtml = `<div style="margin-bottom: 15px; padding: 10px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                <h4 style="margin: 0 0 8px 0; font-size: 12px; color: #856404;">🎯 Google Consent Mode v2 Status</h4>
                <div style="font-size: 11px; color: #856404;">
                    <div><strong>Status:</strong> ⚠️ Erkannt aber nicht konfiguriert</div>
                    <div><strong>Domain:</strong> ${googleConsentModeStatus.domain}</div>
                </div>
            </div>`;
        } else {
            googleConsentModeHtml = `<div style="margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-left: 4px solid #6c757d; border-radius: 4px;">
                <h4 style="margin: 0 0 8px 0; font-size: 12px; color: #6c757d;">🎯 Google Consent Mode v2 Status</h4>
                <div style="font-size: 11px; color: #6c757d;">
                    <div><strong>Status:</strong> ❌ Deaktiviert</div>
                    <div><strong>Domain:</strong> ${googleConsentModeStatus.domain}</div>
                </div>
            </div>`;
        }
        
        // Consent Status
        const consentManagerStatus = getConsentManagerStatus();
        let statusHtml = '';
        
        if (consentManagerStatus.consents) {
            // Status-Info hinzufügen
            if (consentManagerStatus.status === 'no_consent') {
                statusHtml += '<div style="margin-bottom: 10px; padding: 8px; background: #fff3cd; border-radius: 4px; font-size: 11px; color: #856404;"><strong>ℹ️ Default-Status:</strong> Noch kein Consent erteilt</div>';
            } else if (consentManagerStatus.status === 'google_consent_only') {
                statusHtml += '<div style="margin-bottom: 10px; padding: 8px; background: #d4edda; border-radius: 4px; font-size: 11px; color: #155724;"><strong>🔄 Google Consent Mode:</strong> Nur Google Consent Mode aktiv</div>';
            } else if (consentManagerStatus.status === 'combined') {
                statusHtml += '<div style="margin-bottom: 10px; padding: 8px; background: #d1ecf1; border-radius: 4px; font-size: 11px; color: #0c5460;"><strong>🔄 Kombiniert:</strong> Consent Manager + Google Consent Mode</div>';
            } else if (consentManagerStatus.status === 'consent_manager_only') {
                statusHtml += '<div style="margin-bottom: 10px; padding: 8px; background: #e2e3e5; border-radius: 4px; font-size: 11px; color: #383d41;"><strong>📋 Consent Manager:</strong> Nur Consent Manager aktiv</div>';
            }
            
            statusHtml += Object.entries(consentManagerStatus.consents)
                .map(([group, status]) => {
                    const statusClass = status === 'granted' ? 'consent-granted' : 
                                       status === 'denied' ? 'consent-denied' : 'consent-unknown';
                    
                    // Google Consent Mode Felder speziell markieren
                    const isGoogleConsentField = ['ad_storage', 'ad_user_data', 'ad_personalization', 'analytics_storage', 'personalization_storage', 'functionality_storage', 'security_storage'].includes(group);
                    const prefix = isGoogleConsentField ? '🔄 ' : '';
                    
                    return `<div class="consent-status-item ${statusClass}">
                        <span>${prefix}${group}</span>
                        <span>${status.toUpperCase()}</span>
                    </div>`;
                }).join('');
            
            // Zusätzliche Infos
            statusHtml += `<div style="margin-top: 10px; font-size: 10px; color: #6c757d;">
                <div><strong>Version:</strong> ${consentManagerStatus.version}</div>
                <div><strong>Zeitstempel:</strong> ${consentManagerStatus.timestamp}</div>`;
            
            if (consentManagerStatus.googleConsentMode) {
                statusHtml += `<div><strong>Google Consent Mode:</strong> Aktiv (${Object.keys(consentManagerStatus.googleConsentMode).length} Felder)</div>`;
            }
            
            statusHtml += '</div>';
        } else {
            statusHtml = `<div class="no-data">${consentManagerStatus.message}</div>`;
        }
        
        document.getElementById('consent-status-content').innerHTML = googleConsentModeHtml + statusHtml;
        
        // Services anzeigen - nur die mit Consent
        let servicesHtml = '';
        if (consentManagerStatus.services) {
            const activeServices = consentManagerStatus.services.filter(s => s.enabled);
            
            // Status-Info hinzufügen
            if (consentManagerStatus.status === 'no_consent') {
                servicesHtml += '<div style="margin-bottom: 10px; padding: 8px; background: #fff3cd; border-radius: 4px; font-size: 11px; color: #856404;"><strong>ℹ️ Default-Status:</strong> Noch kein Consent erteilt</div>';
            }
            
            // Nur aktivierte Services zeigen
            if (activeServices.length > 0) {
                servicesHtml += activeServices.map(service => `
                    <div class="service-item">
                        <div class="service-name">${escapeHtml(service.name)}</div>
                        <div class="service-group">${escapeHtml(service.group)}</div>
                    </div>
                `).join('');
            } else {
                servicesHtml = '<div class="no-data">Keine Services aktiviert</div>';
            }
        } else {
            servicesHtml = '<div class="no-data">Services können nicht ermittelt werden</div>';
        }
        
        document.getElementById('services-content').innerHTML = servicesHtml;
        
        // Cookies
        const cookies = getCurrentDomainCookies();
        const cookiesHtml = cookies.length > 0 
            ? cookies.map(cookie => {
                let displayValue = '';
                
                // Cookie-Attribute (Größe, Alter etc.)
                const cookieName = (typeof consent_manager_parameters !== 'undefined' && consent_manager_parameters.cookieName) 
                    ? consent_manager_parameters.cookieName 
                    : 'consentmanager';
                const isConsentCookie = cookie.name === cookieName;
                
                let attributesHtml = `<div style="font-size: 10px; color: #6c757d; margin-top: 4px;">`;
                attributesHtml += `<strong>Größe:</strong> ${cookie.size} Bytes`;
                
                if (cookie.size > 4096) {
                    attributesHtml += ` <span style="color: #dc3545; font-weight: bold;">⚠️ Zu groß!</span>`;
                } else if (cookie.size > 3500) {
                    attributesHtml += ` <span style="color: #ffc107;">⚠️ Fast am Limit</span>`;
                }
                
                if (isConsentCookie && cookie.consentAge) {
                    const ageInDays = Math.floor(cookie.consentAge / (24 * 60 * 60 * 1000));
                    const ageInHours = Math.floor(cookie.consentAge / (60 * 60 * 1000));
                    const ageDisplay = ageInDays > 0 ? `${ageInDays} Tage` : `${ageInHours} Stunden`;
                    attributesHtml += ` | <strong>Alter:</strong> ${ageDisplay}`;
                    
                    if (ageInDays > 365) {
                        attributesHtml += ` <span style="color: #ffc107;">⚠️ > 1 Jahr</span>`;
                    }
                }
                
                // Cookie-Attribute aus consent_manager_parameters
                if (isConsentCookie && typeof consent_manager_parameters !== 'undefined') {
                    const sameSite = consent_manager_parameters.cookieSameSite || 'Lax';
                    const secure = consent_manager_parameters.cookieSecure ? 'Yes' : 'No';
                    attributesHtml += `<br><strong>SameSite:</strong> ${escapeHtml(sameSite)} | <strong>Secure:</strong> ${secure} | <strong>Path:</strong> / | <strong>Domain:</strong> ${escapeHtml(cookie.domain)}`;
                }
                
                attributesHtml += `</div>`;
                
                // Wenn JSON-Daten vorhanden, formatiert anzeigen
                if (cookie.parsedValue) {
                    displayValue = `<div class="cookie-value">
                        <div><strong>Raw:</strong> ${escapeHtml(cookie.value.length > 50 ? cookie.value.substring(0, 50) + '...' : cookie.value)}</div>
                        <div><strong>Dekodiert & Formatiert:</strong></div>
                        <pre class="json-preview">${escapeHtml(JSON.stringify(cookie.parsedValue, null, 2))}</pre>
                    </div>`;
                } else if (cookie.decodedValue !== cookie.value) {
                    // URL-dekodiert aber kein JSON
                    displayValue = `<div class="cookie-value">
                        <div><strong>Raw:</strong> ${escapeHtml(cookie.value.length > 50 ? cookie.value.substring(0, 50) + '...' : cookie.value)}</div>
                        <div><strong>Dekodiert:</strong> ${escapeHtml(cookie.decodedValue)}</div>
                    </div>`;
                } else {
                    // Normale Cookie-Werte
                    const shortValue = cookie.value.length > 80 
                        ? cookie.value.substring(0, 80) + '...' 
                        : cookie.value;
                    displayValue = `<div class="cookie-value">${escapeHtml(shortValue) || '<em>leer</em>'}</div>`;
                }
                
                return `<div class="cookie-item">
                    <div class="cookie-name">${escapeHtml(cookie.name)}</div>
                    ${attributesHtml}
                    ${displayValue}
                </div>`;
              }).join('')
            : '<div class="no-data">Keine Cookies gefunden</div>';
        
        document.getElementById('cookies-content').innerHTML = cookiesHtml;
        
        // LocalStorage
        const localStorageData = getLocalStorageData();
        const storageHtml = localStorageData.length > 0
            ? localStorageData.map(item => {
                let displayValue = escapeHtml(item.value);
                
                // JSON formatieren falls möglich
                try {
                    const parsed = JSON.parse(item.value);
                    displayValue = `<pre class="json-preview">${escapeHtml(JSON.stringify(parsed, null, 2))}</pre>`;
                } catch (e) {
                    // Lange Strings kürzen
                    if (item.value.length > 100) {
                        displayValue = escapeHtml(item.value.substring(0, 100) + '...');
                    }
                }
                
                return `<div class="storage-item">
                    <div class="storage-key">${escapeHtml(item.key)}</div>
                    <div class="storage-value">${displayValue}</div>
                </div>`;
              }).join('')
            : '<div class="no-data">Keine LocalStorage Daten gefunden</div>';
        
        document.getElementById('localstorage-content').innerHTML = storageHtml;
    }
    
    // Event Listeners hinzufügen
    function setupEventListeners() {
        if (!debugPanel) return;
        
        // Aktualisieren
        const refreshBtn = document.getElementById('consent-refresh-btn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', updatePanelContent);
        }
        
        // Schließen
        const closeBtn = document.getElementById('consent-close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', hideDebugPanel);
        }
    }
    
    // Panel anzeigen
    function showDebugPanel() {
        if (!debugPanel) {
            debugPanel = createDebugPanel();
            setupEventListeners();
        }
        
        debugPanel.style.display = 'block';
        isVisible = true;
        updatePanelContent();
    }
    
    // Panel ausblenden
    function hideDebugPanel() {
        if (debugPanel) {
            debugPanel.style.display = 'none';
            isVisible = false;
        }
    }
    
    // Toggle Panel
    function toggleDebugPanel() {
        if (isVisible) {
            hideDebugPanel();
        } else {
            showDebugPanel();
        }
    }
    
    // Debug Button erstellen - links mittig
    function createDebugButton() {
        // Prüfen ob Button bereits existiert
        if (document.getElementById('consent-debug-toggle-btn')) return;
        
        const button = document.createElement('button');
        button.id = 'consent-debug-toggle-btn';
        button.innerHTML = 'Consent Debug';
        button.title = 'Consent Debug öffnen';
        button.style.cssText = `
            position: fixed;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            padding: 8px 12px;
            background: #28a745;
            color: white;
            border: none;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            box-shadow: 2px 0 8px rgba(0,0,0,0.15);
            z-index: 9999999;
            transition: all 0.3s ease;
            border-radius: 0 8px 8px 0;
            writing-mode: vertical-lr;
            text-orientation: mixed;
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        `;
        
        button.addEventListener('mouseenter', () => {
            button.style.background = '#218838';
            button.style.paddingLeft = '16px';
            button.style.boxShadow = '4px 0 12px rgba(0,0,0,0.2)';
        });
        
        button.addEventListener('mouseleave', () => {
            button.style.background = '#28a745';
            button.style.paddingLeft = '12px';
            button.style.boxShadow = '2px 0 8px rgba(0,0,0,0.15)';
        });
        
        button.addEventListener('click', toggleDebugPanel);
        
        document.body.appendChild(button);
        
        // Button ausblenden wenn Debug-Panel offen ist
        const updateButtonVisibility = () => {
            if (button) {
                button.style.display = isVisible ? 'none' : 'flex';
            }
        };
        
        // Observer für Panel-Sichtbarkeit
        const observer = new MutationObserver(() => {
            updateButtonVisibility();
        });
        
        // Überwache Panel-Änderungen
        setTimeout(() => {
            if (debugPanel) {
                observer.observe(debugPanel, { 
                    attributes: true, 
                    attributeFilter: ['style'] 
                });
            }
        }, 100);
    }
    
    // Globale Funktionen verfügbar machen
    window.showConsentDebug = showDebugPanel;
    window.hideConsentDebug = hideDebugPanel;
    window.toggleConsentDebug = toggleDebugPanel;
    
    // Auto-Start nach DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeDebugHelper);
    } else {
        setTimeout(initializeDebugHelper, 100);
    }
    
    function initializeDebugHelper() {
        createDebugButton();
    }
    
})();
