<?php
/**
 * Fragment für Inline-Consent Platzhalter
 * 
 * Verfügbare Variablen:
 * - $serviceKey: Service-Schlüssel (z.B. 'youtube')
 * - $consentId: Eindeutige Consent-ID
 * - $service: Service-Daten aus DB
 * - $options: Konfigurationsoptionen
 * - $placeholderData: Zusätzliche Daten (thumbnail, icon, service_name)
 * - $content: Original-Content der geladen werden soll
 */

$thumbnailHtml = '';
if (!empty($placeholderData['thumbnail'])) {
    $thumbnailHtml = '<img src="' . rex_escape($placeholderData['thumbnail']) . '" 
                           alt="' . rex_escape($options['title']) . '" 
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
                    <div class="consent-inline-icon"><?= rex_escape($placeholderData['icon']) ?></div>
                    <h4 class="consent-inline-title"><?= rex_escape($options['title']) ?></h4>
                    <p class="consent-inline-notice"><?= rex_escape($options['privacy_notice']) ?></p>
                    
                    <div class="consent-inline-actions">
                        <button type="button" class="btn btn-consent-accept consent-inline-accept" 
                                data-consent-id="<?= rex_escape($consentId) ?>"
                                data-service="<?= rex_escape($serviceKey) ?>">
                            <i class="fa fa-check"></i> <?= rex_escape($options['placeholder_text']) ?>
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