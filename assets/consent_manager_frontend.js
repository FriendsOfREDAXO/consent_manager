const cmCookieAPI = Cookies.withAttributes({ expires: cmCookieExpires, path: '/', domain: consent_manager_parameters.domain, sameSite: 'Lax', secure: false });

console.log('Consent Manager: Script loaded');

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

    // Es gibt keinen Datenschutzcookie, Consent zeigen
    if (typeof cmCookieAPI.get('consent_manager') === 'undefined') {
        cmCookieAPI.set('consent_manager_test', 'test');
        // Test-Cookie konnte nicht gesetzt werden, kein Consent anzeigen
        if (typeof cmCookieAPI.get('consent_manager_test') === 'undefined') {
            show = 0;
            consent_manager_parameters.no_cookie_set = true;
            console.warn('Addon consent_manager: Es konnte kein Cookie für die Domain ' + consent_manager_parameters.domain + ' gesetzt werden!');
        } else {
            cmCookieAPI.remove('consent_manager_test');
            show = 1;
        }
    } else {
        cookieData = JSON.parse(cmCookieAPI.get('consent_manager'));
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
    if (addonVersion !== cookieVersion || cachelogid !== cookieCachelogid) {
        show = 1;
        consents = [];
        deleteCookies();
    }

    // on startup trigger scripts of enabled consents
    consents.forEach(function (uid) {
        addScript(consent_managerBox.querySelector('[data-uid="script-' + uid + '"]'));
        removeScript(consent_managerBox.querySelector('[data-uid="script-unselect-' + uid + '"]'));
    });

    // on startup trigger Google Consent Mode v2 update if consents exist
    if (consents.length > 0 && typeof window.GoogleConsentModeV2 !== 'undefined' && typeof window.GoogleConsentModeV2.setConsent === 'function') {
        var googleConsentFlags = mapConsentsToGoogleFlags(consents);
        console.log('Consent Manager: Auto-mapping Google Consent Mode flags', consents, googleConsentFlags);
        window.GoogleConsentModeV2.setConsent(googleConsentFlags);
    } else {
        console.log('Consent Manager: Auto-mapping skipped', {consents: consents, hasGCM: typeof window.GoogleConsentModeV2 !== 'undefined', hasSetConsent: typeof window.GoogleConsentModeV2?.setConsent === 'function'});
    }

    // on startup trigger unselect-scripts of disabled consents
    consent_managerBox.querySelectorAll('[data-cookie-uids]').forEach(function (el) {
        // array mit cookie uids
        var cookieUids = JSON.parse(el.getAttribute('data-cookie-uids'));

        cookieUids.forEach(function (uid) {
            if(!consents.includes(uid)) {
                removeScript(consent_managerBox.querySelector('[data-uid="script-' + uid + '"]'));
                addScript(consent_managerBox.querySelector('[data-uid="script-unselect-' + uid + '"]'));
            }
        });
    });

    if (consent_manager_parameters.initially_hidden || consent_manager_parameters.no_cookie_set) {
        show = 0;
    }

    if (show) {
        showBox();
    }

    consent_managerBox.querySelectorAll('.consent_manager-close').forEach(function (el) {
        el.addEventListener('click', function () {
            if (el.classList.contains('consent_manager-save-selection')) {
                deleteCookies();
                saveConsent('selection');
            } else if (el.classList.contains('consent_manager-accept-all')) {
                deleteCookies();
                saveConsent('all');
            } else if (el.classList.contains('consent_manager-accept-none')) {
                deleteCookies();
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
            return false;
        });
    });

    if (document.getElementById('consent_manager-toggle-details')) {
        document.getElementById('consent_manager-toggle-details').addEventListener('click', function () {
            document.getElementById('consent_manager-detail').classList.toggle('consent_manager-hidden');
            return false;
        });
    }

    if (document.getElementById('consent_manager-toggle-details')) {
        document.getElementById('consent_manager-toggle-details').addEventListener('keydown', function (event) {
            if (event.key == 'Enter') {
                document.getElementById('consent_manager-detail').classList.toggle('consent_manager-hidden');
                return false;
            }
        });
    }

    document.querySelectorAll('.consent_manager-show-box, .consent_manager-show-box-reload').forEach(function (el) {
        el.addEventListener('click', function () {
            showBox();
            return false;
        });
    });

    function saveConsent(toSave) {
        consents = [];
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
                var cookieUids = JSON.parse(el.getAttribute('data-cookie-uids'));
                if (el.checked || toSave === 'all') {
                    cookieUids.forEach(function (uid) {
                        consents.push(uid);
                        addScript(consent_managerBox.querySelector('[data-uid="script-' + uid + '"]'));
                        removeScript(consent_managerBox.querySelector('[data-uid="script-unselect-' + uid + '"]'));
                    });
                } else {
                    cookieUids.forEach(function (uid) {
                        removeScript(consent_managerBox.querySelector('[data-uid="script-' + uid + '"]'));
                        addScript(consent_managerBox.querySelector('[data-uid="script-unselect-' + uid + '"]'));
                    });
                }
            });
        } else {
            consent_managerBox.querySelectorAll('[data-cookie-uids]').forEach(function (el) {
                // array mit cookie uids
                var cookieUids = JSON.parse(el.getAttribute('data-cookie-uids'));
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

        cmCookieAPI.set('consent_manager', JSON.stringify(cookieData));
        
        // Google Consent Mode v2 Update
        if (typeof window.GoogleConsentModeV2 !== 'undefined' && typeof window.GoogleConsentModeV2.setConsent === 'function') {
            var googleConsentFlags = mapConsentsToGoogleFlags(consents);
            console.log('Consent Manager: Mapping consents to Google flags', consents, googleConsentFlags);
            window.GoogleConsentModeV2.setConsent(googleConsentFlags);
        } else {
            console.log('Consent Manager: Google Consent Mode not available for mapping');
        }
        
        if (typeof cmCookieAPI.get('consent_manager') === 'undefined') {
            consent_manager_parameters.no_cookie_set = true;
            console.warn('Addon consent_manager: Es konnte kein Cookie für die Domain ' + document.domain + ' gesetzt werden!');
        } else {
            var http = new XMLHttpRequest(),
                url = consent_manager_parameters.fe_controller + '?rex-api-call=consent_manager&buster=' + new Date().getTime(),
                params = 'domain=' + document.domain + '&consentid=' + consent_manager_parameters.consentid + '&buster=' + new Date().getTime();
            http.onerror = (e) => {
                console.error('Addon consent_manager: Fehler beim speichern des Consent! ' + http.statusText);
            };
            http.open('POST', url, false);
            http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            http.setRequestHeader('Cache-Control', 'no-cache, no-store, max-age=0');
            http.setRequestHeader('Expires', 'Thu, 1 Jan 1970 00:00:00 GMT');
            http.setRequestHeader('Pragma', 'no-cache');
            http.send(params);
        }

        if (document.querySelectorAll('.consent_manager-show-box-reload').length || consent_manager_parameters.forcereload === 1) {
            location.reload();
        } else {
            document.dispatchEvent(new CustomEvent('consent_manager-saved', { detail: JSON.stringify(consents) }));
        }
    }

    function deleteCookies() {
        var domain = consent_manager_parameters.domain;
        for (var key in cmCookieAPI.get()) {
            cmCookieAPI.remove(encodeURIComponent(key));
            cmCookieAPI.remove(encodeURIComponent(key), { 'domain': domain });
            cmCookieAPI.remove(encodeURIComponent(key), { 'path': '/' });
            cmCookieAPI.remove(encodeURIComponent(key), { 'domain': domain, 'path': '/' });
            cmCookieAPI.remove(encodeURIComponent(key), { 'domain': ('.' + domain) });
            cmCookieAPI.remove(encodeURIComponent(key), { 'domain': ('.' + domain), 'path': '/' });
            Cookies.remove(encodeURIComponent(key), { 'domain': window.location.hostname });
            Cookies.remove(encodeURIComponent(key), { 'path': '/' });
            Cookies.remove(encodeURIComponent(key), { 'domain': window.location.hostname, 'path': '/' });
        }
    }

    function addScript(el) {
        var scriptDom;
        if (!el) {
            return;
        }
        if (!el.children.length) {
            scriptDom = new DOMParser().parseFromString(window.atob(el.getAttribute('data-script')), 'text/html');
            for (var i = 0; i < scriptDom.scripts.length; i++) {
                var scriptNode = document.createElement('script');
                if (scriptDom.scripts[i].src) {
                    scriptNode.src = scriptDom.scripts[i].src;
                } else {
                    scriptNode.innerHTML = scriptDom.scripts[i].innerHTML;
                }
                el.appendChild(scriptNode);
            }
        }
    }

    function removeScript(el) {
        if (!el) {
            return;
        }
        el.innerHTML = '';
    }

    function showBox() {
        consent_managerBox.querySelectorAll('[data-cookie-uids]').forEach(function (el) {
            var check = true,
                cookieUids = JSON.parse(el.getAttribute('data-cookie-uids'));
            cookieUids.forEach(function (uid) {
                if (consents.indexOf(uid) === -1) {
                    check = false;
                }
            });
            if (check) {
                el.checked = true;
            }
        });
        if (consent_manager_parameters.hidebodyscrollbar) {
            document.querySelector('body').style.overflow = 'hidden';
        }
        document.getElementById('consent_manager-background').classList.remove('consent_manager-hidden');
        var focusableEls = consent_managerBox.querySelectorAll('input[type="checkbox"]');//:not([disabled])
        var firstFocusableEl = focusableEls[0];
        consent_managerBox.focus();
        if (firstFocusableEl) firstFocusableEl.focus();
    }

})();

function consent_manager_showBox() {
    var consents = [];
    if (typeof cmCookieAPI.get('consent_manager') != 'undefined') {
        cookieData = JSON.parse(cmCookieAPI.get('consent_manager'));
        if (cookieData.hasOwnProperty('version')) {
            consents = cookieData.consents;
        }
    }
    consent_managerBox = document.getElementById('consent_manager-background');
    consent_managerBox.querySelectorAll('[data-cookie-uids]').forEach(function (el) {
        var check = true,
            cookieUids = JSON.parse(el.getAttribute('data-cookie-uids'));
        cookieUids.forEach(function (uid) {
            if (consents.indexOf(uid) === -1) {
                check = false;
            }
        });
        if (check) {
            el.checked = true;
        }
    });
    if (consent_manager_parameters.hidebodyscrollbar) {
        document.querySelector('body').style.overflow = 'hidden';
    }
    document.getElementById('consent_manager-background').classList.remove('consent_manager-hidden');
    var focusableEls = consent_managerBox.querySelectorAll('input[type="checkbox"]');//:not([disabled])
    var firstFocusableEl = focusableEls[0];
    consent_managerBox.focus();
    if (firstFocusableEl) firstFocusableEl.focus();
}

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

    return flags;
}

function consent_manager_showBox() {
    var consents = [];
    if (typeof cmCookieAPI.get('consent_manager') != 'undefined') {
        cookieData = JSON.parse(cmCookieAPI.get('consent_manager'));
        if (cookieData.hasOwnProperty('version')) {
            consents = cookieData.consents;
        }
    }
    consent_managerBox = document.getElementById('consent_manager-background');
    consent_managerBox.querySelectorAll('[data-cookie-uids]').forEach(function (el) {
        var check = true,
            cookieUids = JSON.parse(el.getAttribute('data-cookie-uids'));
        cookieUids.forEach(function (uid) {
            if (consents.indexOf(uid) === -1) {
                check = false;
            }
        });
        if (check) {
            el.checked = true;
        }
    });
    if (consent_manager_parameters.hidebodyscrollbar) {
        document.querySelector('body').style.overflow = 'hidden';
    }
    document.getElementById('consent_manager-background').classList.remove('consent_manager-hidden');
    var focusableEls = consent_managerBox.querySelectorAll('input[type="checkbox"]');//:not([disabled])
    var firstFocusableEl = focusableEls[0];
    consent_managerBox.focus();
    if (firstFocusableEl) firstFocusableEl.focus();
}

function consent_manager_hasconsent(id) {
    if (typeof cmCookieAPI.get('consent_manager') !== 'undefined') {
        return JSON.parse(cmCookieAPI.get('consent_manager')).consents.indexOf(id) !== -1;
    }
    return false;
}
