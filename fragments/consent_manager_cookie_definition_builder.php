<?php
/**
 * Cookie Definition Builder Fragment
 * 
 * User-friendly interface for managing cookie definitions
 * Replaces complex YAML editing with simple form fields
 */

$addon = $this->getVar('addon');
$fieldName = $this->getVar('fieldName', 'definition');
$currentValue = $this->getVar('currentValue', '');
$uniqueId = $this->getVar('uniqueId', uniqid());

// Parse existing YAML to populate form
$cookies = [];
if (!empty($currentValue)) {
    try {
        // Simple YAML parsing for cookie definitions
        $lines = explode("\n", trim($currentValue));
        $currentCookie = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '-') {
                // Save previous cookie if exists
                if (!empty($currentCookie['name'])) {
                    $cookies[] = $currentCookie;
                }
                $currentCookie = ['name' => '', 'time' => '', 'desc' => ''];
            } elseif (preg_match('/^\s*(name|time|desc):\s*["\']?(.*?)["\']?\s*$/', $line, $matches)) {
                $field = $matches[1];
                $value = trim($matches[2], '"\'');
                $currentCookie[$field] = $value;
            }
        }
        
        // Add last cookie
        if (!empty($currentCookie['name'])) {
            $cookies[] = $currentCookie;
        }
    } catch (Exception $e) {
        // Fallback: Keep original YAML in textarea
    }
}

// Ensure at least one empty cookie for new entries
if (empty($cookies)) {
    $cookies[] = ['name' => '', 'time' => '', 'desc' => ''];
}
?>

<div class="consent-manager-cookie-builder" data-unique-id="<?= $uniqueId ?>">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="rex-icon fa-cookie-bite"></i>
                <?= $addon->i18n('consent_manager_cookie_definition_builder') ?>
            </h4>
        </div>
        <div class="panel-body">
            
            <!-- Cookie Builder Interface -->
            <div class="cookie-builder-interface">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="cookieTable_<?= $uniqueId ?>">
                        <thead>
                            <tr>
                                <th style="width: 25%;">
                                    <?= $addon->i18n('consent_manager_cookie_name') ?>
                                    <small class="text-muted d-block"><?= $addon->i18n('consent_manager_cookie_name_help') ?></small>
                                </th>
                                <th style="width: 20%;">
                                    <?= $addon->i18n('consent_manager_cookie_time') ?>
                                    <small class="text-muted d-block"><?= $addon->i18n('consent_manager_cookie_time_help') ?></small>
                                </th>
                                <th style="width: 45%;">
                                    <?= $addon->i18n('consent_manager_cookie_desc') ?>
                                    <small class="text-muted d-block"><?= $addon->i18n('consent_manager_cookie_desc_help') ?></small>
                                </th>
                                <th style="width: 10%;"><?= $addon->i18n('consent_manager_cookie_actions') ?></th>
                            </tr>
                        </thead>
                        <tbody id="cookieRows_<?= $uniqueId ?>">
                            <?php foreach ($cookies as $index => $cookie): ?>
                            <tr class="cookie-row" data-index="<?= $index ?>">
                                <td>
                                    <input type="text" 
                                           class="form-control cookie-name" 
                                           value="<?= htmlspecialchars($cookie['name']) ?>" 
                                           placeholder="_ga, PHPSESSID, ..."
                                           data-field="name">
                                </td>
                                <td>
                                    <input type="text" 
                                           class="form-control cookie-time" 
                                           value="<?= htmlspecialchars($cookie['time']) ?>" 
                                           placeholder="1 Jahr, Session, 30 Tage"
                                           data-field="time">
                                </td>
                                <td>
                                    <textarea class="form-control cookie-desc" 
                                              rows="2" 
                                              placeholder="<?= htmlspecialchars($addon->i18n('consent_manager_cookie_desc_placeholder')) ?>"
                                              data-field="desc"><?= htmlspecialchars($cookie['desc']) ?></textarea>
                                </td>
                                <td>
                                    <div class="btn-group-vertical" role="group">
                                        <button type="button" class="btn btn-sm btn-success add-cookie-row" title="<?= $addon->i18n('consent_manager_cookie_add_row') ?>">
                                            <i class="rex-icon fa-plus"></i>
                                        </button>
                                        <?php if (count($cookies) > 1): ?>
                                        <button type="button" class="btn btn-sm btn-danger remove-cookie-row" title="<?= $addon->i18n('consent_manager_cookie_remove_row') ?>">
                                            <i class="rex-icon fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="row">
                    <div class="col-sm-6">
                        <button type="button" class="btn btn-primary btn-sm" id="addCookieBtn_<?= $uniqueId ?>">
                            <i class="rex-icon fa-plus"></i> <?= $addon->i18n('consent_manager_cookie_add_cookie') ?>
                        </button>
                    </div>
                    <div class="col-sm-6 text-right">
                        <button type="button" class="btn btn-default btn-sm" id="toggleYamlView_<?= $uniqueId ?>">
                            <i class="rex-icon fa-code"></i> <?= $addon->i18n('consent_manager_cookie_toggle_yaml') ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- YAML Output (Hidden textarea for form submission) -->
            <textarea name="<?= $fieldName ?>" 
                      id="yamlOutput_<?= $uniqueId ?>" 
                      class="form-control yaml-output" 
                      style="display: none;" 
                      rows="10"><?= htmlspecialchars($currentValue) ?></textarea>
            
            <!-- YAML Preview (Optional, can be toggled) -->
            <div class="yaml-preview" id="yamlPreview_<?= $uniqueId ?>" style="display: none;">
                <div class="form-group">
                    <label><?= $addon->i18n('consent_manager_cookie_yaml_preview') ?>:</label>
                    <pre class="bg-light p-3 border rounded"><code id="yamlCode_<?= $uniqueId ?>"><?= htmlspecialchars($currentValue) ?></code></pre>
                </div>
            </div>
            
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var uniqueId = '<?= $uniqueId ?>';
    var builderContainer = $('.consent-manager-cookie-builder[data-unique-id="' + uniqueId + '"]');
    
    // Add new cookie row
    function addCookieRow() {
        var table = builderContainer.find('#cookieRows_' + uniqueId);
        var newIndex = table.find('.cookie-row').length;
        var newRow = $(`
            <tr class="cookie-row" data-index="${newIndex}">
                <td>
                    <input type="text" class="form-control cookie-name" 
                           placeholder="_ga, PHPSESSID, ..." data-field="name">
                </td>
                <td>
                    <input type="text" class="form-control cookie-time" 
                           placeholder="1 Jahr, Session, 30 Tage" data-field="time">
                </td>
                <td>
                    <textarea class="form-control cookie-desc" rows="2" 
                              placeholder="<?= htmlspecialchars($addon->i18n('consent_manager_cookie_desc_placeholder')) ?>" 
                              data-field="desc"></textarea>
                </td>
                <td>
                    <div class="btn-group-vertical" role="group">
                        <button type="button" class="btn btn-sm btn-success add-cookie-row" title="<?= $addon->i18n('consent_manager_cookie_add_row') ?>">
                            <i class="rex-icon fa-plus"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger remove-cookie-row" title="<?= $addon->i18n('consent_manager_cookie_remove_row') ?>">
                            <i class="rex-icon fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `);
        
        table.append(newRow);
        updateRemoveButtonVisibility();
        generateYaml();
    }
    
    // Remove cookie row
    function removeCookieRow(row) {
        row.remove();
        updateRemoveButtonVisibility();
        generateYaml();
    }
    
    // Show/hide remove buttons based on row count
    function updateRemoveButtonVisibility() {
        var rows = builderContainer.find('.cookie-row');
        if (rows.length <= 1) {
            builderContainer.find('.remove-cookie-row').hide();
        } else {
            builderContainer.find('.remove-cookie-row').show();
        }
    }
    
    // Generate YAML from form data
    function generateYaml() {
        var cookies = [];
        builderContainer.find('.cookie-row').each(function() {
            var row = $(this);
            var name = row.find('[data-field="name"]').val().trim();
            var time = row.find('[data-field="time"]').val().trim();
            var desc = row.find('[data-field="desc"]').val().trim();
            
            // Only add cookie if name is provided
            if (name) {
                cookies.push({name: name, time: time, desc: desc});
            }
        });
        
        // Generate YAML
        var yaml = '';
        cookies.forEach(function(cookie) {
            yaml += '-\n';
            yaml += ' name: ' + cookie.name + '\n';
            yaml += ' time: "' + cookie.time + '"\n';
            yaml += ' desc: "' + cookie.desc.replace(/"/g, '\\"') + '"\n';
        });
        
        // Update hidden textarea and preview
        builderContainer.find('#yamlOutput_' + uniqueId).val(yaml);
        builderContainer.find('#yamlCode_' + uniqueId).text(yaml);
    }
    
    // Event handlers
    builderContainer.on('click', '#addCookieBtn_' + uniqueId, addCookieRow);
    builderContainer.on('click', '.add-cookie-row', addCookieRow);
    builderContainer.on('click', '.remove-cookie-row', function() {
        removeCookieRow($(this).closest('.cookie-row'));
    });
    
    // Update YAML on input change
    builderContainer.on('input', '.cookie-name, .cookie-time, .cookie-desc', generateYaml);
    
    // Toggle YAML preview
    builderContainer.on('click', '#toggleYamlView_' + uniqueId, function() {
        var preview = builderContainer.find('#yamlPreview_' + uniqueId);
        var btn = $(this);
        
        if (preview.is(':visible')) {
            preview.hide();
            btn.html('<i class="rex-icon fa-code"></i> <?= $addon->i18n('consent_manager_cookie_toggle_yaml') ?>');
        } else {
            generateYaml(); // Update preview before showing
            preview.show();
            btn.html('<i class="rex-icon fa-eye-slash"></i> <?= $addon->i18n('consent_manager_cookie_hide_yaml') ?>');
        }
    });
    
    // Initialize
    updateRemoveButtonVisibility();
    generateYaml();
});
</script>

<style>
.consent-manager-cookie-builder .table th small {
    font-weight: normal;
    font-style: italic;
    color: #777;
}

.consent-manager-cookie-builder .cookie-row:hover {
    background-color: #f9f9f9;
}

.consent-manager-cookie-builder .btn-group-vertical .btn {
    margin-bottom: 2px;
}

.consent-manager-cookie-builder .yaml-preview pre {
    max-height: 300px;
    overflow-y: auto;
    font-size: 12px;
}

.consent-manager-cookie-builder .cookie-desc {
    resize: vertical;
    min-height: 50px;
}
</style>
