<?php

use FriendsOfRedaxo\ConsentManager\RexFormSupport;

$showlist = true;
$id = rex_request::request('id', 'int', 0);
$func = rex_request::request('func', 'string');
$csrf = rex_csrf_token::factory('consent_manager_domain');
$table = rex::getTable('consent_manager_domain');
$msg = '';
if ('delete' === $func) {
    $db = rex_sql::factory();
    $db->setTable($table);
    $db->setWhere('id = :id', ['id' => $id]);
    $db->delete();
    $msg = rex_view::success(rex_i18n::msg('consent_manager_successfully_deleted'));
} elseif ('add' === $func || 'edit' === $func) {
    $formDebug = false;
    $showlist = false;
    
    // Debug: POST-Daten anzeigen wenn Debug-Modus aktiv
    if (rex::isDebugMode() && $_SERVER['REQUEST_METHOD'] === 'POST') {
        dump('=== POST Request Data ===');
        dump($_POST);
        dump('=== auto_inject_include_templates ===');
        dump($_POST['auto_inject_include_templates'] ?? 'NOT SET');
    }
    
    $form = rex_form::factory($table, '', 'id = ' . $id, 'post', $formDebug);
    $form->addParam('id', $id);
    $form->addParam('sort', rex_request::request('sort', 'string', ''));
    $form->addParam('sorttype', rex_request::request('sorttype', 'string', ''));
    $form->addParam('start', rex_request::request('start', 'int', 0));
    $form->setApplyUrl(rex_url::currentBackendPage());

    // YRewrite Domains laden falls verfügbar
    $yrewriteDomains = [];
    $existingDomains = [];
    
    // Bereits konfigurierte Domains laden (außer der aktuellen beim Edit)
    $sql = rex_sql::factory();
    $whereClause = $form->isEditMode() ? 'id != ' . $id : '1=1';
    $sql->setQuery('SELECT uid FROM ' . rex::getTable('consent_manager_domain') . ' WHERE ' . $whereClause);
    foreach ($sql as $row) {
        $existingDomains[] = $row->getValue('uid');
    }
    
    $yrewriteSelectHtml = '';
    if (rex_addon::exists('yrewrite') && rex_addon::get('yrewrite')->isAvailable()) {
        foreach (rex_yrewrite::getDomains() as $domain) {
            $cleanDomain = preg_replace('#^https?://#i', '', $domain->getUrl());
            $cleanDomain = rtrim($cleanDomain, '/');
            $cleanDomain = strtolower($cleanDomain);
            
            // Duplikate vermeiden (z.B. wenn eine Domain sowohl als Standard als auch regulär existiert)
            if (!in_array($cleanDomain, $yrewriteDomains, true) && !in_array($cleanDomain, $existingDomains, true)) {
                $yrewriteDomains[] = $cleanDomain;
            }
        }
        
        // HTML für YRewrite Select generieren (nur im Add-Modus)
        if (!$form->isEditMode() && count($yrewriteDomains) > 0) {
            $yrewriteSelectHtml = '
            <div class="form-group">
                <label class="control-label">
                    ' . rex_i18n::msg('consent_manager_domain') . ' 
                    <small class="text-muted">(aus YRewrite wählen - ' . count($yrewriteDomains) . ' verfügbar)</small>
                </label>
                <select id="yrewrite-domain-select" class="form-control selectpicker" data-live-search="true" data-size="8">
                    <option value="">-- Oder eigene Domain eingeben --</option>';
            foreach ($yrewriteDomains as $domain) {
                $yrewriteSelectHtml .= '<option value="' . rex_escape($domain) . '">' . rex_escape($domain) . '</option>';
            }
            $yrewriteSelectHtml .= '
                </select>
                <p class="help-block"><i class="fa fa-info-circle"></i> Wählen Sie eine Domain aus YRewrite oder geben Sie eine eigene ein.</p>
            </div>';
        } elseif (!$form->isEditMode() && count($yrewriteDomains) === 0) {
            // Debug-Info wenn keine Domains verfügbar
            $debugMsg = 'YRewrite ist aktiv, aber keine Domains verfügbar. ';
            if (count($existingDomains) > 0) {
                $debugMsg .= 'Bereits konfiguriert: ' . implode(', ', $existingDomains);
            } else {
                $debugMsg .= 'Keine YRewrite-Domains angelegt.';
            }
            $yrewriteSelectHtml = '<!-- ' . $debugMsg . ' -->';
        }
    } elseif (!$form->isEditMode()) {
        // YRewrite nicht verfügbar
        $yrewriteSelectHtml = '<!-- YRewrite ist nicht installiert oder nicht aktiviert -->';
    }

    // YRewrite Select als HTML-Feld einfügen (wird nicht in DB gespeichert)
    if ($yrewriteSelectHtml !== '') {
        $field = $form->addRawField($yrewriteSelectHtml);
    }

    // Domain Panel
    $domainPanelStart = '
    <div class="panel panel-info" style="border-left: 4px solid #5bc0de; background: rgba(91, 192, 222, 0.2); margin: 20px 0; padding: 15px;">
        <div style="display: flex; align-items: start;">
            <div style="flex-shrink: 0; margin-right: 15px; font-size: 28px; color: #5bc0de; line-height: 1;">
                <i class="fa fa-globe"></i>
            </div>
            <div style="flex: 1;">
                <h4 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 600;">Domain</h4>
    ';
    $field = $form->addRawField($domainPanelStart);

    $field = $form->addTextField('uid');
    $field->setLabel(count($yrewriteDomains) > 0 && !$form->isEditMode() ? '<small class="text-muted">Oder eigene Domain eingeben:</small>' : rex_i18n::msg('consent_manager_domain'));
    $field->getValidator()->add('notEmpty', rex_i18n::msg('consent_manager_domain_empty_msg'));
    $field->getValidator()->add('custom', rex_i18n::msg('consent_manager_domain_malformed_msg'), RexFormSupport::validateHostname(...));
    $field->getValidator()->add('custom', 'Domain muss in Kleinbuchstaben eingegeben werden (z.B. "example.com" statt "Example.com")', static function ($value) {
        return RexFormSupport::validateLowercase($value);
    });
    $field->setNotice('Domain ohne Protokoll eingeben (z.B. "example.com"). Bitte nur Kleinbuchstaben verwenden.');
    $field->setAttribute('id', 'domain-uid-field');
    
    $domainPanelEnd = '
            </div>
        </div>
    </div>
    ';
    $field = $form->addRawField($domainPanelEnd);

    // Rechtliche Seiten Panel
    $legalPanelStart = '
    <div class="panel panel-info" style="border-left: 4px solid #5bc0de; background: rgba(91, 192, 222, 0.2); margin: 20px 0; padding: 15px;">
        <div style="display: flex; align-items: start;">
            <div style="flex-shrink: 0; margin-right: 15px; font-size: 28px; color: #5bc0de; line-height: 1;">
                <i class="fa fa-file-text-o"></i>
            </div>
            <div style="flex: 1;">
                <h4 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 600;">Rechtliche Seiten</h4>
    ';
    $field = $form->addRawField($legalPanelStart);
    
    $field = $form->addLinkmapField('privacy_policy');
    $field->setLabel(rex_i18n::msg('consent_manager_domain_privacy_policy')); /** @phpstan-ignore-line */
    $field->getValidator()->add('notEmpty', rex_i18n::msg('consent_manager_domain_privacy_policy_empty_msg')); /** @phpstan-ignore-line */

    $field = $form->addLinkmapField('legal_notice');
    $field->setLabel(rex_i18n::msg('consent_manager_domain_legal_notice')); /** @phpstan-ignore-line */
    $field->getValidator()->add('notEmpty', rex_i18n::msg('consent_manager_domain_legal_notic_empty_msg')); /** @phpstan-ignore-line */
    
    $legalPanelEnd = '
            </div>
        </div>
    </div>
    ';
    $field = $form->addRawField($legalPanelEnd);

    // Google Consent Mode Panel
    $googlePanelStart = '
    <div class="panel panel-primary" style="border-left: 4px solid #4285f4; background: rgba(66, 133, 244, 0.2); margin: 20px 0; padding: 15px;">
        <div style="display: flex; align-items: start;">
            <div style="flex-shrink: 0; margin-right: 15px; font-size: 28px; color: #4285f4; line-height: 1;">
                <i class="fa fa-google"></i>
            </div>
            <div style="flex: 1;">
                <h4 style="margin: 0 0 15px 0; color: #333; font-size: 16px; font-weight: 600;">Google Consent Mode v2</h4>
    ';
    $field = $form->addRawField($googlePanelStart);
    
    $field = $form->addSelectField('google_consent_mode_enabled');
    $field->setLabel(rex_i18n::msg('consent_manager_google_mode_title'));
    $select = $field->getSelect();
    $select->addOption(rex_i18n::msg('consent_manager_google_mode_disabled'), 'disabled');
    $select->addOption(rex_i18n::msg('consent_manager_google_mode_auto'), 'auto');
    $select->addOption(rex_i18n::msg('consent_manager_google_mode_manual'), 'manual');
    $field->setNotice(rex_i18n::msg('consent_manager_google_mode_notice'));

    $field = $form->addSelectField('google_consent_mode_debug');
    $field->setLabel('Debug-Modus');
    $select = $field->getSelect();
    $select->addOption('Deaktiviert', '0');
    $select->addOption('Aktiviert', '1');
    $field->setNotice('Debug-Panel im Frontend anzeigen. Zeigt Cookie-Status und Consent-Informationen für angemeldete Backend-Benutzer an.');
    
    $googlePanelEnd = '
            </div>
        </div>
    </div>
    ';
    $field = $form->addRawField($googlePanelEnd);

    // Inline-Only Mode Panel
    $inlinePanelStart = '
    <div class="panel panel-default" style="border-left: 4px solid #777; background: rgba(119, 119, 119, 0.2); margin: 20px 0; padding: 15px;">
        <div style="display: flex; align-items: start;">
            <div style="flex-shrink: 0; margin-right: 15px; font-size: 28px; color: #777; line-height: 1;">
                <i class="fa fa-eye-slash"></i>
            </div>
            <div style="flex: 1;">
                <h4 style="margin: 0 0 15px 0; color: #333; font-size: 16px; font-weight: 600;">Inline-Only Modus</h4>
    ';
    $field = $form->addRawField($inlinePanelStart);
    
    $field = $form->addSelectField('inline_only_mode');
    $field->setLabel(rex_i18n::msg('consent_manager_domain_inline_only_mode'));
    $select = $field->getSelect();
    $select->addOption(rex_i18n::msg('consent_manager_domain_inline_only_mode_disabled'), '0');
    $select->addOption(rex_i18n::msg('consent_manager_domain_inline_only_mode_enabled'), '1');
    $field->setNotice(rex_i18n::msg('consent_manager_domain_inline_only_mode_notice'));
    
    $inlinePanelEnd = '
            </div>
        </div>
    </div>
    ';
    $field = $form->addRawField($inlinePanelEnd);

    // Auto-Inject Configuration - Hervorgehoben als Panel
    $autoInjectPanelStart = '
    <div class="panel panel-warning" style="border-left: 4px solid #f0ad4e; background: rgba(240, 173, 78, 0.2); margin: 20px 0; padding: 15px; position: relative; overflow: visible; contain: layout;">
        <div style="display: flex; align-items: start;">
            <div style="flex-shrink: 0; margin-right: 15px; font-size: 28px; color: #f0ad4e; line-height: 1;">
                <i class="fa fa-plug"></i>
            </div>
            <div style="flex: 1; position: relative; overflow: visible;">
                <h4 style="margin: 0 0 15px 0; color: #333; font-size: 16px; font-weight: 600;">Automatische Frontend-Einbindung</h4>
    ';
    $field = $form->addRawField($autoInjectPanelStart);
    
    $field = $form->addSelectField('auto_inject');
    $field->setLabel('Status');
    $select = $field->getSelect();
    $select->addOption('Deaktiviert (manuelle Einbindung erforderlich)', '0');
    $select->addOption('Aktiviert (automatische Einbindung im Frontend)', '1');
    $field->setNotice('<i class="fa fa-info-circle" style="color: #f0ad4e;"></i> Wenn aktiviert, wird das Consent Manager Script automatisch im Frontend eingebunden. Keine manuelle Integration im Template erforderlich.');

    // Auto-Inject: Reload on Consent
    $field = $form->addSelectField('auto_inject_reload_on_consent');
    $field->setLabel('Seite neu laden bei Consent-Änderung');
    $select = $field->getSelect();
    $select->addOption('Nein (nur Consent-Cookie setzen)', '0');
    $select->addOption('Ja (Seite automatisch neu laden)', '1');
    $field->setNotice('Wenn aktiviert, wird die Seite automatisch neu geladen, sobald der Nutzer seine Consent-Einstellungen ändert. Empfohlen für optimale Integration von Drittanbieter-Scripts.');

    // Auto-Inject: Delay
    $field = $form->addTextField('auto_inject_delay');
    $field->setLabel('Verzögerung bis Anzeige (Sekunden)');
    $field->setNotice('Optional: Verzögerung in Sekunden bis zur Anzeige der Consent-Box (0 = sofort). Nützlich um First-Paint zu verbessern.');
    
    // Auto-Inject: Focus Management
    $field = $form->addSelectField('auto_inject_focus');
    $field->setLabel('Fokus auf Consent-Box setzen');
    $select = $field->getSelect();
    $select->addOption('Nein (kein automatischer Fokus)', '0');
    $select->addOption('Ja (Fokus für Barrierefreiheit)', '1');
    $field->setNotice('Wenn aktiviert, wird der Fokus automatisch auf die Consent-Box gesetzt (empfohlen für Barrierefreiheit gemäß WCAG).');
    
    // Auto-Inject: Template-Whitelist (Positivliste) - Multi-Select
    // Alle aktiven Templates laden
    $templateOptions = [];
    if (rex_addon::get('structure')->getPlugin('content')->isAvailable()) {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT id, name FROM ' . rex::getTable('template') . ' WHERE active = 1 ORDER BY name');
        foreach ($sql as $row) {
            $templateId = $row->getValue('id');
            $templateName = $row->getValue('name');
            $templateOptions[$templateId] = $templateName . ' [ID: ' . $templateId . ']';
        }
    }
    
    if (count($templateOptions) > 0) {
        // Aktuell gespeicherte Template-IDs laden
        $savedTemplates = [];
        if ($func === 'edit' && $id > 0) {
            $sql = rex_sql::factory();
            $sql->setQuery('SELECT auto_inject_include_templates FROM ' . rex::getTable('consent_manager_domain') . ' WHERE id = ?', [$id]);
            if ($sql->getRows() > 0) {
                $saved = $sql->getValue('auto_inject_include_templates');
                if (null !== $saved && '' !== $saved) {
                    $savedTemplates = array_map('intval', array_filter(array_map('trim', explode(',', $saved))));
                }
            }
        }
        
        // Checkbox-Liste für Templates
        $checkboxesHtml = '<div class="form-group">' .
            '<label class="control-label">Nur in diesen Templates einbinden</label>' .
            '<div style="border: 1px solid #ddd; border-radius: 4px; padding: 10px; max-height: 250px; overflow-y: auto; background-color: #fff;">' .
            '<div style="padding-bottom: 10px; border-bottom: 1px solid #eee; margin-bottom: 10px;">' .
            '<label style="font-weight: normal; margin: 0;">' .
            '<input type="checkbox" id="domain-templates-select-all" style="margin-right: 5px;"> ' .
            '<strong>Alle auswählen</strong>' .
            '</label>' .
            '</div>';
        
        // Template-Checkboxen hinzufügen
        foreach ($templateOptions as $tplId => $tplName) {
            $checked = in_array($tplId, $savedTemplates, true) ? ' checked' : '';
            $checkboxesHtml .= '<div style="padding: 3px 0;">' .
                '<label style="font-weight: normal; margin: 0;">' .
                '<input type="checkbox" class="domain-template-checkbox" value="' . $tplId . '"' . $checked . ' style="margin-right: 5px;"> ' .
                rex_escape($tplName) .
                '</label>' .
                '</div>';
        }
        
        // Initialer Wert für Hidden Field (gespeicherte Templates)
        $initialValue = count($savedTemplates) > 0 ? implode(',', $savedTemplates) : '';
        
        $checkboxesHtml .= '</div>' .
            '<p class="help-block">' .
            '<i class="fa fa-info-circle"></i> <strong>Nichts auswählen = in allen Templates einbinden</strong> (Standardverhalten). ' .
            'Template(s) auswählen = nur in diesen Templates wird der Consent Manager automatisch eingebunden. ' .
            'Sinnvoll für Websites mit vielen Spezial-Templates (API, AJAX, Print, RSS).' .
            '</p>' .
            '</div>';
        
        $field = $form->addRawField($checkboxesHtml);
        
        // Hidden Field als echtes rex_form-Feld damit es richtig gespeichert wird
        $field = $form->addHiddenField('auto_inject_include_templates');
        $field->setAttribute('id', 'domain-templates-hidden');
        if ($func === 'edit' && $id > 0) {
            $field->setValue($initialValue);
        }
    } else {
        // Fallback: Kein Template gefunden
        $field = $form->addRawField(
            '<div class="form-group">' .
            '<label class="control-label">Nur in diesen Templates einbinden</label>' .
            '<p class="help-block text-warning">' .
            '<i class="fa fa-exclamation-triangle"></i> Keine aktiven Templates gefunden. Der Consent Manager wird in allen Templates eingebunden.' .
            '</p>' .
            '</div>'
        );
    }
    
    // Auto-Inject Panel Ende
    $autoInjectPanelEnd = '
            </div>
        </div>
    </div>
    ';
    $field = $form->addRawField($autoInjectPanelEnd);

    // Theme als Hidden Field (wird in Sidebar gesteuert)
    $field = $form->addHiddenField('theme');

    // oEmbed / CKE5 Video Configuration - nur anzeigen wenn CKE5 verfügbar ist
    if (rex_addon::exists('cke5') && rex_addon::get('cke5')->isAvailable()) {
        $oembedPanelStart = '
        <div class="panel panel-primary" style="border-left: 4px solid #9b59b6; background: rgba(155, 89, 182, 0.2); margin: 20px 0; padding: 15px;">
            <div style="display: flex; align-items: start;">
                <div style="flex-shrink: 0; margin-right: 15px; font-size: 28px; color: #9b59b6; line-height: 1;">
                    <i class="fa fa-video-camera"></i>
                </div>
                <div style="flex: 1;">
                    <h4 style="margin: 0 0 15px 0; color: #333; font-size: 16px; font-weight: 600;">CKE5 oEmbed Integration</h4>
        ';
        $field = $form->addRawField($oembedPanelStart);
        
        $field = $form->addSelectField('oembed_enabled');
        $field->setLabel('Status');
        $select = $field->getSelect();
        $select->addOption('Aktiviert (automatische Umwandlung)', '1');
        $select->addOption('Deaktiviert (keine automatische Umwandlung)', '0');
        $field->setNotice('Legt fest, ob CKE5 oEmbed-Tags (YouTube, Vimeo) automatisch in Consent-Blocker umgewandelt werden.');

        $field = $form->addTextField('oembed_video_width');
        $field->setLabel('Video-Breite (px)');
        $field->setNotice('Standard-Breite für CKE5 oEmbed-Videos (Default: 640)');

        $field = $form->addTextField('oembed_video_height');
        $field->setLabel('Video-Höhe (px)');
        $field->setNotice('Standard-Höhe für CKE5 oEmbed-Videos (Default: 360)');

        $field = $form->addSelectField('oembed_show_allow_all');
        $field->setLabel('Drei-Button-Variante');
        $select = $field->getSelect();
        $select->addOption('Zwei Buttons (Einmal laden, Alle Einstellungen)', '0');
        $select->addOption('Drei Buttons (Einmal laden, Alle zulassen, Alle Einstellungen)', '1');
        $field->setNotice('Drei-Button-Variante zeigt zusätzlich "Alle zulassen" Button zum sofortigen Freischalten aller Services einer Gruppe.');
        
        $oembedPanelEnd = '
                </div>
            </div>
        </div>
        ';
        $field = $form->addRawField($oembedPanelEnd);
    }

    $title = $form->isEditMode() ? rex_i18n::msg('consent_manager_domain_edit') : rex_i18n::msg('consent_manager_domain_add');
    
    $formContent = $form->get();
    
    // JavaScript: Checkbox-Liste → kommagetrennte Liste beim Submit
    $formContent .= '
    <script nonce="' . rex_response::getNonce() . '">
    (function() {
        // "Alle auswählen" Checkbox-Logik
        var selectAllCheckbox = document.getElementById(\'domain-templates-select-all\');
        var templateCheckboxes = document.querySelectorAll(\'.domain-template-checkbox\');
        
        if (selectAllCheckbox && templateCheckboxes.length > 0) {
            // "Alle auswählen" Checkbox Event
            selectAllCheckbox.addEventListener(\'change\', function() {
                templateCheckboxes.forEach(function(checkbox) {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            });
            
            // Einzelne Checkbox Events: "Alle auswählen" aktualisieren
            templateCheckboxes.forEach(function(checkbox) {
                checkbox.addEventListener(\'change\', function() {
                    var allChecked = Array.from(templateCheckboxes).every(function(cb) {
                        return cb.checked;
                    });
                    selectAllCheckbox.checked = allChecked;
                });
            });
            
            // Initial state: "Alle auswählen" prüfen
            var allChecked = Array.from(templateCheckboxes).every(function(cb) {
                return cb.checked;
            });
            selectAllCheckbox.checked = allChecked;
        }
        
        // Template Checkboxes: Werte sammeln beim Submit
        // Suche Formular (rex_form kann verschiedene Namen generieren)
        var form = document.querySelector(\'form[name="rex-form-consent_manager_domain"]\') || 
                   document.querySelector(\'form\');
        
        console.log(\'[Domain Templates Init]\', {
            form: form,
            formName: form ? form.getAttribute(\'name\') : \'NOT FOUND\',
            checkboxCount: templateCheckboxes.length
        });
        
        if (form) {
            // Aktualisiere Hidden Field bei jeder Checkbox-Änderung
            var updateHiddenField = function() {
                var hiddenField = document.getElementById(\'domain-templates-hidden\');
                var checkboxes = document.querySelectorAll(\'.domain-template-checkbox:checked\');
                
                if (hiddenField) {
                    var selectedIds = [];
                    checkboxes.forEach(function(checkbox) {
                        selectedIds.push(checkbox.value);
                    });
                    var newValue = selectedIds.join(\',\');
                    hiddenField.value = newValue;
                    
                    console.log(\'[Domain Template Update]\', {
                        checkedCount: checkboxes.length,
                        selectedIds: selectedIds,
                        hiddenFieldValue: newValue
                    });
                }
            };
            
            // Bei jeder Checkbox-Änderung Hidden Field aktualisieren
            templateCheckboxes.forEach(function(checkbox) {
                checkbox.addEventListener(\'change\', updateHiddenField);
            });
            
            // Initial einmal aufrufen
            updateHiddenField();
            
            // Zusätzlich beim Submit nochmal aufrufen
            form.addEventListener(\'submit\', function(e) {
                updateHiddenField();
                console.log(\'[Domain Template Submit] Final value:\', document.getElementById(\'domain-templates-hidden\').value);
            }, false);
        }
    })();
    </script>
    ';
    
    // Theme-Sidebar erstellen
    $addon = rex_addon::get('consent_manager');
    $cmtheme = new \FriendsOfRedaxo\ConsentManager\Theme();
    $currentTheme = $form->isEditMode() ? $form->getSql()->getValue('theme') : '';
    $previewId = 'theme-preview-' . uniqid();
    $initialDisplay = !empty($currentTheme) ? 'block' : 'none';
    $previewBaseUrl = rex_url::backendPage('consent_manager/theme');
    
    // Theme-Optionen sammeln
    $themeOptions = '<option value="">Standard (globales Theme verwenden)</option>';
    
    $themes = (array) glob($addon->getPath('scss/themes/*.scss'));
    $legacyThemes = (array) glob($addon->getPath('scss/consent_manager_frontend*.scss'));
    $themes = array_merge($themes, $legacyThemes);
    natsort($themes);
    
    foreach ($themes as $themefile) {
        $isLegacy = strpos($themefile, '/themes/') === false;
        $themeid = $isLegacy ? basename((string) $themefile) : 'themes/' . basename((string) $themefile);
        $theme_options = $cmtheme->getThemeInformation($themeid);
        if (count($theme_options) > 0) {
            $label = $theme_options['name'] . ' (' . $theme_options['style'] . ')';
            $selected = ($currentTheme === $themeid) ? ' selected' : '';
            $themeOptions .= '<option value="' . rex_escape($themeid) . '"' . $selected . '>' . rex_escape($label) . '</option>';
        }
    }
    
    if (true === rex_addon::exists('project')) {
        $projectThemes = (array) glob(rex_addon::get('project')->getPath('consent_manager_themes/*.scss'));
        natsort($projectThemes);
        foreach ($projectThemes as $themefile) {
            $themeid = 'project:' . basename((string) $themefile);
            $theme_options = $cmtheme->getThemeInformation($themeid);
            if (count($theme_options) > 0) {
                $label = '⭐ ' . $theme_options['name'] . ' (Custom)';
                $selected = ($currentTheme === $themeid) ? ' selected' : '';
                $themeOptions .= '<option value="' . rex_escape($themeid) . '"' . $selected . '>' . rex_escape($label) . '</option>';
            }
        }
    }
    
    $sidebar = '
    <style>
    .cm-domain-layout {
        display: flex;
        gap: 20px;
    }
    .cm-domain-main {
        flex: 1;
        min-width: 0;
    }
    .cm-domain-sidebar {
        width: 320px;
        flex-shrink: 0;
    }
    .cm-sidebar-panel {
        background: rgba(255,255,255,0.1);
        border: 2px solid rgba(0,0,0,0.1);
        border-radius: 6px;
        padding: 18px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05),
                    0 1px 3px rgba(0,0,0,0.08);
    }
    .cm-sidebar-title {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 12px;
        opacity: 0.9;
        letter-spacing: 0.3px;
    }
    .cm-theme-select {
        width: 100%;
        padding: 8px;
        border-radius: 4px;
        margin-bottom: 15px;
    }
    .cm-domain-theme-preview {
        display: ' . $initialDisplay . ';
    }
    .cm-domain-theme-preview-title {
        font-weight: 600;
        margin-bottom: 10px;
        font-size: 12px;
        opacity: 0.8;
        letter-spacing: 0.2px;
    }
    .cm-domain-theme-preview-container {
        position: relative;
        width: 100%;
        padding-bottom: 62.5%;
        background: rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 4px;
        overflow: hidden;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.06);
        margin-bottom: 12px;
    }
    .cm-domain-theme-preview-iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 1440px;
        height: 900px;
        border: 0;
        transform-origin: 0 0;
        pointer-events: none;
    }
    @media (max-width: 992px) {
        .cm-domain-layout {
            flex-direction: column;
        }
        .cm-domain-sidebar {
            width: 100%;
        }
    }
    </style>
    <div class="cm-sidebar-panel">
        <div class="cm-sidebar-title">' . rex_i18n::msg('consent_manager_domain_theme_title') . '</div>
        <select class="cm-theme-select" id="cm-theme-select-' . $previewId . '">
            ' . $themeOptions . '
        </select>
        <p style="font-size: 11px; margin: 0 0 15px 0;">' . rex_i18n::msg('consent_manager_domain_theme_notice') . '</p>
        
        <div class="cm-domain-theme-preview" id="' . $previewId . '">
            <div class="cm-domain-theme-preview-title">' . rex_i18n::msg('consent_manager_domain_theme_preview_title') . '</div>
            <div class="cm-domain-theme-preview-container">
                <iframe
                    class="cm-domain-theme-preview-iframe"
                    id="' . $previewId . '-iframe"
                ></iframe>
            </div>
            <a href="#" id="' . $previewId . '-link" target="_blank" class="btn btn-xs btn-default btn-block" style="font-size: 11px;">
                <i class="fa fa-external-link"></i> ' . rex_i18n::msg('consent_manager_domain_theme_preview_fullscreen') . '
            </a>
        </div>
    </div>
    <script nonce="' . rex_response::getNonce() . '">
    (function() {
        var previewContainer = document.getElementById("' . $previewId . '");
        var previewIframe = document.getElementById("' . $previewId . '-iframe");
        var previewLink = document.getElementById("' . $previewId . '-link");
        var themeSelect = document.getElementById("cm-theme-select-' . $previewId . '");
        var hiddenThemeInput = document.querySelector(\'input[type="hidden"][id*="theme"]\');
        var baseUrl = "' . $previewBaseUrl . '";
        
        console.log("Theme Preview Init", {
            previewContainer: !!previewContainer,
            previewIframe: !!previewIframe,
            themeSelect: !!themeSelect,
            baseUrl: baseUrl
        });
        
        function scalePreview() {
            if (previewIframe && previewContainer) {
                var container = previewContainer.querySelector(".cm-domain-theme-preview-container");
                if (container) {
                    var containerWidth = container.offsetWidth;
                    var scale = containerWidth / 1440;
                    previewIframe.style.transform = "scale(" + scale + ")";
                }
            }
        }
        
        function updatePreview() {
            var theme = themeSelect ? themeSelect.value : "";
            console.log("updatePreview called", theme);
            
            // Hidden Input synchronisieren
            if (hiddenThemeInput) {
                hiddenThemeInput.value = theme;
            }
            
            if (theme) {
                var url = baseUrl + "&preview=" + encodeURIComponent(theme);
                console.log("Loading preview URL:", url);
                previewIframe.src = url;
                previewLink.href = url;
                previewContainer.style.display = "block";
                setTimeout(scalePreview, 100);
            } else {
                previewIframe.src = "";
                previewContainer.style.display = "none";
            }
        }
        
        // Wait for rex:ready if PJAX is active
        function init() {
            console.log("Theme Preview init() called");
            // Suche erneut nach hidden input (könnte durch PJAX neu geladen sein)
            hiddenThemeInput = document.querySelector(\'input[type="hidden"][id*="theme"]\');
            if (themeSelect) {
                console.log("ThemeSelect found, current value:", themeSelect.value);
                updatePreview();
                themeSelect.addEventListener("change", updatePreview);
                window.addEventListener("resize", scalePreview);
            } else {
                console.error("ThemeSelect not found!");
            }
        }
        
        // Immer sowohl direkt als auch per rex:ready aufrufen
        if (typeof jQuery !== "undefined") {
            jQuery(document).on("rex:ready", function() {
                console.log("rex:ready triggered");
                init();
            });
        }
        
        // Auch direkt aufrufen für Nicht-PJAX-Szenario
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", init);
        } else {
            init();
        }
    })();
    </script>';
    
    // YRewrite Domain Select → TextField Sync Script
    if (!$form->isEditMode() && count($yrewriteDomains) > 0) {
        $sidebar .= '
        <script nonce="' . rex_response::getNonce() . '">
        (function() {
            function initYRewriteSync() {
                var yrewriteSelect = document.getElementById("yrewrite-domain-select");
                var domainField = document.getElementById("domain-uid-field");
                
                if (yrewriteSelect && domainField) {
                    // Bootstrap Selectpicker initialisieren falls verfügbar
                    if (typeof jQuery !== "undefined" && jQuery.fn.selectpicker) {
                        jQuery(yrewriteSelect).selectpicker({
                            liveSearch: true,
                            size: 8
                        });
                    }
                    
                    // Sync: Select → TextField
                    yrewriteSelect.addEventListener("change", function() {
                        if (this.value) {
                            domainField.value = this.value;
                            domainField.focus();
                        }
                    });
                    
                    // Wenn TextField geändert wird, Select zurücksetzen
                    domainField.addEventListener("input", function() {
                        if (yrewriteSelect.value !== this.value) {
                            yrewriteSelect.value = "";
                            if (typeof jQuery !== "undefined" && jQuery.fn.selectpicker) {
                                jQuery(yrewriteSelect).selectpicker("refresh");
                            }
                        }
                    });
                }
            }
            
            // Init mit rex:ready und DOMContentLoaded
            if (typeof jQuery !== "undefined") {
                jQuery(document).on("rex:ready", initYRewriteSync);
            }
            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", initYRewriteSync);
            } else {
                initYRewriteSync();
            }
        })();
        </script>';
    }
    
    // 2-Spalten-Layout
    $content = '
    <div class="cm-domain-layout">
        <div class="cm-domain-main">' . $formContent . '</div>
        <div class="cm-domain-sidebar">' . $sidebar . '</div>
    </div>';

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', $title);
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
}
echo $msg;
if ($showlist) {
    // Setup Wizard Button
    echo '
    <style nonce="' . rex_response::getNonce() . '">
        .domain-wizard-btn {
            padding: 15px 30px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
            overflow: visible;
        }
        
        .domain-wizard-btn::before {
            content: "";
            position: absolute;
            top: -3px;
            left: -3px;
            right: -3px;
            bottom: -3px;
            background: linear-gradient(90deg, #337ab7, #5bc0de, #5cb85c, #337ab7);
            background-size: 300% 300%;
            border-radius: 10px;
            z-index: -1;
            opacity: 0;
            animation: gradient-border 4s ease infinite;
            transition: opacity 0.3s ease;
        }
        
        .domain-wizard-btn:hover::before {
            opacity: 1;
        }
        
        .domain-wizard-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(51, 122, 183, 0.3);
        }
        
        @keyframes gradient-border {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Dark Mode Support */
        body.rex-theme-dark button.domain-wizard-btn,
        body.rex-theme-dark button.domain-wizard-btn * {
            color: #ffffff !important;
        }
        
        @media (prefers-color-scheme: dark) {
            body:not(.rex-theme-light) button.domain-wizard-btn,
            body:not(.rex-theme-light) button.domain-wizard-btn * {
                color: #ffffff !important;
            }
        }
    </style>
    <div style="text-align: right; margin-bottom: 20px;">
        <button type="button" class="btn btn-primary btn-lg domain-wizard-btn" data-toggle="modal" data-target="#setup-wizard-modal">
            <i class="rex-icon fa-magic" style="margin-right: 10px;"></i>
            <strong>Setup Wizard</strong>
            <i class="rex-icon fa-chevron-right" style="margin-left: 10px; font-size: 14px; opacity: 0.8;"></i>
        </button>
    </div>';
    
    $listDebug = false;

    // oembed_enabled nur laden wenn CKE5 verfügbar ist
    $oembedColumn = (rex_addon::exists('cke5') && rex_addon::get('cke5')->isAvailable()) ? ', oembed_enabled' : '';
    $sql = 'SELECT id, uid, google_consent_mode_enabled, google_consent_mode_debug, inline_only_mode' . $oembedColumn . ' FROM ' . $table . ' ORDER BY uid ASC';

    $list = rex_list::factory($sql, 100, '', $listDebug);
    $list->addParam('page', rex_be_controller::getCurrentPage());
    $list->addTableAttribute('class', 'table table-striped table-hover consent_manager-table consent_manager-table-cookiegroup');

    $list->removeColumn('id');
    $list->setColumnLabel('uid', rex_i18n::msg('consent_manager_domain'));
    $list->setColumnParams('uid', ['func' => 'edit', 'id' => '###id###']);
    $list->setColumnSortable('uid');

    // Google Consent Mode Status Spalte
    $list->setColumnLabel('google_consent_mode_enabled', 'Google Consent Mode v2');
    $list->setColumnFormat('google_consent_mode_enabled', 'custom', static function ($params) {
        $value = $params['value'];
        switch ($value) {
            case 'disabled':
                return '<span class="label label-default">❌ ' . rex_i18n::msg('consent_manager_google_mode_disabled') . '</span>';
            case 'auto':
                return '<span class="label label-success">🔄 ' . rex_i18n::msg('consent_manager_google_mode_auto') . '</span>';
            case 'manual':
                return '<span class="label label-info">⚙️ ' . rex_i18n::msg('consent_manager_google_mode_manual') . '</span>';
            default:
                return '<span class="label label-default">❌ ' . rex_i18n::msg('consent_manager_google_mode_disabled') . '</span>';
        }
    });

    // Debug Status Spalte
    $list->setColumnLabel('google_consent_mode_debug', '🔍 Debug');
    $list->setColumnFormat('google_consent_mode_debug', 'custom', static function ($params) {
        $value = (int) $params['value'];
        if (1 === $value) {
            return '<span class="label label-success"><i class="fa fa-bug" style="color: #000; margin-right: 5px;"></i>Aktiv</span>';
        }
        return '<span class="label label-default"><i class="fa fa-bug" style="color: #000; margin-right: 5px;"></i>Deaktiviert</span>';
    });

    // Inline-Only Mode Status Spalte
    $list->setColumnLabel('inline_only_mode', '📱 Inline-Only');
    $list->setColumnFormat('inline_only_mode', 'custom', static function ($params) {
        $value = (int) $params['value'];
        if (1 === $value) {
            return '<span class="label label-info"><i class="fa fa-hand-pointer-o" style="margin-right: 5px;"></i>' . rex_i18n::msg('consent_manager_domain_inline_only_mode_enabled') . '</span>';
        }
        return '<span class="label label-default"><i class="fa fa-window-maximize" style="margin-right: 5px;"></i>' . rex_i18n::msg('consent_manager_domain_inline_only_mode_disabled') . '</span>';
    });

    // Auto-Inject Status Spalte
    $list->setColumnLabel('auto_inject', '🚀 Auto-Inject');
    $list->setColumnFormat('auto_inject', 'custom', static function ($params) {
        $value = (int) $params['value'];
        if (1 === $value) {
            return '<span class="label label-success"><i class="fa fa-magic" style="margin-right: 5px;"></i>Aktiviert</span>';
        }
        return '<span class="label label-default"><i class="fa fa-code" style="margin-right: 5px;"></i>Manuell</span>';
    });

    // oEmbed Status Spalte - nur anzeigen wenn CKE5 verfügbar ist
    if (rex_addon::exists('cke5') && rex_addon::get('cke5')->isAvailable()) {
        $list->setColumnLabel('oembed_enabled', '🎬 oEmbed');
        $list->setColumnFormat('oembed_enabled', 'custom', static function ($params) {
            $value = (int) ($params['value'] ?? 1); // Default: aktiviert
            if (1 === $value) {
                return '<span class="label label-success">✅ Aktiv</span>';
            }
            return '<span class="label label-default">🚫 Deaktiviert</span>';
        });
    }

    $tdIcon = '<i class="fa fa-coffee"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '"' . rex::getAccesskey(rex_i18n::msg('add'), 'add') . '><i class="rex-icon rex-icon-add"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'id' => '###id###']);

    $list->addColumn(rex_i18n::msg('function'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('function'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('function'), ['id' => '###id###', 'func' => 'edit', 'start' => rex_request::request('start', 'string')]);

    $list->addColumn(rex_i18n::msg('delete'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
    $list->setColumnLayout(rex_i18n::msg('delete'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('delete'), ['id' => '###id###', 'func' => 'delete'] + $csrf->getUrlParams());
    $list->addLinkAttribute(rex_i18n::msg('delete'), 'onclick', 'return confirm(\' ###uid### ' . rex_i18n::msg('delete') . ' ?\')');

    $content = $list->get();

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('consent_manager_domains'));
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}

// Setup Wizard Modal einbinden
$wizardFragment = new rex_fragment();
echo $wizardFragment->parse('ConsentManager/setup_wizard.php');
