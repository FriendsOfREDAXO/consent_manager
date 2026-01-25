<?php
/**
 * Fragment: Setup Wizard mit AJAX Polling
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

/* Dark Mode Anpassungen für Panels */
@media (prefers-color-scheme: dark) {
    body:not(.rex-theme-light) .panel {
        background: rgba(255, 255, 255, 0.05) !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
    
    body:not(.rex-theme-light) .panel h4,
    body:not(.rex-theme-light) .panel strong {
        color: #e2e8f0 !important;
    }
    
    body:not(.rex-theme-light) .panel .form-control,
    body:not(.rex-theme-light) .panel input[type="text"],
    body:not(.rex-theme-light) .panel input[type="checkbox"] {
        background-color: #2d3748;
        border-color: #4a5568;
        color: #e2e8f0;
    }
    
    body:not(.rex-theme-light) .panel label {
        color: #cbd5e0 !important;
    }
    
    body:not(.rex-theme-light) .panel .text-muted,
    body:not(.rex-theme-light) .panel small {
        color: #a0aec0 !important;
    }
    
    body:not(.rex-theme-light) .panel .help-block {
        color: #a0aec0;
    }
    
    /* Checkbox-Container im Dark Mode */
    body:not(.rex-theme-light) div[style*="background: #fff"],
    body:not(.rex-theme-light) div[style*="background-color: #fff"],
    body:not(.rex-theme-light) div[style*="background:#fff"] {
        background: #2d3748 !important;
        border-color: #4a5568 !important;
    }
    
    /* Hover-Effekt für Checkbox-Labels im Dark Mode */
    body:not(.rex-theme-light) label[onmouseover] {
        color: #cbd5e0 !important;
    }
    
    body:not(.rex-theme-light) label[onmouseover]:hover {
        background: #374151 !important;
    }
    
    /* Details-Element */
    body:not(.rex-theme-light) details summary {
        color: #63b3ed;
    }
    
    body:not(.rex-theme-light) details > div {
        background: #2d3748 !important;
        color: #cbd5e0;
    }
    
    body:not(.rex-theme-light) details > div code {
        background: #1a202c;
        color: #f7fafc;
        padding: 2px 4px;
        border-radius: 3px;
    }
}

body.rex-theme-dark .panel {
    background: rgba(255, 255, 255, 0.05) !important;
    border-color: rgba(255, 255, 255, 0.1) !important;
}

body.rex-theme-dark .panel h4,
body.rex-theme-dark .panel strong {
    color: #e2e8f0 !important;
}

body.rex-theme-dark .panel .form-control,
body.rex-theme-dark .panel input[type="text"],
body.rex-theme-dark .panel input[type="checkbox"] {
    background-color: #2d3748;
    border-color: #4a5568;
    color: #e2e8f0;
}

body.rex-theme-dark .panel label {
    color: #cbd5e0 !important;
}

body.rex-theme-dark .panel .text-muted,
body.rex-theme-dark .panel small {
    color: #a0aec0 !important;
}

body.rex-theme-dark .panel .help-block {
    color: #a0aec0;
}

body.rex-theme-dark div[style*="background: #fff"],
body.rex-theme-dark div[style*="background-color: #fff"],
body.rex-theme-dark div[style*="background:#fff"] {
    background: #2d3748 !important;
    border-color: #4a5568 !important;
}

body.rex-theme-dark label[onmouseover] {
    color: #cbd5e0 !important;
}

body.rex-theme-dark label[onmouseover]:hover {
    background: #374151 !important;
}

body.rex-theme-dark details summary {
    color: #63b3ed;
}

body.rex-theme-dark details > div {
    background: #2d3748 !important;
    color: #cbd5e0;
}

body.rex-theme-dark details > div code {
    background: #1a202c;
    color: #f7fafc;
    padding: 2px 4px;
    border-radius: 3px;
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
                        <!-- Domain Panel -->
                        <div class="panel panel-info" style="border-left: 4px solid #5bc0de; background: rgba(91, 192, 222, 0.15); margin: 20px 0; padding: 15px;">
                            <div style="display: flex; align-items: start;">
                                <div style="flex-shrink: 0; margin-right: 15px; font-size: 28px; color: #5bc0de; line-height: 1;">
                                    <i class="fa fa-globe"></i>
                                </div>
                                <div style="flex: 1;">
                                    <h4 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 600;"><?= rex_i18n::msg('consent_manager_wizard_domain') ?></h4>
                                    
                                    <?= $yrewriteDebugInfo ?>
                                    
                                    <?php if (count($yrewriteDomains) > 0): ?>
                                    <!-- YRewrite Domain Auswahl -->
                                    <div class="form-group" style="margin-bottom: 15px;">
                                        <label class="control-label"><?= rex_i18n::msg('consent_manager_wizard_domain_select') ?></label>
                                        <select id="wizard-domain-select" class="form-control selectpicker" data-live-search="true" data-size="8">
                                            <option value=""><?= rex_i18n::msg('consent_manager_wizard_domain_select') ?></option>
                                            <?php foreach ($yrewriteDomains as $domain): ?>
                                            <option value="<?= rex_escape($domain) ?>"><?= rex_escape($domain) ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                    
                                    <div style="text-align: center; margin: 15px 0; color: #999;">
                                        <?= rex_i18n::msg('consent_manager_wizard_or') ?>
                                    </div>
                                    <?php endif ?>
                                    
                                    <!-- Manuelle Eingabe -->
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label class="control-label">Domain manuell eingeben</label>
                                        <input type="text" 
                                               id="wizard-domain-input" 
                                               class="form-control" 
                                               placeholder="example.com"
                                               value="">
                                        <p class="help-block">
                                            <i class="fa fa-info-circle"></i> <?= rex_i18n::msg('consent_manager_wizard_domain_hint') ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rechtliche Seiten Panel -->
                        <div class="panel panel-info" style="border-left: 4px solid #5bc0de; background: rgba(91, 192, 222, 0.15); margin: 20px 0; padding: 15px;">
                            <div style="display: flex; align-items: start;">
                                <div style="flex-shrink: 0; margin-right: 15px; font-size: 28px; color: #5bc0de; line-height: 1;">
                                    <i class="fa fa-paragraph"></i>
                                </div>
                                <div style="flex: 1;">
                                    <h4 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 600;"><?= rex_i18n::msg('consent_manager_wizard_legal_pages') ?></h4>
                                    
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="control-label" style="font-weight: normal;">
                                                    <?= rex_i18n::msg('consent_manager_wizard_privacy_policy') ?>
                                                </label>
                                                <?= rex_var_link::getWidget('wizard_privacy', 'privacy_policy', '', []); ?>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="control-label" style="font-weight: normal;">
                                                    <?= rex_i18n::msg('consent_manager_wizard_legal_notice') ?>
                                                </label>
                                                <?= rex_var_link::getWidget('wizard_imprint', 'legal_notice', '', []); ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <p class="help-block" style="margin-bottom: 0;">
                                        <i class="fa fa-info-circle"></i> <?= rex_i18n::msg('consent_manager_wizard_legal_hint') ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Setup-Typ Panel -->
                        <div class="panel panel-success" style="border-left: 4px solid #5cb85c; background: rgba(92, 184, 92, 0.15); margin: 20px 0; padding: 15px;">
                            <div style="display: flex; align-items: start;">
                                <div style="flex-shrink: 0; margin-right: 15px; font-size: 28px; color: #5cb85c; line-height: 1;">
                                    <i class="fa fa-download"></i>
                                </div>
                                <div style="flex: 1;">
                                    <h4 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 600;"><?= rex_i18n::msg('consent_manager_wizard_setup_type') ?></h4>
                                    
                                    <?php if ($hasExistingServices): ?>
                                    <div class="alert alert-info" style="margin-bottom: 15px;">
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
                                    <div class="radio" style="margin-bottom: 0;">
                                        <label>
                                            <input type="radio" name="setup_type" value="minimal">
                                            <strong><?= rex_i18n::msg('consent_manager_setup_minimal_title') ?></strong><br>
                                            <small class="text-muted"><?= rex_i18n::msg('consent_manager_setup_minimal_desc') ?></small>
                                        </label>
                                    </div>
                                </div>
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
                                            <strong style="font-size: 16px;"><?= rex_i18n::msg('consent_manager_wizard_auto_inject') ?></strong>
                                            <label class="wizard-toggle-switch" style="margin: 0;">
                                                <input type="checkbox" id="wizard-auto-inject" name="auto_inject" value="1" checked>
                                                <span class="wizard-toggle-slider"></span>
                                            </label>
                                        </div>
                                        <small class="text-muted" style="display: block; line-height: 1.6; margin-bottom: 15px;">
                                            <i class="fa fa-info-circle" style="color: #f0ad4e;"></i> 
                                            <?= rex_i18n::msg('consent_manager_wizard_auto_inject_hint') ?>
                                        </small>
                                        
                                        <!-- Template-Whitelist (Optional) -->
                                        <div id="wizard-template-selection" style="margin-top: 15px; display: none;">
                                            <label style="font-weight: normal; margin-bottom: 8px;">
                                                <small><strong>Optional:</strong> Nur in bestimmten Templates einbinden</small>
                                            </label>
                                            
                                            <?php
                                            // Alle aktiven Templates laden
                                            $activeTemplates = [];
                                            if (rex_addon::get('structure')->getPlugin('content')->isAvailable()) {
                                                $tplSql = rex_sql::factory();
                                                $tplSql->setQuery('SELECT id, name FROM ' . rex::getTable('template') . ' WHERE active = 1 ORDER BY name');
                                                foreach ($tplSql as $tplRow) {
                                                    $activeTemplates[(int) $tplRow->getValue('id')] = $tplRow->getValue('name');
                                                }
                                            }
                                            ?>
                                            
                                            <?php if (count($activeTemplates) > 0): ?>
                                            <!-- Scrollbare Checkbox-Liste -->
                                            <div style="border: 1px solid #ddd; border-radius: 4px; padding: 10px; max-height: 200px; overflow-y: auto; background: #fff;">
                                                <div style="margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px solid #eee;">
                                                    <label style="margin: 0; font-weight: normal;">
                                                        <input type="checkbox" id="wizard-templates-select-all" style="margin-right: 5px;">
                                                        <strong>Alle auswählen / Abwählen</strong>
                                                    </label>
                                                </div>
                                                <?php foreach ($activeTemplates as $tplId => $tplName): ?>
                                                <div style="margin: 4px 0;">
                                                    <label style="margin: 0; font-weight: normal; display: block; padding: 4px; border-radius: 3px; cursor: pointer;" 
                                                           onmouseover="this.style.background='#f5f5f5'" 
                                                           onmouseout="this.style.background='transparent'">
                                                        <input type="checkbox" 
                                                               class="wizard-template-checkbox" 
                                                               name="wizard_templates[]" 
                                                               value="<?= $tplId ?>" 
                                                               style="margin-right: 8px;">
                                                        <?= rex_escape($tplName) ?> <small class="text-muted">[ID: <?= $tplId ?>]</small>
                                                    </label>
                                                </div>
                                                <?php endforeach ?>
                                            </div>
                                            <?php else: ?>
                                            <p class="text-muted" style="margin: 0;">
                                                <i class="fa fa-info-circle"></i> Keine aktiven Templates gefunden.
                                            </p>
                                            <?php endif ?>
                                            
                                            <small class="text-muted" style="display: block; margin-top: 8px; line-height: 1.5;">
                                                <i class="fa fa-lightbulb-o"></i> 
                                                <strong>Nichts auswählen = Consent Manager wird in allen Templates eingebunden</strong> (empfohlen für die meisten Websites).
                                            </small>
                                            <details style="margin-top: 10px;">
                                                <summary style="cursor: pointer; color: #337ab7; font-size: 12px;">
                                                    <i class="fa fa-question-circle"></i> Beispiele & Best Practices
                                                </summary>
                                                <div style="margin-top: 10px; padding: 10px; background: #fff; border-radius: 4px; font-size: 12px; line-height: 1.7;">
                                                    <strong>Gründe für Template-Einschränkung:</strong>
                                                    <ul style="margin: 8px 0 0 0; padding-left: 20px;">
                                                        <li><strong>API-Endpoints:</strong> JSON/XML-Ausgaben ohne HTML</li>
                                                        <li><strong>AJAX-Templates:</strong> Laden nur Content-Fragmente nach</li>
                                                        <li><strong>Print-Versionen:</strong> Druckansichten (auch via Parameter wie <code>?print=1</code>)</li>
                                                        <li><strong>RSS/Atom-Feeds:</strong> XML-basierte Feeds</li>
                                                        <li><strong>iFrame-Inhalte:</strong> Eingebettete Seiten ohne UI</li>
                                                        <li><strong>PDF-Generierung:</strong> Seiten für PDF-Export</li>
                                                        <li><strong>Error-Pages:</strong> 404/500 Fehlerseiten (optional)</li>
                                                    </ul>
                                                    <strong style="margin-top: 10px; display: block;">⚠️ Wichtig:</strong>
                                                    <p style="margin: 5px 0;">Selbst bei ausgewählten Templates kann es Probleme geben, wenn:</p>
                                                    <ul style="margin: 0; padding-left: 20px;">
                                                        <li>URL-Parameter Print-Modus aktivieren (<code>?print=1</code>)</li>
                                                        <li>AJAX-Popups über Parameter gesteuert werden (<code>?popup=1</code>)</li>
                                                        <li>Dynamische Varianten geladen werden (<code>?view=iframe</code>)</li>
                                                    </ul>
                                                    <p style="margin: 10px 0 5px 0;">
                                                        <strong>→ Bei komplexen Parameter-Checks empfehlen wir die 
                                                        <a href="index.php?page=consent_manager/help#manuelle-einrichtung" target="_blank" style="color: #337ab7;">
                                                            <i class="fa fa-external-link"></i> manuelle Einrichtung
                                                        </a></strong>
                                                    </p>
                                                    <p style="margin: 5px 0; font-size: 11px; color: #666;">
                                                        <i class="fa fa-info-circle"></i> 
                                                        <a href="index.php?page=consent_manager/help#template-positivliste" target="_blank" style="color: #666;">
                                                            Weitere Infos zu Template-Positivliste
                                                        </a>
                                                    </p>
                                                </div>
                                            </details>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Progress-Screen (initial hidden) -->
                <div id="wizard-progress" style="display:none;">
                    <style>
                        .wizard-loader-container {
                            text-align: center;
                            padding: 60px 20px;
                        }
                        .wizard-spinner {
                            width: 80px;
                            height: 80px;
                            margin: 0 auto 30px;
                            position: relative;
                        }
                        .wizard-spinner div {
                            position: absolute;
                            width: 6px;
                            height: 6px;
                            background: #3498db;
                            border-radius: 50%;
                            animation: wizard-bounce 1.2s infinite ease-in-out both;
                        }
                        .wizard-spinner div:nth-child(1) {
                            left: 8px;
                            top: 8px;
                            animation-delay: -0.36s;
                        }
                        .wizard-spinner div:nth-child(2) {
                            left: 8px;
                            top: 37px;
                            animation-delay: -0.24s;
                        }
                        .wizard-spinner div:nth-child(3) {
                            left: 8px;
                            top: 66px;
                            animation-delay: -0.12s;
                        }
                        .wizard-spinner div:nth-child(4) {
                            left: 37px;
                            top: 8px;
                            animation-delay: -0.36s;
                        }
                        .wizard-spinner div:nth-child(5) {
                            left: 37px;
                            top: 37px;
                            animation-delay: -0.24s;
                        }
                        .wizard-spinner div:nth-child(6) {
                            left: 37px;
                            top: 66px;
                            animation-delay: -0.12s;
                        }
                        .wizard-spinner div:nth-child(7) {
                            left: 66px;
                            top: 8px;
                            animation-delay: 0s;
                        }
                        .wizard-spinner div:nth-child(8) {
                            left: 66px;
                            top: 37px;
                            animation-delay: 0.12s;
                        }
                        .wizard-spinner div:nth-child(9) {
                            left: 66px;
                            top: 66px;
                            animation-delay: 0.24s;
                        }
                        @keyframes wizard-bounce {
                            0%, 40%, 100% {
                                transform: scale(1);
                                opacity: 1;
                            }
                            20% {
                                transform: scale(1.5);
                                opacity: 0.5;
                            }
                        }
                        .wizard-waves {
                            position: relative;
                            width: 100%;
                            height: 80px;
                            margin-top: 30px;
                        }
                        .wizard-wave {
                            position: absolute;
                            width: 100%;
                            height: 100%;
                            opacity: 0.4;
                        }
                        .wizard-wave:before {
                            content: '';
                            position: absolute;
                            width: 200%;
                            height: 100%;
                            top: 0;
                            left: 50%;
                            transform: translate(-50%, 0);
                            background: linear-gradient(90deg, transparent, #3498db, transparent);
                            animation: wizard-wave 3s linear infinite;
                        }
                        .wizard-wave:nth-child(2):before {
                            animation-delay: -1s;
                            opacity: 0.5;
                        }
                        .wizard-wave:nth-child(3):before {
                            animation-delay: -2s;
                            opacity: 0.3;
                        }
                        @keyframes wizard-wave {
                            0% {
                                transform: translate(-50%, 0) translateX(-100%);
                            }
                            100% {
                                transform: translate(-50%, 0) translateX(100%);
                            }
                        }
                    </style>
                    
                    <div class="wizard-loader-container">
                        <div class="wizard-spinner">
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                        
                        <h3 id="wizard-message" style="color: #3498db; margin: 0 0 10px 0; font-size: 20px;">
                            Setup wird durchgeführt...
                        </h3>
                        <p style="color: #7f8c8d; margin: 0;">
                            Bitte warten, während wir Ihre Domain konfigurieren
                        </p>
                        
                        <div class="wizard-waves">
                            <div class="wizard-wave"></div>
                            <div class="wizard-wave"></div>
                            <div class="wizard-wave"></div>
                        </div>
                    </div>
                </div>

                <!-- Success-Screen (initial hidden) -->
                <div id="wizard-success" style="display:none;">
                    <!-- Heureka Animation -->
                    <div style="text-align: center; padding: 40px 20px; background: linear-gradient(135deg, #3498db 0%, #2ecc71 100%); border-radius: 8px; margin-bottom: 25px; position: relative; overflow: hidden;">
                        <div class="wizard-celebration" style="animation: celebration 0.6s ease-out;">
                            <div style="font-size: 72px; margin-bottom: 15px; animation: bounce 1s ease-in-out infinite;">
                                🎉
                            </div>
                            <h2 style="color: #fff; font-size: 32px; font-weight: bold; margin: 0 0 10px 0; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">
                                HEUREKA!
                            </h2>
                            <p style="color: rgba(255,255,255,0.95); font-size: 18px; margin: 0; font-weight: 500;">
                                Die erste Domain ist bereit
                            </p>
                        </div>
                    </div>
                    
                    <div class="alert alert-info" style="background: #e8f4fd; border-color: #bee5eb; color: #0c5460;">
                        <i class="fa fa-info-circle"></i> 
                        <strong>Nur noch ein paar Kleinigkeiten...</strong>
                    </div>
                        
                    <!-- Code-Generator Panel -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-code"></i> Code für Footer - Cookie-Einstellungen Link</h3>
                        </div>
                        <div class="panel-body">
                            <p style="margin-bottom: 15px;">
                                <strong>Cookie-Einstellungen Link</strong> für den Footer (DSGVO-Pflicht - Nutzer müssen Einwilligung jederzeit ändern können):
                            </p>
                            
                            <div style="position: relative;">
                                <pre id="wizard-generated-code-footer" style="padding: 15px; border-radius: 4px; border: 1px solid #ddd; font-size: 12px; line-height: 1.6; overflow-x: auto;"><code class="language-html">&lt;!-- Cookie-Einstellungen Link (empfohlen) --&gt;
&lt;a href="#" data-consent-action="settings"&gt;Cookie-Einstellungen&lt;/a&gt;

&lt;!-- Optional: Mit Auto-Reload nach Consent-Änderung --&gt;
&lt;a href="#" data-consent-action="settings,reload"&gt;Cookie-Einstellungen&lt;/a&gt;</code></pre>
                                <button type="button" class="btn btn-sm btn-default" 
                                        onclick="copyWizardCode('wizard-generated-code-footer')" 
                                        style="position: absolute; top: 10px; right: 10px;">
                                    <i class="fa fa-copy"></i> Kopieren
                                </button>
                            </div>
                            
                            <div class="alert alert-info" style="margin-top: 20px; margin-bottom: 0;">
                                <i class="fa fa-info-circle"></i> 
                                <strong>Einfach kopieren und in den Footer einfügen!</strong> Der Link funktioniert ohne weitere Anpassungen.
                            </div>
                        </div>
                    </div>
                    
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-tasks"></i> Was noch zu tun ist</h3>
                        </div>
                        <div class="panel-body">
                            <p style="margin-bottom: 20px;">Folgende Punkte solltest du noch überprüfen:</p>
                            
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
    
    /* Success Celebration Animations */
    @keyframes celebration {
        0% { opacity: 0; transform: scale(0.8); }
        50% { transform: scale(1.05); }
        100% { opacity: 1; transform: scale(1); }
    }
    
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    
    /* Progress Bar Animation */
    #wizard-progress-bar {
        transition: width 0.4s ease-out;
    }
    
    /* Button Hover */
    #wizard-btn-start:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    
    /* Panels: Keine Hover-Animationen für Safari-Stabilität */
    .panel {
        /* Keine Transform-Animationen */
    }
    
    /* Form Controls */
    .form-control:focus {
        transition: border-color 0.2s ease-out;
    }
    
    /* Loading Spinner */
    .fa-spinner {
        animation: spin 1s linear infinite;
    }
</style>

<script nonce="<?= rex_response::getNonce() ?>">
jQuery(function($) {
    var pollingInterval = null;
    
    // Template Checkboxen: "Alle auswählen/abwählen"
    $('#wizard-templates-select-all').on('change', function() {
        $('.wizard-template-checkbox').prop('checked', $(this).is(':checked'));
    });
    
    // Wenn einzelne Checkbox abgewählt wird → "Alle" auch abwählen
    $('.wizard-template-checkbox').on('change', function() {
        var allChecked = $('.wizard-template-checkbox').length === $('.wizard-template-checkbox:checked').length;
        $('#wizard-templates-select-all').prop('checked', allChecked);
    });
    
    // YRewrite Select → Input synchronisieren
    <?php if (count($yrewriteDomains) > 0): ?>
    $('#wizard-domain-select').on('change', function() {
        $('#wizard-domain-input').val($(this).val());
    });
    <?php endif ?>
    
    // Auto-Inject Toggle: Template-Auswahl anzeigen/verbergen
    $('#wizard-auto-inject').on('change', function() {
        if ($(this).is(':checked')) {
            $('#wizard-template-selection').slideDown(200);
        } else {
            $('#wizard-template-selection').slideUp(200);
        }
    });
    
    // Initial: Template-Auswahl anzeigen wenn Auto-Inject aktiv
    if ($('#wizard-auto-inject').is(':checked')) {
        $('#wizard-template-selection').show();
    }
    
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
        
        // Template-IDs aus Checkboxen sammeln
        var selectedTemplates = [];
        $('.wizard-template-checkbox:checked').each(function() {
            selectedTemplates.push($(this).val());
        });
        var includeTemplates = selectedTemplates.join(',');
        
        var privacyPolicy = $('#REX_LINK_wizard_privacy').val() || '0';
        var legalNotice = $('#REX_LINK_wizard_imprint').val() || '0';
        
        // AJAX Request - synchron, Setup läuft komplett durch
        var url = '<?= rex_url::backendController() ?>?rex-api-call=consent_manager_setup_wizard';
        
        $.ajax({
            url: url,
            method: 'POST',
            dataType: 'json',
            data: {
                domain: domain,
                setup_type: setupType,
                auto_inject: autoInject ? 1 : 0,
                include_templates: includeTemplates,
                privacy_policy: privacyPolicy,
                legal_notice: legalNotice
            },
            success: function(response) {
                console.log('Setup Response:', response);
                
                if (response.status === 'success') {
                    // Zum Abschluss-Screen wechseln
                    $('#wizard-progress').hide();
                    $('#wizard-success').show();
                    
                    // Optional: Domain-Informationen anzeigen falls vorhanden
                    if (response.data && response.data.domain) {
                        console.log('Setup erfolgreich für Domain:', response.data.domain);
                    }
                } else {
                    $('#wizard-status').removeClass('alert-info').addClass('alert-danger');
                    $('#wizard-message').html('<i class="rex-icon fa-times"></i> <strong>Fehler!</strong> ' + response.message);
                    $('#setup-wizard-modal .modal-header .close, #setup-wizard-modal .modal-footer .btn-default:first').show();
                    $('#wizard-btn-reset').show();
                }
            },
            error: function(xhr, status, error) {
                console.error('Setup fehlgeschlagen:', error);
                console.log('Response:', xhr.responseText);
                
                $('#wizard-status').removeClass('alert-info').addClass('alert-danger');
                $('#wizard-message').html('<i class="rex-icon fa-times"></i> <strong>Fehler!</strong> ' + error);
                $('#setup-wizard-modal .modal-header .close, #setup-wizard-modal .modal-footer .btn-default:first').show();
                $('#wizard-btn-reset').show();
            }
        });
    }
    
    function triggerProcessing() {
        var url = '<?= rex_url::backendController() ?>?rex-api-call=consent_manager_setup_wizard';
        
        // Separaten Request für Processing senden - KEINE JSON Response erwartet
        $.ajax({
            url: url,
            method: 'POST',
            data: {
                action: 'process'
            },
            success: function(response) {
                console.log('Processing gestartet');
                // Kein logEvent hier - wird vom Polling erfasst
            },
            error: function(xhr, status, error) {
                console.error('Processing fehlgeschlagen:', error);
                console.log('Response:', xhr.responseText);
                logEvent('✗ Processing-Fehler: ' + error, 'error');
                clearInterval(pollingInterval);
                $('#wizard-status').removeClass('alert-info').addClass('alert-danger');
                $('#setup-wizard-modal .modal-header .close, #setup-wizard-modal .modal-footer .btn-default:first').show();
                $('#wizard-btn-reset').show();
            }
        });
    }
    
    function startPolling() {
        var url = '<?= rex_url::backendController() ?>?rex-api-call=consent_manager_setup_wizard';
        
        pollingInterval = setInterval(function() {
            $.ajax({
                url: url,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'status'
                },
                success: function(progress) {
                    if (!progress || !progress.status) {
                        return;
                    }
                    
                    console.log('Progress:', progress);
                    
                    // UI Update
                    updateProgress(progress.percent || 0, progress.message || '');
                    
                    // Log-Eintrag für laufendes Setup
                    if (progress.status === 'running' && progress.message) {
                        logEvent('⏳ ' + progress.message, 'info');
                    }
                    
                    // Status auswerten
                    if (progress.status === 'complete') {
                        clearInterval(pollingInterval);
                        pollingInterval = null;
                        showSuccess({
                            message: progress.message,
                            domain: progress.data?.domain || '',
                            url: progress.data?.url || ''
                        });
                    } else if (progress.status === 'error') {
                        clearInterval(pollingInterval);
                        pollingInterval = null;
                        logEvent('✗ Fehler: ' + progress.message, 'error');
                        $('#wizard-status').removeClass('alert-info').addClass('alert-danger');
                        $('#setup-wizard-modal .modal-header .close, #setup-wizard-modal .modal-footer .btn-default:first').show();
                        $('#wizard-btn-reset').show();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Polling-Fehler:', error);
                }
            });
        }, 500); // Poll alle 500ms
    }
    
    function updateProgress(percent, message) {
        $('#wizard-progress-bar').css('width', percent + '%');
        $('#wizard-progress-text').text(percent + '%');
        
        // Spinner entfernen und nur Text anzeigen
        var statusText = $('#wizard-status-text');
        statusText.html(message);
    }
    
    function logEvent(message, type) {
        console.log('logEvent called:', message, type); // Debug
        
        var icon = type === 'success' ? '✓' : type === 'error' ? '✗' : '→';
        var color = type === 'success' ? '#5cb85c' : type === 'error' ? '#d9534f' : '#666';
        var time = new Date().toLocaleTimeString('de-DE');
        
        var logContent = $('#wizard-log-content');
        console.log('wizard-log-content exists:', logContent.length > 0); // Debug
        
        var logEntry = '<div style="color: ' + color + '; margin-bottom: 5px;">' +
            '<span style="color: #999;">[' + time + ']</span> ' +
            message +
            '</div>';
        
        console.log('Appending:', logEntry); // Debug
        logContent.append(logEntry);
        
        // Auto-scroll
        var logContainer = logContent.parent();
        if (logContainer.length > 0 && logContainer[0]) {
            logContainer.scrollTop(logContainer[0].scrollHeight);
        }
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
    
    function copyWizardCode(elementId) {
        var code = document.getElementById(elementId).textContent;
        
        // Moderne Clipboard API
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(code).then(function() {
                showCopySuccess();
            }).catch(function() {
                fallbackCopy(code);
            });
        } else {
            fallbackCopy(code);
        }
    }
    
    function fallbackCopy(text) {
        var textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        document.body.appendChild(textArea);
        textArea.select();
        
        try {
            document.execCommand('copy');
            showCopySuccess();
        } catch (err) {
            alert('Kopieren fehlgeschlagen. Bitte manuell kopieren.');
        }
        
        document.body.removeChild(textArea);
    }
    
    function showCopySuccess() {
        // Temporäre Success-Nachricht
        var btn = event.target;
        if (!btn || btn.tagName !== 'BUTTON') {
            btn = event.target.closest('button');
        }
        if (!btn) return;
        
        var originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-check"></i> Kopiert!';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-default');
        
        setTimeout(function() {
            btn.innerHTML = originalHtml;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-default');
        }, 2000);
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
        
        // Polling stoppen falls noch aktiv
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
    }
    
    // Navigation Links Handler
    $('#wizard-success').on('click', '.wizard-nav-link', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var url = $(this).attr('href');
        console.log('Navigation to:', url);
        
     
    
    function downloadWizardCode(elementId, filename) {
        var code = document.getElementById(elementId).textContent;
        var blob = new Blob([code], { type: 'text/plain' });
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        
        // Visual Feedback
        var btn = event.target;
        if (!btn || btn.tagName !== 'BUTTON') {
            btn = event.target.closest('button');
        }
        if (!btn) return;
        
        var originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-check"></i> Geladen!';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-default');
        
        setTimeout(function() {
            btn.innerHTML = originalHtml;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-default');
        }, 2000);
    }   // Modal schließen
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
