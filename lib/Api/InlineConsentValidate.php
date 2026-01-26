<?php

/**
 * API Endpoint für HMAC-Validierung von Inline Consent Content.
 *
 * @package FriendsOfRedaxo\ConsentManager
 */

namespace FriendsOfRedaxo\ConsentManager\Api;

use rex;
use rex_addon;
use rex_api_function;
use rex_request;
use rex_response;

use function base64_decode;
use function hash_equals;
use function hash_hmac;

class InlineConsentValidate extends rex_api_function
{
    protected $published = true; // Public API - Frontend needs access

    /**
     * @return rex_api_result
     */
    public function execute()
    {
        rex_response::cleanOutputBuffers();

        // Content und HMAC aus Request holen
        $encodedContent = rex_request::post('content', 'string', '');
        $providedHmac = rex_request::post('hmac', 'string', '');

        if ('' === $encodedContent || '' === $providedHmac) {
            rex_response::sendJson([
                'success' => false,
                'error' => 'Missing content or HMAC',
            ], 400);
            exit;
        }

        // HMAC-Secret aus Config laden
        $addon = rex_addon::get('consent_manager');
        $secret = $addon->getConfig('hmac_secret', '');

        if ('' === $secret) {
            rex_response::sendJson([
                'success' => false,
                'error' => 'HMAC secret not configured',
            ], 500);
            exit;
        }

        // HMAC berechnen
        $calculatedHmac = hash_hmac('sha256', $encodedContent, $secret);

        // Timing-safe Vergleich
        if (!hash_equals($calculatedHmac, $providedHmac)) {
            rex_response::sendJson([
                'success' => false,
                'error' => 'HMAC validation failed',
            ], 403);
            exit;
        }

        // Validierung erfolgreich - Content dekodieren
        $decodedContent = base64_decode($encodedContent, true);

        if (false === $decodedContent) {
            rex_response::sendJson([
                'success' => false,
                'error' => 'Invalid base64 content',
            ], 400);
            exit;
        }

        // Content zurückgeben
        rex_response::sendJson([
            'success' => true,
            'content' => $decodedContent,
        ]);
        exit;
    }
