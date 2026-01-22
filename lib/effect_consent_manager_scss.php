<?php

class rex_effect_consent_manager_scss extends rex_effect_abstract
{
    public function execute()
    {
        $filename = $this->media->getMediaFilename();
        $isDebug = rex::isDebugMode();
        
        // Determine the source file
        $source = $this->findSourceFile($filename);
        
        if (!$source) {
            $debugInfo = [];
            if ($isDebug) {
                $debugInfo[] = "/* === SCSS Compiler Error === */";
                $debugInfo[] = "/* ERROR: Source file not found */";
                $debugInfo[] = "/* Requested: " . $filename . " */";
                $debugInfo[] = "/* Checked paths: */";
                
                if (rex_addon::exists('project') && rex_addon::get('project')->isAvailable()) {
                    $projectPath = rex_addon::get('project')->getPath('consent_manager_themes/' . $filename);
                    $debugInfo[] = "/*   1. Project: " . $projectPath . " [" . (file_exists($projectPath) ? 'EXISTS' : 'NOT FOUND') . "] */";
                }
                
                $themePath = rex_addon::get('consent_manager')->getPath('scss/themes/' . $filename);
                $debugInfo[] = "/*   2. Themes: " . $themePath . " [" . (file_exists($themePath) ? 'EXISTS' : 'NOT FOUND') . "] */";
                
                $basePath = rex_addon::get('consent_manager')->getPath('scss/' . $filename);
                $debugInfo[] = "/*   3. Base: " . $basePath . " [" . (file_exists($basePath) ? 'EXISTS' : 'NOT FOUND') . "] */";
            } else {
                $debugInfo[] = "/* Theme not found: " . $filename . " */";
            }
            
            $css = implode("\n", $debugInfo);
            $this->outputCss($css, $filename, false);
            return;
        }
        
        // Compile SCSS
        try {
            $cssFilename = pathinfo($filename, PATHINFO_FILENAME) . '.css';
            $cacheKey = md5($source . filemtime($source));
            $tempFile = rex_path::addonCache('consent_manager', 'scss_compiled/' . $cacheKey . '_' . $cssFilename);
            
            // Compile nur wenn Cache nicht existiert oder veraltet ist
            if (!file_exists($tempFile) || filemtime($source) > filemtime($tempFile)) {
                rex_dir::create(dirname($tempFile));
                
                $compiler = new rex_scss_compiler();
                $compiler->setScssFile($source);
                $compiler->setCssFile($tempFile);
                $compiler->compile();
            }
            
            // Read the compiled CSS
            $css = rex_file::get($tempFile);
            
            if (false === $css || '' === $css) {
                $debugInfo = [];
                if ($isDebug) {
                    $debugInfo[] = "/* === SCSS Compilation Error === */";
                    $debugInfo[] = "/* ERROR: Compilation produced empty result */";
                    $debugInfo[] = "/* Source: " . $source . " */";
                    $debugInfo[] = "/* Temp file: " . $tempFile . " */";
                } else {
                    $debugInfo[] = "/* Compilation error */";
                }
                $css = implode("\n", $debugInfo);
                $this->outputCss($css, $filename, false);
                return;
            }
            
            // Debug-Info nur im Debug-Modus
            if ($isDebug) {
                $debugHeader = "/* === SCSS Compiler Debug === */\n";
                $debugHeader .= "/* Source: " . $source . " */\n";
                $debugHeader .= "/* Compiled: " . date('Y-m-d H:i:s', filemtime($source)) . " */\n";
                $debugHeader .= "/* Size: " . number_format(strlen($css)) . " bytes */\n\n";
                $css = $debugHeader . $css;
            }
            
            // Output mit Cache-Headern und ETag
            $etag = md5($css);
            $lastModified = filemtime($source);
            
            $this->media->setHeader('Content-Type', 'text/css; charset=utf-8');
            $this->media->setHeader('Cache-Control', 'public, max-age=31536000, immutable');
            $this->media->setHeader('Expires', gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
            $this->media->setHeader('ETag', '"' . $etag . '"');
            $this->media->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
            $this->media->setFormat('css');
            $this->media->setMediaFilename($cssFilename);
            
            // Write to temporary output file
            $outputFile = rex_path::addonCache('consent_manager', 'scss_output/' . $etag . '.css');
            rex_dir::create(dirname($outputFile));
            file_put_contents($outputFile, $css);
            $this->media->setMediaPath($outputFile);
            
            // Cleanup after request
            register_shutdown_function(static function () use ($outputFile) {
                if (file_exists($outputFile)) {
                    unlink($outputFile);
                }
            });

        } catch (Exception $e) {
            $debugInfo = [];
            if ($isDebug) {
                $debugInfo[] = "/* === SCSS Compilation Exception === */";
                $debugInfo[] = "/* Message: " . $e->getMessage() . " */";
                $debugInfo[] = "/* File: " . $e->getFile() . " */";
                $debugInfo[] = "/* Line: " . $e->getLine() . " */";
            } else {
                $debugInfo[] = "/* Compilation failed */";
            }
            
            $css = implode("\n", $debugInfo);
            $this->outputCss($css, $filename, false);
        }
    }
    
    private function outputCss($css, $filename, $cache = false)
    {
        $cssFilename = pathinfo($filename, PATHINFO_FILENAME) . '.css';
        $tempFile = rex_path::addonCache('consent_manager', 'scss_output/' . md5($css) . '.css');
        rex_dir::create(dirname($tempFile));
        file_put_contents($tempFile, $css);
        
        $this->media->setHeader('Content-Type', 'text/css; charset=utf-8');
        if (!$cache) {
            $this->media->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        }
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
