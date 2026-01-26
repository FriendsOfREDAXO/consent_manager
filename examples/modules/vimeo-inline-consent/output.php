<?php

/**
 * Demo-Modul: Inline-Consent für Vimeo Videos.
 *
 * Ausgabe-Teil des Moduls
 */

use FriendsOfRedaxo\ConsentManager\InlineConsent;

// Werte aus dem Eingabeformular
$videoId = trim('REX_VALUE[1]');
$videoTitle = trim('REX_VALUE[2]');
$videoTitle = '' !== $videoTitle ? $videoTitle : 'Vimeo Video';
$videoWidth = trim('REX_VALUE[3]');
$videoWidth = '' !== $videoWidth ? (int) $videoWidth : 640;
$videoHeight = trim('REX_VALUE[4]');
$videoHeight = '' !== $videoHeight ? (int) $videoHeight : 360;
$customThumbnail = trim('REX_VALUE[5]');

// Nur ausgeben wenn Video-ID vorhanden
if ('' < $videoId) {
    // CSS/JS für Inline-Consent einbinden (falls noch nicht geschehen)
    if (class_exists(InlineConsent::class)) {
        echo InlineConsent::getCSS();
        echo InlineConsent::getJavaScript();
    }

    // Optionen für Vimeo
    $options = [
        'title' => $videoTitle,
        'width' => $videoWidth,
        'height' => $videoHeight,
        'privacy_notice' => 'Für Vimeo werden Cookies für erweiterte Player-Funktionen benötigt.',
    ];

    // Custom Thumbnail falls angegeben
    if ('' < $customThumbnail) {
        $options['thumbnail'] = $customThumbnail;
    }

    // Inline-Consent für Vimeo generieren
    echo InlineConsent::doConsent('vimeo', $videoId, $options);
} else {
    // Backend-Preview falls keine Video-ID eingegeben
    if (rex::isBackend()) {
        echo '<div class="alert alert-warning">';
        echo '<i class="fa fa-vimeo"></i> ';
        echo '<strong>Vimeo Inline-Consent:</strong> Bitte Video-ID oder URL eingeben.';
        echo '</div>';
    }
}
