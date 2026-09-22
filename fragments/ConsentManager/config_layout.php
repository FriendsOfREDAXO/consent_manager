<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 *
 * Fragment-Schnittstelle:
 * - form: rex_form|null, optional, Default `null`
 * - csrf: rex_csrf_token|null, optional, Default `null`
 */

/** @var ?rex_form $form */
$form = $this->getVar('form');

/** @var ?rex_csrf_token $csrf */
$csrf = $this->getVar('csrf');

// Prüfen ob bereits Domains konfiguriert sind
$sql = rex_sql::factory();
$sql->setQuery('SELECT COUNT(*) as cnt FROM ' . rex::getTable('consent_manager_domain'));
$hasDomains = (int) $sql->getValue('cnt') > 0;
$allClangs = rex_clang::getAll();
$hasMultipleClangs = count($allClangs) > 1;
$defaultSourceClangId = rex_clang::getStartId();

?>

<div class="rex-addon-output">
    <?php if ($hasDomains): ?>
    <!-- Ohne Domain bietet bereits die Checkliste "Erste Schritte" den Assistenten an -->
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-md-12 text-right">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#setup-wizard-modal">
                <i class="rex-icon fa-magic"></i> Setup Wizard
            </button>
        </div>
    </div>
    <?php endif ?>

    <div class="row">
        <!-- Linke Spalte: Einstellungen (8 Spalten) -->
        <div class="col-md-8">
            <div style="margin-bottom: 20px;">
                <?php if (null !== $form): ?>
                    <?= $form->get() ?>
                <?php else: ?>
                    <p>Form konnte nicht geladen werden.</p>
                <?php endif ?>
            </div>
        </div>
        
        <!-- Rechte Spalte: Setup & Import/Export (4 Spalten) -->
        <div class="col-md-4">
            <!-- Schnellstart Panel -->
            <div class="panel panel-default" style="margin-bottom: 20px;">
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
                               class="btn btn-default btn-sm" style="width: 48%;"
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
                               class="btn btn-default btn-sm" style="width: 48%; margin-right: 2%;"
                               onclick="return confirm('<?= rex_i18n::msg('consent_manager_config_minimal_confirm') ?>')">
                                <i class="rex-icon fa-download"></i> <?= rex_i18n::msg('consent_manager_config_load_complete') ?>
                            </a>
                            <a href="<?= rex_url::currentBackendPage(['func' => 'setup_minimal_update']) ?>" 
                               class="btn btn-default btn-sm" style="width: 48%;"
                               onclick="return confirm('<?= rex_i18n::msg('consent_manager_config_minimal_update_confirm') ?>')">
                                <i class="rex-icon fa-plus"></i> <?= rex_i18n::msg('consent_manager_config_load_new_only') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($hasMultipleClangs): ?>
            <!-- Sprach-Sync -->
            <div class="panel panel-default" style="margin-bottom: 15px;">
                <header class="panel-heading">
                    <div class="panel-title">
                        <i class="rex-icon fa-language"></i> <?= rex_i18n::msg('consent_manager_sync_missing_title') ?>
                    </div>
                </header>
                <div class="panel-body">
                    <p><?= rex_i18n::msg('consent_manager_sync_missing_desc') ?></p>
                    <form action="<?= rex_url::currentBackendPage() ?>" method="post">
                        <input type="hidden" name="func" value="sync_missing_clang" />
                        <?= rex_csrf_token::factory(\FriendsOfRedaxo\ConsentManager\Config::class)->getHiddenField() ?>

                        <div class="form-group">
                            <label for="cm-sync-source-clang"><?= rex_i18n::msg('consent_manager_sync_missing_source') ?></label>
                            <select id="cm-sync-source-clang" name="source_clang_id" class="form-control">
                                <?php foreach ($allClangs as $clang): ?>
                                    <option value="<?= (int) $clang->getId() ?>"<?= (int) $clang->getId() === $defaultSourceClangId ? ' selected' : '' ?>>
                                        <?= rex_escape($clang->getName()) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><?= rex_i18n::msg('consent_manager_sync_missing_targets') ?></label>
                            <div style="max-height: 120px; overflow: auto; border: 1px solid #ddd; padding: 8px 10px; border-radius: 4px;">
                                <?php foreach ($allClangs as $clang): ?>
                                    <?php $clangId = (int) $clang->getId(); ?>
                                    <?php if ($clangId === $defaultSourceClangId) { continue; } ?>
                                    <div class="checkbox" style="margin: 0 0 6px 0;">
                                        <label>
                                            <input type="checkbox" name="target_clang_ids[]" value="<?= $clangId ?>" checked>
                                            <?= rex_escape($clang->getName()) ?>
                                        </label>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?= rex_i18n::msg('consent_manager_sync_missing_tables') ?></label>
                            <div style="border: 1px solid #ddd; padding: 8px 10px; border-radius: 4px;">
                                <div class="checkbox" style="margin: 0 0 6px 0;">
                                    <label>
                                        <input type="checkbox" name="sync_tables[]" value="<?= rex_escape(rex::getTable('consent_manager_cookiegroup')) ?>" checked>
                                        <?= rex_i18n::msg('consent_manager_cookiegroups') ?>
                                    </label>
                                </div>
                                <div class="checkbox" style="margin: 0 0 6px 0;">
                                    <label>
                                        <input type="checkbox" name="sync_tables[]" value="<?= rex_escape(rex::getTable('consent_manager_cookie')) ?>" checked>
                                        <?= rex_i18n::msg('consent_manager_cookies') ?>
                                    </label>
                                </div>
                                <div class="checkbox" style="margin: 0;">
                                    <label>
                                        <input type="checkbox" name="sync_tables[]" value="<?= rex_escape(rex::getTable('consent_manager_text')) ?>" checked>
                                        <?= rex_i18n::msg('consent_manager_text') ?>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-default btn-sm" onclick="return confirm('<?= rex_i18n::msg('consent_manager_sync_missing_confirm') ?>')">
                                <i class="rex-icon fa-random"></i> <?= rex_i18n::msg('consent_manager_sync_missing_button') ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif ?>

            <!-- Export -->
            <div class="panel panel-default" style="margin-bottom: 15px;">
                <header class="panel-heading">
                    <div class="panel-title">
                        <i class="rex-icon fa-upload"></i> <?= rex_i18n::msg('consent_manager_config_export_title') ?>
                    </div>
                </header>
                <div class="panel-body">
                    <p><?= rex_i18n::msg('consent_manager_config_export_desc') ?></p>
                    <div class="text-center">
                        <a href="<?= rex_url::currentBackendPage(['func' => 'export'] + $csrf->getUrlParams()) ?>" 
                           class="btn btn-default btn-sm">
                            <i class="rex-icon fa-download"></i> <?= rex_i18n::msg('consent_manager_config_export_button') ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- JSON Import -->
            <div class="panel panel-default">
                <header class="panel-heading">
                    <div class="panel-title">
                        <i class="rex-icon fa-file-code-o"></i> <?= rex_i18n::msg('consent_manager_config_import_title') ?>
                    </div>
                </header>
                <div class="panel-body">
                    <p><?= rex_i18n::msg('consent_manager_config_import_desc') ?></p>
                    <form action="<?= rex_url::currentBackendPage() ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="func" value="import_json" />
                        <?= rex_csrf_token::factory(\FriendsOfRedaxo\ConsentManager\Config::class)->getHiddenField() ?>
                        <div class="form-group">
                            <input type="file" class="form-control" id="import_file" name="import_file" accept=".json" required>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-default btn-sm">
                                <i class="rex-icon fa-upload"></i> <?= rex_i18n::msg('consent_manager_config_import_button') ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
