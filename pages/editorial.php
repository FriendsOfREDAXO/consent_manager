<?php

/**
 * Editorial-Seite für Redakteure
 * Zugriff nur mit consent_manager[editorial] Berechtigung.
 */

$addon = rex_addon::get('consent_manager');

// Prüfe ob Issue Tracker installiert ist
$hasIssueTracker = rex_addon::exists('issue_tracker') && rex_addon::get('issue_tracker')->isAvailable();

// Admin-Info Text laden
$adminInfo = $addon->getConfig('editorial_info', '');

// Prüfe ob Auto-Blocking aktiviert ist
$autoBlockingEnabled = (bool) $addon->getConfig('auto_blocking_enabled', false);

// Prüfe ob User Config-Rechte hat
$hasConfigPermission = rex::getUser()->isAdmin() || rex::getUser()->hasPerm('consent_manager[config]');

?>

<style nonce="<?= rex_response::getNonce() ?>">
/* Editorial Page - Modern Card Layout */
.consent-editorial-container {
    max-width: 1400px;
    margin: 0 auto;
}

.consent-editorial-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.consent-editorial-card {
    background: #fff;
    border-radius: 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.2s ease;
    border: 1px solid #ddd;
}

.consent-editorial-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.consent-editorial-card-header {
    padding: 15px 20px;
    font-weight: 600;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 1px solid #ddd;
}

.consent-editorial-card-header i {
    font-size: 18px;
    opacity: 0.9;
}

.consent-editorial-card-body {
    padding: 20px;
    font-size: 14px;
    line-height: 1.6;
}

/* Card Variants - Bootstrap 3 Standard Colors */
.card-primary .consent-editorial-card-header {
    background: #337ab7;
    color: #fff;
    border-color: #2e6da4;
}

.card-info .consent-editorial-card-header {
    background: #5bc0de;
    color: #fff;
    border-color: #46b8da;
}

.card-warning .consent-editorial-card-header {
    background: #f0ad4e;
    color: #fff;
    border-color: #eea236;
}

.card-success .consent-editorial-card-header {
    background: #5cb85c;
    color: #fff;
    border-color: #4cae4c;
}

.card-danger .consent-editorial-card-header {
    background: #d9534f;
    color: #fff;
    border-color: #d43f3a;
}

.card-default .consent-editorial-card-header {
    background: #f5f5f5;
    color: #333;
    border-color: #ddd;
}

/* Full Width Cards */
.consent-editorial-card-full {
    grid-column: 1 / -1;
}

/* Two-Thirds Width Cards */
.consent-editorial-card-twothirds {
    grid-column: span 2;
}

/* Compact Lists */
.consent-compact-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.consent-compact-list li {
    padding: 8px 0;
    padding-left: 25px;
    position: relative;
    font-size: 13px;
}

.consent-compact-list li:before {
    content: "✓";
    position: absolute;
    left: 0;
    color: #5cb85c;
    font-weight: bold;
}

/* Highlight Box - Compact */
.consent-highlight-compact {
    background: rgba(91, 192, 222, 0.08);
    border-left: 3px solid #5bc0de;
    padding: 12px 15px;
    border-radius: 0;
    margin: 12px 0;
    font-size: 13px;
}

/* Steps - Compact */
.consent-steps-compact {
    counter-reset: step-counter;
    list-style: none;
    padding: 0;
}

.consent-steps-compact li {
    counter-increment: step-counter;
    padding: 10px 0;
    padding-left: 40px;
    position: relative;
    font-size: 13px;
}

.consent-steps-compact li:before {
    content: counter(step-counter);
    position: absolute;
    left: 0;
    top: 10px;
    width: 28px;
    height: 28px;
    background: #337ab7;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 13px;
}

.consent-steps-compact li strong {
    display: block;
    margin-bottom: 3px;
    color: #337ab7;
}

/* CTA Button */
.consent-cta-button {
    display: inline-block;
    margin-top: 15px;
    width: 100%;
}

/* Dark Theme Support - REDAXO Theme & System Preference */
@media (prefers-color-scheme: dark) {
    body:not(.rex-theme-light) .consent-editorial-card,
    body.rex-theme-dark .consent-editorial-card {
        background: #1f2937;
        border-color: rgba(255,255,255,0.1);
        box-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    
    body:not(.rex-theme-light) .consent-editorial-card:hover,
    body.rex-theme-dark .consent-editorial-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    }
    
    body:not(.rex-theme-light) .consent-editorial-card-body,
    body.rex-theme-dark .consent-editorial-card-body {
        color: #e5e7eb !important;
        background: #1f2937 !important;
    }
    
    body:not(.rex-theme-light) .card-default .consent-editorial-card-header,
    body.rex-theme-dark .card-default .consent-editorial-card-header {
        background: #374151;
        color: #e5e7eb;
        border-bottom-color: rgba(255,255,255,0.1);
    }
    
    body:not(.rex-theme-light) .consent-highlight-compact,
    body.rex-theme-dark .consent-highlight-compact {
        background: rgba(91, 192, 222, 0.15);
        border-color: rgba(91, 192, 222, 0.3);
    }
    
    body:not(.rex-theme-light) .consent-steps-compact li strong,
    body.rex-theme-dark .consent-steps-compact li strong {
        color: #60a5fa;
    }
    
    body:not(.rex-theme-light) .consent-compact-list li:before,
    body.rex-theme-dark .consent-compact-list li:before {
        color: #60a5fa;
    }
    
    body:not(.rex-theme-light) .alert-info,
    body.rex-theme-dark .alert-info {
        background: rgba(91, 192, 222, 0.15);
        border-color: rgba(91, 192, 222, 0.3);
        color: #e5e7eb;
    }
    
    body:not(.rex-theme-light) .panel-default,
    body.rex-theme-dark .panel-default {
        background: #374151;
        border-color: rgba(255,255,255,0.1);
    }
    
    body:not(.rex-theme-light) .panel-default .panel-body,
    body.rex-theme-dark .panel-default .panel-body {
        color: #e5e7eb;
    }
}

/* Forced Dark Theme */
body.rex-theme-dark .consent-editorial-card {
    background: #1f2937;
    border-color: rgba(255,255,255,0.1);
    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

body.rex-theme-dark .consent-editorial-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
}

body.rex-theme-dark .consent-editorial-card-body {
    color: #e5e7eb !important;
    background: #1f2937 !important;
}

body.rex-theme-dark .card-default .consent-editorial-card-header {
    background: #374151;
    color: #e5e7eb;
    border-bottom-color: rgba(255,255,255,0.1);
}

body.rex-theme-dark .consent-highlight-compact {
    background: rgba(91, 192, 222, 0.15);
    border-color: rgba(91, 192, 222, 0.3);
}

body.rex-theme-dark .consent-steps-compact li strong {
    color: #60a5fa;
}

body.rex-theme-dark .consent-compact-list li:before {
    color: #60a5fa;
}

body.rex-theme-dark .alert-info {
    background: rgba(91, 192, 222, 0.15);
    border-color: rgba(91, 192, 222, 0.3);
    color: #e5e7eb;
}

body.rex-theme-dark .panel-default {
    background: #374151;
    border-color: rgba(255,255,255,0.1);
}

body.rex-theme-dark .panel-default .panel-body {
    color: #e5e7eb;
}

/* Responsive */
@media (max-width: 768px) {
    .consent-editorial-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="rex-addon-output consent-editorial-container">
    <!-- Auto-Blocking Warnung (wenn nicht aktiviert) -->
    <?php if (!$autoBlockingEnabled): ?>
    <div class="consent-editorial-card card-danger consent-editorial-card-full" style="margin-bottom: 20px;">
        <div class="consent-editorial-card-header">
            <i class="rex-icon fa-exclamation-triangle"></i>
            <span>Auto-Blocking ist nicht aktiviert</span>
        </div>
        <div class="consent-editorial-card-body" style="background: #fff; color: #333;">
            <p style="margin: 0 0 10px 0;">
                <strong>Die automatische Blockierung externer Inhalte ist derzeit deaktiviert.</strong>
            </p>
            <?php if ($hasConfigPermission): ?>
            <p style="margin: 0;">
                Damit die hier generierten Codes funktionieren, muss Auto-Blocking in den 
                <a href="<?= rex_url::backendPage('consent_manager/config') ?>"><strong>Einstellungen aktiviert werden</strong></a>.
                Ohne diese Funktion werden externe Inhalte direkt geladen und umgehen den Consent-Mechanismus.
            </p>
            <?php else: ?>
            <p style="margin: 0;">
                Damit die hier generierten Codes funktionieren, muss Auto-Blocking in den Einstellungen aktiviert werden.
                <strong>Bitte kontaktieren Sie einen Administrator oder Benutzer mit Config-Berechtigung</strong>, 
                um diese Funktion zu aktivieren. Ohne Auto-Blocking werden externe Inhalte direkt geladen und umgehen den Consent-Mechanismus.
            </p>
            <?php endif ?>
        </div>
    </div>
    <?php endif ?>
    
    <!-- Admin-Hinweise (Full Width wenn vorhanden) -->
    <?php if ('' !== $adminInfo): ?>
    <div class="consent-editorial-card card-primary consent-editorial-card-full" style="margin-bottom: 20px;">
        <div class="consent-editorial-card-header">
            <i class="rex-icon fa-info-circle"></i>
            <span><?= $addon->i18n('consent_manager_editorial_admin_info_title') ?></span>
        </div>
        <div class="consent-editorial-card-body" style="background: #fff; color: #333;">
            <div><?= rex_escape($adminInfo, 'html') ?></div>
        </div>
    </div>
    <?php endif ?>
    
    <!-- Top Grid: Info + Warning -->
    <div class="consent-editorial-grid">
        <!-- Willkommen Card -->
        <div class="consent-editorial-card card-info">
            <div class="consent-editorial-card-header">
                <i class="rex-icon fa-code"></i>
                <span><?= $addon->i18n('consent_manager_editorial_welcome_title') ?></span>
            </div>
            <div class="consent-editorial-card-body">
                <p style="margin: 0;"><?= rex_i18n::rawMsg('consent_manager_editorial_welcome_intro') ?></p>
            </div>
        </div>

        <!-- Wichtig Card -->
        <div class="consent-editorial-card card-warning">
            <div class="consent-editorial-card-header">
                <i class="rex-icon fa-exclamation-triangle"></i>
                <span><?= $addon->i18n('consent_manager_editorial_important_title') ?></span>
            </div>
            <div class="consent-editorial-card-body">
                <p style="margin: 0 0 10px 0; font-size: 13px;"><?= rex_i18n::rawMsg('consent_manager_editorial_important_text') ?></p>
                <div class="consent-highlight-compact">
                    <strong style="display: block; margin-bottom: 5px;">✓ Richtige Verwendung:</strong>
                    YouTube/Maps/Calendly im Content
                </div>
            </div>
        </div>

        <!-- Assistent Button Card -->
        <div class="consent-editorial-card card-primary">
            <div class="consent-editorial-card-header">
                <i class="rex-icon fa-magic"></i>
                <span><?= $addon->i18n('consent_manager_editorial_open_assistant') ?></span>
            </div>
            <div class="consent-editorial-card-body" style="background: #fff; color: #333; text-align: center; padding: 30px 20px;">
                <button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#auto-blocking-assistant-modal" style="padding: 15px 40px; font-size: 16px;">
                    <i class="rex-icon fa-magic"></i> <?= $addon->i18n('consent_manager_editorial_open_assistant') ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Anleitung - Akkordeon -->
    <div class="consent-editorial-card card-default consent-editorial-card-full" style="margin-bottom: 20px;">
        <div class="consent-editorial-card-header collapsed" data-toggle="collapse" data-target="#howto-content" style="cursor: pointer;">
            <i class="rex-icon fa-list-ol"></i>
            <span><?= $addon->i18n('consent_manager_editorial_howto_title') ?></span>
            <span class="pull-right"><i class="rex-icon fa-chevron-down"></i></span>
        </div>
        <div class="consent-editorial-card-body collapse" id="howto-content" style="background: #fff; color: #333;">
            <ol class="consent-steps-compact">
                <li>
                    <strong><?= $addon->i18n('consent_manager_editorial_step1_title') ?></strong>
                    <?= $addon->i18n('consent_manager_editorial_step1_desc') ?>
                </li>
                <li>
                    <strong><?= $addon->i18n('consent_manager_editorial_step2_title') ?></strong>
                    <?= $addon->i18n('consent_manager_editorial_step2_desc') ?>
                </li>
                <li>
                    <strong><?= $addon->i18n('consent_manager_editorial_step3_title') ?></strong>
                    <?= $addon->i18n('consent_manager_editorial_step3_desc') ?>
                </li>
                <li>
                    <strong><?= $addon->i18n('consent_manager_editorial_step4_title') ?></strong>
                    <?= $addon->i18n('consent_manager_editorial_step4_desc') ?>
                    <div class="consent-highlight-compact" style="margin-top: 8px;">
                        <strong><?= $addon->i18n('consent_manager_editorial_step4_how_title') ?></strong>
                        <ul style="margin: 5px 0 0 20px; font-size: 12px;">
                            <li><?= rex_i18n::rawMsg('consent_manager_editorial_step4_how_cke5') ?></li>
                            <li><?= rex_i18n::rawMsg('consent_manager_editorial_step4_how_other') ?></li>
                        </ul>
                    </div>
                </li>
            </ol>
        </div>
    </div>

    <!-- Bottom Grid: Snippets (2/3) + Service/Privacy (1/3) -->
    <div class="consent-editorial-grid" style="grid-template-columns: 2fr 1fr;">
        <!-- Snippets Card - 2/3 Breite -->
        <div class="consent-editorial-card card-primary">
            <div class="consent-editorial-card-header">
                <i class="rex-icon fa-bookmark"></i>
                <span><?= $addon->i18n('consent_manager_editorial_snippets_title') ?></span>
            </div>
            <div class="consent-editorial-card-body" style="background: #fff; color: #333;">
                <div id="snippets-container">
                    <div class="alert alert-info" id="snippets-empty-state" style="margin: 0; padding: 10px; font-size: 13px;">
                        <i class="rex-icon fa-info-circle"></i> <?= $addon->i18n('consent_manager_editorial_snippets_empty') ?>
                    </div>
                    <div id="snippets-list" style="display: none;"></div>
                </div>
            </div>
        </div>

        <!-- Rechte Spalte: Service + Datenschutz als Akkordeons -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <!-- Service fehlt - Akkordeon -->
            <div class="consent-editorial-card card-<?= $hasIssueTracker ? 'success' : 'warning' ?>">
                <div class="consent-editorial-card-header collapsed" data-toggle="collapse" data-target="#service-content" style="cursor: pointer;">
                    <i class="rex-icon fa-<?= $hasIssueTracker ? 'lightbulb-o' : 'exclamation-triangle' ?>"></i>
                    <span><?= $addon->i18n('consent_manager_editorial_missing_service_title') ?></span>
                    <span class="pull-right"><i class="rex-icon fa-chevron-down"></i></span>
                </div>
                <div class="consent-editorial-card-body collapse" id="service-content" style="background: #fff; color: #333;">
                <p style="margin: 0 0 10px 0; font-size: 13px;"><?= $addon->i18n('consent_manager_editorial_missing_service_desc') ?></p>
                
                <?php if ($hasIssueTracker): ?>
                    <a href="<?= rex_url::backendPage('issue_tracker/issues', ['func' => 'add']) ?>" class="btn btn-success btn-block btn-sm">
                        <i class="rex-icon fa-plus-circle"></i> <?= $addon->i18n('consent_manager_editorial_request_service') ?>
                    </a>
                <?php else: ?>
                    <div class="consent-highlight-compact" style="margin: 0; padding: 10px; font-size: 12px;">
                        <i class="rex-icon fa-envelope"></i> <?= rex_i18n::rawMsg('consent_manager_editorial_contact_admin_desc') ?>
                    </div>
                <?php endif ?>
            </div>
        </div>

        <!-- Datenschutz - Akkordeon -->
        <div class="consent-editorial-card card-info">
            <div class="consent-editorial-card-header collapsed" data-toggle="collapse" data-target="#privacy-content" style="cursor: pointer;">
                <i class="rex-icon fa-shield"></i>
                <span><?= $addon->i18n('consent_manager_editorial_privacy_title') ?></span>
                <span class="pull-right"><i class="rex-icon fa-chevron-down"></i></span>
            </div>
            <div class="consent-editorial-card-body collapse" id="privacy-content" style="background: #fff; color: #333;">
                <p style="margin: 0 0 10px 0; font-size: 13px;"><strong><?= $addon->i18n('consent_manager_editorial_privacy_intro') ?></strong></p>
                
                <ul class="consent-compact-list" style="margin-bottom: 10px;">
                    <li><?= $addon->i18n('consent_manager_editorial_privacy_what_service') ?></li>
                    <li><?= $addon->i18n('consent_manager_editorial_privacy_what_data') ?></li>
                    <li><?= $addon->i18n('consent_manager_editorial_privacy_what_provider') ?></li>
                </ul>
                
                <?php if ($hasIssueTracker): ?>
                    <a href="<?= rex_url::backendPage('issue_tracker/issues', ['func' => 'add']) ?>" class="btn btn-info btn-block btn-sm">
                        <i class="rex-icon fa-pencil"></i> <?= $addon->i18n('consent_manager_editorial_privacy_issue_title') ?>
                    </a>
                <?php endif ?>
            </div>
        </div>
        </div>
    </div>

    <!-- Beispiel-Showcase - Collapsible Card -->
    <div class="consent-editorial-card card-default consent-editorial-card-full">
        <div class="consent-editorial-card-header collapsed" data-toggle="collapse" data-target="#example-showcase" style="cursor: pointer;">
            <i class="rex-icon fa-code"></i>
            <span><?= $addon->i18n('consent_manager_editorial_examples_title') ?></span>
            <span class="pull-right"><i class="rex-icon fa-chevron-down"></i></span>
        </div>
        <div class="consent-editorial-card-body collapse" id="example-showcase">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <div>
                    <h4 style="margin: 0 0 10px 0; font-size: 14px; color: #337ab7;"><?= $addon->i18n('consent_manager_editorial_example_youtube') ?></h4>
                    <p style="margin: 0 0 10px 0; font-size: 12px;"><?= $addon->i18n('consent_manager_editorial_example_youtube_desc') ?></p>
                    <pre style="font-size: 11px; padding: 10px; background: #f5f5f5; border-radius: 0;"><code>&lt;iframe src="https://www.youtube.com/embed/VIDEO_ID" 
        width="560" height="315"
        data-consent-block="true"
        data-consent-service="youtube"&gt;&lt;/iframe&gt;</code></pre>
                </div>

                <div>
                    <h4 style="margin: 0 0 10px 0; font-size: 14px; color: #337ab7;"><?= $addon->i18n('consent_manager_editorial_example_custom') ?></h4>
                    <p style="margin: 0 0 10px 0; font-size: 12px;"><?= $addon->i18n('consent_manager_editorial_example_custom_desc') ?></p>
                    <pre style="font-size: 11px; padding: 10px; background: #f5f5f5; border-radius: 0;"><code>&lt;script src="https://example.com/widget.js"
        data-consent-block="true"
        data-consent-service="example"
        data-consent-text="Ihr Text"&gt;&lt;/script&gt;</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Auto-Blocking Assistent Modal (aus config.php übernommen) -->
<div class="modal fade" id="auto-blocking-assistant-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="rex-icon fa-magic"></i> <?= $addon->i18n('consent_manager_auto_blocking_assistant_title') ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <p><?= $addon->i18n('consent_manager_auto_blocking_assistant_intro') ?></p>
                </div>

                <form id="auto-blocking-assistant">
                    <!-- Original Code Input -->
                    <div class="form-group">
                        <label for="original_code"><?= $addon->i18n('consent_manager_auto_blocking_assistant_input_label') ?></label>
                        <textarea class="form-control" id="original_code" rows="4" 
                                  placeholder="<?= $addon->i18n('consent_manager_auto_blocking_assistant_input_placeholder') ?>"></textarea>
                        <p class="help-block">Fügen Sie hier Ihren externen Script- oder iframe-Code ein</p>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <!-- Service Key -->
                            <div class="form-group">
                                <label for="service_key"><?= $addon->i18n('consent_manager_auto_blocking_assistant_service_label') ?> *</label>
                                <select class="form-control" id="service_key">
                                    <option value=""><?= $addon->i18n('consent_manager_auto_blocking_assistant_service_select') ?></option>
                                    <?php
                                    // Lade alle verfügbaren Dienste aus der aktuellen Sprache
                                    $sql = rex_sql::factory();
                                    $clang_id = rex_clang::getCurrentId();
                                    $services = $sql->getArray('SELECT uid, service_name FROM ' . rex::getTable('consent_manager_cookie') . ' WHERE clang_id = ? ORDER BY service_name', [$clang_id]);
                                    foreach ($services as $service) {
                                        echo '<option value="' . rex_escape($service['uid']) . '">' . rex_escape($service['service_name']) . ' (' . rex_escape($service['uid']) . ')</option>';
                                    }
                                    ?>
                                </select>
                                <p class="help-block"><?= $addon->i18n('consent_manager_auto_blocking_assistant_service_notice') ?></p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <!-- Provider Name -->
                            <div class="form-group">
                                <label for="provider_name"><?= $addon->i18n('consent_manager_auto_blocking_assistant_provider_label') ?></label>
                                <input type="text" class="form-control" id="provider_name" placeholder="z.B. Calendly">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <!-- Privacy URL -->
                            <div class="form-group">
                                <label for="privacy_url"><?= $addon->i18n('consent_manager_auto_blocking_assistant_privacy_label') ?></label>
                                <input type="url" class="form-control" id="privacy_url" placeholder="https://example.com/datenschutz">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <!-- Title -->
                            <div class="form-group">
                                <label for="consent_title"><?= $addon->i18n('consent_manager_auto_blocking_assistant_title_label') ?></label>
                                <input type="text" class="form-control" id="consent_title" placeholder="z.B. Termin buchen">
                            </div>
                        </div>
                    </div>

                    <!-- Custom Text -->
                    <div class="form-group">
                        <label for="consent_text"><?= $addon->i18n('consent_manager_auto_blocking_assistant_text_label') ?></label>
                        <textarea class="form-control" id="consent_text" rows="2" placeholder="<?= $addon->i18n('consent_manager_auto_blocking_assistant_text_placeholder') ?>"></textarea>
                        <p class="help-block"><?= $addon->i18n('consent_manager_auto_blocking_assistant_text_notice') ?></p>
                    </div>

                    <!-- Generate Button -->
                    <div class="form-group">
                        <button type="button" class="btn btn-primary" id="generate_code">
                            <i class="rex-icon fa-magic"></i> <?= $addon->i18n('consent_manager_auto_blocking_assistant_generate') ?>
                        </button>
                    </div>

                    <!-- Output Code -->
                    <div class="form-group" id="output_container" style="display: none;">
                        <label for="output_code"><?= $addon->i18n('consent_manager_auto_blocking_assistant_output_label') ?></label>
                        <textarea class="form-control" id="output_code" rows="8" readonly></textarea>
                        <div style="margin-top: 10px;">
                            <button type="button" class="btn btn-success btn-sm" id="copy_code">
                                <i class="rex-icon fa-clipboard"></i> <?= $addon->i18n('consent_manager_auto_blocking_assistant_copy') ?>
                            </button>
                            <button type="button" class="btn btn-info btn-sm" id="save_snippet">
                                <i class="rex-icon fa-bookmark"></i> <?= $addon->i18n('consent_manager_editorial_snippets_save') ?>
                            </button>
                            <span id="copy_success" style="display: none; margin-left: 10px; color: #5cb85c;">
                                <i class="rex-icon fa-check"></i> <?= $addon->i18n('consent_manager_auto_blocking_assistant_copied') ?>
                            </span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="rex-icon fa-times"></i> Schließen
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Snippet Name Modal -->
<div class="modal fade" id="snippet-name-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="rex-icon fa-bookmark"></i> <?= $addon->i18n('consent_manager_editorial_snippets_save') ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="snippet_name_input"><?= $addon->i18n('consent_manager_editorial_snippets_name') ?></label>
                    <input type="text" class="form-control" id="snippet_name_input" 
                           placeholder="<?= $addon->i18n('consent_manager_editorial_snippets_name_placeholder') ?>" 
                           autofocus>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="rex-icon fa-times"></i> Abbrechen
                </button>
                <button type="button" class="btn btn-primary" id="confirm_save_snippet">
                    <i class="rex-icon fa-save"></i> Speichern
                </button>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= rex_response::getNonce() ?>">
jQuery(function($) {
    'use strict';
    
    // LocalStorage Key
    var STORAGE_KEY = 'consent_manager_snippets';
    
    // Snippet-Verwaltung
    const snippetManager = {
        load: function() {
            try {
                var data = localStorage.getItem(STORAGE_KEY);
                return data ? JSON.parse(data) : [];
            } catch (e) {
                return [];
            }
        },
        
        save: function(snippets) {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(snippets));
                return true;
            } catch (e) {
                alert('Fehler beim Speichern des Snippets. Möglicherweise ist der LocalStorage voll.');
                return false;
            }
        },
        
        add: function(name, code, metadata) {
            var snippets = this.load();
            snippets.push({
                id: Date.now(),
                name: name,
                code: code,
                metadata: metadata || {},
                created: new Date().toISOString()
            });
            return this.save(snippets);
        },
        
        delete: function(id) {
            var snippets = this.load().filter(function(s) { return s.id !== id; });
            return this.save(snippets);
        },
        
        render: function() {
            var snippets = this.load();
            var $container = $('#snippets-list');
            var $emptyState = $('#snippets-empty-state');
            
            if (snippets.length === 0) {
                $container.hide();
                $emptyState.show();
                return;
            }
            
            $emptyState.hide();
            $container.empty().show();
            
            snippets.forEach(function(snippet) {
                var date = new Date(snippet.created).toLocaleDateString('de-DE');
                var $item = $(
                    '<div class="panel panel-default" data-snippet-id="' + snippet.id + '">' +
                        '<div class="panel-body">' +
                            '<div class="row">' +
                                '<div class="col-sm-8">' +
                                    '<strong><i class="rex-icon fa-bookmark"></i> ' + $('<div>').text(snippet.name).html() + '</strong>' +
                                    '<br><small class="text-muted">Erstellt: ' + date + '</small>' +
                                    (snippet.metadata.service ? '<br><small>Service: ' + $('<div>').text(snippet.metadata.service).html() + '</small>' : '') +
                                '</div>' +
                                '<div class="col-sm-4 text-right">' +
                                    '<button class="btn btn-primary btn-sm load-snippet" data-snippet-id="' + snippet.id + '">' +
                                        '<i class="rex-icon fa-download"></i> <?= $addon->i18n('consent_manager_editorial_snippets_load') ?>' +
                                    '</button>' +
                                    '<button class="btn btn-danger btn-sm delete-snippet" data-snippet-id="' + snippet.id + '">' +
                                        '<i class="rex-icon fa-trash"></i> <?= $addon->i18n('consent_manager_editorial_snippets_delete') ?>' +
                                    '</button>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>'
                );
                $container.append($item);
            });
        }
    };
    
    // Initial Snippets laden
    snippetManager.render();
    
    // Snippet laden
    $(document).on('click', '.load-snippet', function() {
        var id = parseInt($(this).data('snippet-id'));
        var snippets = snippetManager.load();
        var snippet = snippets.find(function(s) { return s.id === id; });
        
        if (!snippet) {
            alert('Snippet nicht gefunden!');
            return;
        }
        
        // Modal öffnen und Code einfügen
        $('#auto-blocking-assistant-modal').modal('show');
        
        // Kurze Verzögerung, damit Modal geladen ist
        setTimeout(function() { {
            $('#output_code').val(snippet.code);
            $('#output_container').show();
            
            // Metadata zurücksetzen wenn vorhanden
            if (snippet.metadata.service) {
                $('#service_key').val(snippet.metadata.service);
            }
            if (snippet.metadata.provider) {
                $('#provider_name').val(snippet.metadata.provider);
            }
            if (snippet.metadata.privacy) {
                $('#privacy_url').val(snippet.metadata.privacy);
            }
            if (snippet.metadata.title) {
                $('#consent_title').val(snippet.metadata.title);
            }
            if (snippet.metadata.text) {
                $('#consent_text').val(snippet.metadata.text);
            }
        }, 300);
    });
    
    // Snippet löschen
    $(document).on('click', '.delete-snippet', function() {
        if (!confirm('<?= $addon->i18n('consent_manager_editorial_snippets_delete_confirm') ?>')) {
            return;
        }
        
        var id = parseInt($(this).data('snippet-id'));
        if (snippetManager.delete(id)) {
            snippetManager.render();
        }
    });
    
    // Snippet speichern - Modal öffnen
    $('#save_snippet').on('click', function() {
        var code = $('#output_code').val();
        
        if (!code) {
            alert('Kein Code zum Speichern vorhanden!');
            return;
        }
        
        // Modal öffnen
        $('#snippet_name_input').val('');
        $('#snippet-name-modal').modal('show');
    });
    
    // Snippet speichern bestätigen
    $('#confirm_save_snippet').on('click', function() {
        var name = $('#snippet_name_input').val().trim();
        
        if (!name) {
            alert('Bitte geben Sie einen Namen ein!');
            return;
        }
        
        var code = $('#output_code').val();
        var metadata = {
            service: $('#service_key').val(),
            provider: $('#provider_name').val(),
            privacy: $('#privacy_url').val(),
            title: $('#consent_title').val(),
            text: $('#consent_text').val()
        };
        
        if (snippetManager.add(name, code, metadata)) {
            snippetManager.render();
            $('#snippet-name-modal').modal('hide');
            alert('Snippet erfolgreich gespeichert!');
        }
    });
    
    // Enter-Taste im Modal
    $('#snippet_name_input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#confirm_save_snippet').click();
        }
    });
    
    $('#generate_code').on('click', function() {
        var originalCode = $('#original_code').val().trim();
        var serviceKey = $('#service_key').val().trim();
        var providerName = $('#provider_name').val().trim();
        var privacyUrl = $('#privacy_url').val().trim();
        var title = $('#consent_title').val().trim();
        var customText = $('#consent_text').val().trim();
        
        if (!originalCode) {
            alert('Bitte Original-Code eingeben!');
            return;
        }
        
        if (!serviceKey) {
            alert('Bitte Service auswählen!');
            return;
        }
        
        // Parse HTML und füge Attribute hinzu
        var $temp = $('<div>').html(originalCode);
        var $element = $temp.children().first();
        
        if ($element.length === 0) {
            alert('Ungültiger HTML-Code!');
            return;
        }
        
        // Basis-Attribute
        $element.attr('data-consent-block', 'true');
        $element.attr('data-consent-service', serviceKey);
        
        // Optionale Attribute
        if (providerName) {
            $element.attr('data-consent-provider', providerName);
        }
        if (privacyUrl) {
            $element.attr('data-consent-privacy', privacyUrl);
        }
        if (title) {
            $element.attr('data-consent-title', title);
        }
        if (customText) {
            $element.attr('data-consent-text', customText);
        }
        
        // Generierter Code
        var generatedCode = $temp.html();
        
        // Formatierung verbessern (Einrückung bei mehrzeiligen Attributen)
        generatedCode = generatedCode.replace(/data-consent-/g, '\n        data-consent-');
        
        // Ausgabe
        $('#output_code').val(generatedCode);
        $('#output_container').slideDown();
    });
    
    $('#copy_code').on('click', function() {
        var outputCode = $('#output_code')[0];
        outputCode.select();
        outputCode.setSelectionRange(0, 99999); // Mobile
        
        try {
            document.execCommand('copy');
            $('#copy_success').fadeIn().delay(2000).fadeOut();
        } catch (err) {
            alert('Kopieren fehlgeschlagen. Bitte manuell kopieren.');
        }
    });
    
    // Modal-Event: Formular zurücksetzen beim Öffnen
    $('#auto-blocking-assistant-modal').on('show.bs.modal', function() {
        $('#auto-blocking-assistant')[0].reset();
        $('#output_container').hide();
    });
});
</script>
