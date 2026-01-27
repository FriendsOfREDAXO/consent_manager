<?php

/**
 * Tailwind CSS Framework Fragment für Consent Manager
 * (Platzhalter - aktuell identisch mit box.php mit Tailwind Utilities)
 */

use FriendsOfRedaxo\ConsentManager\Frontend;

$consent_manager = new Frontend(0);
if (is_string(rex_request::server('HTTP_HOST'))) {
    $consent_manager->setDomain(rex_request::server('HTTP_HOST'));
}

if (0 === count($consent_manager->texts)) {
    echo '<div id="consent_manager-background"><div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">' . rex_addon::get('consent_manager')->i18n('consent_manager_error_noconfig') . '</div></div>';
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
    border-radius: 0.5rem;
    max-width: 800px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
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
    <div class="consent_manager-wrapper bg-white" id="consent_manager-wrapper" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="consent_manager-headline">
        <button tabindex="0" class="consent_manager-close consent_manager-close-box text-gray-400 hover:text-gray-500" aria-label="Close">&#10006;</button>
        
        <div class="p-6">
            <h2 class="text-xl font-bold mb-2 text-gray-900" id="consent_manager-headline"><?= $consent_manager->texts['headline'] ?></h2>
            <div class="text-gray-600 mb-6 text-sm"><?= nl2br($consent_manager->texts['description']) ?></div>
            
            <div class="space-y-3 mb-8">
                <?php
                foreach ($consent_manager->cookiegroups as $cookiegroup) {
                    if (count($cookiegroup['cookie_uids']) >= 1) {
                        $isRequired = (bool) ($cookiegroup['required'] ?? false);
                        ?>
                        <div class="flex items-center">
                            <input class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" type="checkbox" 
                                id="<?= rex_escape($cookiegroup['uid']) ?>" 
                                data-uid="<?= rex_escape($cookiegroup['uid']) ?>" 
                                data-cookie-uids='<?= json_encode($cookiegroup['cookie_uids']) ?>'
                                <?= $isRequired ? 'checked disabled data-action="toggle-cookie"' : 'tabindex="0"' ?>
                            >
                            <label class="ml-2 block text-sm font-medium text-gray-900" for="<?= rex_escape($cookiegroup['uid']) ?>">
                                <?= rex_escape($cookiegroup['name'] ?? '') ?>
                                <?php if ($isRequired): ?>
                                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800" style="font-size: 0.6rem;"><?= rex_i18n::msg('consent_manager_cookiegroup_required') ?></span>
                                <?php endif; ?>
                            </label>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>

            <div class="mb-6">
                <button id="consent_manager-toggle-details" class="text-blue-600 hover:text-blue-500 text-sm font-medium focus:outline-none" aria-controls="consent_manager-detail" aria-expanded="false">
                    <?= $consent_manager->texts['toggle_details'] ?>
                </button>
            </div>

            <div class="consent_manager-detail consent_manager-hidden border-t border-gray-200 pt-4" id="consent_manager-detail" aria-labelledby="consent_manager-toggle-details">
                <?php
                foreach ($consent_manager->cookiegroups as $cookiegroup) {
                    if (count($cookiegroup['cookie_uids']) >= 1) {
                        ?>
                        <div class="mb-6">
                            <h5 class="text-sm font-bold text-gray-900 mb-1"><?= rex_escape($cookiegroup['name'] ?? '') ?></h5>
                            <div class="text-xs text-gray-500 mb-2"><?= $cookiegroup['description'] ?? '' ?></div>
                            
                            <div class="divide-y divide-gray-200">
                                <?php
                                foreach ($cookiegroup['cookie_uids'] as $cookieUid) {
                                    if (isset($consent_manager->cookies[$cookieUid])) {
                                        $cookie = $consent_manager->cookies[$cookieUid];
                                        foreach (($cookie['definition'] ?? []) as $index => $def) {
                                            ?>
                                            <div class="py-2">
                                                <div class="text-xs font-semibold text-gray-800"><?= rex_escape($def['cookie_name'] ?? '') ?> <?= rex_escape($cookie['service_name'] ?? '') ?></div>
                                                <div class="text-xs text-gray-600 mt-1"><?= $def['description'] ?? '' ?></div>
                                                <div class="grid grid-cols-2 gap-2 mt-2 text-[10px] text-gray-400">
                                                    <div><strong><?= $consent_manager->texts['lifetime'] ?? 'Laufzeit' ?>:</strong> <?= rex_escape($def['cookie_lifetime'] ?? '') ?></div>
                                                    <div><strong><?= $consent_manager->texts['provider'] ?? 'Anbieter' ?>:</strong> <?= rex_escape($cookie['provider'] ?? '') ?></div>
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
            
            <div class="mt-8 border-t border-gray-100 pt-6">
                <div class="flex flex-col sm:flex-row gap-3">
                    <?php if (isset($consent_manager->texts['button_select_none'])): ?>
                    <div class="flex-1">
                        <button tabindex="0" id="consent_manager-accept-none" class="consent_manager-accept-none consent_manager-close w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"><?= $consent_manager->texts['button_select_none'] ?></button>
                    </div>
                    <?php endif; ?>
                    <div class="flex-1">
                        <button tabindex="0" id="consent_manager-save-selection" class="consent_manager-save-selection consent_manager-close w-full px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"><?= $consent_manager->texts['button_accept'] ?></button>
                    </div>
                    <div class="flex-1">
                        <button tabindex="0" id="consent_manager-accept-all" class="consent_manager-accept-all consent_manager-close w-full px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"><?= $consent_manager->texts['button_select_all'] ?></button>
                    </div>
                </div>
                
                <div class="mt-4 text-center">
                    <?php
                    $clang = rex_clang::getCurrentId();
                    foreach ($consent_manager->links as $v) {
                        $article = rex_article::get($v, $clang);
                        if ($article) {
                            echo '<a tabindex="0" href="' . rex_getUrl($v, $clang) . '" class="text-xs text-gray-400 hover:text-gray-500 mx-2">' . rex_escape($article->getName()) . '</a>';
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
