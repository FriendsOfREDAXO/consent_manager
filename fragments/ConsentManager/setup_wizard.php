<?php
/**
 * Fragment: Setup Wizard mit SSE
 * Reaktiver Setup-Assistent für erste Einrichtung
 */

/** @var rex_fragment $this */

$addon = rex_addon::get('consent_manager');

// Bereits konfigurierte Domains laden
$configuredDomains = [];
$sql = rex_sql::factory();
$sql->setQuery('SELECT uid FROM ' . rex::getTable('consent_manager_domain'));
foreach ($sql as $row) {
    $configuredDomains[] = $row->getValue('uid');
}

// Prüfen ob bereits Services konfiguriert sind
$existingServicesCount = rex_sql::factory();
$existingServicesCount->setQuery('SELECT COUNT(*) as cnt FROM ' . rex::getTable('consent_manager_cookie'));
$hasExistingServices = (int) $existingServicesCount->getValue('cnt') > 0;

// Debug: Services-Check
// echo '<!-- Services Check: ' . ($hasExistingServices ? 'JA' : 'NEIN') . ' (' . $existingServicesCount->getValue('cnt') . ' Services) -->';

// YRewrite Domains laden falls verfügbar (ohne bereits konfigurierte)
$yrewriteDomains = [];
$yrewriteDebugInfo = '';

if (rex_addon::exists('yrewrite') && rex_addon::get('yrewrite')->isAvailable()) {
    $allYRewriteDomains = [];
    foreach (rex_yrewrite::getDomains() as $domain) {
        $cleanDomain = preg_replace('#^https?://#i', '', $domain->getUrl());
        $cleanDomain = rtrim($cleanDomain, '/');
        $cleanDomain = strtolower($cleanDomain);
        
        // Duplikate vermeiden (z.B. wenn eine Domain sowohl als Standard als auch regulär existiert)
        if (!in_array($cleanDomain, $allYRewriteDomains, true)) {
            $allYRewriteDomains[] = $cleanDomain;
        }
        
        // Nur nicht-konfigurierte Domains anbieten
        if (!in_array($cleanDomain, $configuredDomains, true) && !in_array($cleanDomain, $yrewriteDomains, true)) {
            $yrewriteDomains[] = $cleanDomain;
        }
    }
    
    // Debug-Info generieren
    if (count($yrewriteDomains) === 0) {
        if (count($allYRewriteDomains) === 0) {
            $yrewriteDebugInfo = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> <strong>YRewrite ist aktiv, aber keine Domains angelegt.</strong><br>Legen Sie zuerst Domains in YRewrite an.</div>';
        } else {
            $yrewriteDebugInfo = '<div class="alert alert-warning"><i class="fa fa-check-circle"></i> <strong>Alle YRewrite-Domains bereits konfiguriert:</strong> ' . implode(', ', $configuredDomains) . '</div>';
        }
    } else {
        $yrewriteDebugInfo = '<div class="alert alert-success" style="margin-bottom: 15px;"><i class="fa fa-check-circle"></i> <strong>' . count($yrewriteDomains) . ' YRewrite-Domain(s) verfügbar</strong></div>';
    }
} else {
    $yrewriteDebugInfo = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> <strong>YRewrite ist nicht installiert oder nicht aktiviert.</strong><br>Sie können trotzdem eine Domain manuell eingeben.</div>';
}

// Wizard verwendet nur das Default-Theme
// Theme-Auswahl wurde entfernt - Nutzer können Theme später auf der Theme-Seite ändern

?>

<style>
/* Setup Wizard Styling - REDAXO Theme kompatibel */
#setup-wizard-modal .modal-content {
    border-radius: 6px;
    border: none;
}

#setup-wizard-modal .modal-header {
    background: linear-gradient(135deg, #337ab7 0%, #2e6da4 100%);
    color: #fff;
    border-radius: 6px 6px 0 0;
    padding: 20px 25px;
    border-bottom: none;
}

#setup-wizard-modal .modal-header .modal-title {
    font-weight: 600;
    font-size: 20px;
}

#setup-wizard-modal .modal-header .close {
    color: #fff;
    opacity: 0.8;
    text-shadow: none;
    font-size: 28px;
}

#setup-wizard-modal .modal-header .close:hover {
    opacity: 1;
}

#setup-wizard-modal .panel {
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

#setup-wizard-modal .panel-heading {
    border-radius: 4px 4px 0 0;
}

#setup-wizard-modal .form-group label {
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
}

#setup-wizard-modal .form-control {
    border-radius: 4px;
    border: 1px solid #d0d0d0;
    transition: border-color 0.2s, box-shadow 0.2s;
}

#setup-wizard-modal .form-control:focus {
    border-color: #66afe9;
    box-shadow: 0 0 0 3px rgba(102, 175, 233, 0.15);
}

#setup-wizard-modal .alert {
    border-radius: 4px;
    border-left-width: 4px;
}

/* Dark Theme Support */
body.rex-theme-dark #setup-wizard-modal .modal-content,
body.rex-theme-dark #setup-wizard-modal .modal-body {
    background-color: #374151;
    color: #e5e7eb;
}

body.rex-theme-dark #setup-wizard-modal .form-group label {
    color: #e5e7eb;
}

body.rex-theme-dark #setup-wizard-modal .form-control {
    background-color: #1f2937;
    border-color: #4b5563;
    color: #f3f4f6;
}

body.rex-theme-dark #setup-wizard-modal .form-control:focus {
    border-color: #60a5fa;
    box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
}

body.rex-theme-dark #setup-wizard-modal .panel {
    background-color: #1f2937;
    border-color: #4b5563;
}

body.rex-theme-dark #setup-wizard-modal .panel-heading {
    background-color: #374151;
    border-color: #4b5563;
    color: #e5e7eb;
}

body.rex-theme-dark #setup-wizard-modal .panel-body {
    background-color: #1f2937;
    color: #d1d5db;
}

body.rex-theme-dark #setup-wizard-modal .alert-info {
    background-color: rgba(59, 130, 246, 0.15);
    border-color: #3b82f6;
    color: #93c5fd;
}

body.rex-theme-dark #setup-wizard-modal .alert-success {
    background-color: rgba(34, 197, 94, 0.15);
    border-color: #22c55e;
    color: #86efac;
}

body.rex-theme-dark #setup-wizard-modal .alert-warning {
    background-color: rgba(245, 158, 11, 0.15);
    border-color: #f59e0b;
    color: #fcd34d;
}

body.rex-theme-dark #setup-wizard-modal .well {
    background-color: #374151;
    border-color: #4b5563;
}

body.rex-theme-dark #setup-wizard-modal .toggle-switch-label {
    color: #e5e7eb;
}

body.rex-theme-dark #setup-wizard-modal #wizard-log > div {
    background-color: #1f2937 !important;
    border: 1px solid #4b5563;
    color: #d1d5db;
}

body.rex-theme-dark #setup-wizard-modal #wizard-log label {
    color: #e5e7eb;
}

body.rex-theme-dark #setup-wizard-modal .panel-warning {
    background-color: rgba(245, 158, 11, 0.15) !important;
    border-color: #f59e0b !important;
}

body.rex-theme-dark #setup-wizard-modal .panel-warning strong {
    color: #fbbf24 !important;
}

body.rex-theme-dark #setup-wizard-modal .panel-warning .text-muted {
    color: #d1d5db !important;
}

body.rex-theme-dark #setup-wizard-modal .panel-warning .fa-plug {
    color: #fbbf24 !important;
}

body.rex-theme-dark #setup-wizard-modal .panel-warning .fa-info-circle {
    color: #fbbf24 !important;
}

body.rex-theme-dark #setup-wizard-modal .btn {
    color: #fff !important;
}

body.rex-theme-dark #setup-wizard-modal .btn-default {
    background-color: #4a5568;
    border-color: #4a5568;
}

body.rex-theme-dark #setup-wizard-modal .btn-default:hover {
    background-color: #5a6778;
    border-color: #5a6778;
}

/* System Dark Mode Support (prefers-color-scheme) */
@media (prefers-color-scheme: dark) {
    body:not(.rex-theme-light) #setup-wizard-modal .modal-content,
    body:not(.rex-theme-light) #setup-wizard-modal .modal-body {
        background-color: #374151;
        color: #e5e7eb;
    }

    body:not(.rex-theme-light) #setup-wizard-modal .form-group label {
        color: #e5e7eb;
    }

    body:not(.rex-theme-light) #setup-wizard-modal .form-control {
        background-color: #1f2937;
        border-color: #4b5563;
        color: #f3f4f6;
    }

    body:not(.rex-theme-light) #setup-wizard-modal .form-control:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
    }

    body:not(.rex-theme-light) #setup-wizard-modal .panel {
        background-color: #1f2937;
        border-color: #4b5563;
    }

    body:not(.rex-theme-light) #setup-wizard-modal .panel-heading {
        background-color: #374151;
        border-color: #4b5563;
        color: #e5e7eb;
    }

    body:not(.rex-theme-light) #setup-wizard-modal .panel-body {
        background-color: #1f2937;
        color: #d1d5db;
    }

    body:not(.rex-theme-light) #setup-wizard-modal .alert-info {
        background-color: rgba(59, 130, 246, 0.15);
        border-color: #3b82f6;
        color: #93c5fd;
    }

    body:not(.rex-theme-light) #setup-wizard-modal .alert-success {
        background-color: rgba(34, 197, 94, 0.15);
        border-color: #22c55e;
        color: #86efac;
    }

    body:not(.rex-theme-light) #setup-wizard-modal .alert-warning {
        background-color: rgba(245, 158, 11, 0.15);
        border-color: #f59e0b;
        color: #fcd34d;
    }

    body:not(.rex-theme-light) #setup-wizard-modal #wizard-log > div {
        background-color: #1f2937 !important;
        border: 1px solid #4b5563;
        color: #d1d5db;
    }

    body:not(.rex-theme-light) #setup-wizard-modal #wizard-log label {
        color: #e5e7eb;
    }

    body:not(.rex-theme-light) #setup-wizard-modal .panel-warning {
        background-color: rgba(245, 158, 11, 0.15) !important;
        border-color: #f59e0b !important;
    }

    body:not(.rex-theme-light) #setup-wizard-modal .panel-warning strong {
        color: #fbbf24 !important;
    }

    body:not(.rex-theme-light) #setup-wizard-modal .panel-warning .text-muted {
        color: #d1d5db !important;
    }

    body:not(.rex-theme-light) #setup-wizard-modal .panel-warning .fa-plug {
        color: #fbbf24 !important;
    }

    body:not(.rex-theme-light) #setup-wizard-modal .panel-warning .fa-info-circle {
        color: #fbbf24 !important;
    }

    body:not(.rex-theme-light) #setup-wizard-modal .btn {
        color: #fff !important;
    }

    body:not(.rex-theme-light) #setup-wizard-modal .btn-default {
        background-color: #4a5568;
        border-color: #4a5568;
    }

    body:not(.rex-theme-light) #setup-wizard-modal .btn-default:hover {
        background-color: #5a6778;
        border-color: #5a6778;
    }

    body:not(.rex-theme-light) #setup-wizard-modal .well {
        background-color: #2d3748;
        border-color: #4a5568;
    }

    body:not(.rex-theme-light) #setup-wizard-modal .toggle-switch-label {
        color: #e2e8f0;
    }
}

/* Progress Bar Styling */
#wizard-progress-container {
    margin: 20px 0;
}

#wizard-progress-bar {
    transition: width 0.4s ease;
    background: linear-gradient(90deg, #5cb85c 0%, #4cae4c 100%);
}

/* Event Log Styling */
#wizard-log {
    max-height: 300px;
    overflow-y: auto;
    font-family: 'Monaco', 'Menlo', 'Consolas', monospace;
    font-size: 13px;
    line-height: 1.6;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 12px;
}

body.rex-theme-dark #wizard-log {
    background: #1a202c;
    border-color: #4a5568;
    color: #cbd5e0;
}

@media (prefers-color-scheme: dark) {
    body:not(.rex-theme-light) #wizard-log {
        background: #1a202c;
        border-color: #4a5568;
        color: #cbd5e0;
    }
}

#wizard-log .log-entry {
    padding: 4px 0;
    border-bottom: 1px solid #e9ecef;
}

body.rex-theme-dark #wizard-log .log-entry {
    border-color: #4a5568;
}

@media (prefers-color-scheme: dark) {
    body:not(.rex-theme-light) #wizard-log .log-entry {
        border-color: #4a5568;
    }
}

#wizard-log .log-entry:last-child {
    border-bottom: none;
}

#wizard-log .log-entry .log-time {
    color: #6c757d;
    font-size: 12px;
}

body.rex-theme-dark #wizard-log .log-entry .log-time {
    color: #a0aec0;
}

@media (prefers-color-scheme: dark) {
    body:not(.rex-theme-light) #wizard-log .log-entry .log-time {
        color: #a0aec0;
    }
}

/* Toggle Switch - Dark Theme kompatibel */
.toggle-switch {
    position: relative;
    width: 50px;
    height: 24px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.3s;
    border-radius: 24px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
}

.toggle-switch input:checked + .toggle-slider {
    background-color: #5cb85c;
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(26px);
}

body.rex-theme-dark .toggle-slider {
    background-color: #4a5568;
}

body.rex-theme-dark .toggle-switch input:checked + .toggle-slider {
    background-color: #48bb78;
}

@media (prefers-color-scheme: dark) {
    body:not(.rex-theme-light) .toggle-slider {
        background-color: #4a5568;
    }

    body:not(.rex-theme-light) .toggle-switch input:checked + .toggle-slider {
        background-color: #48bb78;
    }
}
</style>

<div class="modal fade" id="setup-wizard-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="rex-icon fa-magic"></i> <?= rex_i18n::msg('consent_manager_wizard_title') ?>
                </h4>
            </div>
            <div class="modal-body">
                
                <!-- Willkommens-Screen -->
                <div id="wizard-welcome">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?= rex_i18n::msg('consent_manager_wizard_welcome') ?></h3>
                        </div>
                        <div class="panel-body">
                            <p><?= rex_i18n::msg('consent_manager_wizard_intro') ?></p>
                        </div>
                    </div>

                    <form id="wizard-form">
                        <!-- Domain Eingabe -->
                        <div class="form-group">
                            <label class="control-label">
                                <i class="rex-icon fa-globe"></i> <?= rex_i18n::msg('consent_manager_wizard_domain') ?>
                            </label>
                            
                            <?= $yrewriteDebugInfo ?>
                            
                            <?php if (count($yrewriteDomains) > 0): ?>
                            <!-- YRewrite Domain Auswahl -->
                            <select id="wizard-domain-select" class="form-control selectpicker" data-live-search="true" data-size="8">
                                <option value=""><?= rex_i18n::msg('consent_manager_wizard_domain_select') ?></option>
                                <?php foreach ($yrewriteDomains as $domain): ?>
                                <option value="<?= rex_escape($domain) ?>"><?= rex_escape($domain) ?></option>
                                <?php endforeach ?>
                            </select>
                            
                            <div style="text-align: center; margin: 15px 0; color: #999;">
                                <?= rex_i18n::msg('consent_manager_wizard_or') ?>
                            </div>
                            <?php endif ?>
                            
                            <!-- Manuelle Eingabe -->
                            <input type="text" 
                                   id="wizard-domain-input" 
                                   class="form-control" 
                                   placeholder="example.com"
                                   value="">
                            <p class="help-block">
                                <i class="fa fa-info-circle"></i> <?= rex_i18n::msg('consent_manager_wizard_domain_hint') ?>
                            </p>
                        </div>

                        <!-- Setup-Typ -->
                        <div class="form-group">
                            <label class="control-label">
                                <i class="rex-icon fa-download"></i> <?= rex_i18n::msg('consent_manager_wizard_setup_type') ?>
                            </label>
                            <?php if ($hasExistingServices): ?>
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> 
                                <strong><?= rex_i18n::msg('consent_manager_wizard_services_exist_info') ?></strong><br>
                                <?= rex_i18n::msg('consent_manager_wizard_services_exist_info_hint') ?>
                            </div>
                            <?php endif ?>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="setup_type" value="standard" checked>
                                    <strong><?= rex_i18n::msg('consent_manager_setup_standard_title') ?></strong><br>
                                    <small class="text-muted"><?= rex_i18n::msg('consent_manager_setup_standard_desc') ?></small>
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="setup_type" value="minimal">
                                    <strong><?= rex_i18n::msg('consent_manager_setup_minimal_title') ?></strong><br>
                                    <small class="text-muted"><?= rex_i18n::msg('consent_manager_setup_minimal_desc') ?></small>
                                </label>
                            </div>
                        </div>

                        <!-- Auto-Inject (VOR Theme) -->
                        <div class="panel panel-warning" style="border-left: 4px solid #f0ad4e; background: #fffbf0;">
                            <div class="panel-body">
                                <div style="display: flex; align-items: start;">
                                    <div style="flex-shrink: 0; margin-right: 15px; font-size: 32px; color: #f0ad4e; line-height: 1;">
                                        <i class="fa fa-plug"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                            <strong style="font-size: 16px; color: #333;"><?= rex_i18n::msg('consent_manager_wizard_auto_inject') ?></strong>
                                            <label class="wizard-toggle-switch" style="margin: 0;">
                                                <input type="checkbox" id="wizard-auto-inject" name="auto_inject" value="1" checked>
                                                <span class="wizard-toggle-slider"></span>
                                            </label>
                                        </div>
                                        <small class="text-muted" style="display: block; line-height: 1.6;">
                                            <i class="fa fa-info-circle" style="color: #f0ad4e;"></i> 
                                            <?= rex_i18n::msg('consent_manager_wizard_auto_inject_hint') ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Theme Auswahl -->
                        <!-- Impressum & Datenschutz (optional) -->
                        <div class="form-group">
                            <label class="control-label">
                                <i class="rex-icon fa-file-text-o"></i> <?= rex_i18n::msg('consent_manager_wizard_legal_pages') ?>
                            </label>
                            <div class="row">
                                <div class="col-sm-6">
                                    <label class="control-label" style="font-weight: normal;">
                                        <small><?= rex_i18n::msg('consent_manager_wizard_privacy_policy') ?></small>
                                    </label>
                                    <?= rex_var_link::getWidget('wizard_privacy', 'privacy_policy', '', []); ?>
                                </div>
                                <div class="col-sm-6">
                                    <label class="control-label" style="font-weight: normal;">
                                        <small><?= rex_i18n::msg('consent_manager_wizard_legal_notice') ?></small>
                                    </label>
                                    <?= rex_var_link::getWidget('wizard_imprint', 'legal_notice', '', []); ?>
                                </div>
                            </div>
                            <p class="help-block">
                                <i class="fa fa-info-circle"></i> <?= rex_i18n::msg('consent_manager_wizard_legal_hint') ?>
                            </p>
                        </div>
                    </form>
                </div>

                <!-- Progress-Screen (initial hidden) -->
                <div id="wizard-progress" style="display:none;">
                    <div class="form-group">
                        <div class="progress" style="height: 30px;">
                            <div id="wizard-progress-bar" 
                                 class="progress-bar progress-bar-striped active" 
                                 role="progressbar" 
                                 style="width: 0%; line-height: 30px;">
                                <span id="wizard-progress-text">0%</span>
                            </div>
                        </div>
                    </div>

                    <div id="wizard-status" class="alert alert-info">
                        <i class="fa fa-spinner fa-spin"></i> <span id="wizard-status-text"><?= rex_i18n::msg('consent_manager_wizard_starting') ?></span>
                    </div>

                    <div id="wizard-log" style="margin-top: 20px;">
                        <label><?= rex_i18n::msg('consent_manager_wizard_log') ?>:</label>
                        <div style="background: #f5f5f5; padding: 15px; border-radius: 4px; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px; line-height: 1.8;">
                            <div id="wizard-log-content"></div>
                        </div>
                    </div>
                </div>

                <!-- Success-Screen (initial hidden) -->
                <div id="wizard-success" style="display:none;">
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-check-circle"></i> <?= rex_i18n::msg('consent_manager_wizard_complete_title') ?></h3>
                        </div>
                        <div class="panel-body">
                            <p id="wizard-success-message" style="font-size: 16px; margin-bottom: 0;"></p>
                        </div>
                    </div>
                        
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-info-circle"></i> <?= rex_i18n::msg('consent_manager_wizard_next_steps') ?></h3>
                        </div>
                        <div class="panel-body">
                            <p style="margin-bottom: 20px;"><?= rex_i18n::msg('consent_manager_wizard_next_steps_intro') ?></p>
                            
                            <div class="list-group" style="margin-bottom: 0;">
                                <a href="<?= rex_url::backendPage('consent_manager/cookiegroup') ?>" class="list-group-item wizard-nav-link" data-page="consent_manager/cookiegroup">
                                    <h4 class="list-group-item-heading">
                                        <i class="rex-icon fa-folder-open" style="color: #f39c12;"></i> 
                                        <?= rex_i18n::msg('consent_manager_wizard_link_groups') ?>
                                    </h4>
                                    <p class="list-group-item-text"><?= rex_i18n::msg('consent_manager_wizard_link_groups_desc') ?></p>
                                </a>
                                <a href="<?= rex_url::backendPage('consent_manager/domain') ?>" class="list-group-item wizard-nav-link" data-page="consent_manager/domain">
                                    <h4 class="list-group-item-heading">
                                        <i class="rex-icon fa-globe" style="color: #3498db;"></i> 
                                        <?= rex_i18n::msg('consent_manager_wizard_link_domain') ?>
                                    </h4>
                                    <p class="list-group-item-text"><?= rex_i18n::msg('consent_manager_wizard_link_domain_desc') ?></p>
                                </a>
                                <a href="<?= rex_url::backendPage('consent_manager/cookie') ?>" class="list-group-item wizard-nav-link" data-page="consent_manager/cookie">
                                    <h4 class="list-group-item-heading">
                                        <i class="rex-icon fa-cog" style="color: #e74c3c;"></i> 
                                        <?= rex_i18n::msg('consent_manager_wizard_link_services') ?>
                                    </h4>
                                    <p class="list-group-item-text"><?= rex_i18n::msg('consent_manager_wizard_link_services_desc') ?></p>
                                </a>
                                <a href="<?= rex_url::backendPage('consent_manager/theme') ?>" class="list-group-item wizard-nav-link" data-page="consent_manager/theme">
                                    <h4 class="list-group-item-heading">
                                        <i class="rex-icon fa-grip" style="color: #9b59b6;"></i> 
                                        <?= rex_i18n::msg('consent_manager_wizard_link_theme') ?>
                                    </h4>
                                    <p class="list-group-item-text"><?= rex_i18n::msg('consent_manager_wizard_link_theme_desc') ?></p>
                                </a>
                                <a href="<?= rex_url::backendPage('consent_manager/text') ?>" class="list-group-item wizard-nav-link" data-page="consent_manager/text">
                                    <h4 class="list-group-item-heading">
                                        <i class="rex-icon fa-file-text-o" style="color: #1abc9c;"></i> 
                                        <?= rex_i18n::msg('consent_manager_wizard_link_texts') ?>
                                    </h4>
                                    <p class="list-group-item-text"><?= rex_i18n::msg('consent_manager_wizard_link_texts_desc') ?></p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="rex-icon fa-times"></i> <?= rex_i18n::msg('consent_manager_wizard_close') ?>
                </button>
                <button type="button" id="wizard-btn-start" class="btn btn-primary">
                    <i class="rex-icon fa-magic"></i> <?= rex_i18n::msg('consent_manager_wizard_start') ?>
                </button>
                <button type="button" id="wizard-btn-reset" class="btn btn-warning" style="display:none;">
                    <i class="rex-icon fa-refresh"></i> Wizard neu starten
                </button>
                <a href="<?= rex_url::backendPage('consent_manager/domain') ?>" id="wizard-btn-config" class="btn btn-success" style="display:none;">
                    <i class="rex-icon fa-cog"></i> <?= rex_i18n::msg('consent_manager_wizard_to_config') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<style nonce="<?= rex_response::getNonce() ?>">
    /* Toggle Switch für Auto-Inject */
    .wizard-toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }
    
    .wizard-toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .wizard-toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .3s;
        border-radius: 24px;
    }
    
    .wizard-toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    
    .wizard-toggle-switch input:checked + .wizard-toggle-slider {
        background-color: #5cb85c;
    }
    
    .wizard-toggle-switch input:checked + .wizard-toggle-slider:before {
        transform: translateX(26px);
    }
    
    /* Microanimationen für den Wizard */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    @keyframes progressPulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.8; }
    }
    
    @keyframes successBounce {
        0%, 100% { transform: scale(1); }
        25% { transform: scale(1.1); }
        50% { transform: scale(0.95); }
        75% { transform: scale(1.05); }
    }
    
    /* Modal Animations */
    #setup-wizard-modal .modal-content {
        animation: fadeIn 0.3s ease-out;
    }
    
    #wizard-welcome, #wizard-progress, #wizard-success {
        animation: fadeIn 0.5s ease-out;
    }
    
    /* Log Entries Animation */
    #wizard-log-content > div {
        animation: slideIn 0.3s ease-out;
    }
    
    /* Progress Bar Animation */
    #wizard-progress-bar {
        transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        animation: progressPulse 2s ease-in-out infinite;
    }
    
    /* Button Animations */
    #wizard-btn-start:hover {
        animation: pulse 0.5s ease-in-out;
        box-shadow: 0 6px 16px rgba(0,0,0,0.3);
    }
    
    #wizard-btn-config.btn-success {
        animation: successBounce 0.6s ease-out;
    }
    
    /* Panel Animations */
    .panel {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .panel:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .panel-success {
        animation: fadeIn 0.6s ease-out;
    }
    
    /* Form Controls */
    .form-control:focus, .selectpicker:focus {
        transition: border-color 0.3s, box-shadow 0.3s;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15);
    }
    
    /* Status Icons Animation */
    .fa-check, .fa-times {
        animation: successBounce 0.4s ease-out;
    }
    
    /* Loading Spinner */
    .fa-spinner {
        animation: spin 1s linear infinite;
    }
</style>

<script nonce="<?= rex_response::getNonce() ?>">
jQuery(function($) {
    var eventSource = null;
    
    // Bootstrap Selectpicker initialisieren
    if ($.fn.selectpicker) {
        $('.selectpicker').selectpicker();
    }
    
    // YRewrite Select → Input synchronisieren
    <?php if (count($yrewriteDomains) > 0): ?>
    $('#wizard-domain-select').on('change', function() {
        $('#wizard-domain-input').val($(this).val());
    });
    <?php endif ?>
    
    // Bereits konfigurierte Domains
    var configuredDomains = <?= json_encode($configuredDomains) ?>;
    
    // Start Button
    $('#wizard-btn-start').on('click', function() {
        var domain = $('#wizard-domain-input').val().trim().toLowerCase();
        
        if (domain === '') {
            alert('<?= rex_i18n::msg('consent_manager_wizard_error_domain_required') ?>');
            return;
        }
        
        // Prüfen ob Domain bereits konfiguriert ist
        if (configuredDomains.indexOf(domain) !== -1) {
            if (!confirm('<?= rex_i18n::msg('consent_manager_wizard_domain_exists') ?>')) {
                return;
            }
        }
        
        // Schließen-Button verstecken während Setup läuft
        $('#setup-wizard-modal .modal-header .close, #setup-wizard-modal .modal-footer .btn-default:first').hide();
        
        startWizard(domain);
    });
    
    function startWizard(domain) {
        // UI umschalten
        $('#wizard-welcome').hide();
        $('#wizard-progress').show();
        $('#wizard-btn-start').hide();
        
        var setupType = $('input[name="setup_type"]:checked').val();
        var autoInject = $('#wizard-auto-inject').is(':checked');
        var privacyPolicy = $('#REX_LINK_wizard_privacy').val() || '0';
        var legalNotice = $('#REX_LINK_wizard_imprint').val() || '0';
        
        // SSE Connection aufbauen (Theme wird nicht mehr übergeben - nutzt Default)
        var url = '<?= rex_url::backendController() ?>?rex-api-call=consent_manager_setup_wizard&' +
                  'domain=' + encodeURIComponent(domain) +
                  '&setup_type=' + encodeURIComponent(setupType) +
                  '&auto_inject=' + (autoInject ? '1' : '0') +
                  '&privacy_policy=' + encodeURIComponent(privacyPolicy) +
                  '&legal_notice=' + encodeURIComponent(legalNotice);
        
        eventSource = new EventSource(url);
        
        // Init Event
        eventSource.addEventListener('init', function(e) {
            var data = JSON.parse(e.data);
            console.log('INIT:', JSON.stringify(data, null, 2));
            logEvent('✓ Verbindung hergestellt', 'success');
            logEvent('→ Domain: ' + data.domain, 'info');
        });
        
        // Debug Event
        eventSource.addEventListener('debug', function(e) {
            var data = JSON.parse(e.data);
            console.log('DEBUG:', JSON.stringify(data, null, 2));
        });
        
        // Progress Event
        eventSource.addEventListener('progress', function(e) {
            var data = JSON.parse(e.data);
            console.log('PROGRESS:', data.percent + '% - ' + data.message);
            updateProgress(data.percent, data.message);
            logEvent('⏳ ' + data.message, 'info');
        });
        
        // Domain Created Event
        eventSource.addEventListener('domain_created', function(e) {
            var data = JSON.parse(e.data);
            console.log('DOMAIN_CREATED:', JSON.stringify(data, null, 2));
            logEvent('✓ Domain angelegt: ' + data.domain, 'success');
        });
        
        // Import Complete Event
        eventSource.addEventListener('import_complete', function(e) {
            var data = JSON.parse(e.data);
            logEvent('✓ ' + (data.type === 'standard' ? 'Standard' : 'Minimal') + '-Setup importiert', 'success');
        });
        
        // Theme Assigned Event
        eventSource.addEventListener('theme_assigned', function(e) {
            var data = JSON.parse(e.data);
            logEvent('✓ Theme zugewiesen: ' + data.theme, 'success');
        });
        
        // Cache Cleared Event
        eventSource.addEventListener('cache_cleared', function(e) {
            logEvent('✓ Cache geleert', 'success');
        });
        
        // Validation Event
        eventSource.addEventListener('validation', function(e) {
            var data = JSON.parse(e.data);
            logEvent('✓ Validierung: ' + data.cookies_count + ' Services, ' + data.groups_count + ' Gruppen', 'success');
        });
        
        // Complete Event
        eventSource.addEventListener('complete', function(e) {
            var data = JSON.parse(e.data);
            eventSource.close();
            showSuccess(data);
        });
        
        // Error Event
        eventSource.addEventListener('error', function(e) {
            if (e.data) {
                var data = JSON.parse(e.data);
                logEvent('✗ Fehler: ' + data.message, 'error');
                $('#wizard-status').removeClass('alert-info').addClass('alert-danger');
                $('#wizard-status-text').html('<i class="fa fa-exclamation-triangle"></i> ' + data.message);
            }
            if (eventSource) {
                eventSource.close();
            }
            // Reset-Button anzeigen bei Fehler
            $('#setup-wizard-modal .modal-header .close, #setup-wizard-modal .modal-footer .btn-default:first').show();
            $('#wizard-btn-reset').show();
        });
    }
    
    function updateProgress(percent, message) {
        $('#wizard-progress-bar').css('width', percent + '%');
        $('#wizard-progress-text').text(percent + '%');
        $('#wizard-status-text').text(message);
    }
    
    function logEvent(message, type) {
        var icon = type === 'success' ? '✓' : type === 'error' ? '✗' : '→';
        var color = type === 'success' ? '#5cb85c' : type === 'error' ? '#d9534f' : '#666';
        var time = new Date().toLocaleTimeString('de-DE');
        
        $('#wizard-log-content').append(
            '<div style="color: ' + color + '; margin-bottom: 5px;">' +
            '<span style="color: #999;">[' + time + ']</span> ' +
            message +
            '</div>'
        );
        
        // Auto-scroll
        var logContainer = $('#wizard-log-content').parent();
        logContainer.scrollTop(logContainer[0].scrollHeight);
    }
    
    function showSuccess(data) {
        $('#wizard-progress').hide();
        $('#wizard-success').show();
        $('#wizard-success-message').text(data.message);
        // Schließen-Button wieder anzeigen
        $('#setup-wizard-modal .modal-header .close, #setup-wizard-modal .modal-footer .btn-default:first').show();
        
        $('#wizard-btn-reset').show();
        $('#wizard-btn-config').show();
        
        $('#wizard-status').removeClass('alert-info').addClass('alert-success');
    }
    
    // Reset Button Handler - Wizard neu starten
    $('#wizard-btn-reset').on('click', function() {
        resetWizard();
    });
    
    // Modal Reset beim Öffnen
    $('#setup-wizard-modal').on('show.bs.modal', function() {
        resetWizard();
    });
    
    function resetWizard() {
        // Alle Screens zurücksetzen
        $('#wizard-welcome').show();
        $('#wizard-progress').hide();
        $('#wizard-success').hide();
        
        // Buttons zurücksetzen
        $('#wizard-btn-start').show();
        $('#wizard-btn-reset').hide();
        $('#wizard-btn-config').hide();
        
        // Progress zurücksetzen
        $('#wizard-progress-bar').css('width', '0%');
        $('#wizard-progress-text').text('0%');
        $('#wizard-status').removeClass('alert-danger alert-success').addClass('alert-info');
        $('#wizard-status-text').html('<i class="fa fa-spinner fa-spin"></i> <?= rex_i18n::msg("consent_manager_wizard_starting") ?>');
        
        // Log leeren
        $('#wizard-log-content').empty();
        
        // Close Button sichtbar machen
        $('#setup-wizard-modal .modal-header .close, #setup-wizard-modal .modal-footer .btn-default:first').show();
        
        // EventSource schließen falls noch aktiv
        if (eventSource) {
            eventSource.close();
            eventSource = null;
        }
    }
    
    // Navigation Links Handler
    $('#wizard-success').on('click', '.wizard-nav-link', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var url = $(this).attr('href');
        console.log('Navigation to:', url);
        
        // Modal schließen
        $('#setup-wizard-modal').modal('hide');
        
        // Kurz warten bis Modal geschlossen ist, dann navigieren
        setTimeout(function() {
            console.log('Navigating to:', url);
            // Direkte Navigation ohne PJAX (sicherer)
            window.location.href = url;
        }, 400);
        
        return false;
    });
});
</script>
