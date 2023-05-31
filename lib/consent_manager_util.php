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
        if (null !== rex_request::cookie('consent_manager')) {
            $cookieData = (array) json_decode(strval(rex_request::cookie('consent_manager')), true);
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
            $db->prepareQuery('SELECT count(*) as `count` FROM `' . rex::getTable('consent_manager_cookiegroup') . '` WHERE `domain` LIKE :domain AND `clang_id` = :clang');
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
        $parts = parse_url('https://' . rex_request::server('HTTP_HOST'));
        $domain = $parts['host'] ?? '';
        $validatedDomain = filter_var($domain, FILTER_VALIDATE_DOMAIN);
        if ($validatedDomain !== false) {
            return $validatedDomain;
        }
        return ''.rex_request::server('HTTP_HOST');
    }

}
