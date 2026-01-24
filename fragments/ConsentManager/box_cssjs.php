<?php

/**
 * TODO: hier die Schnittstelle beschreiben:
 * - Welche Vars werden vom Fragment erwartet
 * - Welchen Typ haben die Vars
 * - Welchen Default-Wert haben optionale Vars
 * - Welche Vars sind mandatory und was passiert wenn sie fehlen (return oder Exception)
 */

use FriendsOfRedaxo\ConsentManager\Frontend;

/** @var rex_fragment $this */

if (0 === rex_article::getCurrentId()) {
    return;
}
if (true === rex_request::get('consent_manager_outputjs', 'bool', false)) {
    return;
}

$addon = rex_addon::get('consent_manager');
$forceCache = $this->getVar('forceCache');
$forceReload = $this->getVar('forceReload');
// Default null statt false: nur true/'true'/'1' wird später geprüft, false würde zu Verwirrung führen
$inlineMode = $this->getVar('inline', null);

$consentparams = [];
$consentparams['article'] = rex_article::getCurrentId();
$consentparams['outputcss'] = '';
$consentparams['outputjs'] = '';
$consentparams['lang'] = rex_clang::getCurrentId();
$consentparams['initially_hidden'] = 'false';

// Wenn consent_manager bereits per Fragment-Var übergeben wurde, verwenden
$consent_manager = $this->getVar('consent_manager');
if (!$consent_manager instanceof Frontend) {
    $consent_manager = new Frontend((int) $forceCache);
    // Domain nur setzen, wenn Frontend-Objekt neu erstellt wurde
    if (is_string(rex_request::server('HTTP_HOST'))) {
        $consent_manager->setDomain(rex_request::server('HTTP_HOST'));
    }
}

// Google Consent Mode v2 Integration - muss vor dem Consent Manager geladen werden
$googleConsentModeOutput = '';

if (0 < count($consent_manager->domainInfo)
    && isset($consent_manager->domainInfo['google_consent_mode_enabled'])
    && 'disabled' !== $consent_manager->domainInfo['google_consent_mode_enabled']) {
    // Google Consent Mode v2 externe Datei laden (minifiziert oder normal)
    $googleConsentModeScriptFile = 'google_consent_mode_v2.min.js';
    if (!file_exists($addon->getAssetsPath('google_consent_mode_v2.min.js'))) {
        $googleConsentModeScriptFile = 'google_consent_mode_v2.js';
    }
    $googleConsentModeScriptUrl = $addon->getAssetsUrl($googleConsentModeScriptFile);
    $googleConsentModeOutput .= '    <script src="' . $googleConsentModeScriptUrl . '" defer></script>' . PHP_EOL;

    // Debug-Script laden wenn Debug-Modus aktiviert UND User im Backend eingeloggt
    if (isset($consent_manager->domainInfo['google_consent_mode_debug'])
        && 1 === $consent_manager->domainInfo['google_consent_mode_debug']) {
        // User für Frontend initialisieren
        rex_backend_login::createUser();
        
        // Nur für eingeloggte Backend-Benutzer
        if (rex_backend_login::hasSession() && null !== rex::getUser()) {
            $debugScriptUrl = $addon->getAssetsUrl('consent_debug.js');
            $googleConsentModeOutput .= '    <script src="' . $debugScriptUrl . '" defer></script>' . PHP_EOL;

            // Debug-Konfiguration für JavaScript verfügbar machen
            $googleConsentModeOutput .= '    <script>' . PHP_EOL;
            $googleConsentModeOutput .= '        window.consentManagerDebugConfig = ' . json_encode([
                'mode' => $consent_manager->domainInfo['google_consent_mode_enabled'],
                'auto_mapping' => $consent_manager->domainInfo['google_consent_mode_enabled'] === 'auto',
                'debug_enabled' => true,
                'domain' => rex_request::server('HTTP_HOST'),
                'cache_log_id' => $consent_manager->cacheLogId,
                'version' => $consent_manager->version,
            ]) . ';' . PHP_EOL;
            $googleConsentModeOutput .= '    </script>' . PHP_EOL;
        }
    }

    // Auto-Mapping wird jetzt im Frontend-JS gehandhabt
}

// Consent bei Datenschutz und Impressum ausblenden
if (isset($consent_manager->links['privacy_policy']) && isset($consent_manager->links['legal_notice'])) {
    if (rex_article::getCurrentId() === (int) $consent_manager->links['privacy_policy'] || rex_article::getCurrentId() === (int) $consent_manager->links['legal_notice']) {
        $consentparams['initially_hidden'] = 'true';
    }
}

// Prüfe explizite inline-Parameter (default: null wenn nicht gesetzt)
$inlineParam = $this->getVar('inline', null);
$explicitInlineParam = (true === $inlineParam || false === $inlineParam);

// Consent ausblenden wenn inline-Modus aktiviert ist
if (true === $inlineParam) {
    $consentparams['initially_hidden'] = 'true';
}

// Andere Bedingungen nur prüfen wenn KEIN expliziter inline-Parameter gesetzt wurde
if (!$explicitInlineParam) {
    // Consent ausblenden bei Domain-spezifischem Inline-Only Modus
    if (isset($consent_manager->domainInfo['inline_only_mode']) && '1' === $consent_manager->domainInfo['inline_only_mode']) {
        $consentparams['initially_hidden'] = 'true';
    }

    // Consent standardmäßig ausblenden (nur Inline-Consent verwenden) - globale Einstellung
    if ((bool) rex_config::get('consent_manager', 'inline_only_mode', false)) {
        $consentparams['initially_hidden'] = 'true';
    }
}

// Consent ausblenden wenn keine Dienste konfiguriert sind
if (0 === count($consent_manager->cookiegroups)) {
    // rex_logger::factory()->log('warning', 'Addon consent_manager: Keine Cookie-Gruppen / Cookies ausgewählt bzw. keine Domain zugewiesen! (' . \FriendsOfRedaxo\ConsentManager\Utility::hostname() . ')');
    $consentparams['initially_hidden'] = 'true';
}

// Consent bei Parameter skip_consent ausblenden
if ('' !== rex_config::get('consent_manager', 'skip_consent') && rex_request::get('skip_consent') === rex_config::get('consent_manager', 'skip_consent')) {
    $consentparams['initially_hidden'] = 'true';
}

// Consent bei inline=true Parameter ausblenden (nur Inline-Consent)
if (true === $inlineMode || 'true' === $inlineMode || '1' === $inlineMode) {
    $consentparams['initially_hidden'] = 'true';
}

// Standard-CSS ausgeben
if (false === $addon->getConfig('outputowncss', false)) {
    $_csscontent = Frontend::getFrontendCss();
    if ('' !== $_csscontent) {
        $consentparams['outputcss'] .= '    <style>' . trim($_csscontent) . '</style>' . PHP_EOL;
    }
}

$consentparams['hidescrollbar'] = ('|1|' === $addon->getConfig('hidebodyscrollbar', false)) ? 'true' : 'false';

$_params = [];
$_params['consent_manager_outputjs'] = true;
$_params['lang'] = $consentparams['lang'];
$_params['a'] = $consentparams['article'];
$_params['i'] = $consentparams['initially_hidden'];
$_params['h'] = $consentparams['hidescrollbar'];
$_params['cid'] = $consent_manager->cacheLogId;
$_params['v'] = $consent_manager->version;
$_params['r'] = $forceReload;
$_params['t'] = filemtime($addon->getAssetsPath('consent_manager_frontend.js')) . rex_clang::getCurrentId();

// JavaScript-Konfiguration für Frontend
$jsConfig = [
    'cookieSameSite' => 'Lax',
    'cookieSecure' => rex_request::isHttps(),
    'cookieName' => 'consentmanager',
    'cookieLifetime' => 14, // Tage
    'domain' => rex_request::server('HTTP_HOST', 'string', ''),
    'version' => $consent_manager->version,
    'cacheLogId' => $consent_manager->cacheLogId,
    'focus' => isset($consent_manager->domainInfo['auto_inject_focus']) ? $consent_manager->domainInfo['auto_inject_focus'] : 1,
    'mode' => 'opt-in',
];

$consentparams['outputjs'] .= '    <script>var consent_manager_parameters = ' . json_encode($jsConfig, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ';</script>' . PHP_EOL;
$consentparams['outputjs'] .= '    <script src="' . rex_url::frontendController($_params) . '" id="consent_manager_script" defer></script>' . PHP_EOL;

// Ausgabe Google Consent Mode v2 (vor allem anderen)
echo $googleConsentModeOutput;

// Ausgabe CSS + JavaScript
echo $consentparams['outputcss'];
echo $consentparams['outputjs'];
