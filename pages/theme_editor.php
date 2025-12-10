<?php

/**
 * A11y Theme Editor Page
 * 
 * Backend-Seite zum Erstellen von barrierefreien Themes.
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

// Default colors
$defaultColors = [
    'normal' => [
        'accent' => '#333333',
        'button_bg' => '#333333',
        'button_text' => '#ffffff',
        'button_hover' => '#000000',
        'button_hover_text' => '#ffffff',
        'focus' => '#0066cc',
        'link' => '#0066cc',
        'link_hover' => '#004499',
        'details_link' => '#0066cc',
        'details_link_hover' => '#004499',
        // Details-Bereich (aufgeklappte Ansicht)
        'details_bg' => '#f8f9fa',
        'details_text' => '#1a1a1a',
        'details_heading' => '#1a1a1a',
        'details_border' => '#dee2e6',
        'title' => '#1a1a1a',
        'background' => '#ffffff',
        'text' => '#1a1a1a',
        'overlay' => '#000000',
        'overlay_opacity' => '75',
        'border_radius' => '4',
        'border_width' => '3',
        // Button-Styles
        'button_style' => 'filled',
        'button_radius' => '4',
        'button_border_width' => '2',
        'button_border_color' => '#333333',
        // Shadow-Styles
        'shadow_style' => 'medium',
        'shadow_color' => '#000000',
        'shadow_opacity' => '15',
    ],
    'compact' => [
        'accent' => '#333333',
        'button_bg' => '#333333',
        'button_text' => '#ffffff',
        'button_hover' => '#000000',
        'button_hover_text' => '#ffffff',
        'focus' => '#0066cc',
        'link' => '#0066cc',
        'link_hover' => '#004499',
        'details_link' => '#0066cc',
        'details_link_hover' => '#004499',
        'details_toggle_border' => '#0066cc',
        'details_toggle_border_width' => '2',
        // Details-Bereich (aufgeklappte Ansicht)
        'details_bg' => '#f8f9fa',
        'details_text' => '#1a1a1a',
        'details_heading' => '#1a1a1a',
        'details_border' => '#dee2e6',
        'title' => '#1a1a1a',
        'background' => '#ffffff',
        'text' => '#1a1a1a',
        'overlay' => '#000000',
        'overlay_opacity' => '75',
        'border_radius' => '4',
        'border_width' => '2',
        // Button-Styles
        'button_style' => 'filled',
        'button_radius' => '4',
        'button_border_width' => '2',
        'button_border_color' => '#333333',
        // Shadow-Styles
        'shadow_style' => 'medium',
        'shadow_color' => '#000000',
        'shadow_opacity' => '15',
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
        'link_hover' => rex_request::post('link_hover', 'string', $defaultColors[$themeBase]['link_hover']),
        'details_link' => rex_request::post('details_link', 'string', $defaultColors[$themeBase]['details_link']),
        'details_link_hover' => rex_request::post('details_link_hover', 'string', $defaultColors[$themeBase]['details_link_hover']),
        'details_toggle_border' => rex_request::post('details_toggle_border', 'string', $defaultColors[$themeBase]['details_toggle_border']),
        'details_toggle_border_width' => rex_request::post('details_toggle_border_width', 'string', $defaultColors[$themeBase]['details_toggle_border_width']),
        // Details-Bereich
        'details_bg' => rex_request::post('details_bg', 'string', $defaultColors[$themeBase]['details_bg']),
        'details_text' => rex_request::post('details_text', 'string', $defaultColors[$themeBase]['details_text']),
        'details_heading' => rex_request::post('details_heading', 'string', $defaultColors[$themeBase]['details_heading']),
        'details_border' => rex_request::post('details_border', 'string', $defaultColors[$themeBase]['details_border']),
        'title' => rex_request::post('title_color', 'string', $defaultColors[$themeBase]['title']),
        'background' => rex_request::post('background_color', 'string', $defaultColors[$themeBase]['background']),
        'text' => rex_request::post('text_color', 'string', $defaultColors[$themeBase]['text']),
        'overlay' => rex_request::post('overlay_color', 'string', $defaultColors[$themeBase]['overlay']),
        'overlay_opacity' => rex_request::post('overlay_opacity', 'string', $defaultColors[$themeBase]['overlay_opacity']),
        'border_radius' => rex_request::post('border_radius', 'string', $defaultColors[$themeBase]['border_radius']),
        'border_width' => rex_request::post('border_width', 'string', $defaultColors[$themeBase]['border_width']),
        // Button-Styles
        'button_style' => rex_request::post('button_style', 'string', $defaultColors[$themeBase]['button_style']),
        'button_radius' => rex_request::post('button_radius', 'string', $defaultColors[$themeBase]['button_radius']),
        'button_border_width' => rex_request::post('button_border_width', 'string', $defaultColors[$themeBase]['button_border_width']),
        'button_border_color' => rex_request::post('button_border_color', 'string', $defaultColors[$themeBase]['button_border_color']),
        // Shadow-Styles
        'shadow_style' => rex_request::post('shadow_style', 'string', $defaultColors[$themeBase]['shadow_style']),
        'shadow_color' => rex_request::post('shadow_color', 'string', $defaultColors[$themeBase]['shadow_color']),
        'shadow_opacity' => rex_request::post('shadow_opacity', 'string', $defaultColors[$themeBase]['shadow_opacity']),
    ];
} else {
    $colors = $defaultColors[$themeBase];
}

// Handle form submission
if ('1' === rex_request::post('formsubmit', 'string')) {
    if (!$csrfToken->isValid()) {
        echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } else {
        $themeName = rex_request::post('theme_name', 'string', 'Custom A11y Theme');
        $themeDescription = rex_request::post('theme_description', 'string', 'Individuell angepasstes Barrierefreiheits-Theme');

        // Generate SCSS content
        $scssContent = Theme::generateA11yThemeScss($themeBase, $themeName, $themeDescription, $colors);

        // Save to project addon
        $projectAddon = rex_addon::get('project');
        $themesDir = $projectAddon->getPath('consent_manager_themes/');

        if (!is_dir($themesDir)) {
            rex_dir::create($themesDir);
        }

        $filename = 'consent_manager_frontend_' . rex_string::normalize($themeName) . '.scss';
        $filepath = $themesDir . $filename;

        if (rex_file::put($filepath, $scssContent)) {
            try {
                Theme::generateThemeAssets('project:' . $filename);
                Theme::copyAllAssets();

                echo rex_view::success(rex_i18n::msg('consent_manager_theme_editor_saved', $themeName));
                echo rex_view::info('Theme gespeichert als: <code>' . rex_escape($filename) . '</code><br>Du kannst es jetzt unter "Theme" auswählen.');
            } catch (Exception $e) {
                echo rex_view::error('Fehler beim Kompilieren: ' . rex_escape($e->getMessage()));
            }
        } else {
            echo rex_view::error('Fehler beim Speichern der Datei.');
        }
    }
}

// Render the fragment
$fragment = new rex_fragment();
$fragment->setVar('themeBase', $themeBase);
$fragment->setVar('themeBases', $themeBases);
$fragment->setVar('colors', $colors);
$fragment->setVar('csrfToken', $csrfToken);
echo $fragment->parse('ConsentManager/theme_editor.php');
