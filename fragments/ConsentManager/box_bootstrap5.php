<?php
use FriendsOfRedaxo\ConsentManager\Frontend;

$consent_manager = new Frontend(0);
if (is_string(rex_request::server('HTTP_HOST'))) {
    $consent_manager->setDomain(rex_request::server('HTTP_HOST'));
}

if (0 === count($consent_manager->texts)) {
    echo '<div id="consent_manager-background">' . rex_view::error(rex_addon::get('consent_manager')->i18n('consent_manager_error_noconfig')) . '</div>';
    return;
}

$addon = rex_addon::get('consent_manager');
$backdropEnabled = $addon->getConfig('backdrop', '1') !== '0';
$shadowType = $addon->getConfig('css_framework_shadow', 'large');
$roundedEnabled = $addon->getConfig('css_framework_rounded', '1') === '1';

// Schatten Mapping (Bootstrap 5 nutzt Klassen, aber für präzise Steuerung nutzen wir hier Styles oder Utility-Klassen)
$shadowClass = 'shadow-lg';
if ($shadowType === 'none') {
    $shadowClass = 'shadow-none';
} elseif ($shadowType === 'small') {
    $shadowClass = 'shadow-sm';
}

$roundedClass = $roundedEnabled ? 'rounded-4' : 'rounded-0';
$buttonRoundedClass = $roundedEnabled ? 'rounded-3' : 'rounded-0';

// Backdrop Style
$backdropStyle = $backdropEnabled ? 'background: rgba(0,0,0,0.6);' : 'background: transparent; pointer-events: none;';
?>

<div tabindex="-1" 
     class="consent_manager-background consent_manager-hidden position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-3 <?= $consent_manager->boxClass ?>" 
     id="consent_manager-background" 
     style="z-index: 1000000; <?= $backdropStyle ?>"
     data-domain-name="<?= $consent_manager->domainName ?>" 
     data-version="<?= $consent_manager->version ?>" 
     data-consentid="<?= uniqid('', true) ?>" 
     data-cachelogid="<?= $consent_manager->cacheLogId ?>" 
     data-nosnippet aria-hidden="true">
    
    <div class="consent_manager-wrapper card border-0 w-100 position-relative d-flex flex-column overflow-hidden <?= $shadowClass ?> <?= $roundedClass ?>" 
         id="consent_manager-wrapper" 
         style="max-width: 720px; max-height: 90vh; pointer-events: auto;"
         tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="consent_manager-headline">
        
        <button tabindex="0" class="btn-close consent_manager-close position-absolute shadow-none" 
                style="top: 1.5rem; right: 1.5rem; z-index: 10;"
                aria-label="Close" type="button"></button>
        
        <div class="card-header bg-white border-0 pt-5 px-4 pb-0">
            <h2 class="card-title h4 mb-0 fw-bold" id="consent_manager-headline"><?= $consent_manager->texts['headline'] ?></h2>
        </div>

        <div class="consent_manager-wrapper-inner card-body p-4 overflow-auto flex-grow-1">
            <div class="text-muted small mb-4"><?= nl2br($consent_manager->texts['description']) ?></div>
            <div class="consent_manager-cookiegroups">
                <?php
                foreach ($consent_manager->cookiegroups as $cookiegroup) {
                    if (count($cookiegroup['cookie_uids']) >= 1) {
                        $isRequired = (bool) ($cookiegroup['required'] ?? false);
                        $groupUid = rex_escape($cookiegroup['uid']);
                        ?>
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="form-check mb-0">
                                    <input class="form-check-input shadow-none <?= $roundedEnabled ? '' : 'rounded-0' ?>" type="checkbox" 
                                        id="<?= $groupUid ?>" 
                                        data-uid="<?= $groupUid ?>" 
                                        data-cookie-uids='<?= json_encode($cookiegroup['cookie_uids']) ?>'
                                        <?= $isRequired ? 'checked disabled data-action="toggle-cookie"' : 'tabindex="0"' ?>
                                    >
                                    <label class="form-check-label fw-bold ms-2" for="<?= $groupUid ?>">
                                        <?= rex_escape($cookiegroup['name'] ?? '') ?>
                                        <?php if ($isRequired): ?>
                                            <span class="text-muted small ms-1 fw-normal">(<?= rex_i18n::msg('consent_manager_cookiegroup_required') ?>)</span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                                <button class="btn btn-link btn-sm text-primary text-decoration-none fw-bold p-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#details-<?= $groupUid ?>" aria-expanded="false">
                                    <?= $consent_manager->texts['toggle_details'] ?>
                                </button>
                            </div>

                            <div class="collapse mt-2" id="details-<?= $groupUid ?>">
                                <div class="card card-body card-details border-0 p-3 bg-light shadow-none <?= $roundedEnabled ? 'rounded-3' : 'rounded-0' ?>">
                                    <div class="small mb-3 text-secondary"><?= $cookiegroup['description'] ?? '' ?></div>
                                    
                                    <div class="accordion accordion-flush bg-transparent" id="accordion-<?= $groupUid ?>">
                                        <?php
                                        foreach ($cookiegroup['cookie_uids'] as $cookieUid) {
                                            if (isset($consent_manager->cookies[$cookieUid])) {
                                                $cookie = $consent_manager->cookies[$cookieUid];
                                                $title = ($cookie['service_name'] !== '') ? $cookie['service_name'] : $cookie['provider'];
                                                $accId = 'acc-' . $groupUid . '-' . $cookieUid;
                                                ?>
                                                <div class="accordion-item mb-2 border bg-white <?= $roundedEnabled ? 'rounded-2 overflow-hidden' : '' ?>">
                                                    <h3 class="accordion-header">
                                                        <button class="accordion-button collapsed py-2 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#content-<?= $accId ?>">
                                                            <?= rex_escape($title) ?>
                                                        </button>
                                                    </h3>
                                                    <div id="content-<?= $accId ?>" class="accordion-collapse collapse" data-bs-parent="#accordion-<?= $groupUid ?>">
                                                        <div class="accordion-body small pt-1 pb-3">
                                                            <div class="mb-2 mt-2">
                                                                <strong><?= $consent_manager->texts['provider'] ?? 'Anbieter' ?>:</strong> <?= rex_escape($cookie['provider'] ?? '') ?>
                                                            </div>
                                                            <div class="mb-2"><?= $cookie['description'] ?? '' ?></div>
                                                            
                                                            <?php if (isset($cookie['provider_link_privacy']) && '' !== $cookie['provider_link_privacy']): ?>
                                                                <div class="mb-2">
                                                                    <a href="<?= rex_escape($cookie['provider_link_privacy']) ?>" target="_blank" rel="noopener noreferrer nofollow" class="btn btn-link btn-sm p-0 text-primary fw-bold text-decoration-none"><?= $consent_manager->texts['link_privacy'] ?? 'Datenschutz' ?></a>
                                                                </div>
                                                            <?php endif; ?>

                                                            <?php if (isset($cookie['definition']) && count($cookie['definition']) > 0): ?>
                                                                <div class="mt-3 pt-3 border-top border-light-subtle">
                                                                    <div class="text-uppercase fw-bold text-muted mb-2" style="font-size: 0.7rem; letter-spacing: 0.5px;"><?= $consent_manager->texts['cookie_name'] ?? 'Cookies' ?></div>
                                                                    <?php foreach ($cookie['definition'] as $def): ?>
                                                                        <div class="mb-2">
                                                                            <code class="px-1 text-danger bg-danger-subtle rounded-1"><?= rex_escape($def['cookie_name'] ?? '') ?></code>
                                                                            <span class="text-muted small ms-1">(<?= rex_escape($def['cookie_lifetime'] ?? '') ?>)</span>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>

        <div class="card-footer bg-white border-top-0 px-4 pb-4 pt-0">
            <div class="row g-2">
                <div class="col-sm order-2 order-sm-1">
                    <button tabindex="0" id="consent_manager-accept-all" class="consent_manager-accept-all consent_manager-close btn btn-primary w-100 py-3 fw-bold text-uppercase tracking-wider shadow-sm <?= $buttonRoundedClass ?>" style="font-size: 0.75rem;"><?= $consent_manager->texts['button_select_all'] ?></button>
                </div>
                <?php if (isset($consent_manager->texts['button_select_none'])): ?>
                <div class="col-sm order-1 order-sm-2">
                    <button tabindex="0" id="consent_manager-accept-none" class="consent_manager-accept-none consent_manager-close btn btn-primary w-100 py-3 fw-bold text-uppercase tracking-wider shadow-sm <?= $buttonRoundedClass ?>" style="font-size: 0.75rem;"><?= $consent_manager->texts['button_select_none'] ?></button>
                </div>
                <?php endif; ?>
                <div class="col-sm order-3 order-sm-3">
                    <button tabindex="0" id="consent_manager-save-selection" class="consent_manager-save-selection consent_manager-close btn btn-primary w-100 py-3 fw-bold text-uppercase tracking-wider shadow-sm <?= $buttonRoundedClass ?>" style="font-size: 0.75rem;"><?= $consent_manager->texts['button_accept'] ?></button>
                </div>
            </div>
            
            <div class="mt-4 text-center">
                <?php
                $clang = rex_clang::getCurrentId();
                foreach ($consent_manager->links as $v) {
                    $article = rex_article::get($v, $clang);
                    if ($article instanceof rex_article) {
                        echo '<a tabindex="0" href="' . rex_getUrl($v, $clang) . '" class="text-muted text-decoration-none mx-2 small hover-underline">' . rex_escape($article->getName()) . '</a>';
                    }
                }
                ?>
            </div>
        </div>
    </div>
    
    <style nonce="<?= rex_response::getNonce() ?>">
        #consent_manager-background:not(.consent_manager-hidden) { display: flex !important; }
        .consent_manager-hidden { display: none !important; }
        @media (max-width: 576px) { 
            #consent_manager-wrapper { 
                max-height: 100vh !important; 
                height: 100vh !important; 
                border-radius: 0 !important; 
            } 
        }
    </style>

    
    <?php
    foreach ($consent_manager->scripts as $uid => $script) {
        echo '<div class="consent_manager-script" data-uid="script-' . rex_escape($uid) . '" data-script="' . rex_escape($script, 'html_attr') . '"></div>';
    }
    foreach ($consent_manager->scriptsUnselect as $uid => $script) {
        echo '<div class="consent_manager-script" data-uid="script-unselect-' . rex_escape($uid) . '" data-script="' . rex_escape($script, 'html_attr') . '"></div>';
    }
    ?>
    <div id="consent_manager-detail" class="consent_manager-hidden" hidden></div>
</div>
