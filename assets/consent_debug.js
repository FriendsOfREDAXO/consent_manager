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
