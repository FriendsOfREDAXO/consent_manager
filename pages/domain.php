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

    // Auto-Inject Configuration
    $field = $form->addSelectField('auto_inject');
    $field->setLabel('🚀 Automatische Frontend-Einbindung');
    $select = $field->getSelect();
    $select->addOption('Deaktiviert (manuelle Einbindung erforderlich)', '0');
    $select->addOption('Aktiviert (automatische Einbindung im Frontend)', '1');
    $field->setNotice('Wenn aktiviert, wird das Consent Manager Script automatisch im Frontend eingebunden. Keine manuelle Integration im Template erforderlich.');

    // Theme als Hidden Field (wird in Sidebar gesteuert)
    $field = $form->addHiddenField('theme');

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
    $formContent = $form->get();
    
    // Theme-Sidebar erstellen
    $addon = rex_addon::get('consent_manager');
    $cmtheme = new \FriendsOfRedaxo\ConsentManager\Theme();
    $currentTheme = $form->isEditMode() ? $form->getSql()->getValue('theme') : '';
    $previewId = 'theme-preview-' . uniqid();
    $initialDisplay = !empty($currentTheme) ? 'block' : 'none';
    $previewBaseUrl = rex_url::backendPage('consent_manager/theme');
    
    // Theme-Optionen sammeln
    $themeOptions = '<option value="">Standard (globales Theme verwenden)</option>';
    
    $themes = (array) glob($addon->getPath('scss/themes/*.scss'));
    $legacyThemes = (array) glob($addon->getPath('scss/consent_manager_frontend*.scss'));
    $themes = array_merge($themes, $legacyThemes);
    natsort($themes);
    
    foreach ($themes as $themefile) {
        $isLegacy = strpos($themefile, '/themes/') === false;
        $themeid = $isLegacy ? basename((string) $themefile) : 'themes/' . basename((string) $themefile);
        $theme_options = $cmtheme->getThemeInformation($themeid);
        if (count($theme_options) > 0) {
            $label = $theme_options['name'] . ' (' . $theme_options['style'] . ')';
            $selected = ($currentTheme === $themeid) ? ' selected' : '';
            $themeOptions .= '<option value="' . rex_escape($themeid) . '"' . $selected . '>' . rex_escape($label) . '</option>';
        }
    }
    
    if (true === rex_addon::exists('project')) {
        $projectThemes = (array) glob(rex_addon::get('project')->getPath('consent_manager_themes/*.scss'));
        natsort($projectThemes);
        foreach ($projectThemes as $themefile) {
            $themeid = 'project:' . basename((string) $themefile);
            $theme_options = $cmtheme->getThemeInformation($themeid);
            if (count($theme_options) > 0) {
                $label = '⭐ ' . $theme_options['name'] . ' (Custom)';
                $selected = ($currentTheme === $themeid) ? ' selected' : '';
                $themeOptions .= '<option value="' . rex_escape($themeid) . '"' . $selected . '>' . rex_escape($label) . '</option>';
            }
        }
    }
    
    $sidebar = '
    <style>
    .cm-domain-layout {
        display: flex;
        gap: 20px;
    }
    .cm-domain-main {
        flex: 1;
        min-width: 0;
    }
    .cm-domain-sidebar {
        width: 320px;
        flex-shrink: 0;
    }
    .cm-sidebar-panel {
        background: rgba(255,255,255,0.1);
        border: 2px solid rgba(0,0,0,0.1);
        border-radius: 6px;
        padding: 18px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05),
                    0 1px 3px rgba(0,0,0,0.08);
    }
    .cm-sidebar-title {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 12px;
        opacity: 0.9;
        letter-spacing: 0.3px;
    }
    .cm-theme-select {
        width: 100%;
        padding: 8px;
        border-radius: 4px;
        margin-bottom: 15px;
    }
    .cm-domain-theme-preview {
        display: ' . $initialDisplay . ';
    }
    .cm-domain-theme-preview-title {
        font-weight: 600;
        margin-bottom: 10px;
        font-size: 12px;
        opacity: 0.8;
        letter-spacing: 0.2px;
    }
    .cm-domain-theme-preview-container {
        position: relative;
        width: 100%;
        padding-bottom: 62.5%;
        background: rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 4px;
        overflow: hidden;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.06);
        margin-bottom: 12px;
    }
    .cm-domain-theme-preview-iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 1440px;
        height: 900px;
        border: 0;
        transform-origin: 0 0;
        pointer-events: none;
    }
    @media (max-width: 992px) {
        .cm-domain-layout {
            flex-direction: column;
        }
        .cm-domain-sidebar {
            width: 100%;
        }
    }
    </style>
    <div class="cm-sidebar-panel">
        <div class="cm-sidebar-title">' . rex_i18n::msg('consent_manager_domain_theme_title') . '</div>
        <select class="cm-theme-select" id="cm-theme-select-' . $previewId . '">
            ' . $themeOptions . '
        </select>
        <p style="font-size: 11px; margin: 0 0 15px 0;">' . rex_i18n::msg('consent_manager_domain_theme_notice') . '</p>
        
        <div class="cm-domain-theme-preview" id="' . $previewId . '">
            <div class="cm-domain-theme-preview-title">' . rex_i18n::msg('consent_manager_domain_theme_preview_title') . '</div>
            <div class="cm-domain-theme-preview-container">
                <iframe
                    class="cm-domain-theme-preview-iframe"
                    id="' . $previewId . '-iframe"
                    sandbox="allow-scripts allow-same-origin"
                ></iframe>
            </div>
            <a href="#" id="' . $previewId . '-link" target="_blank" class="btn btn-xs btn-default btn-block" style="font-size: 11px;">
                <i class="fa fa-external-link"></i> ' . rex_i18n::msg('consent_manager_domain_theme_preview_fullscreen') . '
            </a>
        </div>
    </div>
    <script nonce="' . rex_response::getNonce() . '">
    (function() {
        var previewContainer = document.getElementById("' . $previewId . '");
        var previewIframe = document.getElementById("' . $previewId . '-iframe");
        var previewLink = document.getElementById("' . $previewId . '-link");
        var themeSelect = document.getElementById("cm-theme-select-' . $previewId . '");
        var hiddenThemeInput = document.querySelector(\'input[type="hidden"][id*="theme"]\');
        var baseUrl = "' . $previewBaseUrl . '";
        
        function scalePreview() {
            if (previewIframe && previewContainer) {
                var container = previewContainer.querySelector(".cm-domain-theme-preview-container");
                if (container) {
                    var containerWidth = container.offsetWidth;
                    var scale = containerWidth / 1440;
                    previewIframe.style.transform = "scale(" + scale + ")";
                }
            }
        }
        
        function updatePreview() {
            var theme = themeSelect ? themeSelect.value : "";
            
            // Hidden Input synchronisieren
            if (hiddenThemeInput) {
                hiddenThemeInput.value = theme;
            }
            
            if (theme) {
                var url = baseUrl + "&preview=" + encodeURIComponent(theme);
                previewIframe.src = url;
                previewLink.href = url;
                previewContainer.style.display = "block";
                setTimeout(scalePreview, 100);
            } else {
                previewIframe.src = "";
                previewContainer.style.display = "none";
            }
        }
        
        // Wait for rex:ready if PJAX is active
        function init() {
            // Suche erneut nach hidden input (könnte durch PJAX neu geladen sein)
            hiddenThemeInput = document.querySelector(\'input[type="hidden"][id*="theme"]\');
            if (themeSelect) {
                updatePreview();
                themeSelect.addEventListener("change", updatePreview);
                window.addEventListener("resize", scalePreview);
            }
        }
        
        // Nur entweder direkt ODER im rex:ready-Handler aufrufen, nicht beides
        if (typeof jQuery !== "undefined" && jQuery(document).data("pjax")) {
            jQuery(document).on("rex:ready", init);
        } else {
            init();
        }
    })();
    </script>';
    
    // 2-Spalten-Layout
    $content = '
    <div class="cm-domain-layout">
        <div class="cm-domain-main">' . $formContent . '</div>
        <div class="cm-domain-sidebar">' . $sidebar . '</div>
    </div>';

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

    // Auto-Inject Status Spalte
    $list->setColumnLabel('auto_inject', '🚀 Auto-Inject');
    $list->setColumnFormat('auto_inject', 'custom', static function ($params) {
        $value = (int) $params['value'];
        if (1 === $value) {
            return '<span class="label label-success"><i class="fa fa-magic" style="margin-right: 5px;"></i>Aktiviert</span>';
        }
        return '<span class="label label-default"><i class="fa fa-code" style="margin-right: 5px;"></i>Manuell</span>';
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
