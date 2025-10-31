<?php

/**
 * Thumbnail Cache für Consent Manager
 * Cached externe Thumbnails lokal für Datenschutz.
 */

namespace FriendsOfRedaxo\ConsentManager;

use Exception;
use rex_dir;
use rex_path;
use rex_url;

use function function_exists;
use function strlen;

use const CURLINFO_HTTP_CODE;
use const CURLOPT_FOLLOWLOCATION;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_TIMEOUT;
use const CURLOPT_URL;
use const CURLOPT_USERAGENT;

class ThumbnailCache
{
    private static $cacheDir;

    /**
     * Cache-Verzeichnis ermitteln.
     */
    private static function getCacheDir()
    {
        if (null === self::$cacheDir) {
            self::$cacheDir = rex_path::addonCache('consent_manager', 'thumbnails/');
            if (!is_dir(self::$cacheDir)) {
                rex_dir::create(self::$cacheDir);
            }
        }
        return self::$cacheDir;
    }

    /**
     * YouTube Thumbnail cachen.
     *
     * @api
     */
    public static function cacheYouTubeThumbnail($videoId)
    {
        $cacheDir = self::getCacheDir();
        $cacheFile = $cacheDir . 'youtube_' . $videoId . '.jpg';
        $cacheUrl = rex_url::addonAssets('consent_manager', 'cache/thumbnails/youtube_' . $videoId . '.jpg');

        // Prüfen ob bereits gecacht
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 86400 * 7) { // 7 Tage Cache
            return $cacheUrl;
        }

        // Thumbnail URLs (Fallback-Reihenfolge)
        $thumbnailUrls = [
            "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg",
            "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg",
            "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg",
            "https://img.youtube.com/vi/{$videoId}/default.jpg",
        ];

        foreach ($thumbnailUrls as $url) {
            try {
                $imageData = self::downloadImage($url);
                if ($imageData && strlen($imageData) > 1000) { // Mindestgröße prüfen
                    file_put_contents($cacheFile, $imageData);

                    // Auch in public assets kopieren für Web-Zugriff
                    $publicCacheDir = rex_path::addonAssets('consent_manager', 'cache/thumbnails/');
                    if (!is_dir($publicCacheDir)) {
                        rex_dir::create($publicCacheDir);
                    }
                    copy($cacheFile, $publicCacheDir . 'youtube_' . $videoId . '.jpg');

                    return $cacheUrl;
                }
            } catch (Exception $e) {
                // Nächste URL probieren
                continue;
            }
        }

        // Fallback: Platzhalter-Bild
        return self::getPlaceholderImage('youtube');
    }

    /**
     * Vimeo Thumbnail cachen.
     *
     * @api
     */
    public static function cacheVimeoThumbnail($videoId)
    {
        $cacheDir = self::getCacheDir();
        $cacheFile = $cacheDir . 'vimeo_' . $videoId . '.jpg';
        $cacheUrl = rex_url::addonAssets('consent_manager', 'cache/thumbnails/vimeo_' . $videoId . '.jpg');

        // Prüfen ob bereits gecacht
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 86400 * 7) {
            return $cacheUrl;
        }

        try {
            // Vimeo API für Thumbnail
            $apiUrl = "https://vimeo.com/api/v2/video/{$videoId}.json";
            $apiResponse = self::downloadImage($apiUrl);

            if ($apiResponse) {
                $data = json_decode($apiResponse, true);
                if (isset($data[0]['thumbnail_large'])) {
                    $thumbnailUrl = $data[0]['thumbnail_large'];
                    $imageData = self::downloadImage($thumbnailUrl);

                    if ($imageData) {
                        file_put_contents($cacheFile, $imageData);

                        // In public assets kopieren
                        $publicCacheDir = rex_path::addonAssets('consent_manager', 'cache/thumbnails/');
                        if (!is_dir($publicCacheDir)) {
                            rex_dir::create($publicCacheDir);
                        }
                        copy($cacheFile, $publicCacheDir . 'vimeo_' . $videoId . '.jpg');

                        return $cacheUrl;
                    }
                }
            }
        } catch (Exception $e) {
            // Fallback verwenden
        }

        return self::getPlaceholderImage('vimeo');
    }

    /**
     * Bild herunterladen.
     */
    private static function downloadImage($url)
    {
        // cURL verwenden wenn verfügbar
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; ConsentManager/1.0)');

            $data = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (200 === $httpCode && false !== $data) {
                return $data;
            }
        }

        // Fallback: file_get_contents
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Mozilla/5.0 (compatible; ConsentManager/1.0)',
            ],
        ]);

        return @file_get_contents($url, false, $context);
    }

    /**
     * Platzhalter-Bild generieren.
     */
    private static function getPlaceholderImage($service)
    {
        $icons = [
            'youtube' => '🎥',
            'vimeo' => '🎬',
            'default' => '📺',
        ];

        $icon = $icons[$service] ?? $icons['default'];

        // SVG Platzhalter erstellen
        $svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg width="480" height="360" xmlns="http://www.w3.org/2000/svg">
    <rect width="100%" height="100%" fill="#f8f9fa"/>
    <text x="50%" y="50%" font-family="Arial, sans-serif" font-size="48" 
          text-anchor="middle" dominant-baseline="middle" fill="#6c757d">' . $icon . '</text>
    <text x="50%" y="70%" font-family="Arial, sans-serif" font-size="16" 
          text-anchor="middle" dominant-baseline="middle" fill="#6c757d">' . ucfirst($service) . '</text>
</svg>';

        $placeholderFile = self::getCacheDir() . $service . '_placeholder.svg';
        file_put_contents($placeholderFile, $svg);

        // In public assets kopieren
        $publicCacheDir = rex_path::addonAssets('consent_manager', 'cache/thumbnails/');
        if (!is_dir($publicCacheDir)) {
            rex_dir::create($publicCacheDir);
        }
        copy($placeholderFile, $publicCacheDir . $service . '_placeholder.svg');

        return rex_url::addonAssets('consent_manager', 'cache/thumbnails/' . $service . '_placeholder.svg');
    }

    /**
     * Cache aufräumen (alte Dateien löschen).
     *
     * @api
     */
    public static function cleanupCache($maxAge = 2592000) // 30 Tage
    {
        $cacheDir = self::getCacheDir();
        $publicCacheDir = rex_path::addonAssets('consent_manager', 'cache/thumbnails/');

        $dirs = [$cacheDir, $publicCacheDir];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $files = glob($dir . '*');
            foreach ($files as $file) {
                if (is_file($file) && (time() - filemtime($file)) > $maxAge) {
                    unlink($file);
                }
            }
        }
    }
}
