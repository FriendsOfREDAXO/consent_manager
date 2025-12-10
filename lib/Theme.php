<?php

namespace FriendsOfRedaxo\ConsentManager;

use rex_addon;
use rex_dir;
use rex_file;
use rex_path;
use rex_scss_compiler;

/**
 * @api
 */

class Theme
{
    public string $theme = '';

    /**
     * Construtor.
     */
    public function __construct(string $theme = '')
    {
        $this->setTheme($theme);
    }

    /**
     * Set theme.
     */
    public function setTheme(string $theme): void
    {
        $this->theme = $theme;
    }

    /**
     * Get theme.
     */
    public function getTheme(): string
    {
        return $this->theme;
    }

    /**
     * Compile scss.
     */
    public function compileScss(string $source, string $dest): void
    {
        $compiler = new rex_scss_compiler();
        $compiler->setScssFile($source);
        $compiler->setCssFile($dest);
        $compiler->compile();
    }

    /**
     * Get compiled scss.
     */
    public function getCompiledStyle(string $theme = ''): string|false
    {
        if ('' === $theme) {
            $theme = $this->getTheme();
        }

        if (str_starts_with($theme, 'project:')) {
            $projectAddon = rex_addon::get('project');
            $themefile = str_replace('project:', '', $theme);
            $scssFile = $projectAddon->getPath('consent_manager_themes/' . $themefile);
            if (!$projectAddon->isAvailable() || !file_exists($scssFile)) {
                return false;
            }
            $tempfile = rex_path::addonCache('consent_manager', $themefile . '_preview.css');
        } else {
            $scssFile = rex_path::addon('consent_manager', 'scss/' . $theme);
            if (!file_exists($scssFile)) {
                return false;
            }
            $tempfile = rex_path::addonCache('consent_manager', $theme . '_preview.css');
        }

        $this->compileScss($scssFile, $tempfile);
        $css = trim((string) rex_file::get($tempfile));
        rex_file::delete($tempfile);

        return $css;
    }

    /**
     * Compile addon default assets (backend+frontend).
     */
    public static function generateDefaultAssets(): void
    {
        $cmtheme = new self();
        $cmtheme->compileScss(
            rex_path::addon('consent_manager', 'scss/consent_manager_backend.scss'),
            rex_path::addon('consent_manager', 'assets/consent_manager_backend.css'),
        );
        $cmtheme->compileScss(
            rex_path::addon('consent_manager', 'scss/consent_manager_frontend.scss'),
            rex_path::addon('consent_manager', 'assets/consent_manager_frontend.css'),
        );
    }

    /**
     * Copy assets to assets-Direcotry.
     */
    public static function copyAllAssets(): void
    {
        rex_dir::copy(
            rex_path::addon('consent_manager', 'assets'),
            rex_path::addonAssets('consent_manager'),
        );
    }

    /**
     * Compile theme assets.
     */
    public static function generateThemeAssets(string $theme): void
    {
        if (str_starts_with($theme, 'project:')) {
            // FIXME: Und wenn Project nicht aktiviert ist? siehe self::getCompiledStyle
            $projectAddon = rex_addon::get('project');
            $source = $projectAddon->getPath('consent_manager_themes/' . str_replace('project:', '', $theme));
            $dest = rex_path::addon('consent_manager', 'assets/' . str_replace('project:', 'project_', str_replace('.scss', '.css', $theme)));
        } else {
            $source = rex_path::addon('consent_manager', 'scss/' . $theme);
            $dest = rex_path::addon('consent_manager', 'assets/' . str_replace('.scss', '.css', $theme));
        }
        $cmtheme = new self();
        $cmtheme->compileScss($source, $dest);
    }

    /**
     * Get theme info from file.
     * @return array<string, string>
     */
    public function getThemeInformation(string $theme = ''): array
    {
        if ('' === $theme) {
            $theme = $this->getTheme();
        }
        if (str_starts_with($theme, 'project:')) {
            // FIXME: Und wenn Project nicht aktiviert ist? siehe self::getCompiledStyle
            $projectAddon = rex_addon::get('project');
            $themefile = $projectAddon->getPath('consent_manager_themes/' . str_replace('project:', '', $theme));
        } else {
            $themefile = rex_path::addon('consent_manager', 'scss/' . $theme);
        }
        $themefile = rex_file::get($themefile);
        $lines = explode("\n", (string) $themefile);

        $json = '';
        foreach ($lines as $line) {
            if (false !== strstr($line, 'Theme: ')) {
                $json = trim(str_replace('Theme: ', '', $line));
            }
        }
        $themeinfo = (array) json_decode($json, true);
        return $themeinfo;
    }

    /**
     * Generate A11y Theme SCSS from color configuration.
     * 
     * @param string $base Theme base ('normal' or 'compact')
     * @param string $name Theme name
     * @param string $description Theme description
     * @param array<string, string> $colors Color configuration
     * @return string Generated SCSS content
     */
    public static function generateA11yThemeScss(string $base, string $name, string $description, array $colors): string
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

        $hexToRgba = static function (string $hex, float $alpha = 0.2): string {
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
        $titleColor = $colors['title'] ?? $textColor;
        
        // Button-Textfarben
        $buttonText = $colors['button_text'] ?? '#ffffff';
        $buttonHoverText = $colors['button_hover_text'] ?? '#ffffff';
        
        // Button-Styles
        $buttonRadius = ($colors['button_radius'] ?? '4') . 'px';
        $buttonBorderWidth = ($colors['button_border_width'] ?? '2') . 'px';
        $buttonBorderColor = $colors['button_border_color'] ?? $colors['button_bg'];
        
        // Details-Toggle Button (anzeigen/ausblenden)
        $detailsLink = $colors['details_link'] ?? $colors['link'] ?? '#0066cc';
        $detailsLinkHover = $colors['details_link_hover'] ?? '#004499';
        $detailsToggleBorder = $colors['details_toggle_border'] ?? $detailsLink;
        $detailsToggleBorderWidth = ($colors['details_toggle_border_width'] ?? '2') . 'px';
        
        // Details-Bereich (aufgeklappte Ansicht)
        $detailsBg = $colors['details_bg'] ?? '#f8f9fa';
        $detailsText = $colors['details_text'] ?? '#1a1a1a';
        $detailsHeading = $colors['details_heading'] ?? '#1a1a1a';
        $detailsBorder = $colors['details_border'] ?? '#dee2e6';
        $detailsLinkColor = $colors['details_link'] ?? '#0066cc';
        $detailsLinkHoverColor = $colors['details_link_hover'] ?? '#004499';
        
        // Link-Hover-Farbe
        $linkHoverColor = $colors['link_hover'] ?? $colors['button_hover'];
        
        // Accent-Farbe
        $accentColor = $colors['accent'];
        $buttonBg = $colors['button_bg'];
        $buttonHover = $colors['button_hover'];
        $linkColor = $colors['link'];
        $focusColor = $colors['focus'];

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
\$title-color: $titleColor;

\$accent-color: $accentColor;
\$accent-hover: $buttonHover;
\$button-bg: $buttonBg;
\$button-text: $buttonText;
\$button-hover: $buttonHover;
\$button-hover-text: $buttonHoverText;
\$button-radius: $buttonRadius;
\$button-border-width: $buttonBorderWidth;
\$button-border-color: $buttonBorderColor;

\$focus-color: $focusColor;
\$focus-shadow: $focusRgba;

\$link-color: $linkColor;
\$link-hover-color: $linkHoverColor;
\$link-hover-bg: $linkHoverBg;

// Details-Toggle Button
\$details-toggle-color: $detailsLink;
\$details-toggle-hover: $detailsLinkHover;
\$details-toggle-border: $detailsToggleBorder;
\$details-toggle-border-width: $detailsToggleBorderWidth;

// Details-Bereich
\$details-bg: $detailsBg;
\$details-text: $detailsText;
\$details-heading: $detailsHeading;
\$details-border: $detailsBorder;
\$details-link: $detailsLinkColor;
\$details-link-hover: $detailsLinkHoverColor;

\$cookie-title-bg: $cookieTitleBg;
\$cookie-desc-bg: \$details-bg;
\$cookie-border: $accentColor;
\$cookie-accent: $accentColor;

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
    border-radius: $borderRadius !important;
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
    border-radius: $borderRadius;
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
        display: inline-flex !important;
        align-items: center !important;
        line-height: 1.4 !important;
        min-height: 44px !important;
        padding: 8px 14px !important;
        cursor: pointer !important;
        color: \$details-toggle-color !important;
        background-color: transparent !important;
        border: \$details-toggle-border-width solid \$details-toggle-border !important;
        border-radius: \$button-radius !important;
        font-size: 15px !important;
        font-weight: 600 !important;
        transition: 0.2s ease all !important;

        &:hover,
        &:focus {
            background-color: \$details-toggle-color !important;
            color: \$consent_manager-background !important;
            transform: translateY(-2px) !important;
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
    transition: 0.2s ease all !important;
    background: \$button-bg !important;
    border: \$button-border-width solid \$button-border-color !important;
    color: \$button-text !important;
    padding: $buttonPadding !important;
    border-radius: \$button-radius !important;
    font-size: 15px !important;
    font-weight: 600 !important;
    text-align: center !important;
    display: block !important;
    min-height: $buttonMinHeight !important;
    width: 100% !important;
    margin-bottom: 0.75em !important;
    cursor: pointer !important;
    line-height: 1.4 !important;
}

button.consent_manager-save-selection:hover,
button.consent_manager-save-selection:focus,
button.consent_manager-accept-all:hover,
button.consent_manager-accept-all:focus,
button.consent_manager-accept-none:hover,
button.consent_manager-accept-none:focus {
    background: \$button-hover !important;
    border-color: \$button-hover !important;
    color: \$button-hover-text !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 8px $accentRgba !important;
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
        border-radius: $borderRadius;
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
        color: \$details-heading !important;
        background-color: \$cookie-title-bg !important;
        padding: 10px 14px !important;
        margin: 1em 0 0 0 !important;
        font-weight: bold !important;
        font-size: 15px !important;
        border-left: 3px solid \$cookie-accent !important;
    }

    div.consent_manager-cookiegroup-description {
        border-left: 3px solid \$details-border !important;
        padding: 10px 14px !important;
        background: \$details-bg !important;
        color: \$details-text !important;
        font-size: 14px !important;
    }

    div.consent_manager-cookie {
        margin-top: 2px !important;
        border-left: 3px solid \$details-border !important;
        padding: 10px 14px !important;
        background: \$details-bg !important;
        color: \$details-text !important;
        font-size: 14px !important;

        span {
            display: block;
            margin-top: 0.5em;
            line-height: 1.5;
        }
        
        a {
            color: \$details-link !important;
            
            &:hover,
            &:focus {
                color: \$details-link-hover !important;
            }
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
}
