<?php
$consent_manager = new consent_manager_frontend(0);
if (is_string(rex_request::server('HTTP_HOST'))) {
    $consent_manager->setDomain(rex_request::server('HTTP_HOST'));
}
if (0 === count($consent_manager->texts)) {
    echo '<div id="consent_manager-background">' . rex_view::error(rex_addon::get('consent_manager')->i18n('consent_manager_error_noconfig')) . '</div>';
    return;
}
if (null !== $consent_manager->cookiegroups): ?>
        <div tabindex="-1" class="consent_manager-background consent_manager-hidden <?= $consent_manager->boxClass ?>" id="consent_manager-background" data-domain-name="<?= $consent_manager->domainName ?>" data-version="<?= $consent_manager->version ?>" data-consentid="<?= uniqid('', true) ?>" data-cachelogid="<?= $consent_manager->cacheLogId ?>" data-nosnippet aria-hidden="true">
            <div class="consent_manager-wrapper" id="consent_manager-wrapper" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="consent_manager-headline">
                <div class="consent_manager-wrapper-inner">
                    <div class="consent_manager-summary" id="consent_manager-summary">
                        <p class="consent_manager-headline" id="consent_manager-headline"><?= $consent_manager->texts['headline'] ?></p>
                        <p class="consent_manager-text"><?= nl2br($consent_manager->texts['description']) ?></p>
                        <div class="consent_manager-cookiegroups">
                            <?php
                            foreach ($consent_manager->cookiegroups as $cookiegroup) {
                                if (count($cookiegroup['cookie_uids']) >= 1) {
                                    if ($cookiegroup['required']) {
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

                    <div class="consent_manager-detail consent_manager-hidden" id="consent_manager-detail" aria-labelledby="consent_manager-toggle-details">
                    	<?php
                        foreach ($consent_manager->cookiegroups as $cookiegroup) {
                            if (count($cookiegroup['cookie_uids']) >= 1) {
                               $countDefs	= 0;
                        		$countAll 	= 0;
                            	if (isset($cookiegroup['cookie_uids'])) {
									foreach ($cookiegroup['cookie_uids'] as $cookieUid) {
										$countDefs = count($consent_manager->cookies[$cookieUid]['definition'] ?? []);
										$countAll = $countAll + $countDefs;
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
                                                $serviceName		= '';
                                                // XSS-Schutz: Service-Name escapen
                                                if($cookie['service_name']) $serviceName = '(' . rex_escape($cookie['service_name']) . ')';

                                                $linkTarget		=  '';
												$linkRel		=  '';
												$cookProvider	= strtolower($cookie['provider']);

												/** - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
													TODO (!):
													This is used to detect, if a link targets to a foreign website or is an internal link. If foreign
													we add rel="noopener noreferrer nofollow" to the link.
													Beside of German and English the $expressionsAry is not completed for all maybe also used languages yet.
													Please add for all the other supported languages (in small letters) the used language dependend expressions
													identifying "this website" in the specific language.
													For example: "esta pagina" or whatever is used in each language ...
												- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - **/
												$expressionsAry = ['diese website','this website'];

                                                echo '<div class="consent_manager-cookie">';
                                                // XSS-Schutz: Cookie-Name escapen
                                                echo '<span class="consent_manager-cookie-name"><strong>' . rex_escape($def['cookie_name']) . '</strong> ' . $serviceName . '</span>';
                                                // Description darf HTML enthalten (bewusste Entscheidung)
                                                echo '<span class="consent_manager-cookie-description">' . $def['description'] . '</span>';
                                                // XSS-Schutz: Lifetime escapen
                                                echo '<span class="consent_manager-cookie-description">' . $consent_manager->texts['lifetime'] . ' ' . rex_escape($def['cookie_lifetime']) . '</span>';
                                                // XSS-Schutz: Provider escapen
                                                echo '<span class="consent_manager-cookie-provider">' . $consent_manager->texts['provider'] . ' ' . rex_escape($cookie['provider']) . '</span>';

                                                if(!in_array($cookProvider, $expressionsAry)) {
                                                	$linkTarget = 'target="_blank"';
													$linkRel	= 'rel="noopener noreferrer nofollow"';
                                                }
                                                echo '<span class="consent_manager-cookie-link-privacy-policy">'.PHP_EOL;
                                                // XSS-Schutz: Provider-Link escapen
                                                echo '	<a href="' . rex_escape($cookie['provider_link_privacy']) . '" ' . $linkTarget . ' ' . $linkRel . '>' . $consent_manager->texts['link_privacy'] . '</a>'.PHP_EOL;
                                                echo '</span>'.PHP_EOL;
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
    $article = rex_article::get($v, $clang);
    $articleName = null !== $article ? rex_escape($article->getName()) : '';
    echo '<a tabindex="0" href="' . rex_getUrl($v, $clang) . '">' . $articleName . '</a>';
}
?>
                        </div>
                    </div>
                    <button tabindex="0" class="icon-cancel-circled consent_manager-close consent_manager-close-box">&#10006;</button>
                </div>
            </div>
            <?php
            // CSP-compatible script storage using data attributes
            // Scripts are NOT injected via innerHTML but stored as data attributes
            // and loaded via external script tags when consent is given
            // NOTE: Scripts are ALREADY base64-encoded in the cache (see consent_manager_cache.php line 120-121)
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
