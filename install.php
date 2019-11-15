<?php
rex_sql_table::get(rex::getTable('iwcc_cookie'))
    ->ensureColumn(new rex_sql_column('pid', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new rex_sql_column('id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('clang_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('uid', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('service_name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('provider', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('provider_link_privacy', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('definition', 'text'))
    ->ensureColumn(new rex_sql_column('createuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('updateuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('createdate', 'datetime'))
    ->ensureColumn(new rex_sql_column('updatedate', 'datetime'))
    ->setPrimaryKey('pid')
    ->ensure();

rex_sql_table::get(rex::getTable('iwcc_cookiegroup'))
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

rex_sql_table::get(rex::getTable('iwcc_domain'))
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

rex_sql_table::get(rex::getTable('iwcc_text'))
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


if (!$this->hasConfig()) {
    rex_sql_util::importDump(rex_addon::get('iwcc')->getPath('_install.sql'));
    iwcc_clang::addonJustInstalled();
    $this->setConfig('config', []);
}
