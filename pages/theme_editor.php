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
    'normal' => 'Accessibility (Normal) - Popup zentriert',
    'compact' => 'Accessibility (Compact) - Popup kompakt',
    'banner_top' => 'Accessibility Banner (Top) - Banner oben',
    'banner_bottom' => 'Accessibility Banner (Bottom) - Banner unten',
    'minimal' => 'Accessibility Minimal - Ecke unten rechts',
    'fluid' => 'Accessibility Fluid - Responsive mit Glaseffekt',
    'fluid_dark' => 'Accessibility Fluid Dark - Responsive mit dunklem Glaseffekt',
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
    'banner_top' => [
        'accent' => '#2c5aa0',
        'button_bg' => '#2c5aa0',
        'button_text' => '#ffffff',
        'button_hover' => '#1e4080',
        'button_hover_text' => '#ffffff',
        'focus' => '#ffcc00',
        'link' => '#2c5aa0',
        'link_hover' => '#1e4080',
        'details_link' => '#2c5aa0',
        'details_link_hover' => '#1e4080',
        'details_toggle_border' => '#2c5aa0',
        'details_toggle_border_width' => '2',
        'details_bg' => '#e8f0fa',
        'details_text' => '#1a1a1a',
        'details_heading' => '#1a1a1a',
        'details_border' => '#b8d0ed',
        'title' => '#1a1a1a',
        'background' => '#ffffff',
        'text' => '#1a1a1a',
        'overlay' => '#000000',
        'overlay_opacity' => '60',
        'border_radius' => '0',
        'border_width' => '0',
        'button_style' => 'filled',
        'button_radius' => '6',
        'button_border_width' => '0',
        'button_border_color' => '#2c5aa0',
        'shadow_style' => 'large',
        'shadow_color' => '#000000',
        'shadow_opacity' => '20',
    ],
    'banner_bottom' => [
        'accent' => '#ffcc00',
        'button_bg' => '#ffcc00',
        'button_text' => '#1a1a1a',
        'button_hover' => '#e6b800',
        'button_hover_text' => '#000000',
        'focus' => '#ffffff',
        'link' => '#ffcc00',
        'link_hover' => '#e6b800',
        'details_link' => '#ffcc00',
        'details_link_hover' => '#e6b800',
        'details_toggle_border' => '#ffcc00',
        'details_toggle_border_width' => '2',
        'details_bg' => '#2a2a2a',
        'details_text' => '#e8e8e8',
        'details_heading' => '#ffffff',
        'details_border' => '#404040',
        'title' => '#ffffff',
        'background' => '#1a1a1a',
        'text' => '#e8e8e8',
        'overlay' => '#000000',
        'overlay_opacity' => '70',
        'border_radius' => '0',
        'border_width' => '0',
        'button_style' => 'filled',
        'button_radius' => '4',
        'button_border_width' => '0',
        'button_border_color' => '#ffcc00',
        'shadow_style' => 'large',
        'shadow_color' => '#000000',
        'shadow_opacity' => '30',
    ],
    'minimal' => [
        'accent' => '#1a73e8',
        'button_bg' => '#1a73e8',
        'button_text' => '#ffffff',
        'button_hover' => '#1557b0',
        'button_hover_text' => '#ffffff',
        'focus' => '#ffcc00',
        'link' => '#1a73e8',
        'link_hover' => '#1557b0',
        'details_link' => '#1a73e8',
        'details_link_hover' => '#1557b0',
        'details_toggle_border' => '#1a73e8',
        'details_toggle_border_width' => '2',
        'details_bg' => '#f5f5f5',
        'details_text' => '#1a1a1a',
        'details_heading' => '#1a1a1a',
        'details_border' => '#e0e0e0',
        'title' => '#1a1a1a',
        'background' => '#ffffff',
        'text' => '#333333',
        'overlay' => '#000000',
        'overlay_opacity' => '40',
        'border_radius' => '12',
        'border_width' => '1',
        'button_style' => 'filled',
        'button_radius' => '8',
        'button_border_width' => '0',
        'button_border_color' => '#1a73e8',
        'shadow_style' => 'large',
        'shadow_color' => '#000000',
        'shadow_opacity' => '20',
    ],
    'fluid' => [
        'accent' => '#6366f1',
        'button_bg' => '#6366f1',
        'button_text' => '#ffffff',
        'button_hover' => '#4f46e5',
        'button_hover_text' => '#ffffff',
        'focus' => '#fbbf24',
        'link' => '#6366f1',
        'link_hover' => '#4f46e5',
        'details_link' => '#6366f1',
        'details_link_hover' => '#4f46e5',
        'details_toggle_border' => '#6366f1',
        'details_toggle_border_width' => '2',
        'details_bg' => '#ffffff',
        'details_bg_opacity' => '95',
        'details_text' => '#1e293b',
        'details_heading' => '#0f172a',
        'details_border' => '#94a3b8',
        'details_border_opacity' => '30',
        'title' => '#0f172a',
        'background' => '#ffffff',
        'background_opacity' => '85',
        'text' => '#1e293b',
        'overlay' => '#0f172a',
        'overlay_opacity' => '50',
        'border_radius' => '16',
        'border_width' => '1',
        'button_style' => 'filled',
        'button_radius' => '12',
        'button_border_width' => '0',
        'button_border_color' => '#6366f1',
        'shadow_style' => 'large',
        'shadow_color' => '#6366f1',
        'shadow_opacity' => '15',
    ],
    'fluid_dark' => [
        'accent' => '#a78bfa',
        'button_bg' => '#a78bfa',
        'button_text' => '#0f0f23',
        'button_hover' => '#c4b5fd',
        'button_hover_text' => '#0f0f23',
        'focus' => '#fbbf24',
        'link' => '#a78bfa',
        'link_hover' => '#c4b5fd',
        'details_link' => '#a78bfa',
        'details_link_hover' => '#c4b5fd',
        'details_toggle_border' => '#a78bfa',
        'details_toggle_border_width' => '2',
        'details_bg' => '#1e1e2e',
        'details_bg_opacity' => '95',
        'details_text' => '#cdd6f4',
        'details_heading' => '#ffffff',
        'details_border' => '#45475a',
        'details_border_opacity' => '50',
        'title' => '#ffffff',
        'background' => '#1e1e2e',
        'background_opacity' => '85',
        'text' => '#cdd6f4',
        'overlay' => '#000000',
        'overlay_opacity' => '60',
        'border_radius' => '16',
        'border_width' => '1',
        'button_style' => 'filled',
        'button_radius' => '12',
        'button_border_width' => '0',
        'button_border_color' => '#a78bfa',
        'shadow_style' => 'large',
        'shadow_color' => '#a78bfa',
        'shadow_opacity' => '20',
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
        'details_bg_opacity' => rex_request::post('details_bg_opacity', 'string', $defaultColors[$themeBase]['details_bg_opacity'] ?? '100'),
        'details_text' => rex_request::post('details_text', 'string', $defaultColors[$themeBase]['details_text']),
        'details_heading' => rex_request::post('details_heading', 'string', $defaultColors[$themeBase]['details_heading']),
        'details_border' => rex_request::post('details_border', 'string', $defaultColors[$themeBase]['details_border']),
        'details_border_opacity' => rex_request::post('details_border_opacity', 'string', $defaultColors[$themeBase]['details_border_opacity'] ?? '100'),
        'title' => rex_request::post('title_color', 'string', $defaultColors[$themeBase]['title']),
        'background' => rex_request::post('background_color', 'string', $defaultColors[$themeBase]['background']),
        'background_opacity' => rex_request::post('background_opacity', 'string', $defaultColors[$themeBase]['background_opacity'] ?? '100'),
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
