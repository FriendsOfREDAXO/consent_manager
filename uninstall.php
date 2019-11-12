<?php
rex_sql_table::get(rex::getTable('iwcc_cookiegroup'))->drop();
rex_sql_table::get(rex::getTable('iwcc_cookie'))->drop();
rex_sql_table::get(rex::getTable('iwcc_text'))->drop();
rex_sql_table::get(rex::getTable('iwcc_domain'))->drop();