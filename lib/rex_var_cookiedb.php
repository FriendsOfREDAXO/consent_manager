<?php

class rex_var_cookiedb extends rex_var
{
    protected function getOutput()
    {
        $debug = $this->getArg('debug', 0, false);
        $forceCache = $this->getArg('forceCache', 0, false);
        return "consent_manager_frontend::getFragment($debug,$forceCache,'consent_manager_cookiedb.php')";
    }
}