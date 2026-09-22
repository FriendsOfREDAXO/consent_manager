<?php

use FriendsOfRedaxo\ConsentManager\CLang;
use FriendsOfRedaxo\ConsentManager\Config;
use FriendsOfRedaxo\ConsentManager\JsonSetup;

$addon = rex_addon::get('consent_manager');
$redirectUrlAfterAction = null;
$currentUrl = rex_url::backendPage('consent_manager/config/data');

$func = rex_request::request('func', 'string');

// Setup-Vorlagen importieren (komplett = bestehende Daten ersetzen, update = nur fehlende ergänzen)
$setupImports = [
    'setup_minimal' => ['minimal_setup.json', true, 'replace', 'minimal'],
    'setup_standard' => ['default_setup.json', true, 'replace', 'standard'],
    'setup_minimal_update' => ['minimal_setup.json', false, 'update', 'minimal_update'],
    'setup_standard_update' => ['default_setup.json', false, 'update', 'standard_update'],
];
if (isset($setupImports[$func])) {
    [$file, $clearExisting, $mode, $msgKey] = $setupImports[$func];
    $jsonSetupFile = rex_path::addon('consent_manager') . 'setup/' . $file;

    if (file_exists($jsonSetupFile)) {
        $result = JsonSetup::importSetup($jsonSetupFile, $clearExisting, $mode);
        if ($result['success']) {
            CLang::addonJustInstalled();
            echo rex_view::success(rex_i18n::msg('consent_manager_import_' . $msgKey . '_success'));
        } else {
            echo rex_view::error(rex_i18n::msg('consent_manager_import_' . $msgKey . '_error', $result['message']));
        }
    } else {
        echo rex_view::error(rex_i18n::msg('consent_manager_import_' . ('standard' === substr($msgKey, 0, 8) ? 'standard' : 'minimal') . '_file_not_found'));
    }

    $redirectUrlAfterAction = $currentUrl;
}

// Alle weiteren Funktionen mit CSRF-Prüfung
$csrf = rex_csrf_token::factory(Config::class);
if ('' !== $func && !isset($setupImports[$func])) {
    if (!$csrf->isValid()) {
        echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } else {
        if ('export' === $func) {
            while (0 < ob_get_level()) {
                ob_end_clean();
            }

            $export_data = [];
            $sql = rex_sql::factory();
            $sql->setQuery('SELECT * FROM ' . rex::getTable('consent_manager_cookie') . ' ORDER BY id');
            $export_data['cookies'] = $sql->getArray();
            $sql->setQuery('SELECT * FROM ' . rex::getTable('consent_manager_cookiegroup') . ' ORDER BY prio, id');
            $export_data['cookiegroups'] = $sql->getArray();
            $sql->setQuery('SELECT * FROM ' . rex::getTable('consent_manager_text') . ' ORDER BY clang_id, id');
            $export_data['texts'] = $sql->getArray();
            $sql->setQuery('SELECT * FROM ' . rex::getTable('consent_manager_domain') . ' ORDER BY id');
            $export_data['domains'] = $sql->getArray();

            $json_export = json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="consent_manager_export_' . date('Y-m-d_H-i-s') . '.json"');
            if (false !== $json_export) {
                header('Content-Length: ' . strlen($json_export));
                echo $json_export;
            }
            exit;
        }
        if ('sync_missing_clang' === $func) {
            $sourceClangId = rex_request::post('source_clang_id', 'int', rex_clang::getStartId());
            $targetClangIdsRaw = rex_request::post('target_clang_ids', 'array', []);
            $syncTablesRaw = rex_request::post('sync_tables', 'array', []);

            $targetClangIds = [];
            foreach ((array) $targetClangIdsRaw as $targetClangIdRaw) {
                $targetClangId = (int) $targetClangIdRaw;
                if ($targetClangId > 0) {
                    $targetClangIds[] = $targetClangId;
                }
            }

            $allowedTables = Config::getTables(true);
            $syncTables = [];
            foreach ((array) $syncTablesRaw as $syncTableRaw) {
                $syncTable = (string) $syncTableRaw;
                if (in_array($syncTable, $allowedTables, true)) {
                    $syncTables[] = $syncTable;
                }
            }

            if ([] === $syncTables) {
                $syncTables = $allowedTables;
            }

            if ([] === $targetClangIds) {
                echo rex_view::error(rex_i18n::msg('consent_manager_sync_missing_no_targets'));
            } else {
                $syncResult = CLang::syncMissingFromSource($sourceClangId, $targetClangIds, $syncTables);
                if ((int) $syncResult['inserted'] > 0) {
                    $details = [];
                    foreach ($syncResult['per_table'] as $tableName => $count) {
                        if ((int) $count <= 0) {
                            continue;
                        }

                        $label = $tableName;
                        if ($tableName === rex::getTable('consent_manager_cookie')) {
                            $label = rex_i18n::msg('consent_manager_cookies');
                        } elseif ($tableName === rex::getTable('consent_manager_cookiegroup')) {
                            $label = rex_i18n::msg('consent_manager_cookiegroups');
                        } elseif ($tableName === rex::getTable('consent_manager_text')) {
                            $label = rex_i18n::msg('consent_manager_text');
                        }
                        $details[] = rex_escape($label) . ': ' . (int) $count;
                    }

                    $detailText = '';
                    if ([] !== $details) {
                        $detailText = '<br><small>' . implode(' | ', $details) . '</small>';
                    }

                    echo rex_view::success(rex_i18n::msg('consent_manager_sync_missing_success', $syncResult['inserted']) . $detailText);
                } else {
                    echo rex_view::info(rex_i18n::msg('consent_manager_sync_missing_nothing_to_do'));
                }
            }

            $redirectUrlAfterAction = $currentUrl;
        }
        if ('import_json' === $func) {
            $importFile = rex_request::files('import_file', 'array', []);
            if (0 < count($importFile) && UPLOAD_ERR_OK === $importFile['error']) {
                $import_content = file_get_contents($importFile['tmp_name']);
                $import_data = false !== $import_content ? json_decode($import_content, true) : null;

                if (JSON_ERROR_NONE === json_last_error() && is_array($import_data)) {
                    try {
                        $sql = rex_sql::factory();
                        $sql->setQuery('DELETE FROM ' . rex::getTable('consent_manager_cookie'));
                        $sql->setQuery('DELETE FROM ' . rex::getTable('consent_manager_cookiegroup'));
                        $sql->setQuery('DELETE FROM ' . rex::getTable('consent_manager_text'));
                        $sql->setQuery('DELETE FROM ' . rex::getTable('consent_manager_domain'));

                        $table_map = [
                            'cookies' => 'consent_manager_cookie',
                            'cookiegroups' => 'consent_manager_cookiegroup',
                            'texts' => 'consent_manager_text',
                            'domains' => 'consent_manager_domain',
                        ];

                        foreach ($table_map as $table_key => $table) {
                            if (isset($import_data[$table_key]) && is_array($import_data[$table_key])) {
                                $table_name = rex::getTable($table);
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

if (null !== $redirectUrlAfterAction) {
    echo '<div id="cm-config-redirect" data-cm-config-redirect-url="' . rex_escape($redirectUrlAfterAction) . '" data-cm-config-redirect-delay="2000"></div>';
}

$sql = rex_sql::factory();
$sql->setQuery('SELECT COUNT(*) AS cnt FROM ' . rex::getTable('consent_manager_domain'));
$hasDomains = (int) $sql->getValue('cnt') > 0;
$allClangs = rex_clang::getAll();
$hasMultipleClangs = count($allClangs) > 1;
$defaultSourceClangId = rex_clang::getStartId();

$panel = static function (string $title, string $icon, string $body): string {
    $fragment = new rex_fragment();
    $fragment->setVar('title', '<i class="rex-icon ' . $icon . '"></i> ' . $title, false);
    $fragment->setVar('body', $body, false);
    return $fragment->parse('core/page/section.php');
};

// Ohne Domain bietet bereits die Checkliste "Erste Schritte" den Assistenten an
if ($hasDomains) {
    echo '<p class="text-right"><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#setup-wizard-modal"><i class="rex-icon fa-magic"></i> Setup Wizard</button></p>';
}

// Schnellstart: Setup-Vorlagen
$setupBlock = static function (string $titleKey, string $descKey, string $icon, string $funcComplete, string $confirmComplete, string $funcUpdate, string $confirmUpdate): string {
    return '<h4><i class="rex-icon ' . $icon . '"></i> ' . rex_i18n::msg($titleKey) . '</h4>'
        . '<p>' . rex_i18n::msg($descKey) . '</p>'
        . '<p>'
        . '<a href="' . rex_url::currentBackendPage(['func' => $funcComplete]) . '" class="btn btn-default" onclick="return confirm(\'' . rex_i18n::msg($confirmComplete) . '\')"><i class="rex-icon fa-download"></i> ' . rex_i18n::msg('consent_manager_config_load_complete') . '</a> '
        . '<a href="' . rex_url::currentBackendPage(['func' => $funcUpdate]) . '" class="btn btn-default" onclick="return confirm(\'' . rex_i18n::msg($confirmUpdate) . '\')"><i class="rex-icon fa-plus"></i> ' . rex_i18n::msg('consent_manager_config_load_new_only') . '</a>'
        . '</p>';
};
$quickstart = '<p><strong>' . rex_i18n::msg('consent_manager_config_choose_setup') . '</strong></p>'
    . $setupBlock('consent_manager_config_standard_setup_title', 'consent_manager_config_standard_setup_desc', 'fa-cog', 'setup_standard', 'consent_manager_config_standard_confirm', 'setup_standard_update', 'consent_manager_config_standard_update_confirm')
    . '<hr>'
    . $setupBlock('consent_manager_config_minimal_setup_title', 'consent_manager_config_minimal_setup_desc', 'fa-shield', 'setup_minimal', 'consent_manager_config_minimal_confirm', 'setup_minimal_update', 'consent_manager_config_minimal_update_confirm');

// Sprach-Sync
$sync = '';
if ($hasMultipleClangs) {
    $sourceOptions = '';
    $targetChecks = '';
    foreach ($allClangs as $clang) {
        $clangId = (int) $clang->getId();
        $sourceOptions .= '<option value="' . $clangId . '"' . ($clangId === $defaultSourceClangId ? ' selected' : '') . '>' . rex_escape($clang->getName()) . '</option>';
        if ($clangId !== $defaultSourceClangId) {
            $targetChecks .= '<div class="checkbox"><label><input type="checkbox" name="target_clang_ids[]" value="' . $clangId . '" checked> ' . rex_escape($clang->getName()) . '</label></div>';
        }
    }
    $tableChecks = '';
    foreach (['consent_manager_cookiegroup' => 'consent_manager_cookiegroups', 'consent_manager_cookie' => 'consent_manager_cookies', 'consent_manager_text' => 'consent_manager_text'] as $table => $labelKey) {
        $tableChecks .= '<div class="checkbox"><label><input type="checkbox" name="sync_tables[]" value="' . rex_escape(rex::getTable($table)) . '" checked> ' . rex_i18n::msg($labelKey) . '</label></div>';
    }

    $sync = '<p>' . rex_i18n::msg('consent_manager_sync_missing_desc') . '</p>'
        . '<form action="' . rex_url::currentBackendPage() . '" method="post">'
        . '<input type="hidden" name="func" value="sync_missing_clang">'
        . $csrf->getHiddenField()
        . '<div class="form-group"><label for="cm-sync-source-clang">' . rex_i18n::msg('consent_manager_sync_missing_source') . '</label>'
        . '<select id="cm-sync-source-clang" name="source_clang_id" class="form-control">' . $sourceOptions . '</select></div>'
        . '<div class="form-group"><label>' . rex_i18n::msg('consent_manager_sync_missing_targets') . '</label>' . $targetChecks . '</div>'
        . '<div class="form-group"><label>' . rex_i18n::msg('consent_manager_sync_missing_tables') . '</label>' . $tableChecks . '</div>'
        . '<p><button type="submit" class="btn btn-default" onclick="return confirm(\'' . rex_i18n::msg('consent_manager_sync_missing_confirm') . '\')"><i class="rex-icon fa-random"></i> ' . rex_i18n::msg('consent_manager_sync_missing_button') . '</button></p>'
        . '</form>';
}

// Export
$export = '<p>' . rex_i18n::msg('consent_manager_config_export_desc') . '</p>'
    . '<p><a href="' . rex_url::currentBackendPage(['func' => 'export'] + $csrf->getUrlParams()) . '" class="btn btn-default"><i class="rex-icon fa-download"></i> ' . rex_i18n::msg('consent_manager_config_export_button') . '</a></p>';

// Import
$import = '<p>' . rex_i18n::msg('consent_manager_config_import_desc') . '</p>'
    . '<form action="' . rex_url::currentBackendPage() . '" method="post" enctype="multipart/form-data">'
    . '<input type="hidden" name="func" value="import_json">'
    . $csrf->getHiddenField()
    . '<div class="form-group"><input type="file" class="form-control" id="import_file" name="import_file" accept=".json" required></div>'
    . '<p><button type="submit" class="btn btn-default"><i class="rex-icon fa-upload"></i> ' . rex_i18n::msg('consent_manager_config_import_button') . '</button></p>'
    . '</form>';

echo '<div class="row">';
echo '<div class="col-md-6">' . $panel(rex_i18n::msg('consent_manager_config_quickstart_title'), 'fa-rocket', $quickstart) . '</div>';
echo '<div class="col-md-6">';
if ('' !== $sync) {
    echo $panel(rex_i18n::msg('consent_manager_sync_missing_title'), 'fa-language', $sync);
}
echo $panel(rex_i18n::msg('consent_manager_config_export_title'), 'fa-upload', $export);
echo $panel(rex_i18n::msg('consent_manager_config_import_title'), 'fa-file-code-o', $import);
echo '</div>';
echo '</div>';
