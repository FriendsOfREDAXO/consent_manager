/**
 * Google Consent Mode v2 Integration for REDAXO Consent Manager
 * 
 * This script initializes Google's Consent Mode v2 and provides functions
 * to manage consent states for Google services.
 * 
 * IMPORTANT: All consent flags are initially set to 'denied' by default.
 * This is GDPR compliant - services must explicitly be granted consent.
 */

window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}

let GOOGLE_CONSENT_V2_DEFAULT_STATE = 'denied'; // GDPR compliant default
let GOOGLE_CONSENT_V2_STORAGE_KEY = 'consentMode';

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

// Get current settings from localStorage
let consentStorage = localStorage.getItem(GOOGLE_CONSENT_V2_STORAGE_KEY);

if(consentStorage === null) {
    // Initialize consent settings
    let defaultSettings = array_combine(
        GOOGLE_CONSENT_V2_FIELDS, 
        array_fill(0, GOOGLE_CONSENT_V2_FIELDS.length, GOOGLE_CONSENT_V2_DEFAULT_STATE)
    );

    if(defaultSettings !== false) {
        gtag('consent', 'default', defaultSettings);
        localStorage.setItem(GOOGLE_CONSENT_V2_STORAGE_KEY, JSON.stringify(defaultSettings));
    }
} else {
    // Check if array is consistent (if new entries appear in the future)
    let storedSettings = JSON.parse(consentStorage);

    if(Object.keys(storedSettings).length !== GOOGLE_CONSENT_V2_FIELDS.length) {
        GOOGLE_CONSENT_V2_FIELDS.forEach((field) => {
            if (typeof storedSettings[field] == "undefined") {
                storedSettings[field] = GOOGLE_CONSENT_V2_DEFAULT_STATE;
            }
        });

        gtag('consent', 'default', storedSettings);
        localStorage.setItem(GOOGLE_CONSENT_V2_STORAGE_KEY, JSON.stringify(storedSettings));
    } else {
        gtag('consent', 'default', storedSettings);
    }
}

// Set user ID if available
if(localStorage.getItem('userId') != null) {
    window.dataLayer.push({'user_id': localStorage.getItem('userId')});
}

/**
 * Set consent for specified services
 * @param {Object} consent - Object with consent settings
 */
function setConsent(consent) {
    let consentSettings = JSON.parse(localStorage.getItem(GOOGLE_CONSENT_V2_STORAGE_KEY));

    for (const [key, value] of Object.entries(consentSettings)) {
        if (typeof consent[key] !== "undefined") {
            // Trigger event handlers if defined
            if(typeof(GOOGLE_CONSENT_V2_FIELDS_EVENTS[key]) != 'undefined') {
                if(consent[key] === true && consentSettings[key] === 'denied') {
                    GOOGLE_CONSENT_V2_FIELDS_EVENTS[key]['on_granted']();
                } else if (consent[key] === false && consentSettings[key] === 'granted') {
                    GOOGLE_CONSENT_V2_FIELDS_EVENTS[key]['on_denied']();
                }
            }

            consentSettings[key] = (consent[key] === true ? 'granted' : 'denied');
        }
    }

    gtag('consent', 'update', consentSettings);
    localStorage.setItem(GOOGLE_CONSENT_V2_STORAGE_KEY, JSON.stringify(consentSettings));
}

// Expose functions globally in a single namespaced object
window.GoogleConsentModeV2 = {
    setConsent: setConsent,
    fields: GOOGLE_CONSENT_V2_FIELDS,
    events: GOOGLE_CONSENT_V2_FIELDS_EVENTS
};

// Keep backwards compatibility with old global function name
window.setConsent = setConsent;

console.log('Google Consent Mode v2 initialized');
