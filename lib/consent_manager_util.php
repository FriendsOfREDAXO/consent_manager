<?php

class consent_manager_util
{

    /**
     * Check consent for cookieUid
     *
     * @param string $cookieUid
     * @return boolean
     */
    public static function has_consent($cookieUid): bool
    {
        if (null !== rex_request::cookie('consent_manager')) {
            $cookieData = (array)json_decode(strval(rex_request::cookie('consent_manager')), true);
            foreach ((array)$cookieData['consents'] as $consent) {
                if ($cookieUid === $consent) {
                    return true;
                }
            }

        }
        return false;
    }

}