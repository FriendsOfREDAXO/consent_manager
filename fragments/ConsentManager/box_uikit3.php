<?php

/**
 * UIkit 3 Framework Fragment für Consent Manager
 * Verwendet UIkit 3 CSS-Klassen statt der Standard-Consent-Manager-Styles
 * Behält die Original-Struktur und JavaScript-Funktionalität bei
 */

use FriendsOfRedaxo\ConsentManager\Frontend;

$consent_manager = new Frontend(0);
if (is_string(rex_request::server('HTTP_HOST'))) {
    $consent_manager->setDomain(rex_request::server('HTTP_HOST'));
}

if (0 === count($consent_manager->texts)) {
    echo '<div id="consent_manager-background"><div class="uk-alert-danger" uk-alert>' . rex_addon::get('consent_manager')->i18n('consent_manager_error_noconfig') . '</div></div>';
    return;
}

if (0 >= count($consent_manager->cookiegroups)) {
    return;
}
?>

<style nonce="<?= rex_response::getNonce() ?>">
/* Glue-CSS für UIkit Integration */
#consent_manager-background {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    background: rgba(0, 0, 0, 0.6) !important;
    display: none; /* Standardmäßig aus, JS schaltet es ein */
    align-items: center;
    justify-content: center;
    padding: 1rem;
    z-index: 1000000 !important; /* Über UIkit Modals (1010) */
}
#consent_manager-background:not(.consent_manager-hidden) {
    display: flex !important;
}
#consent_manager-background.consent_manager-hidden {
    display: none !important;
}
#consent_manager-wrapper {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    max-width: 800px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    padding: 0;
}
.uk-card-body {
    padding: 40px !important;
}
@media (max-width: 640px) {
    #consent_manager-wrapper {
        max-height: 100vh;
        height: 100vh;
        border-radius: 0;
    }
}
</style>
.consent_manager-hidden {
    display: none !important;
}
.consent_manager-close-box {
    position: absolute;
    top: 1rem;
    right: 1rem;
    border: none;
    background: transparent;
    font-size: 1.5rem;
    cursor: pointer;
    z-index: 10;
}
</style>

<div tabindex="-1" class="consent_manager-background consent_manager-hidden <?= $consent_manager->boxClass ?>" id="consent_manager-background" data-domain-name="<?= $consent_manager->domainName ?>" data-version="<?= $consent_manager->version ?>" data-consentid="<?= uniqid('', true) ?>" data-cachelogid="<?= $consent_manager->cacheLogId ?>" data-nosnippet aria-hidden="true">
    <div class="consent_manager-wrapper uk-card uk-card-default" id="consent_manager-wrapper" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="consent_manager-headline">
        <button tabindex="0" class="consent_manager-close-box consent_manager-close uk-close" aria-label="Close">&#10006;</button>
        
        <div class="consent_manager-wrapper-inner uk-card-body">
            <div class="consent_manager-summary" id="consent_manager-summary">
                <h2 class="uk-h3 uk-margin-small-bottom" id="consent_manager-headline"><?= $consent_manager->texts['headline'] ?></h2>
                <div class="uk-text-meta uk-margin-bottom"><?= nl2br($consent_manager->texts['description']) ?></div>
                
                <div class="consent_manager-cookiegroups uk-margin">
                    <?php
                    foreach ($consent_manager->cookiegroups as $cookiegroup) {
                        if (count($cookiegroup['cookie_uids']) >= 1) {
                            $isRequired = (bool) ($cookiegroup['required'] ?? false);
                            ?>
                            <div class="uk-margin-small">
                                <label class="uk-flex uk-flex-middle" style="cursor: pointer;">
                                    <input class="uk-checkbox uk-margin-small-right" type="checkbox" 
                                        id="<?= rex_escape($cookiegroup['uid']) ?>" 
                                        data-uid="<?= rex_escape($cookiegroup['uid']) ?>" 
                                        data-cookie-uids='<?= json_encode($cookiegroup['cookie_uids']) ?>'
                                        <?= $isRequired ? 'checked disabled data-action="toggle-cookie"' : 'tabindex="0"' ?>
                                    >
                                    <span class="uk-text-bold"><?= rex_escape($cookiegroup['name'] ?? '') ?></span>
                                    <?php if ($isRequired): ?>
                                        <span class="uk-label uk-label-success uk-margin-small-left" style="font-size: 0.6rem;"><?= rex_i18n::msg('consent_manager_cookiegroup_required') ?></span>
                                    <?php endif; ?>
                                </label>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>

                <div class="uk-margin">
                    <button id="consent_manager-toggle-details" class="uk-button uk-button-text uk-text-primary" aria-controls="consent_manager-detail" aria-expanded="false">
                        <?= $consent_manager->texts['toggle_details'] ?>
                    </button>
                </div>
            </div>

            <div class="consent_manager-detail consent_manager-hidden uk-margin-top" id="consent_manager-detail" aria-labelledby="consent_manager-toggle-details">
                <hr>
                <?php
                foreach ($consent_manager->cookiegroups as $cookiegroup) {
                    if (count($cookiegroup['cookie_uids']) >= 1) {
                        $countAll = 0;
                        if (isset($cookiegroup['cookie_uids'])) {
                            foreach ($cookiegroup['cookie_uids'] as $cookieUid) {
                                $countAll += count($consent_manager->cookies[$cookieUid]['definition'] ?? []);
                            }
                        }
                        ?>
                        <div class="uk-margin-medium-bottom">
                            <h4 class="uk-h5 uk-margin-remove-bottom">
                                <?= rex_escape($cookiegroup['name'] ?? '') ?> 
                                <span class="uk-text-muted uk-text-small">(<?= $countAll ?>)</span>
                            </h4>
                            <div class="uk-text-small uk-margin-small-top"><?= $cookiegroup['description'] ?? '' ?></div>
                            
                            <ul uk-accordion="multiple: true" class="uk-margin-small-top">
                                <?php
                                foreach ($cookiegroup['cookie_uids'] as $cookieUid) {
                                    if (isset($consent_manager->cookies[$cookieUid])) {
                                        $cookie = $consent_manager->cookies[$cookieUid];
                                        if (isset($cookie['definition'])) {
                                            foreach ($cookie['definition'] as $def) {
                                                ?>
                                                <li>
                                                    <a class="uk-accordion-title uk-text-small" href="#"><strong><?= rex_escape($def['cookie_name'] ?? '') ?></strong> <?= rex_escape($cookie['service_name'] ?? '') ?></a>
                                                    <div class="uk-accordion-content uk-text-small">
                                                        <p class="uk-margin-remove"><?= $def['description'] ?? '' ?></p>
                                                        <div class="uk-grid-small uk-child-width-1-2@s uk-margin-small-top" uk-grid>
                                                            <div><strong><?= $consent_manager->texts['lifetime'] ?? 'Laufzeit' ?>:</strong> <?= rex_escape($def['cookie_lifetime'] ?? '') ?></div>
                                                            <div><strong><?= $consent_manager->texts['provider'] ?? 'Anbieter' ?>:</strong> <?= rex_escape($cookie['provider'] ?? '') ?></div>
                                                        </div>
                                                        <?php if (isset($cookie['provider_link_privacy']) && '' !== $cookie['provider_link_privacy']): ?>
                                                        <div class="uk-margin-small-top">
                                                            <a href="<?= rex_escape($cookie['provider_link_privacy']) ?>" target="_blank" rel="noopener noreferrer nofollow" class="uk-button uk-button-link uk-button-small"><?= $consent_manager->texts['link_privacy'] ?? 'Datenschutz' ?></a>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </li>
                                                <?php
                                            }
                                        }
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            
            <div class="uk-card-footer uk-padding-remove-horizontal uk-margin-top">
                <div class="uk-grid-small uk-child-width-expand@s uk-flex-middle" uk-grid>
                    <?php if (isset($consent_manager->texts['button_select_none'])): ?>
                    <div>
                        <button tabindex="0" id="consent_manager-accept-none" class="consent_manager-accept-none consent_manager-close uk-button uk-button-primary uk-width-1-1"><?= $consent_manager->texts['button_select_none'] ?></button>
                    </div>
                    <?php endif; ?>
                    <div>
                        <button tabindex="0" id="consent_manager-save-selection" class="consent_manager-save-selection consent_manager-close uk-button uk-button-primary uk-width-1-1"><?= $consent_manager->texts['button_accept'] ?></button>
                    </div>
                    <div>
                        <button tabindex="0" id="consent_manager-accept-all" class="consent_manager-accept-all consent_manager-close uk-button uk-button-primary uk-width-1-1"><?= $consent_manager->texts['button_select_all'] ?></button>
                    </div>
                </div>
                
                <div class="uk-margin-small-top uk-text-center uk-text-meta">
                    <?php
                    $clang = rex_clang::getCurrentId();
                    foreach ($consent_manager->links as $v) {
                        $article = rex_article::get($v, $clang);
                        if ($article) {
                            echo '<a tabindex="0" href="' . rex_getUrl($v, $clang) . '" class="uk-link uk-margin-small-right">' . rex_escape($article->getName()) . '</a>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    foreach ($consent_manager->scripts as $uid => $script) {
        echo '<div class="consent_manager-script" data-uid="script-' . rex_escape($uid) . '" data-script="' . rex_escape($script, 'html_attr') . '"></div>';
    }
    foreach ($consent_manager->scriptsUnselect as $uid => $script) {
        echo '<div class="consent_manager-script" data-uid="script-unselect-' . rex_escape($uid) . '" data-script="' . rex_escape($script, 'html_attr') . '"></div>';
    }
    ?>
</div>
