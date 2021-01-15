<?php

$addon = rex_addon::get('consent_manager');

$form = rex_config_form::factory($addon->name);

$field = $form->addFieldset($addon->i18n('consent_manager_config_legend'));


$field = $form->addCheckboxField('outputcssjs');
$field->addOption($addon->i18n('consent_manager_config_cssjs'), 1);

$field = $form->addRawField('<p><strong>'.$addon->i18n('consent_manager_config_cssjs').'</strong></p><p>'.$addon->i18n('consent_manager_config_cssjs_desc').'</p>');

$field = $form->addRawField('<hr>');

$field = $form->addCheckboxField('outputowncss');
$field->addOption($addon->i18n('consent_manager_config_owncss'), 1);

$field = $form->addRawField('<p><strong>'.$addon->i18n('consent_manager_config_owncss').'</strong></p><p>'.$addon->i18n('consent_manager_config_owncss_desc').'</p>');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('consent_manager_config_title'), false);
$fragment->setVar('body', $form->get() , false);
echo $fragment->parse('core/page/section.php');
