/**
 * Consent Manager Debug Helper
 * Zeigt Cookie-Daten und Consent-Status an
 */

(function() {
    'use strict';
    
    let debugPanel = null;
    let isVisible = false;
    
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
                    
                    cookies.push({
                        name: name,
                        value: value || '',
                        decodedValue: decodedValue,
                        parsedValue: parsedValue,
                        domain: window.location.hostname
                    });
                }
            });
        }
        
        return cookies;
    }
    
    // Consent Status aus Consent Manager Cookie ermitteln
    function getConsentManagerStatus() {
        const cookies = getCurrentDomainCookies();
        const consentCookie = cookies.find(cookie => cookie.name === 'consent_manager');
        
        if (!consentCookie || !consentCookie.parsedValue) {
            // Kein Cookie vorhanden - zeige Default-Status
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
        
        const consentData = consentCookie.parsedValue;
        const consentStatus = {};
        
        // Consent-Gruppen analysieren
        if (consentData.consents && Array.isArray(consentData.consents)) {
            // Bekannte Consent-Gruppen
            const knownGroups = [
                'consent_manager',
                'tags', 
                'analytics',
                'marketing',
                'preferences',
                'statistics',
                'functional',
                'necessary'
            ];
            
            knownGroups.forEach(group => {
                if (consentData.consents.includes(group)) {
                    consentStatus[group] = 'granted';
                } else {
                    consentStatus[group] = 'denied';
                }
            });
        }
        
        return {
            status: 'found',
            data: consentData,
            consents: consentStatus,
            services: getAcceptedServices(consentData),
            version: consentData.version || 'unbekannt',
            timestamp: consentData.consentTime || 'unbekannt'
        };
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

    // LocalStorage Daten abrufen
    function getLocalStorageData() {
        const items = [];
        
        try {
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                const value = localStorage.getItem(key);
                items.push({ key, value });
            }
        } catch (e) {
            items.push({ key: 'Error', value: 'LocalStorage nicht zugänglich' });
        }
        
        return items;
    }
    
    // Panel Inhalt aktualisieren
    function updatePanelContent() {
        if (!debugPanel) return;
        
        // Consent Status
        const consentManagerStatus = getConsentManagerStatus();
        let statusHtml = '';
        
        if (consentManagerStatus.consents) {
            // Status-Info hinzufügen
            if (consentManagerStatus.status === 'no_consent') {
                statusHtml += '<div style="margin-bottom: 10px; padding: 8px; background: #fff3cd; border-radius: 4px; font-size: 11px; color: #856404;"><strong>ℹ️ Default-Status:</strong> Noch kein Consent erteilt</div>';
            }
            
            statusHtml += Object.entries(consentManagerStatus.consents)
                .map(([group, status]) => {
                    const statusClass = status === 'granted' ? 'consent-granted' : 
                                       status === 'denied' ? 'consent-denied' : 'consent-unknown';
                    return `<div class="consent-status-item ${statusClass}">
                        <span>${group}</span>
                        <span>${status.toUpperCase()}</span>
                    </div>`;
                }).join('');
            
            // Zusätzliche Infos
            statusHtml += `<div style="margin-top: 10px; font-size: 10px; color: #6c757d;">
                <div><strong>Version:</strong> ${consentManagerStatus.version}</div>
                <div><strong>Zeitstempel:</strong> ${consentManagerStatus.timestamp}</div>
            </div>`;
        } else {
            statusHtml = `<div class="no-data">${consentManagerStatus.message}</div>`;
        }
        
        document.getElementById('consent-status-content').innerHTML = statusHtml;
        
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
                        <div class="service-name">${service.name}</div>
                        <div class="service-group">${service.group}</div>
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
                
                // Wenn JSON-Daten vorhanden, formatiert anzeigen
                if (cookie.parsedValue) {
                    displayValue = `<div class="cookie-value">
                        <div><strong>Raw:</strong> ${cookie.value.length > 50 ? cookie.value.substring(0, 50) + '...' : cookie.value}</div>
                        <div><strong>Dekodiert & Formatiert:</strong></div>
                        <pre class="json-preview">${JSON.stringify(cookie.parsedValue, null, 2)}</pre>
                    </div>`;
                } else if (cookie.decodedValue !== cookie.value) {
                    // URL-dekodiert aber kein JSON
                    displayValue = `<div class="cookie-value">
                        <div><strong>Raw:</strong> ${cookie.value.length > 50 ? cookie.value.substring(0, 50) + '...' : cookie.value}</div>
                        <div><strong>Dekodiert:</strong> ${cookie.decodedValue}</div>
                    </div>`;
                } else {
                    // Normale Cookie-Werte
                    const shortValue = cookie.value.length > 80 
                        ? cookie.value.substring(0, 80) + '...' 
                        : cookie.value;
                    displayValue = `<div class="cookie-value">${shortValue || '<em>leer</em>'}</div>`;
                }
                
                return `<div class="cookie-item">
                    <div class="cookie-name">${cookie.name}</div>
                    ${displayValue}
                </div>`;
              }).join('')
            : '<div class="no-data">Keine Cookies gefunden</div>';
        
        document.getElementById('cookies-content').innerHTML = cookiesHtml;
        
        // LocalStorage
        const localStorageData = getLocalStorageData();
        const storageHtml = localStorageData.length > 0
            ? localStorageData.map(item => {
                let displayValue = item.value;
                
                // JSON formatieren falls möglich
                try {
                    const parsed = JSON.parse(item.value);
                    displayValue = `<pre class="json-preview">${JSON.stringify(parsed, null, 2)}</pre>`;
                } catch (e) {
                    // Lange Strings kürzen
                    if (item.value.length > 100) {
                        displayValue = item.value.substring(0, 100) + '...';
                    }
                }
                
                return `<div class="storage-item">
                    <div class="storage-key">${item.key}</div>
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
        console.log('🔍 Consent Debug Helper geladen');
        console.log('Erstelle Debug-Button...');
        createDebugButton();
        console.log('Debug-Button erstellt! Verwende auch: showConsentDebug(), hideConsentDebug()');
    }
    
})();
