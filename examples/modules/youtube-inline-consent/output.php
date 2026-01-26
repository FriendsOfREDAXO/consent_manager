<?php

/**
 * Demo-Modul: Inline-Consent für YouTube Videos.
 *
 * Ausgabe-Teil des Moduls
 */

use FriendsOfRedaxo\ConsentManager\InlineConsent;

// Werte aus dem Eingabeformular
$videoId = trim('REX_VALUE[1]');
$videoTitle = trim('REX_VALUE[2]');
$videoTitle = '' !== $videoTitle ? $videoTitle : 'YouTube Video';
$videoWidth = trim('REX_VALUE[3]');
$videoWidth = '' !== $videoWidth ? (int) $videoWidth : 560;
$videoHeight = trim('REX_VALUE[4]');
$videoHeight = '' !== $videoHeight ? (int) $videoHeight : 315;

// Nur ausgeben wenn Video-ID vorhanden
if ('' < $videoId) {
    // CSS/JS für Inline-Consent einbinden (falls noch nicht geschehen)
    if (class_exists(InlineConsent::class)) {
        echo InlineConsent::getCSS();
        echo InlineConsent::getJavaScript();

        // Inline-Consent für YouTube generieren
        echo InlineConsent::doConsent('youtube', $videoId, [
            'title' => $videoTitle,
            'width' => $videoWidth,
            'height' => $videoHeight,
            'thumbnail' => 'auto',
        ]);
    } else {
        echo '<div class="alert alert-danger">InlineConsent-Klasse nicht gefunden!</div>';
    }
} else {
    // Backend-Preview falls keine Video-ID eingegeben
    if (rex::isBackend()) {
        echo '<div class="alert alert-warning">';
        echo '<i class="fa fa-youtube-play"></i> ';
        echo '<strong>YouTube Inline-Consent:</strong> Bitte Video-ID oder URL eingeben.';
        echo '</div>';
    }
}
