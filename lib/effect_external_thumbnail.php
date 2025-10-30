<?php

/**
 * Mediamanager Effect für externe Thumbnail-URLs
 * Lädt Thumbnails von YouTube, Vimeo etc. herunter und cached sie lokal.
 *
 * NOTE: MM-Effekte können nicht im Namespace eines Addons liegen.
 *
 * @package redaxo\consent-manager
 */
class rex_effect_external_thumbnail extends rex_effect_abstract
{
    private const SERVICES = [
        'youtube' => [
            'pattern' => '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/',
            'thumbnail_url' => 'https://img.youtube.com/vi/%s/maxresdefault.jpg',
            'fallback_url' => 'https://img.youtube.com/vi/%s/hqdefault.jpg',
        ],
        'vimeo' => [
            'pattern' => '/(?:vimeo\.com\/)([0-9]+)/',
            'thumbnail_url' => null, // Vimeo braucht API-Call
            'api_url' => 'https://vimeo.com/api/v2/video/%s.json',
        ],
    ];

    /**
     * @api
     * @return void 
     */
    public function execute()
    {
        try {
            $filename = $this->media->getMediaFilename();

            // Parameter aus Dateiname parsen: service_videoid_hash.jpg
            if (preg_match('/^([^_]+)_([^_]+)_([^\.]+)\.jpg$/', $filename, $matches)) {
                $service = $matches[1];
                $videoId = $matches[2];
            } else {
                // Fallback: Parameter aus Effect-Parametern
                $service = $this->params['service'] ?? null;
                $videoId = $this->params['video_id'] ?? null;

                if (null === $service || null === $videoId || '' === $service || '' === $videoId) {
                    $this->createFallbackImage();
                    return;
                }
            }

            if (!isset(self::SERVICES[$service])) {
                throw new rex_exception('External thumbnail effect: Unsupported service "' . $service . '"');
            }

            $thumbnailUrl = $this->getThumbnailUrl($service, $videoId);

            if (null === $thumbnailUrl || '' === $thumbnailUrl) {
                throw new rex_exception('External thumbnail effect: Could not determine thumbnail URL for service "' . $service . '" with video ID "' . $videoId . '"');
            }

            // Thumbnail herunterladen
            rex_logger::factory()->info('External thumbnail: Downloading', [
                'service' => $service,
                'video_id' => $videoId,
                'url' => $thumbnailUrl,
            ]);

            $imageData = $this->downloadThumbnail($thumbnailUrl);

            if (null === $imageData || '' === $imageData) {
                // Fallback versuchen
                if ('youtube' === $service && isset(self::SERVICES[$service]['fallback_url'])) {
                    $fallbackUrl = sprintf(self::SERVICES[$service]['fallback_url'], $videoId);
                    rex_logger::factory()->info('External thumbnail: Trying fallback', [
                        'fallback_url' => $fallbackUrl,
                    ]);
                    $imageData = $this->downloadThumbnail($fallbackUrl);
                }

                if (null === $imageData || '' === $imageData) {
                    rex_logger::factory()->error('External thumbnail: Download failed', [
                        'primary_url' => $thumbnailUrl,
                        'fallback_tried' => isset($fallbackUrl),
                    ]);
                    throw new rex_exception('External thumbnail effect: Could not download thumbnail from "' . $thumbnailUrl . '"');
                }
            }

            rex_logger::factory()->info('External thumbnail: Download successful', [
                'data_size' => strlen($imageData),
            ]);

            // Temporäre Datei erstellen
            $tempFile = rex_path::addonCache('media_manager', 'external_thumbnails/' . $filename);
            rex_dir::create(dirname($tempFile));

            if (false === file_put_contents($tempFile, $imageData)) {
                $this->createFallbackImage();
                return;
            }

            // Pfad auf die neue Datei setzen
            $this->media->setMediaPath($tempFile);

            // Aufräumen nach Request
            register_shutdown_function(static function () use ($tempFile) {
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            });
        } catch (Exception $e) {
            // Bei jedem Fehler: Fallback-Bild erstellen
            rex_logger::logException($e);
            $this->createFallbackImage();
        }
    }

    /**
     * Erstellt ein Fallback-Bild wenn das Thumbnail nicht geladen werden kann.
     */
    private function createFallbackImage(): void
    {
        // 480x360 graues Fallback-Bild mit Placeholder-Text
        $image = imagecreate(480, 360);
        $gray = imagecolorallocate($image, 200, 200, 200);
        $darkgray = imagecolorallocate($image, 100, 100, 100);

        imagefill($image, 0, 0, $gray);

        // Text hinzufügen
        $text = 'Video Thumbnail';
        imagestring($image, 3, 180, 175, $text, $darkgray);

        // Als temporäre Datei speichern
        $filename = $this->media->getMediaFilename();
        $tempFile = rex_path::addonCache('media_manager', 'external_thumbnails/' . $filename);
        rex_dir::create(dirname($tempFile));

        imagejpeg($image, $tempFile, 80);
        imagedestroy($image);

        // Pfad setzen
        $this->media->setMediaPath($tempFile);

        // Aufräumen
        register_shutdown_function(static function () use ($tempFile) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        });
    }

    /**
     * Thumbnail-URL für Service bestimmen.
     */
    private function getThumbnailUrl(string $service, string $videoId): ?string
    {
        $config = self::SERVICES[$service];

        if ('youtube' === $service) {
            return sprintf($config['thumbnail_url'], $videoId);
        }

        if ('vimeo' === $service) {
            // Vimeo API-Call
            $apiUrl = sprintf($config['api_url'], $videoId);
            $response = $this->makeHttpRequest($apiUrl);

            if (null !== $response && '' !== $response) {
                $data = json_decode($response, true);
                if (isset($data[0]['thumbnail_large'])) {
                    return $data[0]['thumbnail_large'];
                }
            }

            return null;
        }

        return null;
    }

    /**
     * Thumbnail herunterladen.
     */
    private function downloadThumbnail(string $url): ?string
    {
        return $this->makeHttpRequest($url);
    }

    /**
     * HTTP-Request durchführen.
     */
    private function makeHttpRequest(string $url): ?string
    {
        // Zuerst cURL versuchen (oft zuverlässiger)
        if (function_exists('curl_init')) {
            return $this->downloadWithCurl($url);
        }

        // Fallback zu file_get_contents
        return $this->downloadWithFileGetContents($url);
    }

    /**
     * Download mit cURL.
     */
    private function downloadWithCurl(string $url): ?string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Accept: image/webp,image/apng,image/*,*/*;q=0.8',
                'Accept-Language: de-DE,de;q=0.9,en;q=0.8',
                'Cache-Control: no-cache',
            ],
        ]);

        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if (false === $data || '' < $error) {
            rex_logger::factory()->error('External thumbnail cURL error', [
                'url' => $url,
                'error' => $error,
                'http_code' => $httpCode,
            ]);
            return null;
        }

        if (200 !== $httpCode) {
            rex_logger::factory()->error('External thumbnail HTTP error', [
                'url' => $url,
                'http_code' => $httpCode,
            ]);
            return null;
        }

        return is_string($data) && '' < $data ? $data : null;
    }

    /**
     * Download mit file_get_contents (Fallback).
     */
    private function downloadWithFileGetContents(string $url): ?string
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 30,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'follow_location' => true,
                'max_redirects' => 5,
                'ignore_errors' => true,
                'header' => [
                    'Accept: image/webp,image/apng,image/*,*/*;q=0.8',
                    'Accept-Language: de-DE,de;q=0.9,en;q=0.8',
                    'Cache-Control: no-cache',
                ],
            ],
        ]);

        try {
            $data = file_get_contents($url, false, $context);

            /**
             * @var array<int, string> $http_response_header materializes out of thin air
             */
            
            $responseCode = null;
            foreach ($http_response_header as $header) {
                if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
                    $responseCode = (int) $matches[1];
                    break;
                }
            }

            if (null !== $responseCode && 200 !== $responseCode) {
                rex_logger::factory()->error('External thumbnail HTTP error', [
                    'url' => $url,
                    'response_code' => $responseCode,
                    'headers' => $http_response_header,
                ]);
                return null;
            }

            return false !== $data && '' < $data ? $data : null;
        } catch (Exception $e) {
            rex_logger::logException($e);
            return null;
        }
    }

    /**
     * @api
     * @return string 
     */
    public function getName()
    {
        return 'External Thumbnail';
    }

    /**
     * @api
     */
    public function getParams()
    {
        return [
            [
                'label' => 'Cache TTL (Stunden)',
                'name' => 'cache_ttl',
                'type' => 'int',
                'default' => 168,
                'notice' => 'Wie lange sollen Thumbnails gecacht werden? (Standard: 168h = 1 Woche)',
            ],
        ];
    }

    /**
     * Video-ID aus URL extrahieren.
     * 
     * @api
     */
    public static function extractVideoId(string $url, string $service): ?string
    {
        if (!isset(self::SERVICES[$service])) {
            return null;
        }

        $pattern = self::SERVICES[$service]['pattern'];

        if (1 === preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Service aus URL bestimmen.
     * 
     * @api
     */
    public static function detectService(string $url): ?string
    {
        foreach (self::SERVICES as $service => $config) {
            if (preg_match($config['pattern'], $url)) {
                return $service;
            }
        }

        return null;
    }
}
