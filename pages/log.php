<?php

$searchvalue = rex_request::request('Consent_Search', 'string', '');
$where = '';

if ('' !== $searchvalue) {
    $dosearch = str_replace(['\'', ';', '"'], '', $searchvalue);
    $sql = rex_sql::factory();
    if (false !== DateTime::createFromFormat('d.m.Y', $searchvalue)) {
        $searchdate = strtotime($dosearch);
        if (false !== $searchdate) {
            $where = ' WHERE `createdate` LIKE ' . $sql->escape(date('Y-m-d', $searchdate) . '%') . ' ';
        }
    }
    $intbool = (bool) preg_match('/^[1-9][0-9]{0,15}$/', $dosearch);
    if (true === $intbool) {
        $where = ' WHERE `cachelogid` = ' . $sql->escape($dosearch) . ' ';
    }
    if ('' === $where) {
        $where = ' WHERE `domain` LIKE ' . $sql->escape($dosearch . '%') . ' OR `ip` LIKE ' . $sql->escape($dosearch . '%') . ' OR `consentid` LIKE ' . $sql->escape($dosearch . '%') . ' ';
    }
}

$list = rex_list::factory(
    '
    SELECT `id`, `createdate`, `domain`, `ip`, `consents`, `cachelogid`, `consentid`
    FROM ' . rex::getTable('consent_manager_consent_log') . ' ' . $where . '
    ORDER by `createdate` DESC, `cachelogid` ASC
    ',
    30, 'Consent-Log', false);

if ('' !== $searchvalue) {
    $list->addParam('Consent_Search', $searchvalue);
}

$list->addTableColumnGroup([40, 160, 120, 120, '*', 40, 200]);

$list->setColumnSortable('id', 'asc');
$list->setColumnSortable('createdate', 'desc');
$list->setColumnSortable('domain', 'asc');
$list->setColumnSortable('cachelogid', 'asc');

$list->setColumnLabel('id', rex_i18n::msg('consent_manager_thead_id'));
$list->setColumnLabel('createdate', rex_i18n::msg('consent_manager_thead_createdate'));
$list->setColumnLabel('domain', rex_i18n::msg('consent_manager_thead_domain'));
$list->setColumnLabel('ip', rex_i18n::msg('consent_manager_thead_ip'));
$list->setColumnLabel('consents', rex_i18n::msg('consent_manager_thead_consents'));
$list->setColumnLabel('cachelogid', rex_i18n::msg('consent_manager_thead_cachelogid'));
$list->setColumnLabel('consentid', rex_i18n::msg('consent_manager_thead_consentid'));

$list->setColumnFormat('createdate', 'custom', static function ($params) {
    $list = $params['list'];
    $str = date('d.m.Y H:i:s', strtotime($list->getValue('createdate')));
    return $str;
});
$list->setColumnFormat('consents', 'custom', static function ($params) {
    $list = $params['list'];
    $consents = (array) json_decode($list->getValue('consents'));
    $str = implode(', ', $consents);
    return $str;
});
$list->setNoRowsMessage(rex_i18n::msg('consent_manager_list_no_rows'));

$list->addTableAttribute('class', 'table table-striped table-hover');

$fragmentsearch = new rex_fragment();
$fragmentsearch->setVar('id', 'consent_manager_log_search');
$fragmentsearch->setVar('autofocus', false);
$fragmentsearch->setVar('value', $searchvalue);
$cmsearch = $fragmentsearch->parse('core/form/search.php');

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('consent_manager_thead_title'));
$fragment->setVar('options', $cmsearch, false);
$fragment->setVar('content', $list->get(), false);
echo $fragment->parse('core/page/section.php');
