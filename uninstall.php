<?php

// Alle Konfigurationswerte entfernen, damit Neuinstallationen sauber starten
// (z. B. damit justInstalled nach einem Reinstall wieder korrekt gesetzt wird).
if (method_exists('rex_config', 'removeNamespace')) {
	rex_config::removeNamespace('consent_manager');
} else {
	rex_config::remove('consent_manager', 'config');
	rex_config::remove('consent_manager', 'justInstalled');
	rex_config::remove('consent_manager', 'forceCache');
}
rex_sql_table::get(rex::getTable('consent_manager_cookiegroup'))->drop();
rex_sql_table::get(rex::getTable('consent_manager_cookie'))->drop();
rex_sql_table::get(rex::getTable('consent_manager_text'))->drop();
rex_sql_table::get(rex::getTable('consent_manager_domain'))->drop();
rex_sql_table::get(rex::getTable('consent_manager_cache_log'))->drop();
rex_sql_table::get(rex::getTable('consent_manager_consent_log'))->drop();
