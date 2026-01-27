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

<div tabindex="-1" class="consent_manager-background consent_manager-hidden <?= $consent_manager->boxClass ?>" id="consent_manager-background" data-domain-name="<?= $consent_manager->domainName ?>" data-version="<?= $consent_manager->version ?>" data-consentid="<?= uniqid('', true) ?>" data-cachelogid="<?= $consent_manager->cacheLogId ?>" data-nosnippet aria-hidden="true">
<style nonce="<?= rex_response::getNonce() ?>">
/* Glue-CSS für UIkit Integration */
#consent_manager-background {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    background: <?= (bool) rex_addon::get('consent_manager')->getConfig('backdrop', true) ? 'rgba(0, 0, 0, 0.6)' : 'transparent' ?> !important;
    display: none; /* Standardmäßig aus, JS schaltet es ein */
    align-items: center;
    justify-content: center;
    padding: 1rem;
    z-index: 1000000 !important; /* Über UIkit Modals (1010) */
    <?php if (!(bool) rex_addon::get('consent_manager')->getConfig('backdrop', true)): ?>
    pointer-events: none !important;
    <?php endif; ?>
}
#consent_manager-background:not(.consent_manager-hidden) {
    display: flex !important;
}
#consent_manager-background.consent_manager-hidden {
    display: none !important;
}
#consent_manager-wrapper {
    <?php if (!(bool) rex_addon::get('consent_manager')->getConfig('backdrop', true)): ?>
    pointer-events: auto !important;
    <?php endif; ?>
    max-width: 800px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    border-radius: 0 !important;
    animation: uk-fade-bottom 0.4s ease-out;
}
@media (max-width: 640px) {
    #consent_manager-wrapper {
        max-height: 100vh;
        height: 100vh;
        border-radius: 0 !important;
    }
}
.consent_manager-hidden {
    display: none !important;
}
.consent_manager-close-box {
    position: absolute;
    top: 20px;
    right: 20px;
    border: none;
    background: transparent;
    cursor: pointer;
    z-index: 10;
    padding: 5px;
    transition: 0.2s ease-in-out;
}
.consent_manager-close-box:hover {
    opacity: 0.6;
}
.uk-button {
    border-radius: 0 !important;
}
.uk-checkbox {
    border-radius: 0 !important;
}
@keyframes uk-fade-bottom {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
}
/* Accordion Icon Fix (Plus/Minus) */
.uk-accordion-title::after {
    content: "";
    width: 1.4em;
    height: 1.4em;
    float: right;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2214%22%20height%3D%2214%22%20viewBox%3D%220%200%2014%2014%22%20xmlns%3D%22http%3D%22//www.w3.org/2000/svg%22%3E%3Cline%20fill%3D%22none%22%20stroke%3D%22%23666%22%20stroke-width%3D%221.1%22%20x1%3D%227%22%20y1%3D%221%22%20x2%3D%227%22%20y2%3D%2213%22%3E%3C/line%3E%3Cline%20fill%3D%22none%22%20stroke%3D%22%23666%22%20stroke-width%3D%221.1%22%20x1%3D%221%22%20y1%3D%227%22%20x2%3D%2213%22%20y2%3D%227%22%3E%3C/line%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: 50% 50%;
}
.uk-accordion-title {
    line-height: 1.4;
    padding: 10px 15px;
    background: #fff;
}
.uk-accordion-divider > li {
    border: 1px solid #eeeeee83;
    margin-bottom: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.uk-accordion-content {
    padding: 0 15px 15px 15px;
    margin-top: 0;
}
</style>
    <div class="consent_manager-wrapper uk-card uk-card-default" id="consent_manager-wrapper" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="consent_manager-headline">
        <button tabindex="0" class="consent_manager-close-box consent_manager-close uk-close-large" aria-label="Close" type="button">
            <svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><line fill="none" stroke="#000" stroke-width="1.4" x1="1" y1="1" x2="19" y2="19"></line><line fill="none" stroke="#000" stroke-width="1.4" x1="19" y1="1" x2="1" y2="19"></line></svg>
        </button>

        <div class="uk-card-header">
            <h2 class="uk-h3 uk-margin-remove-bottom" id="consent_manager-headline"><?= $consent_manager->texts['headline'] ?></h2>
            <div class="uk-text-meta uk-margin-small-top"><?= nl2br($consent_manager->texts['description']) ?></div>
        </div>
        
        <div class="consent_manager-wrapper-inner uk-card-body">
            <div class="consent_manager-summary" id="consent_manager-summary">
                <div class="consent_manager-cookiegroups">
                    <?php
                    foreach ($consent_manager->cookiegroups as $cookiegroup) {
                        if (count($cookiegroup['cookie_uids']) >= 1) {
                            $isRequired = (bool) ($cookiegroup['required'] ?? false);
                            $groupUid = rex_escape($cookiegroup['uid']);
                            ?>
                            <div class="uk-margin-bottom">
                                <div class="uk-flex uk-flex-middle uk-flex-between">
                                    <label class="uk-flex uk-flex-middle">
                                        <input class="uk-checkbox uk-margin-small-right" type="checkbox" 
                                            id="<?= $groupUid ?>" 
                                            data-uid="<?= $groupUid ?>" 
                                            data-cookie-uids='<?= json_encode($cookiegroup['cookie_uids']) ?>'
                                            <?= $isRequired ? 'checked disabled data-action="toggle-cookie"' : 'tabindex="0"' ?>
                                        >
                                        <span class="uk-text-bold"><?= rex_escape($cookiegroup['name'] ?? '') ?></span>
                                        <?php if ($isRequired): ?>
                                            <span class="uk-text-meta uk-margin-small-left">(<?= rex_i18n::msg('consent_manager_cookiegroup_required') ?>)</span>
                                        <?php endif; ?>
                                    </label>
                                    
                                    <a class="uk-button uk-button-text uk-text-primary" href="#details-<?= $groupUid ?>" uk-toggle>
                                        <?= $consent_manager->texts['toggle_details'] ?>
                                    </a>
                                </div>

                                <div id="details-<?= $groupUid ?>" class="uk-margin-small-top uk-card uk-card-default uk-card-body uk-card-small" hidden>
                                    <div class="uk-margin-small-bottom"><?= $cookiegroup['description'] ?? '' ?></div>
                                    
                                    <ul uk-accordion="multiple: true" class="uk-margin-remove uk-accordion-divider">
                                        <?php
                                        foreach ($cookiegroup['cookie_uids'] as $cookieUid) {
                                            if (isset($consent_manager->cookies[$cookieUid])) {
                                                $cookie = $consent_manager->cookies[$cookieUid];
                                                $title = ($cookie['service_name'] !== '') ? $cookie['service_name'] : $cookie['provider'];
                                                ?>
                                                <li>
                                                    <a class="uk-accordion-title uk-text-default uk-text-bold" href="#">
                                                        <?= rex_escape($title) ?>
                                                    </a>
                                                    <div class="uk-accordion-content">
                                                        <div class="uk-margin-small-bottom">
                                                            <strong><?= $consent_manager->texts['provider'] ?? 'Anbieter' ?>:</strong> <?= rex_escape($cookie['provider'] ?? '') ?>
                                                        </div>
                                                        
                                                        <div class="uk-margin-small-bottom">
                                                            <?= $cookie['description'] ?? '' ?>
                                                        </div>
                                                        
                                                        <?php if (isset($cookie['provider_link_privacy']) && '' !== $cookie['provider_link_privacy']): ?>
                                                        <div class="uk-margin-small-bottom">
                                                            <a href="<?= rex_escape($cookie['provider_link_privacy']) ?>" target="_blank" rel="noopener noreferrer nofollow" class="uk-button uk-button-link uk-text-primary"><?= $consent_manager->texts['link_privacy'] ?? 'Datenschutz-Informationen' ?></a>
                                                        </div>
                                                        <?php endif; ?>

                                                        <?php if (isset($cookie['definition']) && count($cookie['definition']) > 0): ?>
                                                            <div class="uk-margin-small-top uk-padding-small uk-background-default" style="border-top: 1px dashed #eee;">
                                                                <div class="uk-text-meta uk-text-uppercase uk-margin-small-bottom uk-text-bold" style="letter-spacing: 0.5px;"><?= $consent_manager->texts['cookie_name'] ?? 'Cookies' ?></div>
                                                                
                                                                <?php foreach ($cookie['definition'] as $def): ?>
                                                                    <div class="uk-margin-small-bottom uk-text-small">
                                                                        <code style="color: #d05d41; background: #fdfafa; padding: 2px 4px;"><?= rex_escape($def['cookie_name'] ?? '') ?></code>
                                                                        <span class="uk-text-meta uk-margin-small-left">(<?= rex_escape($def['cookie_lifetime'] ?? '') ?>)</span>
                                                                        <?php if (isset($def['description']) && $def['description'] !== '' && $def['description'] !== $cookie['description']): ?>
                                                                            <div class="uk-margin-xsmall-top uk-text-muted" style="font-size: 0.85rem; padding-left: 5px; border-left: 2px solid #eee;"><?= $def['description'] ?></div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </li>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
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
                        if ($article instanceof rex_article) {
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
    <!-- Dummy für JavaScript-Kompatibilität (verhindert Fehler beim Schließen) -->
    <div id="consent_manager-detail" class="consent_manager-hidden" hidden></div>
</div>
