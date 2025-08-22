<?php

$addon = rex_addon::get('consent_manager');

$showlist = true;
$pid = rex_request('pid', 'int', 0);
$func = rex_request('func', 'string');
$csrf = rex_csrf_token::factory('consent_manager_cookie');
$clang_id = (int) str_replace('clang', '', rex_be_controller::getCurrentPagePart(3) ?? '');
$table = rex::getTable('consent_manager_cookie');
$msg = '';
if ('delete' === $func) {
    $msg = consent_manager_clang::deleteCookie($pid);
    consent_manager_cache::forceWrite();
} elseif ('add' === $func || 'edit' === $func) {
    $formDebug = false;
    $showlist = false;
    $form = rex_form::factory($table, '', 'pid = ' . $pid, 'post', $formDebug);
    $form->addParam('pid', $pid);
    $form->addParam('sort', rex_request('sort', 'string', ''));
    $form->addParam('sorttype', rex_request('sorttype', 'string', ''));
    $form->addParam('start', rex_request('start', 'int', 0));
    $form->setApplyUrl(rex_url::currentBackendPage());
    $form->addHiddenField('clang_id', $clang_id);
    consent_manager_rex_form::getId($form, $table);

    if ('edit' === $func && 'consent_manager' === $form->getSql()->getValue('uid')) {
        $form->addRawField(consent_manager_rex_form::showInfo($addon->i18n('consent_manager_cookie_consent_manager_info')));
        $form->addRawField(consent_manager_rex_form::getFakeText($addon->i18n('consent_manager_uid'), $form->getSql()->getValue('uid')));
    } else {
        if ($clang_id === rex_clang::getStartId() || !$form->isEditMode()) {
            $field = $form->addTextField('uid');
            $field->setLabel($addon->i18n('consent_manager_uid_with_hint'));
            $field->getValidator()->add('notEmpty', $addon->i18n('consent_manager_uid_empty_msg'));
            $field->getValidator()->add('match', $addon->i18n('consent_manager_uid_malformed_msg'), '/^[a-z0-9-_]+$/');
        } else {
            $form->addRawField(consent_manager_rex_form::getFakeText($addon->i18n('consent_manager_uid'), $form->getSql()->getValue('uid')));
        }
    }
    $field = $form->addTextField('service_name');
    $field->setLabel($addon->i18n('consent_manager_cookie_service_name'));
    $field = $form->addTextAreaField('definition');
    $field->setAttributes(['class' => 'form-control codemirror', 'name' => $field->getAttribute('name'), 'data-codemirror-mode' => 'text/x-yaml']);
    $field->setLabel($addon->i18n('consent_manager_cookie_definition'));
    $field->getValidator()->add('custom', $addon->i18n('consent_manager_cookie_malformed_yaml'), 'consent_manager_rex_form::validateYaml');

    $field = $form->addTextField('provider');
    $field->setLabel($addon->i18n('consent_manager_cookie_provider'));
    $field = $form->addTextField('provider_link_privacy');
    $field->setLabel($addon->i18n('consent_manager_cookie_provider_link_privacy'));
    $field->setNotice($addon->i18n('consent_manager_cookie_notice_provider_link_privacy'));

    if ('edit' === $func && 'consent_manager' !== $form->getSql()->getValue('uid')) {
        if ($clang_id === rex_clang::getStartId() || !$form->isEditMode()) {
            // Google Consent Mode v2 Helper VOR den Script-Feldern
            $googleHelperHtml = '';
            $googleHelperHtml .= '<div id="google-consent-helper-panel" class="panel panel-info">';
            $googleHelperHtml .= '<div class="panel-heading">';
            $googleHelperHtml .= '<h4 class="panel-title">';
            $googleHelperHtml .= '<i class="fa fa-google"></i> Google Consent Mode v2 Helper ';
            $googleHelperHtml .= '<button type="button" id="google-helper-toggle" class="btn btn-xs btn-default pull-right">';
            $googleHelperHtml .= '<i class="fa fa-chevron-down"></i> Helper einblenden';
            $googleHelperHtml .= '</button>';
            $googleHelperHtml .= '<div class="clearfix"></div>';
            $googleHelperHtml .= '</h4>';
            $googleHelperHtml .= '</div>';
            
            $googleHelperHtml .= '<div id="google-consent-helper-content" class="collapse">';
            $googleHelperHtml .= '<div class="panel-body">';
            
            // Messages Container
            $googleHelperHtml .= '<div id="google-helper-messages"></div>';
            
            $googleHelperHtml .= '<p class="help-block">';
            $googleHelperHtml .= '<i class="fa fa-info-circle"></i> ';
            $googleHelperHtml .= 'Automatische Generierung von <code>gtag("consent", "update", {...})</code> Skripten für bekannte Services.';
            $googleHelperHtml .= '</p>';
            
            // Service Auswahl
            $googleHelperHtml .= '<div class="form-group">';
            $googleHelperHtml .= '<label for="google-helper-service"><i class="fa fa-cog"></i> Service-Typ:</label>';
            $googleHelperHtml .= '<select id="google-helper-service" class="form-control">';
            $googleHelperHtml .= '<option value="">-- Service auswählen --</option>';
            $googleHelperHtml .= '<option value="analytics">Analytics (analytics_storage)</option>';
            $googleHelperHtml .= '<option value="google-analytics">Google Analytics (analytics_storage)</option>';
            $googleHelperHtml .= '<option value="google-analytics-4">Google Analytics 4 (analytics_storage)</option>';
            $googleHelperHtml .= '<option value="matomo">Matomo (analytics_storage)</option>';
            $googleHelperHtml .= '<option value="adwords">Google AdWords (ad_storage, ad_user_data, ad_personalization)</option>';
            $googleHelperHtml .= '<option value="google-ads">Google Ads (ad_storage, ad_user_data, ad_personalization)</option>';
            $googleHelperHtml .= '<option value="facebook-pixel">Facebook Pixel (ad_storage, ad_user_data, ad_personalization)</option>';
            $googleHelperHtml .= '<option value="youtube">YouTube (ad_storage, personalization_storage)</option>';
            $googleHelperHtml .= '<option value="google-maps">Google Maps (functionality_storage, personalization_storage)</option>';
            $googleHelperHtml .= '</select>';
            $googleHelperHtml .= '<small class="help-block">Wird automatisch basierend auf dem Service-Namen vorgeschlagen</small>';
            $googleHelperHtml .= '</div>';
            
            // Buttons
            $googleHelperHtml .= '<div class="form-group">';
            $googleHelperHtml .= '<div class="btn-group btn-group-justified" role="group">';
            $googleHelperHtml .= '<div class="btn-group" role="group">';
            $googleHelperHtml .= '<button type="button" id="generate-consent-script" class="btn btn-success">';
            $googleHelperHtml .= '<i class="fa fa-check"></i> Consent-Skript';
            $googleHelperHtml .= '</button>';
            $googleHelperHtml .= '</div>';
            $googleHelperHtml .= '<div class="btn-group" role="group">';
            $googleHelperHtml .= '<button type="button" id="generate-revoke-script" class="btn btn-warning">';
            $googleHelperHtml .= '<i class="fa fa-times"></i> Widerruf-Skript';
            $googleHelperHtml .= '</button>';
            $googleHelperHtml .= '</div>';
            $googleHelperHtml .= '</div>';
            $googleHelperHtml .= '</div>';
            
            // Script Preview
            $googleHelperHtml .= '<div id="script-preview" class="well well-sm" style="display:none;">';
            $googleHelperHtml .= '<div class="form-group">';
            $googleHelperHtml .= '<label><i class="fa fa-code"></i> Generiertes Skript:</label>';
            $googleHelperHtml .= '<div class="input-group">';
            $googleHelperHtml .= '<pre id="preview-content" style="background: #f8f8f8; padding: 10px; margin: 0; max-height: 150px; overflow-y: auto;"></pre>';
            $googleHelperHtml .= '<div class="input-group-btn">';
            $googleHelperHtml .= '<button type="button" id="copy-preview-script" class="btn btn-primary" title="In Zwischenablage kopieren">';
            $googleHelperHtml .= '<i class="fa fa-copy"></i> Kopieren';
            $googleHelperHtml .= '</button>';
            $googleHelperHtml .= '</div>';
            $googleHelperHtml .= '</div>';
            $googleHelperHtml .= '</div>';
            $googleHelperHtml .= '</div>';
            
            $googleHelperHtml .= '</div>';
            $googleHelperHtml .= '</div>';
            $googleHelperHtml .= '</div>';
            
            $field = $form->addRawField($googleHelperHtml);
            
            $field = $form->addTextAreaField('script');
            $field->setAttributes(['class' => 'form-control codemirror', 'name' => $field->getAttribute('name'), 'data-codemirror-mode' => 'text/html']);
            $field->setLabel($addon->i18n('consent_manager_cookiegroup_scripts'));
            $field->setNotice($addon->i18n('consent_manager_cookiegroup_scripts_notice'));

            $field = $form->addTextAreaField('script_unselect');
            $field->setAttributes(['class' => 'form-control codemirror', 'name' => $field->getAttribute('name'), 'data-codemirror-mode' => 'text/html']);
            $field->setLabel($addon->i18n('consent_manager_cookiegroup_scripts_unselect'));
            $field->setNotice($addon->i18n('consent_manager_cookiegroup_scripts_notice'));
        } else {
            $form->addRawField(consent_manager_rex_form::getFakeTextarea($addon->i18n('consent_manager_cookiegroup_scripts'), $form->getSql()->getValue('script')));
            $form->addRawField(consent_manager_rex_form::getFakeTextarea($addon->i18n('consent_manager_cookiegroup_scripts_unselect'), $form->getSql()->getValue('script_unselect')));
        }
    }
    if ('add' === $func) {
        if ($clang_id === rex_clang::getStartId() || !$form->isEditMode()) {
            // Google Consent Mode v2 Helper VOR den Script-Feldern
            $googleHelperHtml = '';
            $googleHelperHtml .= '<div id="google-consent-helper-panel" class="panel panel-info">';
            $googleHelperHtml .= '<div class="panel-heading">';
            $googleHelperHtml .= '<h4 class="panel-title">';
            $googleHelperHtml .= '<i class="fa fa-google"></i> Google Consent Mode v2 Helper ';
            $googleHelperHtml .= '<button type="button" id="google-helper-toggle" class="btn btn-xs btn-default pull-right">';
            $googleHelperHtml .= '<i class="fa fa-chevron-down"></i> Helper einblenden';
            $googleHelperHtml .= '</button>';
            $googleHelperHtml .= '<div class="clearfix"></div>';
            $googleHelperHtml .= '</h4>';
            $googleHelperHtml .= '</div>';
            
            $googleHelperHtml .= '<div id="google-consent-helper-content" class="collapse">';
            $googleHelperHtml .= '<div class="panel-body">';
            
            // Messages Container
            $googleHelperHtml .= '<div id="google-helper-messages"></div>';
            
            $googleHelperHtml .= '<p class="help-block">';
            $googleHelperHtml .= '<i class="fa fa-info-circle"></i> ';
            $googleHelperHtml .= 'Automatische Generierung von <code>gtag("consent", "update", {...})</code> Skripten für bekannte Services.';
            $googleHelperHtml .= '</p>';
            
            // Service Auswahl
            $googleHelperHtml .= '<div class="form-group">';
            $googleHelperHtml .= '<label for="google-helper-service"><i class="fa fa-cog"></i> Service-Typ:</label>';
            $googleHelperHtml .= '<select id="google-helper-service" class="form-control">';
            $googleHelperHtml .= '<option value="">-- Service auswählen --</option>';
            $googleHelperHtml .= '<option value="analytics">Analytics (analytics_storage)</option>';
            $googleHelperHtml .= '<option value="google-analytics">Google Analytics (analytics_storage)</option>';
            $googleHelperHtml .= '<option value="google-analytics-4">Google Analytics 4 (analytics_storage)</option>';
            $googleHelperHtml .= '<option value="matomo">Matomo (analytics_storage)</option>';
            $googleHelperHtml .= '<option value="adwords">Google AdWords (ad_storage, ad_user_data, ad_personalization)</option>';
            $googleHelperHtml .= '<option value="google-ads">Google Ads (ad_storage, ad_user_data, ad_personalization)</option>';
            $googleHelperHtml .= '<option value="facebook-pixel">Facebook Pixel (ad_storage, ad_user_data, ad_personalization)</option>';
            $googleHelperHtml .= '<option value="youtube">YouTube (ad_storage, personalization_storage)</option>';
            $googleHelperHtml .= '<option value="google-maps">Google Maps (functionality_storage, personalization_storage)</option>';
            $googleHelperHtml .= '</select>';
            $googleHelperHtml .= '<small class="help-block">Wird automatisch basierend auf dem Service-Namen vorgeschlagen</small>';
            $googleHelperHtml .= '</div>';
            
            // Buttons
            $googleHelperHtml .= '<div class="form-group">';
            $googleHelperHtml .= '<div class="btn-group btn-group-justified" role="group">';
            $googleHelperHtml .= '<div class="btn-group" role="group">';
            $googleHelperHtml .= '<button type="button" id="generate-consent-script" class="btn btn-success">';
            $googleHelperHtml .= '<i class="fa fa-check"></i> Consent-Skript';
            $googleHelperHtml .= '</button>';
            $googleHelperHtml .= '</div>';
            $googleHelperHtml .= '<div class="btn-group" role="group">';
            $googleHelperHtml .= '<button type="button" id="generate-revoke-script" class="btn btn-warning">';
            $googleHelperHtml .= '<i class="fa fa-times"></i> Widerruf-Skript';
            $googleHelperHtml .= '</button>';
            $googleHelperHtml .= '</div>';
            $googleHelperHtml .= '</div>';
            $googleHelperHtml .= '</div>';
            
            // Script Preview
            $googleHelperHtml .= '<div id="script-preview" class="well well-sm" style="display:none;">';
            $googleHelperHtml .= '<div class="form-group">';
            $googleHelperHtml .= '<label><i class="fa fa-code"></i> Generiertes Skript:</label>';
            $googleHelperHtml .= '<div class="input-group">';
            $googleHelperHtml .= '<pre id="preview-content" style="background: #f8f8f8; padding: 10px; margin: 0; max-height: 150px; overflow-y: auto;"></pre>';
            $googleHelperHtml .= '<div class="input-group-btn">';
            $googleHelperHtml .= '<button type="button" id="copy-preview-script" class="btn btn-primary" title="In Zwischenablage kopieren">';
            $googleHelperHtml .= '<i class="fa fa-copy"></i> Kopieren';
            $googleHelperHtml .= '</button>';
            $googleHelperHtml .= '</div>';
            $googleHelperHtml .= '</div>';
            $googleHelperHtml .= '</div>';
            $googleHelperHtml .= '</div>';
            
            $googleHelperHtml .= '</div>';
            $googleHelperHtml .= '</div>';
            $googleHelperHtml .= '</div>';
            
            $field = $form->addRawField($googleHelperHtml);
            
            $field = $form->addTextAreaField('script');
            $field->setAttributes(['class' => 'form-control codemirror', 'name' => $field->getAttribute('name'), 'data-codemirror-mode' => 'text/html']);
            $field->setLabel($addon->i18n('consent_manager_cookiegroup_scripts'));
            $field->setNotice($addon->i18n('consent_manager_cookiegroup_scripts_notice'));

            $field = $form->addTextAreaField('script_unselect');
            $field->setAttributes(['class' => 'form-control codemirror', 'name' => $field->getAttribute('name'), 'data-codemirror-mode' => 'text/html']);
            $field->setLabel($addon->i18n('consent_manager_cookiegroup_scripts_unselect'));
            $field->setNotice($addon->i18n('consent_manager_cookiegroup_scripts_notice'));
        }
    }

    $field = $form->addTextAreaField('placeholder_text');
    $field->setLabel($addon->i18n('consent_manager_cookie_placeholder_text'));
    $field = $form->addMediaField('placeholder_image');
    $field->setLabel($addon->i18n('consent_manager_cookie_placeholder_image'));

    $title = $form->isEditMode() ? $addon->i18n('consent_manager_cookie_edit') : $addon->i18n('consent_manager_cookie_add');
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
    $sql = 'SELECT pid,uid,service_name,provider FROM ' . $table . ' WHERE clang_id = ' . $clang_id . ' ORDER BY uid';

    $list = rex_list::factory($sql, 100, '', $listDebug);
    $list->addParam('page', rex_be_controller::getCurrentPage());
    $list->addTableAttribute('class', 'table table-striped table-hover consent_manager-table consent_manager-table-cookie');
    $list->addTableAttribute('id', 'consent_manager-table-cookie');

    $tdIcon = '<i class="fa fa-coffee"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '"' . rex::getAccesskey(rex_i18n::msg('add'), 'add') . '><i class="rex-icon rex-icon-add"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'pid' => '###pid###']);

    $list->removeColumn('pid');
    $list->setColumnLabel('uid', $addon->i18n('consent_manager_uid'));
    $list->setColumnParams('uid', ['func' => 'edit', 'pid' => '###pid###']);
    $list->setColumnSortable('uid');

    $list->setColumnLabel('service_name', $addon->i18n('consent_manager_cookie_service_name'));
    $list->setColumnSortable('service_name');

    $list->setColumnLabel('provider', $addon->i18n('consent_manager_cookie_provider'));
    $list->setColumnSortable('provider');

    $list->addColumn(rex_i18n::msg('function'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('function'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('function'), ['pid' => '###pid###', 'func' => 'edit', 'start' => rex_request('start', 'string')]);

    $list->addColumn(rex_i18n::msg('delete'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
    $list->setColumnLayout(rex_i18n::msg('delete'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('delete'), ['pid' => '###pid###', 'func' => 'delete'] + $csrf->getUrlParams());
    $list->addLinkAttribute(rex_i18n::msg('delete'), 'onclick', 'return confirm(\' ###service_name### ' . rex_i18n::msg('delete') . ' ?\')');

    $content = $list->get();

    $fragment = new rex_fragment();
    $fragment->setVar('title', $addon->i18n('consent_manager_cookies'));
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}
