<?php

/**
 * Demo-Modul: Inline-Consent für Google Maps
 * 
 * Ausgabe-Teil des Moduls
 */

// Werte aus dem Eingabeformular
$embedUrl = trim('REX_VALUE[1]');
$mapsTitle = 'REX_VALUE[2]' ?: 'Google Maps';
$mapsHeight = (int) 'REX_VALUE[3]' ?: 450;

// Nur ausgeben wenn Embed-URL vorhanden
if (!empty($embedUrl)) {
    
    // CSS/JS für Inline-Consent einbinden (falls noch nicht geschehen)
    if (class_exists('consent_manager_inline')) {
        echo consent_manager_inline::getCSS();
        echo consent_manager_inline::getJavaScript();
    }
    
    // Inline-Consent für Google Maps generieren
    echo doConsent('google-maps', $embedUrl, [
        'title' => $mapsTitle,
        'height' => $mapsHeight,
        'width' => '100%',
        'privacy_notice' => 'Für Google Maps werden Cookies für die Funktionalität benötigt.'
    ]);
    
} else {
    // Backend-Preview falls keine URL eingegeben
    if (rex::isBackend()) {
        echo '<div class="alert alert-warning">';
        echo '<i class="fa fa-map-marker"></i> ';
        echo '<strong>Google Maps Inline-Consent:</strong> Bitte Google Maps Embed-URL eingeben.';
        echo '</div>';
    }
}