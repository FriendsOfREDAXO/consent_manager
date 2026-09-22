<?php

$addon = rex_addon::get('consent_manager');

$form = rex_config_form::factory((string) $addon->getPackageId());

// --- Ausgabe-Einstellungen Frontend ---
$form->addRawField('<fieldset><legend>' . rex_i18n::msg('consent_manager_config_legend') . '</legend>');

// CSS Framework Modus
$field = $form->addSelectField('css_framework_mode');
$field->setLabel(rex_i18n::msg('consent_manager_config_css_framework_mode'));
$select = $field->getSelect();
$select->addOption(rex_i18n::msg('consent_manager_config_css_framework_mode_none'), '');
$select->addOption(rex_i18n::msg('consent_manager_config_css_framework_mode_uikit3'), 'uikit3');
$select->addOption(rex_i18n::msg('consent_manager_config_css_framework_mode_bootstrap5'), 'bootstrap5');
$select->addOption(rex_i18n::msg('consent_manager_config_css_framework_mode_tailwind'), 'tailwind');
$select->addOption(rex_i18n::msg('consent_manager_config_css_framework_mode_bulma'), 'bulma');
$field->setNotice(rex_i18n::msg('consent_manager_config_css_framework_mode_notice'));
$field->setAttribute('id', 'css-framework-mode-select');

// CSS Output Einstellung
$field = $form->addCheckboxField('outputowncss');
$field->setLabel(rex_i18n::msg('consent_manager_config_owncss'));
$field->addOption(rex_i18n::msg('consent_manager_config_owncss'), 1);
$field->setNotice(rex_i18n::msg('consent_manager_config_owncss_desc'));
$field->setAttribute('id', 'output-own-css-container');

// Body Scrollbar Einstellung
$field = $form->addCheckboxField('hidebodyscrollbar');
$field->setLabel(rex_i18n::msg('consent_manager_config_hidebodyscrollbar'));
$field->addOption(rex_i18n::msg('consent_manager_config_hidebodyscrollbar'), 1);
$field->setNotice(rex_i18n::msg('consent_manager_config_hidebodyscrollbar_desc'));

// Modal-Backdrop Einstellung
$field = $form->addSelectField('backdrop');
$field->setLabel(rex_i18n::msg('consent_manager_config_backdrop'));
$select = $field->getSelect();
$select->addOption(rex_i18n::msg('consent_manager_config_backdrop_enabled'), 1);
$select->addOption(rex_i18n::msg('consent_manager_config_backdrop_disabled'), 0);
$field->setNotice(rex_i18n::msg('consent_manager_config_backdrop_desc'));

$form->addRawField('</fieldset>');

// --- Framework-Optionen (per JS nur bei gewähltem Framework sichtbar) ---
$form->addRawField('<fieldset id="framework-options-panel"><legend>' . rex_i18n::msg('consent_manager_config_framework_legend') . '</legend>');

$field = $form->addSelectField('css_framework_shadow');
$field->setLabel(rex_i18n::msg('consent_manager_config_framework_shadow'));
$select = $field->getSelect();
$select->addOption(rex_i18n::msg('consent_manager_config_framework_shadow_none'), 'none');
$select->addOption(rex_i18n::msg('consent_manager_config_framework_shadow_small'), 'small');
$select->addOption(rex_i18n::msg('consent_manager_config_framework_shadow_large'), 'large');

$field = $form->addSelectField('css_framework_rounded');
$field->setLabel(rex_i18n::msg('consent_manager_config_framework_rounded'));
$select = $field->getSelect();
$select->addOption(rex_i18n::msg('consent_manager_config_framework_rounded_no'), '0');
$select->addOption(rex_i18n::msg('consent_manager_config_framework_rounded_yes'), '1');

$form->addRawField('</fieldset>');

$title = rex_i18n::msg('consent_manager_config_display');
require __DIR__ . '/config.shared.php';
