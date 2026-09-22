<?php

echo rex_view::title(rex_i18n::msg('consent_manager_title'));

// Einrichtungs-Checkliste und Setup-Assistent auf allen Unterseiten, damit der Einstieg nicht von der gewählten Seite abhängt
if (rex::getUser()->hasPerm('consent_manager[config]')) {
    $fragment = new rex_fragment();
    echo $fragment->parse('ConsentManager/onboarding.php');
    echo $fragment->parse('ConsentManager/setup_wizard.php');
}

rex_be_controller::includeCurrentPageSubPath();
