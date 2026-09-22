<?php
/**
 * Gemeinsamer Abschluss der Einstellungs-Unterseiten: Formular ausgeben und nach dem Speichern Cache und Theme-Assets erneuern.
 *
 * Erwartet: rex_config_form $form, string $title
 */

use FriendsOfRedaxo\ConsentManager\Cache;
use FriendsOfRedaxo\ConsentManager\Theme;

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $title, false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');

if ('' !== rex_request::post('_csrf_token', 'string', '')) {
    Cache::forceWrite();
    Theme::generateDefaultAssets();
    Theme::copyAllAssets();
}
