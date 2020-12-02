<?php

if ( rex_sql_table::get(rex::getTable('iwcc_cookie'))->exists() && !rex_sql_table::get(rex::getTable('consent_manager_cookie'))->exists() ) {
    rex_sql_table::get(rex::getTable('iwcc_cookie'))->setName('consent_manager_cookie')->alter();
}
if ( rex_sql_table::get(rex::getTable('iwcc_cookiegroup'))->exists() && !rex_sql_table::get(rex::getTable('consent_manager_cookiegroup'))->exists() ) {
    rex_sql_table::get(rex::getTable('iwcc_cookiegroup'))->setName('consent_manager_cookiegroup')->alter();
}
if ( rex_sql_table::get(rex::getTable('iwcc_text'))->exists() && !rex_sql_table::get(rex::getTable('consent_manager_text'))->exists() ) {
    rex_sql_table::get(rex::getTable('iwcc_text'))->setName('consent_manager_text')->alter();
}
if ( rex_sql_table::get(rex::getTable('iwcc_domain'))->exists() && !rex_sql_table::get(rex::getTable('consent_manager_domain'))->exists() ) {
    rex_sql_table::get(rex::getTable('iwcc_domain'))->setName('consent_manager_domain')->alter();
}
if ( rex_sql_table::get(rex::getTable('iwcc_cache_log'))->exists() && !rex_sql_table::get(rex::getTable('consent_manager_cache_log'))->exists() ) {
    rex_sql_table::get(rex::getTable('iwcc_cache_log'))->setName('consent_manager_cache_log')->alter();
}
if ( rex_sql_table::get(rex::getTable('iwcc_consent_log'))->exists() && !rex_sql_table::get(rex::getTable('consent_manager_consent_log'))->exists() ) {
    rex_sql_table::get(rex::getTable('iwcc_consent_log'))->setName('consent_manager_consent_log')->alter();
}

$addon = rex_addon::get('consent_manager');
$addon->includeFile(__DIR__.'/install.php');
$this->setConfig('forceCache', true);
