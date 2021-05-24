<?php

$addon = rex_addon::get('consent_manager');

$form = rex_config_form::factory($addon->getPackageId());

$field = $form->addFieldset($addon->i18n('consent_manager_config_legend'));

$field = $form->addCheckboxField('outputcss');
$field->addOption($addon->i18n('consent_manager_config_css'), 1);

$field = $form->addRawField('<p><strong>'.$addon->i18n('consent_manager_config_css').'</strong></p><p>'.$addon->i18n('consent_manager_config_css_desc').'</p>');

$field = $form->addRawField('<hr>');

$field = $form->addCheckboxField('outputowncss');
$field->addOption($addon->i18n('consent_manager_config_owncss'), 1);

$field = $form->addRawField('<p><strong>'.$addon->i18n('consent_manager_config_owncss').'</strong></p><p>'.$addon->i18n('consent_manager_config_owncss_desc').'</p>');

$field = $form->addRawField('<hr>');

$field = $form->addCheckboxField('hidebodyscrollbar');
$field->addOption($addon->i18n('consent_manager_config_hidebodyscrollbar'), 1);

$field = $form->addRawField('<p><strong>'.$addon->i18n('consent_manager_config_hidebodyscrollbar').'</strong></p><p>'.$addon->i18n('consent_manager_config_hidebodyscrollbar_desc').'</p>');

$field = $form->addFieldset($addon->i18n('consent_manager_config_token_legend'));

$field = $form->addTextField('skip_consent');
$field->setLabel($addon->i18n('consent_manager_config_token_label'), 1);
$field->setNotice($addon->i18n('consent_manager_config_token_notice'), 1);

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('consent_manager_config_title'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');
