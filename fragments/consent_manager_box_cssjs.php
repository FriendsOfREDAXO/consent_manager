<?php

if (0 === rex_article::getCurrentId()) {
    return;
}

$addon = rex_addon::get('consent_manager');
$forceCache = $this->getVar('forceCache');
$forceReload = $this->getVar('forceReload');

// Nur noch die Web Component laden
echo '    <script src="' . rex_url::addonAssets('consent_manager', 'js/js.cookie.min.js') . '" defer></script>' . PHP_EOL;
echo '    <script src="' . rex_url::addonAssets('consent_manager', 'js/consent_manager_component.js') . '" defer></script>' . PHP_EOL;

// Fragment für Box-Inhalt ausgeben
if (consent_manager_util::consentConfigured()) {
    echo consent_manager_frontend::getFragment(0, 0, 'consent_manager_box.php');
}
