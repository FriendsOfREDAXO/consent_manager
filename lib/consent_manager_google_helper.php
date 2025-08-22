<?php

/**
 * Google Consent Mode v2 Helper für REDAXO Consent Manager
 * 
 * Diese Klasse bietet Helper-Funktionen zur automatischen Generierung
 * von Google Consent Mode v2 setConsent() Aufrufen für verschiedene Dienste.
 */
class consent_manager_google_helper
{
    /**
     * Mapping von Service-Typen zu Consent Mode Flags
     */
    private static $serviceMapping = [
        'analytics' => ['analytics_storage' => true],
        'google-analytics' => ['analytics_storage' => true],
        'google-analytics-4' => ['analytics_storage' => true],
        'matomo' => ['analytics_storage' => true],
        'adwords' => [
            'ad_storage' => true,
            'ad_user_data' => true,
            'ad_personalization' => true
        ],
        'google-ads' => [
            'ad_storage' => true,
            'ad_user_data' => true,
            'ad_personalization' => true
        ],
        'google-adwords' => [
            'ad_storage' => true,
            'ad_user_data' => true,
            'ad_personalization' => true
        ],
        'facebook-pixel' => [
            'ad_storage' => true,
            'ad_user_data' => true,
            'ad_personalization' => true
        ],
        'youtube' => [
            'ad_storage' => true,
            'personalization_storage' => true
        ],
        'google-maps' => [
            'functionality_storage' => true,
            'personalization_storage' => true
        ]
    ];

    /**
     * Standard-Konfiguration für notwendige Dienste
     */
    private static $necessaryServices = [
        'functionality_storage' => true,
        'personalization_storage' => true,
        'security_storage' => true
    ];

    /**
     * Generiert automatisch setConsent() Skript für einen Service
     * 
     * @param string $serviceKey Der Service-Schlüssel
     * @param bool $granted Ob der Service erlaubt ist (true) oder nicht (false)
     * @return string Das generierte setConsent() Skript
     */
    public static function generateConsentScript($serviceKey, $granted = true)
    {
        $serviceKey = strtolower(trim($serviceKey));
        
        if (!isset(self::$serviceMapping[$serviceKey])) {
            return '';
        }

        $consentSettings = self::$serviceMapping[$serviceKey];
        
        // Alle Werte auf granted/denied setzen
        foreach ($consentSettings as $key => $value) {
            $consentSettings[$key] = $granted;
        }

        $script = '<script>' . PHP_EOL;
        $script .= 'setConsent(' . json_encode($consentSettings, JSON_PRETTY_PRINT) . ');' . PHP_EOL;
        $script .= '</script>';

        return $script;
    }

    /**
     * Generiert das Standard-Skript für notwendige Dienste
     * 
     * @return string Das generierte setConsent() Skript für notwendige Dienste
     */
    public static function generateNecessaryServicesScript()
    {
        $script = '<script>' . PHP_EOL;
        $script .= 'setConsent(' . json_encode(self::$necessaryServices, JSON_PRETTY_PRINT) . ');' . PHP_EOL;
        $script .= '</script>';

        return $script;
    }

    /**
     * Erkennt automatisch den Service-Typ basierend auf dem Service-Namen oder Schlüssel
     * 
     * @param string $serviceName Der Name oder Schlüssel des Service
     * @return string|null Der erkannte Service-Typ oder null wenn nicht erkannt
     */
    public static function detectServiceType($serviceName)
    {
        $serviceName = strtolower(trim($serviceName));
        
        // Direkte Treffer
        if (isset(self::$serviceMapping[$serviceName])) {
            return $serviceName;
        }

        // Fuzzy-Matching
        foreach (self::$serviceMapping as $type => $settings) {
            if (strpos($serviceName, str_replace('-', '', $type)) !== false ||
                strpos($serviceName, str_replace('google-', '', $type)) !== false) {
                return $type;
            }
        }

        // Spezielle Patterns
        $patterns = [
            '/analytics|ga\d?/i' => 'google-analytics',
            '/adwords|ads|google.*ad/i' => 'google-ads',
            '/facebook.*pixel|fb.*pixel/i' => 'facebook-pixel',
            '/youtube|yt/i' => 'youtube',
            '/maps|gmaps/i' => 'google-maps',
            '/matomo|piwik/i' => 'matomo'
        ];

        foreach ($patterns as $pattern => $type) {
            if (preg_match($pattern, $serviceName)) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Erstellt eine Benutzeroberfläche zur einfachen Konfiguration
     * 
     * @return array Array mit Optionen für Select-Felder
     */
    public static function getServiceOptions()
    {
        $options = [];
        
        foreach (self::$serviceMapping as $serviceType => $settings) {
            $flags = implode(', ', array_keys($settings));
            $options[$serviceType] = ucfirst(str_replace('-', ' ', $serviceType)) . ' (' . $flags . ')';
        }

        return $options;
    }

    /**
     * Generiert eine Vorschau des setConsent() Aufrufs
     * 
     * @param string $serviceType Der Service-Typ
     * @param bool $granted Ob erlaubt oder nicht
     * @return string HTML-Vorschau
     */
    public static function generatePreview($serviceType, $granted = true)
    {
        if (!isset(self::$serviceMapping[$serviceType])) {
            return '<em>Unbekannter Service-Typ</em>';
        }

        $settings = self::$serviceMapping[$serviceType];
        
        foreach ($settings as $key => $value) {
            $settings[$key] = $granted;
        }

        $preview = '<code>setConsent(' . json_encode($settings) . ');</code>';
        return $preview;
    }

    /**
     * Erweitert die verfügbaren Service-Mappings
     * 
     * @param string $serviceType Der Service-Typ
     * @param array $consentFlags Array mit Consent-Flags
     */
    public static function addServiceMapping($serviceType, $consentFlags)
    {
        self::$serviceMapping[$serviceType] = $consentFlags;
    }

    /**
     * Gibt alle verfügbaren Service-Mappings zurück
     * 
     * @return array
     */
    public static function getAllServiceMappings()
    {
        return self::$serviceMapping;
    }
}
