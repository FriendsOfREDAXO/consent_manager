<?php
/**
 * Theme Editor Fragment.
 *
 * Variablen:
 * @var string $themeBase
 * @var array<string, string> $themeBases
 * @var array<string, string> $colors
 * @var rex_csrf_token $csrfToken
 */

$themeBase = $this->getVar('themeBase', 'normal');
$themeBases = $this->getVar('themeBases', []);
$colors = $this->getVar('colors', []);
$csrfToken = $this->getVar('csrfToken');
?>

<div class="consent-manager-theme-editor">
    
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="rex-icon fa-universal-access"></i> Barrierefreiheits-Hinweis</h3>
        </div>
        <div class="panel-body">
            <p>Der Theme-Editor prüft automatisch die WCAG 2.1 Kontrastanforderungen. Ein Kontrastverhältnis von mindestens <strong>4.5:1</strong> ist für normalen Text erforderlich, <strong>3:1</strong> für großen Text und UI-Komponenten.</p>
        </div>
    </div>
    
    <?php if ('fluid' === $themeBase || 'fluid_dark' === $themeBase): ?>
    <div class="panel panel-warning">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="rex-icon fa-exclamation-triangle"></i> Hinweis zur Barrierefreiheit bei Glaseffekt-Themes</h3>
        </div>
        <div class="panel-body">
            <p><strong>Achtung:</strong> Das Fluid/Glass-Theme verwendet transparente Hintergründe und Unschärfe-Effekte (backdrop-filter). Dies kann die Lesbarkeit für einige Nutzer beeinträchtigen:</p>
            <ul>
                <li><strong>Transparenz:</strong> Text über halbtransparenten Flächen kann je nach Seitenhintergrund schwer lesbar sein</li>
                <li><strong>Browser-Support:</strong> <code>backdrop-filter</code> wird nicht von allen Browsern unterstützt - ein Fallback wird automatisch generiert</li>
                <li><strong>Barrierefreiheit:</strong> Nutzer mit Sehbeeinträchtigungen bevorzugen oft undurchsichtige Hintergründe</li>
            </ul>
            <p class="text-info"><i class="rex-icon fa-info-circle"></i> <strong>Empfehlung:</strong> Setze die Hintergrund-Transparenz auf mindestens 85-90% für bessere Lesbarkeit. Das generierte Theme respektiert automatisch <code>prefers-reduced-transparency</code> und zeigt dann einen undurchsichtigen Hintergrund.</p>
        </div>
    </div>
    <?php endif ?>
    
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Theme-Basis wählen</h3>
        </div>
        <div class="panel-body">
            <?php foreach ($themeBases as $key => $label): ?>
                <a href="<?= rex_url::currentBackendPage(['theme_base' => $key]) ?>" 
                   class="btn <?= $themeBase === $key ? 'btn-primary' : 'btn-default' ?>">
                    <?= rex_escape($label) ?>
                </a>
            <?php endforeach ?>
        </div>
    </div>

    <form action="<?= rex_url::currentBackendPage(['theme_base' => $themeBase]) ?>" method="post" id="theme-editor-form">
        <?= $csrfToken->getHiddenField() ?>
        <input type="hidden" name="formsubmit" value="1">
        <input type="hidden" name="theme_base" value="<?= rex_escape($themeBase) ?>">
        
        <!-- Theme-Information -->
        <section class="rex-page-section">
            <div class="panel panel-default">
                <header class="panel-heading">
                    <div class="panel-title">Theme-Information</div>
                </header>
                <div class="panel-body">
                    <div class="form-group">
                        <label for="theme_name">Theme-Name:</label>
                        <input type="text" class="form-control" id="theme_name" name="theme_name" 
                               value="Custom <?= rex_escape($themeBases[$themeBase]) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="theme_description">Beschreibung:</label>
                        <textarea class="form-control" id="theme_description" name="theme_description" rows="2">Individuell angepasstes <?= rex_escape($themeBases[$themeBase]) ?> Theme mit eigenen Farben</textarea>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Grundfarben -->
        <section class="rex-page-section">
            <div class="panel panel-default">
                <header class="panel-heading">
                    <div class="panel-title"><i class="rex-icon fa-paint-brush"></i> Grundfarben</div>
                </header>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="background_color">Hintergrundfarbe:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control color-picker" id="background_color" name="background_color" value="<?= rex_escape($colors['background']) ?>">
                                    <span class="input-group-addon color-hex-display"><?= rex_escape($colors['background']) ?></span>
                                </div>
                            </div>
                            <?php if ('fluid' === $themeBase || 'fluid_dark' === $themeBase): ?>
                            <div class="form-group">
                                <label for="background_opacity">Hintergrund-Transparenz: <span id="background_opacity_value"><?= rex_escape($colors['background_opacity'] ?? '100') ?>%</span></label>
                                <input type="range" class="form-control" id="background_opacity" name="background_opacity" min="0" max="100" value="<?= rex_escape($colors['background_opacity'] ?? '100') ?>">
                                <small class="help-block">Für Glaseffekt empfohlen: 70-90%</small>
                            </div>
                            <?php endif ?>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="text_color">Textfarbe:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control color-picker" id="text_color" name="text_color" value="<?= rex_escape($colors['text']) ?>">
                                    <span class="input-group-addon color-hex-display"><?= rex_escape($colors['text']) ?></span>
                                    <span class="input-group-addon contrast-badge" id="text-contrast-badge">Prüfe...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="accent_color">Akzentfarbe (Rahmen, Details):</label>
                                <div class="input-group">
                                    <input type="color" class="form-control color-picker" id="accent_color" name="accent_color" value="<?= rex_escape($colors['accent']) ?>">
                                    <span class="input-group-addon color-hex-display"><?= rex_escape($colors['accent']) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title_color">Titel/Überschrift:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control color-picker" id="title_color" name="title_color" value="<?= rex_escape($colors['title'] ?? $colors['text']) ?>">
                                    <span class="input-group-addon color-hex-display"><?= rex_escape($colors['title'] ?? $colors['text']) ?></span>
                                    <span class="input-group-addon contrast-badge" id="title-contrast-badge">Prüfe...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Schriftgrößen -->
        <section class="rex-page-section">
            <div class="panel panel-default">
                <header class="panel-heading">
                    <div class="panel-title"><i class="rex-icon fa-text-height"></i> Schriftgrößen</div>
                </header>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="font_size">Allgemeine Schriftgröße: <span id="font_size_value"><?= rex_escape($colors['font_size'] ?? '') ?><?= (isset($colors['font_size']) && '' !== $colors['font_size']) ? 'px' : 'Standard' ?></span></label>
                                <input type="range" class="form-control" id="font_size" name="font_size" min="12" max="22" value="<?= rex_escape($colors['font_size'] ?? '16') ?>">
                                <small class="help-block">Basis-Schriftgröße für die Consent-Box (12-22px, Standard je nach Theme-Typ)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="button_font_size">Button-Schriftgröße: <span id="button_font_size_value"><?= rex_escape($colors['button_font_size'] ?? '15') ?>px</span></label>
                                <input type="range" class="form-control" id="button_font_size" name="button_font_size" min="12" max="20" value="<?= rex_escape($colors['button_font_size'] ?? '15') ?>">
                                <small class="help-block">Schriftgröße für die Buttons (12-20px)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Details-Bereich -->
        <section class="rex-page-section">
            <div class="panel panel-default">
                <header class="panel-heading">
                    <div class="panel-title"><i class="rex-icon fa-list-alt"></i> Details-Bereich (aufgeklappte Ansicht)</div>
                </header>
                <div class="panel-body">
                    <!-- Details Toggle Button -->
                    <h5><i class="rex-icon fa-toggle-on"></i> "Details" Button</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="details_link">Button-Farbe:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control color-picker" id="details_link" name="details_link" value="<?= rex_escape($colors['details_link'] ?? '#0066cc') ?>">
                                    <span class="input-group-addon color-hex-display"><?= rex_escape($colors['details_link'] ?? '#0066cc') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="details_link_hover">Button-Hover:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control color-picker" id="details_link_hover" name="details_link_hover" value="<?= rex_escape($colors['details_link_hover'] ?? '#004499') ?>">
                                    <span class="input-group-addon color-hex-display"><?= rex_escape($colors['details_link_hover'] ?? '#004499') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="details_toggle_border">Rahmenfarbe:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control color-picker" id="details_toggle_border" name="details_toggle_border" value="<?= rex_escape($colors['details_toggle_border'] ?? $colors['details_link'] ?? '#0066cc') ?>">
                                    <span class="input-group-addon color-hex-display"><?= rex_escape($colors['details_toggle_border'] ?? $colors['details_link'] ?? '#0066cc') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="details_toggle_border_width">Rahmenbreite: <span id="details_toggle_border_width_value"><?= rex_escape($colors['details_toggle_border_width'] ?? '2') ?>px</span></label>
                                <input type="range" class="form-control" id="details_toggle_border_width" name="details_toggle_border_width" min="1" max="5" value="<?= rex_escape($colors['details_toggle_border_width'] ?? '2') ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="well well-sm" style="margin-top: 10px; margin-bottom: 20px;">
                        <strong>Vorschau:</strong><br><br>
                        <button type="button" id="details-toggle-preview" style="display: inline-flex; align-items: center; padding: 8px 14px; border-radius: 4px; font-weight: 600; cursor: pointer;">
                            Details ▶
                        </button>
                    </div>
                    
                    <hr>
                    
                    <!-- Aufgeklappter Bereich -->
                    <h5><i class="rex-icon fa-folder-open"></i> Aufgeklappter Inhalt</h5>
                    <p class="help-block"><i class="rex-icon fa-info-circle"></i> Diese Farben gelten für den Bereich, der erscheint wenn "Details" geklickt wird.</p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="details_bg">Hintergrund:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control color-picker" id="details_bg" name="details_bg" value="<?= rex_escape($colors['details_bg'] ?? '#f8f9fa') ?>">
                                    <span class="input-group-addon color-hex-display"><?= rex_escape($colors['details_bg'] ?? '#f8f9fa') ?></span>
                                </div>
                            </div>
                            <?php if ('fluid' === $themeBase || 'fluid_dark' === $themeBase): ?>
                            <div class="form-group">
                                <label for="details_bg_opacity">Hintergrund-Transparenz: <span id="details_bg_opacity_value"><?= rex_escape($colors['details_bg_opacity'] ?? '100') ?>%</span></label>
                                <input type="range" class="form-control" id="details_bg_opacity" name="details_bg_opacity" min="0" max="100" value="<?= rex_escape($colors['details_bg_opacity'] ?? '100') ?>">
                            </div>
                            <?php endif ?>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="details_text">Textfarbe:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control color-picker" id="details_text" name="details_text" value="<?= rex_escape($colors['details_text'] ?? '#1a1a1a') ?>">
                                    <span class="input-group-addon color-hex-display"><?= rex_escape($colors['details_text'] ?? '#1a1a1a') ?></span>
                                    <span class="input-group-addon contrast-badge" id="details-text-contrast-badge">Prüfe...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="details_heading">Überschriften:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control color-picker" id="details_heading" name="details_heading" value="<?= rex_escape($colors['details_heading'] ?? '#1a1a1a') ?>">
                                    <span class="input-group-addon color-hex-display"><?= rex_escape($colors['details_heading'] ?? '#1a1a1a') ?></span>
                                    <span class="input-group-addon contrast-badge" id="details-heading-contrast-badge">Prüfe...</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="details_border">Rahmenfarbe:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control color-picker" id="details_border" name="details_border" value="<?= rex_escape($colors['details_border'] ?? '#dee2e6') ?>">
                                    <span class="input-group-addon color-hex-display"><?= rex_escape($colors['details_border'] ?? '#dee2e6') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="details_content_link">Inhalt-Linkfarbe:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control color-picker" id="details_content_link" name="details_content_link" value="<?= rex_escape($colors['details_link'] ?? '#0066cc') ?>">
                                    <span class="input-group-addon color-hex-display"><?= rex_escape($colors['details_link'] ?? '#0066cc') ?></span>
                                    <span class="input-group-addon contrast-badge" id="details-link-contrast-badge">Prüfe...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="well well-sm" style="margin-top: 15px;">
                        <strong>Vorschau Aufgeklappter Bereich:</strong><br><br>
                        <div id="details-preview" style="padding: 15px; border: 2px solid #dee2e6; border-radius: 4px;">
                            <h5 id="details-preview-heading" style="margin-top: 0;">Notwendig (2)</h5>
                            <p id="details-preview-text" style="margin-bottom: 5px;">Diese Cookies sind erforderlich...</p>
                            <a href="#" id="details-preview-link" onclick="return false;">Datenschutzerklärung</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Buttons -->
        <section class="rex-page-section">
            <div class="panel panel-default">
                <header class="panel-heading">
                    <div class="panel-title"><i class="rex-icon fa-square"></i> Buttons</div>
                </header>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="button_bg">Button-Hintergrund:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control color-picker" id="button_bg" name="button_bg" value="<?= rex_escape($colors['button_bg']) ?>" data-auto-text="button_text">
                                    <span class="input-group-addon color-hex-display"><?= rex_escape($colors['button_bg']) ?></span>
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default auto-text-btn" onclick="autoTextColor('button_bg', 'button_text')" title="Textfarbe automatisch berechnen">
                                            <i class="rex-icon fa-magic"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="button_text">Button-Textfarbe:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control color-picker" id="button_text" name="button_text" value="<?= rex_escape($colors['button_text']) ?>">
                                    <span class="input-group-addon color-hex-display"><?= rex_escape($colors['button_text']) ?></span>
                                    <span class="input-group-addon contrast-badge" id="button-contrast-badge">Prüfe...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="button_hover">Button-Hover-Hintergrund:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control color-picker" id="button_hover" name="button_hover" value="<?= rex_escape($colors['button_hover']) ?>" data-auto-text="button_hover_text">
                                    <span class="input-group-addon color-hex-display"><?= rex_escape($colors['button_hover']) ?></span>
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default auto-text-btn" onclick="autoTextColor('button_hover', 'button_hover_text')" title="Textfarbe automatisch berechnen">
                                            <i class="rex-icon fa-magic"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="button_hover_text">Button-Hover-Textfarbe:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control color-picker" id="button_hover_text" name="button_hover_text" value="<?= rex_escape($colors['button_hover_text']) ?>">
                                    <span class="input-group-addon color-hex-display"><?= rex_escape($colors['button_hover_text']) ?></span>
                                    <span class="input-group-addon contrast-badge" id="button-hover-contrast-badge">Prüfe...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Button Style Options -->
                    <hr>
                    <h5><i class="rex-icon fa-sliders"></i> Button-Design</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="button_style">Button-Stil:</label>
                                <select class="form-control" id="button_style" name="button_style">
                                    <option value="filled" <?= ($colors['button_style'] ?? 'filled') === 'filled' ? 'selected' : '' ?>>Ausgefüllt (Filled)</option>
                                    <option value="outline" <?= ($colors['button_style'] ?? 'filled') === 'outline' ? 'selected' : '' ?>>Nur Rahmen (Outline)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="button_radius">Button-Eckenradius: <span id="button_radius_value"><?= rex_escape($colors['button_radius'] ?? '4') ?>px</span></label>
                                <input type="range" class="form-control" id="button_radius" name="button_radius" min="0" max="30" value="<?= rex_escape($colors['button_radius'] ?? '4') ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="button_border_width">Button-Rahmenbreite: <span id="button_border_width_value"><?= rex_escape($colors['button_border_width'] ?? '2') ?>px</span></label>
                                <input type="range" class="form-control" id="button_border_width" name="button_border_width" min="1" max="5" value="<?= rex_escape($colors['button_border_width'] ?? '2') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="button_border_color">Button-Rahmenfarbe:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control color-picker" id="button_border_color" name="button_border_color" value="<?= rex_escape($colors['button_border_color'] ?? '#333333') ?>">
                                    <span class="input-group-addon color-hex-display"><?= rex_escape($colors['button_border_color'] ?? '#333333') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="well well-sm" style="margin-top: 15px;">
                        <strong>Vorschau:</strong><br><br>
                        <span class="preview-button" id="button-preview" style="display: inline-block; padding: 12px 24px; border-radius: 4px; font-weight: 600; margin-right: 10px;">Button-Text</span>
                        <span class="preview-button" id="button-hover-preview" style="display: inline-block; padding: 12px 24px; border-radius: 4px; font-weight: 600; margin-right: 10px;">Hover-Zustand</span>
                        <span class="preview-button" id="button-outline-preview" style="display: inline-block; padding: 12px 24px; border-radius: 4px; font-weight: 600; background: transparent; border: 2px solid #333;">Outline-Stil</span>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Shadow & Effekte -->
        <section class="rex-page-section">
            <div class="panel panel-default">
                <header class="panel-heading">
                    <div class="panel-title"><i class="rex-icon fa-sun-o"></i> Schatten & Effekte</div>
                </header>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="shadow_style">Schatten-Stil:</label>
                                <select class="form-control" id="shadow_style" name="shadow_style">
                                    <option value="none" <?= ($colors['shadow_style'] ?? 'medium') === 'none' ? 'selected' : '' ?>>Kein Schatten</option>
                                    <option value="subtle" <?= ($colors['shadow_style'] ?? 'medium') === 'subtle' ? 'selected' : '' ?>>Dezent</option>
                                    <option value="medium" <?= ($colors['shadow_style'] ?? 'medium') === 'medium' ? 'selected' : '' ?>>Mittel</option>
                                    <option value="strong" <?= ($colors['shadow_style'] ?? 'medium') === 'strong' ? 'selected' : '' ?>>Stark</option>
                                    <option value="floating" <?= ($colors['shadow_style'] ?? 'medium') === 'floating' ? 'selected' : '' ?>>Schwebend</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="shadow_color">Schatten-Farbe:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control color-picker" id="shadow_color" name="shadow_color" value="<?= rex_escape($colors['shadow_color'] ?? '#000000') ?>">
                                    <span class="input-group-addon color-hex-display"><?= rex_escape($colors['shadow_color'] ?? '#000000') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="shadow_opacity">Schatten-Stärke: <span id="shadow_opacity_value"><?= rex_escape($colors['shadow_opacity'] ?? '15') ?>%</span></label>
                                <input type="range" class="form-control" id="shadow_opacity" name="shadow_opacity" min="0" max="50" value="<?= rex_escape($colors['shadow_opacity'] ?? '15') ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="well well-sm" style="margin-top: 15px;">
                        <strong>Vorschau:</strong><br><br>
                        <div id="shadow-preview" style="display: inline-block; padding: 20px 40px; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                            Dialog-Vorschau mit Schatten
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Links & Focus -->
        <section class="rex-page-section">
            <div class="panel panel-default">
                <header class="panel-heading">
                    <div class="panel-title"><i class="rex-icon fa-link"></i> Links & Focus</div>
                </header>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="link_color">Link-Farbe:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control color-picker" id="link_color" name="link_color" value="<?= rex_escape($colors['link']) ?>">
                                    <span class="input-group-addon color-hex-display"><?= rex_escape($colors['link']) ?></span>
                                    <span class="input-group-addon contrast-badge" id="link-contrast-badge">Prüfe...</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="link_hover">Link-Hover-Farbe:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control color-picker" id="link_hover" name="link_hover" value="<?= rex_escape($colors['link_hover'] ?? '#004499') ?>">
                                    <span class="input-group-addon color-hex-display"><?= rex_escape($colors['link_hover'] ?? '#004499') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="focus_color">Focus-Farbe (Tastatur):</label>
                                <div class="input-group">
                                    <input type="color" class="form-control color-picker" id="focus_color" name="focus_color" value="<?= rex_escape($colors['focus']) ?>">
                                    <span class="input-group-addon color-hex-display"><?= rex_escape($colors['focus']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Overlay & Layout -->
        <section class="rex-page-section">
            <div class="panel panel-default">
                <header class="panel-heading">
                    <div class="panel-title"><i class="rex-icon fa-columns"></i> Overlay & Layout</div>
                </header>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="overlay_color">Overlay-Farbe:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control color-picker" id="overlay_color" name="overlay_color" value="<?= rex_escape($colors['overlay']) ?>">
                                    <span class="input-group-addon color-hex-display"><?= rex_escape($colors['overlay']) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="overlay_opacity">Overlay-Transparenz: <span id="overlay_opacity_value"><?= rex_escape($colors['overlay_opacity']) ?>%</span></label>
                                <input type="range" class="form-control" id="overlay_opacity" name="overlay_opacity" min="0" max="100" value="<?= rex_escape($colors['overlay_opacity']) ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="border_radius">Eckenradius: <span id="border_radius_value"><?= rex_escape($colors['border_radius']) ?>px</span></label>
                                <input type="range" class="form-control" id="border_radius" name="border_radius" min="0" max="20" value="<?= rex_escape($colors['border_radius']) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="border_width">Rahmenbreite: <span id="border_width_value"><?= rex_escape($colors['border_width']) ?>px</span></label>
                                <input type="range" class="form-control" id="border_width" name="border_width" min="1" max="5" value="<?= rex_escape($colors['border_width']) ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Actions -->
        <section class="rex-page-section">
            <div class="panel panel-default">
                <div class="panel-body">
                    <button class="btn btn-save" type="submit">
                        <i class="rex-icon fa-save"></i> Theme erstellen und speichern
                    </button>
                    <a href="<?= rex_url::backendPage('consent_manager/theme') ?>" class="btn btn-default">
                        <i class="rex-icon fa-arrow-left"></i> Zurück zur Theme-Übersicht
                    </a>
                </div>
            </div>
        </section>
    </form>
</div>

<style>
.consent-manager-theme-editor .color-picker {
    width: 60px;
    height: 38px;
    padding: 2px;
    cursor: pointer;
}
.consent-manager-theme-editor .color-hex-display {
    font-family: monospace;
    min-width: 80px;
}
.consent-manager-theme-editor .contrast-badge {
    font-weight: 600;
    min-width: 140px;
}
.consent-manager-theme-editor .contrast-pass {
    background: #d4edda;
    color: #155724;
}
.consent-manager-theme-editor .contrast-fail {
    background: #f8d7da;
    color: #721c24;
}
.consent-manager-theme-editor .contrast-warning {
    background: #fff3cd;
    color: #856404;
}
.consent-manager-theme-editor input[type="range"] {
    -webkit-appearance: auto;
    appearance: auto;
}
</style>

<script nonce="<?= rex_response::getNonce() ?>">
(function() {
    'use strict';
    
    // Kontrast-Berechnung nach WCAG 2.1
    function getLuminance(hex) {
        const rgb = hexToRgb(hex);
        const [r, g, b] = [rgb.r, rgb.g, rgb.b].map(function(v) {
            v /= 255;
            return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
        });
        return 0.2126 * r + 0.7152 * g + 0.0722 * b;
    }

    function hexToRgb(hex) {
        hex = hex.replace('#', '');
        return {
            r: parseInt(hex.substr(0, 2), 16),
            g: parseInt(hex.substr(2, 2), 16),
            b: parseInt(hex.substr(4, 2), 16)
        };
    }

    function getContrastRatio(hex1, hex2) {
        const l1 = getLuminance(hex1);
        const l2 = getLuminance(hex2);
        const lighter = Math.max(l1, l2);
        const darker = Math.min(l1, l2);
        return (lighter + 0.05) / (darker + 0.05);
    }

    function updateContrastBadge(badgeId, ratio) {
        const badge = document.getElementById(badgeId);
        if (!badge) return;
        
        const ratioText = ratio.toFixed(2) + ':1';
        
        badge.classList.remove('contrast-pass', 'contrast-warning', 'contrast-fail');
        
        if (ratio >= 4.5) {
            badge.classList.add('contrast-pass');
            badge.textContent = '✓ ' + ratioText + ' (AAA)';
        } else if (ratio >= 3) {
            badge.classList.add('contrast-warning');
            badge.textContent = '⚠ ' + ratioText + ' (AA groß)';
        } else {
            badge.classList.add('contrast-fail');
            badge.textContent = '✗ ' + ratioText + ' (unzureichend)';
        }
    }

    // Automatische Textfarbe berechnen (Schwarz oder Weiß)
    window.autoTextColor = function(bgInputId, textInputId) {
        const bgInput = document.getElementById(bgInputId);
        const textInput = document.getElementById(textInputId);
        
        const bgColor = bgInput.value;
        const luminance = getLuminance(bgColor);
        
        // Wähle Schwarz oder Weiß basierend auf Hintergrund-Helligkeit
        const textColor = luminance > 0.179 ? '#000000' : '#ffffff';
        
        textInput.value = textColor;
        updateHexDisplay(textInput);
        
        updateAllContrasts();
        updateButtonPreviews();
    };
    
    function updateHexDisplay(colorInput) {
        const hexDisplay = colorInput.parentElement.querySelector('.color-hex-display');
        if (hexDisplay) {
            hexDisplay.textContent = colorInput.value;
        }
    }

    function updateAllContrasts() {
        const bg = document.getElementById('background_color').value;
        const text = document.getElementById('text_color').value;
        const title = document.getElementById('title_color') ? document.getElementById('title_color').value : text;
        const buttonBg = document.getElementById('button_bg').value;
        const buttonText = document.getElementById('button_text').value;
        const buttonHover = document.getElementById('button_hover').value;
        const buttonHoverText = document.getElementById('button_hover_text').value;
        const link = document.getElementById('link_color').value;
        
        // Details-Bereich
        const detailsBg = document.getElementById('details_bg') ? document.getElementById('details_bg').value : '#f8f9fa';
        const detailsText = document.getElementById('details_text') ? document.getElementById('details_text').value : '#1a1a1a';
        const detailsHeading = document.getElementById('details_heading') ? document.getElementById('details_heading').value : '#1a1a1a';
        const detailsLink = document.getElementById('details_link') ? document.getElementById('details_link').value : '#0066cc';
        
        // Haupt-Kontraste
        updateContrastBadge('text-contrast-badge', getContrastRatio(bg, text));
        updateContrastBadge('title-contrast-badge', getContrastRatio(bg, title));
        updateContrastBadge('button-contrast-badge', getContrastRatio(buttonBg, buttonText));
        updateContrastBadge('button-hover-contrast-badge', getContrastRatio(buttonHover, buttonHoverText));
        updateContrastBadge('link-contrast-badge', getContrastRatio(bg, link));
        
        // Details-Kontraste
        updateContrastBadge('details-text-contrast-badge', getContrastRatio(detailsBg, detailsText));
        updateContrastBadge('details-heading-contrast-badge', getContrastRatio(detailsBg, detailsHeading));
        updateContrastBadge('details-link-contrast-badge', getContrastRatio(detailsBg, detailsLink));
    }

    function updateDetailsPreviews() {
        const detailsBg = document.getElementById('details_bg') ? document.getElementById('details_bg').value : '#f8f9fa';
        const detailsText = document.getElementById('details_text') ? document.getElementById('details_text').value : '#1a1a1a';
        const detailsHeading = document.getElementById('details_heading') ? document.getElementById('details_heading').value : '#1a1a1a';
        const detailsBorder = document.getElementById('details_border') ? document.getElementById('details_border').value : '#dee2e6';
        const detailsLink = document.getElementById('details_link') ? document.getElementById('details_link').value : '#0066cc';
        
        const preview = document.getElementById('details-preview');
        const previewHeading = document.getElementById('details-preview-heading');
        const previewText = document.getElementById('details-preview-text');
        const previewLink = document.getElementById('details-preview-link');
        
        if (preview) {
            preview.style.backgroundColor = detailsBg;
            preview.style.borderColor = detailsBorder;
        }
        if (previewHeading) {
            previewHeading.style.color = detailsHeading;
        }
        if (previewText) {
            previewText.style.color = detailsText;
        }
        if (previewLink) {
            previewLink.style.color = detailsLink;
        }
    }

    function updateButtonPreviews() {
        const buttonBg = document.getElementById('button_bg').value;
        const buttonText = document.getElementById('button_text').value;
        const buttonHover = document.getElementById('button_hover').value;
        const buttonHoverText = document.getElementById('button_hover_text').value;
        const buttonRadius = document.getElementById('button_radius') ? document.getElementById('button_radius').value + 'px' : '4px';
        const buttonBorderWidth = document.getElementById('button_border_width') ? document.getElementById('button_border_width').value + 'px' : '2px';
        const buttonBorderColor = document.getElementById('button_border_color') ? document.getElementById('button_border_color').value : '#333333';
        const buttonStyle = document.getElementById('button_style') ? document.getElementById('button_style').value : 'filled';
        
        const preview = document.getElementById('button-preview');
        const hoverPreview = document.getElementById('button-hover-preview');
        const outlinePreview = document.getElementById('button-outline-preview');
        
        if (preview) {
            preview.style.backgroundColor = buttonBg;
            preview.style.color = buttonText;
            preview.style.borderRadius = buttonRadius;
            preview.style.border = buttonBorderWidth + ' solid ' + buttonBorderColor;
        }
        
        if (hoverPreview) {
            hoverPreview.style.backgroundColor = buttonHover;
            hoverPreview.style.color = buttonHoverText;
            hoverPreview.style.borderRadius = buttonRadius;
            hoverPreview.style.border = buttonBorderWidth + ' solid ' + buttonBorderColor;
        }
        
        if (outlinePreview) {
            outlinePreview.style.backgroundColor = 'transparent';
            outlinePreview.style.color = buttonBorderColor;
            outlinePreview.style.borderRadius = buttonRadius;
            outlinePreview.style.border = buttonBorderWidth + ' solid ' + buttonBorderColor;
        }
    }

    function updateShadowPreview() {
        const shadowStyle = document.getElementById('shadow_style') ? document.getElementById('shadow_style').value : 'medium';
        const shadowColor = document.getElementById('shadow_color') ? document.getElementById('shadow_color').value : '#000000';
        const shadowOpacity = document.getElementById('shadow_opacity') ? parseInt(document.getElementById('shadow_opacity').value) / 100 : 0.15;
        const borderRadius = document.getElementById('border_radius') ? document.getElementById('border_radius').value + 'px' : '4px';
        
        const preview = document.getElementById('shadow-preview');
        if (!preview) return;
        
        // Konvertiere Hex zu RGB
        const hex = shadowColor.replace('#', '');
        const r = parseInt(hex.substr(0, 2), 16);
        const g = parseInt(hex.substr(2, 2), 16);
        const b = parseInt(hex.substr(4, 2), 16);
        
        let boxShadow = 'none';
        switch (shadowStyle) {
            case 'none':
                boxShadow = 'none';
                break;
            case 'subtle':
                boxShadow = '0 2px 4px rgba(' + r + ',' + g + ',' + b + ',' + (shadowOpacity * 0.5) + ')';
                break;
            case 'medium':
                boxShadow = '0 4px 12px rgba(' + r + ',' + g + ',' + b + ',' + shadowOpacity + ')';
                break;
            case 'strong':
                boxShadow = '0 8px 24px rgba(' + r + ',' + g + ',' + b + ',' + (shadowOpacity * 1.5) + ')';
                break;
            case 'floating':
                boxShadow = '0 12px 40px rgba(' + r + ',' + g + ',' + b + ',' + (shadowOpacity * 2) + '), 0 4px 8px rgba(' + r + ',' + g + ',' + b + ',' + (shadowOpacity * 0.5) + ')';
                break;
        }
        
        preview.style.boxShadow = boxShadow;
        preview.style.borderRadius = borderRadius;
    }

    function updateDetailsTogglePreview() {
        const detailsLink = document.getElementById('details_link') ? document.getElementById('details_link').value : '#0066cc';
        const detailsToggleBorder = document.getElementById('details_toggle_border') ? document.getElementById('details_toggle_border').value : detailsLink;
        const detailsToggleBorderWidth = document.getElementById('details_toggle_border_width') ? document.getElementById('details_toggle_border_width').value + 'px' : '2px';
        const buttonRadius = document.getElementById('button_radius') ? document.getElementById('button_radius').value + 'px' : '4px';
        const bg = document.getElementById('background_color') ? document.getElementById('background_color').value : '#ffffff';
        
        const preview = document.getElementById('details-toggle-preview');
        if (preview) {
            preview.style.color = detailsLink;
            preview.style.backgroundColor = 'transparent';
            preview.style.border = detailsToggleBorderWidth + ' solid ' + detailsToggleBorder;
            preview.style.borderRadius = buttonRadius;
        }
    }

    // Event Listeners - use rex:ready for REDAXO backend
    function initThemeEditor() {
        // Update hex values when color picker changes
        document.querySelectorAll('.color-picker').forEach(function(colorInput) {
            colorInput.addEventListener('input', function() {
                updateHexDisplay(this);
                updateAllContrasts();
                updateButtonPreviews();
                updateDetailsPreviews();
                updateDetailsTogglePreview();
                updateShadowPreview();
            });
        });

        // Update range values
        document.querySelectorAll('input[type="range"]').forEach(function(rangeInput) {
            rangeInput.addEventListener('input', function() {
                const valueSpan = document.getElementById(this.id + '_value');
                if (valueSpan) {
                    const isOpacity = this.id.includes('opacity');
                    const isFontSize = this.id === 'font_size';
                    let unit = isOpacity ? '%' : 'px';
                    // Für allgemeine Schriftgröße: Wenn Standard-Bereich, zeige "Standard"
                    if (isFontSize && this.value === '') {
                        valueSpan.textContent = 'Standard';
                    } else {
                        valueSpan.textContent = this.value + unit;
                    }
                }
                updateButtonPreviews();
                updateDetailsTogglePreview();
                updateShadowPreview();
            });
        });
        
        // Update select changes
        document.querySelectorAll('select').forEach(function(selectInput) {
            selectInput.addEventListener('change', function() {
                updateButtonPreviews();
                updateShadowPreview();
            });
        });

        // Initial update
        updateAllContrasts();
        updateButtonPreviews();
        updateDetailsPreviews();
        updateDetailsTogglePreview();
        updateShadowPreview();
    }
    
    // Support both DOMContentLoaded and rex:ready for REDAXO backend
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initThemeEditor);
    } else {
        initThemeEditor();
    }
    $(document).on('rex:ready', initThemeEditor);
})();
</script>
