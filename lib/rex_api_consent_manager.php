<?php

class rex_api_consent_manager extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        $domain = rex_post('domain', 'string', false);
        $consentid = rex_post('consentid', 'string', false);
        $consent_manager = false;
        if (is_string(rex_request::cookie('consent_manager'))) {
            $consent_manager = (array) json_decode(rex_request::cookie('consent_manager'), true);
        }
        if (false === $domain || false === $consentid || false === $consent_manager) {
            exit;
        }

        // Security: Validate domain format (basic hostname validation)
        // Only allow valid hostname characters
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9.-]*[a-zA-Z0-9]$/', $domain) && !preg_match('/^[a-zA-Z0-9]$/', $domain)) {
            exit;
        }

        // Security: Validate consentid format (uniqid generates alphanumeric with dots)
        // consentid format: uniqid('', true) produces something like "5f3a3e1b2c3d4.12345678"
        if (!preg_match('/^[a-f0-9.]+$/i', $consentid)) {
            exit;
        }

        // Security: Validate consents array - only allow valid UID strings
        // This prevents XSS attacks via malicious consent values
        if (!isset($consent_manager['consents']) || !is_array($consent_manager['consents'])) {
            exit;
        }

        // Security: Validate cachelogid - must be numeric or valid format
        if (!isset($consent_manager['cachelogid']) || !is_scalar($consent_manager['cachelogid'])) {
            exit;
        }
        $cachelogid = (string) $consent_manager['cachelogid'];
        // cachelogid should only contain alphanumeric characters, dots and hyphens
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $cachelogid)) {
            exit;
        }

        $validatedConsents = [];
        foreach ($consent_manager['consents'] as $consent) {
            // Only allow alphanumeric characters, hyphens and underscores (valid UIDs)
            if (is_string($consent) && preg_match('/^[a-zA-Z0-9_-]+$/', $consent)) {
                $validatedConsents[] = $consent;
            }
            // Invalid consent values are silently dropped
        }

        if ($consent_manager['consentid'] === $consentid) {
            $anonymizedIp = '';
            if (is_string(rex_request::server('REMOTE_ADDR'))) {
                $ip = rex_request::server('REMOTE_ADDR');
                if (str_contains($ip, '.')) {
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
            }
            $db = rex_sql::factory();
            $db->setTable(rex::getTable('consent_manager_consent_log'));
            // Domain in Kleinbuchstaben normalisieren beim Speichern ins Log
            $db->setValue('domain', strtolower($domain));
            $db->setValue('consentid', $consentid);
            // Use validated consents only (prevents XSS via malicious consent values)
            $db->setValue('consents', json_encode($validatedConsents));
            // Use validated cachelogid
            $db->setValue('cachelogid', $cachelogid);
            $db->setValue('ip', $anonymizedIp);
            $db->setRawValue('createdate', 'NOW()');
            $db->insert();
        }
        exit;
    }
}
