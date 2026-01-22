<?php

use FriendsOfRedaxo\ConsentManager\RexFormSupport;

$showlist = true;
$id = rex_request::request('id', 'int', 0);
$func = rex_request::request('func', 'string');
$csrf = rex_csrf_token::factory('consent_manager_domain');
$table = rex::getTable('consent_manager_domain');
$msg = '';
if ('delete' === $func) {
    $db = rex_sql::factory();
    $db->setTable($table);
    $db->setWhere('id = :id', ['id' => $id]);
    $db->delete();
    $msg = rex_view::success(rex_i18n::msg('consent_manager_successfully_deleted'));
} elseif ('add' === $func || 'edit' === $func) {
    $formDebug = false;
    $showlist = false;
    $form = rex_form::factory($table, '', 'id = ' . $id, 'post', $formDebug);
    $form->addParam('id', $id);
    $form->addParam('sort', rex_request::request('sort', 'string', ''));
    $form->addParam('sorttype', rex_request::request('sorttype', 'string', ''));
    $form->addParam('start', rex_request::request('start', 'int', 0));
    $form->setApplyUrl(rex_url::currentBackendPage());

    $field = $form->addTextField('uid');
    $field->setLabel(rex_i18n::msg('consent_manager_domain'));
    $field->getValidator()->add('notEmpty', rex_i18n::msg('consent_manager_domain_empty_msg'));
    $field->getValidator()->add('custom', rex_i18n::msg('consent_manager_domain_malformed_msg'), RexFormSupport::validateHostname(...));
    $field->getValidator()->add('custom', 'Domain muss in Kleinbuchstaben eingegeben werden (z.B. "example.com" statt "Example.com")', static function ($value) {
        return RexFormSupport::validateLowercase($value);
    });
    $field->setNotice('Domain ohne Protokoll eingeben (z.B. "example.com"). Bitte nur Kleinbuchstaben verwenden.');

    $field = $form->addLinkmapField('privacy_policy');
    $field->setLabel(rex_i18n::msg('consent_manager_domain_privacy_policy')); /** @phpstan-ignore-line */
    $field->getValidator()->add('notEmpty', rex_i18n::msg('consent_manager_domain_privacy_policy_empty_msg')); /** @phpstan-ignore-line */

    $field = $form->addLinkmapField('legal_notice');
    $field->setLabel(rex_i18n::msg('consent_manager_domain_legal_notice')); /** @phpstan-ignore-line */
    $field->getValidator()->add('notEmpty', rex_i18n::msg('consent_manager_domain_legal_notic_empty_msg')); /** @phpstan-ignore-line */

    // Theme Selector
    $field = $form->addSelectField('theme');
    $field->setLabel('🎨 Custom Theme');
    $select = $field->getSelect();
    $select->addOption('Standard (aus globaler Konfiguration)', '');
    
    // Get available themes
    $cmtheme = new FriendsOfRedaxo\ConsentManager\Theme();
    $addon = rex_addon::get('consent_manager');
    
    // Project themes
    if (rex_addon::exists('project')) {
        $projectThemes = (array) glob(rex_addon::get('project')->getPath('consent_manager_themes/consent_manager_frontend*.scss'));
        natsort($projectThemes);
        foreach ($projectThemes as $themefile) {
            $themeid = 'project:' . basename((string) $themefile);
            $theme_options = $cmtheme->getThemeInformation($themeid);
            if (count($theme_options) > 0) {
                $select->addOption('📁 ' . $theme_options['name'], $themeid);
            }
        }
    }
    
    // Addon themes
    $addonThemes = (array) glob($addon->getPath('scss/consent_manager_frontend*.scss'));
    natsort($addonThemes);
    foreach ($addonThemes as $themefile) {
        $themeid = basename((string) $themefile);
        $theme_options = $cmtheme->getThemeInformation($themeid);
        if (count($theme_options) > 0) {
            $select->addOption('🎨 ' . $theme_options['name'], $themeid);
        }
    }
    
    $field->setNotice('Überschreibt das globale Theme nur für diese Domain. Leer lassen für Standard-Theme aus der Konfiguration.');

    // Google Consent Mode v2 Configuration
    $field = $form->addSelectField('google_consent_mode_enabled');
    $field->setLabel(rex_i18n::msg('consent_manager_google_mode_title'));
    $select = $field->getSelect();
    $select->addOption(rex_i18n::msg('consent_manager_google_mode_disabled'), 'disabled');
    $select->addOption(rex_i18n::msg('consent_manager_google_mode_auto'), 'auto');
    $select->addOption(rex_i18n::msg('consent_manager_google_mode_manual'), 'manual');
    $field->setNotice(rex_i18n::msg('consent_manager_google_mode_notice'));

    // Debug Mode Configuration
    $field = $form->addSelectField('google_consent_mode_debug');
    $field->setLabel('🔍 Debug-Modus');
    $select = $field->getSelect();
    $select->addOption('Deaktiviert', '0');
    $select->addOption('Aktiviert', '1');
    $field->setNotice('Debug-Panel im Frontend anzeigen. Zeigt Cookie-Status und Consent-Informationen für angemeldete Backend-Benutzer an.');

    // Inline-Only Mode Configuration
    $field = $form->addSelectField('inline_only_mode');
    $field->setLabel(rex_i18n::msg('consent_manager_domain_inline_only_mode'));
    $select = $field->getSelect();
    $select->addOption(rex_i18n::msg('consent_manager_domain_inline_only_mode_disabled'), '0');
    $select->addOption(rex_i18n::msg('consent_manager_domain_inline_only_mode_enabled'), '1');
    $field->setNotice(rex_i18n::msg('consent_manager_domain_inline_only_mode_notice'));

    // oEmbed / CKE5 Video Configuration - nur anzeigen wenn CKE5 verfügbar ist
    if (rex_addon::exists('cke5') && rex_addon::get('cke5')->isAvailable()) {
        $field = $form->addSelectField('oembed_enabled');
        $field->setLabel('🎬 CKE5 oEmbed Integration');
        $select = $field->getSelect();
        $select->addOption('Aktiviert (automatische Umwandlung)', '1');
        $select->addOption('Deaktiviert (keine automatische Umwandlung)', '0');
        $field->setNotice('Legt fest, ob CKE5 oEmbed-Tags (YouTube, Vimeo) automatisch in Consent-Blocker umgewandelt werden.');

        $field = $form->addTextField('oembed_video_width');
        $field->setLabel('📐 oEmbed Video-Breite (px)');
        $field->setNotice('Standard-Breite für CKE5 oEmbed-Videos (Default: 640)');

        $field = $form->addTextField('oembed_video_height');
        $field->setLabel('📏 oEmbed Video-Höhe (px)');
        $field->setNotice('Standard-Höhe für CKE5 oEmbed-Videos (Default: 360)');

        $field = $form->addSelectField('oembed_show_allow_all');
        $field->setLabel('🔘 oEmbed Drei-Button-Variante');
        $select = $field->getSelect();
        $select->addOption('Zwei Buttons (Einmal laden, Alle Einstellungen)', '0');
        $select->addOption('Drei Buttons (Einmal laden, Alle zulassen, Alle Einstellungen)', '1');
        $field->setNotice('Drei-Button-Variante zeigt zusätzlich "Alle zulassen" Button zum sofortigen Freischalten aller Services einer Gruppe.');
    }

    $title = $form->isEditMode() ? rex_i18n::msg('consent_manager_domain_edit') : rex_i18n::msg('consent_manager_domain_add');
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

    // oembed_enabled nur laden wenn CKE5 verfügbar ist
    $oembedColumn = (rex_addon::exists('cke5') && rex_addon::get('cke5')->isAvailable()) ? ', oembed_enabled' : '';
    $sql = 'SELECT id, uid, google_consent_mode_enabled, google_consent_mode_debug, inline_only_mode' . $oembedColumn . ' FROM ' . $table . ' ORDER BY uid ASC';

    $list = rex_list::factory($sql, 100, '', $listDebug);
    $list->addParam('page', rex_be_controller::getCurrentPage());
    $list->addTableAttribute('class', 'table table-striped table-hover consent_manager-table consent_manager-table-cookiegroup');

    $list->removeColumn('id');
    $list->setColumnLabel('uid', rex_i18n::msg('consent_manager_domain'));
    $list->setColumnParams('uid', ['func' => 'edit', 'id' => '###id###']);
    $list->setColumnSortable('uid');

    // Google Consent Mode Status Spalte
    $list->setColumnLabel('google_consent_mode_enabled', 'Google Consent Mode v2');
    $list->setColumnFormat('google_consent_mode_enabled', 'custom', static function ($params) {
        $value = $params['value'];
        switch ($value) {
            case 'disabled':
                return '<span class="label label-default">❌ ' . rex_i18n::msg('consent_manager_google_mode_disabled') . '</span>';
            case 'auto':
                return '<span class="label label-success">🔄 ' . rex_i18n::msg('consent_manager_google_mode_auto') . '</span>';
            case 'manual':
                return '<span class="label label-info">⚙️ ' . rex_i18n::msg('consent_manager_google_mode_manual') . '</span>';
            default:
                return '<span class="label label-default">❌ ' . rex_i18n::msg('consent_manager_google_mode_disabled') . '</span>';
        }
    });

    // Debug Status Spalte
    $list->setColumnLabel('google_consent_mode_debug', '🔍 Debug');
    $list->setColumnFormat('google_consent_mode_debug', 'custom', static function ($params) {
        $value = (int) $params['value'];
        if (1 === $value) {
            return '<span class="label label-success"><i class="fa fa-bug" style="color: #000; margin-right: 5px;"></i>Aktiv</span>';
        }
        return '<span class="label label-default"><i class="fa fa-bug" style="color: #000; margin-right: 5px;"></i>Deaktiviert</span>';
    });

    // Inline-Only Mode Status Spalte
    $list->setColumnLabel('inline_only_mode', '📱 Inline-Only');
    $list->setColumnFormat('inline_only_mode', 'custom', static function ($params) {
        $value = (int) $params['value'];
        if (1 === $value) {
            return '<span class="label label-info"><i class="fa fa-hand-pointer-o" style="margin-right: 5px;"></i>' . rex_i18n::msg('consent_manager_domain_inline_only_mode_enabled') . '</span>';
        }
        return '<span class="label label-default"><i class="fa fa-window-maximize" style="margin-right: 5px;"></i>' . rex_i18n::msg('consent_manager_domain_inline_only_mode_disabled') . '</span>';
    });

    // oEmbed Status Spalte - nur anzeigen wenn CKE5 verfügbar ist
    if (rex_addon::exists('cke5') && rex_addon::get('cke5')->isAvailable()) {
        $list->setColumnLabel('oembed_enabled', '🎬 oEmbed');
        $list->setColumnFormat('oembed_enabled', 'custom', static function ($params) {
            $value = (int) ($params['value'] ?? 1); // Default: aktiviert
            if (1 === $value) {
                return '<span class="label label-success">✅ Aktiv</span>';
            }
            return '<span class="label label-default">🚫 Deaktiviert</span>';
        });
    }

    $tdIcon = '<i class="fa fa-coffee"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '"' . rex::getAccesskey(rex_i18n::msg('add'), 'add') . '><i class="rex-icon rex-icon-add"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'id' => '###id###']);

    $list->addColumn(rex_i18n::msg('function'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('function'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('function'), ['id' => '###id###', 'func' => 'edit', 'start' => rex_request::request('start', 'string')]);

    $list->addColumn(rex_i18n::msg('delete'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
    $list->setColumnLayout(rex_i18n::msg('delete'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('delete'), ['id' => '###id###', 'func' => 'delete'] + $csrf->getUrlParams());
    $list->addLinkAttribute(rex_i18n::msg('delete'), 'onclick', 'return confirm(\' ###uid### ' . rex_i18n::msg('delete') . ' ?\')');

    $content = $list->get();

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('consent_manager_domains'));
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}
