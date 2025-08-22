<?php

$addon = rex_addon::get('consent_manager');

$func = rex_request('func', 'string');

// Spezialbehandlung für den Default-Import ohne CSRF-Check
if ('setup' === $func) {
    $file = rex_path::addon('consent_manager').'setup/setup.sql';
    rex_sql_util::importDump($file);
    consent_manager_clang::addonJustInstalled();
    echo rex_view::success($addon->i18n('consent_manager_setup_import_successful'));
    // Weiterleitung zur normalen Setup-Seite ohne func Parameter
    echo '<script>setTimeout(function() { window.location.href = "'.rex_url::currentBackendPage(['page' => 'consent_manager/setup']).'"; }, 2000);</script>';
}

// Für alle anderen Funktionen CSRF-Check
$csrf = rex_csrf_token::factory('consent_manager_setup');
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
            
            // Download-Headers setzen
            $filename = 'consent_manager_export_' . date('Y-m-d_H-i-s') . '.json';
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($json_export));
            echo $json_export;
            exit;
        }
        
        if ('import_json' === $func) {
            // JSON Import verarbeiten
            if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
                $json_content = file_get_contents($_FILES['import_file']['tmp_name']);
                $import_data = json_decode($json_content, true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($import_data)) {
                    try {
                        $sql = rex_sql::factory();
                        
                        // Tabellen leeren (wie beim Standard-Setup)
                        $sql->setQuery('DELETE FROM '.rex::getTable('consent_manager_cookie'));
                        $sql->setQuery('DELETE FROM '.rex::getTable('consent_manager_cookiegroup'));
                        $sql->setQuery('DELETE FROM '.rex::getTable('consent_manager_text'));
                        $sql->setQuery('DELETE FROM '.rex::getTable('consent_manager_domain'));
                        
                        // Cookie-Gruppen importieren
                        if (isset($import_data['cookiegroups'])) {
                            foreach ($import_data['cookiegroups'] as $group) {
                                unset($group['pid']); // Primary Key nicht übernehmen
                                $sql->setTable(rex::getTable('consent_manager_cookiegroup'));
                                foreach ($group as $key => $value) {
                                    $sql->setValue($key, $value);
                                }
                                $sql->insert();
                            }
                        }
                        
                        // Cookies importieren
                        if (isset($import_data['cookies'])) {
                            foreach ($import_data['cookies'] as $cookie) {
                                unset($cookie['pid']); // Primary Key nicht übernehmen
                                $sql->setTable(rex::getTable('consent_manager_cookie'));
                                foreach ($cookie as $key => $value) {
                                    $sql->setValue($key, $value);
                                }
                                $sql->insert();
                            }
                        }
                        
                        // Texte importieren
                        if (isset($import_data['texts'])) {
                            foreach ($import_data['texts'] as $text) {
                                unset($text['pid']); // Primary Key nicht übernehmen
                                $sql->setTable(rex::getTable('consent_manager_text'));
                                foreach ($text as $key => $value) {
                                    $sql->setValue($key, $value);
                                }
                                $sql->insert();
                            }
                        }
                        
                        // Domains importieren
                        if (isset($import_data['domains'])) {
                            foreach ($import_data['domains'] as $domain) {
                                unset($domain['pid']); // Primary Key nicht übernehmen
                                $sql->setTable(rex::getTable('consent_manager_domain'));
                                foreach ($domain as $key => $value) {
                                    $sql->setValue($key, $value);
                                }
                                $sql->insert();
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

// Überprüfung ob bereits Services vorhanden sind
$sql = rex_sql::factory();
$sql->setQuery('SELECT COUNT(*) as count FROM '.rex::getTable('consent_manager_cookie'));
$cookie_count = $sql->getValue('count');

// Schönere Grid-Darstellung mit Karten-Layout
$content = '
<div class="rex-addon-output">
    <div class="row">
        <!-- Standard-Setup Karte -->
        <div class="col-md-6">
            <div class="panel panel-primary">
                <header class="panel-heading">
                    <div class="panel-title">
                        <i class="rex-icon fa-download"></i> '.$addon->i18n('consent_manager_setup_headline').'
                    </div>
                </header>
                <div class="panel-body">
                    <p>'.$addon->i18n('consent_manager_setup_info').'</p>
                    <div class="text-center">
                        <a href="'.rex_url::currentBackendPage(['func' => 'setup']).'" 
                           class="btn btn-primary btn-lg" 
                           data-confirm="'.$addon->i18n('consent_manager_setup_import_confirm').'">
                            <i class="rex-icon fa-download"></i> '.$addon->i18n('consent_manager_setup_import').'
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Import/Export Karte -->
        <div class="col-md-6">
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
                        <a href="'.rex_url::currentBackendPage(['func' => 'export', rex_csrf_token::PARAM => $csrf->getUrlParams()['rex_csrf_token']]).'" 
                           class="btn btn-success btn-lg">
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
                        '.rex_csrf_token::factory('consent_manager_setup')->getHiddenField().'
                        <div class="form-group">
                            <label for="import_file" class="sr-only">'.$addon->i18n('consent_manager_import_file_label').'</label>
                            <input type="file" class="form-control" id="import_file" name="import_file" accept=".json" required>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-info btn-lg">
                                <i class="rex-icon fa-upload"></i> '.$addon->i18n('consent_manager_import_json').'
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>';

echo $content;