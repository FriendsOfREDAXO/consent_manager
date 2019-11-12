document.addEventListener('DOMContentLoaded', function ()
{
    var expires = new Date(),
        show = 0,
        consents = {},
        iwccBox;

    expires.setFullYear(expires.getFullYear() + 1);
    // es gibt keinen datenschutzcookie, banner zeigen
    if (typeof Cookies.get('iwcc') === 'undefined')
    {
        show = 1;
        Cookies.set('test', 'test');
        // cookie konnte nicht gesetzt werden, kein cookie banner anzeigen
        if (typeof Cookies.get('test') === 'undefined')
        {
            show = 0;
        }
        else
        {
            Cookies.remove('test');
        }
    }
    else
    {
        consents = JSON.parse(Cookies.get('iwcc'));
    }

    iwccBox = new DOMParser().parseFromString(document.getElementById('iwcc-template').innerHTML, 'text/html');
    iwccBox = iwccBox.getElementById('iwcc-background');
    document.querySelectorAll('body')[0].appendChild(iwccBox);

    for (var key in consents)
    {
        addScript(iwccBox.querySelector('[data-uid="' + key + '"]'));
    }

    if (iwccBox.classList.contains('iwcc-initially-hidden')) {
        show = 0;
    }

    if (show)
    {
        showBox();
    }

    iwccBox.querySelectorAll('.iwcc-close').forEach(function (el)
    {
        el.addEventListener('click', function ()
        {
            if (el.classList.contains('iwcc-save-selection'))
            {
                deleteCookies();
                saveConsent('selection');
            }
            else if (el.classList.contains('iwcc-accept-all'))
            {
                deleteCookies();
                saveConsent('all');
            }
            document.getElementById('iwcc-background').classList.add('iwcc-hidden');
        });
    });

    document.getElementById('iwcc-toggle-details').addEventListener('click', function ()
    {
        document.getElementById('iwcc-detail').classList.toggle('iwcc-hidden');
    });

    document.querySelectorAll('.iwcc-show-box').forEach(function (el)
    {
        el.addEventListener('click', function ()
        {
            showBox();
        });
    });

    function saveConsent(toSave)
    {
        consents = {};
        iwccBox.querySelectorAll('[data-cookie-uids]').forEach(function (el)
        {
            if (el.checked || toSave === 'all')
            {
                consents[el.getAttribute('data-uid')] = JSON.parse(el.getAttribute('data-cookie-uids'));
                addScript(el);
            }
            else
            {
                removeScript(el);
            }
        });
        Cookies.set('iwcc', JSON.stringify(consents), {expires: expires, path: '/'});
    }

    function deleteCookies()
    {
        var domain = document.getElementById('iwcc-background').getAttribute('data-domain-name');
        for (var key in Cookies.get())
        {
            Cookies.remove(key, {'domain': domain, 'path': '/'});
            Cookies.remove(key, {'domain': ('.' + domain), 'path': '/'});
        }
    }

    function addScript(el)
    {
        var scriptWrapper = el.parentNode.querySelector('.iwcc-script'),
            scriptDom;
        if (!scriptWrapper)
        {
            return;
        }
        if (!scriptWrapper.children.length)
        {
            scriptDom = new DOMParser().parseFromString(window.atob(scriptWrapper.getAttribute('data-script')), 'text/html');
            for (var i = 0; i < scriptDom.scripts.length; i++)
            {
                var scriptNode = document.createElement('script');
                if (scriptDom.scripts[i].src)
                {
                    scriptNode.src = scriptDom.scripts[i].src;
                }
                else
                {
                    scriptNode.innerHTML = scriptDom.scripts[i].innerHTML;
                }
                scriptWrapper.append(scriptNode);
            }
        }
    }

    function removeScript(el)
    {
        var scriptWrapper = el.parentNode.querySelector('.iwcc-script');
        if (!scriptWrapper)
        {
            return;
        }
        scriptWrapper.innerHTML = '';
    }

    function showBox()
    {
        for (var key in consents)
        {
            iwccBox.querySelector('[data-uid="' + key + '"]').checked = true;
        }
        document.getElementById('iwcc-background').classList.remove('iwcc-hidden');
    }

});
