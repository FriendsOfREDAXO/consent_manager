<?php

if (!rex_article::getCurrentId()) {
    return;
}
if (true === rex_get('consent_manager_outputjs', 'bool', false)) {
    return;
}

$addon = rex_addon::get('consent_manager');
$forceCache = $this->getVar('forceCache');

$consentparams = [];
$consentparams['article'] = rex_article::getCurrentId();
$consentparams['outputcss'] = '';
$consentparams['outputjs'] = '';
$consentparams['clang'] = rex_clang::getCurrentId();
$consentparams['initially_hidden'] = 'false';

$consent_manager = new consent_manager_frontend($forceCache);
$consent_manager->setDomain($_SERVER['HTTP_HOST']);

// Consent bei Datenschutz und Impressum ausblenden
if (isset($consent_manager->links['privacy_policy']) && isset($consent_manager->links['legal_notice'])) {
    if (rex_article::getCurrentId() == $consent_manager->links['privacy_policy'] || rex_article::getCurrentId() == $consent_manager->links['legal_notice']) {
        $consentparams['initially_hidden'] = 'true';
    }
}

// Consent bei Parameter skip_consent ausblenden
if (rex_config::get('consent_manager', 'skip_consent') != "" && rex_get('skip_consent') == rex_config::get('consent_manager', 'skip_consent')) {
    $consentparams['initially_hidden'] = 'true';
}

// Standard-CSS ausgeben
if (!$addon->getConfig('outputowncss', false)) {
    $_cssfilename = 'consent_manager_frontend.css';
    $consentparams['outputcss'] .= '    <style>' . trim(file_get_contents($addon->getAssetsPath($_cssfilename))) . '</style>' . PHP_EOL;
}

$consentparams['hidescrollbar'] = ('|1|' == $addon->getConfig('hidebodyscrollbar', false)) ? 'true' : 'false';
$consentparams['cachelogid'] = $consent_manager->cacheLogId;
$consentparams['version'] = $consent_manager->version;

$consentparams['outputjs'] .= '    <script src="?consent_manager_outputjs=1&clang=' . $consentparams['clang'] . '&a=' . $consentparams['article'] . '&i=' . $consentparams['initially_hidden'] . '&h=' . $consentparams['hidescrollbar'] . '&cid=' . $consentparams['cachelogid'] . '&v=' . $consentparams['version'] . '&t=' . filemtime($addon->getAssetsPath('consent_manager_frontend.js')) . '" id="consent_manager_script" defer></script>' . PHP_EOL;

// Ausgabe CSS + JavaScript
echo $consentparams['outputcss'];
echo $consentparams['outputjs'];
