<?php
/**
 * Fragment: Webawesome (Shoelace successor) Modal
 * Uses Wa-Dialog and utility classes if available
 */

use FriendsOfRedaxo\ConsentManager\Frontend;

$consent_manager = new Frontend(0);
if (is_string(rex_request::server('HTTP_HOST'))) {
    $consent_manager->setDomain(rex_request::server('HTTP_HOST'));
}

$shadow = rex_addon::get('consent_manager')->getConfig('css_framework_shadow', 'none');
$rounded = rex_addon::get('consent_manager')->getConfig('css_framework_rounded', '0');

// Webawesome specific classes/styles could be added here
?>

<div tabindex="-1" class="consent_manager-background consent_manager-hidden" id="consent_manager-background" 
     data-domain-name="<?= $consent_manager->domainName ?>" 
     data-version="<?= $consent_manager->version ?>" 
     data-consentid="<?= uniqid('', true) ?>" 
     data-cachelogid="<?= $consent_manager->cacheLogId ?>" 
     data-nosnippet aria-hidden="true">
    
    <wa-dialog label="<?= $consent_manager->texts['headline'] ?>" id="consent_manager-wa-dialog" style="--width: 600px; max-width: 95vw;">
        <div class="consent_manager-wa-content">
            <p><?= $consent_manager->texts['description'] ?></p>
            
            <div style="margin-top: 20px;">
                <?php foreach ($consent_manager->cookiegroups as $cookiegroup) : ?>
                    <wa-details summary="<?= $cookiegroup['name'] ?>" style="margin-bottom: 10px;">
                        <div style="margin-bottom: 10px;">
                            <wa-checkbox 
                                class="consent_manager-cookiegroup-checkbox" 
                                data-uid="<?= $cookiegroup['uid'] ?>"
                                <?= $cookiegroup['required'] ? 'checked disabled' : '' ?>>
                                <?= $cookiegroup['name'] ?>
                            </wa-checkbox>
                        </div>
                        <p><?= $cookiegroup['description'] ?></p>
                        
                        <wa-list style="margin-top: 10px;">
                            <?php foreach ($cookiegroup['cookie'] as $cookie) : ?>
                                <wa-list-item>
                                    <strong><?= $cookie['service_name'] ?></strong>
                                    <div slot="description"><?= $cookie['usage'] ?></div>
                                </wa-list-item>
                            <?php endforeach; ?>
                        </wa-list>
                    </wa-details>
                <?php endforeach; ?>
            </div>
            
            <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
                <wa-button variant="default" class="consent_manager-save">
                    <?= $consent_manager->texts['button_accept'] ?>
                </wa-button>
                <wa-button variant="primary" class="consent_manager-accept-all">
                    <?= $consent_manager->texts['button_select_all'] ?>
                </wa-button>
            </div>

            <div style="margin-top: 15px; font-size: 0.8em; text-align: center;">
                <a href="<?= rex_getUrl($consent_manager->links['privacy_policy']) ?>"><?= $consent_manager->texts['link_privacy'] ?></a>
                <?php if (isset($consent_manager->links['legal_notice'])): ?>
                    | <a href="<?= rex_getUrl($consent_manager->links['legal_notice']) ?>"><?= $consent_manager->texts['link_imprint'] ?? 'Impressum' ?></a>
                <?php endif; ?>
            </div>
        </div>
    </wa-dialog>

    <!-- Base64 Scripts -->
    <?php
    foreach ($consent_manager->scripts as $uid => $script) {
        echo '<div class="consent_manager-script" data-uid="script-' . $uid . '" data-script="' . $script . '"></div>';
    }
    foreach ($consent_manager->scriptsUnselect as $uid => $script) {
        echo '<div class="consent_manager-script" data-uid="script-unselect-' . $uid . '" data-script="' . $script . '"></div>';
    }
    ?>

    <script nonce="<?= rex_response::getNonce() ?>">
        // Webawesome / Shoelace Dialog handling
        const dialog = document.querySelector('#consent_manager-wa-dialog');
        const background = document.querySelector('#consent_manager-background');
        
        // Open dialog when background is shown (handled by consent_manager_frontend.js)
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'class') {
                    if (!background.classList.contains('consent_manager-hidden')) {
                        dialog.show();
                    } else {
                        dialog.hide();
                    }
                }
            });
        });
        observer.observe(background, { attributes: true });
    </script>
</div>
