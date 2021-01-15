<?php
$addon = rex_addon::get('consent_manager');
$debug = $this->getVar('debug');
$forceCache = $this->getVar('forceCache');

$_SESSION['consent_manager']['article'] = rex_article::getCurrentId();
$_SESSION['consent_manager']['debug'] = $debug;
$_SESSION['consent_manager']['forceCache'] = $forceCache;
$_SESSION['consent_manager']['outputcssjs'] = '';

$initially_hidden = 'false';
$consent_manager = new consent_manager_frontend(0);
$consent_manager->setDomain($_SERVER['HTTP_HOST']);
if (rex_article::getCurrentId() == $consent_manager->links['privacy_policy'] || rex_article::getCurrentId() == $consent_manager->links['legal_notice']) {
	$initially_hidden = 'true';
}
		
$output = '';

if ('|1|' <> $addon->getConfig('outputowncss', false)) {
    $_cssfilename = 'assets/addons/consent_manager/consent_manager_frontend.css';
    $output .= '    <link rel="stylesheet" href="' . rex_url::base($_cssfilename) . '?v=' . filemtime(rex_path::base($_cssfilename)) . '">' . PHP_EOL;
}

$output .= '    <script>var consent_manager_initially_hidden = ' . $initially_hidden . ';</script>' . PHP_EOL;
$_params = [];
$_params['consent_manager_outputjs'] = true;
$_params['v'] = filemtime(rex_path::base('assets/addons/consent_manager/consent_manager_frontend.js'));
$output .= '    <script src="' . rex_url::frontendController($_params) . '"></script>';

$_SESSION['consent_manager']['outputcssjs'] = $output;
?>
<!--REX_CONSENT_MANAGER_OUTPUT[]-->