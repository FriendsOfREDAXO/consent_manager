<?php

// Cache-Klasse wird ggf. direkt eingebunden, da beim Install/Update der Autoloader nicht aktiv ist
if (!class_exists('FriendsOfRedaxo\ConsentManager\Cache')) {
    require_once __DIR__ . '/lib/Cache.php';
}

use FriendsOfRedaxo\ConsentManager\Cache;

$addon = rex_addon::get('consent_manager');
$addon->includeFile(__DIR__ . '/install.php');
$addon->setConfig('forceCache', true);

// Copy scripts to every language
if (count(rex_clang::getAllIds()) > 1) {
    $sql = rex_sql::factory();
    $sql->setQuery(
        'SELECT `start_lang`.uid, `start_lang`.script FROM `' . rex::getTablePrefix() . 'consent_manager_cookie` AS `start_lang` '
        . 'LEFT JOIN `' . rex::getTablePrefix() . 'consent_manager_cookie` AS `other_lang` ON `start_lang`.uid = `other_lang`.uid '
        . 'WHERE `start_lang`.clang_id = ? AND `start_lang`.script <> ? AND `other_lang`.script = ? '
        . 'GROUP BY uid, script',
        [rex_clang::getStartId(), '', ''],
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

// Ensure alle Domain-Tabelle Spalten (für Updates von älteren Versionen)
rex_sql_table::get(rex::getTable('consent_manager_domain'))
    ->ensureColumn(new rex_sql_column('google_consent_mode_enabled', 'varchar(20)', true, 'disabled'))
    ->ensureColumn(new rex_sql_column('google_consent_mode_config', 'text'))
    ->ensureColumn(new rex_sql_column('google_consent_mode_debug', 'tinyint(1)', true, '0'))
    ->ensureColumn(new rex_sql_column('inline_only_mode', 'varchar(20)', true, 'disabled'))
    ->ensureColumn(new rex_sql_column('theme', 'varchar(255)', true, ''))
    ->ensureColumn(new rex_sql_column('auto_inject', 'tinyint(1)', true, '0'))
    ->ensureColumn(new rex_sql_column('auto_inject_reload_on_consent', 'tinyint(1)', true, '0'))
    ->ensureColumn(new rex_sql_column('auto_inject_delay', 'int(10) unsigned', true, '0'))
    ->ensureColumn(new rex_sql_column('auto_inject_focus', 'tinyint(1)', true, '1'))
    ->ensureColumn(new rex_sql_column('auto_inject_include_templates', 'text'))
    ->ensureColumn(new rex_sql_column('oembed_enabled', 'tinyint(1)', true, '0'))
    ->ensureColumn(new rex_sql_column('oembed_video_width', 'int(10) unsigned', true, '640'))
    ->ensureColumn(new rex_sql_column('oembed_video_height', 'int(10) unsigned', true, '360'))
    ->ensureColumn(new rex_sql_column('oembed_show_allow_all', 'tinyint(1)', true, '0'))
    ->ensure();

// Update cookie lifetime text for consent_manager cookie (Privacy by Design)
$sql = rex_sql::factory();
$sql->setQuery('UPDATE `' . rex::getTablePrefix() . 'consent_manager_cookie` SET definition = REPLACE(definition, "time: \"1 Jahr\"", "time: \"14 Tage / 1 Jahr\"") WHERE uid = "consent_manager"');

// Cleanup: Remove old background images from assets (no longer needed with new mockup preview)
$publicAssetsPath = rex_path::addonAssets('consent_manager');
if (is_dir($publicAssetsPath)) {
    $imagesToDelete = [
        'abstract-building-bw.jpg',
        'abstract-wall-bw.jpg',
        'blue-mountains.jpg',
        'gradienta-abstract-1.jpg',
        'gradienta-abstract-2.jpg',
        'harley-davidson-motorcycle.jpg',
        'milad-fakurian-abstract.jpg',
        'mountains-houses.jpg',
        'pawel-czerwinski-abstract-1.jpg',
        'pawel-czerwinski-abstract-2.jpg',
        'steve-johnson-abstract.jpg',
        'water-blue-sky.jpg',
    ];

    foreach ($imagesToDelete as $image) {
        $imagePath = $publicAssetsPath . $image;
        if (file_exists($imagePath)) {
            rex_file::delete($imagePath);
        }
    }
}
