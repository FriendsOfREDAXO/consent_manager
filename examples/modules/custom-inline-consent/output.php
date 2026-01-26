<?php

/**
 * Demo-Modul: Inline-Consent für Custom iframes/Scripts.
 *
 * Ausgabe-Teil des Moduls
 */

use FriendsOfRedaxo\ConsentManager\InlineConsent;

// Werte aus dem Eingabeformular
$serviceKey = trim('REX_VALUE[1]');
$embedCode = trim('REX_VALUE[2]');
$contentTitle = trim('REX_VALUE[3]');
$contentTitle = '' !== $contentTitle ? $contentTitle : 'External Content';
$buttonText = trim('REX_VALUE[4]');
$buttonText = '' !== $buttonText ? $buttonText : 'Content laden';
$privacyNotice = trim('REX_VALUE[5]');
$privacyNotice = '' !== $privacyNotice ? $privacyNotice : 'Für diesen Inhalt werden Cookies benötigt.';

// Nur ausgeben wenn Service-Key und Code vorhanden
if ('' < $serviceKey && '' < $embedCode) {
    // CSS/JS für Inline-Consent einbinden (falls noch nicht geschehen)
    if (class_exists(InlineConsent::class)) {
        echo InlineConsent::getCSS();
        echo InlineConsent::getJavaScript();
    }

    // Inline-Consent für Custom Content generieren
    echo InlineConsent::doConsent($serviceKey, $embedCode, [
        'title' => $contentTitle,
        'placeholder_text' => $buttonText,
        'privacy_notice' => $privacyNotice,
    ]);
} else {
    // Backend-Preview falls Daten fehlen
    if (rex::isBackend()) {
        echo '<div class="alert alert-warning">';
        echo '<i class="fa fa-code"></i> ';
        echo '<strong>Custom Inline-Consent:</strong> ';

        if ('' === $serviceKey) {
            echo 'Bitte Service-Schlüssel eingeben.';
        } elseif ('' === $embedCode) {
            echo 'Bitte Embed-Code eingeben.';
        }

        echo '</div>';
    }
}
