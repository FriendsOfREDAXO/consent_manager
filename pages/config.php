<?php

use FriendsOfRedaxo\ConsentManager\CLang;
use FriendsOfRedaxo\ConsentManager\Config;
use FriendsOfRedaxo\ConsentManager\JsonSetup;
use FriendsOfRedaxo\ConsentManager\Theme;

$addon = rex_addon::get('consent_manager');

$func = rex_request::request('func', 'string');

// Import/Export Functionality
if ('setup_minimal' === $func) {
    // Import minimal setup (only necessary cookies)
    $jsonSetupFile = rex_path::addon('consent_manager') . 'setup/minimal_setup.json';

    if (file_exists($jsonSetupFile)) {
        $result = JsonSetup::importSetup($jsonSetupFile, true);
        if ($result['success']) {
            CLang::addonJustInstalled();
            echo rex_view::success(rex_i18n::msg('consent_manager_import_minimal_success'));
        } else {
            echo rex_view::error(rex_i18n::msg('consent_manager_import_minimal_error', $result['message']));
        }
    } else {
        echo rex_view::error(rex_i18n::msg('consent_manager_import_minimal_file_not_found'));
    }

    // Redirect to normal config page without func parameter
    echo '<script nonce="' . rex_response::getNonce() . '">setTimeout(function() { window.location.href = "' . rex_url::currentBackendPage(['page' => 'consent_manager/config']) . '"; }, 2000);</script>';
} elseif ('setup_standard' === $func) {
    // Import standard setup (comprehensive setup with common services)
    $jsonSetupFile = rex_path::addon('consent_manager') . 'setup/default_setup.json';

    if (file_exists($jsonSetupFile)) {
        $result = JsonSetup::importSetup($jsonSetupFile, true);
        if ($result['success']) {
            CLang::addonJustInstalled();
            echo rex_view::success(rex_i18n::msg('consent_manager_import_standard_success'));
        } else {
            echo rex_view::error(rex_i18n::msg('consent_manager_import_standard_error', $result['message']));
        }
    } else {
        echo rex_view::error(rex_i18n::msg('consent_manager_import_standard_file_not_found'));
    }

    // Redirect to normal config page without func parameter
    echo '<script nonce="' . rex_response::getNonce() . '">setTimeout(function() { window.location.href = "' . rex_url::currentBackendPage(['page' => 'consent_manager/config']) . '"; }, 2000);</script>';
} elseif ('setup_minimal_update' === $func) {
    // Update with minimal setup (only add new, don't overwrite existing)
    $jsonSetupFile = rex_path::addon('consent_manager') . 'setup/minimal_setup.json';

    if (file_exists($jsonSetupFile)) {
        $result = JsonSetup::importSetup($jsonSetupFile, false, 'update');
        if ($result['success']) {
            CLang::addonJustInstalled();
            echo rex_view::success(rex_i18n::msg('consent_manager_import_minimal_update_success'));
        } else {
            echo rex_view::error(rex_i18n::msg('consent_manager_import_minimal_update_error', $result['message']));
        }
    } else {
        echo rex_view::error(rex_i18n::msg('consent_manager_import_minimal_file_not_found'));
    }

    // Redirect to normal config page without func parameter
    echo '<script nonce="' . rex_response::getNonce() . '">setTimeout(function() { window.location.href = "' . rex_url::currentBackendPage(['page' => 'consent_manager/config']) . '"; }, 2000);</script>';
} elseif ('setup_standard_update' === $func) {
    // Update with standard setup (only add new, don't overwrite existing)
    $jsonSetupFile = rex_path::addon('consent_manager') . 'setup/default_setup.json';

    if (file_exists($jsonSetupFile)) {
        $result = JsonSetup::importSetup($jsonSetupFile, false, 'update');
        if ($result['success']) {
            CLang::addonJustInstalled();
            echo rex_view::success(rex_i18n::msg('consent_manager_import_standard_update_success'));
        } else {
            echo rex_view::error(rex_i18n::msg('consent_manager_import_standard_update_error', $result['message']));
        }
    } else {
        echo rex_view::error(rex_i18n::msg('consent_manager_import_standard_file_not_found'));
    }

    // Redirect to normal config page without func parameter
    echo '<script nonce="' . rex_response::getNonce() . '">setTimeout(function() { window.location.href = "' . rex_url::currentBackendPage(['page' => 'consent_manager/config']) . '"; }, 2000);</script>';
}

// For all other functions CSRF check
$csrf = rex_csrf_token::factory(Config::class);
if ('' !== $func && !in_array($func, ['setup_minimal', 'setup_standard', 'setup_minimal_update', 'setup_standard_update'], true)) {
    if (!$csrf->isValid()) {
        echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } else {
        if ('export' === $func) {
            // Clear output buffer to enable clean JSON download
            while (0 < ob_get_level()) {
                ob_end_clean();
            }

            // Export current configuration
            $export_data = [];

            // Export cookies/services
            $sql = rex_sql::factory();
            $sql->setQuery('SELECT * FROM ' . rex::getTable('consent_manager_cookie') . ' ORDER BY id');
            $export_data['cookies'] = $sql->getArray();

            // Export cookie groups
            $sql->setQuery('SELECT * FROM ' . rex::getTable('consent_manager_cookiegroup') . ' ORDER BY prio, id');
            $export_data['cookiegroups'] = $sql->getArray();

            // Export texts
            $sql->setQuery('SELECT * FROM ' . rex::getTable('consent_manager_text') . ' ORDER BY clang_id, id');
            $export_data['texts'] = $sql->getArray();

            // Export domains
            $sql->setQuery('SELECT * FROM ' . rex::getTable('consent_manager_domain') . ' ORDER BY id');
            $export_data['domains'] = $sql->getArray();

            // JSON Export erstellen
            $json_export = json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            // Headers für Download
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="consent_manager_export_' . date('Y-m-d_H-i-s') . '.json"');
            if (false !== $json_export) {
                header('Content-Length: ' . strlen($json_export));
                echo $json_export;
            }
            exit;
        }
        if ('import_json' === $func) {
            // JSON Import verarbeiten
            $importFile = rex_request::files('import_file', 'array', []);
            if (0 < count($importFile) && UPLOAD_ERR_OK === $importFile['error']) {
                $import_content = file_get_contents($importFile['tmp_name']);
                if (false !== $import_content) {
                    $import_data = json_decode($import_content, true);
                } else {
                    $import_data = null;
                }

                if (JSON_ERROR_NONE === json_last_error() && is_array($import_data)) {
                    try {
                        // Tabellen leeren
                        $sql = rex_sql::factory();
                        $sql->setQuery('DELETE FROM ' . rex::getTable('consent_manager_cookie'));
                        $sql->setQuery('DELETE FROM ' . rex::getTable('consent_manager_cookiegroup'));
                        $sql->setQuery('DELETE FROM ' . rex::getTable('consent_manager_text'));
                        $sql->setQuery('DELETE FROM ' . rex::getTable('consent_manager_domain'));

                        // Importierte Daten einfügen
                        $tables = ['cookies', 'cookiegroups', 'texts', 'domains'];
                        $table_map = [
                            'cookies' => 'consent_manager_cookie',
                            'cookiegroups' => 'consent_manager_cookiegroup',
                            'texts' => 'consent_manager_text',
                            'domains' => 'consent_manager_domain',
                        ];

                        foreach ($tables as $table_key) {
                            if (isset($import_data[$table_key]) && is_array($import_data[$table_key])) {
                                $table_name = rex::getTable($table_map[$table_key]);
                                foreach ($import_data[$table_key] as $row) {
                                    $sql = rex_sql::factory();
                                    $sql->setTable($table_name);
                                    foreach ($row as $key => $value) {
                                        $sql->setValue($key, $value);
                                    }
                                    $sql->insert();
                                }
                            }
                        }

                        echo rex_view::success(rex_i18n::msg('consent_manager_import_json_successful'));
                    } catch (rex_sql_exception $e) {
                        echo rex_view::error(rex_i18n::msg('consent_manager_import_json_error') . ': ' . $e->getMessage());
                    }
                } else {
                    echo rex_view::error(rex_i18n::msg('consent_manager_import_json_invalid'));
                }
            } else {
                echo rex_view::error(rex_i18n::msg('consent_manager_import_json_no_file'));
            }
        }
    }
}

// Import/Export UI und Settings Layout
$sql = rex_sql::factory();
$sql->setQuery('SELECT COUNT(*) as count FROM ' . rex::getTable('consent_manager_cookie'));
$cookie_count = $sql->getValue('count');

// Settings Form erstellen mit verbesserter Struktur
$form = rex_config_form::factory((string) $addon->getPackageId());
$form->addFieldset(rex_i18n::msg('consent_manager_config_legend'));

// CSS Output Einstellung
$field = $form->addCheckboxField('outputowncss');
$field->setLabel(rex_i18n::msg('consent_manager_config_owncss'));
$field->addOption(rex_i18n::msg('consent_manager_config_owncss'), 1);
$field->setNotice(rex_i18n::msg('consent_manager_config_owncss_desc'));

// Body Scrollbar Einstellung
$field = $form->addCheckboxField('hidebodyscrollbar');
$field->setLabel(rex_i18n::msg('consent_manager_config_hidebodyscrollbar'));
$field->addOption(rex_i18n::msg('consent_manager_config_hidebodyscrollbar'), 1);
$field->setNotice(rex_i18n::msg('consent_manager_config_hidebodyscrollbar_desc'));

// Inline-Only Modus
$field = $form->addCheckboxField('inline_only_mode');
$field->setLabel(rex_i18n::msg('consent_manager_config_inline_only_mode'));
$field->addOption(rex_i18n::msg('consent_manager_config_inline_only_mode'), 1);
$field->setNotice(rex_i18n::msg('consent_manager_config_inline_only_mode_desc'));

// Auto-Blocking für manuell eingefügtes HTML
$field = $form->addCheckboxField('auto_blocking_enabled');
$field->setLabel(rex_i18n::msg('consent_manager_config_auto_blocking'));
$field->addOption(rex_i18n::msg('consent_manager_config_auto_blocking_enable'), 1);
$field->setNotice(rex_i18n::msg('consent_manager_config_auto_blocking_desc') . ' <a href="#" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#auto-blocking-assistant-modal"><i class="rex-icon fa-magic"></i> ' . rex_i18n::msg('consent_manager_auto_blocking_assistant_open') . '</a>');

// Redakteur-Hinweise (wird nur auf Editorial-Seite angezeigt)
$field = $form->addTextAreaField('editorial_info');
$field->setLabel(rex_i18n::msg('consent_manager_config_editorial_info'));
$field->setAttribute('rows', '4');
$field->setAttribute('placeholder', rex_i18n::msg('consent_manager_config_editorial_info_placeholder'));
$field->setNotice(rex_i18n::msg('consent_manager_config_editorial_info_notice'));

// Cookie Name
$field = $form->addTextField('cookie_name');
$field->setLabel(rex_i18n::msg('consent_manager_config_cookie_name_label'));
$field->setAttribute('placeholder', 'consentmanager');
$field->setAttribute('pattern', '^[A-Za-z0-9_-]+$');
$field->setNotice(rex_i18n::msg('consent_manager_config_cookie_name_notice'));

// Cookie Lebensdauer
$field = $form->addTextField('lifespan');
$field->setLabel(rex_i18n::msg('consent_manager_config_lifespan_label'));
$field->setAttribute('type', 'number');
$field->setAttribute('step', '1');
$field->setAttribute('pattern', '[0-9]*');
$field->setAttribute('placeholder', '365');
$field->setNotice(rex_i18n::msg('consent_manager_config_lifespan_notice'));

// Token Einstellungen Fieldset
$form->addFieldset(rex_i18n::msg('consent_manager_config_token_legend'));
$field = $form->addTextField('skip_consent');
$field->setLabel(rex_i18n::msg('consent_manager_config_token_label'));
$field->setNotice(rex_i18n::msg('consent_manager_config_token_notice'));

// Layout mit Fragment
$fragment = new rex_fragment();
$fragment->setVar('form', $form);
$fragment->setVar('csrf', $csrf);
echo $fragment->parse('ConsentManager/config_layout.php');

// Setup Wizard Modal Fragment laden
$wizardFragment = new rex_fragment();
echo $wizardFragment->parse('ConsentManager/setup_wizard.php');

if ('' !== rex_request::post('_csrf_token', 'string', '')) {
    Theme::generateDefaultAssets();
    Theme::copyAllAssets();
}

// Auto-Blocking Assistent Modal
?>
<!-- Auto-Blocking Code-Assistent Modal -->
<div class="modal fade" id="auto-blocking-assistant-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="rex-icon fa-magic"></i> <?= $addon->i18n('consent_manager_auto_blocking_assistant_title') ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <p><?= $addon->i18n('consent_manager_auto_blocking_assistant_intro') ?></p>
                    <p><strong>Voraussetzung:</strong> Auto-Blocking muss oben in den Einstellungen aktiviert sein.</p>
                </div>

                <form id="auto-blocking-assistant">
                    <!-- Original Code Input -->
                    <div class="form-group">
                        <label for="original_code"><?= $addon->i18n('consent_manager_auto_blocking_assistant_input_label') ?></label>
                        <textarea class="form-control" id="original_code" rows="4" 
                                  placeholder="<?= $addon->i18n('consent_manager_auto_blocking_assistant_input_placeholder') ?>"></textarea>
                        <p class="help-block">Fügen Sie hier Ihren externen Script- oder iframe-Code ein</p>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <!-- Service Key -->
                            <div class="form-group">
                                <label for="service_key"><?= $addon->i18n('consent_manager_auto_blocking_assistant_service_label') ?> *</label>
                                <select class="form-control" id="service_key">
                                    <option value=""><?= $addon->i18n('consent_manager_auto_blocking_assistant_service_select') ?></option>
                                    <?php
                                    // Lade alle verfügbaren Dienste aus der aktuellen Sprache
                                    $sql = rex_sql::factory();
                                    $clang_id = rex_clang::getCurrentId();
                                    $services = $sql->getArray('SELECT uid, service_name FROM ' . rex::getTable('consent_manager_cookie') . ' WHERE clang_id = ? ORDER BY service_name', [$clang_id]);
                                    foreach ($services as $service) {
                                        echo '<option value="' . rex_escape($service['uid']) . '">' . rex_escape($service['service_name']) . ' (' . rex_escape($service['uid']) . ')</option>';
                                    }
                                    ?>
                                    <option value="__custom__"><?= $addon->i18n('consent_manager_auto_blocking_assistant_service_custom') ?></option>
                                </select>
                                <input type="text" class="form-control" id="service_key_custom" placeholder="z.B. calendly, youtube" style="display: none; margin-top: 10px;">
                                <p class="help-block"><?= $addon->i18n('consent_manager_auto_blocking_assistant_service_notice') ?></p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <!-- Provider Name -->
                            <div class="form-group">
                                <label for="provider_name"><?= $addon->i18n('consent_manager_auto_blocking_assistant_provider_label') ?></label>
                                <input type="text" class="form-control" id="provider_name" placeholder="z.B. Calendly">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <!-- Privacy URL -->
                            <div class="form-group">
                                <label for="privacy_url"><?= $addon->i18n('consent_manager_auto_blocking_assistant_privacy_label') ?></label>
                                <input type="url" class="form-control" id="privacy_url" placeholder="https://example.com/datenschutz">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <!-- Title -->
                            <div class="form-group">
                                <label for="consent_title"><?= $addon->i18n('consent_manager_auto_blocking_assistant_title_label') ?></label>
                                <input type="text" class="form-control" id="consent_title" placeholder="z.B. Termin buchen">
                            </div>

                    <!-- Custom Text -->
                    <div class="form-group">
                        <label for="consent_text"><?= $addon->i18n('consent_manager_auto_blocking_assistant_text_label') ?></label>
                        <textarea class="form-control" id="consent_text" rows="2" placeholder="<?= $addon->i18n('consent_manager_auto_blocking_assistant_text_placeholder') ?>"></textarea>
                        <p class="help-block"><?= $addon->i18n('consent_manager_auto_blocking_assistant_text_notice') ?></p>
                    </div>
                        </div>
                    </div>


                    <!-- Custom Text -->
                    <div class="form-group">
                        <label for="consent_text"><?= $addon->i18n('consent_manager_auto_blocking_assistant_text_label') ?></label>
                        <textarea class="form-control" id="consent_text" rows="2" placeholder="<?= $addon->i18n('consent_manager_auto_blocking_assistant_text_placeholder') ?>"></textarea>
                        <p class="help-block"><?= $addon->i18n('consent_manager_auto_blocking_assistant_text_notice') ?></p>
                    </div>

                    <!-- Generate Button -->
                    <div class="form-group">
                        <button type="button" class="btn btn-primary" id="generate_code">
                            <i class="rex-icon fa-magic"></i> <?= $addon->i18n('consent_manager_auto_blocking_assistant_generate') ?>
                        </button>
                    </div>

                    <!-- Output Code -->
                    <div class="form-group" id="output_container" style="display: none;">
                        <label for="output_code"><?= $addon->i18n('consent_manager_auto_blocking_assistant_output_label') ?></label>
                        <textarea class="form-control" id="output_code" rows="8" readonly></textarea>
                        <button type="button" class="btn btn-success btn-sm" id="copy_code" style="margin-top: 10px;">
                            <i class="rex-icon fa-clipboard"></i> <?= $addon->i18n('consent_manager_auto_blocking_assistant_copy') ?>
                        </button>
                        <span id="copy_success" style="display: none; margin-left: 10px; color: #5cb85c;">
                            <i class="rex-icon fa-check"></i> <?= $addon->i18n('consent_manager_auto_blocking_assistant_copied') ?>
                        </span>
                    </div>
                </form>

                <!-- Beispiele -->
                <hr>
                <h4>Beispiele</h4>
                
                <h5>YouTube Video</h5>
                <pre><code>&lt;iframe src="https://www.youtube.com/embed/VIDEO_ID" 
        width="560" height="315"
        data-consent-block="true"
        data-consent-service="youtube"
        data-consent-provider="YouTube"
        data-consent-privacy="https://policies.google.com/privacy"&gt;&lt;/iframe&gt;</code></pre>

                <h5>Calendly Widget</h5>
                <pre><code>&lt;div data-consent-block="true"
     data-consent-service="calendly"
     data-consent-provider="Calendly"
     data-consent-privacy="https://calendly.com/privacy"&gt;
    &lt;script src="https://assets.calendly.com/assets/external/widget.js"&gt;&lt;/script&gt;
&lt;/div&gt;</code></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="rex-icon fa-times"></i> Schließen
                </button>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= rex_response::getNonce() ?>">
jQuery(function($) {
    'use strict';
    
    // Toggle custom service input
    $('#service_key').on('change', function() {
        if ($(this).val() === '__custom__') {
            $('#service_key_custom').slideDown();
        } else {
            $('#service_key_custom').slideUp();
        }
    });
    
    $('#generate_code').on('click', function() {
        var originalCode = $('#original_code').val().trim();
        var serviceKey = $('#service_key').val().trim();
        
        // Wenn "Custom" ausgewählt, nutze das Custom-Eingabefeld
        if (serviceKey === '__custom__') {
            serviceKey = $('#service_key_custom').val().trim();
        }
        var providerName = $('#provider_name').val().trim();
        var privacyUrl = $('#privacy_url').val().trim();
        var title = $('#consent_title').val().trim();
        var customText = $('#consent_text').val().trim();
        
        if (!originalCode) {
            alert('Bitte Original-Code eingeben!');
            return;
        }
        
        if (!serviceKey) {
            alert('Bitte Service-Schlüssel eingeben!');
            return;
        }
        
        // Parse HTML und füge Attribute hinzu
        var $temp = $('<div>').html(originalCode);
        var $element = $temp.children().first();
        
        if ($element.length === 0) {
            alert('Ungültiger HTML-Code!');
            return;
        }
        
        // Basis-Attribute
        $element.attr('data-consent-block', 'true');
        $element.attr('data-consent-service', serviceKey);
        
        // Optionale Attribute
        if (providerName) {
            $element.attr('data-consent-provider', providerName);
        }
        if (privacyUrl) {
            $element.attr('data-consent-privacy', privacyUrl);
        }
        if (title) {
            $element.attr('data-consent-title', title);
        }
        if (customText) {
            $element.attr('data-consent-text', customText);
        }
        
        // Generierter Code
        var generatedCode = $temp.html();
        
        // Formatierung verbessern (Einrückung bei mehrzeiligen Attributen)
        generatedCode = generatedCode.replace(/data-consent-/g, '\n        data-consent-');
        
        // Ausgabe
        $('#output_code').val(generatedCode);
        $('#output_container').slideDown();
    });
    
    $('#copy_code').on('click', function() {
        var outputCode = $('#output_code')[0];
        outputCode.select();
        outputCode.setSelectionRange(0, 99999); // Mobile
        
        try {
            document.execCommand('copy');
            $('#copy_success').fadeIn().delay(2000).fadeOut();
        } catch (err) {
            alert('Kopieren fehlgeschlagen. Bitte manuell kopieren.');
        }
    });
    
    // Modal-Event: Formular zurücksetzen beim Öffnen
    $('#auto-blocking-assistant-modal').on('show.bs.modal', function() {
        $('#auto-blocking-assistant')[0].reset();
        $('#output_container').hide();
    });
});
</script>

// Auto-Blocking Assistent Modal
?>
<!-- Auto-Blocking Code-Assistent Modal -->
<div class="modal fade" id="auto-blocking-assistant-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="rex-icon fa-magic"></i> <?= $addon->i18n('consent_manager_auto_blocking_assistant_title') ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <p><?= $addon->i18n('consent_manager_auto_blocking_assistant_intro') ?></p>
                    <p><strong>Voraussetzung:</strong> Auto-Blocking muss oben in den Einstellungen aktiviert sein.</p>
                </div>

                <form id="auto-blocking-assistant">
                    <!-- Original Code Input -->
                    <div class="form-group">
                        <label for="original_code"><?= $addon->i18n('consent_manager_auto_blocking_assistant_input_label') ?></label>
                        <textarea class="form-control" id="original_code" rows="4" 
                                  placeholder="<?= $addon->i18n('consent_manager_auto_blocking_assistant_input_placeholder') ?>"></textarea>
                        <p class="help-block">Fügen Sie hier Ihren externen Script- oder iframe-Code ein</p>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <!-- Service Key -->
                            <div class="form-group">
                                <label for="service_key"><?= $addon->i18n('consent_manager_auto_blocking_assistant_service_label') ?> *</label>
                                <select class="form-control" id="service_key">
                                    <option value=""><?= $addon->i18n('consent_manager_auto_blocking_assistant_service_select') ?></option>
                                    <?php
                                    // Lade alle verfügbaren Dienste aus der aktuellen Sprache
                                    $sql = rex_sql::factory();
                                    $clang_id = rex_clang::getCurrentId();
                                    $services = $sql->getArray('SELECT uid, service_name FROM ' . rex::getTable('consent_manager_cookie') . ' WHERE clang_id = ? ORDER BY service_name', [$clang_id]);
                                    foreach ($services as $service) {
                                        echo '<option value="' . rex_escape($service['uid']) . '">' . rex_escape($service['service_name']) . ' (' . rex_escape($service['uid']) . ')</option>';
                                    }
                                    ?>
                                    <option value="__custom__"><?= $addon->i18n('consent_manager_auto_blocking_assistant_service_custom') ?></option>
                                </select>
                                <input type="text" class="form-control" id="service_key_custom" placeholder="z.B. calendly, youtube" style="display: none; margin-top: 10px;">
                                <p class="help-block"><?= $addon->i18n('consent_manager_auto_blocking_assistant_service_notice') ?></p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <!-- Provider Name -->
                            <div class="form-group">
                                <label for="provider_name"><?= $addon->i18n('consent_manager_auto_blocking_assistant_provider_label') ?></label>
                                <input type="text" class="form-control" id="provider_name" placeholder="z.B. Calendly">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <!-- Privacy URL -->
                            <div class="form-group">
                                <label for="privacy_url"><?= $addon->i18n('consent_manager_auto_blocking_assistant_privacy_label') ?></label>
                                <input type="url" class="form-control" id="privacy_url" placeholder="https://example.com/datenschutz">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <!-- Title -->
                            <div class="form-group">
                                <label for="consent_title"><?= $addon->i18n('consent_manager_auto_blocking_assistant_title_label') ?></label>
                                <input type="text" class="form-control" id="consent_title" placeholder="z.B. Termin buchen">
                            </div>
                        </div>
                    </div>

                    <!-- Generate Button -->
                    <div class="form-group">
                        <button type="button" class="btn btn-primary" id="generate_code">
                            <i class="rex-icon fa-magic"></i> <?= $addon->i18n('consent_manager_auto_blocking_assistant_generate') ?>
                        </button>
                    </div>

                    <!-- Output Code -->
                    <div class="form-group" id="output_container" style="display: none;">
                        <label for="output_code"><?= $addon->i18n('consent_manager_auto_blocking_assistant_output_label') ?></label>
                        <textarea class="form-control" id="output_code" rows="8" readonly></textarea>
                        <button type="button" class="btn btn-success btn-sm" id="copy_code" style="margin-top: 10px;">
                            <i class="rex-icon fa-clipboard"></i> <?= $addon->i18n('consent_manager_auto_blocking_assistant_copy') ?>
                        </button>
                        <span id="copy_success" style="display: none; margin-left: 10px; color: #5cb85c;">
                            <i class="rex-icon fa-check"></i> <?= $addon->i18n('consent_manager_auto_blocking_assistant_copied') ?>
                        </span>
                    </div>
                </form>

                <!-- Beispiele -->
                <hr>
                <h4>Beispiele</h4>
                
                <h5>YouTube Video</h5>
                <pre><code>&lt;iframe src="https://www.youtube.com/embed/VIDEO_ID" 
        width="560" height="315"
        data-consent-block="true"
        data-consent-service="youtube"
        data-consent-provider="YouTube"
        data-consent-privacy="https://policies.google.com/privacy"&gt;&lt;/iframe&gt;</code></pre>

                <h5>Calendly Widget</h5>
                <pre><code>&lt;div data-consent-block="true"
     data-consent-service="calendly"
     data-consent-provider="Calendly"
     data-consent-privacy="https://calendly.com/privacy"&gt;
    &lt;script src="https://assets.calendly.com/assets/external/widget.js"&gt;&lt;/script&gt;
&lt;/div&gt;</code></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="rex-icon fa-times"></i> Schließen
                </button>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= rex_response::getNonce() ?>">
jQuery(function($) {
    'use strict';
    
    // Toggle custom service input
    $('#service_key').on('change', function() {
        if ($(this).val() === '__custom__') {
            $('#service_key_custom').slideDown();
        } else {
            $('#service_key_custom').slideUp();
        }
    });
    
    $('#generate_code').on('click', function() {
        var originalCode = $('#original_code').val().trim();
        var serviceKey = $('#service_key').val().trim();
        
        // Wenn "Custom" ausgewählt, nutze das Custom-Eingabefeld
        if (serviceKey === '__custom__') {
            serviceKey = $('#service_key_custom').val().trim();
        }
        var providerName = $('#provider_name').val().trim();
        var privacyUrl = $('#privacy_url').val().trim();
        var title = $('#consent_title').val().trim();
        var customText = $('#consent_text').val().trim();
        
        if (!originalCode) {
            alert('Bitte Original-Code eingeben!');
            return;
        }
        
        if (!serviceKey) {
            alert('Bitte Service-Schlüssel eingeben!');
            return;
        }
        
        // Parse HTML und füge Attribute hinzu
        var $temp = $('<div>').html(originalCode);
        var $element = $temp.children().first();
        
        if ($element.length === 0) {
            alert('Ungültiger HTML-Code!');
            return;
        }
        
        // Basis-Attribute
        $element.attr('data-consent-block', 'true');
        $element.attr('data-consent-service', serviceKey);
        
        // Optionale Attribute
        if (providerName) {
            $element.attr('data-consent-provider', providerName);
        }
        if (privacyUrl) {
            $element.attr('data-consent-privacy', privacyUrl);
        }
        if (title) {
            $element.attr('data-consent-title', title);
        }
        if (customText) {
            $element.attr('data-consent-text', customText);
        }
        
        // Generierter Code
        var generatedCode = $temp.html();
        
        // Formatierung verbessern (Einrückung bei mehrzeiligen Attributen)
        generatedCode = generatedCode.replace(/data-consent-/g, '\n        data-consent-');
        
        // Ausgabe
        $('#output_code').val(generatedCode);
        $('#output_container').slideDown();
    });
    
    $('#copy_code').on('click', function() {
        var outputCode = $('#output_code')[0];
        outputCode.select();
        outputCode.setSelectionRange(0, 99999); // Mobile
        
        try {
            document.execCommand('copy');
            $('#copy_success').fadeIn().delay(2000).fadeOut();
        } catch (err) {
            alert('Kopieren fehlgeschlagen. Bitte manuell kopieren.');
        }
    });
    
    // Modal-Event: Formular zurücksetzen beim Öffnen
    $('#auto-blocking-assistant-modal').on('show.bs.modal', function() {
        $('#auto-blocking-assistant')[0].reset();
        $('#output_container').hide();
    });
});
</script>
