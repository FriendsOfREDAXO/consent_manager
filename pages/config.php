<?php

$addon = rex_addon::get('consent_manager');

$func = rex_request('func', 'string');

// Import/Export Funktionalität
// Spezialbehandlung für den Default-Import ohne CSRF-Check
if ('setup' === $func) {
    $file = rex_path::addon('consent_manager').'setup/setup.sql';
    rex_sql_util::importDump($file);
    consent_manager_clang::addonJustInstalled();
    echo rex_view::success($addon->i18n('consent_manager_setup_import_successful'));
    // Weiterleitung zur normalen Config-Seite ohne func Parameter
    echo '<script>setTimeout(function() { window.location.href = "'.rex_url::currentBackendPage(['page' => 'consent_manager/config']).'"; }, 2000);</script>';
}

// Für alle anderen Funktionen CSRF-Check
$csrf = rex_csrf_token::factory('consent_manager_config');
if ('' !== $func && 'setup' !== $func) {
    if (!$csrf->isValid()) {
        echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } else {
        if ('export' === $func) {
            // Output-Buffer leeren um sauberen JSON-Download zu ermöglichen
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Export der aktuellen Konfiguration
            $export_data = [];
            
            // Cookies/Services exportieren
            $sql = rex_sql::factory();
            $sql->setQuery('SELECT * FROM '.rex::getTable('consent_manager_cookie').' ORDER BY id');
            $export_data['cookies'] = $sql->getArray();
            
            // Cookie-Gruppen exportieren
            $sql->setQuery('SELECT * FROM '.rex::getTable('consent_manager_cookiegroup').' ORDER BY prio, id');
            $export_data['cookiegroups'] = $sql->getArray();
            
            // Texte exportieren
            $sql->setQuery('SELECT * FROM '.rex::getTable('consent_manager_text').' ORDER BY clang_id, id');
            $export_data['texts'] = $sql->getArray();
            
            // Domains exportieren
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

// Layout mit optimierter Spaltenaufteilung (2/3 zu 1/3)
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
        <!-- Linke Spalte: Einstellungen (2/3 Breite) -->
        <div class="col-md-8">
            <div class="panel panel-edit">
                <header class="panel-heading">
                    <div class="panel-title">
                        <i class="rex-icon fa-cogs"></i> '.$addon->i18n('consent_manager_config_title').'
                    </div>
                </header>
                <div class="panel-body">
                    '.$form->get().'
                </div>
            </div>
        </div>
        
        <!-- Rechte Spalte: Import/Export (1/3 Breite) -->
        <div class="col-md-4">
            <!-- Standard-Setup -->
            <div class="panel panel-primary" style="margin-bottom: 15px;">
                <header class="panel-heading">
                    <div class="panel-title">
                        <i class="rex-icon fa-download"></i> '.$addon->i18n('consent_manager_setup_headline').'
                    </div>
                </header>
                <div class="panel-body">
                    <p>'.$addon->i18n('consent_manager_setup_info').'</p>
                    <div class="text-center">
                        <a href="'.rex_url::currentBackendPage(['func' => 'setup']).'" 
                           class="btn btn-primary btn-sm" 
                           data-confirm="'.$addon->i18n('consent_manager_setup_import_confirm').'">
                            <i class="rex-icon fa-download"></i> '.$addon->i18n('consent_manager_setup_import').'
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Export -->
            <div class="panel panel-success" style="margin-bottom: 15px;">
                <header class="panel-heading">
                    <div class="panel-title">
                        <i class="rex-icon fa-upload"></i> '.$addon->i18n('consent_manager_export_headline').'
                    </div>
                </header>
                <div class="panel-body">
                    <p>'.$addon->i18n('consent_manager_export_info').'</p>
                    <div class="text-center">
                        <a href="'.rex_url::currentBackendPage(['func' => 'export'] + $csrf->getUrlParams()).'" 
                           class="btn btn-success btn-sm">
                            <i class="rex-icon fa-download"></i> '.$addon->i18n('consent_manager_export_download').'
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- JSON Import -->
            <div class="panel panel-info">
                <header class="panel-heading">
                    <div class="panel-title">
                        <i class="rex-icon fa-file-code-o"></i> '.$addon->i18n('consent_manager_import_headline').'
                    </div>
                </header>
                <div class="panel-body">
                    <p>'.$addon->i18n('consent_manager_import_info').'</p>
                    <form action="'.rex_url::currentBackendPage().'" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="func" value="import_json" />
                        '.rex_csrf_token::factory('consent_manager_config')->getHiddenField().'
                        <div class="form-group">
                            <label for="import_file" class="sr-only">'.$addon->i18n('consent_manager_import_file_label').'</label>
                            <input type="file" class="form-control" id="import_file" name="import_file" accept=".json" required>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-info btn-sm">
                                <i class="rex-icon fa-upload"></i> '.$addon->i18n('consent_manager_import_upload').'
                            </button>
                        </div>
                    </form>
                </div>
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
                        </div>                        <div class="panel-group" id="quickstart-accordion">
                            <!-- Schritt 1: Standard-Setup importieren (empfohlen) -->
                            <div class="panel panel-success">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        <span class="label label-success">1</span> '.$addon->i18n('consent_manager_quickstart_step_import').' <small class="text-success">('.$addon->i18n('consent_manager_quickstart_status_recommended').')</small>
                                    </h5>
                                </div>
                                <div class="panel-body">
                                    <p><strong>'.$addon->i18n('consent_manager_quickstart_step_import_desc').'</strong></p>
                                    <div class="text-center">
                                        <a href="'.rex_url::currentBackendPage(['func' => 'setup']).'" 
                                           class="btn btn-success btn-lg" 
                                           data-confirm="'.$addon->i18n('consent_manager_setup_import_confirm').'">
                                            <i class="rex-icon fa-download"></i> '.$addon->i18n('consent_manager_setup_import').'
                                        </a>
                                    </div>
                                    <div class="alert alert-info" style="margin-top: 15px;">
                                        <strong><i class="rex-icon fa-info-circle"></i> '.$addon->i18n('consent_manager_quickstart_import_hint').'</strong>
                                    </div>
                                    <hr>
                                    <p class="text-muted"><small><strong>'.$addon->i18n('consent_manager_quickstart_manual_config').'</strong></small></p>
                                </div>
                            </div>
                            
                            <!-- Schritt 2: Domain konfigurieren -->
                            <div class="panel panel-info">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        <span class="label label-info">2</span> '.$addon->i18n('consent_manager_quickstart_step_domain').' <small class="text-warning">('.$addon->i18n('consent_manager_quickstart_status_required').')</small>
                                    </h5>
                                </div>
                                <div class="panel-body">
                                    <p>'.$addon->i18n('consent_manager_quickstart_step_domain_desc').'</p>
                                    <a href="'.rex_url::currentBackendPage(['page' => 'consent_manager/domain']).'" class="btn btn-sm btn-info">
                                        <i class="rex-icon fa-globe"></i> Domain verwalten
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Schritt 3: Cookie-Gruppen erstellen -->
                            <div class="panel panel-warning">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        <span class="label label-warning">3</span> Cookie-Gruppen erstellen <small class="text-muted">(nach Import erledigt ✓)</small>
                                    </h5>
                                </div>
                                <div class="panel-body">
                                    <p>Cookie-Gruppen wie "Technisch notwendig", "Marketing", "Analyse" anlegen.</p>
                                    <a href="'.rex_url::currentBackendPage(['page' => 'consent_manager/cookiegroup']).'" class="btn btn-sm btn-warning">
                                        <i class="rex-icon fa-folder"></i> Gruppen verwalten
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Schritt 4: Services/Cookies definieren -->
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        <span class="label label-primary">4</span> Services/Cookies definieren <small class="text-muted">(nach Import erledigt ✓)</small>
                                    </h5>
                                </div>
                                <div class="panel-body">
                                    <p>Konkrete Services wie Google Analytics, Facebook Pixel etc. konfigurieren.</p>
                                    <a href="'.rex_url::currentBackendPage(['page' => 'consent_manager/cookie']).'" class="btn btn-sm btn-warning">
                                        <i class="rex-icon fa-cog"></i> Services verwalten
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Schritt 5: Texte anpassen -->
                            <div class="panel panel-success">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        <span class="label label-success">5</span> Texte anpassen <small class="text-muted">(nach Import erledigt ✓)</small>
                                    </h5>
                                </div>
                                <div class="panel-body">
                                    <p>Banner-Texte, Datenschutzerklärung und weitere Inhalte individualisieren.</p>
                                    <a href="'.rex_url::currentBackendPage(['page' => 'consent_manager/text']).'" class="btn btn-sm btn-success">
                                        <i class="rex-icon fa-edit"></i> Texte bearbeiten
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Schritt 6: Design wählen -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        <span class="label label-default">6</span> Design wählen <small class="text-warning">(immer erforderlich)</small>
                                    </h5>
                                </div>
                                <div class="panel-body">
                                    <p>Passende Darstellung für den Cookie-Banner wählen oder anpassen.</p>
                                    <a href="'.rex_url::currentBackendPage(['page' => 'consent_manager/theme']).'" class="btn btn-sm btn-primary">
                                        <i class="rex-icon fa-paint-brush"></i> Theme wählen
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Schritt 7: Template-Einbindung -->
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
                                    <div class="alert alert-success" style="margin-bottom: 0;">
                                        <i class="fa fa-check-circle"></i> <strong>'.$addon->i18n('consent_manager_quickstart_template_final_message').'</strong>
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
