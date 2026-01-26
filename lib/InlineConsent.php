<?php

/**
 * Consent Manager Inline Consent.
 *
 * Ermöglicht Consent nur bei Bedarf für einzelne Medien/Services
 *
 * @package FriendsOfRedaxo\ConsentManager
 * @author Friends Of REDAXO
 */

namespace FriendsOfRedaxo\ConsentManager;

use rex;
use rex_fragment;
use rex_url;
use rex_view;

use function is_array;
use function strlen;

use const ENT_QUOTES;

class InlineConsent
{
    private static bool $cssOutputted = false;
    private static bool $jsOutputted = false;

    /**
     * Generiert Inline-Consent für externen Content.
     *
     * @api
     * @param string $serviceKey Service-Schlüssel aus Consent Manager
     * @param string $content Original Content (iframe, script, etc.)
     * @param array<string, mixed> $options Zusätzliche Optionen
     */
    public static function doConsent(string $serviceKey, string $content, array $options = []): string
    {
        // Service aus DB laden
        $service = self::getService($serviceKey);
        if (null === $service) {
            if (rex::isDebugMode()) {
                // TODO: Texte über .lang aufbauen
                return rex_view::warning('Consent Manager: Service "' . htmlspecialchars($serviceKey) . '" nicht gefunden');
            }
            // TODO: Texte über .lang aufbauen?
            return '<!-- Consent Manager: Service "' . $serviceKey . '" not found -->';
        }

        // Bereits zugestimmt?
        if (Utility::has_consent($serviceKey)) {
            return self::renderContent($content, $options);
        }

        // Consent ID generieren
        $consentId = uniqid('consent_', true);

        // Standard-Optionen (nur wenn nicht explizit übergeben)
        $serviceName = isset($service['service_name']) && '' < $service['service_name'] ? $service['service_name'] : ucfirst($serviceKey);
        $defaultOptions = [
            'title' => $serviceName,
            'width' => 'auto',
            'height' => 'auto',
            'thumbnail' => 'auto',
        ];

        // Optionen mergen, aber nur defaults setzen wenn nicht bereits vorhanden
        $options = array_merge($defaultOptions, $options);

        // Spezielle Handler für bekannte Services
        switch (strtolower($serviceKey)) {
            case 'youtube':
                return self::renderYouTubePlaceholder($serviceKey, $content, $options, $consentId, $service);
            case 'vimeo':
                return self::renderVimeoPlaceholder($serviceKey, $content, $options, $consentId, $service);
            case 'google-maps':
                return self::renderGoogleMapsPlaceholder($serviceKey, $content, $options, $consentId, $service);
            default:
                return self::renderGenericPlaceholder($serviceKey, $content, $options, $consentId, $service);
        }
    }

    /**
     * Service aus Datenbank laden.
     *
     * @return array<string, mixed>|null
     */
    private static function getService(string $serviceKey): ?array
    {
        return ConsentManager::getCookieData($serviceKey);
    }

    /**
     * Extrahiert die YouTube Video ID aus einer URL oder gibt die ID zurück.
     */
    private static function extractYouTubeId(string $input): ?string
    {
        if (11 === strlen($input) && (bool) preg_match('/^[a-zA-Z0-9_-]{11}$/', $input)) {
            return $input;
        }
        if (str_contains($input, 'youtube.com') || str_contains($input, 'youtu.be')) {
            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $input, $matches);
            return $matches[1] ?? null;
        }
        return null;
    }

    /**
     * Extrahiert die Vimeo Video ID aus einer URL oder gibt die ID zurück.
     */
    private static function extractVimeoId(string $input): ?string
    {
        if ((bool) preg_match('/^\d{6,}$/', $input)) {
            return $input;
        }
        if (str_contains($input, 'vimeo.com')) {
            preg_match('/vimeo\.com\/(\d+)/', $input, $matches);
            return $matches[1] ?? null;
        }
        return null;
    }

    /**
     * YouTube Platzhalter.
     *
     * @param array<string, mixed> $options
     * @param array<string, mixed> $service
     */
    private static function renderYouTubePlaceholder(string $serviceKey, string $videoId, array $options, string $consentId, array $service): string
    {
        // Video ID extrahieren falls komplette URL übergeben wurde
        $extractedId = self::extractYouTubeId($videoId);
        if (null !== $extractedId) {
            $videoId = $extractedId;
        }

        // Thumbnail über Mediamanager generieren (falls verfügbar)
        $thumbnail = $options['thumbnail'];
        if ('auto' === $thumbnail) {
            /** REVIEW: die Abfrage ist verm. überflüssig. Klassen sind verfügbar */
            if (class_exists(ThumbnailMediaManager::class)) {
                $thumbnail = ThumbnailMediaManager::getThumbnailUrl('youtube', $videoId, $options);
            } else {
                // Fallback zur direkten YouTube-URL
                $thumbnail = 'https://img.youtube.com/vi/' . $videoId . '/maxresdefault.jpg';
            }
        }

        // Build attributes string from options
        $attributesString = '';
        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $key => $value) {
                if ('' === $value) {
                    $attributesString .= ' ' . rex_escape($key);
                } else {
                    $attributesString .= ' ' . rex_escape($key) . '="' . rex_escape($value) . '"';
                }
            }
        }

        $iframe = '<iframe width="' . ($options['width'] ?? '560') . '" height="' . ($options['height'] ?? '315') . '" 
                   src="https://www.youtube.com/embed/' . rex_escape($videoId) . '" 
                   frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                   allowfullscreen' . $attributesString . '></iframe>';

        return self::renderPlaceholderHTML($serviceKey, $iframe, $options, $consentId, $service, [
            'thumbnail' => $thumbnail,
            'icon' => 'uk-icon:play-circle',
            'icon_label' => 'YouTube Video',
            'service_name' => 'YouTube',
        ]);
    }

    /**
     * Vimeo Platzhalter.
     *
     * @param array<string, mixed> $options
     * @param array<string, mixed> $service
     */
    private static function renderVimeoPlaceholder(string $serviceKey, string $videoId, array $options, string $consentId, array $service): string
    {
        // Video ID extrahieren
        $extractedId = self::extractVimeoId($videoId);
        if (null !== $extractedId) {
            $videoId = $extractedId;
        }

        // Thumbnail über Mediamanager generieren (falls verfügbar)
        $thumbnail = $options['thumbnail'];
        if ('auto' === $thumbnail) {
            /** REVIEW: die Abfrage ist verm. überflüssig. Klassen sind verfügbar */
            if (class_exists(ThumbnailMediaManager::class)) {
                $thumbnail = ThumbnailMediaManager::getThumbnailUrl('vimeo', $videoId, $options);
            } else {
                // Fallback zu generischem Vimeo-Placeholder
                $thumbnail = 'data:image/svg+xml;base64,' . base64_encode(
                    '<svg width="640" height="360" xmlns="http://www.w3.org/2000/svg">' .
                    '<rect width="100%" height="100%" fill="#1ab7ea"/>' .
                    '<text x="50%" y="50%" fill="white" text-anchor="middle" dy=".3em" font-family="Arial" font-size="24">Vimeo Video</text>' .
                    '</svg>',
                );
            }
        }

        // Build attributes string from options
        $attributesString = '';
        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $key => $value) {
                if ('' === $value) {
                    $attributesString .= ' ' . rex_escape($key);
                } else {
                    $attributesString .= ' ' . rex_escape($key) . '="' . rex_escape($value) . '"';
                }
            }
        }

        $iframe = '<iframe src="https://player.vimeo.com/video/' . rex_escape($videoId) . '" 
                   width="' . ($options['width'] ?? '640') . '" height="' . ($options['height'] ?? '360') . '" 
                   frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen' . $attributesString . '></iframe>';

        return self::renderPlaceholderHTML($serviceKey, $iframe, $options, $consentId, $service, [
            'thumbnail' => $thumbnail,
            'icon' => '🎬',
            'service_name' => 'Vimeo',
        ]);
    }

    /**
     * Google Maps Platzhalter.
     *
     * @param array<string, mixed> $options
     * @param array<string, mixed> $service
     */
    private static function renderGoogleMapsPlaceholder(string $serviceKey, string $embedUrl, array $options, string $consentId, array $service): string
    {
        $iframe = '<iframe src="' . rex_escape($embedUrl) . '" 
                   width="' . ($options['width'] ?? '100%') . '" height="' . ($options['height'] ?? '450') . '" 
                   style="border:0;" allowfullscreen="" loading="lazy"></iframe>';

        return self::renderPlaceholderHTML($serviceKey, $iframe, $options, $consentId, $service, [
            'thumbnail' => null,
            'icon' => 'uk-icon:location',
            'icon_label' => 'Map Location',
            'service_name' => 'Google Maps',
        ]);
    }

    /**
     * Generischer Platzhalter.
     *
     * @param array<string, mixed> $options
     * @param array<string, mixed> $service
     */
    private static function renderGenericPlaceholder(string $serviceKey, string $content, array $options, string $consentId, array $service): string
    {
        return self::renderPlaceholderHTML($serviceKey, $content, $options, $consentId, $service, [
            'thumbnail' => 'auto' !== $options['thumbnail'] ? $options['thumbnail'] : null,
            'icon' => 'fa fa-external-link-alt',
            'icon_label' => 'External Content',
            'service_name' => $service['service_name'],
        ]);
    }

    /**
     * Platzhalter HTML generieren.
     *
     * @param array<string, mixed> $options
     * @param array<string, mixed> $service
     * @param array<string, mixed> $placeholderData
     */
    private static function renderPlaceholderHTML(string $serviceKey, string $content, array $options, string $consentId, array $service, array $placeholderData): string
    {
        $debug = rex::isDebugMode();

        // Fragment verwenden für bessere Anpassbarkeit
        $fragment = new rex_fragment();
        $fragment->setVar('serviceKey', $serviceKey);
        $fragment->setVar('content', $content);
        $fragment->setVar('options', $options);
        $fragment->setVar('consentId', $consentId);
        $fragment->setVar('service', $service);
        $fragment->setVar('placeholderData', $placeholderData);

        if ($debug) {
            echo "<!-- DEBUG renderPlaceholderHTML: serviceKey=$serviceKey -->\n";
            echo '<!-- DEBUG options: ' . print_r($options, true) . " -->\n";
        }

        // Alle Button-Texte für Fragment hinzufügen
        // TODO: Button-Texte etc. über .lang bereitstellen
        $fragment->setVar('button_inline_details_text', self::getButtonText('button_inline_details', 'Einstellungen'));
        $fragment->setVar('inline_placeholder_text', self::getButtonText('inline_placeholder_text', 'Einmal laden'));
        $fragment->setVar('button_inline_allow_all_text', self::getButtonText('button_inline_allow_all', 'Alle erlauben'));
        $fragment->setVar('inline_action_text', self::getButtonText('inline_action_text', 'Was möchten Sie tun?'));
        $fragment->setVar('show_allow_all', $options['show_allow_all'] ?? false);
        $privacyNotice = self::getButtonText('inline_privacy_notice', 'Für die Anzeige werden Cookies benötigt.');
        $fragment->setVar('inline_privacy_notice', $privacyNotice);
        $fragment->setVar('inline_title_fallback', self::getButtonText('inline_title_fallback', 'Externes Medium'));
        $fragment->setVar('inline_privacy_link_text', self::getButtonText('inline_privacy_link_text', 'Datenschutzerklärung von'));

        if ($debug) {
            // TODO: Texte über .lang aufbauen?
            // XSS-Schutz: Debug-Werte escapen da aus DB
            echo '<!-- DEBUG inline_privacy_notice from DB: ' . htmlspecialchars($privacyNotice, ENT_QUOTES, 'UTF-8') . " -->\n";
        }

        // Icon-Konfiguration
        $fragment->setVar('privacy_icon', $options['privacy_icon'] ?? 'uk-icon:shield');

        return $fragment->parse('ConsentManager/inline_placeholder.php');
    }

    /**
     * Content direkt rendern (wenn bereits Consent vorhanden).
     *
     * @param array<string, mixed> $options
     */
    private static function renderContent(string $content, array $options): string
    {
        // Build attributes string from options
        $attributesString = '';
        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $key => $value) {
                if ('' === $value) {
                    $attributesString .= ' ' . rex_escape($key);
                } else {
                    $attributesString .= ' ' . rex_escape($key) . '="' . rex_escape($value) . '"';
                }
            }
        }

        // Für YouTube URLs oder Video-IDs
        $youtubeId = self::extractYouTubeId($content);
        if (null !== $youtubeId) {
            // TODO: Verlagerung in ein Fragment prüfen
            // Standard YouTube iframe
            $width = $options['width'] ?? '560';
            $height = $options['height'] ?? '315';
            return '<iframe width="' . $width . '" height="' . $height . '" 
                    src="https://www.youtube.com/embed/' . rex_escape($youtubeId) . '" 
                    frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen' . $attributesString . '></iframe>';
        }

        // Für Vimeo URLs oder Video-IDs
        $vimeoId = self::extractVimeoId($content);
        if (null !== $vimeoId) {
            // TODO: Verlagerung in ein Fragment prüfen
            // Standard Vimeo iframe
            $width = $options['width'] ?? '640';
            $height = $options['height'] ?? '360';
            return '<iframe src="https://player.vimeo.com/video/' . rex_escape($vimeoId) . '" 
                    width="' . $width . '" height="' . $height . '" 
                    frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen' . $attributesString . '></iframe>';
        }

        // Für Google Maps Embed URLs: In iframe umwandeln
        if (str_contains($content, 'google.com/maps/embed')) {
            // TODO: Verlagerung in ein Fragment prüfen
            return '<iframe src="' . rex_escape($content) . '" 
                    width="' . ($options['width'] ?? '100%') . '" height="' . ($options['height'] ?? '450') . '" 
                    style="border:0;" allowfullscreen="" loading="lazy"' . $attributesString . '></iframe>';
        }

        // Für andere Inhalte: Direkt zurückgeben
        return $content;
    }

    /**
     * JavaScript für Inline-Consent generieren.
     *
     * @api
     */
    public static function getJavaScript(): string
    {
        if (self::$jsOutputted) {
            // TODO: wenn schon fester Text dann englisch; oder .lang
            return '<!-- JavaScript bereits ausgegeben -->';
        }
        self::$jsOutputted = true;

        // JavaScript-Datei laden
        $jsPath = rex_url::addonAssets('consent_manager', 'consent_inline.js');
        return '<script defer src="' . $jsPath . '"></script>';
    }

    /**
     * Button-Text aus Texte-Verwaltung laden.
     */
    private static function getButtonText(string $key, string $fallback): string
    {
        $texts = ConsentManager::getTexts();
        return $texts[$key] ?? $fallback;
    }

    /**
     * CSS für Inline-Consent generieren.
     *
     * @api
     */
    public static function getCSS(): string
    {
        if (self::$cssOutputted) {
            // TODO: wenn schon fester Text dann englisch
            return '<!-- CSS bereits ausgegeben -->';
        }
        self::$cssOutputted = true;

        // CSS-Datei laden
        $cssPath = rex_url::addonAssets('consent_manager', 'consent_inline.css');
        return '<link rel="stylesheet" href="' . $cssPath . '">';
    }

    /**
     * Scannt HTML-Code und ersetzt Scripts/iframes mit data-consent-Attributen
     * durch Inline-Consent-Placeholder.
     *
     * @api
     * @param string $html HTML-Content der gescannt werden soll
     * @return string Bearbeiteter HTML-Content
     */
    public static function scanAndReplaceConsentElements(string $html): string
    {
        // Pattern für script und iframe Tags mit data-consent-block="true"
        $pattern = '/<(script|iframe|div)([^>]*data-consent-block=["\']true["\'][^>]*)>(.*?)<\/\1>/is';

        $html = preg_replace_callback($pattern, static function ($matches) {
            $tag = $matches[1]; // script, iframe oder div
            $attributes = $matches[2]; // Alle Attribute
            $content = $matches[3]; // Tag-Inhalt

            // data-consent-service extrahieren (Pflichtfeld)
            if (1 !== preg_match("/data-consent-service=[\"']([^\"'\u{a0}]+)[\"']/", $attributes, $serviceMatch)) {
                // Kein Service definiert - Element nicht ersetzen
                return $matches[0];
            }
            $serviceKey = $serviceMatch[1];

            // Optional: data-consent-provider
            $provider = '';
            if (1 === preg_match("/data-consent-provider=[\"']([^\"'\u{a0}]+)[\"']/", $attributes, $providerMatch)) {
                $provider = $providerMatch[1];
            }

            // Optional: data-consent-privacy (Datenschutz-URL)
            $privacyUrl = '';
            if (1 === preg_match("/data-consent-privacy=[\"']([^\"'\u{a0}]+)[\"']/", $attributes, $privacyMatch)) {
                $privacyUrl = $privacyMatch[1];
            }

            // Optional: data-consent-title
            $title = '' !== $provider ? $provider : ucfirst($serviceKey);
            if (1 === preg_match("/data-consent-title=[\"']([^\"'\u{a0}]+)[\"']/", $attributes, $titleMatch)) {
                $title = $titleMatch[1];
            }

            // Optional: data-consent-text (Custom Placeholder Text)
            $customText = '';
            if (1 === preg_match("/data-consent-text=[\"']([^\"'\u{a0}]+)[\"']/", $attributes, $textMatch)) {
                $customText = $textMatch[1];
            }

            // Original-Tag rekonstruieren
            $originalTag = '<' . $tag . $attributes . '>' . $content . '</' . $tag . '>';

            // Optionen zusammenstellen
            $options = [
                'title' => $title,
            ];

            if ('' !== $provider) {
                $options['provider_name'] = $provider;
            }

            if ('' !== $privacyUrl) {
                $options['privacy_url'] = $privacyUrl;
            }

            if ('' !== $customText) {
                $options['privacy_notice'] = $customText;
            }

            // Inline-Consent generieren
            return self::doConsent($serviceKey, $originalTag, $options);
        }, $html);

        return '' !== $html ? $html : '';
    }
}
