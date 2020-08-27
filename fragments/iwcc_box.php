<?php
$iwcc = new iwcc_frontend($this->getVar('forceCache'));
$iwcc->setDomain($_SERVER['HTTP_HOST']);
if ($this->getVar('debug')) {
    dump($iwcc);
}
?>
<?php if ($iwcc->cookiegroups): ?>
    <link href="/assets/addons/iwcc/fontello/css/fontello.css" rel="stylesheet" type="text/css">
    <link href="/assets/addons/iwcc/pretty-checkbox.min.css" rel="stylesheet" type="text/css">
    <link href="/assets/addons/iwcc/iwcc_frontend.css" rel="stylesheet" type="text/css">
    <script src="/assets/addons/iwcc/js.cookie-2.2.1.min.js"></script>
    <script src="/assets/addons/iwcc/iwcc_polyfills.js"></script>
    <script src="/assets/addons/iwcc/iwcc_frontend.js"></script>
    <script id="iwcc-template" type="text/template">
        <div class="iwcc-background iwcc-hidden <?= $iwcc->boxClass ?>" id="iwcc-background" data-domain-name="<?= $iwcc->domainName ?>" data-version="<?= $iwcc->version ?>" data-consentid="<?= uniqid('', true) ?>" data-cachelogid="<?= $iwcc->cacheLogId ?>">
            <div class="iwcc-wrapper" id="iwcc-wrapper">
                <div class="iwcc-wrapper-inner">
                    <div class="iwcc-summary" id="iwcc-summary">
                        <p class="iwcc-headline"><?= $iwcc->texts['headline'] ?></p>
                        <p class="iwcc-text"><?= nl2br($iwcc->texts['description']) ?></p>
                        <div class="iwcc-cookiegroups">
                            <?php
                            foreach ($iwcc->cookiegroups as $cookiegroup) {
                                if ($cookiegroup['required']) {
                                    echo '<div class="iwcc-cookiegroup-checkbox pretty p-icon p-curve p-locked">';
                                    echo '<input type="checkbox" data-action="toggle-cookie" data-uid="'.$cookiegroup['uid'].'" data-cookie-uids=\''.json_encode($cookiegroup['cookie_uids']).'\' checked>';
                                    echo '<div class="state">';
                                    echo '<i class="icon icon-ok-1"></i>';
                                    echo '<label>'.$cookiegroup['name'].'</label>';
                                    echo '</div>';
                                    echo '</div>';
                                } else {
                                    echo '<div class="iwcc-cookiegroup-checkbox pretty p-icon p-curve">';
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
                        <div class="iwcc-show-details">
                            <a id="iwcc-toggle-details" class="icon-info-circled"><?= $iwcc->texts['toggle_details'] ?></a>
                        </div>
                    </div>
                    <div class="iwcc-detail iwcc-hidden" id="iwcc-detail">
                        <?php
                        foreach ($iwcc->cookiegroups as $cookiegroup) {
                            echo '<div class="iwcc-cookiegroup-title iwcc-headline">';
                            echo $cookiegroup['name'].' <span>('.count($cookiegroup['cookie_uids']).')</span>';
                            echo '</div>';
                            echo '<div class="iwcc-cookiegroup-description">';
                            echo $cookiegroup['description'];
                            echo '</div>';
                            echo '<div class="iwcc-cookiegroup">';
                            foreach ($cookiegroup['cookie_uids'] as $cookieUid) {
                                $cookie = $iwcc->cookies[$cookieUid];
                                foreach ($cookie['definition'] as $def) {
                                    echo '<div class="iwcc-cookie">';
                                    echo '<span class="iwcc-cookie-name"><strong>'.$def['cookie_name'].'</strong> ('.$cookie['service_name'].')</span>';
                                    echo '<span class="iwcc-cookie-description">'.$def['description'].'</span>';
                                    echo '<span class="iwcc-cookie-description">'.$iwcc->texts['lifetime'].' '.$def['cookie_lifetime'].'</span>';
                                    echo '<span class="iwcc-cookie-provider">'.$iwcc->texts['provider'].' '.$cookie['provider'].'</span>';
                                    echo '<span class="iwcc-cookie-link-privacy-policy"><a href="'.$cookie['provider_link_privacy'].'">'.$iwcc->texts['link_privacy'].'</a></span>';
                                    echo '</div>';
                                }
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>
                    <div class="iwcc-buttons-sitelinks">
                        <div class="iwcc-buttons">
                            <a class="iwcc-save-selection iwcc-close"><?= $iwcc->texts['button_accept'] ?></a>
                            <a class="iwcc-accept-all iwcc-close"><?= $iwcc->texts['button_select_all'] ?></a>
                        </div>
                        <div class="iwcc-sitelinks">
                            <?php
                            foreach ($iwcc->links as $v) {
                                echo '<a href="'.rex_getUrl($v).'">'.rex_article::get($v)->getName().'</a>';
                            }
                            ?>
                        </div>
                    </div>
                    <a class="icon-cancel-circled iwcc-close iwcc-close-box"></a>
                </div>
            </div>
            <?php
            foreach ($iwcc->scripts as $uid => $script) {
                echo '<div style="display: none" class="iwcc-script" data-uid="'.$uid.'" data-script="'.$script.'"></div>';
            }
            ?>
        </div>
    </script>
<?php endif; ?>
