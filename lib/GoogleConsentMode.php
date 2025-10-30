<?php

namespace FriendsOfRedaxo\ConsentManager;

use Exception;
use rex;
use rex_sql;

use function in_array;

use const JSON_PRETTY_PRINT;

/**
 * Google Consent Mode v2 Implementation
 * Handles domain configuration and JavaScript generation for Google Consent Mode.
 */
class GoogleConsentMode
{
    /**
     * Standard Google Consent Mode v2 Flags mit GDPR-konformen Defaults
     * ALLE Services standardmäßig verweigert - erst nach expliziter Zustimmung gewährt.
     * 
     * @api
     */
    public static $defaultConsentFlags = [
        'ad_storage' => 'denied',
        'ad_user_data' => 'denied',
        'ad_personalization' => 'denied',
        'analytics_storage' => 'denied',
        'personalization_storage' => 'denied',
        'functionality_storage' => 'denied',  // Auch notwendige erst nach Consent
        'security_storage' => 'denied',        // Auch notwendige erst nach Consent
    ];

    /**
     * Service zu Consent-Flag Mappings.
     * 
     * @api
     */
    public static $serviceMappings = [
        'google-analytics' => ['analytics_storage'],
        'google-analytics-4' => ['analytics_storage'],
        'google-tag-manager' => ['analytics_storage', 'ad_storage', 'ad_user_data', 'ad_personalization'],
        'google-ads' => ['ad_storage', 'ad_user_data', 'ad_personalization'],
        'google-adwords' => ['ad_storage', 'ad_user_data', 'ad_personalization'],
        'facebook-pixel' => ['ad_storage', 'ad_user_data', 'ad_personalization'],
        'youtube' => ['ad_storage', 'personalization_storage'],
        'google-maps' => ['functionality_storage', 'personalization_storage'],
        'matomo' => ['analytics_storage'],
        'hotjar' => ['analytics_storage'],
        'microsoft-clarity' => ['analytics_storage'],
    ];

    /**
     * Holt die Google Consent Mode Konfiguration für eine Domain.
     *
     * @api
     * @param string $domain Die Domain
     * @return array konfiguration mit enabled, auto_mapping, default_state etc
     */
    public static function getDomainConfig(string $domain): array
    {
        // Domain in Kleinbuchstaben normalisieren für den Lookup
        $domain = strtolower($domain);

        $sql = rex_sql::factory();
        $sql->setQuery(
            'SELECT google_consent_mode_enabled FROM ' . rex::getTable('consent_manager_domain') . ' WHERE uid = ?',
            [$domain],
        );

        $mode = 'disabled';

        if ($sql->getRows() > 0) {
            $mode = $sql->getValue('google_consent_mode_enabled') ?? 'disabled';
        }

        return [
            'enabled' => 'disabled' !== $mode,
            'auto_mapping' => 'auto' === $mode,
            'mode' => $mode,
            'default_state' => 'denied', // GDPR-konform immer denied als Default
            'domain' => $domain,
            'flags' => self::$defaultConsentFlags,
        ];
    }

    /**
     * Holt Cookie-zu-Consent Mappings für eine Sprache.
     * 
     * @api
     * @param int $clangId Die Sprach-ID
     * @return array Array mit Service-UIDs und deren Consent-Flags
     */
    public static function getCookieConsentMappings(int $clangId): array
    {
        $mappings = [];

        // Hole alle Services/Cookies
        $sql = rex_sql::factory();
        $sql->setQuery(
            'SELECT uid, service_name FROM ' . rex::getTable('consent_manager_cookie') . ' WHERE clang_id = ?',
            [$clangId],
        );

        foreach ($sql->getArray() as $service) {
            $uid = $service['uid'];

            // Suche nach bekannten Service-Mappings
            foreach (self::$serviceMappings as $serviceKey => $flags) {
                if (str_contains($uid, $serviceKey)
                    || false !== stripos($service['service_name'], str_replace('-', ' ', $serviceKey))) {
                    $mappings[$uid] = $flags;
                    break;
                }
            }
        }

        return $mappings;
    }

    /**
     * Generiert das JavaScript für Google Consent Mode v2.
     * 
     * @api
     * @param string $domain Die Domain
     * @param int $clangId Die Sprach-ID
     * @return string Das generierte JavaScript
     */
    public static function generateJavaScript(string $domain, int $clangId): string
    {
        $config = self::getDomainConfig($domain);

        $js = "/* Google Consent Mode v2 - Auto-generated */\n";

        // Konfiguration für Debug-Zwecke exportieren
        $js .= "window.consentManagerGoogleConsentMode = {\n";
        $js .= "    getDomainConfig: function() {\n";
        $js .= '        return ' . json_encode($config, JSON_PRETTY_PRINT) . ";\n";
        $js .= "    }\n";
        $js .= "};\n\n";

        if (!$config['enabled']) {
            $js .= '/* Google Consent Mode v2 nicht aktiviert für Domain: ' . $domain . ' */';
            return $js;
        }

        $defaultFlags = self::$defaultConsentFlags;

        $js .= "window.dataLayer = window.dataLayer || [];\n";
        $js .= "function gtag(){dataLayer.push(arguments);}\n\n";

        // Default consent (alles denied)
        $js .= "gtag('consent', 'default', " . json_encode($defaultFlags, JSON_PRETTY_PRINT) . ");\n\n";

        // Auto-Mapping nur aktivieren wenn gewünscht
        if ($config['auto_mapping']) {
            $mappings = self::getCookieConsentMappings($clangId);

            // Consent Manager Integration
            $js .= "// Integration mit Consent Manager (Auto-Mapping aktiviert)\n";
            $js .= "document.addEventListener('consent_manager-saved', function(e) {\n";
            $js .= "    var consents = JSON.parse(e.detail);\n";
            $js .= "    var updates = {};\n\n";

            // Mappings für Services generieren
            foreach ($mappings as $serviceUid => $flags) {
                $js .= "    // Service: $serviceUid\n";
                $js .= "    if (consents.indexOf('$serviceUid') !== -1) {\n";
                foreach ($flags as $flag) {
                    $js .= "        updates['$flag'] = 'granted';\n";
                }
                $js .= "    }\n";
            }

            $js .= "\n    // Update consent wenn Änderungen vorhanden\n";
            $js .= "    if (Object.keys(updates).length > 0) {\n";
            $js .= "        gtag('consent', 'update', updates);\n";
            $js .= "        console.log('Google Consent Mode updated (auto-mapping):', updates);\n";
            $js .= "    }\n";
            $js .= "});\n\n";
        } else {
            $js .= "// Auto-Mapping deaktiviert - manuelles gtag('consent', 'update') in Service-Scripts erforderlich\n\n";
        }

        $js .= "console.log('Google Consent Mode v2 initialized for domain: $domain');";

        return $js;
    }

    /**
     * Prüft ob Google Consent Mode für eine Domain aktiviert ist.
     * 
     * @api
     * @param string $domain Die zu prüfende Domain
     * @return bool True wenn aktiviert
     */
    public static function isDomainEnabled(string $domain): bool
    {
        $config = self::getDomainConfig($domain);
        return $config['enabled'];
    }

    /**
     * Setzt den Google Consent Mode Status für eine Domain.
     * 
     * @api
     * @param string $domain Die Domain
     * @param string $mode Modus: 'disabled', 'auto', 'manual'
     * @return bool True bei Erfolg
     */
    public static function setDomainEnabled(string $domain, string $mode): bool
    {
        $validModes = ['disabled', 'auto', 'manual'];
        if (!in_array($mode, $validModes, true)) {
            return false;
        }

        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('consent_manager_domain'));
        $sql->setWhere(['uid' => $domain]);
        $sql->setValue('google_consent_mode_enabled', $mode);

        try {
            $sql->update();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Holt alle verfügbaren Service-Mappings.
     * 
     * @api
     * @return array Service-Mappings
     */
    public static function getAllServiceMappings(): array
    {
        return self::$serviceMappings;
    }

    /**
     * Fügt ein neues Service-Mapping hinzu.
     * 
     * @api
     * @param string $serviceKey Service-Schlüssel
     * @param array $consentFlags Array mit Consent-Flags
     */
    public static function addServiceMapping(string $serviceKey, array $consentFlags): void
    {
        self::$serviceMappings[$serviceKey] = $consentFlags;
    }
}
