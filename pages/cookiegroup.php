<?php
$showlist = true;
$pid = rex_request('pid', 'int', 0);
$func = rex_request('func', 'string');
$csrf = rex_csrf_token::factory('iwcc_cookiegroup');
$clang_id = (int)str_replace('clang', '', rex_be_controller::getCurrentPagePart(3));
$table = rex::getTable('iwcc_cookiegroup');
$msg = '';
if ($func == 'delete') {
    $msg = iwcc_clang::deleteDataset($table, $pid);
} elseif ($func == 'add' || $func == 'edit') {
    $formDebug = false;
    $showlist = false;
    $form = rex_form::factory($table, '', 'pid = '.$pid, 'post', $formDebug);
    $form->addParam('pid', $pid);
    $form->addParam('sort', rex_request('sort', 'string', ''));
    $form->addParam('sorttype', rex_request('sorttype', 'string', ''));
    $form->addParam('start', rex_request('start', 'int', 0));
    $form->setApplyUrl(rex_url::currentBackendPage());
    $form->addHiddenField('clang_id', $clang_id);
    iwcc_rex_form::getId($form, $table);

    $db = rex_sql::factory();
    $db->setTable(rex::getTable('iwcc_domain'));
    $db->select('id,uid');
    $domains = $db->getArray();

    if ($clang_id == rex_clang::getStartId() || !$form->isEditMode()) {

        $field = $form->addTextField('uid');
        $field->setLabel($this->i18n('iwcc_uid'));
        $field->getValidator()->add('notEmpty', $this->i18n('iwcc_uid_empty_msg'));
        $field->getValidator()->add('match', $this->i18n('iwcc_uid_malformed_msg'), '/^[a-z0-9-]+$/');

        $field = $form->addCheckboxField('required');
        $field->addOption($this->i18n('iwcc_cookiegroup_required'), 1);
        /*
                $field = $form->addSelectField('domain');
                $field->setLabel($this->i18n('iwcc_domain'));
                $select = $field->getSelect();
                $select->addOption('-', '');
                foreach ($domains as $v)
                {
                    $select->addOption($v['uid'], $v['id']);
                }
        */
        $field = $form->addCheckboxField('domain');
        $field->setLabel($this->i18n('iwcc_domain'));
        foreach ($domains as $v) {
            $field->addOption($v['uid'], $v['id']);
        }

        $field = $form->addPrioField('prio');
        $field->setWhereCondition('clang_id = '.$clang_id);
        $field->setLabel($this->i18n('prio'));
        $field->setLabelField('uid');
    } else {
        $form->addRawField(iwcc_rex_form::getFakeText($this->i18n('iwcc_uid'), $form->getSql()->getValue('uid')));
        $form->addRawField(iwcc_rex_form::getFakeCheckbox('', [[$form->getSql()->getValue('required'), $this->i18n('iwcc_cookiegroup_required')]]));

        $checkboxes = [];
        $checkedBoxes = array_filter(explode('|', $form->getSql()->getValue('domain')));
        foreach ($domains as $v) {
            $checked = (in_array($v['id'], $checkedBoxes)) ? '|1|' : '';
            $checkboxes[] = [$checked, $v['uid']];
        }
        $form->addRawField(iwcc_rex_form::getFakeCheckbox('', $checkboxes));

    }
    $field = $form->addTextField('name');
    $field->setLabel($this->i18n('iwcc_name'));

    $field = $form->addTextAreaField('description');
    $field->setLabel($this->i18n('iwcc_description'));

    $db = rex_sql::factory();
    $db->setTable(rex::getTable('iwcc_cookie'));
    $db->setWhere('clang_id = '.$clang_id.' AND uid != "iwcc" ORDER BY uid ASC');
    $db->select('DISTINCT uid');
    $cookies = $db->getArray();

    if ($clang_id == rex_clang::getStartId() || !$form->isEditMode()) {
        if ($cookies) {
            $field = $form->addCheckboxField('cookie');
            $field->setLabel($this->i18n('iwcc_cookies'));
            foreach ($cookies as $v) {
                $field->addOption($v['uid'], $v['uid']);
            }
        }

    } else {
        if ($cookies) {
            $checkboxes = [];
            $checkedBoxes = array_filter(explode('|', $form->getSql()->getValue('cookie')));
            foreach ($cookies as $v) {
                $checked = (in_array($v['uid'], $checkedBoxes)) ? '|1|' : '';
                $checkboxes[] = [$checked, $v['uid']];
            }
            $form->addRawField(iwcc_rex_form::getFakeCheckbox('', $checkboxes));
        }
    }

    $title = $form->isEditMode() ? $this->i18n('iwcc_cookiegroup_edit') : $this->i18n('iwcc_cookiegroup_add');
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
    $qry = '
    SELECT pid,uid,name,domain,cookie 
    FROM '.$table.' 
    WHERE clang_id = '.$clang_id.' 
    ORDER BY prio';

    $list = rex_list::factory($qry, 100, '', $listDebug);
    $list->addParam('page', rex_be_controller::getCurrentPage());
    $list->addTableAttribute('class', 'iwcc-table iwcc-table-cookiegroup');

    $list->removeColumn('pid');
    $list->setColumnLabel('domain', $this->i18n('iwcc_domain'));
    $list->setColumnSortable('domain');
    $list->setColumnFormat('domain', 'custom', 'iwcc_rex_list::formatDomain');
    $list->setColumnLabel('cookie', $this->i18n('iwcc_cookies'));
    $list->setColumnFormat('cookie', 'custom', 'iwcc_rex_list::formatCookie');
    $list->setColumnLabel('uid', $this->i18n('iwcc_uid'));
    $list->setColumnSortable('uid');
    $list->setColumnLabel('name', $this->i18n('iwcc_name'));
    $list->setColumnSortable('name');

    $tdIcon = '<i class="fa fa-coffee"></i>';
    $thIcon = '<a href="'.$list->getUrl(['func' => 'add']).'"'.rex::getAccesskey(rex_i18n::msg('add'), 'add').'><i class="rex-icon rex-icon-add"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'pid' => '###pid###']);

    $list->addColumn(rex_i18n::msg('function'), '<i class="rex-icon rex-icon-edit"></i> '.rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('function'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('function'), ['pid' => '###pid###', 'func' => 'edit', 'start' => rex_request('start', 'string')]);

    $list->addColumn(rex_i18n::msg('delete'), '<i class="rex-icon rex-icon-delete"></i> '.rex_i18n::msg('delete'));
    $list->setColumnLayout(rex_i18n::msg('delete'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('delete'), ['pid' => '###pid###', 'func' => 'delete'] + $csrf->getUrlParams());
    $list->addLinkAttribute(rex_i18n::msg('delete'), 'onclick', 'return confirm(\' ###uid### '.rex_i18n::msg('delete').' ?\')');

    $content = $list->get();

    $fragment = new rex_fragment();
    $fragment->setVar('title', $this->i18n('iwcc_cookiegroups'));
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}
