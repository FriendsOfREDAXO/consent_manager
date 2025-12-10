<?php

/**
 * A11y Theme Editor
 * Customize colors for Accessibility themes.
 */

use FriendsOfRedaxo\ConsentManager\Theme;

// Check if project addon is available and installed
if (!rex_addon::get('project')->isAvailable()) {
    echo rex_view::error(rex_i18n::msg('consent_manager_theme_editor_project_addon_required'));
    return;
}

$csrfToken = rex_csrf_token::factory('consent_manager_theme_editor');

// Theme bases
$themeBase = rex_request::request('theme_base', 'string', 'normal');
$themeBases = [
    'normal' => 'Accessibility (Normal)',
    'compact' => 'Accessibility (Compact)',
];

// Default colors - erweitert mit mehr Optionen
$defaultColors = [
    'normal' => [
        'accent' => '#333333',
        'button_bg' => '#333333',
        'button_text' => '#ffffff',
        'button_hover' => '#000000',
        'button_hover_text' => '#ffffff',
        'focus' => '#0066cc',
        'link' => '#0066cc',
        'background' => '#ffffff',
        'text' => '#1a1a1a',
        'overlay' => '#000000',
        'overlay_opacity' => '75',
        'border_radius' => '4',
        'border_width' => '3',
    ],
    'compact' => [
        'accent' => '#333333',
        'button_bg' => '#333333',
        'button_text' => '#ffffff',
        'button_hover' => '#000000',
        'button_hover_text' => '#ffffff',
        'focus' => '#0066cc',
        'link' => '#0066cc',
        'background' => '#ffffff',
        'text' => '#1a1a1a',
        'overlay' => '#000000',
        'overlay_opacity' => '75',
        'border_radius' => '4',
        'border_width' => '2',
    ],
];

// Get current colors from form or defaults
$colors = [];
if ('1' === rex_request::post('formsubmit', 'string')) {
    $colors = [
        'accent' => rex_request::post('accent_color', 'string', $defaultColors[$themeBase]['accent']),
        'button_bg' => rex_request::post('button_bg', 'string', $defaultColors[$themeBase]['button_bg']),
        'button_text' => rex_request::post('button_text', 'string', $defaultColors[$themeBase]['button_text']),
        'button_hover' => rex_request::post('button_hover', 'string', $defaultColors[$themeBase]['button_hover']),
        'button_hover_text' => rex_request::post('button_hover_text', 'string', $defaultColors[$themeBase]['button_hover_text']),
        'focus' => rex_request::post('focus_color', 'string', $defaultColors[$themeBase]['focus']),
        'link' => rex_request::post('link_color', 'string', $defaultColors[$themeBase]['link']),
        'background' => rex_request::post('background_color', 'string', $defaultColors[$themeBase]['background']),
        'text' => rex_request::post('text_color', 'string', $defaultColors[$themeBase]['text']),
        'overlay' => rex_request::post('overlay_color', 'string', $defaultColors[$themeBase]['overlay']),
        'overlay_opacity' => rex_request::post('overlay_opacity', 'string', $defaultColors[$themeBase]['overlay_opacity']),
        'border_radius' => rex_request::post('border_radius', 'string', $defaultColors[$themeBase]['border_radius']),
        'border_width' => rex_request::post('border_width', 'string', $defaultColors[$themeBase]['border_width']),
    ];
} else {
    $colors = $defaultColors[$themeBase];
}

// Save theme
if ('1' === rex_request::post('formsubmit', 'string') && !$csrfToken->isValid()) {
    echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
} elseif ('1' === rex_request::post('formsubmit', 'string')) {
    $themeName = rex_request::post('theme_name', 'string', 'Custom A11y Theme');
    $themeDescription = rex_request::post('theme_description', 'string', 'Individuell angepasstes Barrierefreiheits-Theme');

    // Generate SCSS content
    $scssContent = generateA11yThemeScss($themeBase, $themeName, $themeDescription, $colors);

    // Save to project addon
    // Project addon already checked at start of file
    $projectAddon = rex_addon::get('project');
    $themesDir = $projectAddon->getPath('consent_manager_themes/');

    if (!is_dir($themesDir)) {
        rex_dir::create($themesDir);
    }

    $filename = 'consent_manager_frontend_' . rex_string::normalize($themeName) . '.scss';
    $filepath = $themesDir . $filename;

    if (rex_file::put($filepath, $scssContent)) {
        // Compile theme
        try {
            Theme::generateThemeAssets('project:' . $filename);
            Theme::copyAllAssets();

            echo rex_view::success(rex_i18n::msg('consent_manager_theme_editor_saved', $themeName));
            echo rex_view::info('Theme gespeichert als: <code>' . $filename . '</code><br>Du kannst es jetzt unter "Theme" auswählen.');
        } catch (Exception $e) {
            echo rex_view::error('Fehler beim Kompilieren: ' . $e->getMessage());
        }
    } else {
        echo rex_view::error('Fehler beim Speichern der Datei.');
    }
}

// TODO: Funktion verlagern in eine eigene Datei? -> Thomas fragen
/**
 * @param string $base
 * @param string $name
 * @param string $description
 * @param array<string, string> $colors
 * @return string
 */
function generateA11yThemeScss(string $base, string $name, string $description, array $colors): string
{
    $isCompact = ('compact' === $base);

    $fontSize = $isCompact ? '15px' : '16px';
    $lineHeight = $isCompact ? '1.5em' : '1.6em';
    $padding = $isCompact ? '1.5em' : '2.5em';
    $paddingOuter = $isCompact ? '0.75em' : '1em';
    $borderWidth = ($colors['border_width'] ?? ($isCompact ? '2' : '3')) . 'px';
    $borderRadius = ($colors['border_radius'] ?? '4') . 'px';
    $maxWidth = $isCompact ? '55em' : '65em';
    $buttonPadding = $isCompact ? '10px 20px' : '12px 24px';
    $buttonMinHeight = $isCompact ? '44px' : '48px';
    $buttonMinWidth = $isCompact ? '140px' : '150px';
    
    // Overlay-Opacity
    $overlayOpacity = ((int) ($colors['overlay_opacity'] ?? 75)) / 100;

    $hexToRgba = static function ($hex, $alpha = 0.2) {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "rgba($r, $g, $b, $alpha)";
    };
    
    // Overlay-Farbe mit Opacity
    $overlayColor = $colors['overlay'] ?? '#000000';
    $overlayRgba = $hexToRgba($overlayColor, $overlayOpacity);

    $accentRgba = $hexToRgba($colors['accent'], 0.3);
    $focusRgba = $hexToRgba($colors['focus'], 0.2);
    $linkHoverBg = $hexToRgba($colors['link'], 0.1);
    $cookieTitleBg = $hexToRgba($colors['accent'], 0.05);
    
    // Hintergrund und Text
    $background = $colors['background'] ?? '#ffffff';
    $textColor = $colors['text'] ?? '#1a1a1a';
    
    // Button-Textfarben
    $buttonText = $colors['button_text'] ?? '#ffffff';
    $buttonHoverText = $colors['button_hover_text'] ?? '#ffffff';

    return <<<SCSS
        /*
        Theme: {"name": "$name", "description": "$description", "type": "light", "style": "Popup zentriert, Accessibility-optimiert, Custom Colors", "autor": "@custom"}
        */

        \$font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        \$font-size: $fontSize;
        \$line-height: $lineHeight;

        \$overlay-background: $overlayRgba;
        \$consent_manager-background: $background;
        \$consent_manager-border: $textColor;
        \$text-color: $textColor;

        \$accent-color: {$colors['accent']};
        \$accent-hover: {$colors['button_hover']};
        \$button-bg: {$colors['button_bg']};
        \$button-text: $buttonText;
        \$button-hover: {$colors['button_hover']};
        \$button-hover-text: $buttonHoverText;

        \$focus-color: {$colors['focus']};
        \$focus-shadow: $focusRgba;

        \$link-color: {$colors['link']};
        \$link-hover-color: {$colors['button_hover']};
        \$link-hover-bg: $linkHoverBg;

        \$cookie-title-bg: $cookieTitleBg;
        \$cookie-desc-bg: #f9f9f9;
        \$cookie-border: {$colors['accent']};
        \$cookie-accent: {$colors['accent']};

        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        div.consent_manager-background {
            position: fixed;
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
            background: transparent;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: $paddingOuter;
            z-index: 999999;
            height: 100%;
            width: 100%;
            overflow: hidden;
            box-sizing: border-box;
            animation: fadeIn 0.2s;
            outline: 0;
            pointer-events: none;
        }

        div.consent_manager-wrapper {
            pointer-events: auto;
            font-family: \$font-family;
            font-size: \$font-size;
            line-height: \$line-height;
            background: \$consent_manager-background;
            border: $borderWidth solid \$accent-color;
            border-radius: $borderRadius;
            color: \$text-color;
            position: relative;
            width: 100%;
            max-width: $maxWidth;
            max-height: 95vh;
            overflow-y: auto;
            box-sizing: border-box;
            animation: fadeIn 0.4s;
            box-shadow: 0 10px 40px $accentRgba;
        }

        div.consent_manager-wrapper-inner {
            padding: $padding;
            position: relative;
        }

        div.consent_manager-hidden {
            display: none;
        }

        div.consent_manager-script {
            display: none;
        }

        .consent_manager-close-box {
            position: absolute;
            cursor: pointer;
            right: 1.5em;
            top: 1em;
            display: block;
            border-radius: 4px !important;
            border: 2px solid \$consent_manager-border;
            width: 44px;
            height: 44px;
            line-height: 40px;
            background-color: \$consent_manager-background;
            color: \$text-color;
            font-family: Arial, sans-serif;
            font-size: 22px;
            font-weight: bold;
            padding: 0;
            margin: 0;
            transition: 0.2s ease all;
            text-align: center;

            &:hover,
            &:focus {
                background-color: \$text-color;
                color: \$consent_manager-background;
                outline: 3px solid \$focus-color;
                outline-offset: 2px;
                transform: scale(1.05);
            }
        }

        div.consent_manager-background button:focus,
        div.consent_manager-background a:focus,
        div.consent_manager-background input:focus,
        div.consent_manager-background [tabindex]:focus {
            outline: 3px solid \$focus-color !important;
            outline-offset: 2px !important;
            box-shadow: 0 0 0 3px \$focus-shadow !important;
        }

        @media (prefers-contrast: high) {
            div.consent_manager-background button:focus,
            div.consent_manager-background a:focus,
            div.consent_manager-background input:focus,
            div.consent_manager-background [tabindex]:focus {
                outline: 4px solid currentColor !important;
                outline-offset: 3px !important;
            }
        }

        div.consent_manager-wrapper .consent_manager-headline {
            margin: 0 0 0.75em 0;
            font-weight: bold;
            font-size: 18px;
            color: \$text-color;
            line-height: 1.3;
        }

        div.consent_manager-wrapper p.consent_manager-text {
            margin: 0 0 1em 0;
            color: \$text-color;
        }

        div.consent_manager-cookiegroups {
            margin: 0 0 1em 0;
        }

        div.consent_manager-cookiegroup-checkbox {
            margin-bottom: 1em;
            min-height: 44px;
            display: flex;
            align-items: center;
        }

        div.consent_manager-cookiegroups label {
            position: relative;
            font-weight: 600;
            font-size: 15px;
            color: \$text-color;
            cursor: pointer;
            display: flex;
            align-items: center;
            min-height: 44px;
            padding: 6px;
            border-radius: 4px;
            transition: background-color 0.2s ease;

            &:hover {
                background-color: \$link-hover-bg;
            }

            &:focus-within {
                background-color: \$link-hover-bg;
                outline: 3px solid \$focus-color;
                outline-offset: 2px;
            }

            > span {
                cursor: pointer;
            }
        }

        div.consent_manager-cookiegroups label > input[type="checkbox"] {
            width: 22px;
            height: 22px;
            margin: 0 10px 0 4px;
            cursor: pointer;
            border: 2px solid \$text-color;
            flex-shrink: 0;
        }

        .consent_manager-wrapper input[type="checkbox"]:disabled,
        .consent_manager-cookiegroups label > input[type="checkbox"]:disabled + * {
            opacity: 0.6;
            cursor: not-allowed;
        }

        div.consent_manager-show-details {
            padding: 0 0 1em 0;

            button {
                display: inline-flex;
                align-items: center;
                line-height: 1.4;
                min-height: 44px;
                padding: 8px 14px;
                cursor: pointer;
                color: \$link-color;
                background-color: transparent;
                border: 2px solid \$link-color;
                border-radius: 4px;
                font-size: 15px;
                font-weight: 600;
                transition: 0.2s ease all;

                &:hover,
                &:focus {
                    background-color: \$link-color;
                    color: #ffffff;
                    transform: translateY(-2px);
                }

                &[aria-expanded="true"]::after {
                    content: " ▼";
                    margin-left: 6px;
                }

                &[aria-expanded="false"]::after {
                    content: " ▶";
                    margin-left: 6px;
                }
            }
        }

        /* DSGVO: Alle Buttons gleichwertig */
        button.consent_manager-save-selection,
        button.consent_manager-accept-all,
        button.consent_manager-accept-none {
            transition: 0.2s ease all;
            background: \$button-bg;
            border: $borderWidth solid \$button-bg;
            color: \$button-text;
            padding: $buttonPadding;
            border-radius: $borderRadius;
            font-size: 15px;
            font-weight: 600;
            text-align: center;
            display: block;
            min-height: $buttonMinHeight;
            width: 100%;
            margin-bottom: 0.75em;
            cursor: pointer;
            line-height: 1.4;
        }

        button.consent_manager-save-selection:hover,
        button.consent_manager-save-selection:focus,
        button.consent_manager-accept-all:hover,
        button.consent_manager-accept-all:focus,
        button.consent_manager-accept-none:hover,
        button.consent_manager-accept-none:focus {
            background: \$button-hover;
            border-color: \$button-hover;
            color: \$button-hover-text;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px $accentRgba;
        }

        div.consent_manager-sitelinks {
            margin: 1em 0 0 0;

            a {
                display: inline-block;
                margin: 0.4em 1em 0.4em 0;
                color: \$link-color;
                text-decoration: underline;
                text-underline-offset: 2px;
                cursor: pointer;
                font-size: 14px;
                font-weight: 500;
                padding: 3px 6px;
                border-radius: 4px;
                transition: 0.2s ease all;

                &:hover,
                &:focus {
                    color: \$link-hover-color;
                    background-color: \$link-hover-bg;
                    text-decoration: none;
                }
            }
        }

        div.consent_manager-wrapper div.consent_manager-detail {
            margin-bottom: 2em;

            a {
                color: \$link-color;
                text-decoration: underline;
                text-underline-offset: 2px;

                &:hover,
                &:focus {
                    color: \$link-hover-color;
                    background-color: \$link-hover-bg;
                    text-decoration: none;
                }
            }

            div.consent_manager-cookiegroup-title {
                color: \$text-color;
                background-color: \$cookie-title-bg;
                padding: 10px 14px;
                margin: 1em 0 0 0;
                font-weight: bold;
                font-size: 15px;
                border-left: 3px solid \$cookie-accent;
            }

            div.consent_manager-cookiegroup-description {
                border-left: 3px solid \$cookie-border;
                padding: 10px 14px;
                background: \$cookie-desc-bg;
                color: \$text-color;
                font-size: 14px;
            }

            div.consent_manager-cookie {
                margin-top: 2px;
                border-left: 3px solid \$cookie-border;
                padding: 10px 14px;
                background: \$cookie-desc-bg;
                color: \$text-color;
                font-size: 14px;

                span {
                    display: block;
                    margin-top: 0.5em;
                    line-height: 1.5;
                }
            }
        }

        @media only screen and (min-width: 600px) {
            div.consent_manager-cookiegroups {
                padding: 0.75em 0 0 0;
                display: flex;
                flex-wrap: wrap;
                justify-content: flex-end;
                margin-bottom: 0;
            }

            div.consent_manager-cookiegroup-checkbox {
                margin-left: 1em;
                margin-bottom: 0.75em;
            }

            div.consent_manager-show-details {
                text-align: right;
                padding: 1em 0 1em 0;
            }

            div.consent_manager-buttons {
                display: flex;
                justify-content: flex-end;
                align-items: center;
                gap: 10px;
            }

            button.consent_manager-save-selection,
            button.consent_manager-accept-all,
            button.consent_manager-accept-none {
                display: inline-block;
                margin: 0;
                width: auto;
                min-width: $buttonMinWidth;
            }

            div.consent_manager-sitelinks {
                margin: 0;
            }

            div.consent_manager-buttons-sitelinks {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-direction: row-reverse;
                gap: 20px;
            }
        }

        @media print {
            div.consent_manager-background {
                display: none !important;
            }
        }

        @media (prefers-contrast: high) {
            div.consent_manager-wrapper {
                border-width: 3px;
            }
            
            button,
            a {
                text-decoration: underline;
            }
            
            .consent_manager-close-box,
            button.consent_manager-save-selection,
            button.consent_manager-accept-all,
            button.consent_manager-accept-none {
                border-width: 3px;
            }
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border-width: 0;
        }
        SCSS;
}

// Form output
?>

<style>
.theme-editor-form {
    max-width: 900px;
}
.color-input-group {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}
.color-input-group label {
    min-width: 250px;
    font-weight: 600;
}
.color-input-group input[type="color"] {
    width: 60px;
    height: 40px;
    border: 2px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
}
.color-input-group input[type="text"] {
    width: 100px;
    font-family: monospace;
}
.color-input-group input[type="range"] {
    width: 150px;
}
.color-input-group .range-value {
    min-width: 50px;
    font-family: monospace;
}
.contrast-badge {
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    margin-left: 10px;
}
.contrast-pass {
    background: #d4edda;
    color: #155724;
}
.contrast-fail {
    background: #f8d7da;
    color: #721c24;
}
.contrast-warning {
    background: #fff3cd;
    color: #856404;
}
.auto-text-btn {
    padding: 4px 10px;
    font-size: 12px;
    cursor: pointer;
    border: 1px solid #ccc;
    border-radius: 4px;
    background: #f8f9fa;
}
.auto-text-btn:hover {
    background: #e9ecef;
}
.theme-base-selector {
    margin-bottom: 30px;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 4px;
}
.theme-base-selector .btn {
    margin-right: 10px;
}
.a11y-info {
    padding: 15px;
    background: #e7f3ff;
    border-left: 4px solid #007bff;
    border-radius: 4px;
    margin-bottom: 20px;
}
.a11y-info h4 {
    margin-top: 0;
    color: #0056b3;
}
.preview-button {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 4px;
    font-weight: 600;
    margin-right: 10px;
    margin-bottom: 10px;
    cursor: default;
}
.color-section {
    margin-bottom: 30px;
    padding: 20px;
    background: #fafafa;
    border-radius: 8px;
    border: 1px solid #eee;
}
.color-section h4 {
    margin-top: 0;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #ddd;
}
</style>

<div class="theme-editor-form">
    
    <div class="a11y-info">
        <h4>♿ Barrierefreiheits-Hinweis</h4>
        <p>Der Theme-Editor prüft automatisch die WCAG 2.1 Kontrastanforderungen. Ein Kontrastverhältnis von mindestens <strong>4.5:1</strong> ist für normalen Text erforderlich, <strong>3:1</strong> für großen Text und UI-Komponenten.</p>
    </div>
    
    <div class="theme-base-selector">
        <h3>Theme-Basis wählen:</h3>
        <?php foreach ($themeBases as $key => $label): ?>
            <a href="?page=consent_manager/theme_editor&theme_base=<?= $key ?>" 
               class="btn <?= $themeBase === $key ? 'btn-primary' : 'btn-default' ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </div>

    <form action="<?= rex_url::currentBackendPage(['theme_base' => $themeBase]) ?>" method="post">
        <?= $csrfToken->getHiddenField() ?>
        <input type="hidden" name="formsubmit" value="1">
        <input type="hidden" name="theme_base" value="<?= $themeBase ?>">
        
        <fieldset>
            <legend>Theme-Information</legend>
            
            <div class="form-group">
                <label for="theme_name">Theme-Name:</label>
                <input type="text" class="form-control" id="theme_name" name="theme_name" 
                       value="Custom <?= $themeBases[$themeBase] ?>" required>
            </div>
            
            <div class="form-group">
                <label for="theme_description">Beschreibung:</label>
                <textarea class="form-control" id="theme_description" name="theme_description" rows="2">Individuell angepasstes <?= $themeBases[$themeBase] ?> Theme mit eigenen Farben</textarea>
            </div>
        </fieldset>
        
        <div class="color-section">
            <h4>🎨 Grundfarben</h4>
            
            <div class="color-input-group">
                <label for="background_color">Hintergrundfarbe:</label>
                <input type="color" id="background_color" name="background_color" value="<?= $colors['background'] ?>">
                <input type="text" class="form-control color-hex" value="<?= $colors['background'] ?>" readonly>
            </div>
            
            <div class="color-input-group">
                <label for="text_color">Textfarbe:</label>
                <input type="color" id="text_color" name="text_color" value="<?= $colors['text'] ?>">
                <input type="text" class="form-control color-hex" value="<?= $colors['text'] ?>" readonly>
                <span class="contrast-badge" id="text-contrast-badge">Prüfe...</span>
            </div>
            
            <div class="color-input-group">
                <label for="accent_color">Akzentfarbe (Rahmen, Details):</label>
                <input type="color" id="accent_color" name="accent_color" value="<?= $colors['accent'] ?>">
                <input type="text" class="form-control color-hex" value="<?= $colors['accent'] ?>" readonly>
            </div>
        </div>
        
        <div class="color-section">
            <h4>🔘 Buttons</h4>
            
            <div class="color-input-group">
                <label for="button_bg">Button-Hintergrund:</label>
                <input type="color" id="button_bg" name="button_bg" value="<?= $colors['button_bg'] ?>" data-auto-text="button_text">
                <input type="text" class="form-control color-hex" value="<?= $colors['button_bg'] ?>" readonly>
                <button type="button" class="auto-text-btn" onclick="autoTextColor('button_bg', 'button_text')">🔄 Textfarbe berechnen</button>
            </div>
            
            <div class="color-input-group">
                <label for="button_text">Button-Textfarbe:</label>
                <input type="color" id="button_text" name="button_text" value="<?= $colors['button_text'] ?>">
                <input type="text" class="form-control color-hex" value="<?= $colors['button_text'] ?>" readonly>
                <span class="contrast-badge" id="button-contrast-badge">Prüfe...</span>
            </div>
            
            <div class="color-input-group">
                <label for="button_hover">Button-Hover-Hintergrund:</label>
                <input type="color" id="button_hover" name="button_hover" value="<?= $colors['button_hover'] ?>" data-auto-text="button_hover_text">
                <input type="text" class="form-control color-hex" value="<?= $colors['button_hover'] ?>" readonly>
                <button type="button" class="auto-text-btn" onclick="autoTextColor('button_hover', 'button_hover_text')">🔄 Textfarbe berechnen</button>
            </div>
            
            <div class="color-input-group">
                <label for="button_hover_text">Button-Hover-Textfarbe:</label>
                <input type="color" id="button_hover_text" name="button_hover_text" value="<?= $colors['button_hover_text'] ?>">
                <input type="text" class="form-control color-hex" value="<?= $colors['button_hover_text'] ?>" readonly>
                <span class="contrast-badge" id="button-hover-contrast-badge">Prüfe...</span>
            </div>
            
            <div style="margin-top: 15px; padding: 15px; background: #fff; border-radius: 4px;">
                <strong>Vorschau:</strong><br><br>
                <span class="preview-button" id="button-preview">Button-Text</span>
                <span class="preview-button" id="button-hover-preview">Hover-Zustand</span>
            </div>
        </div>
        
        <div class="color-section">
            <h4>🔗 Links & Focus</h4>
            
            <div class="color-input-group">
                <label for="link_color">Link-Farbe:</label>
                <input type="color" id="link_color" name="link_color" value="<?= $colors['link'] ?>">
                <input type="text" class="form-control color-hex" value="<?= $colors['link'] ?>" readonly>
                <span class="contrast-badge" id="link-contrast-badge">Prüfe...</span>
            </div>
            
            <div class="color-input-group">
                <label for="focus_color">Focus-Farbe (Tastatur-Navigation):</label>
                <input type="color" id="focus_color" name="focus_color" value="<?= $colors['focus'] ?>">
                <input type="text" class="form-control color-hex" value="<?= $colors['focus'] ?>" readonly>
            </div>
        </div>
        
        <div class="color-section">
            <h4>🎭 Overlay & Layout</h4>
            
            <div class="color-input-group">
                <label for="overlay_color">Overlay-Farbe:</label>
                <input type="color" id="overlay_color" name="overlay_color" value="<?= $colors['overlay'] ?>">
                <input type="text" class="form-control color-hex" value="<?= $colors['overlay'] ?>" readonly>
            </div>
            
            <div class="color-input-group">
                <label for="overlay_opacity">Overlay-Transparenz:</label>
                <input type="range" id="overlay_opacity" name="overlay_opacity" min="0" max="100" value="<?= $colors['overlay_opacity'] ?>">
                <span class="range-value" id="overlay_opacity_value"><?= $colors['overlay_opacity'] ?>%</span>
            </div>
            
            <div class="color-input-group">
                <label for="border_radius">Eckenradius (px):</label>
                <input type="range" id="border_radius" name="border_radius" min="0" max="20" value="<?= $colors['border_radius'] ?>">
                <span class="range-value" id="border_radius_value"><?= $colors['border_radius'] ?>px</span>
            </div>
            
            <div class="color-input-group">
                <label for="border_width">Rahmenbreite (px):</label>
                <input type="range" id="border_width" name="border_width" min="1" max="5" value="<?= $colors['border_width'] ?>">
                <span class="range-value" id="border_width_value"><?= $colors['border_width'] ?>px</span>
            </div>
        </div>
        
        <fieldset class="rex-form-action">
            <button class="btn btn-save" type="submit">
                <i class="fa fa-save"></i> Theme erstellen und speichern
            </button>
            <a href="?page=consent_manager/theme" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Zurück zur Theme-Übersicht
            </a>
        </fieldset>
    </form>
</div>

<script>
// Kontrast-Berechnung nach WCAG 2.1
function getLuminance(hex) {
    const rgb = hexToRgb(hex);
    const [r, g, b] = [rgb.r, rgb.g, rgb.b].map(v => {
        v /= 255;
        return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
    });
    return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}

function hexToRgb(hex) {
    hex = hex.replace('#', '');
    return {
        r: parseInt(hex.substr(0, 2), 16),
        g: parseInt(hex.substr(2, 2), 16),
        b: parseInt(hex.substr(4, 2), 16)
    };
}

function getContrastRatio(hex1, hex2) {
    const l1 = getLuminance(hex1);
    const l2 = getLuminance(hex2);
    const lighter = Math.max(l1, l2);
    const darker = Math.min(l1, l2);
    return (lighter + 0.05) / (darker + 0.05);
}

function updateContrastBadge(badgeId, ratio) {
    const badge = document.getElementById(badgeId);
    if (!badge) return;
    
    const ratioText = ratio.toFixed(2) + ':1';
    
    if (ratio >= 4.5) {
        badge.className = 'contrast-badge contrast-pass';
        badge.textContent = '✓ ' + ratioText + ' (AAA)';
    } else if (ratio >= 3) {
        badge.className = 'contrast-badge contrast-warning';
        badge.textContent = '⚠ ' + ratioText + ' (nur große Texte)';
    } else {
        badge.className = 'contrast-badge contrast-fail';
        badge.textContent = '✗ ' + ratioText + ' (unzureichend)';
    }
}

// Automatische Textfarbe berechnen (Schwarz oder Weiß)
function autoTextColor(bgInputId, textInputId) {
    const bgInput = document.getElementById(bgInputId);
    const textInput = document.getElementById(textInputId);
    
    const bgColor = bgInput.value;
    const luminance = getLuminance(bgColor);
    
    // Wähle Schwarz oder Weiß basierend auf Hintergrund-Helligkeit
    const textColor = luminance > 0.179 ? '#000000' : '#ffffff';
    
    textInput.value = textColor;
    textInput.nextElementSibling.value = textColor;
    
    updateAllContrasts();
    updateButtonPreviews();
}

function updateAllContrasts() {
    const bg = document.getElementById('background_color').value;
    const text = document.getElementById('text_color').value;
    const buttonBg = document.getElementById('button_bg').value;
    const buttonText = document.getElementById('button_text').value;
    const buttonHover = document.getElementById('button_hover').value;
    const buttonHoverText = document.getElementById('button_hover_text').value;
    const link = document.getElementById('link_color').value;
    
    updateContrastBadge('text-contrast-badge', getContrastRatio(bg, text));
    updateContrastBadge('button-contrast-badge', getContrastRatio(buttonBg, buttonText));
    updateContrastBadge('button-hover-contrast-badge', getContrastRatio(buttonHover, buttonHoverText));
    updateContrastBadge('link-contrast-badge', getContrastRatio(bg, link));
}

function updateButtonPreviews() {
    const buttonBg = document.getElementById('button_bg').value;
    const buttonText = document.getElementById('button_text').value;
    const buttonHover = document.getElementById('button_hover').value;
    const buttonHoverText = document.getElementById('button_hover_text').value;
    const borderRadius = document.getElementById('border_radius').value + 'px';
    
    const preview = document.getElementById('button-preview');
    const hoverPreview = document.getElementById('button-hover-preview');
    
    preview.style.backgroundColor = buttonBg;
    preview.style.color = buttonText;
    preview.style.borderRadius = borderRadius;
    
    hoverPreview.style.backgroundColor = buttonHover;
    hoverPreview.style.color = buttonHoverText;
    hoverPreview.style.borderRadius = borderRadius;
}

// Update hex values when color picker changes
document.querySelectorAll('input[type="color"]').forEach(function(colorInput) {
    colorInput.addEventListener('input', function() {
        const hexField = this.nextElementSibling;
        if (hexField && hexField.classList.contains('color-hex')) {
            hexField.value = this.value;
        }
        updateAllContrasts();
        updateButtonPreviews();
    });
});

// Update range values
document.querySelectorAll('input[type="range"]').forEach(function(rangeInput) {
    rangeInput.addEventListener('input', function() {
        const valueSpan = document.getElementById(this.id + '_value');
        if (valueSpan) {
            const unit = this.id === 'overlay_opacity' ? '%' : 'px';
            valueSpan.textContent = this.value + unit;
        }
        updateButtonPreviews();
    });
});

// Initial update
document.addEventListener('DOMContentLoaded', function() {
    updateAllContrasts();
    updateButtonPreviews();
});
</script>
