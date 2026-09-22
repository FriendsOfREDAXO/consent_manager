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

<?php
// Abschnitte ueber das Core-Fragment, damit Farben und Abstaende dem Backend-Theme folgen.
$section = static function (string $title, string $body, bool $collapse = false, bool $collapsed = true): string {
    $fragment = new rex_fragment();
    $fragment->setVar('title', $title, false);
    $fragment->setVar('body', $body, false);
    if ($collapse) {
        $fragment->setVar('collapse', true);
        $fragment->setVar('collapsed', $collapsed);
    }
    return $fragment->parse('core/page/section.php');
};

$out = '';

if (!$autoBlockingEnabled) {
    $out .= rex_view::warning(
        '<strong>' . $addon->i18n('consent_manager_editorial_autoblock_warning_title') . '</strong><br>'
        . $addon->i18n('consent_manager_editorial_autoblock_warning_intro') . ' '
        . ($hasConfigPermission
            ? rex_i18n::rawMsg('consent_manager_editorial_autoblock_warning_admin', rex_url::backendPage('consent_manager/config'))
            : rex_i18n::rawMsg('consent_manager_editorial_autoblock_warning_user')),
    );
}

if ('' !== $adminInfo) {
    $out .= rex_view::info('<strong>' . $addon->i18n('consent_manager_editorial_admin_info_title') . '</strong><br>' . $adminInfo);
}

// Einstieg: Zweck, Hinweis, Hauptaktion
$intro = '<p>' . rex_i18n::rawMsg('consent_manager_editorial_welcome_intro') . '</p>'
    . '<p><strong>' . $addon->i18n('consent_manager_editorial_important_title') . '</strong> '
    . rex_i18n::rawMsg('consent_manager_editorial_important_text') . '</p>'
    . '<p class="help-block">Richtige Verwendung: YouTube/Maps/Calendly im Content.</p>'
    . '<p><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#auto-blocking-assistant-modal">'
    . '<i class="rex-icon fa-magic"></i> ' . $addon->i18n('consent_manager_editorial_open_assistant') . '</button></p>';
$out .= $section($addon->i18n('consent_manager_editorial_welcome_title'), $intro);

// Anleitung
$howto = '<ol>';
foreach ([1, 2, 3, 4] as $step) {
    $howto .= '<li><strong>' . $addon->i18n('consent_manager_editorial_step' . $step . '_title') . '</strong><br>' . $addon->i18n('consent_manager_editorial_step' . $step . '_desc');
    if (4 === $step) {
        $howto .= '<br><em>' . $addon->i18n('consent_manager_editorial_step4_how_title') . '</em><ul>'
            . '<li>' . rex_i18n::rawMsg('consent_manager_editorial_step4_how_cke5') . '</li>'
            . '<li>' . rex_i18n::rawMsg('consent_manager_editorial_step4_how_other') . '</li></ul>';
    }
    $howto .= '</li>';
}
$howto .= '</ol>';
$out .= $section($addon->i18n('consent_manager_editorial_howto_title'), $howto, true);

// Snippets links, Hinweise rechts
$snippets = '<div id="snippets-container">'
    . '<p class="help-block" id="snippets-empty-state"><i class="rex-icon fa-info-circle"></i> ' . $addon->i18n('consent_manager_editorial_snippets_empty') . '</p>'
    . '<div id="snippets-list" hidden></div></div>';

$service = '<p>' . $addon->i18n('consent_manager_editorial_missing_service_desc') . '</p>';
$service .= $hasIssueTracker
    ? '<a href="' . rex_url::backendPage('issue_tracker/issues', ['func' => 'add']) . '" class="btn btn-default"><i class="rex-icon fa-plus-circle"></i> ' . $addon->i18n('consent_manager_editorial_request_service') . '</a>'
    : '<p class="help-block"><i class="rex-icon fa-envelope"></i> ' . rex_i18n::rawMsg('consent_manager_editorial_contact_admin_desc') . '</p>';

$privacy = '<p><strong>' . $addon->i18n('consent_manager_editorial_privacy_intro') . '</strong></p><ul>'
    . '<li>' . $addon->i18n('consent_manager_editorial_privacy_what_service') . '</li>'
    . '<li>' . $addon->i18n('consent_manager_editorial_privacy_what_data') . '</li>'
    . '<li>' . $addon->i18n('consent_manager_editorial_privacy_what_provider') . '</li></ul>';
if ($hasIssueTracker) {
    $privacy .= '<a href="' . rex_url::backendPage('issue_tracker/issues', ['func' => 'add']) . '" class="btn btn-default"><i class="rex-icon fa-pencil"></i> ' . $addon->i18n('consent_manager_editorial_privacy_issue_title') . '</a>';
}

$out .= '<div class="row"><div class="col-md-8">'
    . $section($addon->i18n('consent_manager_editorial_snippets_title'), $snippets)
    . '</div><div class="col-md-4">'
    . $section($addon->i18n('consent_manager_editorial_missing_service_title'), $service, true)
    . $section($addon->i18n('consent_manager_editorial_privacy_title'), $privacy, true)
    . '</div></div>';

// Beispiele
$examples = '<div class="row"><div class="col-md-6">'
    . '<h4>' . $addon->i18n('consent_manager_editorial_example_youtube') . '</h4>'
    . '<p>' . $addon->i18n('consent_manager_editorial_example_youtube_desc') . '</p>'
    . '<pre><code>' . rex_escape('<iframe src="https://www.youtube.com/embed/VIDEO_ID"
        width="560" height="315"
        data-consent-block="true"
        data-consent-service="youtube"></iframe>') . '</code></pre>'
    . '</div><div class="col-md-6">'
    . '<h4>' . $addon->i18n('consent_manager_editorial_example_custom') . '</h4>'
    . '<p>' . $addon->i18n('consent_manager_editorial_example_custom_desc') . '</p>'
    . '<pre><code>' . rex_escape('<script src="https://example.com/widget.js"
        data-consent-block="true"
        data-consent-service="example"
        data-consent-text="Ihr Text"></script>') . '</code></pre>'
    . '</div></div>';
$out .= $section($addon->i18n('consent_manager_editorial_examples_title'), $examples, true);

echo $out;
?>

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
                        <p class="help-block"><?= $addon->i18n('consent_manager_auto_blocking_assistant_input_help') ?></p>
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
                        <div class="btn-toolbar">
                            <button type="button" class="btn btn-default btn-sm" id="copy_code">
                                <i class="rex-icon fa-clipboard"></i> <?= $addon->i18n('consent_manager_auto_blocking_assistant_copy') ?>
                            </button>
                            <button type="button" class="btn btn-default btn-sm" id="save_snippet">
                                <i class="rex-icon fa-bookmark"></i> <?= $addon->i18n('consent_manager_editorial_snippets_save') ?>
                            </button>
                            <span id="copy_success" class="text-success" style="display: none; margin-left: 10px;">
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
    var snippetManager = {
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
                                    '<button class="btn btn-default btn-sm load-snippet" data-snippet-id="' + snippet.id + '">' +
                                        '<i class="rex-icon fa-download"></i> <?= $addon->i18n('consent_manager_editorial_snippets_load') ?>' +
                                    '</button>' +
                                    '<button class="btn btn-delete btn-sm delete-snippet" data-snippet-id="' + snippet.id + '">' +
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
        setTimeout(function() {
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
        var $elements = $temp.children();
        
        if ($elements.length === 0) {
            alert('Ungültiger HTML-Code!');
            return;
        }
        
        // Alle Elemente bearbeiten (script, iframe, etc.)
        $elements.each(function() {
            var $element = $(this);
            
            // Basis-Attribute
            $element.attr('data-consent-block', 'true');
            $element.attr('data-consent-service', serviceKey);
            
            // Optionale Attribute nur beim ersten Element
            if ($element.is($elements.first())) {
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
            }
        });
        
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
