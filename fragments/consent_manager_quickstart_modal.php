<?php
/**
 * Fragment: Consent Manager Quickstart Modal
 * Zeigt das 7-stufige Schnellstart-Setup Modal an
 */

$addon = rex_addon::get('consent_manager');
?>

<style>
.quickstart-timeline {
    position: relative;
    margin: 30px 0;
    padding: 0;
}

.quickstart-timeline::after {
    content: '';
    position: absolute;
    left: 30px;
    top: 0;
    height: 100%;
    width: 2px;
    background: linear-gradient(to bottom, #337ab7, #5bc0de, #5cb85c);
}

.quickstart-step {
    position: relative;
    margin-bottom: 30px;
    padding-left: 80px;
}

.quickstart-step:last-child {
    margin-bottom: 0;
}

.quickstart-step-icon {
    position: absolute;
    left: 18px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    z-index: 2;
    background-color: #337ab7;
}

.quickstart-step-icon.setup { background-color: #337ab7; }
.quickstart-step-icon.recommended { background-color: #5cb85c; }
.quickstart-step-icon.required { background-color: #f0ad4e; }
.quickstart-step-icon.optional { background-color: #5bc0de; }
.quickstart-step-icon.final { background-color: #5cb85c; }

.quickstart-step-content {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.quickstart-step-title {
    margin-top: 0;
    margin-bottom: 10px;
    color: #337ab7;
    font-size: 16px;
    font-weight: 600;
}

.quickstart-step-desc {
    margin-bottom: 10px;
    color: #666;
    line-height: 1.5;
}

.quickstart-setup-options {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 15px;
}

.quickstart-setup-option {
    flex: 1;
    min-width: 200px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 15px;
    transition: all 0.3s ease;
}

.quickstart-setup-option:hover {
    border-color: #337ab7;
    box-shadow: 0 2px 8px rgba(51, 122, 183, 0.15);
    transform: translateY(-2px);
}

.quickstart-setup-option h6 {
    margin: 0 0 8px 0;
    color: #337ab7;
    font-size: 14px;
    font-weight: 600;
}

.quickstart-setup-option p {
    margin: 0;
    color: #666;
    font-size: 13px;
}

.quickstart-code-block {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 12px;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    margin: 10px 0;
    color: #333;
    white-space: pre-wrap;
}

.quickstart-welcome {
    text-align: center;
    padding: 30px 20px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 8px;
    margin-bottom: 30px;
    border: 1px solid #e9ecef;
}

.quickstart-welcome h4 {
    margin: 0 0 15px 0;
    color: #337ab7;
    font-size: 24px;
    font-weight: 300;
}

.quickstart-info-box {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-left: 4px solid #ffc107;
    padding: 12px;
    border-radius: 4px;
    margin-bottom: 15px;
    color: #856404;
}

.quickstart-info-box strong {
    color: #856404;
}

/* Dark Theme Support - Explicit dark theme */
body.rex-theme-dark .quickstart-step-content {
    background: #2c3e50;
    border-color: #34495e;
    color: rgba(255, 255, 255, 0.75);
}

body.rex-theme-dark .quickstart-step-title {
    color: #409be4;
}

body.rex-theme-dark .quickstart-step-desc {
    color: rgba(255, 255, 255, 0.65);
}

body.rex-theme-dark .quickstart-setup-option {
    background: #34495e;
    border-color: #4a5f7a;
    color: rgba(255, 255, 255, 0.75);
}

body.rex-theme-dark .quickstart-setup-option:hover {
    border-color: #409be4;
    box-shadow: 0 2px 8px rgba(64, 155, 228, 0.2);
}

body.rex-theme-dark .quickstart-setup-option h6 {
    color: #409be4;
}

body.rex-theme-dark .quickstart-setup-option p {
    color: rgba(255, 255, 255, 0.65);
}

body.rex-theme-dark .quickstart-code-block {
    background: #34495e;
    border-color: #4a5f7a;
    color: rgba(255, 255, 255, 0.9);
}

body.rex-theme-dark .quickstart-welcome {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    border-color: #34495e;
    color: rgba(255, 255, 255, 0.75);
}

body.rex-theme-dark .quickstart-welcome h4 {
    color: #409be4;
}

body.rex-theme-dark .quickstart-info-box {
    background: #5d4e37;
    border-color: #8b7355;
    border-left-color: #d4a574;
    color: #f0e6d2;
}

body.rex-theme-dark .quickstart-info-box strong {
    color: #f0e6d2;
}

/* Dark Theme Support - Automatic dark theme based on system preference */
@media (prefers-color-scheme: dark) {
    body:not(.rex-theme-light) .quickstart-step-content {
        background: #2c3e50;
        border-color: #34495e;
        color: rgba(255, 255, 255, 0.75);
    }
    
    body:not(.rex-theme-light) .quickstart-step-title {
        color: #409be4;
    }
    
    body:not(.rex-theme-light) .quickstart-step-desc {
        color: rgba(255, 255, 255, 0.65);
    }
    
    body:not(.rex-theme-light) .quickstart-setup-option {
        background: #34495e;
        border-color: #4a5f7a;
        color: rgba(255, 255, 255, 0.75);
    }
    
    body:not(.rex-theme-light) .quickstart-setup-option:hover {
        border-color: #409be4;
        box-shadow: 0 2px 8px rgba(64, 155, 228, 0.2);
    }
    
    body:not(.rex-theme-light) .quickstart-setup-option h6 {
        color: #409be4;
    }
    
    body:not(.rex-theme-light) .quickstart-setup-option p {
        color: rgba(255, 255, 255, 0.65);
    }
    
    body:not(.rex-theme-light) .quickstart-code-block {
        background: #34495e;
        border-color: #4a5f7a;
        color: rgba(255, 255, 255, 0.9);
    }
    
    body:not(.rex-theme-light) .quickstart-welcome {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        border-color: #34495e;
        color: rgba(255, 255, 255, 0.75);
    }
    
    body:not(.rex-theme-light) .quickstart-welcome h4 {
        color: #409be4;
    }
    
    body:not(.rex-theme-light) .quickstart-info-box {
        background: #5d4e37;
        border-color: #8b7355;
        border-left-color: #d4a574;
        color: #f0e6d2;
    }
    
    body:not(.rex-theme-light) .quickstart-info-box strong {
        color: #f0e6d2;
    }
}

@media (max-width: 768px) {
    .quickstart-timeline::after {
        left: 15px;
    }
    .quickstart-step {
        padding-left: 50px;
    }
    .quickstart-step-icon {
        left: 3px;
        width: 20px;
        height: 20px;
        font-size: 10px;
    }
    .quickstart-setup-options {
        flex-direction: column;
    }
    .quickstart-setup-option {
        min-width: auto;
    }
}
</style>

<!-- Schnellstart Modal -->
<div class="modal fade" id="quickstart-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="rex-icon fa-rocket"></i> <?= $addon->i18n('consent_manager_quickstart_title') ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="quickstart-welcome">
                    <h4><i class="fa fa-rocket"></i> <?= $addon->i18n('consent_manager_quickstart_welcome') ?></h4>
                    <p>Folgen Sie dieser Timeline für eine perfekte Einrichtung</p>
                </div>

                <div class="quickstart-timeline">
                    <!-- Step 1: Import Setup -->
                    <div class="quickstart-step">
                        <div class="quickstart-step-icon recommended">1</div>
                        <div class="quickstart-step-content">
                            <div class="quickstart-step-title">
                                <?= $addon->i18n('consent_manager_quickstart_step_import') ?>
                                <span class="label label-success" style="font-size: 11px; margin-left: 8px;">Empfohlen</span>
                            </div>
                            <div class="quickstart-step-desc">
                                <?= $addon->i18n('consent_manager_quickstart_step_import_desc') ?>
                            </div>
                            
                            <div class="quickstart-info-box">
                                <strong>Nach dem Import sind grundlegende Konfigurationen vorhanden. Sie müssen noch: Domain anpassen, Template-Code einbinden und Gruppen den Domains zuordnen.</strong>
                            </div>
                            
                            <div class="quickstart-setup-options">
                                <div class="quickstart-setup-option">
                                    <h6><?= $addon->i18n('consent_manager_setup_minimal_title') ?></h6>
                                    <p><?= $addon->i18n('consent_manager_setup_minimal_desc') ?></p>
                                    <a href="<?= rex_url::currentBackendPage(['func' => 'setup_minimal']) ?>" 
                                       class="btn btn-primary btn-sm" 
                                       onclick="return confirm('<?= $addon->i18n('consent_manager_setup_minimal_confirm') ?>')">
                                        <i class="rex-icon fa-download"></i> <?= $addon->i18n('consent_manager_setup_minimal_button') ?>
                                    </a>
                                </div>
                                <div class="quickstart-setup-option">
                                    <h6><?= $addon->i18n('consent_manager_setup_standard_title') ?></h6>
                                    <p><?= $addon->i18n('consent_manager_setup_standard_desc') ?></p>
                                    <a href="<?= rex_url::currentBackendPage(['func' => 'setup_standard']) ?>" 
                                       class="btn btn-success btn-sm" 
                                       onclick="return confirm('<?= $addon->i18n('consent_manager_setup_standard_confirm') ?>')">
                                        <i class="rex-icon fa-download"></i> <?= $addon->i18n('consent_manager_setup_standard_button') ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="text-align: center; margin: 30px 0; color: #666; font-style: italic;">
                        — oder konfigurieren Sie manuell —
                    </div>

                    <!-- Step 2: Domain Configuration -->
                    <div class="quickstart-step">
                        <div class="quickstart-step-icon required">2</div>
                        <div class="quickstart-step-content">
                            <div class="quickstart-step-title">
                                <?= $addon->i18n('consent_manager_quickstart_step_domain') ?>
                                <span class="label label-warning" style="font-size: 11px; margin-left: 8px;">Erforderlich</span>
                            </div>
                            <div class="quickstart-step-desc">
                                <?= $addon->i18n('consent_manager_quickstart_step_domain_desc') ?>
                            </div>
                            <a href="<?= rex_url::currentBackendPage(['page' => 'consent_manager/domain']) ?>" class="btn btn-default btn-sm">
                                <i class="rex-icon fa-globe"></i> <?= $addon->i18n('consent_manager_quickstart_btn_domain') ?>
                            </a>
                        </div>
                    </div>

                    <!-- Step 3: Cookie Groups -->
                    <div class="quickstart-step">
                        <div class="quickstart-step-icon optional">3</div>
                        <div class="quickstart-step-content">
                            <div class="quickstart-step-title">
                                <?= $addon->i18n('consent_manager_quickstart_step_cookiegroups') ?>
                                <span class="text-muted" style="font-size: 11px; margin-left: 8px;">(nach Import)</span>
                            </div>
                            <div class="quickstart-step-desc">
                                <?= $addon->i18n('consent_manager_quickstart_step_cookiegroups_desc') ?>
                            </div>
                            <a href="<?= rex_url::currentBackendPage(['page' => 'consent_manager/cookiegroup']) ?>" class="btn btn-default btn-sm">
                                <i class="rex-icon fa-tags"></i> <?= $addon->i18n('consent_manager_quickstart_btn_cookiegroups') ?>
                            </a>
                        </div>
                    </div>

                    <!-- Step 4: Services/Cookies -->
                    <div class="quickstart-step">
                        <div class="quickstart-step-icon optional">4</div>
                        <div class="quickstart-step-content">
                            <div class="quickstart-step-title">
                                <?= $addon->i18n('consent_manager_quickstart_step_services') ?>
                                <span class="text-muted" style="font-size: 11px; margin-left: 8px;">(nach Import)</span>
                            </div>
                            <div class="quickstart-step-desc">
                                <?= $addon->i18n('consent_manager_quickstart_step_services_desc') ?>
                            </div>
                            <a href="<?= rex_url::currentBackendPage(['page' => 'consent_manager/cookie']) ?>" class="btn btn-default btn-sm">
                                <i class="rex-icon fa-cog"></i> <?= $addon->i18n('consent_manager_quickstart_btn_services') ?>
                            </a>
                        </div>
                    </div>

                    <!-- Step 5: Texts -->
                    <div class="quickstart-step">
                        <div class="quickstart-step-icon optional">5</div>
                        <div class="quickstart-step-content">
                            <div class="quickstart-step-title">
                                <?= $addon->i18n('consent_manager_quickstart_step_texts') ?>
                                <span class="text-muted" style="font-size: 11px; margin-left: 8px;">(nach Import)</span>
                            </div>
                            <div class="quickstart-step-desc">
                                <?= $addon->i18n('consent_manager_quickstart_step_texts_desc') ?>
                            </div>
                            <a href="<?= rex_url::currentBackendPage(['page' => 'consent_manager/text']) ?>" class="btn btn-default btn-sm">
                                <i class="rex-icon fa-edit"></i> <?= $addon->i18n('consent_manager_quickstart_btn_texts') ?>
                            </a>
                        </div>
                    </div>

                    <!-- Step 6: Theme -->
                    <div class="quickstart-step">
                        <div class="quickstart-step-icon required">6</div>
                        <div class="quickstart-step-content">
                            <div class="quickstart-step-title">
                                <?= $addon->i18n('consent_manager_quickstart_step_theme') ?>
                                <span class="label label-warning" style="font-size: 11px; margin-left: 8px;">Erforderlich</span>
                            </div>
                            <div class="quickstart-step-desc">
                                <?= $addon->i18n('consent_manager_quickstart_step_theme_desc') ?>
                            </div>
                            <a href="<?= rex_url::currentBackendPage(['page' => 'consent_manager/theme']) ?>" class="btn btn-default btn-sm">
                                <i class="rex-icon fa-paint-brush"></i> <?= $addon->i18n('consent_manager_quickstart_btn_theme') ?>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Step 7: Template Integration -->
                    <div class="quickstart-step">
                        <div class="quickstart-step-icon final">7</div>
                        <div class="quickstart-step-content">
                            <div class="quickstart-step-title">
                                <?= $addon->i18n('consent_manager_quickstart_step_template') ?>
                                <span class="label label-success" style="font-size: 11px; margin-left: 8px;">Finale</span>
                            </div>
                            <div class="quickstart-step-desc">
                                <strong><?= $addon->i18n('consent_manager_quickstart_step_template_desc') ?></strong>
                            </div>
                            
                            <div>
                                <h6><?= $addon->i18n('consent_manager_quickstart_template_code_label') ?></h6>
                                <div class="quickstart-code-block">&lt;?php echo REX_CONSENT_MANAGER[]; ?&gt;</div>
                                <p class="help-block">
                                    <i class="fa fa-info-circle"></i> <?= $addon->i18n('consent_manager_quickstart_template_code_info') ?>
                                </p>
                            </div>
                            
                            <div style="margin-top: 20px;">
                                <h6><?= $addon->i18n('consent_manager_privacy_link_label') ?></h6>
                                <p class="help-block"><?= $addon->i18n('consent_manager_privacy_link_desc') ?></p>
                                <div class="quickstart-code-block">&lt;a href="#" class="consent_manager-show-box"&gt;<?= $addon->i18n('consent_manager_quickstart_privacy_settings_link') ?>&lt;/a&gt;</div>
                            </div>
                            
                            <div class="quickstart-final-success" style="margin-top: 20px;">
                                <p style="margin: 0; font-weight: 600;">
                                    <i class="fa fa-check-circle"></i> <?= $addon->i18n('consent_manager_quickstart_template_final_message') ?>
                                </p>
                                <p style="margin: 10px 0 0 0; font-size: 13px;">
                                    <i class="fa fa-cog"></i> <?= $addon->i18n('consent_manager_quickstart_privacy_settings_info') ?> 
                                    <a class="consent_manager-show-box" style="color: white; text-decoration: underline;"><?= $addon->i18n('consent_manager_quickstart_privacy_settings_link') ?></a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= $addon->i18n('consent_manager_welcome_modal_button_close') ?></button>
            </div>
        </div>
    </div>
</div>
