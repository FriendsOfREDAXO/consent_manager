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
                    <div class="consent-inline-icon">
                        <?php 
                        $iconClass = $placeholderData['icon'] ?? 'fa fa-play-circle';
                        $iconLabel = $placeholderData['icon_label'] ?? 'Media Icon';
                        if (strpos($iconClass, 'uk-icon:') === 0) {
                            // UIkit Icon
                            $ukIcon = str_replace('uk-icon:', '', $iconClass);
                            echo '<span uk-icon="icon: ' . rex_escape($ukIcon) . '; ratio: 3" aria-label="' . rex_escape($iconLabel) . '"></span>';
                        } else {
                            // FontAwesome Icon
                            echo '<i class="' . rex_escape($iconClass) . '" aria-hidden="true"></i>';
                            echo '<span class="sr-only">' . rex_escape($iconLabel) . '</span>';
                        }
                        ?>
                    </div>
                    <h4 class="consent-inline-title"><?= rex_escape($options['title'] ?? $this->getVar('inline_title_fallback')) ?></h4>
                    <p class="consent-inline-notice"><?= rex_escape($options['privacy_notice'] ?? $this->getVar('inline_privacy_notice')) ?></p>
                    <p class="consent-inline-action-text"><?= rex_escape($this->getVar('inline_action_text')) ?></p>
                    
                    <?php if (!empty($service['provider_link_privacy'])): ?>
                    <div class="consent-inline-privacy-link">
                        <a href="<?= rex_escape($service['provider_link_privacy']) ?>" target="_blank" rel="noopener noreferrer">
                            <?php 
                            $privacyIcon = $this->getVar('privacy_icon', 'fa fa-shield-alt');
                            if (strpos($privacyIcon, 'uk-icon:') === 0) {
                                $ukIcon = str_replace('uk-icon:', '', $privacyIcon);
                                echo '<span uk-icon="icon: ' . rex_escape($ukIcon) . '" aria-hidden="true"></span>';
                            } else {
                                echo '<i class="' . rex_escape($privacyIcon) . '" aria-hidden="true"></i>';
                            }
                            ?>
                            <?= rex_escape($this->getVar('inline_privacy_link_text')) ?> <?= rex_escape($service['provider'] ?? $service['service_name'] ?? 'Anbieter') ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <div class="consent-inline-actions">
                        <button type="button" class="btn btn-consent-once consent-inline-once" 
                                data-consent-id="<?= rex_escape($consentId) ?>"
                                data-service="<?= rex_escape($serviceKey) ?>">
                            <i class="fa fa-play"></i> <?= rex_escape($options['placeholder_text'] ?? $this->getVar('inline_placeholder_text')) ?>
                        </button>
                        
                        <?php if ($this->getVar('show_allow_all', false)): ?>
                        <button type="button" class="btn btn-consent-allow-all consent-inline-allow-all" 
                                data-consent-id="<?= rex_escape($consentId) ?>">
                            <i class="fa fa-check"></i> <?= rex_escape($this->getVar('button_inline_allow_all_text')) ?>
                        </button>
                        <?php endif; ?>
                        
                                                <button type="button" class="btn btn-consent-details consent-inline-details" 
                                onclick="consentManager.showBox()">
                            <i class="fa fa-cog"></i> <?= rex_escape($this->getVar('button_inline_details_text')) ?>
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