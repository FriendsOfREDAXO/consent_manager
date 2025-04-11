<?php

$addon = rex_addon::get('consent_manager');

if (rex::isBackend()) {
    rex_view::addJsFile($addon->getAssetsUrl('js/js.cookie.min.js'));

    if (rex::getUser()) {
        rex_extension::register('PACKAGES_INCLUDED', function () use ($addon) {
            if ('consent_manager' === rex_be_controller::getCurrentPagePart(1)) {
                rex_view::addCssFile($addon->getAssetsUrl('consent_manager_backend.css'));
                rex_view::addJsFile($addon->getAssetsUrl('js/consent_manager_backend.js'));
            }
        });
    }

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

if (rex::isFrontend()) {
    rex_extension::register('FE_OUTPUT', static function (rex_extension_point $ep) {
        if (!consent_manager_util::consentConfigured()) {
            return;
        }
        
        $output = $ep->getSubject();
        if (!is_string($output)) {
            return;
        }
        
        $addon = rex_addon::get('consent_manager');

        // Box Template einfügen
        $search = '</head>';
        $javascript = consent_manager_frontend::getFragment(0, 0, 'consent_manager_box_cssjs.php');
        $replace = $javascript . $search;
        $output = str_replace($search, $replace, $output);
        
        $ep->setSubject($output);
    });
}
