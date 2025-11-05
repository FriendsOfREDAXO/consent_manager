<?php

/**
 * Consent Manager Inline Consent Fragment.
 *
 * Fragment für CSS/JS Integration der Inline-Consent-Funktion
 *
 * @package redaxo\consent-manager
 */

use FriendsOfRedaxo\ConsentManager\Frontend;
use FriendsOfRedaxo\ConsentManager\InlineConsent;

// Nur laden wenn Domain konfiguriert ist
$consent_manager = new Frontend(0);

if (is_string(rex_request::server('HTTP_HOST'))) {
    $consent_manager->setDomain(rex_request::server('HTTP_HOST'));
}

// CSS und JavaScript nur ausgeben wenn Domain konfiguriert
if ('' < $consent_manager->domainName) {
    echo InlineConsent::getCSS();
    echo InlineConsent::getJavaScript();
}
