<?php

class rex_api_consent_manager extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        $domain = rex_post('domain', 'string', false);
        $consentid = rex_post('consentid', 'string', false);
        $consent_manager = isset($_COOKIE['consent_manager']) ? json_decode($_COOKIE['consent_manager'], 1) : false;
        if (!$domain || !$consentid || !$consent_manager) {
            exit;
        }

        // Security: Validate domain (only valid hostname characters)
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9.-]*[a-zA-Z0-9]$/', $domain)) {
            exit;
        }

        // Security: Validate consentid (should be uniqid format: hex + dots)
        if (!preg_match('/^[a-f0-9.]+$/i', $consentid)) {
            exit;
        }

        // Security: Validate cachelogid (alphanumeric with dots and hyphens)
        $cachelogid = $consent_manager['cachelogid'] ?? '';
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $cachelogid)) {
            exit;
        }

        // Security: Validate consents array (only valid cookie UIDs allowed)
        $consents = $consent_manager['consents'] ?? [];
        if (!is_array($consents)) {
            exit;
        }
        foreach ($consents as $consentUid) {
            // UIDs should only contain alphanumeric characters, hyphens, and underscores
            if (!is_string($consentUid) || !preg_match('/^[a-zA-Z0-9_-]+$/', $consentUid)) {
                exit;
            }
        }

        if ((string) $consent_manager['consentid'] == $consentid) {
            $ip = $_SERVER['REMOTE_ADDR'];
            if (false !== strpos($ip, '.')) {
                $pieces = explode('.', $ip);
                $nPieces = count($pieces);
                $pieces[$nPieces - 1] = $pieces[$nPieces - 2] = 'XXX';
                $anonymizedIp = implode('.', $pieces);
            } else {
                $pieces = explode(':', $ip);
                $nPieces = count($pieces);
                $pieces[$nPieces - 1] = $pieces[$nPieces - 2] = 'XXXX';
                $anonymizedIp = implode(':', $pieces);
            }
            $db = rex_sql::factory();
            $db->setTable(rex::getTable('consent_manager_consent_log'));
            $db->setValue('domain', $domain);
            $db->setValue('consentid', $consentid);
            $db->setValue('consents', json_encode($consents));
            $db->setValue('cachelogid', $cachelogid);
            $db->setValue('ip', $anonymizedIp);
            $db->setRawValue('createdate', 'NOW()');
            $db->insert();
        }
        exit;
    }
}
