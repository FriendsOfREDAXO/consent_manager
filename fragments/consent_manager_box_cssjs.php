<?php
$addon = rex_addon::get('consent_manager');
$forceCache = $this->getVar('forceCache');

$_SESSION['consent_manager']['article'] = rex_article::getCurrentId();
$_SESSION['consent_manager']['outputcss'] = '';
$_SESSION['consent_manager']['outputjs'] = '';
$_SESSION['consent_manager']['clang'] = rex_clang::getCurrentId();

$initially_hidden = 'false';
$consent_manager = new consent_manager_frontend($forceCache);
$consent_manager->setDomain($_SERVER['HTTP_HOST']);
if (isset($consent_manager->links['privacy_policy']) && isset($consent_manager->links['legal_notice'])) {
    if (rex_article::getCurrentId() == $consent_manager->links['privacy_policy'] || rex_article::getCurrentId() == $consent_manager->links['legal_notice']) {
        $initially_hidden = 'true';
    }
}

if (rex_config::get('consent_manager', 'skip_consent') != "" && rex_get('skip_consent') == rex_config::get('consent_manager', 'skip_consent')) {
    $initially_hidden = 'true';
}

$outputcss = '';
$outputjs = '';

if (!$addon->getConfig('outputowncss', false)) {
    $_cssfilename = 'consent_manager_frontend.css';
    $outputcss .= '    <style>' . trim(file_get_contents($addon->getAssetsPath($_cssfilename))) . '</style>' . PHP_EOL;
}

$hidescrollbar = ('|1|' == $addon->getConfig('hidebodyscrollbar', false)) ? 'true' : 'false';

$_SESSION['consent_manager']['initially_hidden'] = $initially_hidden;
$_SESSION['consent_manager']['cachelogid'] = $consent_manager->cacheLogId;
$_SESSION['consent_manager']['version'] = $consent_manager->version;
$_SESSION['consent_manager']['hidescrollbar'] = $hidescrollbar;

$_params = [];
$_params['consent_manager_outputjs'] = true;
$_params['clang'] = rex_clang::getCurrentId();
$_params['v'] = filemtime($addon->getAssetsPath('consent_manager_frontend.js')) . rex_clang::getCurrentId();
$outputjs .= '    <script src="' . rex_url::frontendController($_params) . '" id="consent_manager_script" defer></script>';

$_SESSION['consent_manager']['cachelogid'] = $consent_manager->cacheLogId;
$_SESSION['consent_manager']['outputcss'] = $outputcss;
$_SESSION['consent_manager']['outputjs'] = $outputjs;
?>
<!--REX_CONSENT_MANAGER_OUTPUT[]-->
