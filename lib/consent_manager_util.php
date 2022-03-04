<?php

class consent_manager_util
{

    public static function has_consent($cookieUid): bool
    {
        if (isset($_COOKIE['consent_manager'])) {
            $cookieData = json_decode($_COOKIE['consent_manager'], true);
            foreach ($cookieData['consents'] as $consent) {
                if ($cookieUid == $consent) {
                    return true;
                }
            }

        }
        return false;
    }

}