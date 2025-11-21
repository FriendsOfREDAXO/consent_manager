<?php
/**
 * Fragment: Consent Manager Config Layout
 * Hauptlayout für die Konfigurationsseite.
 *
 *
 * TODO: hier die Schnittstelle beschreiben:
 * - Welche Vars werden vom Fragment erwartet
 * - Welchen Typ haben die Vars
 * - Welchen Default-Wert haben optionale Vars
 * - Welche Vars sind mandatory und was passiert wenn sie fehlen (return oder Exception)

 */

/** @var rex_fragment $this */

/** @var ?rex_form $form */
$form = $this->getVar('form');

/** @var ?rex_csrf_token $csrf */
$csrf = $this->getVar('csrf');

?>

<div class="rex-addon-output">
    <!-- Schnellstart Button über beiden Panels -->
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-md-12 text-right">
            <button type="button" class="btn btn-warning btn-lg" data-toggle="modal" data-target="#quickstart-modal" style="padding: 12px 25px; font-weight: bold; box-shadow: 0 4px 8px rgba(0,0,0,0.2); border-radius: 6px;">
                <i class="rex-icon fa-rocket" style="margin-right: 8px; font-size: 18px;"></i> 
                <strong><?= rex_i18n::msg('consent_manager_quickstart_button') ?></strong>
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Linke Spalte: Einstellungen (8 Spalten) -->
        <div class="col-md-8">
            <div class="panel panel-edit">
                <header class="panel-heading">
                    <div class="panel-title">
                        <i class="rex-icon fa-cogs"></i> <?= rex_i18n::msg('consent_manager_config_settings_title') ?>
                    </div>
                </header>
                <div class="panel-body">
                    <?php if (null !== $form): ?>
                        <?= $form->get() ?>
                    <?php else: ?>
                        <p>Form konnte nicht geladen werden.</p>
                    <?php endif ?>
                </div>
            </div>
        </div>
        
        <!-- Rechte Spalte: Setup & Import/Export (4 Spalten) -->
        <div class="col-md-4">
            <!-- Schnellstart Panel -->
            <div class="panel panel-primary" style="margin-bottom: 20px;">
                <header class="panel-heading">
                    <div class="panel-title">
                        <i class="rex-icon fa-rocket"></i> <?= rex_i18n::msg('consent_manager_config_quickstart_title') ?>
                    </div>
                </header>
                <div class="panel-body">
                    <p><strong><?= rex_i18n::msg('consent_manager_config_choose_setup') ?></strong></p>
                    
                    <!-- Standard Setup -->
                    <div class="well" style="margin-bottom: 15px; padding: 15px;">
                        <h5><i class="rex-icon fa-cog text-primary"></i> <strong><?= rex_i18n::msg('consent_manager_config_standard_setup_title') ?></strong></h5>
                        <p style="margin-bottom: 12px; color: #666; font-size: 13px;">
                            <?= rex_i18n::msg('consent_manager_config_standard_setup_desc') ?>
                        </p>
                        <div class="text-center">
                            <a href="<?= rex_url::currentBackendPage(['func' => 'setup_standard']) ?>" 
                               class="btn btn-primary btn-sm" style="width: 48%; margin-right: 2%;"
                               onclick="return confirm('<?= rex_i18n::msg('consent_manager_config_standard_confirm') ?>')">
                                <i class="rex-icon fa-download"></i> <?= rex_i18n::msg('consent_manager_config_load_complete') ?>
                            </a>
                            <a href="<?= rex_url::currentBackendPage(['func' => 'setup_standard_update']) ?>" 
                               class="btn btn-outline btn-primary btn-sm" style="width: 48%;"
                               onclick="return confirm('<?= rex_i18n::msg('consent_manager_config_standard_update_confirm') ?>')">
                                <i class="rex-icon fa-plus"></i> <?= rex_i18n::msg('consent_manager_config_load_new_only') ?>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Minimal Setup -->
                    <div class="well" style="margin-bottom: 0; padding: 15px;">
                        <h5><i class="rex-icon fa-shield text-success"></i> <strong><?= rex_i18n::msg('consent_manager_config_minimal_setup_title') ?></strong></h5>
                        <p style="margin-bottom: 12px; color: #666; font-size: 13px;">
                            <?= rex_i18n::msg('consent_manager_config_minimal_setup_desc') ?>
                        </p>
                        <div class="text-center">
                            <a href="<?= rex_url::currentBackendPage(['func' => 'setup_minimal']) ?>" 
                               class="btn btn-success btn-sm" style="width: 48%; margin-right: 2%;"
                               onclick="return confirm('<?= rex_i18n::msg('consent_manager_config_minimal_confirm') ?>')">
                                <i class="rex-icon fa-download"></i> <?= rex_i18n::msg('consent_manager_config_load_complete') ?>
                            </a>
                            <a href="<?= rex_url::currentBackendPage(['func' => 'setup_minimal_update']) ?>" 
                               class="btn btn-outline btn-success btn-sm" style="width: 48%;"
                               onclick="return confirm('<?= rex_i18n::msg('consent_manager_config_minimal_update_confirm') ?>')">
                                <i class="rex-icon fa-plus"></i> <?= rex_i18n::msg('consent_manager_config_load_new_only') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Export -->
            <div class="panel panel-success" style="margin-bottom: 15px;">
                <header class="panel-heading">
                    <div class="panel-title">
                        <i class="rex-icon fa-upload"></i> <?= rex_i18n::msg('consent_manager_config_export_title') ?>
                    </div>
                </header>
                <div class="panel-body">
                    <p><?= rex_i18n::msg('consent_manager_config_export_desc') ?></p>
                    <div class="text-center">
                        <a href="<?= rex_url::currentBackendPage(['func' => 'export'] + $csrf->getUrlParams()) ?>" 
                           class="btn btn-success btn-sm">
                            <i class="rex-icon fa-download"></i> <?= rex_i18n::msg('consent_manager_config_export_button') ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- JSON Import -->
            <div class="panel panel-info">
                <header class="panel-heading">
                    <div class="panel-title">
                        <i class="rex-icon fa-file-code-o"></i> <?= rex_i18n::msg('consent_manager_config_import_title') ?>
                    </div>
                </header>
                <div class="panel-body">
                    <p><?= rex_i18n::msg('consent_manager_config_import_desc') ?></p>
                    <form action="<?= rex_url::currentBackendPage() ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="func" value="import_json" />
                        <?= rex_csrf_token::factory('consent_manager_config')->getHiddenField() ?>
                        <div class="form-group">
                            <input type="file" class="form-control" id="import_file" name="import_file" accept=".json" required>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-info btn-sm">
                                <i class="rex-icon fa-upload"></i> <?= rex_i18n::msg('consent_manager_config_import_button') ?>
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
                <h4><i class="rex-icon fa-info-circle"></i> <?= rex_i18n::msg('consent_manager_config_after_import_title') ?></h4>
                <div class="row">
                    <div class="col-md-3">
                        <strong><?= rex_i18n::msg('consent_manager_config_step_domains') ?></strong><br>
                        <a href="<?= rex_url::currentBackendPage(['page' => 'consent_manager/domain']) ?>">
                            <i class="rex-icon fa-globe"></i> Zu Domains
                        </a>
                    </div>
                    <div class="col-md-3">
                        <strong><?= rex_i18n::msg('consent_manager_config_step_services') ?></strong><br>
                        <a href="<?= rex_url::currentBackendPage(['page' => 'consent_manager/cookie']) ?>">
                            <i class="rex-icon fa-cog"></i> Zu Services
                        </a>
                    </div>
                    <div class="col-md-3">
                        <strong><?= rex_i18n::msg('consent_manager_config_step_texts') ?></strong><br>
                        <a href="<?= rex_url::currentBackendPage(['page' => 'consent_manager/text']) ?>">
                            <i class="rex-icon fa-edit"></i> Zu Texte
                        </a>
                    </div>
                    <div class="col-md-3">
                        <strong><?= rex_i18n::msg('consent_manager_config_step_theme') ?></strong><br>
                        <a href="<?= rex_url::currentBackendPage(['page' => 'consent_manager/theme']) ?>">
                            <i class="rex-icon fa-paint-brush"></i> Zu Themes
                        </a>
                    </div>
                </div>
                <hr>
                <p><?= rex_i18n::msg('consent_manager_config_template_info') ?></p>
            </div>
        </div>
    </div>
</div>
