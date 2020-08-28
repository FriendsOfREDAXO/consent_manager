<?php
rex_extension::register('PACKAGES_INCLUDED', function () {
    if (rex::getUser())
    {
        if (rex::getUser()->isAdmin() && rex::isDebugMode() && rex_request_method() == 'get')
        {
            $compiler = new rex_scss_compiler();
            $compiler->setRootDir($this->getPath());
            $compiler->setScssFile($this->getPath('scss/iwcc_backend.scss'));
            $compiler->setCssFile($this->getPath('assets/iwcc_backend.css'));
            $compiler->compile();
            $compiler->setScssFile($this->getPath('scss/iwcc_frontend.scss'));
            $compiler->setCssFile($this->getPath('assets/iwcc_frontend.css'));
            $compiler->compile();
            rex_file::copy($this->getPath('assets/iwcc_frontend.css'), $this->getAssetsPath('iwcc_frontend.css'));
            rex_file::copy($this->getPath('assets/iwcc_backend.css'), $this->getAssetsPath('iwcc_backend.css'));
            rex_file::copy($this->getPath('assets/iwcc_polyfills.js'), $this->getAssetsPath('iwcc_frontend.js'));
            rex_file::copy($this->getPath('assets/iwcc_frontend.js'), $this->getAssetsPath('iwcc_frontend.js'));
        }
        if (rex::isBackend())
        {
            rex_view::addCssFile($this->getAssetsUrl('iwcc_backend.css'));
        }

    }
});

rex_extension::register('OUTPUT_FILTER', function (rex_extension_point $ep) {
    if (rex::isBackend() && rex_clang::count() == 1)
    {
        $s = '</head>';
        $r = '<style>[id*="rex-page-iwcc"] .rex-page-nav .navbar{display:none}</style></head>';
        $ep->setSubject(str_replace($s, $r, $ep->getSubject()));
    }
});
rex_extension::register('REX_FORM_CONTROL_FIELDS', 'iwcc_rex_form::removeDeleteButton');
rex_extension::register('PAGES_PREPARED', 'iwcc_clang::addLangNav');
rex_extension::register('REX_FORM_SAVED', 'iwcc_clang::formSaved');
rex_extension::register('REX_FORM_SAVED', 'iwcc_cache::write');
rex_extension::register('CLANG_ADDED', 'iwcc_clang::clangAdded');
rex_extension::register('CLANG_DELETED', 'iwcc_clang::clangDeleted');

if (rex_be_controller::getCurrentPagePart(1) == 'iwcc' && $this->getConfig('justInstalled'))
{
    $this->setConfig('justInstalled', false);
    iwcc_clang::addonJustInstalled();
}
if ($this->getConfig('forceCache')) {
    $this->setConfig('forceCache', false);
    iwcc_cache::forceWrite();
}
