<?php
$addon = rex_addon::get('consent_manager');
$debug = $this->getVar('debug');
$forceCache = $this->getVar('forceCache');

$_SESSION['consent_manager']['article'] = rex_article::getCurrentId();
$_SESSION['consent_manager']['debug'] = $debug;
$_SESSION['consent_manager']['outputcssjs'] = '';
$_SESSION['consent_manager']['clang'] = rex_clang::getCurrentId();

$initially_hidden = 'false';
$consent_manager = new consent_manager_frontend($forceCache);
$consent_manager->setDomain($_SERVER['HTTP_HOST']);
if (isset($consent_manager->links['privacy_policy']) && isset($consent_manager->links['legal_notice'])) {
    if (rex_article::getCurrentId() == $consent_manager->links['privacy_policy'] || rex_article::getCurrentId() == $consent_manager->links['legal_notice']) {
        $initially_hidden = 'true';
    }
}

$output = '';

if ('|1|' != $addon->getConfig('outputowncss', false)) {
    $_cssfilename = 'consent_manager_frontend.css';
    $output .= '    <link rel="stylesheet" href="' . $addon->getAssetsUrl($_cssfilename) . '?v=' . filemtime($addon->getAssetsPath($_cssfilename)) . '">' . PHP_EOL;
}

$output .= '    <script>consent_manager_parameters = { initially_hidden: ' . $initially_hidden . ', domain: "' . $_SERVER['HTTP_HOST'] . '", consentid: "' . uniqid('', true) . '", cacheLogId: "' . $consent_manager->cacheLogId . '", version: "' . $consent_manager->version . '", fe_controller: "' . rex_url::frontendController(). '" };</script>' . PHP_EOL;
$_params = [];
$_params['consent_manager_outputjs'] = true;
$_params['clang'] = rex_clang::getCurrentId();
$_params['v'] = filemtime($addon->getAssetsPath('consent_manager_frontend.js')) . rex_clang::getCurrentId();
$output .= '    <script src="' . rex_url::frontendController($_params) . '" id="consent_manager_script"></script>';

$_SESSION['consent_manager']['outputcssjs'] = $output;
?>
<!--REX_CONSENT_MANAGER_OUTPUT[]-->