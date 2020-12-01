<?php
$consent_manager = new consent_manager_frontend($this->getVar('forceCache'));
$consent_manager->setDomain($_SERVER['HTTP_HOST']);
if ($this->getVar('debug')) {
    dump($consent_manager);
}
?>
<?php if ($consent_manager->cookiegroups): ?>
    <link href="/assets/addons/consent_manager/fontello/css/fontello.css" rel="stylesheet" type="text/css">
    <link href="/assets/addons/consent_manager/pretty-checkbox.min.css" rel="stylesheet" type="text/css">
    <link href="/assets/addons/consent_manager/consent_manager_frontend.css" rel="stylesheet" type="text/css">
    <script src="/assets/addons/consent_manager/js.cookie-2.2.1.min.js"></script>
    <script src="/assets/addons/consent_manager/consent_manager_polyfills.js"></script>
    <script src="/assets/addons/consent_manager/consent_manager_frontend.js"></script>
    <script id="consent_manager-template" type="text/template">
        <div class="consent_manager-background consent_manager-hidden <?= $consent_manager->boxClass ?>" id="consent_manager-background" data-domain-name="<?= $consent_manager->domainName ?>" data-version="<?= $consent_manager->version ?>" data-consentid="<?= uniqid('', true) ?>" data-cachelogid="<?= $consent_manager->cacheLogId ?>">
            <div class="consent_manager-wrapper" id="consent_manager-wrapper">
                <div class="consent_manager-wrapper-inner">
                    <div class="consent_manager-summary" id="consent_manager-summary">
                        <p class="consent_manager-headline"><?= $consent_manager->texts['headline'] ?></p>
                        <p class="consent_manager-text"><?= nl2br($consent_manager->texts['description']) ?></p>
                        <div class="consent_manager-cookiegroups">
                            <?php
                            foreach ($consent_manager->cookiegroups as $cookiegroup) {
                                if ($cookiegroup['required']) {
                                    echo '<div class="consent_manager-cookiegroup-checkbox pretty p-icon p-curve p-locked">';
                                    echo '<input type="checkbox" data-action="toggle-cookie" data-uid="'.$cookiegroup['uid'].'" data-cookie-uids=\''.json_encode($cookiegroup['cookie_uids']).'\' checked>';
                                    echo '<div class="state">';
                                    echo '<i class="icon icon-ok-1"></i>';
                                    echo '<label>'.$cookiegroup['name'].'</label>';
                                    echo '</div>';
                                    echo '</div>';
                                } else {
                                    echo '<div class="consent_manager-cookiegroup-checkbox pretty p-icon p-curve">';
                                    echo '<input type="checkbox" data-uid="'.$cookiegroup['uid'].'" data-cookie-uids=\''.json_encode($cookiegroup['cookie_uids']).'\'>';
                                    echo '<div class="state">';
                                    echo '<i class="icon icon-ok-1"></i>';
                                    echo '<label>'.$cookiegroup['name'].'</label>';
                                    echo '</div>';
                                    echo '</div>';
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
                            echo '<div class="consent_manager-cookiegroup-title consent_manager-headline">';
                            echo $cookiegroup['name'].' <span>('.count($cookiegroup['cookie_uids']).')</span>';
                            echo '</div>';
                            echo '<div class="consent_manager-cookiegroup-description">';
                            echo $cookiegroup['description'];
                            echo '</div>';
                            echo '<div class="consent_manager-cookiegroup">';
                            foreach ($cookiegroup['cookie_uids'] as $cookieUid) {
                                $cookie = $consent_manager->cookies[$cookieUid];
                                foreach ($cookie['definition'] as $def) {
                                    echo '<div class="consent_manager-cookie">';
                                    echo '<span class="consent_manager-cookie-name"><strong>'.$def['cookie_name'].'</strong> ('.$cookie['service_name'].')</span>';
                                    echo '<span class="consent_manager-cookie-description">'.$def['description'].'</span>';
                                    echo '<span class="consent_manager-cookie-description">'.$consent_manager->texts['lifetime'].' '.$def['cookie_lifetime'].'</span>';
                                    echo '<span class="consent_manager-cookie-provider">'.$consent_manager->texts['provider'].' '.$cookie['provider'].'</span>';
                                    echo '<span class="consent_manager-cookie-link-privacy-policy"><a href="'.$cookie['provider_link_privacy'].'">'.$consent_manager->texts['link_privacy'].'</a></span>';
                                    echo '</div>';
                                }
                            }
                            echo '</div>';
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
                            foreach ($consent_manager->links as $v) {
                                echo '<a href="'.rex_getUrl($v).'">'.rex_article::get($v)->getName().'</a>';
                            }
                            ?>
                        </div>
                    </div>
                    <a class="icon-cancel-circled consent_manager-close consent_manager-close-box"></a>
                </div>
            </div>
            <?php
            foreach ($consent_manager->scripts as $uid => $script) {
                echo '<div style="display: none" class="consent_manager-script" data-uid="'.$uid.'" data-script="'.$script.'"></div>';
            }
            ?>
        </div>
    </script>
<?php endif; ?>
