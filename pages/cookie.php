<?php

use FriendsOfRedaxo\ConsentManager\Cache;
use FriendsOfRedaxo\ConsentManager\CLang;
use FriendsOfRedaxo\ConsentManager\RexFormSupport;

$showlist = true;
$pid = rex_request::request('pid', 'int', 0);
$func = rex_request::request('func', 'string');
$csrf = rex_csrf_token::factory('consent_manager_cookie');
$clang_id = (int) str_replace('clang', '', rex_be_controller::getCurrentPagePart(3) ?? '');
$table = rex::getTable('consent_manager_cookie');
$msg = '';
if ('delete' === $func) {
    $msg = CLang::deleteCookie($pid);
    Cache::forceWrite();
} elseif ('duplicate' === $func) {
    // Dienst duplizieren
    if (!$csrf->isValid()) {
        $msg = rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } else {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . $table . ' WHERE pid = ?', [$pid]);
        
        if (1 === $sql->getRows()) {
            $newSql = rex_sql::factory();
            $newSql->setTable($table);
            
            // Alle Felder kopieren außer pid
            foreach ($sql->getFieldnames() as $fieldname) {
                if ('pid' !== $fieldname) {
                    $newSql->setValue($fieldname, $sql->getValue($fieldname));
                }
            }
            
            // UID und service_name anpassen
            $originalUid = $sql->getValue('uid');
            $originalName = $sql->getValue('service_name');
            $counter = 1;
            $newUid = $originalUid . '-copy';
            
            // Prüfen ob UID bereits existiert, dann Suffix erhöhen
            $checkSql = rex_sql::factory();
            while (true) {
                $checkSql->setQuery('SELECT pid FROM ' . $table . ' WHERE uid = ? AND clang_id = ?', [$newUid, $clang_id]);
                if (0 === $checkSql->getRows()) {
                    break;
                }
                $counter++;
                $newUid = $originalUid . '-copy-' . $counter;
            }
            
            $newSql->setValue('uid', $newUid);
            $newSql->setValue('service_name', $originalName . ' (Kopie)');
            $newSql->setValue('createdate', date('Y-m-d H:i:s'));
            $newSql->setValue('updatedate', date('Y-m-d H:i:s'));
            
            try {
                $newSql->insert();
                $newPid = $newSql->getLastId();
                Cache::forceWrite();
                
                // Zur Edit-Seite des neuen Eintrags weiterleiten mit Hinweis
                $msg = rex_view::warning(rex_i18n::msg('consent_manager_cookie_duplicated_edit_uid'));
                // Redirect zur Edit-Seite
                header('Location: ' . rex_url::currentBackendPage(['func' => 'edit', 'pid' => $newPid, 'msg' => 'duplicated']));
                exit;
            } catch (rex_sql_exception $e) {
                $msg = rex_view::error(rex_i18n::msg('consent_manager_cookie_duplicate_error') . ': ' . $e->getMessage());
            }
        } else {
            $msg = rex_view::error(rex_i18n::msg('consent_manager_cookie_not_found'));
        }
    }
} elseif ('add' === $func || 'edit' === $func) {
    $formDebug = false;
    $showlist = false;
    
    // Warnung anzeigen wenn von duplicate weitergeleitet (nur einmalig)
    if ('duplicated' === rex_request::request('msg', 'string', '')) {
        echo rex_view::warning(
            '<strong>' . rex_i18n::msg('consent_manager_cookie_duplicated_edit_uid_title') . '</strong><br>' .
            rex_i18n::msg('consent_manager_cookie_duplicated_edit_uid_desc')
        );
        
        // URL ohne msg-Parameter neu laden um Reload-Sperre zu aktivieren
        echo '<script nonce="' . rex_response::getNonce() . '">';
        echo 'if (window.history.replaceState) {';
        echo '  var url = new URL(window.location);';
        echo '  url.searchParams.delete("msg");';
        echo '  window.history.replaceState({}, document.title, url);';
        echo '}';
        echo '</script>';
    }
    
    $form = rex_form::factory($table, '', 'pid = ' . $pid, 'post', $formDebug);
    $form->addParam('pid', $pid);
    $form->addParam('sort', rex_request::request('sort', 'string', ''));
    $form->addParam('sorttype', rex_request::request('sorttype', 'string', ''));
    $form->addParam('start', rex_request::request('start', 'int', 0));
    $form->setApplyUrl(rex_url::currentBackendPage());
    $form->addHiddenField('clang_id', $clang_id);
    RexFormSupport::getId($form, $table);

    // Multi-Language Sprach-Switcher (gilt für gesamten Datensatz, daher ganz oben)
    if ($form->isEditMode() && rex_clang::count() > 1) {
        // Sprach-Switcher für alle Services außer consent_manager (System-Cookie)
        if ('consent_manager' !== $form->getSql()->getValue('uid')) {
            $languageSwitcher = '<div class="alert alert-info" style="margin: 15px 0;">';
            $languageSwitcher .= '<i class="rex-icon fa-language"></i> <strong>' . rex_i18n::msg('consent_manager_cookie_multilang_title') . '</strong><br>';
            $languageSwitcher .= '<small>' . rex_i18n::msg('consent_manager_cookie_multilang_desc', rex_i18n::rawMsg(rex_clang::get(rex_clang::getStartId())->getName())) . '</small><br><br>';
            $languageSwitcher .= '<div class="btn-group" role="group">';
            
            foreach (rex_clang::getAll() as $clang) {
                $clangId = $clang->getId();
                $isActive = $clangId === $clang_id ? 'btn-primary' : 'btn-default';
                $icon = $clangId === rex_clang::getStartId() ? '<i class="rex-icon fa-star"></i> ' : '';
                $url = rex_url::currentBackendPage(['func' => 'edit', 'pid' => $pid, 'page' => 'consent_manager/cookie/clang' . $clangId]);
                $languageSwitcher .= '<a href="' . $url . '" class="btn ' . $isActive . ' btn-sm">' . $icon . rex_escape($clang->getName()) . '</a>';
            }
            
            $languageSwitcher .= '</div>';
            $languageSwitcher .= '</div>';
            $field = $form->addRawField($languageSwitcher);
            
            // Fallback-Hinweis für Nicht-Start-Sprachen (bezieht sich auf alle Felder)
            if ($clang_id !== rex_clang::getStartId()) {
                $startLangName = rex_i18n::rawMsg(rex_clang::get(rex_clang::getStartId())->getName());
                $fallbackNotice = '<div class="alert alert-warning"><i class="rex-icon fa-info-circle"></i> ' . 
                    rex_i18n::msg('consent_manager_cookie_fallback_notice', $startLangName) . '</div>';
                $field = $form->addRawField($fallbackNotice);
            }
        }
    }

    if ('edit' === $func && 'consent_manager' === $form->getSql()->getValue('uid')) {
        $form->addRawField(RexFormSupport::showInfo(rex_i18n::rawMsg('consent_manager_cookie_consent_manager_info')));
        $form->addRawField(RexFormSupport::getFakeText(rex_i18n::msg('consent_manager_uid'), $form->getSql()->getValue('uid')));
    } else {
        if ($clang_id === rex_clang::getStartId() || !$form->isEditMode()) {
            $field = $form->addTextField('uid');
            $field->setLabel(rex_i18n::msg('consent_manager_uid_with_hint'));
            $field->getValidator()->add('notEmpty', rex_i18n::msg('consent_manager_uid_empty_msg'));
            $field->getValidator()->add('match', rex_i18n::msg('consent_manager_uid_malformed_msg'), '/^[a-z0-9-_]+$/');
        } else {
            $form->addRawField(RexFormSupport::getFakeText(rex_i18n::msg('consent_manager_uid'), (string) $form->getSql()->getValue('uid')));
        }
    }
    $field = $form->addTextField('service_name');
    $field->setLabel(rex_i18n::msg('consent_manager_cookie_service_name'));
    
    $field = $form->addTextField('variant');
    $field->setLabel(rex_i18n::msg('consent_manager_cookie_variant'));
    $field->setNotice(rex_i18n::msg('consent_manager_cookie_variant_notice'));
    
    $field = $form->addTextAreaField('definition');
    $field->setAttributes(['class' => 'form-control codemirror', 'name' => $field->getAttribute('name'), 'data-codemirror-mode' => 'text/x-yaml']);
    $field->setLabel(rex_i18n::msg('consent_manager_cookie_definition'));
    $field->getValidator()->add('custom', rex_i18n::msg('consent_manager_cookie_malformed_yaml'), RexFormSupport::validateYaml(...));

    $field = $form->addTextField('provider');
    $field->setLabel(rex_i18n::msg('consent_manager_cookie_provider'));
    $field = $form->addTextField('provider_link_privacy');
    $field->setLabel(rex_i18n::msg('consent_manager_cookie_provider_link_privacy'));
    $field->setNotice(rex_i18n::msg('consent_manager_cookie_notice_provider_link_privacy'));

    if ('edit' === $func && 'consent_manager' !== $form->getSql()->getValue('uid')) {
        // Google Consent Mode v2 Helper Fragment verwenden
        $fragment = new rex_fragment();
        $googleHelperHtml = $fragment->parse('ConsentManager/google_consent_helper.php');
        $field = $form->addRawField($googleHelperHtml);

        $field = $form->addTextAreaField('script');
        $field->setAttributes(['class' => 'form-control codemirror', 'name' => $field->getAttribute('name'), 'data-codemirror-mode' => 'text/html']);
        $field->setLabel(rex_i18n::msg('consent_manager_cookiegroup_scripts'));
        $field->setNotice(rex_i18n::rawMsg('consent_manager_cookiegroup_scripts_notice'));

        $field = $form->addTextAreaField('script_unselect');
        $field->setAttributes(['class' => 'form-control codemirror', 'name' => $field->getAttribute('name'), 'data-codemirror-mode' => 'text/html']);
        $field->setLabel(rex_i18n::msg('consent_manager_cookiegroup_scripts_unselect'));
        $field->setNotice(rex_i18n::rawMsg('consent_manager_cookiegroup_scripts_notice'));
    }
    if ('add' === $func) {
        // Script-Felder sind in ALLEN Sprachen editierbar (für unterschiedliche Tracking-IDs etc.)
        // Google Consent Mode v2 Helper Fragment verwenden
        $fragment = new rex_fragment();
        $googleHelperHtml = $fragment->parse('ConsentManager/google_consent_helper.php');
        $field = $form->addRawField($googleHelperHtml);

        $field = $form->addTextAreaField('script');
        $field->setAttributes(['class' => 'form-control codemirror', 'name' => $field->getAttribute('name'), 'data-codemirror-mode' => 'text/html']);
        $field->setLabel(rex_i18n::msg('consent_manager_cookiegroup_scripts'));
        $field->setNotice(rex_i18n::rawMsg('consent_manager_cookiegroup_scripts_notice'));

        $field = $form->addTextAreaField('script_unselect');
        $field->setAttributes(['class' => 'form-control codemirror', 'name' => $field->getAttribute('name'), 'data-codemirror-mode' => 'text/html']);
        $field->setLabel(rex_i18n::msg('consent_manager_cookiegroup_scripts_unselect'));
        $field->setNotice(rex_i18n::rawMsg('consent_manager_cookiegroup_scripts_notice'));
    }

    $field = $form->addTextAreaField('placeholder_text');
    $field->setLabel(rex_i18n::msg('consent_manager_cookie_placeholder_text'));
    $field->setNotice(rex_i18n::rawMsg('consent_manager_cookie_placeholder_text_notice'));
    $field = $form->addMediaField('placeholder_image');
    $field->setLabel(rex_i18n::msg('consent_manager_cookie_placeholder_image'));
    $field->setNotice(rex_i18n::msg('consent_manager_cookie_placeholder_image_notice'));

    $title = $form->isEditMode() ? rex_i18n::msg('consent_manager_cookie_edit') : rex_i18n::msg('consent_manager_cookie_add');
    $content = $form->get();

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', $title);
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
}
echo $msg;
if ($showlist) {
    $listDebug = false;
    $sql = 'SELECT pid,uid,service_name,variant,provider FROM ' . $table . ' WHERE clang_id = ' . $clang_id . ' ORDER BY uid';

    $list = rex_list::factory($sql, 100, '', $listDebug);
    $list->addParam('page', rex_be_controller::getCurrentPage());
    $list->addTableAttribute('class', 'table table-striped table-hover consent_manager-table consent_manager-table-cookie');
    $list->addTableAttribute('id', 'consent_manager-table-cookie');

    $tdIcon = '<i class="fa fa-coffee"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '"' . rex::getAccesskey(rex_i18n::msg('add'), 'add') . '><i class="rex-icon rex-icon-add"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'pid' => '###pid###']);

    $list->removeColumn('pid');
    $list->setColumnLabel('uid', rex_i18n::msg('consent_manager_uid'));
    $list->setColumnParams('uid', ['func' => 'edit', 'pid' => '###pid###']);
    $list->setColumnSortable('uid');

    // Variant-Spalte entfernen (wird in service_name integriert)
    $list->removeColumn('variant');

    $list->setColumnLabel('service_name', rex_i18n::msg('consent_manager_cookie_service_name'));
    $list->setColumnSortable('service_name');
    $list->setColumnFormat('service_name', 'custom', static function (array $params): string {
        $value = $params['value'];
        $list = $params['list'];
        $variant = $list->getValue('variant');
        $html = '<strong>' . rex_escape($value) . '</strong>';
        if ('' !== $variant && null !== $variant) {
            $html .= '<br><small style="color: #6c757d; font-style: italic;">→ ' . rex_escape($variant) . '</small>';
        }
        return $html;
    });

    $list->setColumnLabel('provider', rex_i18n::msg('consent_manager_cookie_provider'));
    $list->setColumnSortable('provider');

    $list->addColumn(rex_i18n::msg('function'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('function'), ['<th class="rex-table-action" colspan="3">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('function'), ['pid' => '###pid###', 'func' => 'edit', 'start' => rex_request::request('start', 'string')]);

    $list->addColumn(rex_i18n::msg('consent_manager_duplicate'), '<i class="rex-icon rex-icon-duplicate"></i> ' . rex_i18n::msg('consent_manager_duplicate'));
    $list->setColumnLayout(rex_i18n::msg('consent_manager_duplicate'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('consent_manager_duplicate'), ['pid' => '###pid###', 'func' => 'duplicate', 'start' => rex_request::request('start', 'string')] + $csrf->getUrlParams());

    $list->addColumn(rex_i18n::msg('delete'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
    $list->setColumnLayout(rex_i18n::msg('delete'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('delete'), ['pid' => '###pid###', 'func' => 'delete'] + $csrf->getUrlParams());
    $list->addLinkAttribute(rex_i18n::msg('delete'), 'onclick', 'return confirm(\' ###service_name### ' . rex_i18n::msg('delete') . ' ?\')');


    $content = $list->get();

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('consent_manager_cookies'));
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}
