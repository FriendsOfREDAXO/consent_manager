<?php

$addon = rex_addon::get('consent_manager');

// Ensure JSON setup class is loaded
if (!class_exists('consent_manager_json_setup')) {
    require_once __DIR__ . '/../lib/consent_manager_json_setup.php';
}

$func = rex_request('func', 'string');

// Import/Export Functionality
if ('setup_minimal' === $func) {
    // Import minimal setup (only necessary cookies)
    $jsonSetupFile = rex_path::addon('consent_manager').'setup/minimal_setup.json';
    
    if (file_exists($jsonSetupFile)) {
        $result = consent_manager_json_setup::importSetup($jsonSetupFile, true);
        if ($result['success']) {
            consent_manager_clang::addonJustInstalled();
            echo rex_view::success('Minimales Setup erfolgreich importiert - nur technisch notwendige Cookies');
        } else {
            echo rex_view::error('Minimales Setup Import fehlgeschlagen: ' . $result['message']);
        }
    } else {
        echo rex_view::error('Minimal setup file nicht gefunden: minimal_setup.json');
    }
    
    // Redirect to normal config page without func parameter
    echo '<script>setTimeout(function() { window.location.href = "'.rex_url::currentBackendPage(['page' => 'consent_manager/config']).'"; }, 2000);</script>';

} elseif ('setup_standard' === $func) {
    // Import standard setup (comprehensive setup with common services)
    $jsonSetupFile = rex_path::addon('consent_manager').'setup/default_setup.json';
    
    if (file_exists($jsonSetupFile)) {
        $result = consent_manager_json_setup::importSetup($jsonSetupFile, true);
        if ($result['success']) {
            consent_manager_clang::addonJustInstalled();
            echo rex_view::success('Standard Setup erfolgreich importiert - alle wichtigen Services vorkonfiguriert');
        } else {
            echo rex_view::error('Standard Setup Import fehlgeschlagen: ' . $result['message']);
        }
    } else {
        echo rex_view::error('Standard setup file nicht gefunden: default_setup.json');
    }
    
    // Redirect to normal config page without func parameter
    echo '<script>setTimeout(function() { window.location.href = "'.rex_url::currentBackendPage(['page' => 'consent_manager/config']).'"; }, 2000);</script>';

} elseif ('setup_minimal_update' === $func) {
    // Update with minimal setup (only add new, don't overwrite existing)
    $jsonSetupFile = rex_path::addon('consent_manager').'setup/minimal_setup.json';
    
    if (file_exists($jsonSetupFile)) {
        $result = consent_manager_json_setup::importSetup($jsonSetupFile, false, 'update');
        if ($result['success']) {
            consent_manager_clang::addonJustInstalled();
            echo rex_view::success('Minimal Setup Update erfolgreich - nur neue Services hinzugefügt, bestehende unverändert');
        } else {
            echo rex_view::error('Minimal Setup Update fehlgeschlagen: ' . $result['message']);
        }
    } else {
        echo rex_view::error('Minimal setup file nicht gefunden: minimal_setup.json');
    }
    
    // Redirect to normal config page without func parameter
    echo '<script>setTimeout(function() { window.location.href = "'.rex_url::currentBackendPage(['page' => 'consent_manager/config']).'"; }, 2000);</script>';

} elseif ('setup_standard_update' === $func) {
    // Update with standard setup (only add new, don't overwrite existing)
    $jsonSetupFile = rex_path::addon('consent_manager').'setup/default_setup.json';
    
    if (file_exists($jsonSetupFile)) {
        $result = consent_manager_json_setup::importSetup($jsonSetupFile, false, 'update');
        if ($result['success']) {
            consent_manager_clang::addonJustInstalled();
            echo rex_view::success('Standard Setup Update erfolgreich - nur neue Services hinzugefügt, bestehende unverändert');
        } else {
            echo rex_view::error('Standard Setup Update fehlgeschlagen: ' . $result['message']);
        }
    } else {
        echo rex_view::error('Standard setup file nicht gefunden: default_setup.json');
    }
    
    // Redirect to normal config page without func parameter
    echo '<script>setTimeout(function() { window.location.href = "'.rex_url::currentBackendPage(['page' => 'consent_manager/config']).'"; }, 2000);</script>';

}

// For all other functions CSRF check
$csrf = rex_csrf_token::factory('consent_manager_config');
if ('' !== $func && !in_array($func, ['setup_minimal', 'setup_standard', 'setup_minimal_update', 'setup_standard_update'])) {
    if (!$csrf->isValid()) {
        echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } else {
        if ('export' === $func) {
            // Clear output buffer to enable clean JSON download
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Export current configuration
            $export_data = [];
            
            // Export cookies/services
            $sql = rex_sql::factory();
            $sql->setQuery('SELECT * FROM '.rex::getTable('consent_manager_cookie').' ORDER BY id');
            $export_data['cookies'] = $sql->getArray();
            
            // Export cookie groups
            $sql->setQuery('SELECT * FROM '.rex::getTable('consent_manager_cookiegroup').' ORDER BY prio, id');
            $export_data['cookiegroups'] = $sql->getArray();
            
            // Export texts
            $sql->setQuery('SELECT * FROM '.rex::getTable('consent_manager_text').' ORDER BY clang_id, id');
            $export_data['texts'] = $sql->getArray();
            
            // Export domains
            $sql->setQuery('SELECT * FROM '.rex::getTable('consent_manager_domain').' ORDER BY id');
            $export_data['domains'] = $sql->getArray();
            
            // JSON Export erstellen
            $json_export = json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
            // Headers für Download
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="consent_manager_export_'.date('Y-m-d_H-i-s').'.json"');
            header('Content-Length: ' . strlen($json_export));
            
            echo $json_export;
            exit;
            
        } elseif ('import_json' === $func) {
            // JSON Import verarbeiten
            if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
                $import_content = file_get_contents($_FILES['import_file']['tmp_name']);
                $import_data = json_decode($import_content, true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($import_data)) {
                    try {
                        // Tabellen leeren
                        $sql = rex_sql::factory();
                        $sql->setQuery('DELETE FROM '.rex::getTable('consent_manager_cookie'));
                        $sql->setQuery('DELETE FROM '.rex::getTable('consent_manager_cookiegroup'));
                        $sql->setQuery('DELETE FROM '.rex::getTable('consent_manager_text'));
                        $sql->setQuery('DELETE FROM '.rex::getTable('consent_manager_domain'));
                        
                        // Importierte Daten einfügen
                        $tables = ['cookies', 'cookiegroups', 'texts', 'domains'];
                        $table_map = [
                            'cookies' => 'consent_manager_cookie',
                            'cookiegroups' => 'consent_manager_cookiegroup',
                            'texts' => 'consent_manager_text',
                            'domains' => 'consent_manager_domain'
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
                        
                        echo rex_view::success($addon->i18n('consent_manager_import_json_successful'));
                    } catch (rex_sql_exception $e) {
                        echo rex_view::error($addon->i18n('consent_manager_import_json_error') . ': ' . $e->getMessage());
                    }
                } else {
                    echo rex_view::error($addon->i18n('consent_manager_import_json_invalid'));
                }
            } else {
                echo rex_view::error($addon->i18n('consent_manager_import_json_no_file'));
            }
        }
    }
}

// Import/Export UI und Settings Layout
$sql = rex_sql::factory();
$sql->setQuery('SELECT COUNT(*) as count FROM '.rex::getTable('consent_manager_cookie'));
$cookie_count = $sql->getValue('count');

// Settings Form erstellen mit verbesserter Struktur
$form = rex_config_form::factory(strval($addon->getPackageId()));
$form->addFieldset($addon->i18n('consent_manager_config_legend'));

// CSS Output Einstellung
$field = $form->addCheckboxField('outputowncss');
$field->setLabel($addon->i18n('consent_manager_config_owncss'));
$field->addOption($addon->i18n('consent_manager_config_owncss'), 1);
$field->setNotice($addon->i18n('consent_manager_config_owncss_desc'));

// Body Scrollbar Einstellung
$field = $form->addCheckboxField('hidebodyscrollbar');
$field->setLabel($addon->i18n('consent_manager_config_hidebodyscrollbar'));
$field->addOption($addon->i18n('consent_manager_config_hidebodyscrollbar'), 1);
$field->setNotice($addon->i18n('consent_manager_config_hidebodyscrollbar_desc'));

// Cookie Lebensdauer
$field = $form->addTextField('lifespan');
$field->setLabel($addon->i18n('consent_manager_config_lifespan_label'));
$field->setAttribute('type', 'number');
$field->setAttribute('step', '1');
$field->setAttribute('pattern', '[0-9]*');
$field->setAttribute('placeholder', '365');
$field->setNotice($addon->i18n('consent_manager_config_lifespan_notice'));

// Token Einstellungen Fieldset
$form->addFieldset($addon->i18n('consent_manager_config_token_legend'));
$field = $form->addTextField('skip_consent');
$field->setLabel($addon->i18n('consent_manager_config_token_label'));
$field->setNotice($addon->i18n('consent_manager_config_token_notice'));

// Layout mit vereinfachten Setup-Optionen
echo '<div class="rex-addon-output">
    <!-- Schnellstart Button über beiden Panels -->
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-md-12 text-right">
            <button type="button" class="btn btn-warning btn-lg" data-toggle="modal" data-target="#quickstart-modal" style="padding: 12px 25px; font-weight: bold; box-shadow: 0 4px 8px rgba(0,0,0,0.2); border-radius: 6px;">
                <i class="rex-icon fa-rocket" style="margin-right: 8px; font-size: 18px;"></i> 
                <strong>'.$addon->i18n('consent_manager_quickstart_button').'</strong>
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Linke Spalte: Einstellungen (8 Spalten) -->
        <div class="col-md-8">
            <div class="panel panel-edit">
                <header class="panel-heading">
                    <div class="panel-title">
                        <i class="rex-icon fa-cogs"></i> Consent-Manager Einstellungen
                    </div>
                </header>
                <div class="panel-body">
                    '.$form->get().'
                </div>
            </div>
        </div>
        
        <!-- Rechte Spalte: Setup & Import/Export (4 Spalten) -->
        <div class="col-md-4">
            <!-- Schnellstart Panel -->
            <div class="panel panel-primary" style="margin-bottom: 20px;">
                <header class="panel-heading">
                    <div class="panel-title">
                        <i class="rex-icon fa-rocket"></i> Schnellstart - Setup importieren
                    </div>
                </header>
                <div class="panel-body">
                    <p><strong>Wählen Sie ein Setup zum schnellen Start:</strong></p>
                    
                    <!-- Minimal Setup -->
                    <div class="well" style="margin-bottom: 15px; padding: 15px;">
                        <h5><i class="rex-icon fa-shield text-success"></i> <strong>Minimal Setup</strong></h5>
                        <p style="margin-bottom: 12px; color: #666; font-size: 13px;">
                            Nur technisch notwendige Cookies für DSGVO-Compliance. 
                            Perfekt für einfache Websites ohne Tracking.
                        </p>
                        <div class="text-center">
                            <a href="'.rex_url::currentBackendPage(['func' => 'setup_minimal']).'" 
                               class="btn btn-success btn-sm" style="width: 48%; margin-right: 2%;"
                               onclick="return confirm(\'Minimal Setup importieren?\\n\\nACHTUNG: Alle aktuellen Einstellungen werden überschrieben!\')">
                                <i class="rex-icon fa-download"></i> Komplett laden
                            </a>
                            <a href="'.rex_url::currentBackendPage(['func' => 'setup_minimal_update']).'" 
                               class="btn btn-outline btn-success btn-sm" style="width: 48%;"
                               onclick="return confirm(\'Minimal Setup Update?\\n\\nNur neue Services werden hinzugefügt, bestehende bleiben unverändert.\')">
                                <i class="rex-icon fa-plus"></i> Nur Neue
                            </a>
                        </div>
                    </div>
                    
                    <!-- Standard Setup -->
                    <div class="well" style="margin-bottom: 0; padding: 15px;">
                        <h5><i class="rex-icon fa-cog text-primary"></i> <strong>Standard Setup</strong></h5>
                        <p style="margin-bottom: 12px; color: #666; font-size: 13px;">
                            Umfassendes Setup mit Google Analytics, Facebook Pixel, YouTube, 
                            Google Maps und anderen wichtigen Services.
                        </p>
                        <div class="text-center">
                            <a href="'.rex_url::currentBackendPage(['func' => 'setup_standard']).'" 
                               class="btn btn-primary btn-sm" style="width: 48%; margin-right: 2%;"
                               onclick="return confirm(\'Standard Setup importieren?\\n\\nACHTUNG: Alle aktuellen Einstellungen werden überschrieben!\')">
                                <i class="rex-icon fa-download"></i> Komplett laden
                            </a>
                            <a href="'.rex_url::currentBackendPage(['func' => 'setup_standard_update']).'" 
                               class="btn btn-outline btn-primary btn-sm" style="width: 48%;"
                               onclick="return confirm(\'Standard Setup Update?\\n\\nNur neue Services werden hinzugefügt, bestehende bleiben unverändert.\')">
                                <i class="rex-icon fa-plus"></i> Nur Neue
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Export -->
            <div class="panel panel-success" style="margin-bottom: 15px;">
                <header class="panel-heading">
                    <div class="panel-title">
                        <i class="rex-icon fa-upload"></i> Konfiguration exportieren
                    </div>
                </header>
                <div class="panel-body">
                    <p>Exportieren Sie Ihre aktuelle Konfiguration als JSON-Datei zum Backup oder zur Übertragung.</p>
                    <div class="text-center">
                        <a href="'.rex_url::currentBackendPage(['func' => 'export'] + $csrf->getUrlParams()).'" 
                           class="btn btn-success btn-sm">
                            <i class="rex-icon fa-download"></i> JSON exportieren
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- JSON Import -->
            <div class="panel panel-info">
                <header class="panel-heading">
                    <div class="panel-title">
                        <i class="rex-icon fa-file-code-o"></i> JSON-Konfiguration importieren
                    </div>
                </header>
                <div class="panel-body">
                    <p>Importieren Sie eine zuvor exportierte JSON-Konfiguration.</p>
                    <form action="'.rex_url::currentBackendPage().'" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="func" value="import_json" />
                        '.rex_csrf_token::factory('consent_manager_config')->getHiddenField().'
                        <div class="form-group">
                            <input type="file" class="form-control" id="import_file" name="import_file" accept=".json" required>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-info btn-sm">
                                <i class="rex-icon fa-upload"></i> JSON importieren
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Nach dem Import - Nächste Schritte -->
    <div class="row" style="margin-top: 30px;">
        <div class="col-md-12">
            <div class="alert alert-info">
                <h4><i class="rex-icon fa-info-circle"></i> Nach dem Setup Import:</h4>
                <div class="row">
                    <div class="col-md-3">
                        <strong>1. Domain konfigurieren</strong><br>
                        <a href="'.rex_url::currentBackendPage(['page' => 'consent_manager/domain']).'">
                            <i class="rex-icon fa-globe"></i> Zu Domains
                        </a>
                    </div>
                    <div class="col-md-3">
                        <strong>2. Services anpassen</strong><br>
                        <a href="'.rex_url::currentBackendPage(['page' => 'consent_manager/cookie']).'">
                            <i class="rex-icon fa-cog"></i> Zu Services
                        </a>
                    </div>
                    <div class="col-md-3">
                        <strong>3. Texte anpassen</strong><br>
                        <a href="'.rex_url::currentBackendPage(['page' => 'consent_manager/text']).'">
                            <i class="rex-icon fa-edit"></i> Zu Texte
                        </a>
                    </div>
                    <div class="col-md-3">
                        <strong>4. Design wählen</strong><br>
                        <a href="'.rex_url::currentBackendPage(['page' => 'consent_manager/theme']).'">
                            <i class="rex-icon fa-paint-brush"></i> Zu Themes
                        </a>
                    </div>
                </div>
                <hr>
                <p><strong>Template-Code:</strong> <code>&lt;?php echo REX_CONSENT_MANAGER[]; ?&gt;</code> 
                (vor dem schließenden &lt;/body&gt;-Tag einbinden)</p>
            </div>
        </div>
    </div>
</div>

<!-- Schnellstart Modal -->
<div class="modal fade" id="quickstart-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="rex-icon fa-rocket"></i> '.$addon->i18n('consent_manager_quickstart_title').'
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <strong>'.$addon->i18n('consent_manager_quickstart_welcome').'</strong>
                        </div>
                        <div class="panel-group" id="quickstart-accordion">
                            <!-- Step 1: Import Setup (recommended) -->
                            <div class="panel panel-success">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        <span class="label label-success">1</span> '.$addon->i18n('consent_manager_quickstart_step_import').' <small class="text-success">('.$addon->i18n('consent_manager_quickstart_status_recommended').')</small>
                                    </h5>
                                </div>
                                <div class="panel-body">
                                    <p><strong>'.$addon->i18n('consent_manager_quickstart_step_import_desc').'</strong></p>
                                    
                                    <!-- Minimal und Standard Setup nebeneinander -->
                                    <div class="row" style="margin-bottom: 15px;">
                                        <div class="col-md-6">
                                            <div class="well well-sm">
                                                <h6><i class="rex-icon fa-shield text-success"></i> <strong>'.$addon->i18n('consent_manager_setup_minimal_title').'</strong></h6>
                                                <p style="font-size: 12px; margin-bottom: 10px; color: #666;">
                                                    '.$addon->i18n('consent_manager_setup_minimal_desc').'
                                                </p>
                                                <div class="text-center">
                                                    <a href="'.rex_url::currentBackendPage(['func' => 'setup_minimal']).'" 
                                                       class="btn btn-success btn-sm" style="width: 100%;"
                                                       data-confirm="'.$addon->i18n('consent_manager_setup_minimal_confirm').'">
                                                        <i class="rex-icon fa-download"></i> '.$addon->i18n('consent_manager_setup_minimal_button').'
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="well well-sm">
                                                <h6><i class="rex-icon fa-cog text-primary"></i> <strong>'.$addon->i18n('consent_manager_setup_standard_title').'</strong></h6>
                                                <p style="font-size: 12px; margin-bottom: 10px; color: #666;">
                                                    '.$addon->i18n('consent_manager_setup_standard_desc').'
                                                </p>
                                                <div class="text-center">
                                                    <a href="'.rex_url::currentBackendPage(['func' => 'setup_standard']).'" 
                                                       class="btn btn-primary btn-sm" style="width: 100%;"
                                                       data-confirm="'.$addon->i18n('consent_manager_setup_standard_confirm').'">
                                                        <i class="rex-icon fa-download"></i> '.$addon->i18n('consent_manager_setup_standard_button').'
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info" style="margin-bottom: 0;">
                                        <strong><i class="rex-icon fa-info-circle"></i> '.$addon->i18n('consent_manager_quickstart_import_hint').'</strong>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Step 2: Configure Domain -->
                            <div class="panel panel-info">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        <span class="label label-info">2</span> '.$addon->i18n('consent_manager_quickstart_step_domain').' <small class="text-warning">('.$addon->i18n('consent_manager_quickstart_status_required').')</small>
                                    </h5>
                                </div>
                                <div class="panel-body">
                                    <p>'.$addon->i18n('consent_manager_quickstart_step_domain_desc').'</p>
                                    <a href="'.rex_url::currentBackendPage(['page' => 'consent_manager/domain']).'" class="btn btn-sm btn-info">
                                        <i class="rex-icon fa-globe"></i> '.$addon->i18n('consent_manager_quickstart_btn_domain').'
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Step 3: Create Cookie Groups -->
                            <div class="panel panel-warning">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        <span class="label label-warning">3</span> '.$addon->i18n('consent_manager_quickstart_step_cookiegroups').' <small class="text-muted">('.$addon->i18n('consent_manager_quickstart_status_after_import').')</small>
                                    </h5>
                                </div>
                                <div class="panel-body">
                                    <p>'.$addon->i18n('consent_manager_quickstart_step_cookiegroups_desc').'</p>
                                    <a href="'.rex_url::currentBackendPage(['page' => 'consent_manager/cookiegroup']).'" class="btn btn-sm btn-warning">
                                        <i class="rex-icon fa-folder"></i> '.$addon->i18n('consent_manager_quickstart_btn_cookiegroups').'
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Step 4: Define Services/Cookies -->
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        <span class="label label-primary">4</span> '.$addon->i18n('consent_manager_quickstart_step_services').' <small class="text-muted">('.$addon->i18n('consent_manager_quickstart_status_after_import').')</small>
                                    </h5>
                                </div>
                                <div class="panel-body">
                                    <p>'.$addon->i18n('consent_manager_quickstart_step_services_desc').'</p>
                                    <a href="'.rex_url::currentBackendPage(['page' => 'consent_manager/cookie']).'" class="btn btn-sm btn-primary">
                                        <i class="rex-icon fa-cog"></i> '.$addon->i18n('consent_manager_quickstart_btn_services').'
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Step 5: Customize Texts -->
                            <div class="panel panel-success">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        <span class="label label-success">5</span> '.$addon->i18n('consent_manager_quickstart_step_texts').' <small class="text-muted">('.$addon->i18n('consent_manager_quickstart_status_after_import').')</small>
                                    </h5>
                                </div>
                                <div class="panel-body">
                                    <p>'.$addon->i18n('consent_manager_quickstart_step_texts_desc').'</p>
                                    <a href="'.rex_url::currentBackendPage(['page' => 'consent_manager/text']).'" class="btn btn-sm btn-success">
                                        <i class="rex-icon fa-edit"></i> '.$addon->i18n('consent_manager_quickstart_btn_texts').'
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Step 6: Choose Design -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        <span class="label label-default">6</span> '.$addon->i18n('consent_manager_quickstart_step_theme').' <small class="text-warning">('.$addon->i18n('consent_manager_quickstart_status_required').')</small>
                                    </h5>
                                </div>
                                <div class="panel-body">
                                    <p>'.$addon->i18n('consent_manager_quickstart_step_theme_desc').'</p>
                                    <a href="'.rex_url::currentBackendPage(['page' => 'consent_manager/theme']).'" class="btn btn-sm btn-default">
                                        <i class="rex-icon fa-paint-brush"></i> '.$addon->i18n('consent_manager_quickstart_btn_theme').'
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Step 7: Template Integration -->
                            <div class="panel panel-success">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        <span class="label label-success">7</span> '.$addon->i18n('consent_manager_quickstart_step_template').' <small class="text-success">('.$addon->i18n('consent_manager_quickstart_status_final').')</small>
                                    </h5>
                                </div>
                                <div class="panel-body">
                                    <p><strong>'.$addon->i18n('consent_manager_quickstart_step_template_desc').'</strong></p>
                                    <div class="well well-sm">
                                        <p><strong>'.$addon->i18n('consent_manager_quickstart_template_code_label').'</strong></p>
                                        <pre style="background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 12px;">&lt;?php echo REX_CONSENT_MANAGER[]; ?&gt;</pre>
                                        <p class="help-block" style="margin-bottom: 0;">
                                            <i class="fa fa-info-circle"></i> '.$addon->i18n('consent_manager_quickstart_template_code_info').'
                                        </p>
                                    </div>
                                    <div class="well well-sm" style="margin-top: 15px;">
                                        <p><strong>'.$addon->i18n('consent_manager_privacy_link_label').'</strong></p>
                                        <p class="help-block">'.$addon->i18n('consent_manager_privacy_link_desc').'</p>
                                        <pre style="background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 11px;">&lt;a href="#" class="consent_manager-show-box"&gt;'.$addon->i18n('consent_manager_quickstart_privacy_settings_link').'&lt;/a&gt;</pre>
                                    </div>
                                    <div class="alert alert-success" style="margin-bottom: 0;">
                                        <i class="fa fa-check-circle"></i> <strong>'.$addon->i18n('consent_manager_quickstart_template_final_message').'</strong>
                                        <br><br>
                                        <p style="margin-bottom: 0;">
                                            <i class="fa fa-cog"></i> '.$addon->i18n('consent_manager_quickstart_privacy_settings_info').' 
                                            <a class="consent_manager-show-box">'.$addon->i18n('consent_manager_quickstart_privacy_settings_link').'</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Schließen</button>
            </div>
        </div>
    </div>
</div>';

if ('' !== rex_post('_csrf_token', 'string', '')) {
    consent_manager_theme::generateDefaultAssets();
    consent_manager_theme::copyAllAssets();
}
