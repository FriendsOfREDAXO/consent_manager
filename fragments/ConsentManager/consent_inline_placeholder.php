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

/** @var rex_fragment $this  */
// Variablen aus Fragment abrufen
$serviceKey = $this->getVar('serviceKey', '');
$consentId = $this->getVar('consentId', ''); 
$service = $this->getVar('service', []);
$options = $this->getVar('options', []);
$placeholderData = $this->getVar('placeholderData', []);
$content = $this->getVar('content', '');

// Text-Variablen aus Fragment abrufen (von FriendsOfRedaxo\ConsentManager\InlineConsent::getButtonText())
$inline_title_fallback = $this->getVar('inline_title_fallback', 'Externes Medium');
$inline_privacy_notice = $this->getVar('inline_privacy_notice', 'Für die Anzeige werden Cookies benötigt.');
$inline_action_text = $this->getVar('inline_action_text', 'Was möchten Sie tun?');
$inline_placeholder_text = $this->getVar('inline_placeholder_text', 'Einmal laden');
$inline_privacy_link_text = $this->getVar('inline_privacy_link_text', 'Datenschutzerklärung von');
$button_inline_details_text = $this->getVar('button_inline_details_text', 'Einstellungen');
$button_inline_allow_all_text = $this->getVar('button_inline_allow_all_text', 'Alle erlauben');
$show_allow_all = $this->getVar('show_allow_all', false) !== false;

if (rex::isDebugMode()) {
    echo "<!-- DEBUG Fragment Variables: -->\n";
    echo "<!-- inline_privacy_notice: $inline_privacy_notice -->\n";
    echo "<!-- options[privacy_notice]: " . ($options['privacy_notice'] ?? 'NOT SET') . " -->\n";
    echo "<!-- Final value: " . ($options['privacy_notice'] ?? $inline_privacy_notice) . " -->\n";
}


$thumbnailHtml = '';
if (isset($placeholderData['thumbnail']) && '' !== $placeholderData['thumbnail']) {
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
                    <h4 class="consent-inline-title"><?= rex_escape($options['title'] ?? $inline_title_fallback) ?></h4>
                    <p class="consent-inline-notice"><?= rex_escape($options['privacy_notice'] ?? $inline_privacy_notice) ?></p>
                    <p class="consent-inline-action-text"><?= rex_escape($inline_action_text) ?></p>
                    
                    <?php if (isset($service['provider_link_privacy']) && '' !== $service['provider_link_privacy']): ?>
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
                            <?= rex_escape($inline_privacy_link_text) ?> <?= rex_escape($service['provider'] ?? $service['service_name'] ?? 'Anbieter') ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <div class="consent-inline-actions">
                        <button type="button" class="btn btn-consent-once consent-inline-once" 
                                data-consent-id="<?= rex_escape($consentId) ?>"
                                data-service="<?= rex_escape($serviceKey) ?>">
                            <i class="fa fa-play" aria-hidden="true"></i> <?= rex_escape($options['placeholder_text'] ?? $inline_placeholder_text) ?>
                        </button>
                        
                        <?php if ($show_allow_all): ?>
                        <button type="button" class="btn btn-consent-allow-all consent-inline-allow-all" 
                                data-service="<?= rex_escape($serviceKey) ?>">
                            <i class="fa fa-check-circle" aria-hidden="true"></i> <?= rex_escape($button_inline_allow_all_text) ?>
                        </button>
                        <?php endif; ?>
                        
                        <button type="button" class="btn btn-consent-details consent-inline-details"
                                 data-service="<?= rex_escape($serviceKey) ?>">
                            <i class="fa fa-cog" aria-hidden="true"></i> <?= rex_escape($button_inline_details_text) ?>
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