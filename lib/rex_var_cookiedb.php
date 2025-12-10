<?php

class rex_var_cookiedb extends rex_var
{
    protected function getOutput()
    {
        $forceCache = $this->getArg('forceCache', 0, false);
        $forceReload = $this->getArg('forceReload', 0, false);
        return "consent_manager_frontend::getFragment($forceCache, $forceReload, 'consent_manager_cookiedb.php')";
    }
}
