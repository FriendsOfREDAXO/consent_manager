<?php

if (0 === rex_article::getCurrentId()) {
    return;
}
if (true === rex_get('consent_manager_outputjs', 'bool', false)) {
    return;
}

$addon       = rex_addon::get('consent_manager');
$forceCache  = $this->getVar('forceCache');
$forceReload = $this->getVar('forceReload');
$inlineMode  = $this->getVar('inline', false);

$consentparams                     = [];
$consentparams['article']          = rex_article::getCurrentId();
$consentparams['outputcss']        = '';
$consentparams['outputjs']         = '';
$consentparams['lang']             = rex_clang::getCurrentId();
$consentparams['initially_hidden'] = 'false';

$consent_manager = new consent_manager_frontend($forceCache);
if (is_string(rex_request::server('HTTP_HOST'))) {
    $consent_manager->setDomain(rex_request::server('HTTP_HOST'));
}

// Google Consent Mode v2 Integration - muss vor dem Consent Manager geladen werden
$googleConsentModeOutput = '';

if (! empty($consent_manager->domainInfo) &&
    isset($consent_manager->domainInfo['google_consent_mode_enabled']) &&
    $consent_manager->domainInfo['google_consent_mode_enabled'] !== 'disabled') {

    // Google Consent Mode v2 externe Datei laden (minifiziert oder normal)
    $googleConsentModeScriptFile = 'google_consent_mode_v2.min.js';
    if (!file_exists($addon->getAssetsPath('google_consent_mode_v2.min.js'))) {
        $googleConsentModeScriptFile = 'google_consent_mode_v2.js';
    }
    $googleConsentModeScriptUrl = $addon->getAssetsUrl($googleConsentModeScriptFile);
    $googleConsentModeOutput .= '    <script src="' . $googleConsentModeScriptUrl . '" defer></script>' . PHP_EOL;
    
    // Debug-Script laden wenn Debug-Modus aktiviert
    if (!empty($consent_manager->domainInfo['google_consent_mode_debug']) && 
        $consent_manager->domainInfo['google_consent_mode_debug'] == 1) {
        $debugScriptUrl = $addon->getAssetsUrl('consent_debug.js');
        $googleConsentModeOutput .= '    <script src="' . $debugScriptUrl . '" defer></script>' . PHP_EOL;
        
        // Debug-Konfiguration für JavaScript verfügbar machen
        $googleConsentModeOutput .= '    <script>' . PHP_EOL;
        $googleConsentModeOutput .= '        window.consentManagerDebugConfig = ' . json_encode([
            'mode' => $consent_manager->domainInfo['google_consent_mode_enabled'] ?? 'disabled',
            'auto_mapping' => ($consent_manager->domainInfo['google_consent_mode_enabled'] ?? 'disabled') === 'auto',
            'debug_enabled' => true,
            'domain' => rex_request::server('HTTP_HOST'),
            'cache_log_id' => $consent_manager->cacheLogId,
            'version' => $consent_manager->version
        ]) . ';' . PHP_EOL;
        $googleConsentModeOutput .= '    </script>' . PHP_EOL;
    }
    
    // Auto-Mapping wird jetzt im Frontend-JS gehandhabt
}

// Consent bei Datenschutz und Impressum ausblenden
if (isset($consent_manager->links['privacy_policy']) && isset($consent_manager->links['legal_notice'])) {
    if (rex_article::getCurrentId() === (int) $consent_manager->links['privacy_policy'] || rex_article::getCurrentId() === (int) $consent_manager->links['legal_notice']) {
        $consentparams['initially_hidden'] = 'true';
    }
}

// Prüfe explizite inline-Parameter
$inlineParam = $this->getVar('inline');
$explicitInlineParam = ($inlineParam === true || $inlineParam === false);

// Consent ausblenden wenn inline-Modus aktiviert ist
if ($inlineParam === true) {
    $consentparams['initially_hidden'] = 'true';
}

// Andere Bedingungen nur prüfen wenn KEIN expliziter inline-Parameter gesetzt wurde
if (!$explicitInlineParam) {
    // Consent ausblenden bei Domain-spezifischem Inline-Only Modus
    if (isset($consent_manager->domainInfo['inline_only_mode']) && $consent_manager->domainInfo['inline_only_mode'] == '1') {
        $consentparams['initially_hidden'] = 'true';
    }

    // Consent standardmäßig ausblenden (nur Inline-Consent verwenden) - globale Einstellung
    if (rex_config::get('consent_manager', 'inline_only_mode', false)) {
        $consentparams['initially_hidden'] = 'true';
    }
}

// Consent ausblenden wenn keine Dienste konfiguriert sind
if (0 === count($consent_manager->cookiegroups)) {
    //rex_logger::factory()->log('warning', 'Addon consent_manager: Keine Cookie-Gruppen / Cookies ausgewählt bzw. keine Domain zugewiesen! (' . consent_manager_util::hostname() . ')');
    $consentparams['initially_hidden'] = 'true';
}

// Consent bei Parameter skip_consent ausblenden
if ('' !== rex_config::get('consent_manager', 'skip_consent') && rex_get('skip_consent') === rex_config::get('consent_manager', 'skip_consent')) {
    $consentparams['initially_hidden'] = 'true';
}

// Consent bei inline=true Parameter ausblenden (nur Inline-Consent)
if ($inlineMode === true || $inlineMode === 'true' || $inlineMode === '1') {
    $consentparams['initially_hidden'] = 'true';
}

// Standard-CSS ausgeben
if (false === $addon->getConfig('outputowncss', false)) {
    $_csscontent = consent_manager_frontend::getFrontendCss();
    if ('' !== $_csscontent) {
        $consentparams['outputcss'] .= '    <style>' . trim($_csscontent) . '</style>' . PHP_EOL;
    }
}

$consentparams['hidescrollbar'] = ('|1|' === $addon->getConfig('hidebodyscrollbar', false)) ? 'true' : 'false';

$_params                             = [];
$_params['consent_manager_outputjs'] = true;
$_params['lang']                     = $consentparams['lang'];
$_params['a']                        = $consentparams['article'];
$_params['i']                        = $consentparams['initially_hidden'];
$_params['h']                        = $consentparams['hidescrollbar'];
$_params['cid']                      = $consent_manager->cacheLogId;
$_params['v']                        = $consent_manager->version;
$_params['r']                        = $forceReload;
$_params['t']                        = filemtime($addon->getAssetsPath('consent_manager_frontend.js')) . rex_clang::getCurrentId();

$consentparams['outputjs'] .= '    <script src="' . rex_url::frontendController($_params) . '" id="consent_manager_script" defer></script>' . PHP_EOL;

// Ausgabe Google Consent Mode v2 (vor allem anderen)
echo $googleConsentModeOutput;

// Ausgabe CSS + JavaScript
echo $consentparams['outputcss'];
echo $consentparams['outputjs'];
