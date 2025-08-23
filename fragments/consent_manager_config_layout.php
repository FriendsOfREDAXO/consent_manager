<?php
/**
 * Fragment: Consent Manager Config Layout
 * Hauptlayout für die Konfigurationsseite
 */

$addon = $this->getVar('addon');
$form = $this->getVar('form');
$csrf = $this->getVar('csrf');
?>

<div class="rex-addon-output">
    <!-- Schnellstart Button über beiden Panels -->
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-md-12 text-right">
            <button type="button" class="btn btn-warning btn-lg" data-toggle="modal" data-target="#quickstart-modal" style="padding: 12px 25px; font-weight: bold; box-shadow: 0 4px 8px rgba(0,0,0,0.2); border-radius: 6px;">
                <i class="rex-icon fa-rocket" style="margin-right: 8px; font-size: 18px;"></i> 
                <strong><?= $addon->i18n('consent_manager_quickstart_button') ?></strong>
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
                    <?php if ($form): ?>
                        <?= $form->get() ?>
                    <?php else: ?>
                        <p>Form konnte nicht geladen werden.</p>
                    <?php endif; ?>
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
                            <a href="<?= rex_url::currentBackendPage(['func' => 'setup_minimal']) ?>" 
                               class="btn btn-success btn-sm" style="width: 48%; margin-right: 2%;"
                               onclick="return confirm('Minimal Setup importieren?\n\nACHTUNG: Alle aktuellen Einstellungen werden überschrieben!')">
                                <i class="rex-icon fa-download"></i> Komplett laden
                            </a>
                            <a href="<?= rex_url::currentBackendPage(['func' => 'setup_minimal_update']) ?>" 
                               class="btn btn-outline btn-success btn-sm" style="width: 48%;"
                               onclick="return confirm('Minimal Setup Update?\n\nNur neue Services werden hinzugefügt, bestehende bleiben unverändert.')">
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
                            <a href="<?= rex_url::currentBackendPage(['func' => 'setup_standard']) ?>" 
                               class="btn btn-primary btn-sm" style="width: 48%; margin-right: 2%;"
                               onclick="return confirm('Standard Setup importieren?\n\nACHTUNG: Alle aktuellen Einstellungen werden überschrieben!')">
                                <i class="rex-icon fa-download"></i> Komplett laden
                            </a>
                            <a href="<?= rex_url::currentBackendPage(['func' => 'setup_standard_update']) ?>" 
                               class="btn btn-outline btn-primary btn-sm" style="width: 48%;"
                               onclick="return confirm('Standard Setup Update?\n\nNur neue Services werden hinzugefügt, bestehende bleiben unverändert.')">
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
                        <a href="<?= rex_url::currentBackendPage(['func' => 'export'] + $csrf->getUrlParams()) ?>" 
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
                    <form action="<?= rex_url::currentBackendPage() ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="func" value="import_json" />
                        <?= rex_csrf_token::factory('consent_manager_config')->getHiddenField() ?>
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
                        <a href="<?= rex_url::currentBackendPage(['page' => 'consent_manager/domain']) ?>">
                            <i class="rex-icon fa-globe"></i> Zu Domains
                        </a>
                    </div>
                    <div class="col-md-3">
                        <strong>2. Services anpassen</strong><br>
                        <a href="<?= rex_url::currentBackendPage(['page' => 'consent_manager/cookie']) ?>">
                            <i class="rex-icon fa-cog"></i> Zu Services
                        </a>
                    </div>
                    <div class="col-md-3">
                        <strong>3. Texte anpassen</strong><br>
                        <a href="<?= rex_url::currentBackendPage(['page' => 'consent_manager/text']) ?>">
                            <i class="rex-icon fa-edit"></i> Zu Texte
                        </a>
                    </div>
                    <div class="col-md-3">
                        <strong>4. Design wählen</strong><br>
                        <a href="<?= rex_url::currentBackendPage(['page' => 'consent_manager/theme']) ?>">
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
