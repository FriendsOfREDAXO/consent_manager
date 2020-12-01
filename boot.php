<?php
rex_extension::register('PACKAGES_INCLUDED', function () {
    if (rex::getUser())
    {
        if (rex::getUser()->isAdmin() && rex::isDebugMode() && rex_request_method() == 'get')
        {
            $compiler = new rex_scss_compiler();
            $compiler->setRootDir($this->getPath());
            $compiler->setScssFile($this->getPath('scss/consent_manager_backend.scss'));
            $compiler->setCssFile($this->getPath('assets/consent_manager_backend.css'));
            $compiler->compile();
            $compiler->setScssFile($this->getPath('scss/consent_manager_frontend.scss'));
            $compiler->setCssFile($this->getPath('assets/consent_manager_frontend.css'));
            $compiler->compile();
            rex_file::copy($this->getPath('assets/consent_manager_frontend.css'), $this->getAssetsPath('consent_manager_frontend.css'));
            rex_file::copy($this->getPath('assets/consent_manager_backend.css'), $this->getAssetsPath('consent_manager_backend.css'));
            rex_file::copy($this->getPath('assets/consent_manager_polyfills.js'), $this->getAssetsPath('consent_manager_frontend.js'));
            rex_file::copy($this->getPath('assets/consent_manager_frontend.js'), $this->getAssetsPath('consent_manager_frontend.js'));
        }
        if (rex::isBackend())
        {
            rex_view::addCssFile($this->getAssetsUrl('consent_manager_backend.css'));
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
rex_extension::register('REX_FORM_CONTROL_FIELDS', 'consent_manager_rex_form::removeDeleteButton');
rex_extension::register('PAGES_PREPARED', 'consent_manager_clang::addLangNav');
rex_extension::register('REX_FORM_SAVED', 'consent_manager_clang::formSaved');
rex_extension::register('REX_FORM_SAVED', 'consent_manager_cache::write');
rex_extension::register('CLANG_ADDED', 'consent_manager_clang::clangAdded');
rex_extension::register('CLANG_DELETED', 'consent_manager_clang::clangDeleted');

if (rex_be_controller::getCurrentPagePart(1) == 'iwcc' && $this->getConfig('justInstalled'))
{
    $this->setConfig('justInstalled', false);
    consent_manager_clang::addonJustInstalled();
}
if ($this->getConfig('forceCache')) {
    $this->setConfig('forceCache', false);
    consent_manager_cache::forceWrite();
}
