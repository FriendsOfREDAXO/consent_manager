<?php

/**
 * NOTE: rex_var-Klassen können nicht im Namespace eines Addons liegen.
 */

class rex_var_cookiedb extends rex_var
{
    protected function getOutput()
    {
        $forceCache = $this->getArg('forceCache', 0, false);
        $forceReload = $this->getArg('forceReload', 0, false);
        return "\\FriendsOfRedaxo\\ConsentManager\\Frontend::getFragment($forceCache, $forceReload, 'ConsentManager/cookiedb.php')";
    }
}
