<?php

/**
 * Consent Manager Inline Consent Fragment
 * 
 * Fragment für CSS/JS Integration der Inline-Consent-Funktion
 * 
 * @package redaxo\consent-manager
 */

$addon = rex_addon::get('consent_manager');

// Nur laden wenn Domain konfiguriert ist
$consent_manager = new consent_manager_frontend(0);
if (is_string(rex_request::server('HTTP_HOST'))) {
    $consent_manager->setDomain(rex_request::server('HTTP_HOST'));
}

// CSS und JavaScript nur ausgeben wenn Domain konfiguriert
if (!empty($consent_manager->domainName)) {
    echo consent_manager_inline::getCSS();
    echo consent_manager_inline::getJavaScript();
}