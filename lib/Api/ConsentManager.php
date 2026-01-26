<?php

namespace FriendsOfRedaxo\ConsentManager\Api;

use rex;
use rex_api_function;
use rex_request;
use rex_sql;

use function count;
use function is_array;
use function is_scalar;
use function is_string;
use function strlen;

class ConsentManager extends rex_api_function
{
    protected $published = true;

    /**
     * @api
     */
    public function execute(): never
    {
        $domain = rex_request::post('domain', 'string', false);
        $consentid = rex_request::post('consentid', 'string', false);
        $consent_manager = false;
        if (is_string(rex_request::cookie('consentmanager'))) {
            $consent_manager = (array) json_decode(rex_request::cookie('consentmanager'), true);
        }
        if (false === $domain || false === $consentid || false === $consent_manager) {
            exit;
        }

        // Security: Validate domain format (basic hostname validation)
        // Only allow valid hostname characters and limit length (DNS max = 255)
        if (strlen($domain) > 255) {
            exit;
        }
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9.-]*[a-zA-Z0-9]$/', $domain) && !preg_match('/^[a-zA-Z0-9]$/', $domain)) {
            exit;
        }

        // Security: Validate consentid format (uniqid generates alphanumeric with dots)
        // consentid format: uniqid('', true) produces something like "5f3a3e1b2c3d4.12345678" (max 30 chars)
        if (strlen($consentid) > 30) {
            exit;
        }
        if (!preg_match('/^[a-f0-9.]+$/i', $consentid)) {
            exit;
        }

        // Security: Validate consents array - only allow valid UID strings
        // This prevents XSS attacks via malicious consent values
        if (!isset($consent_manager['consents']) || !is_array($consent_manager['consents'])) {
            exit;
        }

        // Security: Validate cachelogid - must be numeric or valid format (max 50 chars)
        if (!isset($consent_manager['cachelogid']) || !is_scalar($consent_manager['cachelogid'])) {
            exit;
        }
        $cachelogid = (string) $consent_manager['cachelogid'];
        if (strlen($cachelogid) > 50) {
            exit;
        }
        // cachelogid should only contain alphanumeric characters, dots and hyphens
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $cachelogid)) {
            exit;
        }

        $validatedConsents = [];
        foreach ($consent_manager['consents'] as $consent) {
            // Only allow alphanumeric characters, hyphens and underscores (valid UIDs)
            // Max 50 chars per UID to prevent oversized payloads
            if (is_string($consent) && strlen($consent) <= 50 && preg_match('/^[a-zA-Z0-9_-]+$/', $consent)) {
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
