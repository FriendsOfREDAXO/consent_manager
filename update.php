<?php
$sql = \rex_sql::factory();
$sql->setQuery("SELECT * FROM ". \rex::getTablePrefix() ."iwcc_text WHERE `uid` = 'button_accept';");
if($sql->getRows() == 0) {
    foreach(\rex_clang::getAllIds() as $clang_id) {
	    $sql->setQuery("INSERT INTO `". \rex::getTablePrefix() ."iwcc_text` (`id`, `clang_id`, `uid`, `text`) VALUES (7, ". $clang_id .", 'button_accept', 'Auswahl bestätigen');");
    }
}

$sql = \rex_sql::factory();
$sql->setQuery("SELECT * FROM ". \rex::getTablePrefix() ."iwcc_text WHERE `uid` = 'button_select_all';");
if($sql->getRows() == 0) {
    foreach(\rex_clang::getAllIds() as $clang_id) {
	    $sql->setQuery("INSERT INTO `". \rex::getTablePrefix() ."iwcc_text` (`id`, `clang_id`, `uid`, `text`) VALUES (8, ". $clang_id .", 'button_select_all', 'Alle auswählen');");
    }
}
