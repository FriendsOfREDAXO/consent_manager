<?php
// Nur im Backend
if (rex::isBackend()) {

    rex_perm::register('consent_manager[texteditonly]');
    if (rex::getUser()) {
        if (!rex::getUser()->isAdmin() && rex::getUser()->hasPerm('consent_manager[texteditonly]')) {
            $page = $this->getProperty('page');
            if ($page) {
                foreach (['cookiegroup', 'cookie', 'domain', 'config', 'setup', 'help'] as $removepage) {
                    unset($page['subpages'][$removepage]);
                }
                $this->setProperty('page', $page);
            }
        }
    }

    rex_extension::register('PACKAGES_INCLUDED', function () {
        if (rex::getUser()) {
            if (rex::getUser()->isAdmin() && rex::isDebugMode() && 'get' == rex_request_method()) {
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
            if ('consent_manager' == rex_be_controller::getCurrentPagePart(1)) {
                rex_view::addCssFile($this->getAssetsUrl('consent_manager_backend.css'));
            }
        }
    });

    rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) {
        if (1 == rex_clang::count()) {
            $s = '</head>';
            $r = '<style>[id*="rex-page-consent_manager"] .rex-page-nav .navbar{display:none}</style></head>';
            $ep->setSubject(str_replace($s, $r, $ep->getSubject()));
        }
    });
    rex_extension::register('REX_FORM_CONTROL_FIELDS', 'consent_manager_rex_form::removeDeleteButton');
    rex_extension::register('PAGES_PREPARED', 'consent_manager_clang::addLangNav');
    rex_extension::register('REX_FORM_SAVED', 'consent_manager_clang::formSaved');
    rex_extension::register('REX_FORM_SAVED', 'consent_manager_cache::write');
    rex_extension::register('CLANG_ADDED', 'consent_manager_clang::clangAdded');
    rex_extension::register('CLANG_DELETED', 'consent_manager_clang::clangDeleted');

    if ('consent_manager' == rex_be_controller::getCurrentPagePart(1) && $this->getConfig('justInstalled')) {
        $this->setConfig('justInstalled', false);
        consent_manager_clang::addonJustInstalled();
    }
    if ($this->getConfig('forceCache')) {
        $this->setConfig('forceCache', false);
        consent_manager_cache::forceWrite();
    }

}

// Nur im Frontend
if (rex::isFrontend()) {
    rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) {

        if (true === rex_get('consent_manager_outputjs', 'bool', false)) {
            $consent_manager = new consent_manager_frontend(0);
            $consent_manager->setDomain($_SERVER['HTTP_HOST']);
            $consent_manager->outputJavascript();
            exit;
        }

        if (!rex_article::getCurrentId()) {
            return;
        }

        if (isset($_SESSION['consent_manager']['outputjs']) && strstr($ep->getSubject(), '<!--REX_CONSENT_MANAGER_OUTPUT[]-->')) {
            $consent_manager = isset($_COOKIE['consent_manager']) ? json_decode($_COOKIE['consent_manager'], 1) : false;
            $outcss = rex_addon::get('consent_manager')->getConfig('outputcss', false);
            $_search = '<!--REX_CONSENT_MANAGER_OUTPUT[]-->';
            $_replace = '';
            if ($outcss) {
                if (!$consent_manager || strstr($ep->getSubject(), 'consent_manager-show-box') || strstr($ep->getSubject(), 'consent_manager-show-box-reload')) {
                    if (isset($_SESSION['consent_manager']['outputcss'])) {
                        $_replace .= $_SESSION['consent_manager']['outputcss'];
                    }
                } else {
                    $_replace .= '    <style>.consent_manager-hidden{display:none}</style>' . PHP_EOL;
                }
            } else {
                $_replace .= $_SESSION['consent_manager']['outputcss'];
            }
            if (isset($_SESSION['consent_manager']['outputjs'])) {
                $_replace .= $_SESSION['consent_manager']['outputjs'];
            }
            $ep->setSubject(str_replace($_search, $_replace, $ep->getSubject()));
        }

    });

}
