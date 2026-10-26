<?php
/**
 * Hinweis für Themes und Theme-Editor, solange Themes nicht ausgegeben werden (CSS-Framework-Modus oder eigenes CSS),
 * mit Knopf zum Zurückschalten. Gibt true zurück, wenn Themes gerade nicht greifen.
 *
 * Erwartet: rex_addon $addon
 */

use FriendsOfRedaxo\ConsentManager\Cache;
use FriendsOfRedaxo\ConsentManager\Theme;

/** @var rex_addon $addon */

$csrf = rex_csrf_token::factory('consent_manager_use_themes');
if ('use_themes' === rex_post('consent_manager_action', 'string', '')) {
    if (!$csrf->isValid()) {
        echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } else {
        $addon->setConfig('css_framework_mode', '');
        $addon->setConfig('outputowncss', '');
        Cache::forceWrite();
        Theme::generateDefaultAssets();
        Theme::copyAllAssets();
        echo rex_view::success(rex_i18n::msg('consent_manager_themes_enabled'));
    }
}

$framework = (string) $addon->getConfig('css_framework_mode', '');
$ownCss = '|1|' === $addon->getConfig('outputowncss', false);
if ('' === $framework && !$ownCss) {
    return false;
}

$reason = '' !== $framework
    ? rex_i18n::msg('consent_manager_themes_inactive_framework', rex_i18n::msg('consent_manager_config_css_framework_mode_' . $framework))
    : rex_i18n::msg('consent_manager_themes_inactive_owncss');

$button = '<form method="post" action="' . rex_url::currentBackendPage() . '" style="display:inline">'
    . $csrf->getHiddenField()
    . '<button type="submit" name="consent_manager_action" value="use_themes" class="btn btn-primary">' . rex_i18n::msg('consent_manager_themes_enable') . '</button>'
    . '</form>';

echo rex_view::info('<p>' . $reason . '</p><p>' . $button . '</p>');

return true;
