<?php

// Cache-Klasse wird ggf. direkt eingebunden, da beim Install/Update der Autoloader nicht aktiv ist
if (!class_exists('FriendsOfRedaxo\ConsentManager\Cache')) {
    require_once __DIR__ . '/lib/Cache.php';
}

use FriendsOfRedaxo\ConsentManager\Cache;

$addon = rex_addon::get('consent_manager');
$addon->includeFile(__DIR__ . '/install.php');

// Migration: Verschiebe Cache von data/ nach cache/ (seit 5.3.0)
$oldCacheFile = rex_path::addonData('consent_manager', 'config.json');
$newCacheFile = rex_path::addonCache('consent_manager', 'config.json');

if (file_exists($oldCacheFile)) {
    // Alten Cache nach cache/ kopieren (wenn neue Datei nicht existiert)
    if (!file_exists($newCacheFile)) {
        $cacheContent = file_get_contents($oldCacheFile);
        if (false !== $cacheContent) {
            rex_file::putCache($newCacheFile, json_decode($cacheContent, true));
        }
    }
    // Alten Cache in jedem Fall löschen
    rex_file::delete($oldCacheFile);
}

$addon->setConfig('forceCache', true);

// Copy scripts to every language
if (count(rex_clang::getAllIds()) > 1) {
    $sql = rex_sql::factory();
    $sql->setQuery(
        'SELECT `start_lang`.uid, `start_lang`.script FROM `' . rex::getTablePrefix() . 'consent_manager_cookie` AS `start_lang` '
        . 'LEFT JOIN `' . rex::getTablePrefix() . 'consent_manager_cookie` AS `other_lang` ON `start_lang`.uid = `other_lang`.uid '
        . 'WHERE `start_lang`.clang_id = ? AND `start_lang`.script <> ? AND `other_lang`.script = ? '
        . 'GROUP BY uid, script',
        [rex_clang::getStartId(), '', '']
    );
    for ($i = 0; $i < $sql->getRows(); ++$i) {
        $db = rex_sql::factory();
        $db->setTable(rex::getTable('consent_manager_cookie'));
        $db->setWhere('uid = :uid AND clang_id <> :clang_id AND script = ""', ['uid' => $sql->getValue('uid'), 'clang_id' => rex_clang::getStartId()]);
        $db->setValue('script', $sql->getValue('script'));
        $db->update();

        $sql->next();
    }

    // Write cache
    if ($addon->isAvailable()) {
        Cache::forceWrite();
    }
}

// Log normalisierte Domains zu Kleinbuchstaben (Fix für Issue #339)
$sql = rex_sql::factory();
$sql->setQuery('UPDATE `' . rex::getTablePrefix() . 'consent_manager_consent_log` SET domain = LOWER(domain) WHERE domain != LOWER(domain)', []);

// Ensure inline_only_mode Spalte in Domain-Tabelle
rex_sql_table::get(rex::getTable('consent_manager_domain'))
    ->ensureColumn(new rex_sql_column('inline_only_mode', 'varchar(20)', true, 'disabled'))
    ->ensure();

// Ensure oembed_enabled Spalte in Domain-Tabelle (default: 0 = deaktiviert beim Update)
rex_sql_table::get(rex::getTable('consent_manager_domain'))
    ->ensureColumn(new rex_sql_column('oembed_enabled', 'tinyint(1)', true, '0'))
    ->ensureColumn(new rex_sql_column('oembed_video_width', 'int(10) unsigned', true, '640'))
    ->ensureColumn(new rex_sql_column('oembed_video_height', 'int(10) unsigned', true, '360'))
    ->ensureColumn(new rex_sql_column('oembed_show_allow_all', 'tinyint(1)', true, '0'))
    ->ensure();

// Update cookie lifetime text for consent_manager cookie (Privacy by Design)
$sql = rex_sql::factory();
$sql->setQuery('UPDATE `' . rex::getTablePrefix() . 'consent_manager_cookie` SET definition = REPLACE(definition, "time: \"1 Jahr\"", "time: \"14 Tage / 1 Jahr\"") WHERE uid = "consent_manager"');

