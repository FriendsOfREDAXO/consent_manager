<?php
/**
 * Fragment für Google Consent Mode v2 Helper
 * Generiert die HTML-Struktur für den Helper-Panel.
 *
 * TODO: hier die Schnittstelle beschreiben:
 * - Welche Vars werden vom Fragment erwartet
 * - Welchen Typ haben die Vars
 * - Welchen Default-Wert haben optionale Vars
 * - Welche Vars sind mandatory und was passiert wenn sie fehlen (return oder Exception)
 */

?>
<style>
.google-consent-preview-code {
    padding: 10px;
    margin: 0;
    max-height: 150px;
    overflow-y: auto;
    background-color: var(--bs-gray-100, #f8f9fa);
    border: 1px solid var(--bs-gray-300, #dee2e6);
    border-radius: 0.25rem;
    color: var(--bs-gray-900, #212529);
    font-family: 'Courier New', Courier, monospace;
    font-size: 12px;
    line-height: 1.4;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .google-consent-preview-code {
        background-color: #2d3748;
        border-color: #4a5568;
        color: #f7fafc;
    }
}

/* REDAXO Backend dark theme support */
.rex-theme-dark .google-consent-preview-code,
body.dark .google-consent-preview-code,
[data-theme="dark"] .google-consent-preview-code {
    background-color: #2d3748 !important;
    border-color: #4a5568 !important;
    color: #f7fafc !important;
}

/* Bootstrap 4/5 dark utilities */
.bg-dark .google-consent-preview-code,
.text-bg-dark .google-consent-preview-code {
    background-color: #343a40 !important;
    border-color: #495057 !important;
    color: #f8f9fa !important;
}
</style>
<div id="google-consent-helper-panel" class="panel panel-info">
    <div class="panel-heading">
        <h4 class="panel-title">
            <i class="fa fa-google"></i> <?= rex_i18n::msg('consent_manager_google_helper_title') ?>
            <button type="button" id="google-helper-toggle" class="btn btn-xs btn-default pull-right">
                <i class="fa fa-chevron-down"></i> <?= rex_i18n::msg('consent_manager_google_helper_show') ?>
            </button>
            <div class="clearfix"></div>
        </h4>
    </div>
    
    <div id="google-consent-helper-content" class="collapse">
        <div class="panel-body">
            
            <!-- Messages Container -->
            <div id="google-helper-messages"></div>
            
            <p class="help-block">
                <i class="fa fa-info-circle"></i> 
                <?= rex_i18n::msg('consent_manager_google_helper_description') ?>
            </p>
            
            <!-- Service Auswahl -->
            <div class="form-group">
                <label for="google-helper-service"><i class="fa fa-cog"></i> <?= rex_i18n::msg('consent_manager_google_helper_service_type') ?>:</label>
                <select id="google-helper-service" class="form-control">
                    <option value=""><?= rex_i18n::msg('consent_manager_google_helper_select_service') ?></option>
                    <option value="analytics"><?= rex_i18n::msg('consent_manager_google_service_analytics') ?></option>
                    <option value="google-analytics"><?= rex_i18n::msg('consent_manager_google_service_google_analytics') ?></option>
                    <option value="google-analytics-4"><?= rex_i18n::msg('consent_manager_google_service_google_analytics_4') ?></option>
                    <option value="google-tag-manager"><?= rex_i18n::msg('consent_manager_google_service_google_tag_manager') ?></option>
                    <option value="google-tag-manager-all"><?= rex_i18n::msg('consent_manager_google_service_google_tag_manager_all') ?></option>
                    <option value="matomo"><?= rex_i18n::msg('consent_manager_google_service_matomo') ?></option>
                    <option value="adwords"><?= rex_i18n::msg('consent_manager_google_service_adwords') ?></option>
                    <option value="google-ads"><?= rex_i18n::msg('consent_manager_google_service_google_ads') ?></option>
                    <option value="facebook-pixel"><?= rex_i18n::msg('consent_manager_google_service_facebook_pixel') ?></option>
                    <option value="youtube"><?= rex_i18n::msg('consent_manager_google_service_youtube') ?></option>
                    <option value="google-maps"><?= rex_i18n::msg('consent_manager_google_service_google_maps') ?></option>
                </select>
                <small class="help-block"><?= rex_i18n::msg('consent_manager_google_helper_auto_detection') ?></small>
            </div>
            
            <!-- Buttons -->
            <div class="form-group">
                <div class="btn-group btn-group-justified" role="group">
                    <div class="btn-group" role="group">
                        <button type="button" id="generate-consent-script" class="btn btn-success">
                            <i class="fa fa-check"></i> <?= rex_i18n::msg('consent_manager_google_helper_consent_script') ?>
                        </button>
                    </div>
                    <div class="btn-group" role="group">
                        <button type="button" id="generate-revoke-script" class="btn btn-warning">
                            <i class="fa fa-times"></i> <?= rex_i18n::msg('consent_manager_google_helper_revoke_script') ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Script Preview -->
            <div id="script-preview" class="well well-sm" style="display:none;">
                <div class="form-group">
                    <label><i class="fa fa-code"></i> <?= rex_i18n::msg('consent_manager_google_helper_generated_script') ?>:</label>
                    <div class="input-group">
                        <pre id="preview-content" class="google-consent-preview-code"></pre>
                        <div class="input-group-btn">
                            <button type="button" id="copy-preview-script" class="btn btn-primary" title="<?= rex_i18n::msg('consent_manager_google_helper_copy_to_clipboard') ?>">
                                <i class="fa fa-copy"></i> <?= rex_i18n::msg('consent_manager_google_helper_copy') ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
