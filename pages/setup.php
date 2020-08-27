<?php
$func = rex_request('func', 'string');
$csrf = rex_csrf_token::factory('iwcc_setup');
if ($func != '') {
    if (!$csrf->isValid()) {
        echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } else {
        if ($func == 'setup') {
            $file = rex_path::addon('iwcc').'setup/setup.sql';
            rex_sql_util::importDump($file);
            iwcc_clang::addonJustInstalled();
            echo rex_view::success($this->i18n('iwcc_setup_import_successful'));
        }
    }
}
$content = '<h3>'.$this->i18n('iwcc_setup_headline').'</h3>';
$content .= '<p>'.rex_i18n::rawMsg('iwcc_setup_info').'</p>';
$content .= '<p><a class="btn btn-primary" href="'.rex_url::currentBackendPage(['func' => 'setup'] + $csrf->getUrlParams()).'">'.$this->i18n('iwcc_setup_import').'</a></p>';

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('iwcc_setup'));
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
