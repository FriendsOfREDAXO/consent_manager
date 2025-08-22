<?php
/**
 * Database Update für Google Consent Mode v2
 * Dieses Skript stellt sicher, dass das google_consent_mode_enabled Feld existiert
 * Nur für Admin-Benutzer zugänglich
 */

// Prüfen ob wir in REDAXO sind und Benutzer ist Admin
if (!class_exists('rex') || !rex::isBackend() || !rex::getUser() || !rex::getUser()->isAdmin()) {
    die('Zugriff verweigert');
}

$addon = rex_addon::get('consent_manager');
$table = rex_sql::escapeIdentifier(rex::getTable('consent_manager_domain'));

if (rex::isDebugMode()) {
    echo "<h2>Google Consent Mode v2 - Database Update</h2>\n";
}

try {
    // Spalte google_consent_mode_enabled hinzufügen/sicherstellen
    rex_sql_table::get($table)
        ->ensureColumn(new rex_sql_column('google_consent_mode_enabled', 'tinyint(1)', true, '0'))
        ->alter();
    
    // Cache löschen
    consent_manager_cache::forceWrite();
    
    if (rex::isDebugMode()) {
        echo "<p>✅ Google Consent Mode v2 Spalte erfolgreich hinzugefügt</p>\n";
        echo "<p>✅ Cache gelöscht</p>\n";
        echo "<p><strong>Update abgeschlossen!</strong></p>\n";
    }
    
} catch (Exception $e) {
    if (rex::isDebugMode()) {
        echo "<p style='color: red;'>❌ Fehler: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    }
    // Log error for production
    rex_logger::factory()->log('error', 'Consent Manager Google Consent Mode v2 update failed: ' . $e->getMessage(), [], __FILE__, __LINE__);
}
?>
