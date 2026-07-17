<?php

use FriendsOfRedaxo\ConsentManager\Cache;
use FriendsOfRedaxo\ConsentManager\CLang;
use FriendsOfRedaxo\ConsentManager\RexFormSupport;
use FriendsOfRedaxo\ConsentManager\RexListSupport;
use FriendsOfRedaxo\ConsentManager\UidRenameWorkflow;
use FriendsOfRedaxo\ConsentManager\Utility;

$showlist = true;
$pid = rex_request::request('pid', 'int', 0);
$func = rex_request::request('func', 'string');
$csrf = rex_csrf_token::factory('consent_manager_cookiegroup');
$clang_id = (int) str_replace('clang', '', rex_be_controller::getCurrentPagePart(3) ?? '');
$table = rex::getTable('consent_manager_cookiegroup');
$languageCustomServicesEnabled = (bool) rex_addon::get('consent_manager')->getConfig('cookiegroup_language_custom_services_enabled', true);
$hasCookieModeColumns = false;
$columnCheckSql = rex_sql::factory();
$columnCheckSql->setQuery('SHOW COLUMNS FROM ' . $table . ' LIKE ?', ['cookie_mode']);
if ($columnCheckSql->getRows() > 0) {
    $columnCheckSql->setQuery('SHOW COLUMNS FROM ' . $table . ' LIKE ?', ['cookie_custom']);
    $hasCookieModeColumns = $columnCheckSql->getRows() > 0;
}
$msg = '';
$startClangId = rex_clang::getStartId();
$renameOpen = false;
$renameMode = '';
$renameResult = null;
$renameOldUid = '';
$renamePid = rex_request::request('rename_pid', 'int', 0);
$approvedDryrunToken = '';
$buildRenameToken = static function (string $oldUid, string $newUid, bool $updateConsentLogs): string {
    return hash('sha256', 'cookiegroup|' . $oldUid . '|' . $newUid . '|' . ((int) $updateConsentLogs));
};

if ('uid_rename_open' === $func) {
    if ($renamePid > 0) {
        $uidSql = rex_sql::factory();
        $uidSql->setQuery('SELECT uid FROM ' . $table . ' WHERE pid = ? LIMIT 1', [$renamePid]);
        if ($uidSql->getRows() > 0) {
            $renameOldUid = (string) $uidSql->getValue('uid');
            $renameOpen = true;
        }
    }
    $func = '';
}

if ('uid_rename_dryrun' === $func || 'uid_rename_apply' === $func) {
    if (!$csrf->isValid()) {
        $msg = rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } elseif ($clang_id !== $startClangId) {
        $msg = rex_view::error(rex_i18n::msg('consent_manager_uid_primary_only_notice'));
    } else {
        $renameOldUid = rex_request::post('old_uid', 'string', '');
        $renameNewUid = rex_request::post('new_uid', 'string', '');
        $updateConsentLogs = (bool) rex_request::post('update_consent_logs', 'int', 1);
        $expectedToken = $buildRenameToken($renameOldUid, $renameNewUid, $updateConsentLogs);
        $postedDryrunToken = rex_request::post('dryrun_token', 'string', '');

        if ('uid_rename_dryrun' === $func) {
            $renameResult = UidRenameWorkflow::dryRun('cookiegroup', $renameOldUid, $renameNewUid);
            $renameMode = 'dryrun';
            if (is_array($renameResult) && ($renameResult['ok'] ?? false)) {
                $approvedDryrunToken = $expectedToken;
            }
        } else {
            if ($postedDryrunToken !== $expectedToken) {
                $renameResult = UidRenameWorkflow::dryRun('cookiegroup', $renameOldUid, $renameNewUid);
                $renameMode = 'dryrun';
                if (is_array($renameResult) && ($renameResult['ok'] ?? false)) {
                    $approvedDryrunToken = $expectedToken;
                }
                $msg = rex_view::error('Vor der Umbenennung muss ein Dry-Run fuer genau diesen Schluessel ausgefuehrt werden. Bitte Hinweise und moegliche Nacharbeit pruefen.');
                $renameOpen = true;
                $func = '';
            } else {
                $renameResult = UidRenameWorkflow::apply('cookiegroup', $renameOldUid, $renameNewUid, $updateConsentLogs);
                $renameMode = 'apply';
            }
        }

        if ('' === $msg) {
            if (is_array($renameResult) && ($renameResult['ok'] ?? false)) {
                $msg = rex_view::success('dryrun' === $renameMode ? 'Dry-Run erfolgreich. Bitte Hinweise pruefen.' : 'Umbenennung erfolgreich ausgefuehrt.');
            } else {
                $msg = rex_view::error('Dry-Run/Umbenennung fehlgeschlagen. Details siehe Dialog.');
            }
        }

        $renameOpen = true;
    }
    $func = '';
}

if ('add' === $func && $clang_id !== $startClangId) {
    header('Location: ' . rex_url::backendPage('consent_manager/cookiegroup/clang' . $startClangId, ['func' => 'add', 'uid_primary_only' => 1]));
    exit;
}

if (1 === rex_request::request('uid_primary_only', 'int', 0)) {
    $msg .= rex_view::warning(rex_i18n::msg('consent_manager_uid_primary_only_notice'));
}
if ('delete' === $func) {
    $msg = CLang::deleteDataset($table, $pid);
} elseif ('duplicate' === $func) {
    // Cookie-Gruppe duplizieren
    if (!$csrf->isValid()) {
        $msg = rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } else {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . $table . ' WHERE pid = ?', [$pid]);

        if (1 === $sql->getRows()) {
            $newSql = rex_sql::factory();
            $newSql->setTable($table);

            // Alle Felder kopieren außer pid
            foreach ($sql->getFieldnames() as $fieldname) {
                if ('pid' !== $fieldname) {
                    $newSql->setValue($fieldname, $sql->getValue($fieldname));
                }
            }

            // UID und Name anpassen
            $originalUid = $sql->getValue('uid');
            $originalName = $sql->getValue('name');
            $counter = 1;
            $newUid = $originalUid . '-copy';

            $idSql = rex_sql::factory();
            $idSql->setTable($table);
            $newSql->setValue('id', $idSql->setNewId('id', 1));

            // Prüfen ob UID bereits existiert, dann Suffix erhöhen
            $checkSql = rex_sql::factory();
            while (true) {
                $checkSql->setQuery('SELECT pid FROM ' . $table . ' WHERE uid = ? AND clang_id = ?', [$newUid, $clang_id]);
                if (0 === $checkSql->getRows()) {
                    break;
                }
                ++$counter;
                $newUid = $originalUid . '-copy-' . $counter;
            }

            $newSql->setValue('uid', $newUid);
            $newSql->setValue('name', $originalName . ' (Kopie)');
            $newSql->setValue('createdate', date('Y-m-d H:i:s'));
            $newSql->setValue('updatedate', date('Y-m-d H:i:s'));

            try {
                $newSql->insert();
                $newPid = $newSql->getLastId();
                Cache::forceWrite();

                // Zur Edit-Seite des neuen Eintrags weiterleiten mit Hinweis
                $msg = rex_view::warning(rex_i18n::msg('consent_manager_cookiegroup_duplicated_edit_uid'));
                // Redirect zur Edit-Seite
                header('Location: ' . rex_url::currentBackendPage(['func' => 'edit', 'pid' => $newPid, 'msg' => 'duplicated']));
                exit;
            } catch (rex_sql_exception $e) {
                $msg = rex_view::error(rex_i18n::msg('consent_manager_cookiegroup_duplicate_error') . ': ' . $e->getMessage());
            }
        } else {
            $msg = rex_view::error(rex_i18n::msg('consent_manager_cookiegroup_not_found'));
        }
    }
} elseif ('add' === $func || 'edit' === $func) {
    $formDebug = false;
    $showlist = false;

    // Warnung anzeigen wenn von duplicate weitergeleitet (nur einmalig)
    if ('duplicated' === rex_request::request('msg', 'string', '')) {
        echo rex_view::warning(
            '<strong>' . rex_i18n::msg('consent_manager_cookiegroup_duplicated_edit_uid_title') . '</strong><br>' .
            rex_i18n::msg('consent_manager_cookiegroup_duplicated_edit_uid_desc'),
        );

        // URL ohne msg-Parameter neu laden um Reload-Sperre zu aktivieren
        echo '<script nonce="' . rex_response::getNonce() . '">';
        echo 'if (window.history.replaceState) {';
        echo '  var url = new URL(window.location);';
        echo '  url.searchParams.delete("msg");';
        echo '  window.history.replaceState({}, document.title, url);';
        echo '}';
        echo '</script>';
    }

    $form = rex_form::factory($table, '', 'pid = ' . $pid, 'post', $formDebug);
    $form->addParam('pid', $pid);
    $form->addParam('sort', rex_request::request('sort', 'string', ''));
    $form->addParam('sorttype', rex_request::request('sorttype', 'string', ''));
    $form->addParam('start', rex_request::request('start', 'int', 0));
    $form->setApplyUrl(rex_url::currentBackendPage());
    $form->addHiddenField('clang_id', $clang_id);
    RexFormSupport::getId($form, $table);

    $startClang = rex_clang::get(rex_clang::getStartId());
    $startLanguageName = null !== $startClang ? $startClang->getName() : 'Startsprache';
    $currentClang = rex_clang::get($clang_id);
    $currentLanguageName = null !== $currentClang ? $currentClang->getName() : $startLanguageName;

    $db = rex_sql::factory();
    $db->setTable(rex::getTable('consent_manager_domain'));
    $db->select('id,uid');
    $domains = $db->getArray();

    if (!$form->isEditMode()) {
        $form->addFieldset(rex_i18n::msg('consent_manager_general'));

        $field = $form->addTextField('uid');
        $field->setLabel(rex_i18n::msg('consent_manager_uid'));
        $field->getValidator()->add('notEmpty', rex_i18n::msg('consent_manager_uid_empty_msg'));
        $field->getValidator()->add('match', rex_i18n::msg('consent_manager_uid_malformed_msg'), '/^[a-z0-9-]+$/');

        $field = $form->addCheckboxField('required');
        $field->addOption(rex_i18n::msg('consent_manager_cookiegroup_required'), 1);

        $field = $form->addPrioField('prio');
        $field->setWhereCondition('clang_id = ' . $clang_id);
        $field->setLabel(rex_i18n::msg('consent_manager_prio'));
        $field->setLabelField('uid');

        if (count($domains) > 0) {
            $form->addFieldset(rex_i18n::msg('consent_manager_domain'));
            $field = $form->addCheckboxField('domain');
            $field->setLabel(rex_i18n::msg('consent_manager_domain'));
            foreach ($domains as $v) {
                $field->addOption((string) $v['uid'], (int) $v['id']);
            }
        }
    } else {
        $isPrimaryLanguage = $clang_id === rex_clang::getStartId();

        $form->addFieldset(rex_i18n::msg('consent_manager_general'));

        $field = $form->addReadOnlyField('uid_readonly', (string) $form->getSql()->getValue('uid'));
        $field->setLabel(rex_i18n::msg('consent_manager_uid'));
        $form->addHiddenField('uid', (string) $form->getSql()->getValue('uid'));

        if ($isPrimaryLanguage) {
            $field = $form->addCheckboxField('required');
            $field->addOption(rex_i18n::msg('consent_manager_cookiegroup_required'), 1);
        } else {
            $form->addRawField(RexFormSupport::getFakeCheckbox('', [[$form->getSql()->getValue('required'), rex_i18n::msg('consent_manager_cookiegroup_required')]])); /** @phpstan-ignore-line */
        }

        $checkboxes = [];
        $checkedBoxes = array_filter(explode('|', (string) $form->getSql()->getValue('domain')), static function ($value) {
            return '' !== $value;
        });
        foreach ($domains as $v) {
            $checked = (in_array((string) $v['id'], $checkedBoxes, true)) ? '|1|' : '';
            $checkboxes[] = [$checked, $v['uid']];
        }
        if (count($checkboxes) > 0) {
            $form->addFieldset(rex_i18n::msg('consent_manager_domain'));
            if ($isPrimaryLanguage) {
                $field = $form->addCheckboxField('domain');
                $field->setLabel(rex_i18n::msg('consent_manager_domain'));
                foreach ($domains as $v) {
                    $field->addOption((string) $v['uid'], (int) $v['id']);
                }
            } else {
                $form->addRawField(RexFormSupport::getFakeCheckbox(rex_i18n::msg('consent_manager_domain'), $checkboxes)); /** @phpstan-ignore-line */
            }
        }
    }

    if ($form->isEditMode() && $clang_id !== rex_clang::getStartId()) {
         $form->addFieldset(rex_i18n::msg('consent_manager_general'));
    }

    $field = $form->addTextField('name');
    $field->setLabel(rex_i18n::msg('consent_manager_name'));
    $field->getValidator()->add('notEmpty', rex_i18n::msg('consent_manager_uid_empty_msg'));

    $field = $form->addTextAreaField('description');
    $field->setLabel(rex_i18n::msg('consent_manager_description'));
    $field->getValidator()->add('notEmpty', rex_i18n::msg('consent_manager_uid_empty_msg'));

    $db = rex_sql::factory();
    $db->setTable(rex::getTable('consent_manager_cookie'));
    $db->setWhere('clang_id = ' . $clang_id . ' AND uid NOT IN ("consent_manager", "consentmanager") ORDER BY uid ASC');
    $db->select('DISTINCT pid, uid, service_name, variant');
    $cookies = $db->getArray();

    $primaryCookieSelectionByUid = [];
    if ($clang_id !== rex_clang::getStartId()) {
        $dbPrimaryCookiegroup = rex_sql::factory();
        $dbPrimaryCookiegroup->setTable($table);
        $dbPrimaryCookiegroup->setWhere('clang_id = ' . rex_clang::getStartId());
        $dbPrimaryCookiegroup->select('uid,cookie');
        foreach ($dbPrimaryCookiegroup->getArray() as $row) {
            $primaryCookieSelectionByUid[(string) ($row['uid'] ?? '')] = (string) ($row['cookie'] ?? '');
        }
    }

    if ($clang_id === rex_clang::getStartId() || true !== $form->isEditMode()) {
        if ([] !== $cookies) {
            $form->addFieldset(rex_i18n::msg('consent_manager_cookies'));

            // Styling und Buttons
            $html = '<div style="display: flex; justify-content: center; margin-bottom: 5px;">';
            $html .= '<div id="cm-cookie-toolbar" class="input-group input-group-xs" style="width: auto;">';
            $html .= '<span class="input-group-btn">';
            $html .= '<button type="button" class="btn btn-info cm-select-all"><i class="fa fa-check-square-o"></i> Alle auswählen</button>';
            $html .= '<button type="button" class="btn btn-default cm-deselect-all"><i class="fa fa-square-o"></i> Alle abwählen</button>';
            $html .= '</span>';
            $html .= '<input type="text" id="cm-cookie-search" class="form-control" placeholder="Suche..." style="width: 200px;">';
            $html .= '</div>';
            $html .= '</div>';
            
            $html .= '<script nonce="' . rex_response::getNonce() . '">
            jQuery(function($) {
                // Checkboxen selektieren via Klasse
                var $checkboxes = $(".cm-cookie-checkbox-item");
                
                // Styling Klasse auf das Container-Element der Checkboxen anwenden
                if ($checkboxes.length > 0) {
                    $checkboxes.first().closest(".rex-form-group").addClass("consent-manager-cookie-list");
                }
                
                // Toolbar Wrapper finden
                var $toolbar = $("#cm-cookie-toolbar");
                
                // Den zugehörigen Form-Group Container des Toolbars finden
                var $toolbarGroup = $toolbar.closest(".rex-form-group");
                
                // Label (dt) des Toolbars entfernen/verstecken für Full-Width Look
                $toolbarGroup.addClass("cm-toolbar-group");
                $toolbarGroup.find("dt").html("&nbsp;"); 

                // Select All Handler
                $toolbar.on("click", ".cm-select-all", function() {
                    $checkboxes.filter(":visible").prop("checked", true);
                });
                
                // Deselect All Handler
                $toolbar.on("click", ".cm-deselect-all", function() {
                    $checkboxes.filter(":visible").prop("checked", false);
                });
                
                // Live Search Handler
                $("#cm-cookie-search").on("keyup", function() {
                    var value = $(this).val().toLowerCase();
                    $checkboxes.each(function() {
                        var $input = $(this);
                        var text = $input.closest("label").text().toLowerCase();
                        var $wrapper = $input.closest(".checkbox");
                        
                        if (text.indexOf(value) > -1) {
                            $wrapper.show();
                        } else {
                            $wrapper.hide();
                        }
                    });
                });
            });
            </script>';
            
            $html .= '<style nonce="' . rex_response::getNonce() . '">
            /* Toolbar Row Styling */
            .cm-toolbar-group {
                margin-bottom: 0 !important;
                border-bottom: 0 !important;
            }
            .cm-toolbar-group dd {
                padding-bottom: 5px;
            }

            /* Cookie List Styling */
            .consent-manager-cookie-list .checkbox {
                display: inline-block;
                width: 49%;
                margin: 5px 0;
                vertical-align: top;
                padding-right: 10px;
                box-sizing: border-box; 
                /* Removed background and border */
                padding: 8px 0; 
            }
            .consent-manager-cookie-list .checkbox:hover {
                /* Removed hover background */
            }

            /* Custom Checkbox Styling */
            .consent-manager-cookie-list .checkbox input[type="checkbox"] {
                appearance: none;
                -webkit-appearance: none;
                -moz-appearance: none;
                width: 26px;
                height: 26px;
                border: 3px solid #e0e0e0;
                border-radius: 50%;
                cursor: pointer;
                position: relative;
                transition: all 0.3s ease;
                vertical-align: middle;
                margin-right: 8px;
                margin-top: 0;
                float: left; /* Ensure it floats left like standard checkbox */
            }

            .consent-manager-cookie-list .checkbox input[type="checkbox"]::before {
                content: "";
                position: absolute;
                width: 12px;
                height: 12px;
                border-radius: 50%;
                background: #667eea;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) scale(0);
                transition: transform 0.3s ease;
            }

            .consent-manager-cookie-list .checkbox input[type="checkbox"]:checked {
                border-color: #667eea;
            }

            .consent-manager-cookie-list .checkbox input[type="checkbox"]:checked::before {
                transform: translate(-50%, -50%) scale(1);
            }

            .consent-manager-cookie-list .checkbox input[type="checkbox"]:hover {
                border-color: #667eea;
            }

            .consent-manager-cookie-list .checkbox input[type="checkbox"]:focus {
                outline: 2px solid #667eea;
                outline-offset: 2px;
            }

            .consent-manager-cookie-list .checkbox label {
                width: 100%;
                cursor: pointer;
                display: block; /* Important for float layout */
                padding-left: 0;
                line-height: 28px; /* Align text vertically with 26px checkbox */
            }
            </style>';
            
            $form->addRawField($html);

            $field = $form->addCheckboxField('cookie');
            $field->setAttribute('class', 'cm-cookie-checkbox-item'); // Klasse für JS-Selektor
            $field->setLabel(rex_i18n::msg('consent_manager_cookies'));
            foreach ($cookies as $v) {
                // Name und UID speichern
                $field->addOption(rex_escape($v['service_name']), $v['uid']);
            }
        }
    } else {
        if ([] !== $cookies) {
            $form->addFieldset(rex_i18n::msg('consent_manager_cookies'));

            if ($hasCookieModeColumns && $languageCustomServicesEnabled) {
                $cookieMode = trim((string) $form->getSql()->getValue('cookie_mode'));
                if ('custom' !== $cookieMode) {
                    $cookieMode = 'inherit';
                }

                $modeField = $form->addSelectField('cookie_mode');
                $modeField->setLabel(rex_i18n::msg('consent_manager_cookiegroup_cookie_mode'));
                $modeField->setAttribute('class', 'cm-cookie-mode-select');
                $modeSelect = $modeField->getSelect();
                $modeSelect->addOption(rex_i18n::msg('consent_manager_cookiegroup_cookie_mode_inherit', $startLanguageName), 'inherit');
                $modeSelect->addOption(rex_i18n::msg('consent_manager_cookiegroup_cookie_mode_custom'), 'custom');

                $inheritHintStyle = 'inherit' === $cookieMode ? '' : ' style="display:none"';
                $customHintStyle = 'custom' === $cookieMode ? '' : ' style="display:none"';
                $form->addRawField('<p class="help-block cm-cookie-mode-hint cm-cookie-mode-hint-inherit"' . $inheritHintStyle . '>' . rex_i18n::msg('consent_manager_cookiegroup_cookie_mode_inherit_hint', $startLanguageName) . '</p>');
                $form->addRawField('<p class="help-block cm-cookie-mode-hint cm-cookie-mode-hint-custom"' . $customHintStyle . '>' . rex_i18n::msg('consent_manager_cookiegroup_cookie_mode_custom_hint') . '</p>');

                $form->addRawField('<style nonce="' . rex_response::getNonce() . '">
                .cm-cookie-mode-section-custom .checkbox {
                    display: inline-block;
                    width: 49%;
                    margin: 5px 0;
                    vertical-align: top;
                    padding-right: 10px;
                    box-sizing: border-box;
                    padding: 8px 0;
                }

                .cm-cookie-mode-section-custom .checkbox input[type="checkbox"] {
                    appearance: none;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    width: 26px;
                    height: 26px;
                    border: 3px solid #e0e0e0;
                    border-radius: 50%;
                    cursor: pointer;
                    position: relative;
                    transition: all 0.3s ease;
                    vertical-align: middle;
                    margin-right: 8px;
                    margin-top: 0;
                    float: left;
                }

                .cm-cookie-mode-section-custom .checkbox input[type="checkbox"]::before {
                    content: "";
                    position: absolute;
                    width: 12px;
                    height: 12px;
                    border-radius: 50%;
                    background: #667eea;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%) scale(0);
                    transition: transform 0.3s ease;
                }

                .cm-cookie-mode-section-custom .checkbox input[type="checkbox"]:checked {
                    border-color: #667eea;
                }

                .cm-cookie-mode-section-custom .checkbox input[type="checkbox"]:checked::before {
                    transform: translate(-50%, -50%) scale(1);
                }

                .cm-cookie-mode-section-custom .checkbox input[type="checkbox"]:hover {
                    border-color: #667eea;
                }

                .cm-cookie-mode-section-custom .checkbox input[type="checkbox"]:focus {
                    outline: 2px solid #667eea;
                    outline-offset: 2px;
                }

                .cm-cookie-mode-section-custom .checkbox label {
                    width: 100%;
                    cursor: pointer;
                    display: block;
                    padding-left: 0;
                    line-height: 28px;
                }
                </style>');

                $uid = (string) $form->getSql()->getValue('uid');
                $inheritedCookieSelection = $primaryCookieSelectionByUid[$uid] ?? '';
                $checkedBoxes = array_filter(explode('|', $inheritedCookieSelection), static function ($value) {
                    return '' !== $value;
                });

                $customCookieSelection = (string) $form->getSql()->getValue('cookie_custom');
                if ('custom' === $cookieMode && '' === trim($customCookieSelection)) {
                    $form->getSql()->setValue('cookie_custom', $inheritedCookieSelection);
                }

                $checkboxes = [];
                foreach ($cookies as $v) {
                    $checked = (in_array((string) $v['uid'], $checkedBoxes, true)) ? '|1|' : '';
                    $checkboxes[] = [$checked, rex_escape($v['service_name'])];
                }

                $inheritSectionStyle = ' style="display:none"';
                $customSectionStyle = 'custom' === $cookieMode ? '' : ' style="display:none"';
                $form->addRawField('<div class="cm-cookie-mode-section cm-cookie-mode-section-inherit"' . $inheritSectionStyle . '>');
                $form->addRawField(RexFormSupport::getFakeCheckbox(rex_i18n::msg('consent_manager_cookies'), $checkboxes)); /** @phpstan-ignore-line */
                $form->addRawField('</div>');

                $form->addRawField('<div class="cm-cookie-mode-section cm-cookie-mode-section-custom consent-manager-cookie-list"' . $customSectionStyle . '>');
                $field = $form->addCheckboxField('cookie_custom');
                $field->setLabel(rex_i18n::msg('consent_manager_cookies'));
                $field->setAttribute('class', 'cm-cookie-custom-checkbox');
                if ('custom' !== $cookieMode) {
                    $field->setAttribute('disabled', 'disabled');
                }
                foreach ($cookies as $v) {
                    $field->addOption(rex_escape($v['service_name']), $v['uid']);
                }
                $form->addRawField('</div>');
            } else {
                $checkboxes = [];
                if ($hasCookieModeColumns && !$languageCustomServicesEnabled) {
                    $form->addRawField('<p class="help-block">' . rex_i18n::msg('consent_manager_cookiegroup_cookie_mode_globally_disabled') . '</p>');
                    $uid = (string) $form->getSql()->getValue('uid');
                    $selection = (string) ($primaryCookieSelectionByUid[$uid] ?? '');
                } else {
                    $selection = (string) $form->getSql()->getValue('cookie');
                }

                if ('' !== $selection) {
                    $checkedBoxes = array_filter(explode('|', $selection), static function ($value) {
                        return '' !== $value;
                    });
                } else {
                    $checkedBoxes = [];
                }
                foreach ($cookies as $v) {
                    $checked = (in_array((string) $v['uid'], $checkedBoxes, true)) ? '|1|' : '';
                    $checkboxes[] = [$checked, rex_escape($v['service_name'])];
                }
                $form->addRawField(RexFormSupport::getFakeCheckbox(rex_i18n::msg('consent_manager_cookies'), $checkboxes)); /** @phpstan-ignore-line */
            }
        }
    }

    $title = $form->isEditMode() ? rex_i18n::msg('consent_manager_cookiegroup_edit') : rex_i18n::msg('consent_manager_cookiegroup_add');
    $formContent = $form->get();

    $mainSection = new rex_fragment();
    $mainSection->setVar('class', 'edit', false);
    $mainSection->setVar('title', $title);
    $mainSection->setVar('body', $formContent, false);

    $helpBody = '<div class="cm-cookiegroup-help-list">';
    $helpBody .= '<div class="panel panel-info" style="border-left: 4px solid #5bc0de; background: rgba(91, 192, 222, 0.07); margin-bottom: 12px; padding: 10px 12px;">';
    $helpBody .= '<small>' . rex_i18n::msg('consent_manager_cookiegroup_helpbox_point_primary', $startLanguageName) . '</small>';
    $helpBody .= '</div>';
    $helpBody .= '<div class="panel panel-default" style="border-left: 4px solid #777; background: rgba(119, 119, 119, 0.05); margin-bottom: 12px; padding: 10px 12px;">';
    $helpBody .= '<small>' . rex_i18n::msg('consent_manager_cookiegroup_helpbox_point_system_cookie') . '</small>';
    $helpBody .= '</div>';
    $helpBody .= '<div class="panel panel-primary" style="border-left: 4px solid #337ab7; background: rgba(51, 122, 183, 0.07); margin-bottom: 12px; padding: 10px 12px;">';
    $helpBody .= '<small>' . rex_i18n::msg('consent_manager_cookiegroup_helpbox_point_translatable', $currentLanguageName) . '</small>';
    $helpBody .= '</div>';
    $helpBody .= '<div class="panel panel-default" style="border-left: 4px solid #777; background: rgba(119, 119, 119, 0.05); margin-bottom: 12px; padding: 10px 12px;">';
    $helpBody .= '<small>' . rex_i18n::msg('consent_manager_cookiegroup_helpbox_point_service_code') . '</small>';
    $helpBody .= '</div>';
    $helpBody .= '<div class="panel panel-warning" style="border-left: 4px solid #f0ad4e; background: rgba(240, 173, 78, 0.08); margin-bottom: 0; padding: 10px 12px;">';
    $helpBody .= '<small>' . rex_i18n::msg('consent_manager_cookiegroup_helpbox_point_status') . '</small>';
    $helpBody .= '</div>';
    $helpBody .= '</div>';

    $helpSection = new rex_fragment();
    $helpSection->setVar('class', 'default', false);
    $helpSection->setVar('title', rex_i18n::msg('consent_manager_cookiegroup_helpbox_title'));
    $helpSection->setVar('body', $helpBody, false);

    echo '<div class="row cm-cookiegroup-panels">';
    echo '<div class="col-md-8 col-lg-9">' . $mainSection->parse('core/page/section.php') . '</div>';
    echo '<div class="col-md-4 col-lg-3">' . $helpSection->parse('core/page/section.php') . '</div>';
    echo '</div>';
}
echo $msg;

if ($showlist) {
    if (false === Utility::consentConfigured()) {
        echo rex_view::warning(rex_i18n::msg('consent_manager_cookiegroup_nodomain_notice'));
    }

    $isTranslatedCookieGroupUid = static function (string $uid) use ($table, $clang_id): bool {
        if ($clang_id === rex_clang::getStartId()) {
            return false;
        }

        static $cache = [];
        $cacheKey = $clang_id . '|' . $uid;
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $fields = ['name', 'description', 'script'];
        $fieldList = implode(',', $fields);

        $sqlCompare = rex_sql::factory();
        $sqlCompare->setQuery('SELECT ' . $fieldList . ' FROM ' . $table . ' WHERE uid = ? AND clang_id = ? ORDER BY pid ASC LIMIT 1', [$uid, $clang_id]);
        if ($sqlCompare->getRows() === 0) {
            $cache[$cacheKey] = false;
            return false;
        }
        $targetRow = $sqlCompare->getArray()[0];

        $sqlBase = rex_sql::factory();
        $sqlBase->setQuery('SELECT ' . $fieldList . ' FROM ' . $table . ' WHERE uid = ? AND clang_id = ? ORDER BY pid ASC LIMIT 1', [$uid, rex_clang::getStartId()]);
        if ($sqlBase->getRows() === 0) {
            $cache[$cacheKey] = false;
            return false;
        }
        $startRow = $sqlBase->getArray()[0];

        foreach ($fields as $field) {
            $targetValue = trim((string) ($targetRow[$field] ?? ''));
            $startValue = trim((string) ($startRow[$field] ?? ''));
            if ('' !== $targetValue && $targetValue !== $startValue) {
                $cache[$cacheKey] = true;
                return true;
            }
        }

        $cache[$cacheKey] = false;
        return false;
    };

    $listDebug = false;

    $getCookieAssignmentStatusByUid = static function (string $uid) use ($table, $clang_id, $hasCookieModeColumns, $languageCustomServicesEnabled): string {
        if ($clang_id === rex_clang::getStartId()) {
            return 'primary';
        }

        if (!$languageCustomServicesEnabled) {
            return 'global_inherit';
        }

        static $cache = [];
        $cacheKey = $clang_id . '|' . $uid;
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $normalizeSelection = static function (string $selection): string {
            $parts = array_filter(explode('|', $selection), static function ($value) {
                return '' !== $value;
            });
            $parts = array_values(array_unique($parts));
            sort($parts);

            return implode('|', $parts);
        };

        $sqlStart = rex_sql::factory();
        $sqlStart->setQuery('SELECT cookie FROM ' . $table . ' WHERE uid = ? AND clang_id = ? ORDER BY pid ASC LIMIT 1', [$uid, rex_clang::getStartId()]);
        if (0 === $sqlStart->getRows()) {
            $cache[$cacheKey] = 'inherited';

            return 'inherited';
        }

        $startSelection = (string) $sqlStart->getValue('cookie');

        $sqlTarget = rex_sql::factory();
        if ($hasCookieModeColumns) {
            $sqlTarget->setQuery('SELECT cookie, cookie_mode, cookie_custom FROM ' . $table . ' WHERE uid = ? AND clang_id = ? ORDER BY pid ASC LIMIT 1', [$uid, $clang_id]);
        } else {
            $sqlTarget->setQuery('SELECT cookie FROM ' . $table . ' WHERE uid = ? AND clang_id = ? ORDER BY pid ASC LIMIT 1', [$uid, $clang_id]);
        }

        if (0 === $sqlTarget->getRows()) {
            $cache[$cacheKey] = 'inherited';

            return 'inherited';
        }

        $effectiveSelection = $startSelection;
        if ($hasCookieModeColumns) {
            $cookieMode = trim((string) $sqlTarget->getValue('cookie_mode'));
            if ('custom' === $cookieMode) {
                $customSelection = trim((string) $sqlTarget->getValue('cookie_custom'));
                if ('' !== $customSelection) {
                    $effectiveSelection = $customSelection;
                }
            }
        } else {
            $effectiveSelection = (string) $sqlTarget->getValue('cookie');
        }

        if ($normalizeSelection($effectiveSelection) !== $normalizeSelection($startSelection)) {
            $cache[$cacheKey] = 'custom';

            return 'custom';
        }

        $cache[$cacheKey] = 'inherited';

        return 'inherited';
    };
    $qry = '
    SELECT pid,uid,name,domain,cookie
    FROM ' . $table . '
    WHERE clang_id = ' . $clang_id . '
    ORDER BY prio';

    $list = rex_list::factory($qry, 100, '', $listDebug);
    $list->addParam('page', rex_be_controller::getCurrentPage());
    $list->addTableAttribute('class', 'table table-striped table-hover consent_manager-table consent_manager-table-cookiegroup');

    $list->removeColumn('pid');
    $list->setColumnLabel('domain', rex_i18n::msg('consent_manager_domain'));
    $list->setColumnSortable('domain');
    $list->setColumnFormat('domain', 'custom', RexListSupport::formatDomain(...));
    $list->setColumnLabel('cookie', rex_i18n::msg('consent_manager_cookies'));
    $list->setColumnFormat('cookie', 'custom', RexListSupport::formatCookie(...));
    $list->setColumnLabel('uid', rex_i18n::msg('consent_manager_uid'));
    $list->setColumnParams('uid', ['func' => 'edit', 'pid' => '###pid###']);
    $list->setColumnSortable('uid');
    $list->setColumnFormat('uid', 'custom', static function (array $params): string {
        return rex_escape((string) $params['value']);
    });
    $list->setColumnLabel('name', rex_i18n::msg('consent_manager_name'));
    $list->setColumnSortable('name');

    $tdIcon = '<i class="rex-icon rex-icon-edit"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '"' . rex::getAccesskey(rex_i18n::msg('add'), 'add') . '><i class="rex-icon rex-icon-add"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'pid' => '###pid###']);

    $translationStatusHeader = '<i class="rex-icon fa-language" title="Uebersetzung"></i>';
    $list->addColumn($translationStatusHeader, '', 4, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnFormat($translationStatusHeader, 'custom', static function (array $params) use ($isTranslatedCookieGroupUid): string {
        $uid = (string) $params['list']->getValue('uid');
        $translated = $isTranslatedCookieGroupUid($uid);
        $color = $translated ? '#3cba54' : '#9aa0a6';
        $title = $translated ? 'Uebersetzt' : 'Nicht uebersetzt';

        return '<i class="rex-icon fa-language" title="' . $title . '" style="color:' . $color . ';"></i>';
    });

    $serviceAssignmentHeaderTitle = rex_escape(rex_i18n::msg('consent_manager_cookiegroup_list_service_assignment'));
    $serviceAssignmentHeader = '<i class="rex-icon fa-link" title="' . $serviceAssignmentHeaderTitle . '"></i>';
    $list->addColumn($serviceAssignmentHeader, '', 5, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnFormat($serviceAssignmentHeader, 'custom', static function (array $params) use ($getCookieAssignmentStatusByUid): string {
        $uid = (string) $params['list']->getValue('uid');
        $status = $getCookieAssignmentStatusByUid($uid);

        $icon = 'fa-link';
        $color = '#9aa0a6';
        $title = rex_i18n::msg('consent_manager_cookiegroup_service_status_inherited');

        if ('custom' === $status) {
            $icon = 'fa-unlink';
            $color = '#f0ad4e';
            $title = rex_i18n::msg('consent_manager_cookiegroup_service_status_custom');
        } elseif ('primary' === $status) {
            $color = '#5bc0de';
            $title = rex_i18n::msg('consent_manager_cookiegroup_service_status_primary');
        } elseif ('global_inherit' === $status) {
            $color = '#777';
            $title = rex_i18n::msg('consent_manager_cookiegroup_service_status_global_off');
        }

        return '<i class="rex-icon ' . $icon . '" title="' . rex_escape($title) . '" style="color:' . $color . ';"></i>';
    });

    $list->addColumn(rex_i18n::msg('function'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('function'), ['<th class="rex-table-action" colspan="4">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('function'), ['pid' => '###pid###', 'func' => 'edit', 'start' => rex_request::request('start', 'string')]);

    $list->addColumn(rex_i18n::msg('consent_manager_duplicate'), '<i class="rex-icon rex-icon-duplicate"></i> ' . rex_i18n::msg('consent_manager_duplicate'));
    $list->setColumnLayout(rex_i18n::msg('consent_manager_duplicate'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('consent_manager_duplicate'), ['pid' => '###pid###', 'func' => 'duplicate', 'start' => rex_request::request('start', 'string')] + $csrf->getUrlParams());

    $list->addColumn(rex_i18n::msg('consent_manager_rename'), '<i class="rex-icon fa-exchange"></i> ' . rex_i18n::msg('consent_manager_rename'));
    $list->setColumnLayout(rex_i18n::msg('consent_manager_rename'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('consent_manager_rename'), ['func' => 'uid_rename_open', 'rename_pid' => '###pid###', 'start' => rex_request::request('start', 'string')]);

    $list->addColumn(rex_i18n::msg('delete'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
    $list->setColumnLayout(rex_i18n::msg('delete'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('delete'), ['pid' => '###pid###', 'func' => 'delete'] + $csrf->getUrlParams());
    $list->addLinkAttribute(rex_i18n::msg('delete'), 'onclick', 'return confirm(\' ###uid### ' . rex_i18n::msg('delete') . ' ?\')');

    $content = $list->get();

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('consent_manager_cookiegroups'));
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');

    $renameNewUidValue = rex_request::post('new_uid', 'string', '');
    $updateConsentLogsChecked = 1 === rex_request::post('update_consent_logs', 'int', 1);
    $currentDryrunToken = $approvedDryrunToken;
    if ('' === $currentDryrunToken && 'dryrun' === $renameMode && is_array($renameResult) && ($renameResult['ok'] ?? false) && '' !== $renameOldUid && '' !== $renameNewUidValue) {
        $currentDryrunToken = $buildRenameToken($renameOldUid, $renameNewUidValue, $updateConsentLogsChecked);
    }
    $applyDisabled = '' === $currentDryrunToken;
    if ($renameOpen):
?>
<div class="modal fade in" id="cm-cookiegroup-rename-modal" tabindex="-1" role="dialog" aria-hidden="false" style="display:block;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="window.location='<?= rex_url::currentBackendPage(['func' => '', 'rename_pid' => 0, 'start' => rex_request::request('start', 'string')]) ?>';"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="rex-icon fa-exchange"></i> <?= rex_i18n::msg('consent_manager_rename') ?></h4>
            </div>
            <div class="modal-body">
                <form action="<?= rex_url::currentBackendPage() ?>" method="post" id="cm-cookiegroup-rename-form">
                    <?= $csrf->getHiddenField() ?>
                    <input type="hidden" name="old_uid" value="<?= rex_escape($renameOldUid) ?>">
                    <input type="hidden" name="func" id="cm-cookiegroup-rename-func" value="">
                    <input type="hidden" name="dryrun_token" id="cm-cookiegroup-rename-token" value="<?= rex_escape($currentDryrunToken) ?>">
                    <input type="hidden" name="start" value="<?= rex_escape(rex_request::request('start', 'string')) ?>">

                    <div class="alert alert-info" style="margin-bottom: 12px;">
                        Vor dem Umbenennen ist ein Dry-Run verpflichtend. Bitte pruefen Sie Auswirkungen, Hinweise und moegliche manuelle Nacharbeit.
                    </div>

                    <div class="form-group">
                        <label>Aktueller Schluessel</label>
                        <input type="text" class="form-control" value="<?= rex_escape($renameOldUid) ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="cm-cookiegroup-rename-new">Neuer Schluessel</label>
                        <input id="cm-cookiegroup-rename-new" type="text" class="form-control" name="new_uid" value="<?= rex_escape($renameNewUidValue) ?>" required>
                    </div>

                    <div class="checkbox" style="margin-top:0;">
                        <label>
                            <input type="checkbox" name="update_consent_logs" value="1"<?= $updateConsentLogsChecked ? ' checked' : '' ?>>
                            Bei Gruppen-Rename auch Consent-Log-Eintraege anpassen
                        </label>
                    </div>
                </form>

                <?php if (is_array($renameResult)): ?>
                    <hr>
                    <?php $impact = is_array($renameResult['impact'] ?? null) ? $renameResult['impact'] : []; ?>
                    <div class="well" style="margin-bottom:10px; padding:10px;">
                        <div><strong>Modus:</strong> <?= rex_escape($renameMode) ?></div>
                        <div><strong>Treffer Datensaetze:</strong> <?= (int) ($impact['source_rows'] ?? 0) ?></div>
                        <div><strong>Betroffene Sprachen:</strong> <?= (int) ($impact['affected_clangs'] ?? 0) ?></div>
                        <div><strong>Referenzen in Consent-Logs:</strong> <?= (int) ($impact['consent_log_refs'] ?? 0) ?></div>
                    </div>

                    <?php $errors = is_array($renameResult['errors'] ?? null) ? $renameResult['errors'] : []; ?>
                    <?php if ([] !== $errors): ?>
                        <div class="alert alert-danger"><strong>Fehler</strong><ul style="margin:8px 0 0 18px;"><?php foreach ($errors as $error): ?><li><?= rex_escape((string) $error) ?></li><?php endforeach ?></ul></div>
                    <?php endif ?>

                    <?php $warnings = is_array($renameResult['warnings'] ?? null) ? $renameResult['warnings'] : []; ?>
                    <?php if ([] !== $warnings): ?>
                        <div class="alert alert-warning"><strong>Hinweise</strong><ul style="margin:8px 0 0 18px;"><?php foreach ($warnings as $warning): ?><li><?= rex_escape((string) $warning) ?></li><?php endforeach ?></ul></div>
                    <?php endif ?>

                    <?php $manualActions = is_array($renameResult['manual_actions'] ?? null) ? $renameResult['manual_actions'] : []; ?>
                    <?php if ([] !== $manualActions): ?>
                        <div class="alert alert-info"><strong>Moegliche manuelle Nacharbeit</strong><ul style="margin:8px 0 0 18px;"><?php foreach ($manualActions as $manualAction): ?><li><?= rex_escape((string) $manualAction) ?></li><?php endforeach ?></ul></div>
                    <?php endif ?>
                <?php endif ?>
            </div>
            <div class="modal-footer">
                <a class="btn btn-default" href="<?= rex_url::currentBackendPage(['func' => '', 'rename_pid' => 0, 'start' => rex_request::request('start', 'string')]) ?>">Schliessen</a>
                <button type="button" class="btn btn-warning" onclick="document.getElementById('cm-cookiegroup-rename-func').value='uid_rename_dryrun'; document.getElementById('cm-cookiegroup-rename-form').submit();"><i class="rex-icon fa-search"></i> Dry-Run</button>
                <button type="button" class="btn btn-danger<?= $applyDisabled ? ' disabled' : '' ?>"<?= $applyDisabled ? ' title="Bitte zuerst Dry-Run ausfuehren." aria-disabled="true"' : '' ?> onclick="if (this.classList.contains('disabled')) { return false; } if (confirm('Umbenennung jetzt ausfuehren? Hinweise wurden geprueft?')) { document.getElementById('cm-cookiegroup-rename-func').value='uid_rename_apply'; document.getElementById('cm-cookiegroup-rename-form').submit(); }"><i class="rex-icon fa-play"></i> Umbenennen</button>
            </div>
        </div>
    </div>
</div>
<div class="modal-backdrop fade in"></div>
<script nonce="<?= rex_response::getNonce() ?>">
    jQuery(function () {
        var input = document.getElementById('cm-cookiegroup-rename-new');
        if (input) {
            input.focus();
            input.select();
        }
    });
</script>
<?php
    endif;
}
