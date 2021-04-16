<?php

$justinstalled = true;

if (rex_sql_table::get(rex::getTable('iwcc_cookie'))->exists() && !rex_sql_table::get(rex::getTable('consent_manager_cookie'))->exists()) {
    rex_sql_table::get(rex::getTable('iwcc_cookie'))->setName(rex::getTable('consent_manager_cookie'))->alter();
    $justinstalled = false;
}
if (rex_sql_table::get(rex::getTable('iwcc_cookiegroup'))->exists() && !rex_sql_table::get(rex::getTable('consent_manager_cookiegroup'))->exists()) {
    rex_sql_table::get(rex::getTable('iwcc_cookiegroup'))->setName(rex::getTable('consent_manager_cookiegroup'))->alter();
}
if (rex_sql_table::get(rex::getTable('iwcc_text'))->exists() && !rex_sql_table::get(rex::getTable('consent_manager_text'))->exists()) {
    rex_sql_table::get(rex::getTable('iwcc_text'))->setName(rex::getTable('consent_manager_text'))->alter();
}
if (rex_sql_table::get(rex::getTable('iwcc_domain'))->exists() && !rex_sql_table::get(rex::getTable('consent_manager_domain'))->exists()) {
    rex_sql_table::get(rex::getTable('iwcc_domain'))->setName(rex::getTable('consent_manager_domain'))->alter();
}
if (rex_sql_table::get(rex::getTable('iwcc_cache_log'))->exists() && !rex_sql_table::get(rex::getTable('consent_manager_cache_log'))->exists()) {
    rex_sql_table::get(rex::getTable('iwcc_cache_log'))->setName(rex::getTable('consent_manager_cache_log'))->alter();
}
if (rex_sql_table::get(rex::getTable('iwcc_consent_log'))->exists() && !rex_sql_table::get(rex::getTable('consent_manager_consent_log'))->exists()) {
    rex_sql_table::get(rex::getTable('iwcc_consent_log'))->setName(rex::getTable('consent_manager_consent_log'))->alter();
}

rex_sql_table::get(rex::getTable('consent_manager_cookie'))
    ->ensureColumn(new rex_sql_column('pid', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new rex_sql_column('id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('clang_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('uid', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('service_name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('provider', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('provider_link_privacy', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('definition', 'text'))
    ->ensureColumn(new rex_sql_column('script', 'text'))
    ->ensureColumn(new rex_sql_column('placeholder_text', 'text'))
    ->ensureColumn(new rex_sql_column('placeholder_image', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('createuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('updateuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('createdate', 'datetime'))
    ->ensureColumn(new rex_sql_column('updatedate', 'datetime'))
    ->setPrimaryKey('pid')
    ->ensure();

rex_sql_table::get(rex::getTable('consent_manager_cookiegroup'))
    ->ensureColumn(new rex_sql_column('pid', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new rex_sql_column('id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('clang_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('domain', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('uid', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('prio', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('required', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('description', 'text'))
    ->ensureColumn(new rex_sql_column('cookie', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('script', 'text'))
    ->ensureColumn(new rex_sql_column('createuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('updateuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('createdate', 'datetime'))
    ->ensureColumn(new rex_sql_column('updatedate', 'datetime'))
    ->setPrimaryKey('pid')
    ->ensure();

rex_sql_table::get(rex::getTable('consent_manager_domain'))
    ->ensureColumn(new rex_sql_column('id', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new rex_sql_column('uid', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('privacy_policy', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('legal_notice', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('createuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('updateuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('createdate', 'datetime'))
    ->ensureColumn(new rex_sql_column('updatedate', 'datetime'))
    ->setPrimaryKey('id')
    ->ensure();

rex_sql_table::get(rex::getTable('consent_manager_text'))
    ->ensureColumn(new rex_sql_column('pid', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new rex_sql_column('id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('clang_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('uid', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('text', 'text'))
    ->ensureColumn(new rex_sql_column('createuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('updateuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('createdate', 'datetime'))
    ->ensureColumn(new rex_sql_column('updatedate', 'datetime'))
    ->setPrimaryKey('pid')
    ->ensure();

rex_sql_table::get(rex::getTable('consent_manager_cache_log'))
    ->ensureColumn(new rex_sql_column('id', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new rex_sql_column('consent', 'text'))
    ->ensureColumn(new rex_sql_column('createuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('createdate', 'datetime'))
    ->setPrimaryKey('id')
    ->ensure();

rex_sql_table::get(rex::getTable('consent_manager_consent_log'))
    ->ensureColumn(new rex_sql_column('id', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new rex_sql_column('domain', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('consentid', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('consents', 'text'))
    ->ensureColumn(new rex_sql_column('cachelogid', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('ip', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('createdate', 'datetime'))
    ->setPrimaryKey('id')
    ->ensure();

if (-1 === $this->getConfig('justInstalled', -1)) {
    $this->setConfig('justInstalled', $justinstalled);
}
