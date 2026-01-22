<?php

class rex_effect_consent_manager_scss extends rex_effect_abstract
{
    public function execute()
    {
        $filename = $this->media->getMediaFilename();
        $debugInfo = [];
        $debugInfo[] = "/* === SCSS Compiler Debug Info === */";
        $debugInfo[] = "/* Requested filename: " . $filename . " */";
        
        // Determine the source file
        $source = $this->findSourceFile($filename);
        
        if (!$source) {
            $debugInfo[] = "/* ERROR: Source file not found */";
            $debugInfo[] = "/* Checked paths: */";
            
            if (rex_addon::exists('project') && rex_addon::get('project')->isAvailable()) {
                $projectPath = rex_addon::get('project')->getPath('consent_manager_themes/' . $filename);
                $debugInfo[] = "/*   1. Project: " . $projectPath . " [" . (file_exists($projectPath) ? 'EXISTS' : 'NOT FOUND') . "] */";
            }
            
            $themePath = rex_addon::get('consent_manager')->getPath('scss/themes/' . $filename);
            $debugInfo[] = "/*   2. Themes: " . $themePath . " [" . (file_exists($themePath) ? 'EXISTS' : 'NOT FOUND') . "] */";
            
            $basePath = rex_addon::get('consent_manager')->getPath('scss/' . $filename);
            $debugInfo[] = "/*   3. Base: " . $basePath . " [" . (file_exists($basePath) ? 'EXISTS' : 'NOT FOUND') . "] */";
            
            $css = implode("\n", $debugInfo);
            $this->outputDebugCss($css, $filename);
            return;
        }
        
        $debugInfo[] = "/* Source found: " . $source . " */";
        $debugInfo[] = "/* File exists: " . (file_exists($source) ? 'YES' : 'NO') . " */";
        
        // Compile SCSS
        try {
            $debugInfo[] = "/* Starting compilation... */";
            
            $cssFilename = pathinfo($filename, PATHINFO_FILENAME) . '.css';
            $tempFile = rex_path::addonCache('consent_manager', 'scss_compiled/' . $cssFilename);
            rex_dir::create(dirname($tempFile));
            
            $compiler = new rex_scss_compiler();
            $compiler->setScssFile($source);
            $compiler->setCssFile($tempFile);
            $compiler->compile();
            
            // Read the compiled CSS
            $css = rex_file::get($tempFile);
            
            if (false === $css || '' === $css) {
                $debugInfo[] = "/* ERROR: Compilation produced empty result */";
                $debugInfo[] = "/* Temp file: " . $tempFile . " */";
                $debugInfo[] = "/* File exists: " . (file_exists($tempFile) ? 'YES' : 'NO') . " */";
                if (file_exists($tempFile)) {
                    $debugInfo[] = "/* File size: " . filesize($tempFile) . " bytes */";
                }
                $css = implode("\n", $debugInfo);
                $this->outputDebugCss($css, $filename);
                return;
            }
            
            $debugInfo[] = "/* Compilation successful! */";
            $debugInfo[] = "/* CSS length: " . strlen($css) . " bytes */";
            
            // Prepend debug info
            $css = implode("\n", $debugInfo) . "\n\n" . $css;
            
            // Write final CSS with debug info
            file_put_contents($tempFile, $css);
            
            // Set headers and path
            $this->media->setHeader('Content-Type', 'text/css');
            $this->media->setFormat('css');
            $this->media->setMediaFilename($cssFilename);
            $this->media->setMediaPath($tempFile);
            
            // Cleanup after request
            register_shutdown_function(static function () use ($tempFile) {
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            });

        } catch (Exception $e) {
            $debugInfo[] = "/* ERROR: Exception during compilation */";
            $debugInfo[] = "/* Message: " . $e->getMessage() . " */";
            $debugInfo[] = "/* File: " . $e->getFile() . " */";
            $debugInfo[] = "/* Line: " . $e->getLine() . " */";
            
            $css = implode("\n", $debugInfo);
            $this->outputDebugCss($css, $filename);
        }
    }
    
    private function outputDebugCss($css, $filename)
    {
        $cssFilename = pathinfo($filename, PATHINFO_FILENAME) . '.css';
        $tempFile = rex_path::addonCache('consent_manager', 'scss_compiled/' . $cssFilename);
        rex_dir::create(dirname($tempFile));
        file_put_contents($tempFile, $css);
        
        $this->media->setHeader('Content-Type', 'text/css');
        $this->media->setFormat('css');
        $this->media->setMediaFilename($cssFilename);
        $this->media->setMediaPath($tempFile);
        
        register_shutdown_function(static function () use ($tempFile) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        });
    }
    
    private function findSourceFile($filename)
    {
        // 1. Check Project Addon
        if (rex_addon::exists('project') && rex_addon::get('project')->isAvailable()) {
            $projectPath = rex_addon::get('project')->getPath('consent_manager_themes/' . $filename);
            if (file_exists($projectPath)) {
                return $projectPath;
            }
            // Fallback for requests without extension in filename if needed
        }

        // 2. Check Consent Manager Addon (themes folder)
        $addonPath = rex_addon::get('consent_manager')->getPath('scss/themes/' . $filename);
        if (file_exists($addonPath)) {
            return $addonPath;
        }

        // 3. Fallback to scss root (for base files)
         $basePath = rex_addon::get('consent_manager')->getPath('scss/' . $filename);
         if (file_exists($basePath)) {
             return $basePath;
         }

        return null;
    }

    public function getName()
    {
        return 'Consent Manager SCSS Compiler';
    }

}
