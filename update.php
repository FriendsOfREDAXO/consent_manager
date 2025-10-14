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

// Neue Text-Keys für Inline-Consent-System hinzufügen
$inlineTexts = [
    'button_inline_details' => 'Einstellungen',
    'inline_placeholder_text' => 'Einmal laden',
    'button_inline_allow_all' => 'Alle erlauben',
    'inline_action_text' => 'Was möchten Sie tun?',
    'inline_privacy_notice' => 'Für die Anzeige werden Cookies benötigt.',
    'inline_title_fallback' => 'Externes Medium',
    'inline_privacy_link_text' => 'Datenschutzerklärung von'
];

foreach ($inlineTexts as $uid => $defaultText) {
    $sql = \rex_sql::factory();
    $sql->setQuery('SELECT count(*) AS `count` FROM `' . rex::getTablePrefix() . 'consent_manager_text` WHERE `uid` = :uid', ['uid' => $uid]);
    if (0 === (int) $sql->getValue('count')) {
        foreach (rex_clang::getAllIds() as $lang) {
            $sql = \rex_sql::factory();
            $sql->setTable(rex::getTable('consent_manager_text'));
            $sql->setValue('uid', $uid);
            $sql->setValue('clang_id', $lang);
            $sql->setValue('text', $defaultText);
            $sql->insert();
        }
    }
}
