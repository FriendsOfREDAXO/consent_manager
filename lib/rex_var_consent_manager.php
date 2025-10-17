<?php

class rex_var_consent_manager extends rex_var
{
    protected function getOutput()
    {
        // forceCache Parameter (Standard: 0 im Frontend, 1 im Backend/Modul-Context)
        $forceCache = $this->hasArg('forceCache') 
            ? (int) $this->getArg('forceCache') 
            : ($this->getContext() ? 1 : 0);
        
        // forceReload Parameter (Standard: 0 wenn cache gesetzt, sonst 1 im Context)
        $forceReload = $this->hasArg('forceReload')
            ? (int) $this->getArg('forceReload')
            : ($this->getContext() && !$this->hasArg('cache') ? 1 : 0);
        
        // inline Parameter prüfen
        $inline = $this->hasArg('inline') && ($this->getArg('inline') === 'true' || $this->getArg('inline') === '1');
        
        // Fragment-Dateiname (Standard: consent_manager_box_cssjs.php)
        $fragmentFile = $this->hasArg('fragment') 
            ? $this->getArg('fragment') 
            : 'consent_manager_box_cssjs.php';
        
        if ($inline) {
            // Inline-Modus: getFragmentWithVars mit inline=true
            return 'consent_manager_frontend::getFragmentWithVars(' 
                . $forceCache . ', ' 
                . $forceReload . ', ' 
                . self::quote($fragmentFile) . ', '
                . '["inline" => true]'
                . ')';
        } else {
            // Standard-Modus: getFragment
            return 'consent_manager_frontend::getFragment(' 
                . $forceCache . ', ' 
                . $forceReload . ', ' 
                . self::quote($fragmentFile)
                . ')';
        }
    }
}
