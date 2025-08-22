<?php

/**
 * Google Consent Mode v2 Integration for REDAXO Consent Manager
 * @api
 */
class consent_manager_google_consent_mode
{
    const CONSENT_FLAGS = [
        'ad_storage' => 'Werbung - Ermöglicht die Speicherung für werbebezogene Zwecke',
        'ad_user_data' => 'Nutzer-Daten für Werbung - Übermittlung von Nutzerdaten für Werbezwecke',
        'ad_personalization' => 'Personalisierte Werbung - Zustimmung für personalisierte Werbeanzeigen',
        'analytics_storage' => 'Statistik - Ermöglicht die Speicherung für Analysezwecke',
        'functionality_storage' => 'Funktionalität - Speicherung für Website-Funktionalität',
        'personalization_storage' => 'Personalisierung - Speicherung für Personalisierungszwecke',
        'security_storage' => 'Sicherheit - Speicherung für Sicherheitszwecke'
    ];

    /**
     * Check if Google Consent Mode v2 is enabled for current domain
     * @return bool
     */
    public static function isEnabledForDomain($domain = null): bool
    {
        if (null === $domain) {
            $domain = consent_manager_util::hostname();
        }

        $sql = rex_sql::factory();
        $sql->setQuery('SELECT google_consent_mode_enabled FROM ' . rex::getTable('consent_manager_domain') . ' WHERE uid = ?', [$domain]);
        
        if ($sql->getRows() > 0) {
            return (bool) $sql->getValue('google_consent_mode_enabled');
        }
        
        return false;
    }

    /**
     * Get domain configuration for Google Consent Mode v2
     * @param string|null $domain
     * @return array
     */
    public static function getDomainConfig($domain = null): array
    {
        if (null === $domain) {
            $domain = consent_manager_util::hostname();
        }

        $sql = rex_sql::factory();
        $sql->setQuery('SELECT google_consent_mode_enabled, google_consent_mode_default_state, google_consent_mode_debug FROM ' . rex::getTable('consent_manager_domain') . ' WHERE uid = ?', [$domain]);
        
        if ($sql->getRows() > 0) {
            return [
                'enabled' => (bool) $sql->getValue('google_consent_mode_enabled'),
                'default_state' => $sql->getValue('google_consent_mode_default_state') ?: 'denied',
                'debug' => (bool) $sql->getValue('google_consent_mode_debug')
            ];
        }
        
        return [
            'enabled' => false,
            'default_state' => 'denied',
            'debug' => false
        ];
    }

    /**
     * Get Google Consent Mode mapping for all cookies
     * @param int $clangId
     * @return array
     */
    public static function getCookieConsentMappings($clangId): array
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT uid, google_consent_mapping FROM ' . rex::getTable('consent_manager_cookie') . ' WHERE clang_id = ? AND google_consent_mapping IS NOT NULL AND google_consent_mapping != ""', [$clangId]);
        
        $mappings = [];
        for ($i = 0; $i < $sql->getRows(); ++$i) {
            $uid = $sql->getValue('uid');
            $mapping = json_decode($sql->getValue('google_consent_mapping'), true);
            if ($mapping) {
                $mappings[$uid] = $mapping;
            }
            $sql->next();
        }
        
        return $mappings;
    }

    /**
     * Generate Google Consent Mode v2 JavaScript code
     * @param string|null $domain
     * @param int $clangId
     * @return string
     */
    public static function generateJavaScript($domain = null, $clangId = 1): string
    {
        $config = self::getDomainConfig($domain);
        
        if (!$config['enabled']) {
            return '';
        }

        $mappings = self::getCookieConsentMappings($clangId);
        $defaultState = $config['default_state'];
        $debug = $config['debug'];

        $js = "/* --- Google Consent Mode v2 --- */\n";
        $js .= "window.dataLayer = window.dataLayer || [];\n";
        $js .= "function gtag(){dataLayer.push(arguments);}\n\n";

        // Configuration
        $js .= "let GOOGLE_CONSENT_V2_DEFAULT_STATE = '{$defaultState}';\n";
        $js .= "let GOOGLE_CONSENT_V2_STORAGE_KEY = 'consentMode';\n";
        $js .= "let GOOGLE_CONSENT_V2_DEBUG = " . ($debug ? 'true' : 'false') . ";\n\n";

        // Consent flags
        $flags = array_keys(self::CONSENT_FLAGS);
        $js .= "let GOOGLE_CONSENT_V2_FIELDS = " . json_encode($flags, JSON_UNESCAPED_SLASHES) . ";\n\n";

        // Cookie mappings
        $js .= "let GOOGLE_CONSENT_V2_COOKIE_MAPPINGS = " . json_encode($mappings, JSON_UNESCAPED_SLASHES) . ";\n\n";

        // Core functionality
        $js .= self::getConsentModeJavaScript();

        // Debug inspector
        if ($debug) {
            $js .= "\n" . self::getDebugInspectorJavaScript();
        }

        return $js;
    }

    /**
     * Get the core Google Consent Mode JavaScript functionality
     * @return string
     */
    private static function getConsentModeJavaScript(): string
    {
        return <<<'JAVASCRIPT'
// Initialize consent state
let consentStorage = localStorage.getItem(GOOGLE_CONSENT_V2_STORAGE_KEY);

if (consentStorage === null) {
    // Initialize with default settings
    let defaultSettings = {};
    GOOGLE_CONSENT_V2_FIELDS.forEach(function(field) {
        defaultSettings[field] = GOOGLE_CONSENT_V2_DEFAULT_STATE;
    });
    
    // Always allow necessary storage
    defaultSettings['functionality_storage'] = 'granted';
    defaultSettings['personalization_storage'] = 'granted'; 
    defaultSettings['security_storage'] = 'granted';

    gtag('consent', 'default', defaultSettings);
    localStorage.setItem(GOOGLE_CONSENT_V2_STORAGE_KEY, JSON.stringify(defaultSettings));
    
    if (GOOGLE_CONSENT_V2_DEBUG) {
        console.log('Google Consent Mode v2: Initialized with defaults', defaultSettings);
    }
} else {
    // Load existing settings
    let storedSettings = JSON.parse(consentStorage);

    // Check consistency and add missing fields
    let updated = false;
    GOOGLE_CONSENT_V2_FIELDS.forEach(function(field) {
        if (typeof storedSettings[field] === 'undefined') {
            storedSettings[field] = GOOGLE_CONSENT_V2_DEFAULT_STATE;
            updated = true;
        }
    });

    // Always ensure necessary storage is granted
    if (storedSettings['functionality_storage'] !== 'granted') {
        storedSettings['functionality_storage'] = 'granted';
        updated = true;
    }
    if (storedSettings['personalization_storage'] !== 'granted') {
        storedSettings['personalization_storage'] = 'granted';
        updated = true;
    }
    if (storedSettings['security_storage'] !== 'granted') {
        storedSettings['security_storage'] = 'granted';
        updated = true;
    }

    gtag('consent', 'default', storedSettings);
    
    if (updated) {
        localStorage.setItem(GOOGLE_CONSENT_V2_STORAGE_KEY, JSON.stringify(storedSettings));
    }

    if (GOOGLE_CONSENT_V2_DEBUG) {
        console.log('Google Consent Mode v2: Loaded from storage', storedSettings);
    }
}

// Function to update consent based on consent manager decisions
function updateGoogleConsent(consentDecisions) {
    let currentSettings = JSON.parse(localStorage.getItem(GOOGLE_CONSENT_V2_STORAGE_KEY) || '{}');
    let updated = false;

    // Process each cookie mapping
    for (let cookieUid in GOOGLE_CONSENT_V2_COOKIE_MAPPINGS) {
        if (typeof consentDecisions[cookieUid] !== 'undefined') {
            let consentGiven = consentDecisions[cookieUid];
            let mapping = GOOGLE_CONSENT_V2_COOKIE_MAPPINGS[cookieUid];
            
            for (let flag in mapping) {
                if (mapping[flag] === true) {
                    let newState = consentGiven ? 'granted' : 'denied';
                    if (currentSettings[flag] !== newState) {
                        currentSettings[flag] = newState;
                        updated = true;
                        
                        if (GOOGLE_CONSENT_V2_DEBUG) {
                            console.log('Google Consent Mode v2: Updated ' + flag + ' to ' + newState + ' for cookie ' + cookieUid);
                        }
                    }
                }
            }
        }
    }

    if (updated) {
        gtag('consent', 'update', currentSettings);
        localStorage.setItem(GOOGLE_CONSENT_V2_STORAGE_KEY, JSON.stringify(currentSettings));
        
        if (GOOGLE_CONSENT_V2_DEBUG) {
            console.log('Google Consent Mode v2: Updated consent', currentSettings);
            if (window.googleConsentDebugInspector) {
                window.googleConsentDebugInspector.update();
            }
        }
    }
}

// Integration with consent manager
if (typeof window.consent_manager_integration === 'undefined') {
    window.consent_manager_integration = {};
}
window.consent_manager_integration.google_consent_mode_v2 = updateGoogleConsent;

JAVASCRIPT;
    }

    /**
     * Get the debug inspector JavaScript
     * @return string
     */
    private static function getDebugInspectorJavaScript(): string
    {
        return <<<'JAVASCRIPT'
// Debug Inspector
window.googleConsentDebugInspector = {
    isVisible: false,
    
    create: function() {
        if (document.getElementById('google-consent-debug')) return;
        
        let inspector = document.createElement('div');
        inspector.id = 'google-consent-debug';
        inspector.innerHTML = `
            <div style="position: fixed; top: 20px; right: 20px; width: 400px; background: #fff; border: 2px solid #007cba; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); z-index: 999999; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; font-size: 13px;">
                <div style="background: #007cba; color: white; padding: 12px; border-radius: 6px 6px 0 0; cursor: move; user-select: none;" id="debug-header">
                    <strong>🔍 Google Consent Mode v2 Debug</strong>
                    <span style="float: right; cursor: pointer; font-size: 16px;" onclick="window.googleConsentDebugInspector.toggle()">×</span>
                </div>
                <div style="padding: 15px; max-height: 400px; overflow-y: auto;" id="debug-content">
                    <div id="consent-status"></div>
                    <hr style="margin: 10px 0;">
                    <div id="cookie-mappings"></div>
                    <hr style="margin: 10px 0;">
                    <div id="recent-events" style="max-height: 150px; overflow-y: auto; background: #f9f9f9; padding: 8px; border-radius: 4px;">
                        <strong>Recent Events:</strong>
                        <div id="event-log"></div>
                    </div>
                    <div style="margin-top: 10px; text-align: center;">
                        <button onclick="window.googleConsentDebugInspector.clearEvents()" style="padding: 6px 12px; border: 1px solid #ddd; background: #f5f5f5; border-radius: 4px; cursor: pointer;">Clear Log</button>
                        <button onclick="window.googleConsentDebugInspector.exportData()" style="padding: 6px 12px; border: 1px solid #007cba; background: #007cba; color: white; border-radius: 4px; cursor: pointer; margin-left: 5px;">Export Data</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(inspector);
        this.makeDraggable();
        this.update();
        
        // Log initial state
        this.logEvent('Debug Inspector initialized');
        
        // Show for 5 seconds then auto-hide
        setTimeout(() => {
            if (this.isVisible) this.toggle();
        }, 5000);
    },
    
    toggle: function() {
        let inspector = document.getElementById('google-consent-debug');
        if (!inspector) return;
        
        this.isVisible = !this.isVisible;
        inspector.style.display = this.isVisible ? 'block' : 'none';
    },
    
    update: function() {
        let inspector = document.getElementById('google-consent-debug');
        if (!inspector) return;
        
        let currentSettings = JSON.parse(localStorage.getItem(GOOGLE_CONSENT_V2_STORAGE_KEY) || '{}');
        
        // Update consent status
        let statusHtml = '<strong>Current Consent Status:</strong><br>';
        for (let flag in currentSettings) {
            let color = currentSettings[flag] === 'granted' ? 'green' : 'red';
            statusHtml += `<span style="color: ${color};">● ${flag}</span>: <strong>${currentSettings[flag]}</strong><br>`;
        }
        document.getElementById('consent-status').innerHTML = statusHtml;
        
        // Update cookie mappings
        let mappingsHtml = '<strong>Cookie → Consent Flag Mappings:</strong><br>';
        for (let cookieUid in GOOGLE_CONSENT_V2_COOKIE_MAPPINGS) {
            let mapping = GOOGLE_CONSENT_V2_COOKIE_MAPPINGS[cookieUid];
            let flags = Object.keys(mapping).filter(key => mapping[key]).join(', ');
            mappingsHtml += `<code>${cookieUid}</code> → ${flags}<br>`;
        }
        document.getElementById('cookie-mappings').innerHTML = mappingsHtml;
    },
    
    logEvent: function(message) {
        let eventLog = document.getElementById('event-log');
        if (!eventLog) return;
        
        let timestamp = new Date().toLocaleTimeString();
        let eventDiv = document.createElement('div');
        eventDiv.innerHTML = `<small style="color: #666;">${timestamp}</small> ${message}`;
        eventLog.appendChild(eventDiv);
        
        // Keep only last 10 events
        while (eventLog.children.length > 10) {
            eventLog.removeChild(eventLog.firstChild);
        }
        
        eventLog.scrollTop = eventLog.scrollHeight;
    },
    
    clearEvents: function() {
        let eventLog = document.getElementById('event-log');
        if (eventLog) eventLog.innerHTML = '';
    },
    
    exportData: function() {
        let data = {
            timestamp: new Date().toISOString(),
            consentSettings: JSON.parse(localStorage.getItem(GOOGLE_CONSENT_V2_STORAGE_KEY) || '{}'),
            cookieMappings: GOOGLE_CONSENT_V2_COOKIE_MAPPINGS,
            defaultState: GOOGLE_CONSENT_V2_DEFAULT_STATE
        };
        
        let blob = new Blob([JSON.stringify(data, null, 2)], {type: 'application/json'});
        let url = URL.createObjectURL(blob);
        let a = document.createElement('a');
        a.href = url;
        a.download = 'google-consent-mode-debug-' + Date.now() + '.json';
        a.click();
        URL.revokeObjectURL(url);
    },
    
    makeDraggable: function() {
        let header = document.getElementById('debug-header');
        let inspector = document.getElementById('google-consent-debug');
        let isDragging = false;
        let currentX, currentY, initialX, initialY;
        
        header.addEventListener('mousedown', function(e) {
            if (e.target.onclick) return; // Don't drag when clicking close button
            
            isDragging = true;
            initialX = e.clientX - inspector.offsetLeft;
            initialY = e.clientY - inspector.offsetTop;
        });
        
        document.addEventListener('mousemove', function(e) {
            if (!isDragging) return;
            
            e.preventDefault();
            currentX = e.clientX - initialX;
            currentY = e.clientY - initialY;
            
            inspector.style.left = currentX + 'px';
            inspector.style.top = currentY + 'px';
            inspector.style.right = 'auto';
        });
        
        document.addEventListener('mouseup', function() {
            isDragging = false;
        });
    }
};

// Create inspector after DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        window.googleConsentDebugInspector.create();
    });
} else {
    window.googleConsentDebugInspector.create();
}

// Keyboard shortcut (Ctrl+Shift+G) to toggle inspector
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.shiftKey && e.key === 'G') {
        e.preventDefault();
        window.googleConsentDebugInspector.toggle();
    }
});

JAVASCRIPT;
    }
}
