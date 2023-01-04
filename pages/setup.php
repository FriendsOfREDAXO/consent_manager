<?php

$addon = rex_addon::get('consent_manager');

$func = rex_request('func', 'string');
$csrf = rex_csrf_token::factory('consent_manager_setup');
if ('' !== $func) {
    if (!$csrf->isValid()) {
        echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } else {
        if ('setup' === $func) {
            $file = rex_path::addon('consent_manager').'setup/setup.sql';
            rex_sql_util::importDump($file);
            consent_manager_clang::addonJustInstalled();
            echo rex_view::success($addon->i18n('consent_manager_setup_import_successful'));
        }
    }
}
$content = '<h3>'.$addon->i18n('consent_manager_setup_headline').'</h3>';
$content .= '<p>'.rex_i18n::rawMsg('consent_manager_setup_info').'</p>';
$content .= '<p><a class="btn btn-primary" href="'.rex_url::currentBackendPage(['func' => 'setup'] + $csrf->getUrlParams()).'" data-confirm="'.$addon->i18n('consent_manager_setup_import_confirm').'">'.$addon->i18n('consent_manager_setup_import').'</a></p>';

$fragment = new rex_fragment();
$fragment->setVar('title', $addon->i18n('consent_manager_setup'));
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
