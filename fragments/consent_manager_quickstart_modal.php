<?php
/**
 * Fragment: Consent Manager Quickstart Modal
 * Zeigt das 7-stufige Schnellstart-Setup Modal an
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
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <strong><?= $addon->i18n('consent_manager_quickstart_welcome') ?></strong>
                        </div>
                        <div class="panel-group" id="quickstart-accordion">
                            <!-- Step 1: Import Setup (recommended) -->
                            <div class="panel panel-success">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        <span class="label label-success">1</span> <?= $addon->i18n('consent_manager_quickstart_step_import') ?> <small class="text-success">(<?= $addon->i18n('consent_manager_quickstart_status_recommended') ?>)</small>
                                    </h5>
                                </div>
                                <div class="panel-body">
                                    <p><?= $addon->i18n('consent_manager_quickstart_step_import_desc') ?></p>
                                    <div class="alert alert-warning">
                                        <strong><?= $addon->i18n('consent_manager_quickstart_import_hint') ?></strong>
                                    </div>
                                    
                                    <!-- Setup Variants -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="panel panel-info">
                                                <div class="panel-heading">
                                                    <h6><?= $addon->i18n('consent_manager_setup_minimal_title') ?></h6>
                                                </div>
                                                <div class="panel-body">
                                                    <p><?= $addon->i18n('consent_manager_setup_minimal_desc') ?></p>
                                                    <div class="text-center">
                                                        <a href="<?= rex_url::currentBackendPage(['func' => 'setup_minimal']) ?>" 
                                                           class="btn btn-sm btn-primary" 
                                                           onclick="return confirm('<?= $addon->i18n('consent_manager_setup_minimal_confirm') ?>')">
                                                            <i class="rex-icon fa-download"></i> <?= $addon->i18n('consent_manager_setup_minimal_button') ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="panel panel-info">
                                                <div class="panel-heading">
                                                    <h6><?= $addon->i18n('consent_manager_setup_standard_title') ?></h6>
                                                </div>
                                                <div class="panel-body">
                                                    <p><?= $addon->i18n('consent_manager_setup_standard_desc') ?></p>
                                                    <div class="text-center">
                                                        <a href="<?= rex_url::currentBackendPage(['func' => 'setup_standard']) ?>" 
                                                           class="btn btn-sm btn-success" 
                                                           onclick="return confirm('<?= $addon->i18n('consent_manager_setup_standard_confirm') ?>')">
                                                            <i class="rex-icon fa-download"></i> <?= $addon->i18n('consent_manager_setup_standard_button') ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <strong><?= $addon->i18n('consent_manager_quickstart_manual_config') ?></strong>
                            </div>

                            <!-- Step 2: Domain Configuration -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        <span class="label label-warning">2</span> <?= $addon->i18n('consent_manager_quickstart_step_domain') ?> <small class="text-warning">(<?= $addon->i18n('consent_manager_quickstart_status_required') ?>)</small>
                                    </h5>
                                </div>
                                <div class="panel-body">
                                    <p><?= $addon->i18n('consent_manager_quickstart_step_domain_desc') ?></p>
                                    <a href="<?= rex_url::currentBackendPage(['page' => 'consent_manager/domain']) ?>" class="btn btn-sm btn-default">
                                        <i class="rex-icon fa-globe"></i> <?= $addon->i18n('consent_manager_quickstart_btn_domain') ?>
                                    </a>
                                </div>
                            </div>

                            <!-- Step 3: Cookie Groups -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        <span class="label label-default">3</span> <?= $addon->i18n('consent_manager_quickstart_step_cookiegroups') ?> <small class="text-muted">(<?= $addon->i18n('consent_manager_quickstart_status_after_import') ?>)</small>
                                    </h5>
                                </div>
                                <div class="panel-body">
                                    <p><?= $addon->i18n('consent_manager_quickstart_step_cookiegroups_desc') ?></p>
                                    <a href="<?= rex_url::currentBackendPage(['page' => 'consent_manager/cookiegroup']) ?>" class="btn btn-sm btn-default">
                                        <i class="rex-icon fa-tags"></i> <?= $addon->i18n('consent_manager_quickstart_btn_cookiegroups') ?>
                                    </a>
                                </div>
                            </div>

                            <!-- Step 4: Services/Cookies -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        <span class="label label-default">4</span> <?= $addon->i18n('consent_manager_quickstart_step_services') ?> <small class="text-muted">(<?= $addon->i18n('consent_manager_quickstart_status_after_import') ?>)</small>
                                    </h5>
                                </div>
                                <div class="panel-body">
                                    <p><?= $addon->i18n('consent_manager_quickstart_step_services_desc') ?></p>
                                    <a href="<?= rex_url::currentBackendPage(['page' => 'consent_manager/cookie']) ?>" class="btn btn-sm btn-default">
                                        <i class="rex-icon fa-cog"></i> <?= $addon->i18n('consent_manager_quickstart_btn_services') ?>
                                    </a>
                                </div>
                            </div>

                            <!-- Step 5: Texts -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        <span class="label label-default">5</span> <?= $addon->i18n('consent_manager_quickstart_step_texts') ?> <small class="text-muted">(<?= $addon->i18n('consent_manager_quickstart_status_after_import') ?>)</small>
                                    </h5>
                                </div>
                                <div class="panel-body">
                                    <p><?= $addon->i18n('consent_manager_quickstart_step_texts_desc') ?></p>
                                    <a href="<?= rex_url::currentBackendPage(['page' => 'consent_manager/text']) ?>" class="btn btn-sm btn-default">
                                        <i class="rex-icon fa-edit"></i> <?= $addon->i18n('consent_manager_quickstart_btn_texts') ?>
                                    </a>
                                </div>
                            </div>

                            <!-- Step 6: Theme -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        <span class="label label-default">6</span> <?= $addon->i18n('consent_manager_quickstart_step_theme') ?> <small class="text-warning">(<?= $addon->i18n('consent_manager_quickstart_status_required') ?>)</small>
                                    </h5>
                                </div>
                                <div class="panel-body">
                                    <p><?= $addon->i18n('consent_manager_quickstart_step_theme_desc') ?></p>
                                    <a href="<?= rex_url::currentBackendPage(['page' => 'consent_manager/theme']) ?>" class="btn btn-sm btn-default">
                                        <i class="rex-icon fa-paint-brush"></i> <?= $addon->i18n('consent_manager_quickstart_btn_theme') ?>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Step 7: Template Integration -->
                            <div class="panel panel-success">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        <span class="label label-success">7</span> <?= $addon->i18n('consent_manager_quickstart_step_template') ?> <small class="text-success">(<?= $addon->i18n('consent_manager_quickstart_status_final') ?>)</small>
                                    </h5>
                                </div>
                                <div class="panel-body">
                                    <p><strong><?= $addon->i18n('consent_manager_quickstart_step_template_desc') ?></strong></p>
                                    <div class="well well-sm">
                                        <p><strong><?= $addon->i18n('consent_manager_quickstart_template_code_label') ?></strong></p>
                                        <pre style="background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 12px;">&lt;?php echo REX_CONSENT_MANAGER[]; ?&gt;</pre>
                                        <p class="help-block" style="margin-bottom: 0;">
                                            <i class="fa fa-info-circle"></i> <?= $addon->i18n('consent_manager_quickstart_template_code_info') ?>
                                        </p>
                                    </div>
                                    <div class="well well-sm" style="margin-top: 15px;">
                                        <p><strong><?= $addon->i18n('consent_manager_privacy_link_label') ?></strong></p>
                                        <p class="help-block"><?= $addon->i18n('consent_manager_privacy_link_desc') ?></p>
                                        <pre style="background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 11px;">&lt;a href="#" class="consent_manager-show-box"&gt;<?= $addon->i18n('consent_manager_quickstart_privacy_settings_link') ?>&lt;/a&gt;</pre>
                                    </div>
                                    <div class="alert alert-success" style="margin-bottom: 0;">
                                        <i class="fa fa-check-circle"></i> <strong><?= $addon->i18n('consent_manager_quickstart_template_final_message') ?></strong>
                                        <br><br>
                                        <p style="margin-bottom: 0;">
                                            <i class="fa fa-cog"></i> <?= $addon->i18n('consent_manager_quickstart_privacy_settings_info') ?> 
                                            <a class="consent_manager-show-box"><?= $addon->i18n('consent_manager_quickstart_privacy_settings_link') ?></a>
                                        </p>
                                    </div>
                                </div>
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
