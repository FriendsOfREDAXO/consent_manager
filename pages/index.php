<?php

echo rex_view::title(rex_i18n::msg('consent_manager_title'));

// Einrichtungs-Checkliste und Setup-Assistent auf allen Unterseiten außer Einstellungen und Darstellung, damit der Einstieg nicht von der gewählten Seite abhängt
if (rex::requireUser()->hasPerm('consent_manager[config]')) {
    $fragment = new rex_fragment();
    if (!in_array(rex_be_controller::getCurrentPagePart(2), ['config', 'design'], true)) {
        echo $fragment->parse('ConsentManager/onboarding.php');
    }
    echo $fragment->parse('ConsentManager/setup_wizard.php');
}

rex_be_controller::includeCurrentPageSubPath();
