<?php

$addon = rex_addon::get('consent_manager');

$form = new rex_config_form('consent_manager');

$field = $form->addCheckboxField('hidebodyscrollbar');
$field->setLabel($addon->i18n('consent_manager_config_hidebodyscrollbar'));
$field->addOption($addon->i18n('consent_manager_config_hidebodyscrollbar'), 1);
$field->setNotice($addon->i18n('consent_manager_config_hidebodyscrollbar_desc'));

$field = $form->addTextField('lifespan');
$field->setLabel($addon->i18n('consent_manager_config_lifespan_label'));
$field->setAttribute('type', 'number');
$field->setAttribute('step', '1');
$field->setAttribute('pattern', '[0-9]*');
$field->setAttribute('placeholder', '365');
$field->setNotice($addon->i18n('consent_manager_config_lifespan_notice'));

$field = $form->addTextField('skip_consent');
$field->setLabel($addon->i18n('consent_manager_config_token_label'));
$field->setNotice($addon->i18n('consent_manager_config_token_notice'));

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('consent_manager_config_title'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');
