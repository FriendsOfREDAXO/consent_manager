<?php

/**
 * Erweiterte Thumbnail-Cache-Funktionalität mit Mediamanager-Integration.
 *
 * @package redaxo\consent-manager
 */

namespace FriendsOfRedaxo\ConsentManager;

use rex;
use rex_addon;
use rex_media_manager;
use rex_path;
use rex_sql;

use function count;
use function is_array;

class ThumbnailMediaManager
{
    /**
     * Thumbnail-URL über Media Manager generieren.
     *
     * @api
     * @param string $service Service-Name (youtube, vimeo)
     * @param string $videoId Video-ID
     * @param array<string, mixed> $options Zusätzliche Optionen
     * @return string|null Thumbnail-URL oder null bei Fehler
     *
     * TODO: prüfen, warum hier $options vorkommt. Der Parameter wird im Code nicht benutzt.
     */
    public static function getThumbnailUrl(string $service, string $videoId, array $options = []): ?string
    {
        // Prüfen ob Media Manager verfügbar ist
        if (!rex_addon::get('media_manager')->isAvailable()) {
            return null;
        }

        // Prüfen ob unser Mediamanager-Type existiert
        $sql = rex_sql::factory();
        // TODO: Query in setTable/setWhere/select ändern
        $sql->setQuery('SELECT id FROM ' . rex::getTable('media_manager_type') . ' WHERE name = ?', ['consent_manager_thumbnail']);

        if (0 === $sql->getRows()) {
            // Fallback zu direkter URL wenn kein Media Manager Type vorhanden
            return self::getDirectThumbnailUrl($service, $videoId);
        }

        // Eindeutigen Dateinamen für Thumbnail generieren
        $filename = self::generateThumbnailFilename($service, $videoId);

        // Mediamanager-URL erstellen - der Effect lädt das Bild bei Bedarf
        return rex_media_manager::getUrl('consent_manager_thumbnail', $filename);
    }

    /**
     * Prüft ob Thumbnail bereits gecacht ist.
     *
     * @api
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

        return false !== $cacheTime && (time() - $cacheTime) < $ttl;
    }

    /**
     * Cache-Größe ermitteln.
     *
     * @api
     * @return array{files: int, size: int}
     */
    public static function getCacheSize(): array
    {
        $cachePath = rex_path::addonCache('media_manager', 'consent_manager_thumbnail/');

        if (!is_dir($cachePath)) {
            return ['files' => 0, 'size' => 0];
        }

        $files = glob($cachePath . '*.jpg');
        $files = false === $files ? [] : $files;
        $totalSize = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                $fileSize = filesize($file);
                if (false !== $fileSize) {
                    $totalSize += $fileSize;
                }
            }
        }

        return [
            'files' => count($files),
            'size' => $totalSize,
        ];
    }

    /**
     * Cache bereinigen.
     *
     * @api
     */
    public static function clearCache(?string $service = null): int
    {
        if (null !== $service && '' !== $service) {
            // Nur bestimmten Service löschen
            return rex_media_manager::deleteCache($service . '_*', 'consent_manager_thumbnail');
        }
        // Kompletten Thumbnail-Cache löschen
        return rex_media_manager::deleteCache(null, 'consent_manager_thumbnail');
    }

    /**
     * Eindeutigen Dateinamen generieren.
     */
    private static function generateThumbnailFilename(string $service, string $videoId): string
    {
        return $service . '_' . $videoId . '_' . substr(md5($service . $videoId), 0, 8) . '.jpg';
    }

    /**
     * Service aus URL erkennen.
     *
     * @api
     * @return array{service: string, video_id: string}|null
     */
    public static function detectServiceFromUrl(string $url): ?array
    {
        // YouTube
        if ((bool) preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
            return [
                'service' => 'youtube',
                'video_id' => $matches[1],
            ];
        }

        // Vimeo
        if ((bool) preg_match('/(?:vimeo\.com\/)([0-9]+)/', $url, $matches)) {
            return [
                'service' => 'vimeo',
                'video_id' => $matches[1],
            ];
        }

        return null;
    }

    /**
     * Thumbnail für Platzhalter generieren.
     *
     * @api
     * @param array<string, mixed> $options
     */
    public static function generatePlaceholderThumbnail(string $service, string $videoId, array $options = []): string
    {
        $thumbnailUrl = self::getThumbnailUrl($service, $videoId, $options);

        if (null === $thumbnailUrl || '' === $thumbnailUrl) {
            // Fallback zu direkter URL
            return self::getDirectThumbnailUrl($service, $videoId);
        }

        return $thumbnailUrl;
    }

    /**
     * Thumbnail-URL aus Video-URL (YouTube oder Vimeo) generieren.
     * NULL bei ungültiger URL.
     *
     * @api
     * @param array<string, mixed> $options Zusätzliche Optionen
     */
    public static function getThumbnailUrlFromVideoUrl(string $videoUrl, array $options = []): ?string
    {
        $serviceInfo = self::detectServiceFromUrl($videoUrl);

        if (!is_array($serviceInfo)) {
            return null;
        }

        return self::getThumbnailUrl($serviceInfo['service'], $serviceInfo['video_id'], $options);
    }

    /**
     * Direkte Thumbnail-URL als Fallback.
     *
     * TODO: prüfen, ob nicht ein Fragment für die SVGs besser wäre
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
                    '</svg>',
                );
            default:
                return 'data:image/svg+xml;base64,' . base64_encode(
                    '<svg width="480" height="360" xmlns="http://www.w3.org/2000/svg">' .
                    '<rect width="100%" height="100%" fill="#ccc"/>' .
                    '<text x="50%" y="50%" fill="white" text-anchor="middle" dy=".3em" font-family="Arial" font-size="24">Video Thumbnail</text>' .
                    '</svg>',
                );
        }
    }
}
