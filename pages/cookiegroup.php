<?php

use FriendsOfRedaxo\ConsentManager\CLang;
use FriendsOfRedaxo\ConsentManager\RexFormSupport;
use FriendsOfRedaxo\ConsentManager\RexListSupport;
use FriendsOfRedaxo\ConsentManager\Utility;

$showlist = true;
$pid = rex_request::request('pid', 'int', 0);
$func = rex_request::request('func', 'string');
$csrf = rex_csrf_token::factory('consent_manager_cookiegroup');
$clang_id = (int) str_replace('clang', '', rex_be_controller::getCurrentPagePart(3) ?? '');
$table = rex::getTable('consent_manager_cookiegroup');
$msg = '';
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

    $db = rex_sql::factory();
    $db->setTable(rex::getTable('consent_manager_domain'));
    $db->select('id,uid');
    $domains = $db->getArray();

    if ($clang_id === rex_clang::getStartId() || !$form->isEditMode()) {
        $field = $form->addTextField('uid');
        $field->setLabel(rex_i18n::msg('consent_manager_uid'));
        $field->getValidator()->add('notEmpty', rex_i18n::msg('consent_manager_uid_empty_msg'));
        $field->getValidator()->add('match', rex_i18n::msg('consent_manager_uid_malformed_msg'), '/^[a-z0-9-]+$/');

        $field = $form->addCheckboxField('required');
        $field->addOption(rex_i18n::msg('consent_manager_cookiegroup_required'), 1);

        if (count($domains) > 0) {
            $field = $form->addCheckboxField('domain');
            $field->setLabel(rex_i18n::msg('consent_manager_domain'));
            foreach ($domains as $v) {
                $field->addOption((string) $v['uid'], (int) $v['id']);
            }
        }

        $field = $form->addPrioField('prio');
        $field->setWhereCondition('clang_id = ' . $clang_id);
        $field->setLabel(rex_i18n::msg('consent_manager_prio'));
        $field->setLabelField('uid');
    } else {
        $form->addRawField(RexFormSupport::getFakeText(rex_i18n::msg('consent_manager_uid'), (string) $form->getSql()->getValue('uid')));
        $form->addRawField(RexFormSupport::getFakeCheckbox('', [[$form->getSql()->getValue('required'), rex_i18n::msg('consent_manager_cookiegroup_required')]])); /** @phpstan-ignore-line */

        $checkboxes = [];
        $checkedBoxes = array_filter(explode('|', (string) $form->getSql()->getValue('domain')), static function ($value) {
            return '' !== $value;
        });
        foreach ($domains as $v) {
            $checked = (in_array((string) $v['id'], $checkedBoxes, true)) ? '|1|' : '';
            $checkboxes[] = [$checked, $v['uid']];
        }
        if (count($checkboxes) > 0) {
            $form->addRawField(RexFormSupport::getFakeCheckbox(rex_i18n::msg('consent_manager_domain'), $checkboxes)); /** @phpstan-ignore-line */
        }
    }
    $field = $form->addTextField('name');
    $field->setLabel(rex_i18n::msg('consent_manager_name'));
    $field->getValidator()->add('notEmpty', rex_i18n::msg('consent_manager_uid_empty_msg'));

    $field = $form->addTextAreaField('description');
    $field->setLabel(rex_i18n::msg('consent_manager_description'));
    $field->getValidator()->add('notEmpty', rex_i18n::msg('consent_manager_uid_empty_msg'));

    $db = rex_sql::factory();
    $db->setTable(rex::getTable('consent_manager_cookie'));
    $db->setWhere('clang_id = ' . $clang_id . ' AND uid != "consent_manager" ORDER BY uid ASC');
    $db->select('DISTINCT uid, service_name, variant');
    $cookies = $db->getArray();

    if ($clang_id === rex_clang::getStartId() || true !== $form->isEditMode()) {
        if ([] !== $cookies) {
            // Eigene HTML-Darstellung mit Toggles und Config-Buttons
            $serviceHtml = RexFormSupport::getActiveServiceToggleList(rex_i18n::msg('consent_manager_cookies'), $cookies, $clang_id, 'cookie', $form);
            $form->addRawField($serviceHtml);
        }
    } else {
        if ([] !== $cookies) {
            $checkboxes = [];
            if (null !== $form->getSql()->getValue('cookie')) {
                $checkedBoxes = array_filter(explode('|', (string) $form->getSql()->getValue('cookie')), static function ($value) {
                    return '' !== $value;
                });
            } else {
                $checkedBoxes = [];
            }
            foreach ($cookies as $v) {
                $checked = (in_array((string) $v['uid'], $checkedBoxes, true)) ? '|1|' : '';
                $checkboxes[] = [$checked, $v['uid']];
            }
            $form->addRawField(RexFormSupport::getServiceToggleList(rex_i18n::msg('consent_manager_cookies'), $checkboxes, $clang_id)); /** @phpstan-ignore-line */
        }
    }

    $title = $form->isEditMode() ? rex_i18n::msg('consent_manager_cookiegroup_edit') : rex_i18n::msg('consent_manager_cookiegroup_add');
    $content = $form->get();

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', $title);
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
}
echo $msg;

if ($showlist) {
    if (false === Utility::consentConfigured()) {
        echo rex_view::warning(rex_i18n::msg('consent_manager_cookiegroup_nodomain_notice'));
    }

    $listDebug = false;
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
    $list->setColumnLabel('name', rex_i18n::msg('consent_manager_name'));
    $list->setColumnSortable('name');

    $tdIcon = '<i class="fa fa-coffee"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '"' . rex::getAccesskey(rex_i18n::msg('add'), 'add') . '><i class="rex-icon rex-icon-add"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'pid' => '###pid###']);

    $list->addColumn(rex_i18n::msg('function'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('function'), ['<th class="rex-table-action" colspan="3">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('function'), ['pid' => '###pid###', 'func' => 'edit', 'start' => rex_request::request('start', 'string')]);

    $list->addColumn(rex_i18n::msg('consent_manager_duplicate'), '<i class="rex-icon rex-icon-duplicate"></i> ' . rex_i18n::msg('consent_manager_duplicate'));
    $list->setColumnLayout(rex_i18n::msg('consent_manager_duplicate'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('consent_manager_duplicate'), ['pid' => '###pid###', 'func' => 'duplicate', 'start' => rex_request::request('start', 'string')] + $csrf->getUrlParams());

    $list->addColumn(rex_i18n::msg('delete'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
    $list->setColumnLayout(rex_i18n::msg('delete'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('delete'), ['pid' => '###pid###', 'func' => 'delete'] + $csrf->getUrlParams());
    $list->addLinkAttribute(rex_i18n::msg('delete'), 'onclick', 'return confirm(\' ###uid### ' . rex_i18n::msg('delete') . ' ?\')');

    $content = $list->get();

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('consent_manager_cookiegroups'));
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}
