<?php

/**
 * Google Consent Mode v2 Helper für einfachere Konfiguration
 * @api
 */
class consent_manager_google_consent_helper
{
    /**
     * Vordefinierte Service-Templates für häufig verwendete Dienste
     */
    const SERVICE_TEMPLATES = [
        'google-analytics' => [
            'name' => 'Google Analytics',
            'flags' => ['analytics_storage'],
            'description' => 'Webanalyse-Tool von Google'
        ],
        'google-ads' => [
            'name' => 'Google Ads / AdWords',
            'flags' => ['ad_storage', 'ad_user_data', 'ad_personalization'],
            'description' => 'Online-Werbeplattform von Google'
        ],
        'google-tag-manager' => [
            'name' => 'Google Tag Manager',
            'flags' => ['analytics_storage'],
            'description' => 'Tag-Management-System von Google'
        ],
        'facebook-pixel' => [
            'name' => 'Facebook Pixel',
            'flags' => ['ad_storage', 'ad_user_data', 'ad_personalization'],
            'description' => 'Tracking-Tool für Facebook-Werbung'
        ],
        'youtube' => [
            'name' => 'YouTube Videos',
            'flags' => ['functionality_storage'],
            'description' => 'Eingebettete YouTube-Videos'
        ],
        'matomo' => [
            'name' => 'Matomo Analytics',
            'flags' => ['analytics_storage'],
            'description' => 'Open-Source Webanalyse-Tool'
        ]
    ];

    /**
     * Automatische Generierung der setConsent() Scripte basierend auf Mappings
     * @param array $googleConsentMapping
     * @return array ['consent_script', 'revoke_script']
     */
    public static function generateConsentScripts($googleConsentMapping): array
    {
        if (empty($googleConsentMapping)) {
            return ['consent_script' => '', 'revoke_script' => ''];
        }

        // Consent Script (auf true setzen)
        $consentFlags = [];
        foreach ($googleConsentMapping as $flag => $enabled) {
            if ($enabled) {
                $consentFlags[] = "    {$flag}: true";
            }
        }

        $consentScript = "<script>\nsetConsent({\n" . implode(",\n", $consentFlags) . "\n});\n</script>";

        // Revoke Script (auf false setzen)
        $revokeFlags = [];
        foreach ($googleConsentMapping as $flag => $enabled) {
            if ($enabled) {
                $revokeFlags[] = "    {$flag}: false";
            }
        }

        $revokeScript = "<script>\nsetConsent({\n" . implode(",\n", $revokeFlags) . "\n});\n</script>";

        return [
            'consent_script' => $consentScript,
            'revoke_script' => $revokeScript
        ];
    }

    /**
     * Basis-Script für notwendige Speicherung (immer erlauben)
     * @return string
     */
    public static function getNecessaryStorageScript(): string
    {
        return "<script>\n// Notwendige Speicherung (immer erlaubt)\nsetConsent({\n    functionality_storage: true,\n    personalization_storage: true,\n    security_storage: true\n});\n</script>";
    }

    /**
     * Validierung der Domain-Konfiguration
     * @param string $domain
     * @return array ['valid' => bool, 'messages' => array]
     */
    public static function validateDomainConfig($domain): array
    {
        $messages = [];
        $valid = true;

        // Prüfen ob Google Consent Mode aktiviert ist
        $config = consent_manager_google_consent_mode::getDomainConfig($domain);
        if (!$config['enabled']) {
            $valid = false;
            $messages[] = 'Google Consent Mode v2 ist nicht aktiviert für Domain: ' . $domain;
        }

        // Prüfen ob Services mit Mappings vorhanden sind
        $mappings = consent_manager_google_consent_mode::getCookieConsentMappings(1);
        if (empty($mappings)) {
            $messages[] = 'Noch keine Services mit Google Consent Mode Mappings konfiguriert.';
        }

        // DSGVO-Empfehlung prüfen
        if ($config['default_state'] !== 'denied') {
            $messages[] = 'Warnung: Standard-Zustand ist nicht "denied" - empfohlen für DSGVO-Konformität.';
        }

        return ['valid' => $valid, 'messages' => $messages];
    }

    /**
     * Quick-Setup für häufig verwendete Services
     * @param string $serviceKey
     * @param string $cookieUid
     * @param int $clangId
     * @return bool
     */
    public static function quickSetupService($serviceKey, $cookieUid, $clangId = 1): bool
    {
        if (!isset(self::SERVICE_TEMPLATES[$serviceKey])) {
            return false;
        }

        $template = self::SERVICE_TEMPLATES[$serviceKey];
        
        // Google Consent Mapping erstellen
        $mapping = [];
        foreach ($template['flags'] as $flag) {
            $mapping[$flag] = true;
        }

        // Scripts generieren
        $scripts = self::generateConsentScripts($mapping);

        // Service in Datenbank speichern/aktualisieren
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('consent_manager_cookie'));
        
        // Prüfen ob Service bereits existiert
        $sql->setQuery('SELECT pid FROM ' . rex::getTable('consent_manager_cookie') . ' WHERE uid = ? AND clang_id = ?', [$cookieUid, $clangId]);
        
        $sql->setQuery('SELECT COUNT(*) AS cnt FROM ' . rex::getTable('consent_manager_cookie') . ' WHERE uid = ? AND clang_id = ?', [$cookieUid, $clangId]);
        
        if ($sql->getValue('cnt') > 0) {
            // Update bestehenden Service
            $sql->setWhere('uid = :uid AND clang_id = :clang_id', ['uid' => $cookieUid, 'clang_id' => $clangId]);
            $sql->setValue('google_consent_mapping', json_encode($mapping));
            $sql->setValue('script', $scripts['consent_script']);
            $sql->setValue('script_unselect', $scripts['revoke_script']);
            $sql->update();
        } else {
            // Neuen Service erstellen
            $sql->setValue('uid', $cookieUid);
            $sql->setValue('clang_id', $clangId);
            $sql->setValue('service_name', $template['name']);
            $sql->setValue('definition', 'name: "' . $template['name'] . '"\ndesc: "' . $template['description'] . '"');
            $sql->setValue('google_consent_mapping', json_encode($mapping));
            $sql->setValue('script', $scripts['consent_script']);
            $sql->setValue('script_unselect', $scripts['revoke_script']);
            $sql->insert();
        }

        return true;
    }

    /**
     * Debug-Informationen für Entwickler
     * @param string $domain
     * @return array
     */
    public static function getDebugInfo($domain = null): array
    {
        if (null === $domain) {
            $domain = consent_manager_util::hostname();
        }

        $config = consent_manager_google_consent_mode::getDomainConfig($domain);
        $mappings = consent_manager_google_consent_mode::getCookieConsentMappings(1);
        $validation = self::validateDomainConfig($domain);

        return [
            'domain' => $domain,
            'config' => $config,
            'mappings' => $mappings,
            'validation' => $validation,
            'javascript_size' => strlen(consent_manager_google_consent_mode::generateJavaScript($domain, 1)),
            'available_templates' => array_keys(self::SERVICE_TEMPLATES)
        ];
    }

    /**
     * Exportiert die aktuelle Konfiguration als JSON
     * @param string $domain
     * @return string
     */
    public static function exportConfiguration($domain = null): string
    {
        $debugInfo = self::getDebugInfo($domain);
        return json_encode($debugInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
