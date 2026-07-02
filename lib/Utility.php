<?php

namespace FriendsOfRedaxo\ConsentManager;

use rex;
use rex_clang;
use rex_request;
use rex_sql;

use function count;
use function is_array;
use function is_string;
use function ltrim;
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
        $dominfo = self::get_domaininfo('https://' . rex_request::server('HTTP_HOST'));
        // Return full hostname including subdomain (DSGVO requirement)
        if ('' < $dominfo['subdomain']) {
            return self::normalizeDomain($dominfo['subdomain'] . '.' . $dominfo['domain']);
        }
        return self::normalizeDomain($dominfo['domain']);
    }

    /**
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
        $asciiHost = self::normalizeDomain($host);
        if ('' !== $asciiHost) {
            $variants[] = $asciiHost;
        }

        if (function_exists('idn_to_utf8')) {
            $utf8Host = idn_to_utf8($host, IDNA_DEFAULT);
            if (is_string($utf8Host) && '' !== $utf8Host) {
                $variants[] = strtolower($utf8Host);
            }

            $utf8AsciiHost = idn_to_utf8($asciiHost, IDNA_DEFAULT);
            if (is_string($utf8AsciiHost) && '' !== $utf8AsciiHost) {
                $variants[] = strtolower($utf8AsciiHost);
            }
        }

        return array_values(array_unique(array_filter($variants)));
    }

    /**
     * @api
     */
    public static function normalizeDomain(string $domain): string
    {
        $host = self::extractHost($domain);
        if ('' === $host) {
            return '';
        }

        if (function_exists('idn_to_ascii')) {
            $asciiHost = idn_to_ascii($host, IDNA_DEFAULT);
            if (is_string($asciiHost) && '' !== $asciiHost) {
                return strtolower($asciiHost);
            }
        }

        return $host;
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
        $domain = trim($domain);
        if ('' === $domain) {
            return '';
        }

        $url = str_contains($domain, '://') ? $domain : 'https://' . $domain;
        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || '' === $host) {
            return '';
        }

        $host = strtolower($host);
        return rtrim($host, '.');
    }
}
