<?php

/**
 * @api
 */

class consent_manager_theme
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
     * @return string|false
     */
    public function getCompiledStyle(string $theme = '')
    {
        if ('' === $theme) {
            $theme = $this->getTheme();
        }

        if (0 === strpos($theme, 'project:')) {
            $addon = rex_addon::get('project');
            if (!file_exists($addon->getPath('consent_manager_themes/' . str_replace('project:', '', $theme)))) {
                return false;
            }

            $themefile = str_replace('project:', '', $theme);
            $tempfile = rex_path::addonCache('consent_manager', $themefile . '_preview.css');
            $this->compileScss($addon->getPath('consent_manager_themes/' . $themefile), $tempfile);
            $css = trim(strval(rex_file::get($tempfile)));
            rex_file::delete($tempfile);
        } else {
            $addon = rex_addon::get('consent_manager');
            if (!file_exists($addon->getPath('scss/' . $theme))) {
                return false;
            }
            $themefile = $theme;
            $tempfile = rex_path::addonCache('consent_manager', $theme . '_preview.css');
            $this->compileScss($addon->getPath('scss/' . $themefile), $tempfile);
            $css = trim(strval(rex_file::get($tempfile)));
            rex_file::delete($tempfile);
        }

        return $css;
    }

    /**
     * Compile addon default assets (backend+frontend).
     */
    public static function generateDefaultAssets(): void
    {
        $addon = rex_addon::get('consent_manager');

        $cmtheme = new self();
        $cmtheme->compileScss($addon->getPath('scss/consent_manager_backend.scss'), $addon->getPath('assets/consent_manager_backend.css'));
        $cmtheme->compileScss($addon->getPath('scss/consent_manager_frontend.scss'), $addon->getPath('assets/consent_manager_frontend.css'));
    }

    /**
     * Copy assets to assets-Direcotry.
     */
    public static function copyAllAssets(): void
    {
        $addon = rex_addon::get('consent_manager');
        rex_dir::copy($addon->getPath('assets'), $addon->getAssetsPath());
    }

    /**
     * Compile theme assets.
     */
    public static function generateThemeAssets(string $theme): void
    {
        if (0 === strpos($theme, 'project:')) {
            $addon = rex_addon::get('project');
            $source = $addon->getPath('consent_manager_themes/' . str_replace('project:', '', $theme));
            $addon = rex_addon::get('consent_manager');
            $dest = $addon->getPath('assets/' . str_replace('project:', 'project_', str_replace('.scss', '.css', $theme)));
        } else {
            $addon = rex_addon::get('consent_manager');
            $source = $addon->getPath('scss/' . $theme);
            $dest = $addon->getPath('assets/' . str_replace('.scss', '.css', $theme));
        }
        $cmtheme = new self();
        $cmtheme->compileScss($source, $dest);
    }

    /**
     * Get theme info from file.
     * @return array<string, string>
     */
    public function getThemeInformation(string $theme = '')
    {
        if ('' === $theme) {
            $theme = $this->getTheme();
        }
        if (0 === strpos($theme, 'project:')) {
            $addon = rex_addon::get('project');
            $themefile = rex_file::get($addon->getPath('consent_manager_themes/' . str_replace('project:', '', $theme)));
        } else {
            $addon = rex_addon::get('consent_manager');
            $themefile = rex_file::get($addon->getPath('scss/' . $theme));
        }
        $lines = explode("\n", (string) $themefile);

        $json = '';
        foreach ($lines as $line) {
            if (false !== strstr($line, 'Theme: ')) {
                $json = trim(str_replace('Theme: ', '', $line));
            }
        }
        $themeinfo = (array) json_decode($json, true);
        return $themeinfo; /** @phpstan-ignore-line */
    }
}
