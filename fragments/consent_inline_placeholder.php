<?php
/**
 * Fragment für Inline-Consent Platzhalter
 * 
 * Verfügbare Variablen (via $this->getVar()):
 * - serviceKey: Service-Schlüssel (z.B. 'youtube')
 * - consentId: Eindeutige Consent-ID
 * - service: Service-Daten aus DB
 * - options: Konfigurationsoptionen
 * - placeholderData: Zusätzliche Daten (thumbnail, icon, service_name)
 * - content: Original-Content der geladen werden soll
 */

// Variablen aus Fragment abrufen
$serviceKey = $this->getVar('serviceKey', '');
$consentId = $this->getVar('consentId', ''); 
$service = $this->getVar('service', []);
$options = $this->getVar('options', []);
$placeholderData = $this->getVar('placeholderData', []);
$content = $this->getVar('content', '');

// Debug: Variablen ausgeben (nur im Debug-Modus)
if (rex::isDebugMode()) {
    echo '<div style="background:#e2e3e5;border:1px solid #d6d8db;padding:10px;margin:10px 0;font-size:12px;">';
    echo '<strong>Fragment Debug:</strong><br>';
    echo 'serviceKey: ' . var_export($serviceKey, true) . '<br>';
    echo 'consentId: ' . var_export($consentId, true) . '<br>';
    echo 'options: ' . var_export($options, true) . '<br>';
    echo 'placeholderData: ' . var_export($placeholderData, true) . '<br>';
    echo 'content length: ' . strlen($content) . ' chars<br>';
    echo '</div>';
}

$thumbnailHtml = '';
if (!empty($placeholderData['thumbnail'])) {
    $thumbnailHtml = '<img src="' . rex_escape($placeholderData['thumbnail']) . '" 
                           alt="' . rex_escape($options['title'] ?? 'Video') . '" 
                           class="consent-inline-thumbnail" 
                           loading="lazy" />';
}
?>

<div class="consent-inline-container" data-consent-id="<?= rex_escape($consentId) ?>" 
     data-service="<?= rex_escape($serviceKey) ?>">
    
    <div class="consent-inline-placeholder">
        <div class="consent-inline-content">
            <?= $thumbnailHtml ?>
            
            <div class="consent-inline-overlay">
                <div class="consent-inline-info">
                    <div class="consent-inline-icon"><?= rex_escape($placeholderData['icon'] ?? '🎥') ?></div>
                    <h4 class="consent-inline-title"><?= rex_escape($options['title'] ?? 'Video laden') ?></h4>
                    <p class="consent-inline-notice"><?= rex_escape($options['privacy_notice'] ?? 'Für die Anzeige werden Cookies benötigt.') ?></p>
                    
                    <div class="consent-inline-actions">
                        <button type="button" class="btn btn-consent-accept consent-inline-accept" 
                                data-consent-id="<?= rex_escape($consentId) ?>"
                                data-service="<?= rex_escape($serviceKey) ?>">
                            <i class="fa fa-check"></i> <?= rex_escape($options['placeholder_text'] ?? 'Video laden') ?>
                        </button>
                        
                        <button type="button" class="btn btn-consent-details consent-inline-details" 
                                data-service="<?= rex_escape($serviceKey) ?>">
                            <i class="fa fa-info-circle"></i> Cookie-Details
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <script type="text/plain" class="consent-content-data" 
                data-consent-code="<?= rex_escape($serviceKey) ?>">
            <?= $content ?>
        </script>
    </div>
</div>