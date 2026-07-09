<?php

use FriendsOfRedaxo\ConsentManager\Cache;
use FriendsOfRedaxo\ConsentManager\CLang;
use FriendsOfRedaxo\ConsentManager\RexFormSupport;
use FriendsOfRedaxo\ConsentManager\UidRenameWorkflow;

$showlist = true;
$pid = rex_request::request('pid', 'int', 0);
$func = rex_request::request('func', 'string');
$csrf = rex_csrf_token::factory('consent_manager_cookie');
$clang_id = (int) str_replace('clang', '', rex_be_controller::getCurrentPagePart(3) ?? '');
$table = rex::getTable('consent_manager_cookie');
$msg = '';
$startClangId = rex_clang::getStartId();
$systemCookieUids = ['consent_manager', 'consentmanager'];
$renameOpen = false;
$renameMode = '';
$renameResult = null;
$renameOldUid = '';
$renamePid = rex_request::request('rename_pid', 'int', 0);
$approvedDryrunToken = '';
$buildRenameToken = static function (string $oldUid, string $newUid): string {
    return hash('sha256', 'cookie|' . $oldUid . '|' . $newUid);
};

if ('add' === $func && $clang_id !== $startClangId) {
    header('Location: ' . rex_url::backendPage('consent_manager/cookie/clang' . $startClangId, ['func' => 'add', 'uid_primary_only' => 1]));
    exit;
}

if (1 === rex_request::request('uid_primary_only', 'int', 0)) {
    $msg .= rex_view::warning(rex_i18n::msg('consent_manager_uid_primary_only_notice'));
}

$cleanupDuplicatesByUid = static function (string $tableName, int $clangId): int {
    $duplicateSql = rex_sql::factory();
    $duplicateSql->setQuery(
        'SELECT uid FROM ' . $tableName . ' WHERE clang_id = ? GROUP BY uid HAVING COUNT(*) > 1',
        [$clangId],
    );

    $deletedRows = 0;
    foreach ($duplicateSql->getArray() as $duplicate) {
        $uid = (string) ($duplicate['uid'] ?? '');
        if ('' === $uid) {
            continue;
        }

        $rowsSql = rex_sql::factory();
        $rowsSql->setQuery('SELECT pid FROM ' . $tableName . ' WHERE clang_id = ? AND uid = ? ORDER BY pid ASC', [$clangId, $uid]);
        $rows = $rowsSql->getArray();
        if (count($rows) <= 1) {
            continue;
        }

        $deletePids = [];
        foreach ($rows as $index => $row) {
            if (0 === $index) {
                continue;
            }
            $deletePids[] = (int) $row['pid'];
        }

        if ([] !== $deletePids) {
            $deleteSql = rex_sql::factory();
            $deleteSql->setQuery('DELETE FROM ' . $tableName . ' WHERE pid IN (' . implode(',', $deletePids) . ')');
            $deletedRows += count($deletePids);
        }
    }

    return $deletedRows;
};

$deletedDuplicates = $cleanupDuplicatesByUid($table, $clang_id);
if ($deletedDuplicates > 0) {
    Cache::forceWrite();
    $msg .= rex_view::warning(rex_i18n::msg('consent_manager_cookie_duplicates_cleaned', $deletedDuplicates));
}

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
        $expectedToken = $buildRenameToken($renameOldUid, $renameNewUid);
        $postedDryrunToken = rex_request::post('dryrun_token', 'string', '');

        if ('uid_rename_dryrun' === $func) {
            $renameResult = UidRenameWorkflow::dryRun('cookie', $renameOldUid, $renameNewUid);
            $renameMode = 'dryrun';
            if (is_array($renameResult) && ($renameResult['ok'] ?? false)) {
                $approvedDryrunToken = $expectedToken;
            }
        } else {
            if ($postedDryrunToken !== $expectedToken) {
                $renameResult = UidRenameWorkflow::dryRun('cookie', $renameOldUid, $renameNewUid);
                $renameMode = 'dryrun';
                if (is_array($renameResult) && ($renameResult['ok'] ?? false)) {
                    $approvedDryrunToken = $expectedToken;
                }
                $msg = rex_view::error('Vor der Umbenennung muss ein Dry-Run fuer genau diesen Schluessel ausgefuehrt werden. Bitte Hinweise und moegliche Nacharbeit pruefen.');
                $renameOpen = true;
                $func = '';
            } else {
                $renameResult = UidRenameWorkflow::apply('cookie', $renameOldUid, $renameNewUid);
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

if ('delete' === $func) {
    $msg = CLang::deleteCookie($pid);
    Cache::forceWrite();
} elseif ('duplicate' === $func) {
    // Dienst duplizieren
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

            // UID und service_name anpassen
            $originalUid = $sql->getValue('uid');
            $originalName = $sql->getValue('service_name');
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
            $newSql->setValue('service_name', $originalName . ' (Kopie)');
            $newSql->setValue('createdate', date('Y-m-d H:i:s'));
            $newSql->setValue('updatedate', date('Y-m-d H:i:s'));

            try {
                $newSql->insert();
                $newPid = $newSql->getLastId();
                Cache::forceWrite();

                // Zur Edit-Seite des neuen Eintrags weiterleiten mit Hinweis
                $msg = rex_view::warning(rex_i18n::msg('consent_manager_cookie_duplicated_edit_uid'));
                // Redirect zur Edit-Seite
                header('Location: ' . rex_url::currentBackendPage(['func' => 'edit', 'pid' => $newPid, 'msg' => 'duplicated']));
                exit;
            } catch (rex_sql_exception $e) {
                $msg = rex_view::error(rex_i18n::msg('consent_manager_cookie_duplicate_error') . ': ' . $e->getMessage());
            }
        } else {
            $msg = rex_view::error(rex_i18n::msg('consent_manager_cookie_not_found'));
        }
    }
} elseif ('add' === $func || 'edit' === $func) {
    $formDebug = false;
    $showlist = false;

    // Warnung anzeigen wenn von duplicate weitergeleitet (nur einmalig)
    if ('duplicated' === rex_request::request('msg', 'string', '')) {
        echo rex_view::warning(
            '<strong>' . rex_i18n::msg('consent_manager_cookie_duplicated_edit_uid_title') . '</strong><br>' .
            rex_i18n::msg('consent_manager_cookie_duplicated_edit_uid_desc'),
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

    // Multi-Language Sprach-Switcher (gilt für gesamten Datensatz, daher ganz oben)
    if ($form->isEditMode() && rex_clang::count() > 1) {
        // Sprach-Switcher für alle Services außer consent_manager (System-Cookie)
        if (!in_array((string) $form->getSql()->getValue('uid'), $systemCookieUids, true)) {
            $currentUid = (string) $form->getSql()->getValue('uid');
            $languageSwitcher = '<div class="alert alert-info" style="margin: 15px 0;">';
            $languageSwitcher .= '<i class="rex-icon fa-language"></i> <strong>' . rex_i18n::msg('consent_manager_cookie_multilang_title') . '</strong><br>';
            $languageSwitcher .= '<small>' . rex_i18n::msg('consent_manager_cookie_multilang_desc', rex_clang::get(rex_clang::getStartId())->getName()) . '</small><br><br>';
            $languageSwitcher .= '<div class="btn-group" role="group">';

            foreach (rex_clang::getAll() as $clang) {
                $clangId = $clang->getId();
                $isActive = $clangId === $clang_id ? 'btn-primary active' : 'btn-primary';
                $icon = $clangId === rex_clang::getStartId() ? '<i class="rex-icon fa-star"></i> ' : '';
                $targetPid = $pid;
                if ($clangId !== $clang_id) {
                    $targetSql = rex_sql::factory();
                    $targetSql->setQuery('SELECT pid FROM ' . $table . ' WHERE uid = ? AND clang_id = ? ORDER BY pid ASC LIMIT 1', [$currentUid, $clangId]);
                    if ($targetSql->getRows() > 0) {
                        $targetPid = (int) $targetSql->getValue('pid');
                    }
                }
                $url = rex_url::currentBackendPage(['func' => 'edit', 'pid' => $targetPid, 'page' => 'consent_manager/cookie/clang' . $clangId]);
                $languageSwitcher .= '<a href="' . $url . '" class="btn ' . $isActive . ' btn-sm">' . $icon . rex_escape($clang->getName()) . '</a>';
            }

            $languageSwitcher .= '</div>';
            $languageSwitcher .= '</div>';
            $field = $form->addRawField($languageSwitcher);

            // Fallback-Hinweis für Nicht-Start-Sprachen (bezieht sich auf alle Felder)
            if ($clang_id !== rex_clang::getStartId()) {
                $startLangName = rex_clang::get(rex_clang::getStartId())->getName();
                $fallbackNotice = '<div class="alert alert-warning"><i class="rex-icon fa-info-circle"></i> ' .
                    rex_i18n::msg('consent_manager_cookie_fallback_notice', $startLangName) . '</div>';
                $field = $form->addRawField($fallbackNotice);
            }
        }
    }

    if ('edit' === $func && in_array((string) $form->getSql()->getValue('uid'), $systemCookieUids, true)) {
        $form->addRawField(RexFormSupport::showInfo(rex_i18n::rawMsg('consent_manager_cookie_consent_manager_info')));
        $field = $form->addReadOnlyField('uid_readonly', (string) $form->getSql()->getValue('uid'));
        $field->setLabel(rex_i18n::msg('consent_manager_uid'));
        $form->addHiddenField('uid', (string) $form->getSql()->getValue('uid'));
    } else {
        if ($form->isEditMode()) {
            $field = $form->addReadOnlyField('uid_readonly', (string) $form->getSql()->getValue('uid'));
            $field->setLabel(rex_i18n::msg('consent_manager_uid'));
            $form->addHiddenField('uid', (string) $form->getSql()->getValue('uid'));
        } else {
            $field = $form->addTextField('uid');
            $field->setLabel(rex_i18n::msg('consent_manager_uid_with_hint'));
            $field->getValidator()->add('notEmpty', rex_i18n::msg('consent_manager_uid_empty_msg'));
            $field->getValidator()->add('match', rex_i18n::msg('consent_manager_uid_malformed_msg'), '/^[a-z0-9-_]+$/');
        }
    }
    $field = $form->addTextField('service_name');
    $field->setLabel(rex_i18n::msg('consent_manager_cookie_service_name'));

    $field = $form->addTextField('variant');
    $field->setLabel(rex_i18n::msg('consent_manager_cookie_variant'));
    $field->setNotice(rex_i18n::msg('consent_manager_cookie_variant_notice'));

    $field = $form->addTextAreaField('definition');
    $field->setAttributes(['class' => 'form-control codemirror', 'name' => $field->getAttribute('name'), 'data-codemirror-mode' => 'text/x-yaml']);
    $field->setLabel(rex_i18n::msg('consent_manager_cookie_definition'));
    $field->getValidator()->add('custom', rex_i18n::msg('consent_manager_cookie_malformed_yaml'), RexFormSupport::validateYaml(...));

    $field = $form->addTextField('provider');
    $field->setLabel(rex_i18n::msg('consent_manager_cookie_provider'));
    $field = $form->addTextField('provider_link_privacy');
    $field->setLabel(rex_i18n::msg('consent_manager_cookie_provider_link_privacy'));
    $field->setNotice(rex_i18n::msg('consent_manager_cookie_notice_provider_link_privacy'));

    if ('edit' === $func && !in_array((string) $form->getSql()->getValue('uid'), $systemCookieUids, true)) {
        // Google Consent Mode v2 Helper Fragment verwenden
        $fragment = new rex_fragment();
        $googleHelperHtml = $fragment->parse('ConsentManager/google_consent_helper.php');
        $field = $form->addRawField($googleHelperHtml);

        $field = $form->addTextAreaField('script');
        $field->setAttributes(['class' => 'form-control codemirror', 'name' => $field->getAttribute('name'), 'data-codemirror-mode' => 'text/html']);
        $field->setLabel(rex_i18n::msg('consent_manager_cookiegroup_scripts'));
        $field->setNotice(rex_i18n::rawMsg('consent_manager_cookiegroup_scripts_notice'));

        $field = $form->addTextAreaField('script_unselect');
        $field->setAttributes(['class' => 'form-control codemirror', 'name' => $field->getAttribute('name'), 'data-codemirror-mode' => 'text/html']);
        $field->setLabel(rex_i18n::msg('consent_manager_cookiegroup_scripts_unselect'));
        $field->setNotice(rex_i18n::rawMsg('consent_manager_cookiegroup_scripts_notice'));
    }
    if ('add' === $func) {
        // Script-Felder sind in ALLEN Sprachen editierbar (für unterschiedliche Tracking-IDs etc.)
        // Google Consent Mode v2 Helper Fragment verwenden
        $fragment = new rex_fragment();
        $googleHelperHtml = $fragment->parse('ConsentManager/google_consent_helper.php');
        $field = $form->addRawField($googleHelperHtml);

        $field = $form->addTextAreaField('script');
        $field->setAttributes(['class' => 'form-control codemirror', 'name' => $field->getAttribute('name'), 'data-codemirror-mode' => 'text/html']);
        $field->setLabel(rex_i18n::msg('consent_manager_cookiegroup_scripts'));
        $field->setNotice(rex_i18n::rawMsg('consent_manager_cookiegroup_scripts_notice'));

        $field = $form->addTextAreaField('script_unselect');
        $field->setAttributes(['class' => 'form-control codemirror', 'name' => $field->getAttribute('name'), 'data-codemirror-mode' => 'text/html']);
        $field->setLabel(rex_i18n::msg('consent_manager_cookiegroup_scripts_unselect'));
        $field->setNotice(rex_i18n::rawMsg('consent_manager_cookiegroup_scripts_notice'));
    }

    $field = $form->addTextAreaField('placeholder_text');
    $field->setLabel(rex_i18n::msg('consent_manager_cookie_placeholder_text'));
    $field->setNotice(rex_i18n::rawMsg('consent_manager_cookie_placeholder_text_notice'));
    $field = $form->addMediaField('placeholder_image');
    $field->setLabel(rex_i18n::msg('consent_manager_cookie_placeholder_image'));
    $field->setNotice(rex_i18n::msg('consent_manager_cookie_placeholder_image_notice'));

    $title = $form->isEditMode() ? rex_i18n::msg('consent_manager_cookie_edit') : rex_i18n::msg('consent_manager_cookie_add');
    $content = $form->get();

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', $title);
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
}
echo $msg;
if ($showlist) {
    $listDebug = false;
    $sql = 'SELECT pid,uid,service_name,variant,provider FROM ' . $table . ' WHERE clang_id = ' . $clang_id . ' ORDER BY uid';

    $isTranslatedCookieUid = static function (string $uid) use ($table, $clang_id): bool {
        if ($clang_id === rex_clang::getStartId()) {
            return false;
        }

        static $cache = [];
        $cacheKey = $clang_id . '|' . $uid;
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $fields = ['service_name', 'variant', 'provider', 'provider_link_privacy', 'definition', 'script', 'script_unselect', 'placeholder_text'];
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

    $list = rex_list::factory($sql, 100, '', $listDebug);
    $list->addParam('page', rex_be_controller::getCurrentPage());
    $list->addTableAttribute('class', 'table table-striped table-hover consent_manager-table consent_manager-table-cookie');
    $list->addTableAttribute('id', 'consent_manager-table-cookie');

    $tdIcon = '<i class="rex-icon rex-icon-edit"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '"' . rex::getAccesskey(rex_i18n::msg('add'), 'add') . '><i class="rex-icon rex-icon-add"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'pid' => '###pid###']);

    $list->removeColumn('pid');
    $list->setColumnLabel('uid', rex_i18n::msg('consent_manager_uid'));
    $list->setColumnParams('uid', ['func' => 'edit', 'pid' => '###pid###']);
    $list->setColumnSortable('uid');

    $translationStatusHeader = '<i class="rex-icon fa-language" title="Uebersetzung"></i>';
    $list->addColumn($translationStatusHeader, '', 2, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnFormat($translationStatusHeader, 'custom', static function (array $params) use ($isTranslatedCookieUid): string {
        $uid = (string) $params['list']->getValue('uid');
        $translated = $isTranslatedCookieUid($uid);
        $color = $translated ? '#3cba54' : '#9aa0a6';
        $title = $translated ? 'Uebersetzt' : 'Nicht uebersetzt';

        return '<i class="rex-icon fa-language" title="' . $title . '" style="color:' . $color . ';"></i>';
    });

    // Variant-Spalte entfernen (wird in service_name integriert)
    $list->removeColumn('variant');

    $list->setColumnLabel('service_name', rex_i18n::msg('consent_manager_cookie_service_name'));
    $list->setColumnSortable('service_name');
    $list->setColumnFormat('service_name', 'custom', static function (array $params): string {
        $value = $params['value'];
        $list = $params['list'];
        $variant = $list->getValue('variant');
        $html = '<strong>' . rex_escape($value) . '</strong>';
        if ('' !== $variant && null !== $variant) {
            $html .= '<br><small style="color: #6c757d; font-style: italic;">→ ' . rex_escape($variant) . '</small>';
        }
        return $html;
    });

    $list->setColumnLabel('provider', rex_i18n::msg('consent_manager_cookie_provider'));
    $list->setColumnSortable('provider');

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
    $list->addLinkAttribute(rex_i18n::msg('delete'), 'onclick', 'return confirm(\' ###service_name### ' . rex_i18n::msg('delete') . ' ?\')');

    $content = $list->get();

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('consent_manager_cookies'));
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');

    $renameNewUidValue = rex_request::post('new_uid', 'string', '');
    $currentDryrunToken = $approvedDryrunToken;
    if ('' === $currentDryrunToken && 'dryrun' === $renameMode && is_array($renameResult) && ($renameResult['ok'] ?? false) && '' !== $renameOldUid && '' !== $renameNewUidValue) {
        $currentDryrunToken = $buildRenameToken($renameOldUid, $renameNewUidValue);
    }
    $applyDisabled = '' === $currentDryrunToken;
    if ($renameOpen):
?>
<div class="modal fade in" id="cm-cookie-rename-modal" tabindex="-1" role="dialog" aria-hidden="false" style="display:block;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="window.location='<?= rex_url::currentBackendPage(['func' => '', 'rename_pid' => 0, 'start' => rex_request::request('start', 'string')]) ?>';"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="rex-icon fa-exchange"></i> <?= rex_i18n::msg('consent_manager_rename') ?></h4>
            </div>
            <div class="modal-body">
                <form action="<?= rex_url::currentBackendPage() ?>" method="post" id="cm-cookie-rename-form">
                    <?= $csrf->getHiddenField() ?>
                    <input type="hidden" name="old_uid" value="<?= rex_escape($renameOldUid) ?>">
                    <input type="hidden" name="func" id="cm-cookie-rename-func" value="">
                    <input type="hidden" name="dryrun_token" id="cm-cookie-rename-token" value="<?= rex_escape($currentDryrunToken) ?>">
                    <input type="hidden" name="start" value="<?= rex_escape(rex_request::request('start', 'string')) ?>">

                    <div class="alert alert-info" style="margin-bottom: 12px;">
                        Vor dem Umbenennen ist ein Dry-Run verpflichtend. Bitte pruefen Sie Auswirkungen, Hinweise und moegliche manuelle Nacharbeit.
                    </div>

                    <div class="form-group">
                        <label>Aktueller Schluessel</label>
                        <input type="text" class="form-control" value="<?= rex_escape($renameOldUid) ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="cm-cookie-rename-new">Neuer Schluessel</label>
                        <input id="cm-cookie-rename-new" type="text" class="form-control" name="new_uid" value="<?= rex_escape($renameNewUidValue) ?>" required>
                    </div>
                </form>

                <?php if (is_array($renameResult)): ?>
                    <hr>
                    <?php $impact = is_array($renameResult['impact'] ?? null) ? $renameResult['impact'] : []; ?>
                    <div class="well" style="margin-bottom:10px; padding:10px;">
                        <div><strong>Modus:</strong> <?= rex_escape($renameMode) ?></div>
                        <div><strong>Treffer Datensaetze:</strong> <?= (int) ($impact['source_rows'] ?? 0) ?></div>
                        <div><strong>Betroffene Sprachen:</strong> <?= (int) ($impact['affected_clangs'] ?? 0) ?></div>
                        <div><strong>Referenzen in Gruppen:</strong> <?= (int) ($impact['group_refs'] ?? 0) ?></div>
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
                <button type="button" class="btn btn-warning" onclick="document.getElementById('cm-cookie-rename-func').value='uid_rename_dryrun'; document.getElementById('cm-cookie-rename-form').submit();"><i class="rex-icon fa-search"></i> Dry-Run</button>
                <button type="button" class="btn btn-danger<?= $applyDisabled ? ' disabled' : '' ?>"<?= $applyDisabled ? ' title="Bitte zuerst Dry-Run ausfuehren." aria-disabled="true"' : '' ?> onclick="if (this.classList.contains('disabled')) { return false; } if (confirm('Umbenennung jetzt ausfuehren? Hinweise wurden geprueft?')) { document.getElementById('cm-cookie-rename-func').value='uid_rename_apply'; document.getElementById('cm-cookie-rename-form').submit(); }"><i class="rex-icon fa-play"></i> Umbenennen</button>
            </div>
        </div>
    </div>
</div>
<div class="modal-backdrop fade in"></div>
<script nonce="<?= rex_response::getNonce() ?>">
    jQuery(function () {
        var input = document.getElementById('cm-cookie-rename-new');
        if (input) {
            input.focus();
            input.select();
        }
    });
</script>
<?php
    endif;
}
