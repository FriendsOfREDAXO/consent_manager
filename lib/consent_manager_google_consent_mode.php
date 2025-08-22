<?php

/**
 * Google Consent Mode v2 Implementation
 * Handles domain configuration and JavaScript generation for Google Consent Mode
 */
class consent_manager_google_consent_mode
{
    /**
     * Standard Google Consent Mode v2 Flags mit GDPR-konformen Defaults
     */
    public static $defaultConsentFlags = [
        'ad_storage' => 'denied',
        'ad_user_data' => 'denied', 
        'ad_personalization' => 'denied',
        'analytics_storage' => 'denied',
        'personalization_storage' => 'denied',
        'functionality_storage' => 'granted',
        'security_storage' => 'granted'
    ];

    /**
     * Service zu Consent-Flag Mappings
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
        'microsoft-clarity' => ['analytics_storage']
    ];

    /**
     * Holt die Google Consent Mode Konfiguration für eine Domain
     * 
     * @param string $domain Die Domain
     * @return array Konfiguration mit enabled, default_state etc.
     */
    public static function getDomainConfig(string $domain): array
    {
        $sql = rex_sql::factory();
        $sql->setQuery(
            'SELECT google_consent_mode_enabled FROM ' . rex::getTable('consent_manager_domain') . ' WHERE domain = ?',
            [$domain]
        );
        
        if ($sql->getRows() > 0) {
            $enabled = (bool) $sql->getValue('google_consent_mode_enabled');
        } else {
            $enabled = false;
        }

        return [
            'enabled' => $enabled,
            'default_state' => 'denied', // GDPR-konform immer denied als Default
            'domain' => $domain,
            'flags' => self::$defaultConsentFlags
        ];
    }

    /**
     * Holt Cookie-zu-Consent Mappings für eine Sprache
     * 
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
            [$clangId]
        );
        
        foreach ($sql->getArray() as $service) {
            $uid = $service['uid'];
            
            // Suche nach bekannten Service-Mappings
            foreach (self::$serviceMappings as $serviceKey => $flags) {
                if (strpos($uid, $serviceKey) !== false || 
                    stripos($service['service_name'], str_replace('-', ' ', $serviceKey)) !== false) {
                    $mappings[$uid] = $flags;
                    break;
                }
            }
        }

        return $mappings;
    }

    /**
     * Generiert das JavaScript für Google Consent Mode v2
     * 
     * @param string $domain Die Domain
     * @param int $clangId Die Sprach-ID  
     * @return string Das generierte JavaScript
     */
    public static function generateJavaScript(string $domain, int $clangId): string
    {
        $config = self::getDomainConfig($domain);
        
        if (!$config['enabled']) {
            return '/* Google Consent Mode v2 nicht aktiviert für Domain: ' . $domain . ' */';
        }

        $mappings = self::getCookieConsentMappings($clangId);
        $defaultFlags = self::$defaultConsentFlags;

        $js = "/* Google Consent Mode v2 - Auto-generated */\n";
        $js .= "window.dataLayer = window.dataLayer || [];\n";
        $js .= "function gtag(){dataLayer.push(arguments);}\n\n";
        
        // Default consent (alles denied außer notwendige)
        $js .= "gtag('consent', 'default', " . json_encode($defaultFlags, JSON_PRETTY_PRINT) . ");\n\n";
        
        // Consent Manager Integration
        $js .= "// Integration mit Consent Manager\n";
        $js .= "document.addEventListener('consent_manager-saved', function(e) {\n";
        $js .= "    var consents = JSON.parse(e.originalEvent.detail);\n";
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
        $js .= "        console.log('Google Consent Mode updated:', updates);\n";
        $js .= "    }\n";
        $js .= "});\n\n";
        
        $js .= "console.log('Google Consent Mode v2 initialized for domain: $domain');";
        
        return $js;
    }

    /**
     * Prüft ob Google Consent Mode für eine Domain aktiviert ist
     * 
     * @param string $domain Die zu prüfende Domain
     * @return bool True wenn aktiviert
     */
    public static function isDomainEnabled(string $domain): bool
    {
        $config = self::getDomainConfig($domain);
        return $config['enabled'];
    }

    /**
     * Setzt die Google Consent Mode Aktivierung für eine Domain
     * 
     * @param string $domain Die Domain
     * @param bool $enabled Aktivierung
     * @return bool Erfolg der Operation
     */
    public static function setDomainEnabled(string $domain, bool $enabled): bool
    {
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('consent_manager_domain'));
        $sql->setWhere('domain = ?', [$domain]);
        $sql->setValue('google_consent_mode_enabled', $enabled ? 1 : 0);
        
        try {
            return $sql->update() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Holt alle verfügbaren Service-Mappings
     * 
     * @return array Service-Mappings
     */
    public static function getAllServiceMappings(): array
    {
        return self::$serviceMappings;
    }

    /**
     * Fügt ein neues Service-Mapping hinzu
     * 
     * @param string $serviceKey Service-Schlüssel
     * @param array $consentFlags Array mit Consent-Flags
     */
    public static function addServiceMapping(string $serviceKey, array $consentFlags): void
    {
        self::$serviceMappings[$serviceKey] = $consentFlags;
    }
}
