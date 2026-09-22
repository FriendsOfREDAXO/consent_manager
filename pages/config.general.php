<?php

$addon = rex_addon::get('consent_manager');

$form = rex_config_form::factory((string) $addon->getPackageId());

// --- Funktion & Datenschutz ---
$form->addRawField('<fieldset><legend>' . rex_i18n::msg('consent_manager_config_function_legend') . '</legend>');

// Inline-Only Modus
$field = $form->addCheckboxField('inline_only_mode');
$field->setLabel(rex_i18n::msg('consent_manager_config_inline_only_mode'));
$field->addOption(rex_i18n::msg('consent_manager_config_inline_only_mode'), 1);
$field->setNotice(rex_i18n::msg('consent_manager_config_inline_only_mode_desc'));

// Inline Consent Session Scope
$field = $form->addCheckboxField('inline_consent_session_scope');
$field->setLabel(rex_i18n::msg('consent_manager_config_inline_consent_session_scope'));
$field->addOption(rex_i18n::msg('consent_manager_config_inline_consent_session_scope_enable'), 1);
$field->setNotice(rex_i18n::msg('consent_manager_config_inline_consent_session_scope_desc'));

// Auto-Blocking für manuell eingefügtes HTML
$field = $form->addCheckboxField('auto_blocking_enabled');
$field->setLabel(rex_i18n::msg('consent_manager_config_auto_blocking'));
$field->addOption(rex_i18n::msg('consent_manager_config_auto_blocking_enable'), 1);
$field->setNotice(rex_i18n::msg('consent_manager_config_auto_blocking_desc'));

// Sprachspezifische Dienstzuweisung je Cookie-Gruppe
$field = $form->addCheckboxField('cookiegroup_language_custom_services_enabled');
$field->setLabel(rex_i18n::msg('consent_manager_config_cookiegroup_language_custom_services'));
$field->addOption(rex_i18n::msg('consent_manager_config_cookiegroup_language_custom_services_enable'), 1);
$field->setNotice(rex_i18n::msg('consent_manager_config_cookiegroup_language_custom_services_notice'));

// Redakteur-Hinweise
$field = $form->addTextAreaField('editorial_info');
$field->setLabel(rex_i18n::msg('consent_manager_config_editorial_info'));
$field->setAttribute('rows', '4');
$field->setNotice(rex_i18n::msg('consent_manager_config_editorial_info_notice'));

$form->addRawField('</fieldset>');

// --- Technische Details ---
$form->addRawField('<fieldset><legend>' . rex_i18n::msg('consent_manager_config_technical_legend') . '</legend>');

// Cookie Name
$field = $form->addTextField('cookie_name');
$field->setLabel(rex_i18n::msg('consent_manager_config_cookie_name_label'));
$field->setValue((string) $addon->getConfig('cookie_name', 'consentmanager'));
$field->setNotice(rex_i18n::msg('consent_manager_config_cookie_name_notice'));

// Cookie Lebensdauer
$field = $form->addTextField('lifespan');
$field->setLabel(rex_i18n::msg('consent_manager_config_lifespan_label'));
$field->setAttribute('type', 'number');
$field->setNotice(rex_i18n::msg('consent_manager_config_lifespan_notice'));

// Token Einstellungen
$field = $form->addTextField('skip_consent');
$field->setLabel(rex_i18n::msg('consent_manager_config_token_label'));
$field->setNotice(rex_i18n::msg('consent_manager_config_token_notice'));

$form->addRawField('</fieldset>');

$title = rex_i18n::msg('consent_manager_config_general');
require __DIR__ . '/config.shared.php';
