<?php

/**
 * Consent Manager Build Script
 * Minifies JavaScript files for production use
 */

echo "Consent Manager Build Script\n";
echo "===========================\n\n";

// Files to minify
$files = [
    'consent_manager_frontend.js' => 'consent_manager_frontend.min.js',
    'google_consent_mode_v2.js' => 'google_consent_mode_v2.min.js',
];

$addonPath = __DIR__ . '/assets/';

foreach ($files as $source => $target) {
    $sourcePath = $addonPath . $source;
    $targetPath = $addonPath . $target;

    if (!file_exists($sourcePath)) {
        echo "❌ Source file not found: $source\n";
        continue;
    }

    echo "📄 Processing $source...\n";

    // Read source file
    $content = file_get_contents($sourcePath);

    // Basic minification (remove comments, extra whitespace)
    $minified = $content;

    // Remove single-line comments (but keep important ones)
    $minified = preg_replace('/\/\/.*$/m', '', $minified);

    // Remove multi-line comments
    $minified = preg_replace('/\/\*.*?\*\//s', '', $minified);

    // Remove extra whitespace and newlines
    $minified = preg_replace('/\s+/', ' ', $minified);
    $minified = preg_replace('/\s*([{}();,])\s*/', '$1', $minified);

    // Remove trailing/leading whitespace
    $minified = trim($minified);

    // Write minified file
    if (file_put_contents($targetPath, $minified)) {
        $originalSize = filesize($sourcePath);
        $minifiedSize = filesize($targetPath);
        $reduction = round((1 - $minifiedSize / $originalSize) * 100, 1);

        echo "✅ Created $target ({$minifiedSize} bytes, {$reduction}% reduction)\n";
    } else {
        echo "❌ Failed to create $target\n";
    }

    echo "\n";
}

echo "Build completed!\n";
echo "Note: This is basic minification. For better compression, use a proper minifier like Terser.\n";
?>