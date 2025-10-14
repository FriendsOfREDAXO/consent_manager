<?php

/**
 * Demo-Modul: Inline-Consent für YouTube Videos
 * 
 * Ausgabe-Teil des Moduls
 */

// Werte aus dem Eingabeformular
$videoId = 'REX_VALUE[1]';
$videoTitle = 'REX_VALUE[2]' ?: 'YouTube Video';
$videoWidth = (int) 'REX_VALUE[3]' ?: 560;
$videoHeight = (int) 'REX_VALUE[4]' ?: 315;

// Nur ausgeben wenn Video-ID vorhanden
if (!empty($videoId)) {
    
    // CSS/JS für Inline-Consent einbinden (falls noch nicht geschehen)
    if (class_exists('consent_manager_inline')) {
        echo consent_manager_inline::getCSS();
        echo consent_manager_inline::getJavaScript();
    }
    
    // Inline-Consent für YouTube generieren
    echo doConsent('youtube', $videoId, [
        'title' => $videoTitle,
        'width' => $videoWidth,
        'height' => $videoHeight,
        'thumbnail' => 'auto' // Automatisches YouTube-Thumbnail
    ]);
    
} else {
    // Backend-Preview falls keine Video-ID eingegeben
    if (rex::isBackend()) {
        echo '<div class="alert alert-warning">';
        echo '<i class="fa fa-youtube-play"></i> ';
        echo '<strong>YouTube Inline-Consent:</strong> Bitte Video-ID oder URL eingeben.';
        echo '</div>';
    }
}