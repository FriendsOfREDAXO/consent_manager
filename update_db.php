<?php
/**
 * Database Update für Google Consent Mode v2
 * Dieses Skript stellt sicher, dass das Feld 'google_consent_mode_enabled' existiert
 */

// Prüfen ob wir in REDAXO sind
if (!class_exists('rex')) {
    die('Nur in REDAXO ausführbar');
}

$addon = rex_addon::get('consent_manager');
$table = rex::getTable('consent_manager_domain');

echo "<h2>Google Consent Mode v2 - Database Update</h2>\n";

try {
    // Prüfen ob Tabelle existiert
    if (!rex_sql_table::get($table)->exists()) {
        echo "<p style='color: red;'>❌ Tabelle {$table} existiert nicht!</p>\n";
        exit;
    }
    
    echo "<p>✅ Tabelle {$table} gefunden</p>\n";
    
    // Spalte google_consent_mode_enabled hinzufügen/sicherstellen
    rex_sql_table::get($table)
        ->ensureColumn(new rex_sql_column('google_consent_mode_enabled', 'tinyint(1)', true, '0'))
        ->alter();
    
    echo "<p>✅ Spalte 'google_consent_mode_enabled' hinzugefügt/aktualisiert</p>\n";
    
    // Prüfen ob Spalte wirklich existiert
    $sql = rex_sql::factory();
    $result = $sql->getArray("SHOW COLUMNS FROM `{$table}` LIKE 'google_consent_mode_enabled'");
    
    if (empty($result)) {
        echo "<p style='color: red;'>❌ Spalte wurde nicht erstellt!</p>\n";
    } else {
        echo "<p style='color: green;'>✅ Spalte 'google_consent_mode_enabled' ist vorhanden:</p>\n";
        echo "<pre>" . print_r($result[0], true) . "</pre>\n";
    }
    
    // Cache löschen
    consent_manager_cache::forceWrite();
    echo "<p>✅ Cache gelöscht</p>\n";
    
    echo "<p><strong>Update abgeschlossen!</strong></p>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Fehler: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}
?>
