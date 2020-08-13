// get IE version https://stackoverflow.com/a/19999868
function iwccIEVersion(){var n=window.navigator.userAgent,i=n.indexOf("MSIE ");return i>0?parseInt(n.substring(i+5,n.indexOf(".",i))):-1}
// element.classList polyfill http://purl.eligrey.com/github/classList.js/blob/master/classList.js
"document"in self&&("classList"in document.createElement("_")&&(!document.createElementNS||"classList"in document.createElementNS("http://www.w3.org/2000/svg","g"))||!function(t){"use strict";if("Element"in t){var e="classList",n="prototype",i=t.Element[n],s=Object,r=String[n].trim||function(){return this.replace(/^\s+|\s+$/g,"")},o=Array[n].indexOf||function(t){for(var e=0,n=this.length;n>e;e++)if(e in this&&this[e]===t)return e;return-1},c=function(t,e){this.name=t,this.code=DOMException[t],this.message=e},a=function(t,e){if(""===e)throw new c("SYNTAX_ERR","The token must not be empty.");if(/\s/.test(e))throw new c("INVALID_CHARACTER_ERR","The token must not contain space characters.");return o.call(t,e)},l=function(t){for(var e=r.call(t.getAttribute("class")||""),n=e?e.split(/\s+/):[],i=0,s=n.length;s>i;i++)this.push(n[i]);this._updateClassName=function(){t.setAttribute("class",this.toString())}},u=l[n]=[],h=function(){return new l(this)};if(c[n]=Error[n],u.item=function(t){return this[t]||null},u.contains=function(t){return~a(this,t+"")},u.add=function(){var t,e=arguments,n=0,i=e.length,s=!1;do t=e[n]+"",~a(this,t)||(this.push(t),s=!0);while(++n<i);s&&this._updateClassName()},u.remove=function(){var t,e,n=arguments,i=0,s=n.length,r=!1;do for(t=n[i]+"",e=a(this,t);~e;)this.splice(e,1),r=!0,e=a(this,t);while(++i<s);r&&this._updateClassName()},u.toggle=function(t,e){var n=this.contains(t),i=n?e!==!0&&"remove":e!==!1&&"add";return i&&this[i](t),e===!0||e===!1?e:!n},u.replace=function(t,e){var n=a(t+"");~n&&(this.splice(n,1,e),this._updateClassName())},u.toString=function(){return this.join(" ")},s.defineProperty){var f={get:h,enumerable:!0,configurable:!0};try{s.defineProperty(i,e,f)}catch(p){void 0!==p.number&&-2146823252!==p.number||(f.enumerable=!1,s.defineProperty(i,e,f))}}else s[n].__defineGetter__&&i.__defineGetter__(e,h)}}(self),function(){"use strict";var t=document.createElement("_");if(t.classList.add("c1","c2"),!t.classList.contains("c2")){var e=function(t){var e=DOMTokenList.prototype[t];DOMTokenList.prototype[t]=function(t){var n,i=arguments.length;for(n=0;i>n;n++)t=arguments[n],e.call(this,t)}};e("add"),e("remove")}if(t.classList.toggle("c3",!1),t.classList.contains("c3")){var n=DOMTokenList.prototype.toggle;DOMTokenList.prototype.toggle=function(t,e){return 1 in arguments&&!this.contains(t)==!e?e:n.call(this,t)}}"replace"in document.createElement("_").classList||(DOMTokenList.prototype.replace=function(t,e){var n=this.toString().split(" "),i=n.indexOf(t+"");~i&&(n=n.slice(i),this.remove.apply(this,n),this.add(e),this.add.apply(this,n.slice(1)))}),t=null}());
// DOMParser polyfill https://gist.github.com/1129031
!function(t){"use strict";var e=t.prototype,r=e.parseFromString;try{if((new t).parseFromString("","text/html"))return}catch(t){}e.parseFromString=function(t,e){if(/^\s*text\/html\s*(?:;|$)/i.test(e)){var n=document.implementation.createHTMLDocument("");return t.toLowerCase().indexOf("<!doctype")>-1?n.documentElement.innerHTML=t:n.body.innerHTML=t,n}return r.apply(this,arguments)}}(DOMParser);
// nodelist.forEach polyfill https://developer.mozilla.org/en-US/docs/Web/API/NodeList/forEach
if (window.NodeList && !NodeList.prototype.forEach){NodeList.prototype.forEach = Array.prototype.forEach;}
// node.remove polyfill https://github.com/jserz/js_piece/blob/master/DOM/ChildNode/remove()/remove().md
[Element.prototype,CharacterData.prototype,DocumentType.prototype].forEach(function(e){e.hasOwnProperty("remove")||Object.defineProperty(e,"remove",{configurable:!0,enumerable:!0,writable:!0,value:function(){null!==this.parentNode&&this.parentNode.removeChild(this)}})});

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

    if (iwccIEVersion() === 9)
    {
        iwccBox.querySelectorAll('.iwcc-cookiegroup-checkbox').forEach(function (el)
        {
            el.classList.remove('pretty');
            el.querySelectorAll('.icon').forEach(function (icon)
            {
                icon.remove();
            })
        });
    }

    for (var key in consents)
    {
        addScript(iwccBox.querySelector('[data-uid="' + key + '"]'));
    }

    if (iwccBox.classList.contains('iwcc-initially-hidden'))
    {
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
    document.querySelectorAll('.iwcc-show-box-reload').forEach(function (el)
    {
        el.addEventListener('click', function ()
        {
            showBoxReload();
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
            Cookies.remove(encodeURIComponent(key));
            Cookies.remove(encodeURIComponent(key), {'domain': domain});
            Cookies.remove(encodeURIComponent(key), {'path': '/'});
            Cookies.remove(encodeURIComponent(key), {'domain': domain, 'path': '/'});
            Cookies.remove(encodeURIComponent(key), {'domain': ('.' + domain)});
            Cookies.remove(encodeURIComponent(key), {'domain': ('.' + domain), 'path': '/'});
        }
    }

    function addScript(el)
    {
        var scriptWrapper,
            scriptDom;
        if (!el)
        {
            return;
        }
        scriptWrapper = el.parentNode.querySelector('.iwcc-script');
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
                scriptWrapper.appendChild(scriptNode);
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
        var checkbox;
        for (var key in consents)
        {
            checkbox = iwccBox.querySelector('[data-uid="' + key + '"]');
            if (!checkbox)
            {
                continue;
            }
            checkbox.checked = true;
        }
        document.getElementById('iwcc-background').classList.remove('iwcc-hidden');
    }

    function showBoxReload()
    {
        document.querySelector(".iwcc-accept-all, .iwcc-save-selection").addEventListener("click", function(evt) {
            location.reload();
        });
        showBox();
    }

});