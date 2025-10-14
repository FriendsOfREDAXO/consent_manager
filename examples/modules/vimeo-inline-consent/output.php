<?php

/**
 * Demo-Modul: Inline-Consent für Vimeo Videos
 * 
 * Ausgabe-Teil des Moduls
 */

// Werte aus dem Eingabeformular
$videoId = 'REX_VALUE[1]';
$videoTitle = 'REX_VALUE[2]' ?: 'Vimeo Video';
$videoWidth = (int) 'REX_VALUE[3]' ?: 640;
$videoHeight = (int) 'REX_VALUE[4]' ?: 360;
$customThumbnail = 'REX_VALUE[5]';

// Nur ausgeben wenn Video-ID vorhanden
if (!empty($videoId)) {
    
    // CSS/JS für Inline-Consent einbinden (falls noch nicht geschehen)
    if (class_exists('consent_manager_inline')) {
        echo consent_manager_inline::getCSS();
        echo consent_manager_inline::getJavaScript();
    }
    
    // Optionen für Vimeo
    $options = [
        'title' => $videoTitle,
        'width' => $videoWidth,
        'height' => $videoHeight,
        'privacy_notice' => 'Für Vimeo werden Cookies für erweiterte Player-Funktionen benötigt.'
    ];
    
    // Custom Thumbnail falls angegeben
    if (!empty($customThumbnail)) {
        $options['thumbnail'] = $customThumbnail;
    }
    
    // Inline-Consent für Vimeo generieren
    echo doConsent('vimeo', $videoId, $options);
    
} else {
    // Backend-Preview falls keine Video-ID eingegeben
    if (rex::isBackend()) {
        echo '<div class="alert alert-warning">';
        echo '<i class="fa fa-vimeo"></i> ';
        echo '<strong>Vimeo Inline-Consent:</strong> Bitte Video-ID oder URL eingeben.';
        echo '</div>';
    }
}