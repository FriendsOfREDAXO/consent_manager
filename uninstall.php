<?php
rex_config::remove('consent_manager', 'config');
rex_sql_table::get(rex::getTable('consent_manager_cookiegroup'))->drop();
rex_sql_table::get(rex::getTable('consent_manager_cookie'))->drop();
rex_sql_table::get(rex::getTable('consent_manager_text'))->drop();
rex_sql_table::get(rex::getTable('consent_manager_domain'))->drop();
