<?php

class consent_manager_util
{
    /**
     * Check consent for cookieUid.
     *
     * @param string $cookieUid
     * @api
     */
    public static function has_consent($cookieUid): bool
    {
        if (null !== rex_request::cookie('consent_manager') && is_string(rex_request::cookie('consent_manager'))) {
            $cookieData = (array) json_decode(rex_request::cookie('consent_manager'), true);
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
     * @api
     */
    public static function consentConfigured(): bool
    {
        $db = rex_sql::factory();
        $db->setDebug(false);

        // Check host
        $db->prepareQuery('SELECT `id` FROM `' . rex::getTable('consent_manager_domain') . '` WHERE `uid` = :uid');
        $dbresult = $db->execute(['uid' => rex_request::server('HTTP_HOST')]);
        if (1 === (int) $dbresult->getRows()) {
            $domain = $dbresult->getValue('id');
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
     * Hostname without subdomain and port.
     * @api
     */
    public static function hostname(): string
    {
        $dominfo = self::get_domaininfo('https://' . rex_request::server('HTTP_HOST'));
        return $dominfo['domain'];
    }

    /**
     * Daomain info from Url.
     * @return array<string, string>
     * @api
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
        if (2 === strlen($tld) && strlen($host) <= 3) { /** @phpstan-ignore-line */
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
            'domain' => $domain,
            'host' => $host,
            'tld' => $tld,
        ];
    }
}
