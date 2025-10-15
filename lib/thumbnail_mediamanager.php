<?php

/**
 * Erweiterte Thumbnail-Cache-Funktionalität mit Mediamanager-Integration
 * 
 * @package redaxo\consent-manager
 */
class rex_consent_manager_thumbnail_mediamanager
{
    /**
     * Generiert Thumbnail-URL über Mediamanager
     * 
     * @param string $service Service-Name (youtube, vimeo)
     * @param string $videoId Video-ID
     * @param array $options Zusätzliche Optionen
     * @return string|null Thumbnail-URL oder null bei Fehler
     */
    public static function getThumbnailUrl(string $service, string $videoId, array $options = []): ?string
    {
        // Prüfen ob Media Manager verfügbar ist
        if (!rex_addon::get('media_manager')->isAvailable()) {
            return null;
        }

        // Prüfen ob unser Mediamanager-Type existiert
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT id FROM ' . rex::getTable('media_manager_type') . ' WHERE name = ?', ['consent_manager_thumbnail']);
        
        if (!$sql->getRows()) {
            return null;
        }

        // Eindeutigen Dateinamen für Thumbnail generieren
        $filename = self::generateThumbnailFilename($service, $videoId);
        
        // Mediamanager-URL erstellen - der Effect lädt das Bild bei Bedarf
        return rex_media_manager::getUrl('consent_manager_thumbnail', $filename);
    }

    /**
     * Prüft ob Thumbnail bereits gecacht ist
     */
    public static function isThumbnailCached(string $service, string $videoId): bool
    {
        $filename = self::generateThumbnailFilename($service, $videoId);
        $cachePath = rex_path::addonCache('media_manager', 'consent_manager_thumbnail/' . $filename);
        
        if (!file_exists($cachePath)) {
            return false;
        }
        
        // Cache-TTL prüfen (Standard: 1 Woche)
        $cacheTime = filemtime($cachePath);
        $ttl = 168 * 3600; // 1 Woche in Sekunden
        
        return (time() - $cacheTime) < $ttl;
    }

    /**
     * Cache-Größe ermitteln
     */
    public static function getCacheSize(): array
    {
        $cachePath = rex_path::addonCache('media_manager', 'consent_manager_thumbnail/');
        
        if (!is_dir($cachePath)) {
            return ['files' => 0, 'size' => 0];
        }
        
        $files = glob($cachePath . '*.jpg');
        $totalSize = 0;
        
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    $totalSize += filesize($file);
                }
            }
        }
        
        return [
            'files' => count($files ?: []),
            'size' => $totalSize
        ];
    }

    /**
     * Cache bereinigen
     */
    public static function clearCache(?string $service = null): int
    {
        if ($service) {
            // Nur bestimmten Service löschen
            return rex_media_manager::deleteCache($service . '_*', 'consent_manager_thumbnail');
        } else {
            // Kompletten Thumbnail-Cache löschen
            return rex_media_manager::deleteCache(null, 'consent_manager_thumbnail');
        }
    }

    /**
     * Eindeutigen Dateinamen generieren
     */
    private static function generateThumbnailFilename(string $service, string $videoId): string
    {
        return $service . '_' . $videoId . '_' . substr(md5($service . $videoId), 0, 8) . '.jpg';
    }

    /**
     * Service aus URL erkennen
     */
    public static function detectServiceFromUrl(string $url): ?array
    {
        // YouTube
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
            return [
                'service' => 'youtube',
                'video_id' => $matches[1]
            ];
        }
        
        // Vimeo
        if (preg_match('/(?:vimeo\.com\/)([0-9]+)/', $url, $matches)) {
            return [
                'service' => 'vimeo',
                'video_id' => $matches[1]
            ];
        }
        
        return null;
    }

    /**
     * Thumbnail für Platzhalter generieren
     */
    public static function generatePlaceholderThumbnail(string $service, string $videoId, array $options = []): string
    {
        $thumbnailUrl = self::getThumbnailUrl($service, $videoId, $options);
        
        if (!$thumbnailUrl) {
            // Fallback zu direkter URL
            return self::getDirectThumbnailUrl($service, $videoId);
        }
        
        return $thumbnailUrl;
    }

    /**
     * Thumbnail-URL direkt aus Video-URL generieren
     * 
     * @param string $videoUrl YouTube oder Vimeo URL
     * @param array $options Zusätzliche Optionen
     * @return string|null Thumbnail-URL oder null bei ungültiger URL
     */
    public static function getThumbnailUrlFromVideoUrl(string $videoUrl, array $options = []): ?string
    {
        $serviceInfo = self::detectServiceFromUrl($videoUrl);
        
        if (!$serviceInfo) {
            return null;
        }
        
        return self::getThumbnailUrl($serviceInfo['service'], $serviceInfo['video_id'], $options);
    }

    /**
     * Direkte Thumbnail-URL als Fallback
     */
    private static function getDirectThumbnailUrl(string $service, string $videoId): string
    {
        switch ($service) {
            case 'youtube':
                return 'https://img.youtube.com/vi/' . $videoId . '/maxresdefault.jpg';
            case 'vimeo':
                // Vimeo braucht API-Call - Fallback zu generischem Bild
                return 'data:image/svg+xml;base64,' . base64_encode(
                    '<svg width="480" height="360" xmlns="http://www.w3.org/2000/svg">' .
                    '<rect width="100%" height="100%" fill="#1ab7ea"/>' .
                    '<text x="50%" y="50%" fill="white" text-anchor="middle" dy=".3em" font-family="Arial" font-size="24">Vimeo Video</text>' .
                    '</svg>'
                );
            default:
                return 'data:image/svg+xml;base64,' . base64_encode(
                    '<svg width="480" height="360" xmlns="http://www.w3.org/2000/svg">' .
                    '<rect width="100%" height="100%" fill="#ccc"/>' .
                    '<text x="50%" y="50%" fill="white" text-anchor="middle" dy=".3em" font-family="Arial" font-size="24">Video Thumbnail</text>' .
                    '</svg>'
                );
        }
    }
}