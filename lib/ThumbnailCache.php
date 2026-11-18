<?php

/**
 * Thumbnail Cache für Consent Manager
 * Cached externe Thumbnails lokal für Datenschutz.
 */

namespace FriendsOfRedaxo\ConsentManager;

use Exception;
use rex_dir;
use rex_file;
use rex_path;
use rex_url;

use function is_string;
use function strlen;


class ThumbnailCache
{
    private static ?string $cacheDir = null;

    /**
     * Cache-Verzeichnis ermitteln.
     */
    private static function getCacheDir(): string
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
    public static function cacheYouTubeThumbnail(string $videoId): string
    {
        $cacheDir = self::getCacheDir();
        $cacheFile = $cacheDir . 'youtube_' . $videoId . '.jpg';
        $cacheUrl = rex_url::addonAssets('consent_manager', 'cache/thumbnails/youtube_' . $videoId . '.jpg');

        // Prüfen ob bereits gecacht
        $fileMtime = file_exists($cacheFile) ? filemtime($cacheFile) : false;
        if (false !== $fileMtime && (time() - $fileMtime) < 86400 * 7) { // 7 Tage Cache
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
                if ('' !== $imageData && strlen($imageData) > 1000) { // Mindestgröße prüfen
                    rex_file::put($cacheFile, $imageData);

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
    public static function cacheVimeoThumbnail(string $videoId): string
    {
        $cacheDir = self::getCacheDir();
        $cacheFile = $cacheDir . 'vimeo_' . $videoId . '.jpg';
        $cacheUrl = rex_url::addonAssets('consent_manager', 'cache/thumbnails/vimeo_' . $videoId . '.jpg');

        // Prüfen ob bereits gecacht
        $fileMtime = file_exists($cacheFile) ? filemtime($cacheFile) : false;
        if (false !== $fileMtime && (time() - $fileMtime) < 86400 * 7) {
            return $cacheUrl;
        }

        try {
            // Vimeo API für Thumbnail
            $apiUrl = "https://vimeo.com/api/v2/video/{$videoId}.json";
            $apiResponse = self::downloadImage($apiUrl);

            if ('' !== $apiResponse) {
                $data = json_decode($apiResponse, true);
                if (isset($data[0]['thumbnail_large'])) {
                    $thumbnailUrl = $data[0]['thumbnail_large'];
                    $imageData = self::downloadImage($thumbnailUrl);

                    if ('' !== $imageData) {
                        rex_file::put($cacheFile, $imageData);

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
     * Bild herunterladen (rex_socket: Proxy aus der REDAXO-Konfiguration, Weiterleitungen, SSL-Prüfung).
     */
    private static function downloadImage(string $url): string
    {
        if ('' === $url) {
            return '';
        }

        return Utility::httpGet($url, 10, ['User-Agent' => 'Mozilla/5.0 (compatible; ConsentManager/1.0)']) ?? '';
    }

    /**
     * Platzhalter-Bild generieren.
     *
     * TODO: prüfen, ob man hier nicht auch auf ein Fragment setzen sollte; kein Ausgabecode im Programm selbst...
     */
    private static function getPlaceholderImage(string $service): string
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
        rex_file::put($placeholderFile, $svg);

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
     * Default: 30 Tage.
     *
     * @api
     */
    public static function cleanupCache(int $maxAge = 2592000): void
    {
        $cacheDir = self::getCacheDir();
        $publicCacheDir = rex_path::addonAssets('consent_manager', 'cache/thumbnails/');

        $dirs = [$cacheDir, $publicCacheDir];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $files = glob($dir . '*');
            if (false !== $files) {
                foreach ($files as $file) {
                    $fileMtime = filemtime($file);
                    if (is_file($file) && false !== $fileMtime && (time() - $fileMtime) > $maxAge) {
                        rex_file::delete($file);
                    }
                }
            }
        }
    }
}
