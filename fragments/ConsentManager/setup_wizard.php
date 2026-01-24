<?php
/**
 * Fragment: Setup Wizard mit SSE
 * Reaktiver Setup-Assistent für erste Einrichtung
 */

/** @var rex_fragment $this */

$addon = rex_addon::get('consent_manager');

// YRewrite Domains laden falls verfügbar
$yrewriteDomains = [];
if (rex_addon::exists('yrewrite') && rex_addon::get('yrewrite')->isAvailable()) {
    foreach (rex_yrewrite::getDomains() as $domain) {
        $cleanDomain = preg_replace('#^https?://#i', '', $domain->getUrl());
        $cleanDomain = rtrim($cleanDomain, '/');
        $yrewriteDomains[] = $cleanDomain;
    }
}

// Verfügbare Themes laden
$themes = \FriendsOfRedaxo\ConsentManager\Theme::getAll();

?>

<div class="modal fade" id="setup-wizard-modal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="rex-icon fa-magic"></i> <?= rex_i18n::msg('consent_manager_wizard_title') ?>
                </h4>
            </div>
            <div class="modal-body">
                
                <!-- Willkommens-Screen -->
                <div id="wizard-welcome">
                    <div style="text-align: center; padding: 30px 20px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 8px; margin-bottom: 30px;">
                        <div style="font-size: 48px; margin-bottom: 15px;">🚀</div>
                        <h4 style="margin: 0 0 15px 0; color: #337ab7;"><?= rex_i18n::msg('consent_manager_wizard_welcome') ?></h4>
                        <p style="margin: 0; color: #666;"><?= rex_i18n::msg('consent_manager_wizard_intro') ?></p>
                    </div>

                    <form id="wizard-form">
                        <!-- Domain Eingabe -->
                        <div class="form-group">
                            <label class="control-label">
                                <i class="rex-icon fa-globe"></i> <?= rex_i18n::msg('consent_manager_wizard_domain') ?>
                            </label>
                            
                            <?php if (count($yrewriteDomains) > 0): ?>
                            <!-- YRewrite Domain Auswahl -->
                            <select id="wizard-domain-select" class="form-control">
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

                        <!-- Theme Auswahl -->
                        <?php if (count($themes) > 0): ?>
                        <div class="form-group">
                            <label class="control-label">
                                <i class="rex-icon fa-paint-brush"></i> <?= rex_i18n::msg('consent_manager_wizard_theme') ?>
                            </label>
                            <select id="wizard-theme" name="theme_uid" class="form-control">
                                <?php foreach ($themes as $theme): ?>
                                <option value="<?= rex_escape($theme['uid']) ?>"><?= rex_escape($theme['name']) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <?php endif ?>

                        <!-- Auto-Inject -->
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="wizard-auto-inject" name="auto_inject" value="1" checked>
                                    <strong><?= rex_i18n::msg('consent_manager_wizard_auto_inject') ?></strong><br>
                                    <small class="text-muted"><?= rex_i18n::msg('consent_manager_wizard_auto_inject_hint') ?></small>
                                </label>
                            </div>
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
                    <div style="text-align: center; padding: 40px 20px;">
                        <div style="font-size: 64px; color: #5cb85c; margin-bottom: 20px;">✓</div>
                        <h4 style="color: #5cb85c; margin-bottom: 15px;"><?= rex_i18n::msg('consent_manager_wizard_complete_title') ?></h4>
                        <p style="color: #666; margin-bottom: 30px;" id="wizard-success-message"></p>
                        
                        <div class="well" style="text-align: left;">
                            <h5><?= rex_i18n::msg('consent_manager_wizard_next_steps') ?>:</h5>
                            <ul style="margin: 15px 0;">
                                <li><?= rex_i18n::msg('consent_manager_wizard_step_customize') ?></li>
                                <li><?= rex_i18n::msg('consent_manager_wizard_step_services') ?></li>
                                <li><?= rex_i18n::msg('consent_manager_wizard_step_test') ?></li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="wizard-btn-start" class="btn btn-primary">
                    <i class="rex-icon fa-magic"></i> <?= rex_i18n::msg('consent_manager_wizard_start') ?>
                </button>
                <button type="button" id="wizard-btn-close" class="btn btn-default" data-dismiss="modal" style="display:none;">
                    <?= rex_i18n::msg('consent_manager_wizard_close') ?>
                </button>
                <a href="<?= rex_url::backendPage('consent_manager/domain') ?>" id="wizard-btn-config" class="btn btn-success" style="display:none;">
                    <i class="rex-icon fa-cog"></i> <?= rex_i18n::msg('consent_manager_wizard_to_config') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= rex_response::getNonce() ?>">
jQuery(function($) {
    var eventSource = null;
    
    // YRewrite Select → Input synchronisieren
    <?php if (count($yrewriteDomains) > 0): ?>
    $('#wizard-domain-select').on('change', function() {
        $('#wizard-domain-input').val($(this).val());
    });
    <?php endif ?>
    
    // Start Button
    $('#wizard-btn-start').on('click', function() {
        var domain = $('#wizard-domain-input').val().trim();
        
        if (domain === '') {
            alert('<?= rex_i18n::msg('consent_manager_wizard_error_domain_required') ?>');
            return;
        }
        
        startWizard(domain);
    });
    
    function startWizard(domain) {
        // UI umschalten
        $('#wizard-welcome').hide();
        $('#wizard-progress').show();
        $('#wizard-btn-start').hide();
        
        var setupType = $('input[name="setup_type"]:checked').val();
        var themeUid = $('#wizard-theme').val() || '';
        var autoInject = $('#wizard-auto-inject').is(':checked');
        
        // SSE Connection aufbauen
        var url = '<?= rex_url::backendController() ?>?rex-api-call=consent_manager_setup_wizard&' +
                  'domain=' + encodeURIComponent(domain) +
                  '&setup_type=' + encodeURIComponent(setupType) +
                  '&theme_uid=' + encodeURIComponent(themeUid) +
                  '&auto_inject=' + (autoInject ? '1' : '0');
        
        eventSource = new EventSource(url);
        
        // Init Event
        eventSource.addEventListener('init', function(e) {
            var data = JSON.parse(e.data);
            logEvent('✓ Verbindung hergestellt', 'success');
            logEvent('→ Domain: ' + data.domain, 'info');
        });
        
        // Progress Event
        eventSource.addEventListener('progress', function(e) {
            var data = JSON.parse(e.data);
            updateProgress(data.percent, data.message);
            logEvent('⏳ ' + data.message, 'info');
        });
        
        // Domain Created Event
        eventSource.addEventListener('domain_created', function(e) {
            var data = JSON.parse(e.data);
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
            $('#wizard-btn-close').show();
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
        $('#wizard-btn-close').show();
        $('#wizard-btn-config').show();
        
        $('#wizard-status').removeClass('alert-info').addClass('alert-success');
    }
});
</script>
