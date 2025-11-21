<?php
// try to load the redaxo php-cs-fixer-config from vendor if available
$vendorConfig = __DIR__ . '/vendor/redaxo/php-cs-fixer-config/.php-cs-fixer.php';
$vendorConfigAlt = __DIR__ . '/vendor/redaxo/php-cs-fixer-config/php-cs-fixer.php';

if (file_exists($vendorConfig)) {
    return include $vendorConfig;
}

if (file_exists($vendorConfigAlt)) {
    return include $vendorConfigAlt;
}

// Fallback configuration
$config = new PhpCsFixer\Config();
$config->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
        'single_quote' => true,
    ])
    ->setFinder(PhpCsFixer\Finder::create()->in(__DIR__));

return $config;
