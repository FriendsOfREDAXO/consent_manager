/* globals Cookies, iwccIEVersion */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    var expires = new Date(),
        show = 0,
        cookieData = {},
        consents = [],
        addonVersion = -1,
        cookieVersion = -1,
        iwccBox;

    expires.setFullYear(expires.getFullYear() + 1);
    // es gibt keinen datenschutzcookie, banner zeigen
    if (typeof Cookies.get('iwcc') === 'undefined') {
        show = 1;
        Cookies.set('test', 'test', {path: '/', sameSite: 'Lax', secure: false});
        // cookie konnte nicht gesetzt werden, kein cookie banner anzeigen
        if (typeof Cookies.get('test') === 'undefined') {
            show = 0;
        } else {
            Cookies.remove('test');
        }
    } else {
        cookieData = JSON.parse(Cookies.get('iwcc'));
        // cookie version auslesen. cookie version = major addon version zum zeitpunkt des cookie speicherns
        if (cookieData.hasOwnProperty('version')) {
            consents = cookieData.consents;
            cookieVersion = parseInt(cookieData.version);
        }
    }

    iwccBox = new DOMParser().parseFromString(document.getElementById('iwcc-template').innerHTML, 'text/html');
    iwccBox = iwccBox.getElementById('iwcc-background');
    document.querySelectorAll('body')[0].appendChild(iwccBox);

    // aktuelle major addon version auslesen
    addonVersion = parseInt(document.getElementById('iwcc-background').getAttribute('data-version'));
    // cookie wurde mit einer aelteren major version gesetzt, alle consents loeschen und box zeigen
    if (addonVersion !== cookieVersion) {
        show = 1;
        consents = [];
        deleteCookies();
    }

    if (iwccIEVersion() === 9) {
        iwccBox.querySelectorAll('.iwcc-cookiegroup-checkbox').forEach(function (el) {
            el.classList.remove('pretty');
            el.querySelectorAll('.icon').forEach(function (icon) {
                icon.remove();
            });
        });
    }

    consents.forEach(function (uid) {
        addScript(iwccBox.querySelector('[data-uid="' + uid + '"]'));
    });

    if (iwccBox.classList.contains('iwcc-initially-hidden')) {
        show = 0;
    }

    if (show) {
        showBox();
    }

    iwccBox.querySelectorAll('.iwcc-close').forEach(function (el) {
        el.addEventListener('click', function () {
            if (el.classList.contains('iwcc-save-selection')) {
                deleteCookies();
                saveConsent('selection');
            } else if (el.classList.contains('iwcc-accept-all')) {
                deleteCookies();
                saveConsent('all');
            }
            document.getElementById('iwcc-background').classList.add('iwcc-hidden');
        });
    });

    document.getElementById('iwcc-toggle-details').addEventListener('click', function () {
        document.getElementById('iwcc-detail').classList.toggle('iwcc-hidden');
    });

    document.querySelectorAll('.iwcc-show-box, .iwcc-show-box-reload').forEach(function (el) {
        el.addEventListener('click', function () {
            showBox();
        });
    });

    function saveConsent(toSave) {
        consents = [];
        cookieData = {
            consents: [],
            version: addonVersion,
            consentid: document.getElementById('iwcc-background').getAttribute('data-consentid'),
            cachelogid: document.getElementById('iwcc-background').getAttribute('data-cachelogid')
        };
        // checkboxen
        iwccBox.querySelectorAll('[data-cookie-uids]').forEach(function (el) {
            // array mit cookie uids
            var cookieUids = JSON.parse(el.getAttribute('data-cookie-uids'));
            if (el.checked || toSave === 'all') {
                cookieUids.forEach(function (uid) {
                    consents.push(uid);
                    addScript(iwccBox.querySelector('[data-uid="' + uid + '"]'));
                });
            } else {
                cookieUids.forEach(function (uid) {
                    removeScript(iwccBox.querySelector('[data-uid="' + uid + '"]'));
                });
            }
        });
        cookieData.consents = consents;
        Cookies.set('iwcc', JSON.stringify(cookieData), {expires: expires, path: '/', sameSite: 'Lax', secure: false});

        var http = new XMLHttpRequest(),
            url = '/index.php?rex-api-call=iwcc',
            params = 'consentid=' + document.getElementById('iwcc-background').getAttribute('data-consentid');
        http.open('POST', url, true);
        http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        http.send(params);

        if (document.querySelectorAll('.iwcc-show-box-reload').length) {
            location.reload();
        } else {
            document.dispatchEvent(new CustomEvent('iwcc-saved', {detail: JSON.stringify(consents)}));
        }
    }

    function deleteCookies() {
        var domain = document.getElementById('iwcc-background').getAttribute('data-domain-name');
        for (var key in Cookies.get()) {
            Cookies.remove(encodeURIComponent(key));
            Cookies.remove(encodeURIComponent(key), {'domain': domain});
            Cookies.remove(encodeURIComponent(key), {'path': '/'});
            Cookies.remove(encodeURIComponent(key), {'domain': domain, 'path': '/'});
            Cookies.remove(encodeURIComponent(key), {'domain': ('.' + domain)});
            Cookies.remove(encodeURIComponent(key), {'domain': ('.' + domain), 'path': '/'});
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
        iwccBox.querySelectorAll('[data-cookie-uids]').forEach(function (el) {
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
        document.getElementById('iwcc-background').classList.remove('iwcc-hidden');
    }

});