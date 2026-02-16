<?php

/**
 * TODO: hier die Schnittstelle beschreiben:
 * - Welche Vars werden vom Fragment erwartet
 * - Welchen Typ haben die Vars
 * - Welchen Default-Wert haben optionale Vars
 * - Welche Vars sind mandatory und was passiert wenn sie fehlen (return oder Exception)
 */

use FriendsOfRedaxo\ConsentManager\Frontend;

$addon = rex_addon::get('consent_manager');
$consent_manager = new Frontend(0);
if (is_string(rex_request::server('HTTP_HOST'))) {
    $consent_manager->setDomain(rex_request::server('HTTP_HOST'));
}
if (0 === count($consent_manager->texts)) {
    echo '<div id="consent_manager-background">' . rex_view::error($addon->i18n('consent_manager_error_noconfig')) . '</div>';
    return;
}

// Check for CSS Framework Mode
$cssFrameworkMode = $addon->getConfig('css_framework_mode');
if ($cssFrameworkMode) {
    echo $this->parse('ConsentManager/box_' . $cssFrameworkMode . '.php');
    return;
}

if (0 < count($consent_manager->cookiegroups)) : ?>
        <div tabindex="-1" class="consent_manager-background consent_manager-hidden <?= $consent_manager->boxClass ?>" id="consent_manager-background" data-domain-name="<?= $consent_manager->domainName ?>" data-version="<?= $consent_manager->version ?>" data-consentid="<?= uniqid('', true) ?>" data-cachelogid="<?= $consent_manager->cacheLogId ?>" data-nosnippet aria-hidden="true">
            <?php
            // Inline-CSS nur ausgeben wenn kein Framework-Modus und kein eigenes CSS aktiv ist
            if ('' === $cssFrameworkMode && false === $addon->getConfig('outputowncss', false)) :
            ?>
            <style nonce="<?= rex_response::getNonce() ?>">
                #consent_manager-background {
                    <?php if ($addon->getConfig('backdrop', '1') === '0'): ?>
                    background: transparent !important;
                    pointer-events: none !important;
                    <?php endif; ?>
                }
                #consent_manager-wrapper {
                    max-height: 90vh !important;
                    overflow-y: auto !important;
                    border-radius: 0 !important;
                    <?php if ($addon->getConfig('backdrop', '1') === '0'): ?>
                    pointer-events: auto !important;
                    box-shadow: 0 0 20px rgba(0,0,0,0.2) !important;
                    background: #fff !important;
                    <?php endif; ?>
                }
                .consent_manager-header {
                    padding: 20px 20px 0 20px;
                    background: transparent;
                    position: relative;
                    z-index: 10;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    color: inherit;
                }
                .consent_manager-close {
                    cursor: pointer;
                    background: transparent;
                    border: none;
                    font-size: 24px;
                    line-height: 1;
                    padding: 5px;
                    color: inherit;
                    opacity: 0.7;
                }
                .consent_manager-close:hover {
                    opacity: 1;
                }
            </style>
            <?php endif; ?>
            <div class="consent_manager-wrapper" id="consent_manager-wrapper" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="consent_manager-headline">
                <div class="consent_manager-header">
                    <p class="consent_manager-headline" id="consent_manager-headline" style="margin:0; font-weight:bold; color: inherit;"><?= $consent_manager->texts['headline'] ?></p>
                    <button class="consent_manager-close" aria-label="Close" type="button">×</button>
                </div>
                <div class="consent_manager-wrapper-inner">
                    <div class="consent_manager-summary" id="consent_manager-summary">
                        <p class="consent_manager-text"><?= nl2br($consent_manager->texts['description']) ?></p>
                        <div class="consent_manager-cookiegroups">
                            <?php
                            foreach ($consent_manager->cookiegroups as $cookiegroup) {
                                if (count($cookiegroup['cookie_uids']) >= 1) {
                                    // TODO: was steht eigentlch in dem Feld? String, Int, Bool, ...?
                                    if ((bool) $cookiegroup['required']) {
                                        echo '<div class="consent_manager-cookiegroup-checkbox">';
                                        echo '<label for="' . rex_escape($cookiegroup['uid']) . '"><input type="checkbox" disabled="disabled" data-action="toggle-cookie" id="' . rex_escape($cookiegroup['uid']) . '" data-uid="' . rex_escape($cookiegroup['uid']) . '" data-cookie-uids=\'' . json_encode($cookiegroup['cookie_uids']) . '\' checked>';
                                        echo '<span>' . rex_escape($cookiegroup['name']) . '</span></label>';
                                        echo '</div>' . PHP_EOL;
                                    } else {
                                        echo '<div class="consent_manager-cookiegroup-checkbox">';
                                        echo '<label for="' . rex_escape($cookiegroup['uid']) . '"><input tabindex="0" type="checkbox" id="' . rex_escape($cookiegroup['uid']) . '" data-uid="' . rex_escape($cookiegroup['uid']) . '" data-cookie-uids=\'' . json_encode($cookiegroup['cookie_uids']) . '\'>';
                                        echo '<span>' . rex_escape($cookiegroup['name']) . '</span></label>';
                                        echo '</div>' . PHP_EOL;
                                    }
                                }
                            }
?>
                        </div>
                        <div class="consent_manager-show-details">
                            <button id="consent_manager-toggle-details" class="icon-info-circled" aria-controls="consent_manager-detail" aria-expanded="false"><?= $consent_manager->texts['toggle_details'] ?></button>
                        </div>
                    </div>

                    <div class="consent_manager-detail consent_manager-hidden" id="consent_manager-detail" aria-labelledby="consent_manager-toggle-details" role="region">
                    	<?php
                        foreach ($consent_manager->cookiegroups as $cookiegroup) {
                            if (count($cookiegroup['cookie_uids']) >= 1) {
                                $countDefs = 0;
                                $countAll = 0;
                                if (isset($cookiegroup['cookie_uids'])) {
                                    foreach ($cookiegroup['cookie_uids'] as $cookieUid) {
                                        $countDefs = count($consent_manager->cookies[$cookieUid]['definition'] ?? []);
                                        $countAll += $countDefs;
                                    }
                                }
                                echo '<div class="consent_manager-cookiegroup-title consent_manager-headline">';
                                echo rex_escape($cookiegroup['name']) . ' <span class="consent_manager-cookiegroup-number">(' . $countAll . ')</span>';
                                echo '</div>';
                                echo '<div class="consent_manager-cookiegroup-description">';
                                // Description darf HTML enthalten (bewusste Entscheidung)
                                echo $cookiegroup['description'];
                                echo '</div>';
                                echo '<div class="consent_manager-cookiegroup">';
                                foreach ($cookiegroup['cookie_uids'] as $cookieUid) {
                                    if (isset($consent_manager->cookies[$cookieUid])) {
                                        $cookie = $consent_manager->cookies[$cookieUid];
                                        if (isset($cookie['definition'])) {
                                            foreach ($cookie['definition'] as $def) {
                                                $serviceName = '';
                                                if ('' !== ($cookie['service_name'] ?? '')) {
                                                    // XSS-Schutz: Service-Name escapen
                                                    $serviceName = '(' . rex_escape($cookie['service_name']) . ')';
                                                }

                                                $linkTarget = '';
                                                $linkRel = '';
                                                $cookProvider = strtolower($cookie['provider']);

                                                /** - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                                                 * TODO (!):
                                                 * This is used to detect, if a link targets to a foreign website or is an internal link. If foreign
                                                 * we add rel="noopener noreferrer nofollow" to the link.
                                                 * Beside of German and English the $expressionsAry is not completed for all maybe also used languages yet.
                                                 * Please add for all the other supported languages (in small letters) the used language dependend expressions
                                                 * identifying "this website" in the specific language.
                                                 * For example: "esta pagina" or whatever is used in each language ...
                                                 * - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
                                                $expressionsAry = ['diese website', 'this website'];

                                                echo '<div class="consent_manager-cookie">';
                                                // XSS-Schutz: Cookie-Name und Service-Name escapen
                                                echo '<span class="consent_manager-cookie-name"><strong>' . rex_escape($def['cookie_name']) . '</strong> ' . $serviceName . '</span>';
                                                // Description darf HTML enthalten (bewusste Entscheidung)
                                                echo '<span class="consent_manager-cookie-description">' . $def['description'] . '</span>';
                                                // XSS-Schutz: Lifetime escapen
                                                echo '<span class="consent_manager-cookie-description">' . $consent_manager->texts['lifetime'] . ' ' . rex_escape($def['cookie_lifetime']) . '</span>';
                                                // XSS-Schutz: Provider escapen
                                                echo '<span class="consent_manager-cookie-provider">' . $consent_manager->texts['provider'] . ' ' . rex_escape($cookie['provider']) . '</span>';

                                                if (!in_array($cookProvider, $expressionsAry, true)) {
                                                    $linkTarget = 'target="_blank"';
                                                    $linkRel = 'rel="noopener noreferrer nofollow"';
                                                }
                                                echo '<span class="consent_manager-cookie-link-privacy-policy">' . PHP_EOL;
                                                // XSS-Schutz: Provider-Link escapen
                                                echo '	<a href="' . rex_escape($cookie['provider_link_privacy']) . '" ' . $linkTarget . ' ' . $linkRel . '>' . $consent_manager->texts['link_privacy'] . '</a>' . PHP_EOL;
                                                echo '</span>' . PHP_EOL;
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
$clang = rex_request::request('lang', 'integer', 0);
if (0 === $clang) {
    $clang = rex_clang::getCurrent()->getId();
}
foreach ($consent_manager->links as $v) {
    $article = rex_article::get($v, $clang);
    if ($article instanceof rex_article) {
        echo '<a tabindex="0" href="' . rex_getUrl($v, $clang) . '">' . rex_escape($article->getName()) . '</a>';
    }
}
?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            // CSP-compatible script storage using data attributes
            // Scripts are NOT injected via innerHTML but stored as data attributes
            // and loaded via external script tags when consent is given
            // NOTE: Scripts are ALREADY base64-encoded in the cache (see Cache.php line 120-121)
            // DO NOT encode again here or you'll get double-encoding!
            foreach ($consent_manager->scripts as $uid => $script) {
                // Script is already base64-encoded from cache
                echo '<div class="consent_manager-script" data-uid="script-' . $uid . '" data-script="' . $script . '"></div>';
            }
foreach ($consent_manager->scriptsUnselect as $uid => $script) {
    echo '<div class="consent_manager-script" data-uid="script-unselect-' . $uid . '" data-script="' . $script . '"></div>';
}
?>
        </div>
<?php endif ?>
