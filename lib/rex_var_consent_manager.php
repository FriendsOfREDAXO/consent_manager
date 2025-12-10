<?php

class rex_var_consent_manager extends rex_var
{
    protected function getOutput()
    {
        $forceCache = $this->getArg('forceCache', 0, false);
        $forceReload = $this->getArg('forceReload', 0, false);
        return "consent_manager_frontend::getFragment($forceCache, $forceReload, 'consent_manager_box_cssjs.php')";
    }
}
