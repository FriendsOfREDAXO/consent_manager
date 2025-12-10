<?php

$addon = rex_addon::get('consent_manager');

// Nur im Backend
if (rex::isBackend()) {
    rex_perm::register('consent_manager[texteditonly]');
    if (null !== rex::getUser()) {
        if (!rex::getUser()->isAdmin() && rex::getUser()->hasPerm('consent_manager[texteditonly]')) {
            $page = (array) $addon->getProperty('page', []);
            if ([] !== $page) {
                /** @var array<int, string> */
                $rarray = ['cookiegroup', 'cookie', 'domain', 'config', 'setup', 'changelog', 'help'];
                foreach ($rarray as $removepage) {
                    unset($page['subpages'][$removepage]); /** @phpstan-ignore-line */
                }
                $addon->setProperty('page', $page);
            }
        }
    }

    rex_extension::register('PACKAGES_INCLUDED', static function () {
        $addon = rex_addon::get('consent_manager');
        if (null !== rex::getUser()) {
            if ('consent_manager' === rex_be_controller::getCurrentPagePart(1)) {
                rex_view::addCssFile($addon->getAssetsUrl('consent_manager_backend.css'));
                rex_view::addJsFile($addon->getAssetsUrl('consent_manager_backend.js'));
            }
        }
    });

    if ('consent_manager' === rex_be_controller::getCurrentPagePart(1)) {
        rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) {
            if (1 === rex_clang::count()) {
                $s = '</head>';
                $r = '<style>.rex-page-nav .navbar{display:none}</style></head>';
                if (is_string($ep->getSubject())) {
                    $ep->setSubject(str_replace($s, $r, $ep->getSubject()));
                }
            }
        });
    }

    rex_extension::register('REX_FORM_CONTROL_FIELDS', 'consent_manager_rex_form::removeDeleteButton');
    rex_extension::register('PAGES_PREPARED', 'consent_manager_clang::addLangNav');
    rex_extension::register('REX_FORM_SAVED', 'consent_manager_clang::formSaved');
    rex_extension::register('REX_FORM_SAVED', 'consent_manager_cache::write');
    rex_extension::register('CLANG_ADDED', 'consent_manager_clang::clangAdded');
    rex_extension::register('CLANG_DELETED', 'consent_manager_clang::clangDeleted');

    if ('consent_manager' === rex_be_controller::getCurrentPagePart(1) && true === $addon->getConfig('justInstalled')) {
        $addon->setConfig('justInstalled', false);
        consent_manager_clang::addonJustInstalled();
    }
    if (true === $addon->getConfig('forceCache')) {
        $addon->setConfig('forceCache', false);
        consent_manager_cache::forceWrite();
    }
}

// Nur im Frontend
if (rex::isFrontend()) {
    rex_extension::register('FE_OUTPUT', static function (rex_extension_point $ep) {
        if (true === rex_get('consent_manager_outputjs', 'bool', false)) {
            $consent_manager = new consent_manager_frontend(0);
            $consent_manager->outputJavascript();
            exit;
        }
    });
}
