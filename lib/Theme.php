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
}
