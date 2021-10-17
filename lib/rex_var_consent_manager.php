<?php

class rex_var_consent_manager extends rex_var
{
    protected function getOutput()
    {
        if (rex::isFrontend()) {
            if (!isset($_SESSION)) {
                rex_login::startSession();
                $_SESSION['consent_manager']['article'] = rex_article::getCurrentId();
                $_SESSION['consent_manager']['outputcss'] = '';
                $_SESSION['consent_manager']['outputjs'] = '';
                $_SESSION['consent_manager']['clang'] = rex_clang::getCurrentId();
            }
        }
        $forceCache = $this->getArg('forceCache', 0, false);
        return "consent_manager_frontend::getFragment($forceCache, 'consent_manager_box_cssjs.php')";
    }
}
