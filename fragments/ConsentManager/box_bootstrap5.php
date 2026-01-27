<?php

/**
 * Bootstrap 5 Framework Fragment für Consent Manager
 * (Platzhalter - aktuell identisch mit box.php mit Bootstrap Klassen)
 */

use FriendsOfRedaxo\ConsentManager\Frontend;

$consent_manager = new Frontend(0);
if (is_string(rex_request::server('HTTP_HOST'))) {
    $consent_manager->setDomain(rex_request::server('HTTP_HOST'));
}

if (0 === count($consent_manager->texts)) {
    echo '<div id="consent_manager-background"><div class="alert alert-danger">' . rex_addon::get('consent_manager')->i18n('consent_manager_error_noconfig') . '</div></div>';
    return;
}

if (0 >= count($consent_manager->cookiegroups)) {
    return;
}
?>

<style nonce="<?= rex_response::getNonce() ?>">
#consent_manager-background {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    z-index: 999999;
}
#consent_manager-background.consent_manager-hidden {
    display: none !important;
}
#consent_manager-wrapper {
    background: #fff;
    border-radius: 0.375rem;
    max-width: 800px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}
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
    <div class="consent_manager-wrapper card" id="consent_manager-wrapper" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="consent_manager-headline">
        <button tabindex="0" class="btn-close consent_manager-close consent_manager-close-box" aria-label="Close"></button>
        
        <div class="consent_manager-wrapper-inner card-body">
            <h2 class="card-title h4 mb-3" id="consent_manager-headline"><?= $consent_manager->texts['headline'] ?></h2>
            <div class="card-text mb-4"><?= nl2br($consent_manager->texts['description']) ?></div>
            
            <div class="consent_manager-cookiegroups mb-4">
                <?php
                foreach ($consent_manager->cookiegroups as $cookiegroup) {
                    if (count($cookiegroup['cookie_uids']) >= 1) {
                        $isRequired = (bool) ($cookiegroup['required'] ?? false);
                        ?>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" 
                                id="<?= rex_escape($cookiegroup['uid']) ?>" 
                                data-uid="<?= rex_escape($cookiegroup['uid']) ?>" 
                                data-cookie-uids='<?= json_encode($cookiegroup['cookie_uids']) ?>'
                                <?= $isRequired ? 'checked disabled data-action="toggle-cookie"' : 'tabindex="0"' ?>
                            >
                            <label class="form-check-label fw-bold" for="<?= rex_escape($cookiegroup['uid']) ?>">
                                <?= rex_escape($cookiegroup['name'] ?? '') ?>
                                <?php if ($isRequired): ?>
                                    <span class="badge bg-success ms-2" style="font-size: 0.6rem;"><?= rex_i18n::msg('consent_manager_cookiegroup_required') ?></span>
                                <?php endif; ?>
                            </label>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>

            <div class="mb-4">
                <button id="consent_manager-toggle-details" class="btn btn-link p-0 text-decoration-none" aria-controls="consent_manager-detail" aria-expanded="false">
                    <?= $consent_manager->texts['toggle_details'] ?>
                </button>
            </div>

            <div class="consent_manager-detail consent_manager-hidden" id="consent_manager-detail" aria-labelledby="consent_manager-toggle-details">
                <hr>
                <?php
                foreach ($consent_manager->cookiegroups as $cookiegroup) {
                    if (count($cookiegroup['cookie_uids']) >= 1) {
                        ?>
                        <div class="mb-4">
                            <h5 class="h6 mb-2"><?= rex_escape($cookiegroup['name'] ?? '') ?></h5>
                            <div class="small mb-3"><?= $cookiegroup['description'] ?? '' ?></div>
                            
                            <div class="accordion accordion-flush" id="accordion-<?= rex_escape($cookiegroup['uid']) ?>">
                                <?php
                                foreach ($cookiegroup['cookie_uids'] as $cookieUid) {
                                    if (isset($consent_manager->cookies[$cookieUid])) {
                                        $cookie = $consent_manager->cookies[$cookieUid];
                                        foreach (($cookie['definition'] ?? []) as $index => $def) {
                                            $id = rex_escape($cookiegroup['uid'] . '-' . $cookieUid . '-' . $index);
                                            ?>
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed py-2 small fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $id ?>">
                                                        <?= rex_escape($def['cookie_name'] ?? '') ?> <?= rex_escape($cookie['service_name'] ?? '') ?>
                                                    </button>
                                                </h2>
                                                <div id="collapse-<?= $id ?>" class="accordion-collapse collapse" data-bs-parent="#accordion-<?= rex_escape($cookiegroup['uid']) ?>">
                                                    <div class="accordion-body small">
                                                        <p class="mb-2"><?= $def['description'] ?? '' ?></p>
                                                        <div class="row g-2">
                                                            <div class="col-6"><strong><?= $consent_manager->texts['lifetime'] ?? 'Laufzeit' ?>:</strong> <?= rex_escape($def['cookie_lifetime'] ?? '') ?></div>
                                                            <div class="col-6"><strong><?= $consent_manager->texts['provider'] ?? 'Anbieter' ?>:</strong> <?= rex_escape($cookie['provider'] ?? '') ?></div>
                                                        </div>
                                                        <?php if (isset($cookie['provider_link_privacy']) && '' !== $cookie['provider_link_privacy']): ?>
                                                        <div class="mt-2">
                                                            <a href="<?= rex_escape($cookie['provider_link_privacy']) ?>" target="_blank" rel="noopener noreferrer nofollow" class="btn btn-link btn-sm p-0"><?= $consent_manager->texts['link_privacy'] ?? 'Datenschutz' ?></a>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            
            <div class="card-footer bg-transparent px-0 border-top-0">
                <div class="row g-2">
                    <?php if (isset($consent_manager->texts['button_select_none'])): ?>
                    <div class="col-sm">
                        <button tabindex="0" id="consent_manager-accept-none" class="consent_manager-accept-none consent_manager-close btn btn-outline-secondary w-100"><?= $consent_manager->texts['button_select_none'] ?></button>
                    </div>
                    <?php endif; ?>
                    <div class="col-sm">
                        <button tabindex="0" id="consent_manager-save-selection" class="consent_manager-save-selection consent_manager-close btn btn-primary w-100"><?= $consent_manager->texts['button_accept'] ?></button>
                    </div>
                    <div class="col-sm">
                        <button tabindex="0" id="consent_manager-accept-all" class="consent_manager-accept-all consent_manager-close btn btn-primary w-100"><?= $consent_manager->texts['button_select_all'] ?></button>
                    </div>
                </div>
                
                <div class="mt-3 text-center small">
                    <?php
                    $clang = rex_clang::getCurrentId();
                    foreach ($consent_manager->links as $v) {
                        $article = rex_article::get($v, $clang);
                        if ($article) {
                            echo '<a tabindex="0" href="' . rex_getUrl($v, $clang) . '" class="text-muted me-3">' . rex_escape($article->getName()) . '</a>';
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
