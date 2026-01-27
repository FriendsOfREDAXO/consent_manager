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

// Schatten Mapping
$shadowClass = 'uk-box-shadow-xlarge';
if ($shadowType === 'none') {
    $shadowClass = '';
} elseif ($shadowType === 'small') {
    $shadowClass = 'uk-box-shadow-medium';
}

// Abrundung Mapping
$roundedClass = $roundedEnabled ? 'uk-border-rounded' : '';
$checkboxRoundedClass = $roundedEnabled ? 'uk-border-rounded' : '';

// Backdrop Style
$backdropStyle = $backdropEnabled ? 'background: rgba(0,0,0,0.6);' : 'background: transparent; pointer-events: none;';
?>

<div tabindex="-1" 
     class="consent_manager-background consent_manager-hidden uk-position-fixed uk-position-cover uk-flex uk-flex-center uk-flex-middle uk-padding <?= $consent_manager->boxClass ?>" 
     id="consent_manager-background" 
     style="z-index: 1000000; <?= $backdropStyle ?>"
     data-domain-name="<?= $consent_manager->domainName ?>" 
     data-version="<?= $consent_manager->version ?>" 
     data-consentid="<?= uniqid('', true) ?>" 
     data-cachelogid="<?= $consent_manager->cacheLogId ?>" 
     data-nosnippet aria-hidden="true">

    <div class="consent_manager-wrapper uk-card uk-card-default uk-width-1-1 uk-width-2-3@s uk-width-1-2@m uk-position-relative uk-overflow-hidden uk-flex uk-flex-column <?= $shadowClass ?> <?= $roundedClass ?>" 
         id="consent_manager-wrapper" 
         style="max-width: 720px; max-height: 90vh; pointer-events: auto;"
         tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="consent_manager-headline">
        
        <button tabindex="0" class="consent_manager-close-box consent_manager-close uk-position-top-right uk-padding-small uk-close-large" 
                style="top: 10px; right: 10px; z-index: 10;"
                aria-label="Close" type="button">
            <svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><line fill="none" stroke="#000" stroke-width="2" x1="1" y1="1" x2="19" y2="19"></line><line fill="none" stroke="#000" stroke-width="2" x1="19" y1="1" x2="1" y2="19"></line></svg>
        </button>

        <div class="uk-card-header uk-padding border-0" style="padding-top: 50px !important;">
            <h2 class="uk-h3 uk-margin-remove-bottom uk-text-bold" id="consent_manager-headline"><?= $consent_manager->texts['headline'] ?></h2>
        </div>
        
        <div class="consent_manager-wrapper-inner uk-card-body uk-padding uk-overflow-auto uk-flex-1">
            <div class="uk-text-meta uk-margin-bottom uk-text-small"><?= nl2br($consent_manager->texts['description']) ?></div>
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
                                        <input class="uk-checkbox uk-margin-small-right <?= $checkboxRoundedClass ?>" type="checkbox" 
                                            id="<?= $groupUid ?>" 
                                            data-uid="<?= $groupUid ?>" 
                                            data-cookie-uids='<?= json_encode($cookiegroup['cookie_uids']) ?>'
                                            <?= $isRequired ? 'checked disabled data-action="toggle-cookie"' : 'tabindex="0"' ?>
                                        >
                                        <span class="uk-text-bold uk-text-small"><?= rex_escape($cookiegroup['name'] ?? '') ?></span>
                                        <?php if ($isRequired): ?>
                                            <span class="uk-text-meta uk-margin-small-left" style="font-size: 0.7rem;">(<?= rex_i18n::msg('consent_manager_cookiegroup_required') ?>)</span>
                                        <?php endif; ?>
                                    </label>
                                    
                                    <a class="uk-button uk-button-text uk-text-primary uk-text-bold" style="font-size: 0.7rem;" href="#details-<?= $groupUid ?>" uk-toggle>
                                        <?= $consent_manager->texts['toggle_details'] ?>
                                    </a>
                                </div>

                                <div id="details-<?= $groupUid ?>" class="uk-margin-small-top uk-card uk-card-default uk-card-body uk-card-small uk-background-muted <?= $roundedClass ?>" style="box-shadow: none; border: 1px solid #eee;" hidden>
                                    <div class="uk-margin-small-bottom uk-text-meta uk-text-small"><?= $cookiegroup['description'] ?? '' ?></div>
                                    
                                    <ul uk-accordion="multiple: true" class="uk-margin-remove uk-accordion-divider">
                                        <?php
                                        foreach ($cookiegroup['cookie_uids'] as $cookieUid) {
                                            if (isset($consent_manager->cookies[$cookieUid])) {
                                                $cookie = $consent_manager->cookies[$cookieUid];
                                                $title = ($cookie['service_name'] !== '') ? $cookie['service_name'] : $cookie['provider'];
                                                ?>
                                                <li class="uk-margin-small-bottom" style="border: 1px solid #eee; background: #fff; <?= $roundedEnabled ? 'border-radius: 6px; overflow: hidden;' : '' ?>">
                                                    <a class="uk-accordion-title uk-text-default uk-text-bold" href="#" style="font-size: 0.8rem; padding: 10px 15px; line-height: 1.4;">
                                                        <?= rex_escape($title) ?>
                                                    </a>
                                                    <div class="uk-accordion-content uk-text-small" style="padding: 0 15px 15px 15px; margin-top: 0;">
                                                        <div class="uk-margin-small-bottom">
                                                            <strong><?= $consent_manager->texts['provider'] ?? 'Anbieter' ?>:</strong> <?= rex_escape($cookie['provider'] ?? '') ?>
                                                        </div>
                                                        
                                                        <div class="uk-margin-small-bottom text-muted">
                                                            <?= $cookie['description'] ?? '' ?>
                                                        </div>
                                                        
                                                        <?php if (isset($cookie['provider_link_privacy']) && '' !== $cookie['provider_link_privacy']): ?>
                                                        <div class="uk-margin-small-bottom">
                                                            <a href="<?= rex_escape($cookie['provider_link_privacy']) ?>" target="_blank" rel="noopener noreferrer nofollow" class="uk-button uk-button-link uk-text-primary uk-text-bold" style="font-size: 0.7rem;"><?= $consent_manager->texts['link_privacy'] ?? 'Datenschutz' ?></a>
                                                        </div>
                                                        <?php endif; ?>

                                                        <?php if (isset($cookie['definition']) && count($cookie['definition']) > 0): ?>
                                                            <div class="uk-margin-small-top uk-padding-small uk-background-default" style="border-top: 1px solid #eee;">
                                                                <div class="uk-text-meta uk-text-uppercase uk-margin-small-bottom uk-text-bold" style="letter-spacing: 0.5px; font-size: 0.65rem;"><?= $consent_manager->texts['cookie_name'] ?? 'Cookies' ?></div>
                                                                
                                                                <?php foreach ($cookie['definition'] as $def): ?>
                                                                    <div class="uk-margin-small-bottom">
                                                                        <code style="color: #d05d41; background: #fdfafa; padding: 2px 4px; font-size: 0.75rem;"><?= rex_escape($def['cookie_name'] ?? '') ?></code>
                                                                        <span class="uk-text-meta uk-margin-small-left" style="font-size: 0.7rem;">(<?= rex_escape($def['cookie_lifetime'] ?? '') ?>)</span>
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
            
            <div class="uk-card-footer uk-padding-remove-horizontal uk-margin-top border-0">
                <div class="uk-grid-small uk-child-width-expand@s uk-flex-middle" uk-grid>
                    <div>
                        <button tabindex="0" id="consent_manager-accept-all" class="consent_manager-accept-all consent_manager-close uk-button uk-button-primary uk-button-small uk-width-1-1 <?= $roundedClass ?>"><?= $consent_manager->texts['button_select_all'] ?></button>
                    </div>
                    <?php if (isset($consent_manager->texts['button_select_none'])): ?>
                    <div class="uk-flex-first@s">
                        <button tabindex="0" id="consent_manager-accept-none" class="consent_manager-accept-none consent_manager-close uk-button uk-button-primary uk-button-small uk-width-1-1 <?= $roundedClass ?>"><?= $consent_manager->texts['button_select_none'] ?></button>
                    </div>
                    <?php endif; ?>
                    <div class="uk-flex-last@s">
                        <button tabindex="0" id="consent_manager-save-selection" class="consent_manager-save-selection consent_manager-close uk-button uk-button-primary uk-button-small uk-width-1-1 <?= $roundedClass ?>"><?= $consent_manager->texts['button_accept'] ?></button>
                    </div>
                </div>
                
                <div class="uk-margin-top uk-text-center uk-text-meta uk-text-small">
                    <?php
                    $clang = rex_clang::getCurrentId();
                    foreach ($consent_manager->links as $v) {
                        $article = rex_article::get($v, $clang);
                        if ($article instanceof rex_article) {
                            echo '<a tabindex="0" href="' . rex_getUrl($v, $clang) . '" class="uk-link-muted uk-margin-small-right">' . rex_escape($article->getName()) . '</a>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <style nonce="<?= rex_response::getNonce() ?>">
        #consent_manager-background:not(.consent_manager-hidden) { display: flex !important; }
        .consent_manager-hidden { display: none !important; }
        .uk-accordion-title::after {
            content: ""; width: 1.4em; height: 1.4em; float: right;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2214%22%20height%3D%2214%22%20viewBox%3D%220%200%2014%2014%22%20xmlns%3D%22http%3D%22//www.w3.org/2000/svg%22%3E%3Cline%20fill%3D%22none%22%20stroke%3D%22%23666%22%20stroke-width%3D%221.1%22%20x1%3D%227%22%20y1%3D%221%22%20x2%3D%227%22%20y2%3D%2213%22%3E%3C/line%3E%3Cline%20fill%3D%22none%22%20stroke%3D%22%23666%22%20stroke-width%3D%221.1%22%20x1%3D%221%22%20y1%3D%227%22%20x2%3D%2213%22%20y2%3D%227%22%3E%3C/line%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: 50% 50%;
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

