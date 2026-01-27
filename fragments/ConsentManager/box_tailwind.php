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

<?php
$addon = rex_addon::get('consent_manager');
$backdropEnabled = $addon->getConfig('backdrop', '1') !== '0';
$shadowType = $addon->getConfig('css_framework_shadow', 'large');
$roundedEnabled = $addon->getConfig('css_framework_rounded', '1') === '1';

$shadowClass = 'shadow-2xl';
if ($shadowType === 'none') {
    $shadowClass = 'shadow-none';
} elseif ($shadowType === 'small') {
    $shadowClass = 'shadow-lg';
}

$roundedClass = $roundedEnabled ? 'rounded-2xl' : 'rounded-none';
$buttonRoundedClass = $roundedEnabled ? 'rounded-lg' : 'rounded-none';
?>

<div tabindex="-1" class="consent_manager-background consent_manager-hidden <?= $consent_manager->boxClass ?> fixed inset-0 flex items-center justify-center p-4 <?= $backdropEnabled ? 'bg-slate-900/60' : 'bg-transparent pointer-events-none' ?> z-[1000000]" id="consent_manager-background" data-domain-name="<?= $consent_manager->domainName ?>" data-version="<?= $consent_manager->version ?>" data-consentid="<?= uniqid('', true) ?>" data-cachelogid="<?= $consent_manager->cacheLogId ?>" data-nosnippet aria-hidden="true">
    <div class="consent_manager-wrapper bg-white <?= $shadowClass ?> <?= $roundedClass ?> w-full max-w-3xl max-h-[90vh] flex flex-col transform transition-all duration-300 <?= $backdropEnabled ? '' : 'pointer-events-auto border border-slate-200' ?>" id="consent_manager-wrapper" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="consent_manager-headline">
        
        <!-- Header -->
        <div class="flex-none bg-white border-b border-slate-100 px-6 py-5 sm:px-8 sm:py-6 flex items-start justify-between <?= $roundedEnabled ? 'rounded-t-2xl' : '' ?>">
            <div>
                <h2 class="text-xl font-bold text-slate-900 leading-tight" id="consent_manager-headline"><?= $consent_manager->texts['headline'] ?></h2>
            </div>
            <button tabindex="0" class="consent_manager-close text-slate-400 hover:text-slate-600 transition-colors p-2 -mr-2 -mt-2 focus:outline-none" aria-label="Close">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <!-- Main Content (Scrollable) -->
        <div class="flex-grow overflow-y-auto px-6 py-6 sm:px-8 sm:py-8">
            <div class="text-slate-600 mb-8 text-base leading-relaxed"><?= nl2br($consent_manager->texts['description']) ?></div>
            
            <div class="space-y-0 border-t border-slate-100 mb-4">
                <?php
                foreach ($consent_manager->cookiegroups as $cookiegroup) {
                    if (count($cookiegroup['cookie_uids']) >= 1) {
                        $isRequired = (bool) ($cookiegroup['required'] ?? false);
                        ?>
                        <div class="group border-b border-slate-100">
                            <details class="cursor-default">
                                <summary class="flex items-center justify-between py-5 cursor-pointer list-none appearance-none [&::-webkit-details-marker]:hidden">
                                    <div class="flex items-center flex-grow pr-4">
                                        <div class="relative flex items-center">
                                            <input class="w-5 h-5 appearance-none border-2 border-slate-300 checked:bg-sky-600 checked:border-sky-600 transition-all cursor-pointer focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 <?= $roundedEnabled ? 'rounded' : 'rounded-none' ?>" type="checkbox" 
                                                id="<?= rex_escape($cookiegroup['uid']) ?>" 
                                                data-uid="<?= rex_escape($cookiegroup['uid']) ?>" 
                                                data-cookie-uids='<?= json_encode($cookiegroup['cookie_uids']) ?>'
                                                <?= $isRequired ? 'checked disabled data-action="toggle-cookie"' : 'tabindex="0"' ?>
                                                onclick="event.stopPropagation();"
                                            >
                                            <svg class="absolute w-3 h-3 text-white pointer-events-none left-1 opacity-0 check-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <style nonce="<?= rex_response::getNonce() ?>">
                                                input:checked ~ .check-icon { opacity: 1; }
                                            </style>
                                        </div>
                                        <label class="ml-4 block text-base font-semibold text-slate-900 cursor-pointer" for="<?= rex_escape($cookiegroup['uid']) ?>" onclick="event.stopPropagation();">
                                            <?= rex_escape($cookiegroup['name'] ?? '') ?>
                                            <?php if ($isRequired): ?>
                                                <span class="ml-2 px-2 py-0.5 inline-flex text-[10px] tracking-wider uppercase font-bold bg-slate-100 text-slate-400 <?= $roundedEnabled ? 'rounded' : 'rounded-none' ?>"><?= rex_i18n::msg('consent_manager_cookiegroup_required') ?></span>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                    <div class="flex items-center text-slate-400 gap-2 shrink-0">
                                        <span class="text-xs font-medium"><?= $consent_manager->texts['toggle_details'] ?></span>
                                        <svg class="w-4 h-4 transition-transform duration-200 group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </summary>
                                <div class="pb-6 pl-9 pr-4">
                                    <div class="text-sm text-slate-500 mb-4 leading-relaxed"><?= $cookiegroup['description'] ?? '' ?></div>
                                    
                                    <div class="space-y-4">
                                        <?php
                                        foreach ($cookiegroup['cookie_uids'] as $cookieUid) {
                                            if (isset($consent_manager->cookies[$cookieUid])) {
                                                $cookie = $consent_manager->cookies[$cookieUid];
                                                foreach (($cookie['definition'] ?? []) as $index => $def) {
                                                    ?>
                                                    <div class="bg-slate-50 p-4 border-l-2 border-slate-200 <?= $roundedEnabled ? 'rounded-r-lg' : '' ?>">
                                                        <div class="text-xs font-bold text-slate-900 flex justify-between gap-4 mb-2">
                                                            <span><?= rex_escape($def['cookie_name'] ?? '') ?></span>
                                                            <span class="text-slate-400 font-normal"><?= rex_escape($cookie['service_name'] ?? '') ?></span>
                                                        </div>
                                                        <div class="text-xs text-slate-600 mb-3 leading-relaxed"><?= $def['description'] ?? '' ?></div>
                                                        <div class="flex flex-wrap gap-x-6 gap-y-2 text-[10px] text-slate-400 uppercase tracking-tight font-medium">
                                                            <div><span class="text-slate-300"><?= $consent_manager->texts['lifetime'] ?? 'Laufzeit' ?>:</span> <span class="text-slate-500"><?= rex_escape($def['cookie_lifetime'] ?? '') ?></span></div>
                                                            <div><span class="text-slate-300"><?= $consent_manager->texts['provider'] ?? 'Anbieter' ?>:</span> <span class="text-slate-500"><?= rex_escape($cookie['provider'] ?? '') ?></span></div>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </details>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>

        <!-- Footer (Fixed) -->
        <div class="flex-none bg-slate-50/50  px-6 py-6 sm:px-8 sm:py-8 border-t border-slate-100 <?= $roundedEnabled ? 'rounded-b-2xl' : '' ?>">
            <div class="flex flex-col sm:flex-row gap-3">
                <button tabindex="0" id="consent_manager-accept-all" class="consent_manager-accept-all consent_manager-close flex-1 px-4 py-3.5 bg-blue-600 border border-blue-600 text-white text-[11px] font-bold uppercase tracking-widest hover:bg-blue-700 transition-all shadow-sm active:scale-[0.98] <?= $buttonRoundedClass ?>"><?= $consent_manager->texts['button_select_all'] ?></button>
                
                <?php if (isset($consent_manager->texts['button_select_none'])): ?>
                    <button tabindex="0" id="consent_manager-accept-none" class="consent_manager-accept-none consent_manager-close flex-1 px-4 py-3.5 bg-white border border-slate-200 text-slate-900 text-[11px] font-bold uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm active:scale-[0.98] <?= $buttonRoundedClass ?>"><?= $consent_manager->texts['button_select_none'] ?></button>
                <?php endif; ?>
                
                <button tabindex="0" id="consent_manager-save-selection" class="consent_manager-save-selection consent_manager-close flex-1 px-4 py-3.5 bg-blue-600 border border-blue-600 text-white text-[11px] font-bold uppercase tracking-widest hover:bg-blue-700 transition-all shadow-sm active:scale-[0.98] <?= $buttonRoundedClass ?>"><?= $consent_manager->texts['button_accept'] ?></button>
            </div>

            <div class="mt-6 flex flex-wrap justify-center gap-x-6 gap-y-2">
                <?php
                $clang = rex_clang::getCurrentId();
                foreach ($consent_manager->links as $v) {
                    $article = rex_article::get($v, $clang);
                    if ($article instanceof rex_article) {
                        echo '<a tabindex="0" href="' . rex_getUrl($v, $clang) . '" class="text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors underline underline-offset-4 decoration-slate-200">' . rex_escape($article->getName()) . '</a>';
                    }
                }
                ?>
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
    <!-- Dummy für JavaScript-Kompatibilität -->
    <div id="consent_manager-detail" class="hidden" hidden aria-hidden="true"></div>
</div>
