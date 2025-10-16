<?php

$addon = rex_addon::get('consent_manager');
$addon->includeFile(__DIR__.'/install.php');
$addon->setConfig('forceCache', true);


// Copy scripts to every language
if (count(rex_clang::getAllIds()) > 1) {
    $sql = \rex_sql::factory();
    $sql->setQuery('SELECT `start_lang`.uid, `start_lang`.script FROM `'. rex::getTablePrefix() .'consent_manager_cookie` AS `start_lang` '
        . 'LEFT JOIN `'. rex::getTablePrefix() .'consent_manager_cookie` AS `other_lang` ON `start_lang`.uid = `other_lang`.uid '
        . 'WHERE `start_lang`.clang_id = '. rex_clang::getStartId() ." AND `start_lang`.script <> '' AND `other_lang`.script = '' "
        . 'GROUP BY uid, script');
    for ($i = 0; $i < $sql->getRows(); ++$i) {
        $db = rex_sql::factory();
        $db->setTable(rex::getTable('consent_manager_cookie'));
        $db->setWhere('uid = :uid AND clang_id <> :clang_id AND script = ""', ['uid' => $sql->getValue('uid'), 'clang_id' => rex_clang::getStartId()]);
        $db->setValue('script', $sql->getValue('script'));
        $db->update();

        $sql->next();
    }

    // Write cache
    consent_manager_cache::forceWrite();
}

// Update legacy default cookie "iwcc" to "consent_manager"
$sql = \rex_sql::factory();
$sql->setQuery('UPDATE `'. rex::getTablePrefix() .'consent_manager_cookie` '
    .'SET uid = "consent_manager", definition = REPLACE(definition, "name: iwcc", "name: consent_manager") '
    .'WHERE uid = "iwcc"');

// Log normalisierte Domains zu Kleinbuchstaben (Fix für Issue #339)
$sql = \rex_sql::factory();  
$sql->setQuery('UPDATE `'. rex::getTablePrefix() .'consent_manager_consent_log` SET domain = LOWER(domain) WHERE domain != LOWER(domain)');

// Ensure inline_only_mode Spalte in Domain-Tabelle
rex_sql_table::get(rex::getTable('consent_manager_domain'))
    ->ensureColumn(new rex_sql_column('inline_only_mode', 'varchar(20)', true, 'disabled'))
    ->ensure();

    // Texte für Version 4.5.0 hinzufügen
    if (rex_string::versionCompare($version, '4.5.0', '<')) {
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('consent_manager_text'));

        $buttonTexts = [
            'button_inline_details' => 'Einstellungen',
            'inline_placeholder_text' => 'Einmal laden',
            'button_inline_allow_all' => 'Alle erlauben',
            'inline_action_text' => 'Was möchten Sie tun?',
            'inline_privacy_notice' => 'Für die Anzeige werden Cookies benötigt.',
            'inline_title_fallback' => 'Externes Medium',
            'inline_privacy_link_text' => 'Datenschutzerklärung von'
        ];

        foreach ($buttonTexts as $key => $defaultValue) {
            $sql->setWhere(['uid' => $key]);
            if (!$sql->select()->getRows()) {
                $sql->setTable(rex::getTable('consent_manager_text'));
                $sql->setValues([
                    'key' => $key,
                    'value' => $defaultValue,
                    'createdate' => date('Y-m-d H:i:s'),
                    'updatedate' => date('Y-m-d H:i:s')
                ]);
                $sql->insert();
            }
            $sql->reset();
        }
        
        // Mediamanager-Type für Thumbnails erstellen (falls Media Manager verfügbar)
        if (rex_addon::get('media_manager')->isAvailable()) {
            // Prüfe ob Type bereits existiert
            $sql = rex_sql::factory();
            $sql->setQuery('SELECT id FROM ' . rex::getTable('media_manager_type') . ' WHERE name = ?', ['consent_manager_thumbnail']);
            
            if (!$sql->getRows()) {
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
                        'rex_effect_external_thumbnail_cache_ttl' => 168
                    ]
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
                        'rex_effect_resize_allow_enlarge' => 'not_enlarge'
                    ]
                ]));
                $sql->addGlobalCreateFields();
                $sql->addGlobalUpdateFields();
                $sql->insert();
            }
        }
    }
