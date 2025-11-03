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
    echo '<script>setTimeout(function() { window.location.href = "' . rex_url::currentBackendPage(['page' => 'consent_manager/config']) . '"; }, 2000);</script>';
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
    echo '<script>setTimeout(function() { window.location.href = "' . rex_url::currentBackendPage(['page' => 'consent_manager/config']) . '"; }, 2000);</script>';
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
    echo '<script>setTimeout(function() { window.location.href = "' . rex_url::currentBackendPage(['page' => 'consent_manager/config']) . '"; }, 2000);</script>';
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
    echo '<script>setTimeout(function() { window.location.href = "' . rex_url::currentBackendPage(['page' => 'consent_manager/config']) . '"; }, 2000);</script>';
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
            header('Content-Length: ' . strlen($json_export));

            echo $json_export;
            exit;
        }
        if ('import_json' === $func) {
            // JSON Import verarbeiten
            $importFile = rex_request::files('import_file', 'array', []);
            if (0 < count($importFile) && UPLOAD_ERR_OK === $importFile['error']) {
                $import_content = file_get_contents($importFile['tmp_name']);
                $import_data = json_decode($import_content, true);

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

// Quickstart Modal Fragment laden
$modalFragment = new rex_fragment();
echo $modalFragment->parse('ConsentManager/quickstart_modal.php');

if ('' !== rex_request::post('_csrf_token', 'string', '')) {
    Theme::generateDefaultAssets();
    Theme::copyAllAssets();
}
