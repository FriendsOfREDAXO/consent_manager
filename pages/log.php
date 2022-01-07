<?php
$addon = rex_addon::get('consent_manager');

$list = rex_list::factory(
    '
    SELECT `id`, `createdate`, `domain`, `consents`, `cachelogid`, `consentid`
    FROM ' . rex::getTable('consent_manager_consent_log') . '
    ORDER by `createdate` DESC, `cachelogid` ASC
    ',
    30, 'Consent-Log', false);

$list->addTableColumnGroup([40, 160, 120, '*', 40, 200]);

$list->setColumnSortable('id', 'asc');
$list->setColumnSortable('createdate', 'desc');
$list->setColumnSortable('domain', 'asc');
$list->setColumnSortable('cachelogid', 'asc');

$list->setColumnLabel('id', $addon->i18n('thead_id'));
$list->setColumnLabel('createdate', $addon->i18n('thead_createdate'));
$list->setColumnLabel('domain', $addon->i18n('thead_domain'));
$list->setColumnLabel('consents', $addon->i18n('thead_consents'));
$list->setColumnLabel('cachelogid', $addon->i18n('thead_cachelogid'));
$list->setColumnLabel('consentid', $addon->i18n('thead_consentid'));

$list->setColumnFormat('createdate', 'custom', static function ($params) {
    $list = $params['list'];
    $str = date('d.m.Y H:i:s', strtotime($list->getValue('createdate')));
    return $str;
});
$list->setColumnFormat('consents', 'custom', static function ($params) {
    $list = $params['list'];
    $consents = json_decode($list->getValue('consents'));
    $str = implode(', ', $consents);
    return $str;
});
$list->setNoRowsMessage($addon->i18n('list_no_rows'));

$list->addTableAttribute('class', 'table-striped table-hover');

$fragment = new rex_fragment();
$fragment->setVar('title', $addon->i18n('thead_title'));
$fragment->setVar('content', $list->get(), false);
echo $fragment->parse('core/page/section.php');
