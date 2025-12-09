<?php

use FriendsOfRedaxo\ConsentManager\Cache;
use FriendsOfRedaxo\ConsentManager\CLang;
use FriendsOfRedaxo\ConsentManager\RexFormSupport;

$showlist = true;
$pid = rex_request::request('pid', 'int', 0);
$func = rex_request::request('func', 'string');
$csrf = rex_csrf_token::factory('consent_manager_cookie');
$clang_id = (int) str_replace('clang', '', rex_be_controller::getCurrentPagePart(3) ?? '');
$table = rex::getTable('consent_manager_cookie');
$msg = '';
if ('delete' === $func) {
    $msg = CLang::deleteCookie($pid);
    Cache::forceWrite();
} elseif ('add' === $func || 'edit' === $func) {
    $formDebug = false;
    $showlist = false;
    $form = rex_form::factory($table, '', 'pid = ' . $pid, 'post', $formDebug);
    $form->addParam('pid', $pid);
    $form->addParam('sort', rex_request::request('sort', 'string', ''));
    $form->addParam('sorttype', rex_request::request('sorttype', 'string', ''));
    $form->addParam('start', rex_request::request('start', 'int', 0));
    $form->setApplyUrl(rex_url::currentBackendPage());
    $form->addHiddenField('clang_id', $clang_id);
    RexFormSupport::getId($form, $table);

    if ('edit' === $func && 'consent_manager' === $form->getSql()->getValue('uid')) {
        $form->addRawField(RexFormSupport::showInfo(rex_i18n::msg('consent_manager_cookie_consent_manager_info')));
        $form->addRawField(RexFormSupport::getFakeText(rex_i18n::msg('consent_manager_uid'), $form->getSql()->getValue('uid')));
    } else {
        if ($clang_id === rex_clang::getStartId() || !$form->isEditMode()) {
            $field = $form->addTextField('uid');
            $field->setLabel(rex_i18n::msg('consent_manager_uid_with_hint'));
            $field->getValidator()->add('notEmpty', rex_i18n::msg('consent_manager_uid_empty_msg'));
            $field->getValidator()->add('match', rex_i18n::msg('consent_manager_uid_malformed_msg'), '/^[a-z0-9-_]+$/');
        } else {
            $form->addRawField(RexFormSupport::getFakeText(rex_i18n::msg('consent_manager_uid'), (string) $form->getSql()->getValue('uid')));
        }
    }
    $field = $form->addTextField('service_name');
    $field->setLabel(rex_i18n::msg('consent_manager_cookie_service_name'));
    $field = $form->addTextAreaField('definition');
    $field->setAttributes(['class' => 'form-control codemirror', 'name' => $field->getAttribute('name'), 'data-codemirror-mode' => 'text/x-yaml']);
    $field->setLabel(rex_i18n::msg('consent_manager_cookie_definition'));
    $field->getValidator()->add('custom', rex_i18n::msg('consent_manager_cookie_malformed_yaml'), RexFormSupport::validateYaml(...));

    $field = $form->addTextField('provider');
    $field->setLabel(rex_i18n::msg('consent_manager_cookie_provider'));
    $field = $form->addTextField('provider_link_privacy');
    $field->setLabel(rex_i18n::msg('consent_manager_cookie_provider_link_privacy'));
    $field->setNotice(rex_i18n::msg('consent_manager_cookie_notice_provider_link_privacy'));

    if ('edit' === $func && 'consent_manager' !== $form->getSql()->getValue('uid')) {
        if ($clang_id === rex_clang::getStartId() || !$form->isEditMode()) {
            // Google Consent Mode v2 Helper Fragment verwenden
            $fragment = new rex_fragment();
            $googleHelperHtml = $fragment->parse('ConsentManager/google_consent_helper.php');
            $field = $form->addRawField($googleHelperHtml);

            $field = $form->addTextAreaField('script');
            $field->setAttributes(['class' => 'form-control codemirror', 'name' => $field->getAttribute('name'), 'data-codemirror-mode' => 'text/html']);
            $field->setLabel(rex_i18n::msg('consent_manager_cookiegroup_scripts'));
            $field->setNotice(rex_i18n::msg('consent_manager_cookiegroup_scripts_notice'));

            $field = $form->addTextAreaField('script_unselect');
            $field->setAttributes(['class' => 'form-control codemirror', 'name' => $field->getAttribute('name'), 'data-codemirror-mode' => 'text/html']);
            $field->setLabel(rex_i18n::msg('consent_manager_cookiegroup_scripts_unselect'));
            $field->setNotice(rex_i18n::msg('consent_manager_cookiegroup_scripts_notice'));
        } else {
            $form->addRawField(RexFormSupport::getFakeTextarea(rex_i18n::msg('consent_manager_cookiegroup_scripts'), (string) $form->getSql()->getValue('script')));
            $form->addRawField(RexFormSupport::getFakeTextarea(rex_i18n::msg('consent_manager_cookiegroup_scripts_unselect'), (string) $form->getSql()->getValue('script_unselect')));
        }
    }
    if ('add' === $func) {
        if ($clang_id === rex_clang::getStartId() || !$form->isEditMode()) {
            // Google Consent Mode v2 Helper Fragment verwenden
            $fragment = new rex_fragment();
            $googleHelperHtml = $fragment->parse('ConsentManager/google_consent_helper.php');
            $field = $form->addRawField($googleHelperHtml);

            $field = $form->addTextAreaField('script');
            $field->setAttributes(['class' => 'form-control codemirror', 'name' => $field->getAttribute('name'), 'data-codemirror-mode' => 'text/html']);
            $field->setLabel(rex_i18n::msg('consent_manager_cookiegroup_scripts'));
            $field->setNotice(rex_i18n::msg('consent_manager_cookiegroup_scripts_notice'));

            $field = $form->addTextAreaField('script_unselect');
            $field->setAttributes(['class' => 'form-control codemirror', 'name' => $field->getAttribute('name'), 'data-codemirror-mode' => 'text/html']);
            $field->setLabel(rex_i18n::msg('consent_manager_cookiegroup_scripts_unselect'));
            $field->setNotice(rex_i18n::msg('consent_manager_cookiegroup_scripts_notice'));
        }
    }

    $field = $form->addTextAreaField('placeholder_text');
    $field->setLabel(rex_i18n::msg('consent_manager_cookie_placeholder_text'));
    $field->setNotice(rex_i18n::msg('consent_manager_cookie_placeholder_text_notice'));
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
    $sql = 'SELECT pid,uid,service_name,provider FROM ' . $table . ' WHERE clang_id = ' . $clang_id . ' ORDER BY uid';

    $list = rex_list::factory($sql, 100, '', $listDebug);
    $list->addParam('page', rex_be_controller::getCurrentPage());
    $list->addTableAttribute('class', 'table table-striped table-hover consent_manager-table consent_manager-table-cookie');
    $list->addTableAttribute('id', 'consent_manager-table-cookie');

    $tdIcon = '<i class="fa fa-coffee"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '"' . rex::getAccesskey(rex_i18n::msg('add'), 'add') . '><i class="rex-icon rex-icon-add"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'pid' => '###pid###']);

    $list->removeColumn('pid');
    $list->setColumnLabel('uid', rex_i18n::msg('consent_manager_uid'));
    $list->setColumnParams('uid', ['func' => 'edit', 'pid' => '###pid###']);
    $list->setColumnSortable('uid');

    $list->setColumnLabel('service_name', rex_i18n::msg('consent_manager_cookie_service_name'));
    $list->setColumnSortable('service_name');

    $list->setColumnLabel('provider', rex_i18n::msg('consent_manager_cookie_provider'));
    $list->setColumnSortable('provider');

    $list->addColumn(rex_i18n::msg('function'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('function'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('function'), ['pid' => '###pid###', 'func' => 'edit', 'start' => rex_request::request('start', 'string')]);

    $list->addColumn(rex_i18n::msg('delete'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
    $list->setColumnLayout(rex_i18n::msg('delete'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('delete'), ['pid' => '###pid###', 'func' => 'delete'] + $csrf->getUrlParams());
    $list->addLinkAttribute(rex_i18n::msg('delete'), 'onclick', 'return confirm(\' ###service_name### ' . rex_i18n::msg('delete') . ' ?\')');

    $content = $list->get();

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('consent_manager_cookies'));
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}
