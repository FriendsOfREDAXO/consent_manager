<?php

class rex_var_consent_manager extends rex_var
{
    protected function getOutput()
    {
        // Extrahiere forceCache und forceReload Parameter
        $forceCache = (int) $this->getArg('forceCache', 0, false);
        $forceReload = (int) $this->getArg('forceReload', 0, false);
        
        // Prüfe ob inline Parameter gesetzt ist
        $inline = $this->getArg('inline', false, false);
        
        if ($inline === 'true' || $inline === '1' || $inline === true) {
            // Für inline-Modus: verwende getFragmentWithVars mit inline=true
            return self::quote(
                '<?= consent_manager_frontend::getFragmentWithVars('.
                $forceCache.', '.
                $forceReload.', '.
                self::quote('consent_manager_box_cssjs.php').', '.
                '["inline" => true]'.
                ') ?>'
            );
        } else {
            // Standard-Modus: verwende ursprüngliche getFragment Methode
            return self::quote(
                '<?= consent_manager_frontend::getFragment('.
                $forceCache.', '.
                $forceReload.', '.
                self::quote('consent_manager_box_cssjs.php').
                ') ?>'
            );
        }
    }
}
