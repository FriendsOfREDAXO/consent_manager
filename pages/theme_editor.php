<?php

/**
 * A11y Theme Editor
 * Customize colors for Accessibility themes.
 */

use FriendsOfRedaxo\ConsentManager\Theme;

// Check if project addon is available and installed
// TODO: Prüfen ob das weg kann.
// Weiter unten wird nochmal abgefragt, on Project installiert ist.
if (!rex_addon::get('project')->isAvailable()) {
    echo rex_view::error(rex_i18n::msg('consent_manager_theme_editor_project_addon_required'));
    return;
}

$csrfToken = rex_csrf_token::factory('consent_manager_theme_editor');

// Theme bases
$themeBase = rex_request('theme_base', 'string', 'normal');
$themeBases = [
    'normal' => 'Accessibility (Normal)',
    'compact' => 'Accessibility (Compact)',
];

// Default colors
$defaultColors = [
    'normal' => [
        'accent' => '#333333',
        'button_bg' => '#333333',
        'button_hover' => '#000000',
        'focus' => '#0066cc',
        'link' => '#0066cc',
    ],
    'compact' => [
        'accent' => '#333333',
        'button_bg' => '#333333',
        'button_hover' => '#000000',
        'focus' => '#0066cc',
        'link' => '#0066cc',
    ],
];

// Get current colors from form or defaults
$colors = [];
if ('1' === rex_post('formsubmit', 'string')) {
    $colors = [
        'accent' => rex_post('accent_color', 'string', $defaultColors[$themeBase]['accent']),
        'button_bg' => rex_post('button_bg', 'string', $defaultColors[$themeBase]['button_bg']),
        'button_hover' => rex_post('button_hover', 'string', $defaultColors[$themeBase]['button_hover']),
        'focus' => rex_post('focus_color', 'string', $defaultColors[$themeBase]['focus']),
        'link' => rex_post('link_color', 'string', $defaultColors[$themeBase]['link']),
    ];
} else {
    $colors = $defaultColors[$themeBase];
}

// Save theme
if ('1' === rex_post('formsubmit', 'string') && !$csrfToken->isValid()) {
    echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
} elseif ('1' === rex_post('formsubmit', 'string')) {
    $themeName = rex_post('theme_name', 'string', 'Custom A11y Theme');
    $themeDescription = rex_post('theme_description', 'string', 'Individuell angepasstes Barrierefreiheits-Theme');

    // Generate SCSS content
    $scssContent = generateA11yThemeScss($themeBase, $themeName, $themeDescription, $colors);

    // Save to project addon
    /**
     * REXSTAN: If condition is always true.
     * NOTE: denn am Anfang wird ja schon auf Project geprüft. Eine der beiden Abfragen ist Murks.
     */
    if (rex_addon::get('project')->isAvailable()) {
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
    } else {
        echo rex_view::warning('Das project-Addon muss installiert sein, um eigene Themes zu speichern.');
    }
}

// TODO: Funktion verlagern in eine eigene Datei? -> Thomas fragen
function generateA11yThemeScss($base, $name, $description, $colors)
{
    $isCompact = ('compact' === $base);

    $fontSize = $isCompact ? '15px' : '16px';
    $lineHeight = $isCompact ? '1.5em' : '1.6em';
    $padding = $isCompact ? '1.5em' : '2.5em';
    $paddingOuter = $isCompact ? '0.75em' : '1em';
    $borderWidth = $isCompact ? '2px' : '3px';
    $maxWidth = $isCompact ? '55em' : '65em';
    $buttonPadding = $isCompact ? '10px 20px' : '12px 24px';
    $buttonMinHeight = $isCompact ? '44px' : '48px';
    $buttonMinWidth = $isCompact ? '140px' : '150px';

    $hexToRgba = static function ($hex, $alpha = 0.2) {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "rgba($r, $g, $b, $alpha)";
    };

    $accentRgba = $hexToRgba($colors['accent'], 0.3);
    $focusRgba = $hexToRgba($colors['focus'], 0.2);
    $linkHoverBg = $hexToRgba($colors['link'], 0.1);
    $cookieTitleBg = $hexToRgba($colors['accent'], 0.05);

    return <<<SCSS
        /*
        Theme: {"name": "$name", "description": "$description", "type": "light", "style": "Popup zentriert, Accessibility-optimiert, Custom Colors", "autor": "@custom"}
        */

        \$font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        \$font-size: $fontSize;
        \$line-height: $lineHeight;

        \$overlay-background: rgba(0, 0, 0, 0.75);
        \$consent_manager-background: #ffffff;
        \$consent_manager-border: #1a1a1a;
        \$text-color: #1a1a1a;

        \$accent-color: {$colors['accent']};
        \$accent-hover: {$colors['button_hover']};
        \$button-bg: {$colors['button_bg']};
        \$button-hover: {$colors['button_hover']};

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

        button:focus,
        a:focus,
        input:focus,
        [tabindex]:focus {
            outline: 3px solid \$focus-color !important;
            outline-offset: 2px !important;
            box-shadow: 0 0 0 3px \$focus-shadow !important;
        }

        @media (prefers-contrast: high) {
            button:focus,
            a:focus,
            input:focus,
            [tabindex]:focus {
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
            border: 2px solid \$button-bg;
            color: #ffffff;
            padding: $buttonPadding;
            border-radius: 4px;
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
    max-width: 800px;
}
.color-input-group {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}
.color-input-group label {
    min-width: 200px;
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
.color-preview {
    width: 40px;
    height: 40px;
    border: 2px solid #ddd;
    border-radius: 4px;
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
</style>

<div class="theme-editor-form">
    
    <div class="theme-base-selector">
        <h3>Theme-Basis wählen:</h3>
        <?php foreach ($themeBases as $key => $label): ?>
            <a href="?page=consent_manager/theme_editor&theme_base=<?= $key ?>" 
               class="btn <?= $themeBase === $key ? 'btn-primary' : 'btn-default' ?>">
                <?= $label ?>
            </a>
        <?php endforeach ?>
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
        
        <fieldset>
            <legend>Farben anpassen</legend>
            
            <div class="color-input-group">
                <label for="accent_color">Akzentfarbe (Rahmen, Details):</label>
                <input type="color" id="accent_color" name="accent_color" value="<?= $colors['accent'] ?>">
                <input type="text" class="form-control" value="<?= $colors['accent'] ?>" readonly>
            </div>
            
            <div class="color-input-group">
                <label for="button_bg">Button-Hintergrund:</label>
                <input type="color" id="button_bg" name="button_bg" value="<?= $colors['button_bg'] ?>">
                <input type="text" class="form-control" value="<?= $colors['button_bg'] ?>" readonly>
            </div>
            
            <div class="color-input-group">
                <label for="button_hover">Button-Hover:</label>
                <input type="color" id="button_hover" name="button_hover" value="<?= $colors['button_hover'] ?>">
                <input type="text" class="form-control" value="<?= $colors['button_hover'] ?>" readonly>
            </div>
            
            <div class="color-input-group">
                <label for="focus_color">Focus-Farbe (Tastatur-Navigation):</label>
                <input type="color" id="focus_color" name="focus_color" value="<?= $colors['focus'] ?>">
                <input type="text" class="form-control" value="<?= $colors['focus'] ?>" readonly>
            </div>
            
            <div class="color-input-group">
                <label for="link_color">Link-Farbe:</label>
                <input type="color" id="link_color" name="link_color" value="<?= $colors['link'] ?>">
                <input type="text" class="form-control" value="<?= $colors['link'] ?>" readonly>
            </div>
        </fieldset>
        
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
// Update hex values when color picker changes
document.querySelectorAll('input[type="color"]').forEach(function(colorInput) {
    colorInput.addEventListener('change', function() {
        this.nextElementSibling.value = this.value;
    });
});
</script>
