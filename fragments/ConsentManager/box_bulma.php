<?php
/**
 * Fragment: Bulma Framework Modal
 * Uses native Bulma classes: modal, modal-card, modal-card-head, modal-card-body, modal-card-foot
 */

use FriendsOfRedaxo\ConsentManager\Frontend;

$consent_manager = new Frontend(0);
if (is_string(rex_request::server('HTTP_HOST'))) {
    $consent_manager->setDomain(rex_request::server('HTTP_HOST'));
}

$shadow = rex_addon::get('consent_manager')->getConfig('css_framework_shadow', 'none');
$rounded = rex_addon::get('consent_manager')->getConfig('css_framework_rounded', '0');

$shadowClass = '';
if ($shadow === 'small') $shadowClass = 'is-shadowless'; // Bulma has no 'small' shadow by default, let's use utilities
if ($shadow === 'large') $shadowClass = 'has-shadow'; 
?>

<div tabindex="-1" class="consent_manager-background consent_manager-hidden" id="consent_manager-background" 
     style="z-index: 1000;"
     data-domain-name="<?= $consent_manager->domainName ?>" 
     data-version="<?= $consent_manager->version ?>" 
     data-consentid="<?= uniqid('', true) ?>" 
     data-cachelogid="<?= $consent_manager->cacheLogId ?>" 
     data-nosnippet aria-hidden="true">
    
    <div class="modal is-active" id="consent_manager-bulma-modal">
        <div class="modal-background"></div>
        <div class="modal-card <?= $shadowClass ?>" style="width: 100%; max-width: 640px; <?= $rounded === '0' ? 'border-radius: 0;' : '' ?>">
            <header class="modal-card-head" <?= $rounded === '0' ? 'style="border-radius: 0;"' : '' ?>>
                <p class="modal-card-title"><?= $consent_manager->texts['headline'] ?></p>
            </header>
            <section class="modal-card-body">
                <div class="content">
                    <p><?= $consent_manager->texts['description'] ?></p>
                </div>
                
                <div class="consent_manager-cookiegroups mt-4">
                    <?php foreach ($consent_manager->cookiegroups as $cookiegroup) : ?>
                        <div class="box mb-3 p-3" <?= $rounded === '0' ? 'style="border-radius: 0;"' : '' ?>>
                            <div class="field">
                                <label class="checkbox is-size-6 has-text-weight-bold">
                                    <input type="checkbox" 
                                           class="consent_manager-cookiegroup-checkbox mr-2" 
                                           data-uid="<?= $cookiegroup['uid'] ?>"
                                           <?= $cookiegroup['required'] ? 'checked disabled' : '' ?>>
                                    <?= $cookiegroup['name'] ?>
                                </label>
                            </div>
                            <p class="is-size-7 has-text-grey"><?= $cookiegroup['description'] ?></p>
                            
                            <details class="mt-2">
                                <summary class="is-size-7 has-text-link is-clickable">Details anzeigen</summary>
                                <div class="mt-2 pl-3 border-left">
                                    <?php foreach ($cookiegroup['cookie'] as $cookie) : ?>
                                        <div class="mb-2">
                                            <div class="is-size-7 has-text-weight-semibold"><?= $cookie['service_name'] ?></div>
                                            <div class="is-size-7 has-text-grey"><?= $cookie['usage'] ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </details>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <footer class="modal-card-foot is-justify-content-flex-end" <?= $rounded === '0' ? 'style="border-radius: 0;"' : '' ?>>
                <button class="button is-light consent_manager-save">
                    <?= $consent_manager->texts['button_accept'] ?>
                </button>
                <button class="button is-primary consent_manager-accept-all">
                    <?= $consent_manager->texts['button_select_all'] ?>
                </button>
            </footer>
            
            <div class="has-text-centered py-3 is-size-7 has-text-grey" style="background: var(--bulma-modal-card-foot-background-color, #f5f5f5);">
                <?php
                $privacy_policy_id = $consent_manager->links['privacy_policy'] ?? 0;
                $legal_notice_id = $consent_manager->links['legal_notice'] ?? 0;
                ?>
                <a href="<?= $privacy_policy_id ? rex_getUrl($privacy_policy_id) : '#' ?>" class="has-text-grey"><?= $consent_manager->texts['link_privacy'] ?></a>
                <?php if ($legal_notice_id): ?>
                    <span class="mx-1">|</span>
                    <a href="<?= rex_getUrl($legal_notice_id) ?>" class="has-text-grey"><?= $consent_manager->texts['link_imprint'] ?? 'Impressum' ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Base64 Scripts -->
    <?php
    foreach ($consent_manager->scripts as $uid => $script) {
        echo '<div class="consent_manager-script" data-uid="script-' . $uid . '" data-script="' . $script . '"></div>';
    }
    foreach ($consent_manager->scriptsUnselect as $uid => $script) {
        echo '<div class="consent_manager-script" data-uid="script-unselect-' . $uid . '" data-script="' . $script . '"></div>';
    }
    ?>

    <style nonce="<?= rex_response::getNonce() ?>">
        #consent_manager-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #consent_manager-background.consent_manager-hidden {
            display: none !important;
        }
    </style>
</div>
