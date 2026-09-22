<?php
/**
 * Gemeinsamer Abschluss der Einstellungs-Unterseiten: Formular ausgeben und nach dem Speichern Cache und Theme-Assets erneuern.
 *
 * Erwartet: rex_config_form $form, string $title
 */

use FriendsOfRedaxo\ConsentManager\Cache;
use FriendsOfRedaxo\ConsentManager\Theme;

/** @var rex_config_form $form */
/** @var string $title */

if (rex_request::get('applied', 'bool', false)) {
    echo rex_view::success(rex_i18n::msg('form_applied'));
}

$output = $form->get();

if ('' !== rex_request::post('_csrf_token', 'string', '')) {
    Cache::forceWrite();
    Theme::generateDefaultAssets();
    Theme::copyAllAssets();

    // Neu laden, damit die Navigation den gespeicherten Stand zeigt (z. B. Themes im Framework-Modus)
    if ('' === $form->getWarning()) {
        rex_response::sendRedirect(rex_url::currentBackendPage(['applied' => 1], false));
    }
}

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $title, false);
$fragment->setVar('body', $output, false);
echo $fragment->parse('core/page/section.php');
