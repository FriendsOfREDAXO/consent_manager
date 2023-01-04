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

// Add Text for new Button button_select_none
if (rex_string::versionCompare($this->getVersion(), '4.0', '<'))
{
    $sql = \rex_sql::factory();
    $sql->setQuery('SELECT count(*) AS `count` FROM `'. rex::getTablePrefix() .'consent_manager_text` WHERE `uid` = \'button_select_none\'');
    if ('0' === $sql->getValue('count')) {
        foreach (rex_clang::getAllIds() as $lang) {
            $sql = \rex_sql::factory();
            $sql->setTable(rex::getTable('consent_manager_text'));
            $sql->setValue('uid', 'button_select_none');
            $sql->setValue('clang_id', $lang);
            $sql->setValue('text', 'Alles ablehnen');
            $sql->insert();
        }
    }
}
