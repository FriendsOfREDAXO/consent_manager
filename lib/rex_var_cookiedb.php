<?php

class rex_var_cookiedb extends rex_var
{
    protected function getOutput()
    {
        $debug = $this->getArg('debug', 0, false);
        $forceCache = $this->getArg('forceCache', 0, false);
        return "iwcc_frontend::getFragment($debug,$forceCache,'iwcc_cookiedb.php')";
    }
}