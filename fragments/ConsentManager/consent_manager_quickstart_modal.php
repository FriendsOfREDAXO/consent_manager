<?php
/**
 * Fragment: Consent Manager Quickstart Modal
 * Zeigt das 7-stufige Schnellstart-Setup Modal an
 *    <div class="quickstart-step-title">
 *      <?= $addon->i18n('consent_manager_quickstart_step_configuration') ?>
 *      <span class="text-muted" style="font-size: 11px; margin-left: 8px;"><?= $addon->i18n('consent_manager_quickstart_status_after_import') ?></span>
 *    </div>
 *    <div class="quickstart-step-title">
 *      <?= $addon->i18n('consent_manager_quickstart_step_providers') ?>
 *      <span class="text-muted" style="font-size: 11px; margin-left: 8px;"><?= $addon->i18n('consent_manager_quickstart_status_after_import') ?></span>
 *    </div>
 * wird über boot.php geladen.
 */

$addon = rex_addon::get('consent_manager');
?>

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
                    <p><?= $addon->i18n('consent_manager_quickstart_timeline_intro') ?></p>
                </div>

                <div class="quickstart-timeline">
                    <!-- Step 1: Import Setup -->
                    <div class="quickstart-step">
                        <div class="quickstart-step-icon recommended">1</div>
                        <div class="quickstart-step-content">
                            <div class="quickstart-step-title">
                                <?= $addon->i18n('consent_manager_quickstart_step_import') ?>
                                <span class="label label-success" style="font-size: 11px; margin-left: 8px;"><?= $addon->i18n('consent_manager_quickstart_recommended') ?></span>
                            </div>
                            <div class="quickstart-step-desc">
                                <?= $addon->i18n('consent_manager_quickstart_step_import_desc') ?>
                            </div>
                            
                            <div class="quickstart-info-box">
                                <strong><?= $addon->i18n('consent_manager_quickstart_import_info') ?></strong>
                            </div>
                            
                            <div class="quickstart-setup-options">
                                <div class="quickstart-setup-option">
                                    <h6><?= $addon->i18n('consent_manager_setup_standard_title') ?></h6>
                                    <p><?= $addon->i18n('consent_manager_setup_standard_desc') ?></p>
                                    <a href="<?= rex_url::currentBackendPage(['func' => 'setup_standard']) ?>" 
                                       class="btn btn-success btn-sm" 
                                       onclick="return confirm('<?= $addon->i18n('consent_manager_setup_standard_confirm') ?>')">
                                        <i class="rex-icon fa-download"></i> <?= $addon->i18n('consent_manager_setup_standard_button') ?>
                                    </a>
                                </div>
                                <div class="quickstart-setup-option">
                                    <h6><?= $addon->i18n('consent_manager_setup_minimal_title') ?></h6>
                                    <p><?= $addon->i18n('consent_manager_setup_minimal_desc') ?> <strong><?= $addon->i18n('consent_manager_setup_minimal_expert') ?></strong></p>
                                    <a href="<?= rex_url::currentBackendPage(['func' => 'setup_minimal']) ?>" 
                                       class="btn btn-primary btn-sm" 
                                       onclick="return confirm('<?= $addon->i18n('consent_manager_setup_minimal_confirm') ?>')">
                                        <i class="rex-icon fa-download"></i> <?= $addon->i18n('consent_manager_setup_minimal_button') ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="text-align: center; margin: 30px 0; color: #666; font-style: italic;">
                        <?= $addon->i18n('consent_manager_quickstart_manual_alternative') ?>
                    </div>

                    <!-- Step 2: Domain Configuration -->
                    <div class="quickstart-step">
                        <div class="quickstart-step-icon required">2</div>
                        <div class="quickstart-step-content">
                            <div class="quickstart-step-title">
                                <?= $addon->i18n('consent_manager_quickstart_step_domain') ?>
                                <span class="label label-warning" style="font-size: 11px; margin-left: 8px;"><?= $addon->i18n('consent_manager_quickstart_status_required') ?></span>
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
                                <span class="text-muted" style="font-size: 11px; margin-left: 8px;"><?= $addon->i18n('consent_manager_quickstart_status_after_import') ?></span>
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
                                <span class="label label-warning" style="font-size: 11px; margin-left: 8px;"><?= $addon->i18n('consent_manager_quickstart_status_required') ?></span>
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
                                <span class="label label-success" style="font-size: 11px; margin-left: 8px;"><?= $addon->i18n('consent_manager_quickstart_status_final') ?></span>
                            </div>
                            <div class="quickstart-step-desc">
                                <strong><?= $addon->i18n('consent_manager_quickstart_step_template_desc') ?></strong>
                            </div>
                            
                            <div>
                                <h6><?= $addon->i18n('consent_manager_quickstart_template_code_label') ?></h6>
                                <div style="display: flex; align-items: flex-start; gap: 8px;">
                                    <div class="quickstart-code-block" id="template-code-block" style="flex: 1; margin: 0;">&lt;?php echo REX_CONSENT_MANAGER[]; ?&gt;</div>
                                    <clipboard-copy for="template-code-block" class="btn btn-xs btn-default" style="flex-shrink: 0; margin-top: 12px;" title="<?= $addon->i18n('consent_manager_quickstart_copy_title') ?>">
                                        <i class="fa fa-copy"></i> <?= $addon->i18n('consent_manager_quickstart_copy_button') ?>
                                    </clipboard-copy>
                                </div>
                                <p class="help-block">
                                    <i class="fa fa-info-circle"></i> <?= $addon->i18n('consent_manager_quickstart_template_code_info') ?>
                                </p>
                            </div>
                            
                            <div style="margin-top: 20px;">
                                <h6><?= $addon->i18n('consent_manager_privacy_link_label') ?></h6>
                                <p class="help-block"><?= $addon->i18n('consent_manager_privacy_link_desc') ?></p>
                                <div style="display: flex; align-items: flex-start; gap: 8px;">
                                    <div class="quickstart-code-block" id="privacy-code-block" style="flex: 1; margin: 0;">&lt;a href="#" class="consent_manager-show-box"&gt;<?= $addon->i18n('consent_manager_quickstart_privacy_settings_link') ?>&lt;/a&gt;</div>
                                    <clipboard-copy for="privacy-code-block" class="btn btn-xs btn-default" style="flex-shrink: 0; margin-top: 12px;" title="<?= $addon->i18n('consent_manager_quickstart_copy_title') ?>">
                                        <i class="fa fa-copy"></i> <?= $addon->i18n('consent_manager_quickstart_copy_button') ?>
                                    </clipboard-copy>
                                </div>
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
