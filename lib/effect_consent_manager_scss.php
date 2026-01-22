<?php

class rex_effect_consent_manager_scss extends rex_effect_abstract
{
    public function execute()
    {
        $filename = $this->media->getMediaFilename();
        $isDebug = rex::isDebugMode();
        
        // Security: Validate filename to prevent path traversal attacks
        if (!$this->isValidFilename($filename)) {
            $errorMsg = $isDebug 
                ? "/* Security Error: Invalid filename '" . htmlspecialchars($filename, ENT_QUOTES) . "' */" 
                : "/* Invalid theme file */";
            $this->outputCss($errorMsg, $filename, false);
            return;
        }
        
        // Determine the source file
        $source = $this->findSourceFile($filename);
        
        if (null === $source) {
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
            
            if (!is_string($css) || '' === $css) {
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
                $filemtime = filemtime($source);
                $compiledTime = false !== $filemtime ? date('Y-m-d H:i:s', $filemtime) : 'unknown';
                $debugHeader .= "/* Compiled: " . $compiledTime . " */\n";
                $debugHeader .= "/* Size: " . number_format(strlen($css)) . " bytes */\n\n";
                $css = $debugHeader . $css;
            }
            
            // Output mit Cache-Headern und ETag
            $etag = md5($css);
            $timestamp = filemtime($source);
            $lastModified = false !== $timestamp ? $timestamp : time();
            
            // GZIP-Kompression wenn Browser unterstützt
            $supportsGzip = $this->supportsGzipEncoding();
            $outputContent = $css;
            
            if ($supportsGzip) {
                $gzipped = gzencode($css, 9, FORCE_GZIP);
                if (is_string($gzipped)) {
                    $outputContent = $gzipped;
                    $this->media->setHeader('Content-Encoding', 'gzip');
                    $this->media->setHeader('Vary', 'Accept-Encoding');
                } else {
                    // Fallback: GZIP failed, use uncompressed
                    $supportsGzip = false;
                }
            }
            
            $this->media->setHeader('Content-Type', 'text/css; charset=utf-8');
            $this->media->setHeader('Content-Length', (string) strlen($outputContent));
            $this->media->setHeader('Cache-Control', 'public, max-age=31536000, immutable');
            $expiresTime = time() + 31536000;
            $this->media->setHeader('Expires', gmdate('D, d M Y H:i:s', $expiresTime) . ' GMT');
            $this->media->setHeader('ETag', '"' . $etag . '"');
            $this->media->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
            $this->media->setFormat('css');
            $this->media->setMediaFilename($cssFilename);
            
            // Write to temporary output file (mit GZIP wenn aktiviert)
            $outputFile = rex_path::addonCache('consent_manager', 'scss_output/' . $etag . ($supportsGzip ? '.css.gz' : '.css'));
            rex_dir::create(dirname($outputFile));
            file_put_contents($outputFile, $outputContent);
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
    
    /**
     * Output CSS with appropriate headers
     *
     * @param string $css The CSS content to output
     * @param string $filename The filename for the output
     * @param bool $cache Whether to send cache headers
     * @return void
     */
    private function outputCss($css, $filename, $cache = false)
    {
        $cssFilename = pathinfo($filename, PATHINFO_FILENAME) . '.css';
        $tempFile = rex_path::addonCache('consent_manager', 'scss_output/' . md5($css) . '.css');
        rex_dir::create(dirname($tempFile));
        file_put_contents($tempFile, $css);
        
        $this->media->setHeader('Content-Type', 'text/css; charset=utf-8');
        $this->media->setHeader('Content-Length', (string) strlen($css));
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
    
    /**
     * Check if the client supports GZIP encoding
     *
     * @return bool True if GZIP is supported, false otherwise
     */
    private function supportsGzipEncoding(): bool
    {
        // Check if browser accepts gzip
        $acceptEncoding = rex_request::server('HTTP_ACCEPT_ENCODING', 'string', '');
        if ('' === $acceptEncoding) {
            return false;
        }
        
        $encodings = array_map('trim', explode(',', strtolower($acceptEncoding)));
        
        // Check for gzip or x-gzip support
        // Also check ob_gzhandler exists and zlib compression is not already active
        $zlibCompression = ini_get('zlib.output_compression');
        return (in_array('gzip', $encodings, true) || in_array('x-gzip', $encodings, true))
            && function_exists('ob_gzhandler')
            && (false === $zlibCompression || '' === $zlibCompression);
    }
    
    /**
     * Validate filename to prevent path traversal and other security issues
     *
     * @param string $filename The filename to validate
     * @return bool True if valid, false otherwise
     */
    private function isValidFilename(string $filename): bool
    {
        // 1. Must end with .scss
        if (!str_ends_with($filename, '.scss')) {
            return false;
        }
        
        // 2. Must match pattern: consent_manager_(frontend|backend)[a-z0-9_]*.scss
        if (1 !== preg_match('/^consent_manager_(frontend|backend)[a-z0-9_]*\.scss$/i', $filename)) {
            return false;
        }
        
        // 3. No directory traversal characters
        if (str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
            return false;
        }
        
        // 4. No null bytes
        if (str_contains($filename, "\0")) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Find the actual source file path for a given filename
     *
     * @param string $filename The filename to search for
     * @return string|null The full path to the file, or null if not found
     */
    private function findSourceFile(string $filename): ?string
    {
        // Define allowed base directories
        $allowedDirs = [];
        
        // 1. Project themes directory
        if (rex_addon::exists('project') && rex_addon::get('project')->isAvailable()) {
            $projectDir = rex_addon::get('project')->getPath('consent_manager_themes/');
            if (is_dir($projectDir)) {
                $allowedDirs[] = $projectDir;
            }
        }
        
        // 2. Consent Manager themes directory
        $themesDir = rex_addon::get('consent_manager')->getPath('scss/themes/');
        if (is_dir($themesDir)) {
            $allowedDirs[] = $themesDir;
        }
        
        // 3. Consent Manager scss root (for base files like consent_manager_backend.scss)
        $scssDir = rex_addon::get('consent_manager')->getPath('scss/');
        if (is_dir($scssDir)) {
            $allowedDirs[] = $scssDir;
        }
        
        // Check each allowed directory
        foreach ($allowedDirs as $dir) {
            $fullPath = $dir . $filename;
            
            if (!file_exists($fullPath)) {
                continue;
            }
            
            // Security: Verify the resolved path is within the allowed directory
            // This prevents symlink attacks and other path manipulation
            $realPath = realpath($fullPath);
            $realDir = realpath($dir);
            
            if (false === $realPath || false === $realDir) {
                continue;
            }
            
            // Ensure the resolved path starts with the allowed directory
            if (!str_starts_with($realPath, $realDir)) {
                continue;
            }
            
            return $realPath;
        }
        
        return null;
    }

    public function getName()
    {
        return 'Consent Manager SCSS Compiler';
    }

    public function getParams()
    {
        return [
            // Keine Parameter erforderlich - der Effekt arbeitet automatisch
            // basierend auf dem übergebenen Dateinamen
        ];
    }

}
