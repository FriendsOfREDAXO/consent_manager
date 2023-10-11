<?php

$addon = rex_addon::get('consent_manager');

$form = rex_config_form::factory(strval($addon->getPackageId()));

$form->addFieldset($addon->i18n('consent_manager_config_legend'));

/* Entfällt vorerst siehe https://github.com/FriendsOfREDAXO/consent_manager/issues/149

$field = $form->addCheckboxField('outputcss');
$field->addOption($addon->i18n('consent_manager_config_css'), 1);

$field = $form->addRawField('<p><strong>'.$addon->i18n('consent_manager_config_css').'</strong></p><p>'.$addon->i18n('consent_manager_config_css_desc').'</p>');

$field = $form->addRawField('<hr>');
*/

$field = $form->addCheckboxField('outputowncss');
$field->addOption($addon->i18n('consent_manager_config_owncss'), 1);

$field = $form->addRawField('<p><strong>'.$addon->i18n('consent_manager_config_owncss').'</strong></p><p>'.$addon->i18n('consent_manager_config_owncss_desc').'</p>');

$field = $form->addRawField('<hr>');

$field = $form->addCheckboxField('hidebodyscrollbar');
$field->addOption($addon->i18n('consent_manager_config_hidebodyscrollbar'), 1);

$field = $form->addRawField('<p><strong>'.$addon->i18n('consent_manager_config_hidebodyscrollbar').'</strong></p><p>'.$addon->i18n('consent_manager_config_hidebodyscrollbar_desc').'</p>');

$field = $form->addRawField('<hr>');

$field = $form->addTextField('lifespan');
$field->setLabel($addon->i18n('consent_manager_config_lifespan_label'));
$field->setAttribute('type', 'number');
$field->setAttribute('step', '1');
$field->setAttribute('pattern', '[0-9]*');
$field->setAttribute('placeholder', '365');
$field->setNotice($addon->i18n('consent_manager_config_lifespan_notice'));

$form->addFieldset($addon->i18n('consent_manager_config_token_legend'));

$field = $form->addTextField('skip_consent');
$field->setLabel($addon->i18n('consent_manager_config_token_label'));
$field->setNotice($addon->i18n('consent_manager_config_token_notice'));

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('consent_manager_config_title'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');

if ('' !== rex_post('_csrf_token', 'string', '')) {
    consent_manager_theme::generateDefaultAssets();
    consent_manager_theme::copyAllAssets();
}
