<?php

class rex_var_consent_manager extends rex_var
{
    protected function getOutput()
    {
        // Prüfe ob inline Parameter gesetzt ist
        $inline = $this->getArg('inline', false, false);
        
        if ($inline === 'true' || $inline === '1' || $inline === true) {
            // Für inline-Modus: verwende getFragmentWithVars mit inline=true
            return self::quote(
                '<?= consent_manager_frontend::getFragmentWithVars('.
                self::quote($this->getContext() ? 1 : 0).', '.
                self::quote($this->getContext() && !$this->hasArg('cache') ? 1 : 0).', '.
                self::quote('consent_manager_box_cssjs.php').', '.
                '["inline" => true]'.
                ') ?>'
            );
        } else {
            // Standard-Modus: verwende ursprüngliche getFragment Methode
            return self::quote(
                '<?= consent_manager_frontend::getFragment('.
                self::quote($this->getContext() ? 1 : 0).', '.
                self::quote($this->getContext() && !$this->hasArg('cache') ? 1 : 0).', '.
                self::quote('consent_manager_box_cssjs.php').
                ') ?>'
            );
        }
    }
}
