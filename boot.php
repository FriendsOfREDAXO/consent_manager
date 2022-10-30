<?php
$addon = rex_addon::get('consent_manager');

// Nur im Backend
if (rex::isBackend()) {

    rex_perm::register('consent_manager[texteditonly]');
    if (null !== rex::getUser()) {
        if (!rex::getUser()->isAdmin() && rex::getUser()->hasPerm('consent_manager[texteditonly]')) {
            $page = (array)$addon->getProperty('page', []);
            if ([] !== $page) {
                foreach (['cookiegroup', 'cookie', 'domain', 'config', 'setup', 'changelog', 'help'] as $removepage) {
                    unset($page['subpages'][$removepage]);
                }
                $addon->setProperty('page', $page);
            }
        }
    }

    rex_extension::register('PACKAGES_INCLUDED', function () {
        $addon = rex_addon::get('consent_manager');

        if (null !== rex::getUser()) {
            if (rex::getUser()->isAdmin() && rex::isDebugMode() && 'get' === rex_request_method()) {
                $compiler = new rex_scss_compiler();
                $compiler->setRootDir($addon->getPath());
                $compiler->setScssFile($addon->getPath('scss/consent_manager_backend.scss'));
                $compiler->setCssFile($addon->getPath('assets/consent_manager_backend.css'));
                $compiler->compile();
                $compiler->setScssFile($addon->getPath('scss/consent_manager_frontend.scss'));
                $compiler->setCssFile($addon->getPath('assets/consent_manager_frontend.css'));
                $compiler->compile();
                rex_file::copy($addon->getPath('assets/consent_manager_frontend.css'), $addon->getAssetsPath('consent_manager_frontend.css'));
                rex_file::copy($addon->getPath('assets/consent_manager_backend.css'), $addon->getAssetsPath('consent_manager_backend.css'));
                rex_file::copy($addon->getPath('assets/consent_manager_polyfills.js'), $addon->getAssetsPath('consent_manager_polyfills.js'));
                rex_file::copy($addon->getPath('assets/consent_manager_frontend.js'), $addon->getAssetsPath('consent_manager_frontend.js'));
            }
            if ('consent_manager' === rex_be_controller::getCurrentPagePart(1)) {
                rex_view::addCssFile($addon->getAssetsUrl('consent_manager_backend.css'));
                rex_view::addJsFile($addon->getAssetsUrl('consent_manager_backend.js'));
            }
        }
    });

    rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) {
        if (1 === rex_clang::count()) {
            $s = '</head>';
            $r = '<style>[id*="rex-page-consent_manager"] .rex-page-nav .navbar{display:none}</style></head>';
            $ep->setSubject(str_replace($s, $r, strval($ep->getSubject())));
        }
    });
    rex_extension::register('REX_FORM_CONTROL_FIELDS', 'consent_manager_rex_form::removeDeleteButton');
    rex_extension::register('PAGES_PREPARED', 'consent_manager_clang::addLangNav');
    rex_extension::register('REX_FORM_SAVED', 'consent_manager_clang::formSaved');
    rex_extension::register('REX_FORM_SAVED', 'consent_manager_cache::write');
    rex_extension::register('CLANG_ADDED', 'consent_manager_clang::clangAdded');
    rex_extension::register('CLANG_DELETED', 'consent_manager_clang::clangDeleted');

    if ('consent_manager' === rex_be_controller::getCurrentPagePart(1) && $addon->getConfig('justInstalled') === true) {
        $addon->setConfig('justInstalled', false);
        consent_manager_clang::addonJustInstalled();
    }
    if ($addon->getConfig('forceCache') === true) {
        $addon->setConfig('forceCache', false);
        consent_manager_cache::forceWrite();
    }

}

// Nur im Frontend
if (rex::isFrontend()) {

    rex_extension::register('FE_OUTPUT', static function (rex_extension_point $ep) {

        if (true === rex_get('consent_manager_outputjs', 'bool', false)) {
            $consent_manager = new consent_manager_frontend(0);
            //$consent_manager->setDomain($_SERVER['HTTP_HOST']);
            $consent_manager->outputJavascript();
            exit;
        }

    });

}
