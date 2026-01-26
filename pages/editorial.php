<?php

/**
 * Editorial-Seite für Redakteure
 * Zugriff nur mit consent_manager[editorial] Berechtigung
 */

$addon = rex_addon::get('consent_manager');

// Prüfe ob Issue Tracker installiert ist
$hasIssueTracker = rex_addon::exists('issue_tracker') && rex_addon::get('issue_tracker')->isAvailable();

?>

<div class="rex-addon-output">
    <!-- Willkommens-Info -->
    <div class="panel panel-info">
        <header class="panel-heading">
            <div class="panel-title">
                <i class="rex-icon fa-info-circle"></i> <?= $addon->i18n('consent_manager_editorial_welcome_title') ?>
            </div>
        </header>
        <div class="panel-body">
            <p><?= $addon->i18n('consent_manager_editorial_welcome_intro') ?></p>
            <p><strong><?= $addon->i18n('consent_manager_editorial_welcome_why_title') ?></strong></p>
            <ul>
                <li><?= rex_i18n::rawMsg('consent_manager_editorial_welcome_reason1') ?></li>
                <li><?= rex_i18n::rawMsg('consent_manager_editorial_welcome_reason2') ?></li>
                <li><?= rex_i18n::rawMsg('consent_manager_editorial_welcome_reason3') ?></li>
            </ul>
        </div>
    </div>

    <!-- Anleitung -->
    <div class="panel panel-default">
        <header class="panel-heading">
            <div class="panel-title">
                <i class="rex-icon fa-book"></i> <?= $addon->i18n('consent_manager_editorial_howto_title') ?>
            </div>
        </header>
        <div class="panel-body">
            <ol>
                <li><strong><?= $addon->i18n('consent_manager_editorial_step1_title') ?></strong><br>
                    <?= $addon->i18n('consent_manager_editorial_step1_desc') ?></li>
                <li><strong><?= $addon->i18n('consent_manager_editorial_step2_title') ?></strong><br>
                    <?= $addon->i18n('consent_manager_editorial_step2_desc') ?></li>
                <li><strong><?= $addon->i18n('consent_manager_editorial_step3_title') ?></strong><br>
                    <?= $addon->i18n('consent_manager_editorial_step3_desc') ?></li>
                <li><strong><?= $addon->i18n('consent_manager_editorial_step4_title') ?></strong><br>
                    <?= $addon->i18n('consent_manager_editorial_step4_desc') ?></li>
            </ol>

            <div class="text-center" style="margin-top: 20px;">
                <button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#auto-blocking-assistant-modal">
                    <i class="rex-icon fa-magic"></i> <?= $addon->i18n('consent_manager_editorial_open_assistant') ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Service fehlt? -->
    <div class="panel panel-<?= $hasIssueTracker ? 'success' : 'warning' ?>">
        <header class="panel-heading">
            <div class="panel-title">
                <i class="rex-icon fa-<?= $hasIssueTracker ? 'lightbulb-o' : 'exclamation-triangle' ?>"></i> 
                <?= $addon->i18n('consent_manager_editorial_missing_service_title') ?>
            </div>
        </header>
        <div class="panel-body">
            <p><?= $addon->i18n('consent_manager_editorial_missing_service_desc') ?></p>
            
            <?php if ($hasIssueTracker): ?>
                <div class="alert alert-success">
                    <p><strong><i class="rex-icon fa-check-circle"></i> <?= $addon->i18n('consent_manager_editorial_issue_tracker_available') ?></strong></p>
                    <p><?= rex_i18n::rawMsg('consent_manager_editorial_issue_tracker_desc') ?></p>
                    <a href="<?= rex_url::backendPage('issue_tracker/issues', ['func' => 'add']) ?>" class="btn btn-success">
                        <i class="rex-icon fa-plus-circle"></i> <?= $addon->i18n('consent_manager_editorial_request_service') ?>
                    </a>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <p><strong><i class="rex-icon fa-envelope"></i> <?= $addon->i18n('consent_manager_editorial_contact_admin') ?></strong></p>
                    <p><?= rex_i18n::rawMsg('consent_manager_editorial_contact_admin_desc') ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Beispiel-Showcase -->
    <div class="panel panel-default">
        <header class="panel-heading collapsed" data-toggle="collapse" data-target="#example-showcase">
            <div class="panel-title" style="cursor: pointer;">
                <i class="rex-icon fa-code"></i> <?= $addon->i18n('consent_manager_editorial_examples_title') ?>
                <span class="pull-right"><i class="rex-icon fa-chevron-down"></i></span>
            </div>
        </header>
        <div class="panel-body collapse" id="example-showcase">
            <h4><?= $addon->i18n('consent_manager_editorial_example_youtube') ?></h4>
            <p><?= $addon->i18n('consent_manager_editorial_example_youtube_desc') ?></p>
            <pre><code>&lt;iframe src="https://www.youtube.com/embed/VIDEO_ID" 
        width="560" height="315"
        data-consent-block="true"
        data-consent-service="youtube"
        data-consent-provider="YouTube"
        data-consent-privacy="https://policies.google.com/privacy"&gt;&lt;/iframe&gt;</code></pre>

            <hr>

            <h4><?= $addon->i18n('consent_manager_editorial_example_custom') ?></h4>
            <p><?= $addon->i18n('consent_manager_editorial_example_custom_desc') ?></p>
            <pre><code>&lt;script src="https://example.com/widget.js"
        data-consent-block="true"
        data-consent-service="example-widget"
        data-consent-text="Zur Nutzung dieser Funktion benötigen wir Ihre Zustimmung."&gt;&lt;/script&gt;</code></pre>
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
                        <button type="button" class="btn btn-success btn-sm" id="copy_code" style="margin-top: 10px;">
                            <i class="rex-icon fa-clipboard"></i> <?= $addon->i18n('consent_manager_auto_blocking_assistant_copy') ?>
                        </button>
                        <span id="copy_success" style="display: none; margin-left: 10px; color: #5cb85c;">
                            <i class="rex-icon fa-check"></i> <?= $addon->i18n('consent_manager_auto_blocking_assistant_copied') ?>
                        </span>
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

<script nonce="<?= rex_response::getNonce() ?>">
jQuery(function($) {
    'use strict';
    
    // Toggle custom service input
    $('#service_key').on('change', function() {
        if ($(this).val() === '__custom__') {
            $('#service_key_custom').slideDown();
        } else {
            $('#service_key_custom').slideUp();
        }
    });
    
    $('#generate_code').on('click', function() {
        var originalCode = $('#original_code').val().trim();
        var serviceKey = $('#service_key').val().trim();
        
        // Wenn "Custom" ausgewählt, nutze das Custom-Eingabefeld
        if (serviceKey === '__custom__') {
            serviceKey = $('#service_key_custom').val().trim();
        }
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
