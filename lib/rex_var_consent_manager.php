<?php

class rex_var_consent_manager extends rex_var
{
    protected function getOutput()
    {
        // forceCache Parameter (Standard: 0, nur im Backend/Modul-Input-Context 1)
        $forceCache = $this->hasArg('forceCache') 
            ? (int) $this->getArg('forceCache') 
            : ($this->getContext() === 'module' && $this->environmentIs(self::ENV_INPUT) ? 1 : 0);
        
        // forceReload Parameter (Standard: 0)
        $forceReload = $this->hasArg('forceReload')
            ? (int) $this->getArg('forceReload')
            : 0;
        
        // inline Parameter prüfen
        $inline = $this->hasArg('inline') && ($this->getArg('inline') === 'true' || $this->getArg('inline') === '1');
        
        // Fragment-Dateiname (Standard: consent_manager_box_cssjs.php)
        $fragmentFile = $this->hasArg('fragment') 
            ? $this->getArg('fragment') 
            : 'consent_manager_box_cssjs.php';
        
        if ($inline) {
            // Inline-Modus: getFragmentWithVars mit inline=true
            return '\\FriendsOfRedaxo\\ConsentManager\\Frontend::getFragmentWithVars(' 
                . $forceCache . ', ' 
                . $forceReload . ', ' 
                . self::quote($fragmentFile) . ', '
                . '["inline" => true]'
                . ')';
        } else {
            // Standard-Modus: getFragment
            return '\\FriendsOfRedaxo\\ConsentManager\\Frontend::getFragment(' 
                . $forceCache . ', ' 
                . $forceReload . ', ' 
                . self::quote($fragmentFile)
                . ')';
        }
    }
}
