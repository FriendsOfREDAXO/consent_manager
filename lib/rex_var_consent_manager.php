<?php

/**
 * NOTE: rex_var-Klassen können nicht im Namespace eines Addons liegen.
 */

class rex_var_consent_manager extends rex_var
{
    protected function getOutput()
    {
        // forceCache Parameter (Standard: 0, nur im Backend/Modul-Input-Context 1)
        $forceCache = $this->hasArg('forceCache')
            ? (int) $this->getArg('forceCache')
            : ('module' === $this->getContext() && $this->environmentIs(self::ENV_INPUT) ? 1 : 0);

        // forceReload Parameter (Standard: 0)
        $forceReload = $this->hasArg('forceReload')
            ? (int) $this->getArg('forceReload')
            : 0;

        // inline Parameter prüfen
        $inline = $this->hasArg('inline') && ('true' === $this->getArg('inline') || '1' === $this->getArg('inline'));

        // Fragment-Dateiname (Standard: ConsentManager/box_cssjs.php)
        $fragmentFile = $this->hasArg('fragment')
            ? $this->getArg('fragment')
            : 'ConsentManager/box_cssjs.php';

        if ($inline) {
            // Inline-Modus: getFragmentWithVars mit inline=true
            return '\\FriendsOfRedaxo\\ConsentManager\\Frontend::getFragmentWithVars('
                . $forceCache . ', '
                . $forceReload . ', '
                . self::quote($fragmentFile) . ', '
                . '["inline" => true]'
                . ')';
        }
        // Standard-Modus: getFragment
        return '\\FriendsOfRedaxo\\ConsentManager\\Frontend::getFragment('
            . $forceCache . ', '
            . $forceReload . ', '
            . self::quote($fragmentFile)
            . ')';
    }
}
