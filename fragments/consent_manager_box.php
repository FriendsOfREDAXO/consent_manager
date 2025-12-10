<?php
$consent_manager = new consent_manager_frontend(0);
$consent_manager->setDomain($_SERVER['HTTP_HOST']);
?>
<?php if ($consent_manager->cookiegroups): ?>
        <div class="consent_manager-background consent_manager-hidden <?= $consent_manager->boxClass ?>" id="consent_manager-background" data-domain-name="<?= $consent_manager->domainName ?>" data-version="<?= $consent_manager->version ?>" data-consentid="<?= uniqid('', true) ?>" data-cachelogid="<?= $consent_manager->cacheLogId ?>" data-nosnippet>
            <div class="consent_manager-wrapper" id="consent_manager-wrapper">
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
                                        // XSS-Schutz: uid und name escapen
                                        echo '<label for="'.rex_escape($cookiegroup['uid']).'"><input type="checkbox" disabled="disabled" data-action="toggle-cookie" id="'.rex_escape($cookiegroup['uid']).'" data-uid="'.rex_escape($cookiegroup['uid']).'" data-cookie-uids=\''.json_encode($cookiegroup['cookie_uids']).'\' checked>';
                                        echo '<span>'.rex_escape($cookiegroup['name']).'</span></label>';
                                        echo '</div>' . PHP_EOL;
                                    } else {
                                        echo '<div class="consent_manager-cookiegroup-checkbox">';
                                        // XSS-Schutz: uid und name escapen
                                        echo '<label for="'.rex_escape($cookiegroup['uid']).'"><input type="checkbox" id="'.rex_escape($cookiegroup['uid']).'" data-uid="'.rex_escape($cookiegroup['uid']).'" data-cookie-uids=\''.json_encode($cookiegroup['cookie_uids']).'\'>';
                                        echo '<span>'.rex_escape($cookiegroup['name']).'</span></label>';
                                        echo '</div>' . PHP_EOL;
                                    }
                                }
                            }
                            ?>
                        </div>
                        <div class="consent_manager-show-details">
                            <a id="consent_manager-toggle-details" class="icon-info-circled"><?= $consent_manager->texts['toggle_details'] ?></a>
                        </div>
                    </div>
                    <div class="consent_manager-detail consent_manager-hidden" id="consent_manager-detail">
                        <?php
                        foreach ($consent_manager->cookiegroups as $cookiegroup) {
                            if (count($cookiegroup['cookie_uids']) >= 1) {
                                echo '<div class="consent_manager-cookiegroup-title consent_manager-headline">';
                                // XSS-Schutz: Name escapen
                                echo rex_escape($cookiegroup['name']).' <span>('.count($cookiegroup['cookie_uids']).')</span>';
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
                                                echo '<div class="consent_manager-cookie">';
                                                // XSS-Schutz: Cookie-Name und Service-Name escapen
                                                echo '<span class="consent_manager-cookie-name"><strong>'.rex_escape($def['cookie_name']).'</strong> ('.rex_escape($cookie['service_name']).')</span>';
                                                // Description darf HTML enthalten (bewusste Entscheidung)
                                                echo '<span class="consent_manager-cookie-description">'.$def['description'].'</span>';
                                                // XSS-Schutz: Lifetime escapen
                                                echo '<span class="consent_manager-cookie-description">'.$consent_manager->texts['lifetime'].' '.rex_escape($def['cookie_lifetime']).'</span>';
                                                // XSS-Schutz: Provider escapen
                                                echo '<span class="consent_manager-cookie-provider">'.$consent_manager->texts['provider'].' '.rex_escape($cookie['provider']).'</span>';
                                                // XSS-Schutz: Provider-Link escapen
                                                echo '<span class="consent_manager-cookie-link-privacy-policy"><a href="'.rex_escape($cookie['provider_link_privacy']).'">'.$consent_manager->texts['link_privacy'].'</a></span>';
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
                            <a class="consent_manager-save-selection consent_manager-close"><?= $consent_manager->texts['button_accept'] ?></a>
                            <a class="consent_manager-accept-all consent_manager-close"><?= $consent_manager->texts['button_select_all'] ?></a>
                        </div>
                        <div class="consent_manager-sitelinks">
                            <?php
                            $clang = rex_request('lang', 'integer', 0);
                            if ($clang === 0) {
                                $clang = rex_clang::getCurrent()->getId();
                            }
                            foreach ($consent_manager->links as $v) {
                                // XSS-Schutz: Artikel-Name escapen
                                $articleName = !is_null(rex_article::get($v, $clang)) ? rex_escape(rex_article::get($v, $clang)->getName()) : '';
                                echo '<a href="' . rex_getUrl($v, $clang) . '">' . $articleName . '</a>';
                            }
                            ?>
                        </div>
                    </div>
                    <a class="icon-cancel-circled consent_manager-close consent_manager-close-box">&#10006;</a>
                </div>
            </div>
            <?php
            foreach ($consent_manager->scripts as $uid => $script) {
                echo '<div style="display: none" class="consent_manager-script" data-uid="script-'.$uid.'" data-script="'.$script.'"></div>';
            }
            ?>
        </div>
<?php endif; ?>
