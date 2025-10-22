<?php

/**
 * Consent Manager oEmbed Parser.
 *
 * CKE5 Integration for automatic oEmbed → Inline Blocker conversion
 * Mit optionaler Vidstack-Player Integration
 *
 * @package redaxo\consent-manager
 * @author Friends Of REDAXO
 */

namespace FriendsOfRedaxo\ConsentManager;

use Exception;
use FriendsOfRedaxo\VidStack\Video;
use rex;
use rex_addon;
use rex_clang;
use rex_extension;
use rex_extension_point;
use rex_sql;
use rex_sql_exception;

use function is_string;

class OEmbedParser
{
    /**
     * Register oEmbed parser as OUTPUT_FILTER extension.
     *
     * @param string|null $domain Optional: Nur für spezifische Domain registrieren
     */
    public static function register(?string $domain = null): void
    {
        rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) use ($domain) {
            $content = $ep->getSubject();
            if (!is_string($content)) {
                return $content;
            }

            // Domain-Check wenn spezifiziert
            if (null !== $domain && $domain !== rex::getServer()) {
                return $content;
            }

            return self::parse($content, $domain);
        }, rex_extension::LATE);
    }

    /**
     * Parse oEmbed tags and replace with Consent Manager Inline Blocker.
     *
     * @param string $content HTML content
     * @param string|null $domain Domain für Player-Konfiguration
     * @return string Processed content
     */
    public static function parse(string $content, ?string $domain = null): string
    {
        // Domain ermitteln falls nicht übergeben
        if (null === $domain && isset($_SERVER['HTTP_HOST'])) {
            $domain = $_SERVER['HTTP_HOST'];
        }

        // Domain-Config laden
        $config = self::getDomainConfig($domain);

        // Wenn oEmbed deaktiviert ist, keine Umwandlung durchführen
        if (!$config['enabled']) {
            return $content; // Inhalt unverändert zurückgeben
        }

        // Regex für <oembed url="..."></oembed>
        $pattern = '/<oembed\s+url=["\']([^"\']+)["\']\s*><\/oembed>/i';

        return preg_replace_callback($pattern, static function ($matches) use ($domain) {
            $url = $matches[1];
            return self::processOembed($url, $domain);
        }, $content);
    }

    /**
     * Process single oEmbed URL.
     *
     * @param string $url Video URL (YouTube, Vimeo)
     * @param string|null $domain Domain für Konfiguration
     * @return string HTML output (Inline Blocker)
     */
    private static function processOembed(string $url, ?string $domain): string
    {
        // Plattform erkennen
        $platform = self::detectPlatform($url);
        if (!$platform) {
            // Unbekannte Plattform - Original zurückgeben oder Fehler
            if (rex::isDebugMode()) {
                return '<!-- Consent Manager oEmbed: Unsupported platform for URL: ' . htmlspecialchars($url) . ' -->';
            }
            return '';
        }

        // Service-Key basierend auf Plattform
        $serviceKey = $platform['service'];
        $videoId = $platform['id'];

        // Prüfe ob Service in Consent Manager existiert
        if (!self::serviceExists($serviceKey)) {
            if (rex::isDebugMode()) {
                return '<div class="alert alert-warning">Consent Manager: Service "' . htmlspecialchars($serviceKey) . '" nicht konfiguriert</div>';
            }
            return '';
        }

        // Domain-spezifische Konfiguration laden
        $config = self::getDomainConfig($domain ?? rex::getServer());

        // Optionen für Inline-Blocker
        $options = [
            'title' => self::getVideoTitle($platform),
            'width' => $config['width'] ?? 640,
            'height' => $config['height'] ?? 360,
            'show_allow_all' => $config['show_allow_all'] ?? false,
            'thumbnail' => 'auto',
        ];

        // Inline-Blocker generieren
        return InlineConsent::doConsent($serviceKey, $url, $options);
    }

    /**
     * Plattform aus URL erkennen.
     *
     * @param string $url Video URL
     * @return array|null ['service' => string, 'id' => string, 'platform' => string]
     */
    private static function detectPlatform(string $url): ?array
    {
        // YouTube
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return [
                'service' => 'youtube',
                'platform' => 'youtube',
                'id' => $matches[1],
                'url' => $url,
            ];
        }

        // Vimeo
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
            return [
                'service' => 'vimeo',
                'platform' => 'vimeo',
                'id' => $matches[1],
                'url' => $url,
            ];
        }

        return null;
    }

    /**
     * Service-Existenz prüfen.
     *
     * @param string $serviceKey Service UID
     */
    private static function serviceExists(string $serviceKey): bool
    {
        try {
            $sql = rex_sql::factory();
            $sql->setQuery('
                SELECT COUNT(*) as count
                FROM ' . rex::getTable('consent_manager_cookie') . '
                WHERE uid = ? AND clang_id = ?
            ', [$serviceKey, rex_clang::getCurrentId()]);

            return (int) $sql->getValue('count') > 0;
        } catch (rex_sql_exception $e) {
            return false;
        }
    }

    /**
     * Domain-spezifische Konfiguration laden.
     *
     * @param string $domain Domain-Name
     * @return array Konfiguration
     */
    private static function getDomainConfig(string $domain): array
    {
        // Standard-Konfiguration
        $defaultConfig = [
            'enabled' => true, // Default: aktiviert
            'width' => 640,
            'height' => 360,
            'show_allow_all' => false,
        ];

        // Domain aus Datenbank laden
        try {
            $sql = rex_sql::factory();
            $sql->setQuery('
                SELECT oembed_enabled, oembed_video_width, oembed_video_height, oembed_show_allow_all
                FROM ' . rex::getTable('consent_manager_domain') . '
                WHERE uid = ?
            ', [$domain]);

            if ($sql->getRows() > 0) {
                $dbConfig = [
                    'enabled' => (bool) ($sql->getValue('oembed_enabled') ?? 1), // Default 1 wenn NULL
                    'width' => (int) $sql->getValue('oembed_video_width') ?: 640,
                    'height' => (int) $sql->getValue('oembed_video_height') ?: 360,
                    'show_allow_all' => (bool) $sql->getValue('oembed_show_allow_all'),
                ];

                return array_merge($defaultConfig, $dbConfig);
            }
        } catch (rex_sql_exception $e) {
            // Bei Fehler: Default-Config verwenden
        }

        // Fallback: Prüfe auch auf programmatisch gesetzte Konfiguration (für Kompatibilität)
        $addon = rex_addon::get('consent_manager');
        $domainConfigs = $addon->getProperty('oembed_domain_configs', []);

        if (isset($domainConfigs[$domain])) {
            return array_merge($defaultConfig, $domainConfigs[$domain]);
        }

        return $defaultConfig;
    }

    /**
     * Domain-spezifische Konfiguration setzen.
     *
     * @param string $domain Domain-Name
     * @param array $config Konfiguration
     */
    public static function setDomainConfig(string $domain, array $config): void
    {
        $addon = rex_addon::get('consent_manager');
        $domainConfigs = $addon->getProperty('oembed_domain_configs', []);
        $domainConfigs[$domain] = $config;
        $addon->setProperty('oembed_domain_configs', $domainConfigs);
    }

    /**
     * Video-Titel aus Platform-Info generieren.
     *
     * @param array $platform Platform info
     */
    private static function getVideoTitle(array $platform): string
    {
        $platformName = ucfirst($platform['platform']);
        return $platformName . ' Video';
    }

    /**
     * Vidstack-Player Embed generieren (wenn Vidstack verfügbar und gewünscht).
     *
     * @param string $url Video URL
     * @param array $options Player-Optionen
     * @return string HTML
     */
    private static function generateVidstackEmbed(string $url, array $options): string
    {
        // Prüfe ob Vidstack verfügbar ist (neue Version 1.7.2+)
        if (!class_exists('FriendsOfRedaxo\VidStack\Video')) {
            return '<!-- Vidstack Player nicht verfügbar -->';
        }

        try {
            $player = new Video($url);

            // Attribute für den Player setzen
            $attributes = [
                'crossorigin' => '',
                'playsinline' => true,
                'controls' => true,  // Vidstack Controls anzeigen
            ];

            if ($options['video_width']) {
                $attributes['width'] = $options['video_width'];
            }
            if ($options['video_height']) {
                $attributes['height'] = $options['video_height'];
            }

            $player->setAttributes($attributes);

            // Nur generate() verwenden - OHNE Vidstack Consent
            // Der Consent Manager Inline-Blocker kommt davor (via doConsent)
            return $player->generate();
        } catch (Exception $e) {
            if (rex::isDebugMode()) {
                return '<!-- Vidstack Error: ' . htmlspecialchars($e->getMessage()) . ' -->';
            }
            return '<!-- Vidstack Error -->';
        }
    }

    /**
     * Native Inline-Blocker Embed generieren.
     *
     * @param string $serviceKey Service UID
     * @param string $url Video URL
     * @param array $options Optionen
     * @return string HTML
     */
    private static function generateNativeEmbed(string $serviceKey, string $url, array $options): string
    {
        return InlineConsent::doConsent($serviceKey, $url, $options);
    }
}
