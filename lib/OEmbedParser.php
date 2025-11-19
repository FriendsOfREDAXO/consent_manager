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
use rex_request;
use rex_sql;
use rex_sql_exception;
use rex_view;

use function is_string;

class OEmbedParser
{
    /**
     * Register oEmbed parser as OUTPUT_FILTER extension.
     *
     * @api
     * @param string|null $domain Optional: Nur für spezifische Domain registrieren
     */
    public static function register(?string $domain = null): void
    {
        /**
         * TODO: Muss die Domain-Abfrage nicht außerhalb des Output-Filters erfolgen?
         * Begründung: "Nur für spezifische Domain registrieren" bedeutet ja grade, dass 
         * rex_extension::register nur ausgeführt wird, wenn ... 
         */
        rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) use ($domain) {
            /** @var string $content */
            $content = $ep->getSubject();

            /**
             * NOTE: nur theoretisch kann $content etwas anderes sein als string. 
             */
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
     * @api
     * @param string $content HTML content
     * @param string|null $domain Domain für Player-Konfiguration
     * @return string Processed content
     */
    public static function parse(string $content, ?string $domain = null): string
    {
        // Domain ermitteln falls nicht übergeben
        if (null === $domain) {
            $domain = rex_request::server('HTTP_HOST', 'string', $domain);
        }

        // Domain-Config laden
        $config = self::getDomainConfig($domain);

        // Wenn oEmbed deaktiviert ist, keine Umwandlung durchführen
        if (!(bool) $config['enabled']) {
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
     * @api
     * @param string $url Video URL (YouTube, Vimeo)
     * @param string|null $domain Domain für Konfiguration
     * @return string HTML output (Inline Blocker)
     */
    private static function processOembed(string $url, ?string $domain): string
    {
        // Plattform erkennen
        $platform = self::detectPlatform($url);
        if (null === $platform) {
            // Unbekannte Plattform - Original zurückgeben oder Fehler
            if (rex::isDebugMode()) {
                // TODO: Texte über .lang aufbauen
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
                // TODO: Texte über .lang aufbauen
                return rex_view::warning('Consent Manager: Service "' . htmlspecialchars($serviceKey) . '" nicht konfiguriert');
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
     * @api
     * @return array{service: string, id: string, platform: string}|null
     */
    private static function detectPlatform(string $videoUrl): ?array
    {
        // YouTube
        if ((bool) preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/', $videoUrl, $matches)) {
            return [
                'service' => 'youtube',
                'platform' => 'youtube',
                'id' => $matches[1],
                'url' => $videoUrl,
            ];
        }

        // Vimeo
        if ((bool) preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $matches)) {
            return [
                'service' => 'vimeo',
                'platform' => 'vimeo',
                'id' => $matches[1],
                'url' => $videoUrl,
            ];
        }

        return null;
    }

    /**
     * Service-Existenz prüfen (Service UID).
     *
     * @api
     */
    private static function serviceExists(string $serviceKey): bool
    {
        try {
            $sql = rex_sql::factory();
            // TODO: Query ändern in setTable/setWhere/select
            $sql->setQuery('
                SELECT COUNT(*) as count
                FROM ' . rex::getTable('consent_manager_cookie') . '
                WHERE uid = ? AND clang_id = ?
            ', [$serviceKey, rex_clang::getCurrentId()]);

            return $sql->getValue('count') > 0;
        } catch (rex_sql_exception $e) {
            return false;
        }
    }

    /**
     * Holt Domain-Konfiguration.
     *
     * @api
     * @return array<string, mixed> Konfiguration
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
            // TODO: Query ändern in setTable/setWhere/select
            $sql->setQuery('
                SELECT oembed_enabled, oembed_video_width, oembed_video_height, oembed_show_allow_all
                FROM ' . rex::getTable('consent_manager_domain') . '
                WHERE uid = ?
            ', [$domain]);

            if ($sql->getRows() > 0) {
                $dbConfig = [
                    'enabled' => (bool) ($sql->getValue('oembed_enabled') ?? 1), // Default 1 wenn NULL
                    'width' => (int) ($sql->getValue('oembed_video_width') ?? 640),
                    'height' => (int) ($sql->getValue('oembed_video_height') ?? 360),
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
     * @api
     * @param array<string, mixed> $config Konfiguration
     */
    public static function setDomainConfig(string $domain, array $config): void
    {
        $addon = rex_addon::get('consent_manager');
        $domainConfigs = $addon->getProperty('oembed_domain_configs', []);
        $domainConfigs[$domain] = $config;
        $addon->setProperty('oembed_domain_configs', $domainConfigs);
    }

    /**
     * Video-Titel für Platzhalter generieren.
     *
     * @param array<string, mixed> $platform Platform info
     */
    private static function getVideoTitle(array $platform): string
    {
        $platformName = ucfirst($platform['platform']);
        return $platformName . ' Video';
    }

    /**
     * Vidstack-Player Embed generieren (wenn Vidstack verfügbar und gewünscht).
     *
     * @api
     * @param array<string, mixed> $options Player-Optionen
     * @phpstan-ignore-next-line method.unused (Reserved for future Vidstack integration)
     */
    private static function generateVidstackEmbed(string $videoUrl, array $options): string
    {
        // Prüfe ob Vidstack Addon verfügbar ist
        if (!rex_addon::exists('vidstack') || !rex_addon::get('vidstack')->isAvailable()) {
            // TODO: Texte über .lang aufbauen
            return '<!-- Vidstack Player nicht verfügbar -->';
        }

        try {
            $player = new Video($videoUrl);

            // Attribute für den Player setzen
            $attributes = [
                'crossorigin' => '',
                'playsinline' => true,
                'controls' => true,  // Vidstack Controls anzeigen
            ];

            if (isset($options['video_width']) && '' !== (string) $options['video_width']) {
                $attributes['width'] = $options['video_width'];
            }
            if (isset($options['video_height']) && '' !== (string) $options['video_height']) {
                $attributes['height'] = $options['video_height'];
            }

            $player->setAttributes($attributes);

            // Nur generate() verwenden - OHNE Vidstack Consent
            // Der Consent Manager Inline-Blocker kommt davor (via doConsent)
            return $player->generate();
        } catch (Exception $e) {
            if (rex::isDebugMode()) {
                // TODO: Texte über .lang aufbauen
                return '<!-- Vidstack Error: ' . htmlspecialchars($e->getMessage()) . ' -->';
            }
                // TODO: Texte über .lang aufbauen
            return '<!-- Vidstack Error -->';
        }
    }

    /**
     * Native Inline-Blocker Embed generieren.
     *
     * @api
     * @param array<string, mixed> $options Optionen
     * @return string HTML
     * @phpstan-ignore-next-line method.unused (Reserved for future native embed integration)
     */
    private static function generateNativeEmbed(string $serviceKey, string $videoUrl, array $options): string
    {
        return InlineConsent::doConsent($serviceKey, $videoUrl, $options);
    }
}
