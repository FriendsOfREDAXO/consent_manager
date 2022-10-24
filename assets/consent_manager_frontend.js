/* globals Cookies, consent_managerIEVersion */
(function () {
    'use strict';
    var expires = new Date(),
        show = 0,
        cookieData = {},
        consents = [],
        addonVersion = -1,
        cachelogid = -1,
        cookieVersion = -1,
        cookieCachelogid = -1,
        consent_managerBox;

    expires.setFullYear(expires.getFullYear() + 1);
    // es gibt keinen datenschutzcookie, banner zeigen
    if (typeof Cookies.get('consent_manager') === 'undefined') {
        show = 1;
        Cookies.set('test', 'test', { path: '/', sameSite: 'Lax', secure: false });
        // cookie konnte nicht gesetzt werden, kein cookie banner anzeigen
        if (typeof Cookies.get('test') === 'undefined') {
            show = 0;
        } else {
            Cookies.remove('test');
        }
    } else {
        cookieData = JSON.parse(Cookies.get('consent_manager'));
        // cookie version auslesen. cookie version = major addon version zum zeitpunkt des cookie speicherns
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

    // aktuelle major addon version auslesen
    addonVersion = parseInt(consent_manager_parameters.version);
    cachelogid = parseInt(consent_manager_parameters.cachelogid);
    // cookie wurde mit einer aelteren major version gesetzt, alle consents loeschen und box zeigen
    if (addonVersion !== cookieVersion || cachelogid !== cookieCachelogid) {
        show = 1;
        consents = [];
        deleteCookies();
    }

    if (consent_managerIEVersion() === 9) {
        consent_managerBox.querySelectorAll('.consent_manager-cookiegroup-checkbox').forEach(function (el) {
            el.classList.remove('pretty');
            el.querySelectorAll('.icon').forEach(function (icon) {
                icon.remove();
            });
        });
    }

    consents.forEach(function (uid) {
        addScript(consent_managerBox.querySelector('[data-uid="script-' + uid + '"]'));
    });

    if (consent_manager_parameters.initially_hidden) {
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
            }
            if (consent_manager_parameters.hidebodyscrollbar) {
                document.querySelector('body').style.overflow = 'auto';
            }
            document.getElementById('consent_manager-background').classList.add('consent_manager-hidden');
        });
    });

    document.getElementById('consent_manager-toggle-details').addEventListener('click', function () {
        document.getElementById('consent_manager-detail').classList.toggle('consent_manager-hidden');
    });

    document.getElementById('consent_manager-toggle-details').addEventListener('keydown', function (event) {
        if (event.key == 'Enter') {
            document.getElementById('consent_manager-detail').classList.toggle('consent_manager-hidden');
        }
    });

    document.querySelectorAll('.consent_manager-show-box, .consent_manager-show-box-reload').forEach(function (el) {
        el.addEventListener('click', function () {
            showBox();
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
        consent_managerBox.querySelectorAll('[data-cookie-uids]').forEach(function (el) {
            // array mit cookie uids
            var cookieUids = JSON.parse(el.getAttribute('data-cookie-uids'));
            if (el.checked || toSave === 'all') {
                cookieUids.forEach(function (uid) {
                    consents.push(uid);
                    addScript(consent_managerBox.querySelector('[data-uid="script-' + uid + '"]'));
                });
            } else {
                cookieUids.forEach(function (uid) {
                    removeScript(consent_managerBox.querySelector('[data-uid="script-' + uid + '"]'));
                });
            }
        });
        cookieData.consents = consents;
        Cookies.set('consent_manager', JSON.stringify(cookieData), { expires: expires, path: '/', domain: consent_manager_parameters.domain, sameSite: 'Lax', secure: false });

        var http = new XMLHttpRequest(),
            url = consent_manager_parameters.fe_controller + '?rex-api-call=consent_manager',
            params = 'domain=' + consent_manager_parameters.domain + '&consentid=' + consent_manager_parameters.consentid;
        http.open('POST', url, false);
        http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        http.send(params);

        if (document.querySelectorAll('.consent_manager-show-box-reload').length) {
            location.reload();
        } else {
            document.dispatchEvent(new CustomEvent('consent_manager-saved', { detail: JSON.stringify(consents) }));
        }
    }

    function deleteCookies() {
        var domain = consent_manager_parameters.domain;
        for (var key in Cookies.get()) {
            Cookies.remove(encodeURIComponent(key));
            Cookies.remove(encodeURIComponent(key), { 'domain': domain });
            Cookies.remove(encodeURIComponent(key), { 'path': '/' });
            Cookies.remove(encodeURIComponent(key), { 'domain': domain, 'path': '/' });
            Cookies.remove(encodeURIComponent(key), { 'domain': ('.' + domain) });
            Cookies.remove(encodeURIComponent(key), { 'domain': ('.' + domain), 'path': '/' });
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
        document.getElementById('consent_manager-save-selection').focus();
    }

})();

function consent_manager_showBox() {
    var consents = [];
    if (typeof Cookies.get('consent_manager') != 'undefined') {
        cookieData = JSON.parse(Cookies.get('consent_manager'));
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
    document.getElementById('consent_manager-save-selection').focus();
}
