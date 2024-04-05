<?php
$consent_manager = new consent_manager_frontend(0);
if (is_string(rex_request::server('HTTP_HOST'))) {
    $consent_manager->setDomain(rex_request::server('HTTP_HOST'));
}
if (0 === count($consent_manager->texts)) {
    echo '<div id="consent_manager-background">' . rex_view::error(rex_addon::get('consent_manager')->i18n('consent_manager_error_noconfig')) . '</div>';
    return;
}
?>
<?php if (null !== $consent_manager->cookiegroups): ?>
        <div tabindex="-1" class="consent_manager-background consent_manager-hidden <?= $consent_manager->boxClass ?>" id="consent_manager-background" data-domain-name="<?= $consent_manager->domainName ?>" data-version="<?= $consent_manager->version ?>" data-consentid="<?= uniqid('', true) ?>" data-cachelogid="<?= $consent_manager->cacheLogId ?>" data-nosnippet>
            <div class="consent_manager-wrapper" id="consent_manager-wrapper" tabindex="-1" aria-modal="true" role="dialog" title="Cookie Consent">
                <div class="consent_manager-wrapper-inner">
                    <div class="consent_manager-summary" id="consent_manager-summary">
                        <p class="consent_manager-headline"><?= $consent_manager->texts['headline'] ?></p>
                        <p class="consent_manager-text"><?= nl2br($consent_manager->texts['description']) ?></p>
                        <div class="consent_manager-cookiegroups">
                            <?php
                            foreach ($consent_manager->cookiegroups as $cookiegroup) {
                                if (count($cookiegroup['cookie_uids']) >= 1) {
                                    if ($cookiegroup['required']) {
                                        echo '<div class="consent_manager-cookiegroup-checkbox">';
                                        echo '<label for="' . $cookiegroup['uid'] . '"><input type="checkbox" disabled="disabled" data-action="toggle-cookie" id="' . $cookiegroup['uid'] . '" data-uid="' . $cookiegroup['uid'] . '" data-cookie-uids=\'' . json_encode($cookiegroup['cookie_uids']) . '\' checked>';
                                        echo '<span>' . $cookiegroup['name'] . '</span></label>';
                                        echo '</div>' . PHP_EOL;
                                    } else {
                                        echo '<div class="consent_manager-cookiegroup-checkbox">';
                                        echo '<label for="' . $cookiegroup['uid'] . '"><input tabindex="0" type="checkbox" id="' . $cookiegroup['uid'] . '" data-uid="' . $cookiegroup['uid'] . '" data-cookie-uids=\'' . json_encode($cookiegroup['cookie_uids']) . '\'>';
                                        echo '<span>' . $cookiegroup['name'] . '</span></label>';
                                        echo '</div>' . PHP_EOL;
                                    }
                                }
                            }
?>
                        </div>
                        <div class="consent_manager-show-details">
                            <a href="#" id="consent_manager-toggle-details" class="icon-info-circled" tabindex="0"><?= $consent_manager->texts['toggle_details'] ?></a>
                        </div>
                    </div>
                    <div class="consent_manager-detail consent_manager-hidden" id="consent_manager-detail">
<?php
                        foreach ($consent_manager->cookiegroups as $cookiegroup) {
                            if (count($cookiegroup['cookie_uids']) >= 1) {
                                echo '<div class="consent_manager-cookiegroup-title consent_manager-headline">';
                                echo $cookiegroup['name'] . ' <span>(' . count($cookiegroup['cookie_uids']) . ')</span>';
                                echo '</div>';
                                echo '<div class="consent_manager-cookiegroup-description">';
                                echo $cookiegroup['description'];
                                echo '</div>';
                                echo '<div class="consent_manager-cookiegroup">';
                                foreach ($cookiegroup['cookie_uids'] as $cookieUid) {
                                    if (isset($consent_manager->cookies[$cookieUid])) {
                                        $cookie = $consent_manager->cookies[$cookieUid];
                                        if (isset($cookie['definition'])) {
                                            foreach ($cookie['definition'] as $def) {
                                                echo '<div class="consent_manager-cookie">';
                                                echo '<span class="consent_manager-cookie-name"><strong>' . $def['cookie_name'] . '</strong> (' . $cookie['service_name'] . ')</span>';
                                                echo '<span class="consent_manager-cookie-description">' . $def['description'] . '</span>';
                                                echo '<span class="consent_manager-cookie-description">' . $consent_manager->texts['lifetime'] . ' ' . $def['cookie_lifetime'] . '</span>';
                                                echo '<span class="consent_manager-cookie-provider">' . $consent_manager->texts['provider'] . ' ' . $cookie['provider'] . '</span>';
                                                echo '<span class="consent_manager-cookie-link-privacy-policy"><a href="' . $cookie['provider_link_privacy'] . '">' . $consent_manager->texts['link_privacy'] . '</a></span>';
                                                echo '</div>' . PHP_EOL;
                                            }
                                        }
                                    }
                                }
                                echo '</div>';
                            }
                        }
?>
                    </div>
                    <div class="consent_manager-buttons-sitelinks">
                        <div class="consent_manager-buttons">
                            <?php if (isset($consent_manager->texts['button_select_none'])) { ?>
                            <button tabindex="0" id="consent_manager-accept-none" class="consent_manager-accept-none consent_manager-close"><?= $consent_manager->texts['button_select_none'] ?></button>
                            <?php } ?>
                            <button tabindex="0" id="consent_manager-save-selection" class="consent_manager-save-selection consent_manager-close"><?= $consent_manager->texts['button_accept'] ?></button>
                            <button tabindex="0" id="consent_manager-accept-all" class="consent_manager-accept-all consent_manager-close"><?= $consent_manager->texts['button_select_all'] ?></button>
                        </div>
                        <div class="consent_manager-sitelinks">
<?php
$clang = rex_request('lang', 'integer', 0);
if (0 === $clang) {
    $clang = rex_clang::getCurrent()->getId();
}
foreach ($consent_manager->links as $v) {
    echo '<a tabindex="0" href="' . rex_getUrl($v, $clang) . '">' . (null !== rex_article::get($v, $clang) ? rex_article::get($v, $clang)->getName() : '') . '</a>';
}
?>
                        </div>
                    </div>
                    <button tabindex="0" class="icon-cancel-circled consent_manager-close consent_manager-close-box">&#10006;</button>
                </div>
            </div>
            <?php
            foreach ($consent_manager->scripts as $uid => $script) {
                echo '<div style="display: none" class="consent_manager-script" data-uid="script-' . $uid . '" data-script="' . $script . '"></div>';
            }
            foreach ($consent_manager->scriptsUnselect as $uid => $script) {
                echo '<div style="display: none" class="consent_manager-script" data-uid="script-unselect-' . $uid . '" data-script="' . $script . '"></div>';
            }
?>
        </div>
<?php endif ?>
