<?php

namespace FriendsOfRedaxo\ConsentManager;

use rex;
use rex_clang;
use rex_request;
use rex_sql;

use function count;
use function function_exists;
use function implode;
use function is_array;
use function is_string;
use function parse_url;
use function strtolower;
use function strlen;
use function trim;

class Utility
{
    /**
     * Check consent for cookieUid.
     *
     * @api
     */
    public static function has_consent(string $cookieUid): bool
    {
        if (null !== rex_request::cookie('consentmanager') && is_string(rex_request::cookie('consentmanager'))) {
            $cookieData = (array) json_decode(rex_request::cookie('consentmanager'), true);
            if (isset($cookieData['consents']) && is_array($cookieData['consents']) && 0 !== count($cookieData['consents'])) {
                foreach ($cookieData['consents'] as $consent) {
                    if ($cookieUid === $consent) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Check if consent is configured.
     *
     * @api
     */
    public static function consentConfigured(): bool
    {
        $db = rex_sql::factory();
        $db->setDebug(false);

        $hostVariants = self::getDomainVariants((string) rex_request::server('HTTP_HOST'));
        if ([] === $hostVariants) {
            return false;
        }

        $placeholders = implode(', ', array_fill(0, count($hostVariants), '?'));
        $db->setQuery(
            'SELECT `id` FROM `' . rex::getTable('consent_manager_domain') . '` WHERE `uid` IN (' . $placeholders . ') LIMIT 1',
            $hostVariants,
        );

        if (1 === $db->getRows()) {
            $domain = $db->getValue('id');
            // Check domain in cookie group
            $db->prepareQuery('SELECT count(*) as `count` FROM `' . rex::getTable('consent_manager_cookiegroup') . '` WHERE `domain` LIKE :domain AND `clang_id` = :clang AND `cookie` != \'\'');
            $dbresult = $db->execute(['domain' => '%|' . $domain . '|%', 'clang' => rex_clang::getCurrentId()]);
            if (0 !== (int) $dbresult->getValue('count')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Hostname WITH subdomain (DSGVO-konform).
     * Returns full hostname including subdomain to ensure consent is domain-specific.
     * Issue #317: Subdomain consent must be separate from main domain consent.
     *
     * @api
     */
    public static function hostname(): string
    {
        return self::extractHost((string) rex_request::server('HTTP_HOST'));
    }

    /**
     * Normalize domain to ASCII (punycode) when possible.
     *
     * @api
     */
    public static function normalizeDomain(string $domain): string
    {
        $host = self::extractHost($domain);
        if ('' === $host) {
            return '';
        }

        if (function_exists('idn_to_ascii')) {
            $idnaOptions = defined('IDNA_DEFAULT') ? IDNA_DEFAULT : 0;
            $ascii = idn_to_ascii($host, $idnaOptions);
            if (is_string($ascii) && '' !== $ascii) {
                return self::toLower($ascii);
            }
        }

        return $host;
    }

    /**
     * Returns UTF-8 and ASCII domain variants for robust matching.
     *
     * @api
     * @return array<int, string>
     */
    public static function getDomainVariants(string $domain): array
    {
        $host = self::extractHost($domain);
        if ('' === $host) {
            return [];
        }

        $variants = [$host];

        $ascii = self::normalizeDomain($host);
        if ('' !== $ascii) {
            $variants[] = $ascii;
        }

        if (function_exists('idn_to_utf8')) {
            $idnaOptions = defined('IDNA_DEFAULT') ? IDNA_DEFAULT : 0;
            $utf8FromHost = idn_to_utf8($host, $idnaOptions);
            if (is_string($utf8FromHost) && '' !== $utf8FromHost) {
                $variants[] = self::toLower($utf8FromHost);
            }

            if ('' !== $ascii) {
                $utf8FromAscii = idn_to_utf8($ascii, $idnaOptions);
                if (is_string($utf8FromAscii) && '' !== $utf8FromAscii) {
                    $variants[] = self::toLower($utf8FromAscii);
                }
            }
        }

        return array_values(array_unique(array_filter($variants, static fn ($value) => is_string($value) && '' !== $value)));
    }

    /**
     * Resolve configured domain key from UTF-8/punycode candidates.
     *
     * @param array<string, mixed> $domains
     *
     * @api
     */
    public static function resolveConfiguredDomainKey(array $domains, string $domain): string
    {
        foreach (self::getDomainVariants($domain) as $candidate) {
            if (isset($domains[$candidate])) {
                return $candidate;
            }
        }

        $normalizedCandidates = array_unique(array_map([self::class, 'normalizeDomain'], self::getDomainVariants($domain)));
        foreach (array_keys($domains) as $configuredDomain) {
            if (in_array(self::normalizeDomain((string) $configuredDomain), $normalizedCandidates, true)) {
                return (string) $configuredDomain;
            }
        }

        return '';
    }

    /**
     * Domain info from Url.
     *
     * @api
     * @return array<string, string>
     */
    public static function get_domaininfo(string $url): array
    {
        $urlinfo = parse_url($url);
        if (is_array($urlinfo) && isset($urlinfo['host'])) {
            $url = 'https://' . $urlinfo['host'];
        }

        // regex can be replaced with parse_url
        preg_match('/^(https|http|ftp):\\/\\/(.*?)\\//', "$url/", $matches);
        $parts = explode('.', $matches[2]);
        $tld = array_pop($parts);
        $host = array_pop($parts);
        if (2 === strlen($tld) && strlen($host) <= 3) {
            $tld = "$host.$tld";
            $host = array_pop($parts);
        }

        $domain = ltrim("$host.$tld", '.');
        if (null === $host) {
            $host = $tld;
        }
        return [
            'protocol' => $matches[1],
            'subdomain' => implode('.', $parts),
            'domain' => strtolower($domain), // Domain immer in Kleinbuchstaben
            'host' => strtolower($host),     // Host auch in Kleinbuchstaben
            'tld' => strtolower($tld),       // TLD auch in Kleinbuchstaben
        ];
    }

    private static function extractHost(string $domain): string
    {
        $value = trim($domain);
        if ('' === $value) {
            return '';
        }

        if (false === strpos($value, '://')) {
            $value = 'https://' . $value;
        }

        $host = (string) parse_url($value, PHP_URL_HOST);
        if ('' === $host) {
            return '';
        }

        return self::toLower(trim($host, '.'));
    }

    private static function toLower(string $value): string
    {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($value, 'UTF-8');
        }

        return strtolower($value);
    }
}
