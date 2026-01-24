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

// Prüfen ob bereits Domains konfiguriert sind
$sql = rex_sql::factory();
$sql->setQuery('SELECT COUNT(*) as cnt FROM ' . rex::getTable('consent_manager_domain'));
$hasDomains = (int) $sql->getValue('cnt') > 0;

?>

<div class="rex-addon-output">
    <!-- Setup Wizard / Domain Setup Button -->
    <style>
        .quickstart-btn, .setup-domain-btn {
            padding: 15px 30px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
            overflow: visible;
        }
        
        .quickstart-btn::before, .setup-domain-btn::before {
            content: '';
            position: absolute;
            top: -3px;
            left: -3px;
            right: -3px;
            bottom: -3px;
            background: linear-gradient(90deg, #337ab7, #5bc0de, #5cb85c, #337ab7);
            background-size: 300% 300%;
            border-radius: 10px;
            z-index: -1;
            opacity: 0;
            animation: gradient-border 4s ease infinite;
            transition: opacity 0.3s ease;
        }
        
        /* Setup Domain Button - permanente Animation */
        .setup-domain-btn::before {
            opacity: 1;
        }
        
        .quickstart-btn:hover::before {
            opacity: 1;
        }
        
        .quickstart-btn:hover, .setup-domain-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(51, 122, 183, 0.3);
        }
        
        /* Setup Domain Button - pulsierende Animation */
        .setup-domain-btn {
            animation: pulse-scale 2s ease-in-out infinite;
        }
        
        @keyframes gradient-border {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        @keyframes pulse-scale {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        
        /* Dark Mode Support für Button-Text */
        body.rex-theme-dark button.setup-domain-btn,
        body.rex-theme-dark button.setup-domain-btn *,
        body.rex-theme-dark button.quickstart-btn,
        body.rex-theme-dark button.quickstart-btn *,
        body.rex-theme-dark .btn-primary.quickstart-btn,
        body.rex-theme-dark .btn-primary.quickstart-btn *,
        body.rex-theme-dark .btn-success.setup-domain-btn,
        body.rex-theme-dark .btn-success.setup-domain-btn * {
            color: #ffffff !important;
        }
        
        @media (prefers-color-scheme: dark) {
            body:not(.rex-theme-light) button.setup-domain-btn,
            body:not(.rex-theme-light) button.setup-domain-btn *,
            body:not(.rex-theme-light) button.quickstart-btn,
            body:not(.rex-theme-light) button.quickstart-btn *,
            body:not(.rex-theme-light) .btn-primary.quickstart-btn,
            body:not(.rex-theme-light) .btn-primary.quickstart-btn *,
            body:not(.rex-theme-light) .btn-success.setup-domain-btn,
            body:not(.rex-theme-light) .btn-success.setup-domain-btn * {
                color: #ffffff !important;
            }
        }
    </style>
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-md-12 text-right">
            <?php if ($hasDomains): ?>
            <button type="button" class="btn btn-primary btn-lg quickstart-btn" data-toggle="modal" data-target="#setup-wizard-modal">
                <i class="rex-icon fa-magic" style="margin-right: 10px;"></i>
                <strong>Setup Wizard</strong>
                <i class="rex-icon fa-chevron-right" style="margin-left: 10px; font-size: 14px; opacity: 0.8;"></i>
            </button>
            <?php else: ?>
            <button type="button" class="btn btn-success btn-lg setup-domain-btn" data-toggle="modal" data-target="#setup-wizard-modal">
                <i class="rex-icon fa-rocket" style="margin-right: 10px;"></i>
                <strong><?= rex_i18n::msg('consent_manager_setup_first_domain') ?></strong>
                <i class="rex-icon fa-chevron-right" style="margin-left: 10px; font-size: 14px; opacity: 0.8;"></i>
            </button>
            <?php endif ?>
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
</div>
<?php
// Setup Wizard Modal einbinden
$fragment = new rex_fragment();
echo $fragment->parse('ConsentManager/setup_wizard.php');
?>