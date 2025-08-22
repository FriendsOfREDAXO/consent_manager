/**
 * Google Consent Mode v2 Helper für das Backend
 * PJAX-kompatible Version mit Bootstrap 3 Collapse
 */

$(document).on('rex:ready pjax:complete', function() {
    console.log('Google Consent Helper loaded (PJAX compatible)');
    
    // Service-Mappings
    var mappings = {
        'analytics': {
            'analytics_storage': true
        },
        'google-analytics': {
            'analytics_storage': true
        },
        'google-analytics-4': {
            'analytics_storage': true
        },
        'google-tag-manager': {
            'analytics_storage': true,
            'ad_storage': true,
            'functionality_storage': true,
            'personalization_storage': true
        },
        'google-tag-manager-all': {
            'ad_storage': true,
            'ad_user_data': true,
            'ad_personalization': true,
            'analytics_storage': true,
            'functionality_storage': true,
            'personalization_storage': true,
            'security_storage': true
        },
        'matomo': {
            'analytics_storage': true
        },
        'matomo-self-hosted': {
            'analytics_storage': true
        },
        'adwords': {
            'ad_storage': true,
            'ad_user_data': true,
            'ad_personalization': true
        },
        'google-ads': {
            'ad_storage': true,
            'ad_user_data': true,
            'ad_personalization': true
        },
        'google-adwords': {
            'ad_storage': true,
            'ad_user_data': true,
            'ad_personalization': true
        },
        'facebook-pixel': {
            'ad_storage': true,
            'ad_user_data': true,
            'ad_personalization': true
        },
        'youtube': {
            'ad_storage': true,
            'personalization_storage': true
        },
        'google-maps': {
            'functionality_storage': true,
            'personalization_storage': true
        }
    };
    
    // Script generieren
    function generateConsentScript(serviceType, granted) {
        if (!mappings[serviceType]) {
            return "";
        }
        
        var settings = {};
        for (var key in mappings[serviceType]) {
            settings[key] = granted ? 'granted' : 'denied';
        }
        
        var action = granted ? 'Consent' : 'Widerruf';
        var comment = '<!-- Google Consent Mode v2 - ' + action + ' für ' + serviceType + ' (automatisch generiert) -->';
        
        return comment + "\n<script>\ngtag('consent', 'update', " + JSON.stringify(settings, null, 2) + ");\n</script>";
    }
    
    // Nur initialisieren wenn Helper Panel vorhanden
    if ($('#google-consent-helper-panel').length === 0) {
        return;
    }
    
    // Debug: Alle verfügbaren Textareas loggen
    console.log('Available textareas:');
    $('textarea').each(function(index) {
        var $this = $(this);
        console.log(' - Textarea ' + index + ':', {
            name: $this.attr('name'),
            id: $this.attr('id'),
            class: $this.attr('class')
        });
    });
    
    // Event Listeners - mit Namespace für PJAX
    $(document).off('click.googlehelper').on('click.googlehelper', '#google-helper-toggle', function(e) {
        e.preventDefault();
        console.log('Toggle helper clicked');
        
        var $panel = $('#google-consent-helper-content');
        var $button = $(this);
        
        $panel.collapse('toggle');
        
        // Button-Text ändern - dynamisch basierend auf aktuellem Text
        $panel.on('show.bs.collapse', function() {
            var currentHtml = $button.html();
            var newHtml = currentHtml.replace('fa-chevron-down', 'fa-chevron-up');
            // Verschiedene Sprach-Varianten unterstützen
            if (newHtml.includes('Helper einblenden')) {
                newHtml = newHtml.replace('Helper einblenden', 'Helper ausblenden');
            } else if (newHtml.includes('Show helper')) {
                newHtml = newHtml.replace('Show helper', 'Hide helper');
            } else if (newHtml.includes('Visa hjälparen')) {
                newHtml = newHtml.replace('Visa hjälparen', 'Dölj hjälparen');
            }
            $button.html(newHtml);
        });
        
        $panel.on('hide.bs.collapse', function() {
            var currentHtml = $button.html();
            var newHtml = currentHtml.replace('fa-chevron-up', 'fa-chevron-down');
            // Verschiedene Sprach-Varianten unterstützen
            if (newHtml.includes('Helper ausblenden')) {
                newHtml = newHtml.replace('Helper ausblenden', 'Helper einblenden');
            } else if (newHtml.includes('Hide helper')) {
                newHtml = newHtml.replace('Hide helper', 'Show helper');
            } else if (newHtml.includes('Dölj hjälparen')) {
                newHtml = newHtml.replace('Dölj hjälparen', 'Visa hjälparen');
            }
            $button.html(newHtml);
        });
    });
    
    // Consent Script generieren
    $(document).off('click.consent').on('click.consent', '#generate-consent-script', function() {
        var serviceType = $('#google-helper-service').val();
        console.log('Generate consent for:', serviceType);
        
        if (!serviceType) {
            showMessage('Bitte Service-Typ auswählen', 'warning');
            return;
        }
        
        var script = generateConsentScript(serviceType, true);
        
        // REDAXO Textarea-Selektoren - verschiedene Möglichkeiten ausprobieren
        var $textarea = $("textarea[name*='script']:not([name*='unselect'])").first();
        
        if ($textarea.length === 0) {
            // Fallback Selektoren
            $textarea = $("#rex-form-script, textarea[id*='script'], textarea[name='REX_INPUT_VALUE[script]']").first();
        }
        
        console.log('Found textarea:', $textarea.length, $textarea.attr('name') || $textarea.attr('id'));
        
        if ($textarea.length) {
            var currentContent = '';
            
            // Aktuellen Inhalt ermitteln
            if ($textarea[0].CodeMirror) {
                currentContent = $textarea[0].CodeMirror.getValue();
            } else {
                currentContent = $textarea.val();
            }
            
            // Neuen Code am Anfang hinzufügen (mit Leerzeile falls bereits Inhalt vorhanden)
            var newContent = script;
            if (currentContent.trim() !== '') {
                newContent = script + '\n\n' + currentContent;
            }
            
            // Aktualisieren
            if ($textarea[0].CodeMirror) {
                console.log('Using CodeMirror setValue');
                $textarea[0].CodeMirror.setValue(newContent);
            } else {
                console.log('Using jQuery val()');
                $textarea.val(newContent);
            }
            showMessage('Consent-Skript am Anfang des Feldes hinzugefügt!', 'success');
            showPreview(script);
        } else {
            console.error('No script textarea found');
            showMessage('Script-Feld nicht gefunden. Bitte manuell aus Preview kopieren.', 'warning');
            showPreview(script);
        }
    });
    
    // Revoke Script generieren
    $(document).off('click.revoke').on('click.revoke', '#generate-revoke-script', function() {
        var serviceType = $('#google-helper-service').val();
        console.log('Generate revoke for:', serviceType);
        
        if (!serviceType) {
            showMessage('Bitte Service-Typ auswählen', 'warning');
            return;
        }
        
        var script = generateConsentScript(serviceType, false);
        
        // REDAXO Textarea-Selektoren für script_unselect
        var $textarea = $("textarea[name*='script_unselect'], textarea[name*='unselect']").first();
        
        if ($textarea.length === 0) {
            // Fallback Selektoren
            $textarea = $("#rex-form-script_unselect, textarea[id*='unselect'], textarea[name='REX_INPUT_VALUE[script_unselect]']").first();
        }
        
        console.log('Found unselect textarea:', $textarea.length, $textarea.attr('name') || $textarea.attr('id'));
        
        if ($textarea.length) {
            var currentContent = '';
            
            // Aktuellen Inhalt ermitteln
            if ($textarea[0].CodeMirror) {
                currentContent = $textarea[0].CodeMirror.getValue();
            } else {
                currentContent = $textarea.val();
            }
            
            // Neuen Code am Anfang hinzufügen (mit Leerzeile falls bereits Inhalt vorhanden)
            var newContent = script;
            if (currentContent.trim() !== '') {
                newContent = script + '\n\n' + currentContent;
            }
            
            // Aktualisieren
            if ($textarea[0].CodeMirror) {
                console.log('Using CodeMirror setValue');
                $textarea[0].CodeMirror.setValue(newContent);
            } else {
                console.log('Using jQuery val()');
                $textarea.val(newContent);
            }
            showMessage('Widerruf-Skript am Anfang des Feldes hinzugefügt!', 'success');
            showPreview(script);
        } else {
            console.error('No script_unselect textarea found');
            showMessage('Script-Unselect-Feld nicht gefunden. Bitte manuell aus Preview kopieren.', 'warning');
            showPreview(script);
        }
    });
    
    // Copy Button
    $(document).off('click.copy').on('click.copy', '#copy-preview-script', function() {
        var script = $('#preview-content').text();
        if (!script) {
            showMessage('Kein Skript zum Kopieren', 'warning');
            return;
        }
        
        copyToClipboard(script, $(this));
    });
    
    // Auto-Detection
    $('input[name="service_name"]').off('blur.autodetect').on('blur.autodetect', function() {
        var serviceName = $(this).val().toLowerCase().trim();
        if (!serviceName) return;
        
        console.log('Auto-detecting service:', serviceName);
        
        for (var type in mappings) {
            if (serviceName.includes(type.replace('-', '')) || 
                serviceName.includes(type.replace('google-', '')) ||
                (type === 'google-analytics' && /analytics|ga\d?/.test(serviceName)) ||
                (type === 'google-ads' && /adwords|ads|google.*ad/.test(serviceName)) ||
                (type === 'google-tag-manager' && /tag.*manager|gtm|tagmanager/.test(serviceName)) ||
                (type === 'google-tag-manager-all' && /tag.*manager.*all|gtm.*all|tagmanager.*all|all.*gtm|all.*tag.*manager/.test(serviceName)) ||
                (type === 'facebook-pixel' && /facebook.*pixel|fb.*pixel/.test(serviceName)) ||
                (type === 'youtube' && /youtube|yt/.test(serviceName)) ||
                (type === 'google-maps' && /maps|gmaps/.test(serviceName)) ||
                (type === 'matomo' && /matomo|piwik/.test(serviceName)) ||
                (type === 'matomo-self-hosted' && /matomo.*self|self.*matomo|matomo.*hosted|hosted.*matomo/.test(serviceName)) ||
                (type === 'vimeo' && /vimeo/.test(serviceName)) ||
                (type === 'pinterest-tag' && /pinterest/.test(serviceName))) {
                
                $('#google-helper-service').val(type);
                showMessage('Service "' + type + '" automatisch erkannt!', 'info');
                break;
            }
        }
    });
    
    // Hilfsfunktionen
    function showMessage(message, type) {
        // Toast oben mittig erstellen
        var toastClass = 'alert-' + type;
        var iconClass = type === 'success' ? 'fa-check-circle' : 
                       type === 'warning' ? 'fa-exclamation-triangle' : 
                       type === 'danger' ? 'fa-times-circle' : 'fa-info-circle';
        
        var $toast = $('<div class="alert ' + toastClass + ' alert-dismissible consent-helper-toast" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert">' +
            '<span>&times;</span></button>' +
            '<i class="fa ' + iconClass + '"></i> ' + message + '</div>');
        
        // CSS für Toast-Positionierung hinzufügen (einmalig)
        if ($('#consent-helper-toast-css').length === 0) {
            $('<style id="consent-helper-toast-css">' +
            '.consent-helper-toast { ' +
                'position: fixed !important; ' +
                'top: 20px !important; ' +
                'left: 50% !important; ' +
                'transform: translateX(-50%) !important; ' +
                'z-index: 9999 !important; ' +
                'min-width: 300px !important; ' +
                'max-width: 500px !important; ' +
                'box-shadow: 0 4px 8px rgba(0,0,0,0.2) !important; ' +
                'border: none !important; ' +
                'margin: 0 !important; ' +
            '}' +
            '</style>').appendTo('head');
        }
        
        // Alte Toasts entfernen
        $('.consent-helper-toast').remove();
        
        // Neuen Toast zu Body hinzufügen
        $('body').append($toast);
        
        // Slide-in Animation
        $toast.hide().slideDown(300);
        
        // Auto-hide nach 4 Sekunden
        setTimeout(function() {
            $toast.slideUp(300, function() {
                $(this).remove();
            });
        }, 4000);
    }
    
    function showPreview(script) {
        $('#preview-content').text(script);
        $('#script-preview').slideDown();
    }
    
    function copyToClipboard(text, $button) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                var originalHtml = $button.html();
                $button.html('<i class="fa fa-check"></i> Kopiert!').removeClass('btn-primary').addClass('btn-success');
                
                setTimeout(function() {
                    $button.html(originalHtml).removeClass('btn-success').addClass('btn-primary');
                }, 2000);
                
                showMessage('Skript in Zwischenablage kopiert!', 'success');
            }).catch(function(err) {
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }
    }
    
    function fallbackCopy(text) {
        var $temp = $('<textarea>').val(text).appendTo('body').select();
        try {
            document.execCommand('copy');
            showMessage('Skript in Zwischenablage kopiert!', 'success');
        } catch (err) {
            showMessage('Bitte manuell kopieren (Strg+C)', 'warning');
        }
        $temp.remove();
    }
});
