/**
 * Google Consent Mode v2 Integration for REDAXO Consent Manager
 * 
 * This script initializes Google's Consent Mode v2 and provides functions
 * to manage consent states for Google services.
 * 
 * IMPORTANT: All consent flags are initially set to 'denied' by default.
 * This is GDPR compliant - services must explicitly be granted consent.
 */

function debugLog(message, data) {
    if (window.consentManagerDebugConfig && window.consentManagerDebugConfig.debug_enabled) {
        if (data !== undefined) {
            console.log('Google Consent Mode: ' + message, data);
        } else {
            console.log('Google Consent Mode: ' + message);
        }
    }
}

if (window.consentManagerDebugConfig && window.consentManagerDebugConfig.debug_enabled) {
    console.log('Google Consent Mode v2: Script loaded');
}

window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}

// GDPR-konforme Defaults - ALLE Services standardmäßig verweigert
// Erst nach expliziter Zustimmung werden Services auf 'granted' gesetzt
let GOOGLE_CONSENT_V2_DEFAULTS = {
    'ad_storage': 'denied',
    'ad_user_data': 'denied', 
    'ad_personalization': 'denied',
    'analytics_storage': 'denied',
    'personalization_storage': 'denied',
    'functionality_storage': 'denied',    // Auch notwendige erst nach Consent
    'security_storage': 'denied'          // Auch notwendige erst nach Consent
};

let GOOGLE_CONSENT_V2_FIELDS = [
    'ad_storage',           // Google Ads
    'ad_user_data',         // Google Ads
    'ad_personalization',   // Google Ads
    'analytics_storage',    // Google Analytics
    'personalization_storage', // Necessary
    'functionality_storage',   // Necessary
    'security_storage',        // Necessary
];

// Sample for custom event handlers
let GOOGLE_CONSENT_V2_FIELDS_EVENTS = {
    'analytics_storage': {
        'on_granted': function() {
            console.log('Analytics storage consent granted');
        },
        'on_denied': function() {
            console.log('Analytics storage consent denied');
        }
    }
};

/**
 * Helper to bring in PHP's array_combine function
 * @param {Array} keys
 * @param {Array} values
 * @returns {Object|false}
 */
function array_combine(keys, values) {
    const newArray = {};
    let i = 0;

    if (
        typeof keys !== 'object' ||
        typeof values !== 'object' ||
        typeof keys.length !== 'number' ||
        typeof values.length !== 'number' ||
        !keys.length ||
        !values.length ||
        keys.length !== values.length
    ) {
        return false;
    }

    for (i = 0; i < keys.length; i++) {
        newArray[keys[i]] = values[i];
    }

    return newArray;
}

/**
 * Helper to bring in PHP's array_fill function
 * @param {number} startIndex
 * @param {number} num
 * @param {*} mixedVal
 * @returns {Array}
 */
function array_fill(startIndex, num, mixedVal) {
    let key;
    const tmpArr = [];

    if (!isNaN(startIndex) && !isNaN(num)) {
        for (key = 0; key < num; key++) {
            tmpArr[(key + startIndex)] = mixedVal;
        }
    }

    return tmpArr;
}

// Current runtime settings
let currentConsentSettings = { ...GOOGLE_CONSENT_V2_DEFAULTS };

// Initialize with defaults (always denied at start)
// This ensures that hits sent before consent is granted are tagged as "denied"
gtag('consent', 'default', currentConsentSettings);

// Set user ID if available
if(localStorage.getItem('userId') != null) {
    window.dataLayer.push({'user_id': localStorage.getItem('userId')});
}

/**
 * Set consent for specified services
 * @param {Object} consent - Object with consent settings
 */
function setConsent(consent) {
    debugLog('Updating with consent', consent);
    
    // Merge provided consent into current settings
    for (const key of Object.keys(currentConsentSettings)) {
        if (typeof consent[key] !== "undefined") {
            // Trigger event handlers if defined
            if(typeof(GOOGLE_CONSENT_V2_FIELDS_EVENTS[key]) != 'undefined') {
                if(consent[key] === true && currentConsentSettings[key] === 'denied') {
                    GOOGLE_CONSENT_V2_FIELDS_EVENTS[key]['on_granted']();
                } else if (consent[key] === false && currentConsentSettings[key] === 'granted') {
                    GOOGLE_CONSENT_V2_FIELDS_EVENTS[key]['on_denied']();
                }
            }

            currentConsentSettings[key] = (consent[key] === true ? 'granted' : 'denied');
        }
    }

    debugLog('Final settings', currentConsentSettings);
    gtag('consent', 'update', currentConsentSettings);
}

// Expose functions globally in a single namespaced object
window.GoogleConsentModeV2 = {
    setConsent: setConsent,
    fields: GOOGLE_CONSENT_V2_FIELDS,
    events: GOOGLE_CONSENT_V2_FIELDS_EVENTS
};

// Keep backwards compatibility with old global function name
window.setConsent = setConsent;

if (window.consentManagerDebugConfig && window.consentManagerDebugConfig.debug_enabled) {
    console.log('Google Consent Mode v2 initialized');
}
