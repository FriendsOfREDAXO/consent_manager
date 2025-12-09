<?php

use FriendsOfRedaxo\ConsentManager\Cache;

$addon = rex_addon::get('consent_manager');

$justinstalled = true;

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
    ->ensureColumn(new rex_sql_column('script_unselect', 'text'))
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
    ->ensureColumn(new rex_sql_column('google_consent_mode_enabled', 'varchar(20)', true, 'disabled'))
    ->ensureColumn(new rex_sql_column('google_consent_mode_config', 'text'))
    ->ensureColumn(new rex_sql_column('google_consent_mode_debug', 'tinyint(1)', true, '0'))
    ->ensureColumn(new rex_sql_column('inline_only_mode', 'varchar(20)', true, 'disabled'))
    ->ensureColumn(new rex_sql_column('oembed_enabled', 'tinyint(1)', true, '1'))
    ->ensureColumn(new rex_sql_column('oembed_video_width', 'int(10) unsigned', true, '640'))
    ->ensureColumn(new rex_sql_column('oembed_video_height', 'int(10) unsigned', true, '360'))
    ->ensureColumn(new rex_sql_column('oembed_show_allow_all', 'tinyint(1)', true, '0'))
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

if (-1 === $addon->getConfig('justInstalled', -1)) {
    $addon->setConfig('justInstalled', $justinstalled);
}

// Add Text for new Button button_select_none
if (rex_version::compare($addon->getVersion(), '4.0', '<')) {
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT count(*) AS `count` FROM `' . rex::getTablePrefix() . 'consent_manager_text` WHERE `uid` = \'button_accept\'');
    if ($sql->getValue('count') > 0) {
        $sql->setQuery('SELECT count(*) AS `count` FROM `' . rex::getTablePrefix() . 'consent_manager_text` WHERE `uid` = \'button_select_none\'');
        if (0 === (int) $sql->getValue('count')) { /** @phpstan-ignore-line */
            foreach (rex_clang::getAllIds() as $lang) {
                $sql = rex_sql::factory();
                $sql->setTable(rex::getTable('consent_manager_text'));
                $sql->setValue('id', 23);
                $sql->setValue('uid', 'button_select_none');
                $sql->setValue('clang_id', $lang);
                $sql->setValue('text', 'Nur notwendige');
                $sql->insert();
            }
        }
    }
}

// Neue Text-Keys für Inline-Consent-System hinzufügen
$inlineTexts = [
    'button_inline_details' => 'Einstellungen',
    'inline_placeholder_text' => 'Einmal laden',
    'button_inline_allow_all' => 'Alle erlauben',
    'inline_action_text' => 'Was möchten Sie tun?',
    'inline_privacy_notice' => 'Für die Anzeige werden Cookies benötigt.',
    'inline_title_fallback' => 'Externes Medium',
    'inline_privacy_link_text' => 'Datenschutzerklärung von',
];

foreach ($inlineTexts as $uid => $defaultText) {
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT count(*) AS `count` FROM `' . rex::getTablePrefix() . 'consent_manager_text` WHERE `uid` = :uid', ['uid' => $uid]);
    if (0 === $sql->getValue('count')) {
        foreach (rex_clang::getAllIds() as $lang) {
            $sql = rex_sql::factory();
            $sql->setTable(rex::getTable('consent_manager_text'));
            $sql->setValue('uid', $uid);
            $sql->setValue('clang_id', $lang);
            $sql->setValue('text', $defaultText);
            $sql->insert();
        }
    }
}

// Mediamanager-Type für Thumbnails erstellen (falls Media Manager verfügbar)
if (rex_addon::get('media_manager')->isAvailable()) {
    // Prüfe ob Type bereits existiert
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT id FROM ' . rex::getTable('media_manager_type') . ' WHERE name = ?', ['consent_manager_thumbnail']);

    if (0 === $sql->getRows()) {
        // Media Manager Type erstellen (als normaler Type, nicht System)
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('media_manager_type'));
        $sql->setValue('name', 'consent_manager_thumbnail');
        $sql->setValue('description', 'Consent Manager External Thumbnails (YouTube, Vimeo)');
        $sql->setValue('status', 0); // 0 = normaler Type, 1 = System-Type (nicht editierbar)
        $sql->addGlobalCreateFields();
        $sql->addGlobalUpdateFields();
        $sql->insert();

        $typeId = $sql->getLastId();

        // Effect für externe Thumbnails hinzufügen
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('media_manager_type_effect'));
        $sql->setValue('type_id', $typeId);
        $sql->setValue('effect', 'external_thumbnail');
        $sql->setValue('priority', 1);
        $sql->setValue('parameters', json_encode([
            'rex_effect_external_thumbnail' => [
                'rex_effect_external_thumbnail_service' => 'youtube',
                'rex_effect_external_thumbnail_video_id' => '',
                'rex_effect_external_thumbnail_cache_ttl' => 168,
            ],
        ]));
        $sql->addGlobalCreateFields();
        $sql->addGlobalUpdateFields();
        $sql->insert();

        // Optional: Resize-Effect hinzufügen für einheitliche Größe
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('media_manager_type_effect'));
        $sql->setValue('type_id', $typeId);
        $sql->setValue('effect', 'resize');
        $sql->setValue('priority', 2);
        $sql->setValue('parameters', json_encode([
            'rex_effect_resize' => [
                'rex_effect_resize_width' => '480',
                'rex_effect_resize_height' => '360',
                'rex_effect_resize_style' => 'maximum',
                'rex_effect_resize_allow_enlarge' => 'not_enlarge',
            ],
        ]));
        $sql->addGlobalCreateFields();
        $sql->addGlobalUpdateFields();
        $sql->insert();
    }
}

/**
 * Mit der Umstellung auf Namespace wurde auch die Cronjob-Klasse "rex_cronjob_log_delete" umbenannt in
 * "FriendsOfRedaxo\ConsentManager\Cronjob\LogDelete". Damit bestehende Cronjobs nach dem Update weiterhin funktionieren,
 * muss der Cronjob-Typ in der Tabelle rex_cronjob auf den neuen Wert inkl. Namespace geändert werden.
 */
if (rex_addon::get('cronjob')->isAvailable()) {
    $sql = rex_sql::factory();
    $sql->setTable(rex::getTable('cronjob'));
    $sql->setValue('type', 'FriendsOfRedaxo\\ConsentManager\\Cronjob\\LogDelete');
    $sql->setWhere('type like :old', [':old' => 'rex_cronjob_log_delete']);
    $sql->addGlobalUpdateFields();
    $sql->update();
}

// Rewrite
// Beim Update kann es sein, dass die neue Klasse noch nicht geladen werden kann.
if (class_exists(Cache::class)) {
    Cache::forceWrite();
} elseif (class_exists('consent_manager_cache')) {
    // Legacy compatibility - use legacy Cache class
    /** @phpstan-ignore-next-line */
    consent_manager_cache::forceWrite();
}

// Delete Template cache
rex_dir::delete(rex_path::cache('addons/templates'));
// Delete Module cache
rex_dir::delete(rex_path::cache('templates'));
