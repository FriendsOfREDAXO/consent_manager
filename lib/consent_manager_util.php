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
     * Hostname without subdomain and port.
     * https:// onlinecode.org/php-get-domain-name-from-full-url-with-parameters-programming/
     * @api
     */
    public static function hostname(): string
    {
        $parts = parse_url('https://' . rex_request::server('HTTP_HOST'));
        //$parts = parse_url('http://' . 'test.aesoft.de/foo/bar'); // test
        //$parts = parse_url('http://' . 'mail.onlinecode.co.uk'); // test
        $domain = $parts['host'] ?? '';
        if (false !== preg_match('/(?P<domain>[a-z0-9][a-z0-9-]{1,63}.[a-z.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }
        return ''.rex_request::server('HTTP_HOST');
    }

}
