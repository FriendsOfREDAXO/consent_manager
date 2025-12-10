<?php

$addon = rex_addon::get('consent_manager');

$showlist = true;
$id = rex_request('id', 'int', 0);
$func = rex_request('func', 'string');
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
    $form = rex_form::factory($table, '', 'id = ' . $id, 'post', $formDebug);
    $form->addParam('id', $id);
    $form->addParam('sort', rex_request('sort', 'string', ''));
    $form->addParam('sorttype', rex_request('sorttype', 'string', ''));
    $form->addParam('start', rex_request('start', 'int', 0));
    $form->setApplyUrl(rex_url::currentBackendPage());

    $field = $form->addTextField('uid');
    $field->setLabel($addon->i18n('consent_manager_domain'));
    $field->getValidator()->add('notEmpty', $addon->i18n('consent_manager_domain_empty_msg'));
    $field->getValidator()->add('custom', $addon->i18n('consent_manager_domain_malformed_msg'), 'consent_manager_rex_form::validateHostname');

    $field = $form->addLinkmapField('privacy_policy');
    $field->setLabel($addon->i18n('consent_manager_domain_privacy_policy')); /** @phpstan-ignore-line */
    $field->getValidator()->add('notEmpty', $addon->i18n('consent_manager_domain_privacy_policy_empty_msg')); /** @phpstan-ignore-line */

    $field = $form->addLinkmapField('legal_notice');
    $field->setLabel($addon->i18n('consent_manager_domain_legal_notice')); /** @phpstan-ignore-line */
    $field->getValidator()->add('notEmpty', $addon->i18n('consent_manager_domain_legal_notic_empty_msg')); /** @phpstan-ignore-line */

    $title = $form->isEditMode() ? $addon->i18n('consent_manager_domain_edit') : $addon->i18n('consent_manager_domain_add');
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
    $sql = 'SELECT id, uid FROM ' . $table . ' ORDER BY uid ASC';

    $list = rex_list::factory($sql, 100, '', $listDebug);
    $list->addParam('page', rex_be_controller::getCurrentPage());
    $list->addTableAttribute('class', 'table table-striped table-hover consent_manager-table consent_manager-table-cookiegroup');

    $list->removeColumn('id');
    $list->setColumnLabel('uid', $addon->i18n('consent_manager_domain'));
    $list->setColumnParams('uid', ['func' => 'edit', 'id' => '###id###']);
    $list->setColumnSortable('uid');

    $tdIcon = '<i class="fa fa-coffee"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '"' . rex::getAccesskey(rex_i18n::msg('add'), 'add') . '><i class="rex-icon rex-icon-add"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'id' => '###id###']);

    $list->addColumn(rex_i18n::msg('function'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('function'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('function'), ['id' => '###id###', 'func' => 'edit', 'start' => rex_request('start', 'string')]);

    $list->addColumn(rex_i18n::msg('delete'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
    $list->setColumnLayout(rex_i18n::msg('delete'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('delete'), ['id' => '###id###', 'func' => 'delete'] + $csrf->getUrlParams());
    $list->addLinkAttribute(rex_i18n::msg('delete'), 'onclick', 'return confirm(\' ###uid### ' . rex_i18n::msg('delete') . ' ?\')');

    $content = $list->get();

    $fragment = new rex_fragment();
    $fragment->setVar('title', $addon->i18n('consent_manager_domains'));
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}
