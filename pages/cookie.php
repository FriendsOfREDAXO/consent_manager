<?php
$showlist = true;
$pid = rex_request('pid', 'int', 0);
$func = rex_request('func', 'string');
$csrf = rex_csrf_token::factory('iwcc_cookie');
$clang_id = (int)str_replace('clang', '', rex_be_controller::getCurrentPagePart(3));
$table = rex::getTable('iwcc_cookie');
$msg = '';
if ($func == 'delete')
{
    $msg = iwcc_clang::deleteCookie($pid);
}
elseif ($func == 'add' || $func == 'edit')
{
    $formDebug = false;
    $showlist = false;
    $form = rex_form::factory($table, '', 'pid = ' . $pid, 'post', $formDebug);
    $form->addParam('pid', $pid);
    $form->addParam('sort', rex_request('sort', 'string', ''));
    $form->addParam('sorttype', rex_request('sorttype', 'string', ''));
    $form->addParam('start', rex_request('start', 'int', 0));
    $form->setApplyUrl(rex_url::currentBackendPage());
    $form->addHiddenField('clang_id', $clang_id);
    iwcc_rex_form::getId($form, $table);

    if ($func == 'edit' && $form->getSql()->getValue('uid') == 'iwcc')
    {
        $form->addRawField(iwcc_rex_form::showInfo($this->i18n('iwcc_cookie_iwcc_info')));
        $form->addRawField(iwcc_rex_form::getFakeText($this->i18n('iwcc_uid'), $form->getSql()->getValue('uid')));
    }
    else
    {
        if ($clang_id == rex_clang::getStartId() || !$form->isEditMode())
        {
            $field = $form->addTextField('uid');
            $field->setLabel($this->i18n('iwcc_uid_with_hint'));
            $field->getValidator()->add('notEmpty', $this->i18n('iwcc_uid_empty_msg'));
            $field->getValidator()->add('match', $this->i18n('iwcc_uid_malformed_msg'), '/^[a-z0-9-]+$/');
        }
        else
        {
            $form->addRawField(iwcc_rex_form::getFakeText($this->i18n('iwcc_uid'), $form->getSql()->getValue('uid')));
        }
    }
    $field = $form->addTextField('service_name');
    $field->setLabel($this->i18n('iwcc_cookie_service_name'));
    $field = $form->addTextAreaField('definition');
    $field->setAttributes(['class' => 'form-control codemirror', 'name'=> $field->getAttribute('name'), 'data-codemirror-mode' => 'text/x-yaml']);
    $field->setLabel($this->i18n('iwcc_cookie_definition'));
    $field = $form->addTextField('provider');
    $field->setLabel($this->i18n('iwcc_cookie_provider'));
    $field = $form->addTextField('provider_link_privacy');
    $field->setLabel($this->i18n('iwcc_cookie_provider_link_privacy'));
    $field->setNotice($this->i18n('iwcc_cookie_notice_provider_link_privacy'));

    if ($func == 'edit' && $form->getSql()->getValue('uid') != 'iwcc')
    {
        if ($clang_id == rex_clang::getStartId() || !$form->isEditMode())
        {
            $field = $form->addTextAreaField('script');
            $field->setLabel($this->i18n('iwcc_cookiegroup_scripts'));
            $field->setNotice($this->i18n('iwcc_cookiegroup_scripts_notice'));
        }
        else
        {
            $form->addRawField(iwcc_rex_form::getFakeTextarea($this->i18n('iwcc_cookiegroup_scripts'), $form->getSql()->getValue('script')));
        }
    }

    $field = $form->addTextAreaField('placeholder_text');
    $field->setLabel($this->i18n('iwcc_cookie_placeholder_text'));
    $field = $form->addMediaField('placeholder_image');
    $field->setLabel($this->i18n('iwcc_cookie_placeholder_image'));

    $title = $form->isEditMode() ? $this->i18n('iwcc_cookie_edit') : $this->i18n('iwcc_cookie_add');
    $content = $form->get();

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', $title);
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
}
echo $msg;
if ($showlist)
{
    $listDebug = false;
    $sql = 'SELECT pid,uid,service_name,provider FROM ' . $table . ' WHERE clang_id = ' . $clang_id . ' ORDER BY uid';

    $list = rex_list::factory($sql, 100, '', $listDebug);
    $list->addParam('page', rex_be_controller::getCurrentPage());
    $list->addTableAttribute('class', 'iwcc-table iwcc-table-cookie');
    $list->addTableAttribute('id', 'iwcc-table-cookie');

    $tdIcon = '<i class="fa fa-coffee"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '"' . rex::getAccesskey(rex_i18n::msg('add'), 'add') . '><i class="rex-icon rex-icon-add"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'pid' => '###pid###']);

    $list->removeColumn('pid');
    $list->setColumnLabel('uid', $this->i18n('iwcc_uid'));
    $list->setColumnSortable('uid');

    $list->setColumnLabel('service_name', $this->i18n('iwcc_cookie_service_name'));
    $list->setColumnSortable('service_name');

    $list->setColumnLabel('provider', $this->i18n('iwcc_cookie_provider'));
    $list->setColumnSortable('provider');

    $list->addColumn(rex_i18n::msg('function'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('function'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('function'), ['pid' => '###pid###', 'func' => 'edit', 'start' => rex_request('start', 'string')]);

    $list->addColumn(rex_i18n::msg('delete'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
    $list->setColumnLayout(rex_i18n::msg('delete'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('delete'), ['pid' => '###pid###', 'func' => 'delete'] + $csrf->getUrlParams());
    $list->addLinkAttribute(rex_i18n::msg('delete'), 'onclick', 'return confirm(\' ###service_name### ' . rex_i18n::msg('delete') . ' ?\')');

    $content = $list->get();

    $fragment = new rex_fragment();
    $fragment->setVar('title', $this->i18n('iwcc_cookies'));
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}
