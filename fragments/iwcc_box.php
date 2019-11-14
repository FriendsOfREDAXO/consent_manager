<?php
$iwcc = new iwcc_frontend($this->getVar('forceCache'));
$iwcc->setDomain($_SERVER['HTTP_HOST']);
if ($this->getVar('debug'))
{
    dump($iwcc);
}
?>
<?php if ($iwcc->cookiegroups): ?>
    <link href="/assets/addons/iwcc/fontello/css/fontello.css" rel="stylesheet" type="text/css">
    <link href="/assets/addons/iwcc/pretty-checkbox.min.css" rel="stylesheet" type="text/css">
    <link href="/assets/addons/iwcc/iwcc_frontend.css" rel="stylesheet" type="text/css">
    <script src="/assets/addons/iwcc/js.cookie-2.2.1.min.js"></script>
    <script src="/assets/addons/iwcc/iwcc_frontend.js"></script>
    <script id="iwcc-template" type="text/template">
        <div class="iwcc-background iwcc-hidden <?= $iwcc->boxClass ?>" id="iwcc-background" data-domain-name="<?= $iwcc->domainName ?>">
            <div class="iwcc-wrapper" id="iwcc-wrapper">
                <div class="iwcc-wrapper-inner">
                    <div class="iwcc-summary" id="iwcc-summary">
                        <p class="iwcc-headline"><?= $iwcc->texts['headline'] ?></p>
                        <p class="iwcc-text"><?= nl2br($iwcc->texts['description']) ?></p>
                        <div class="iwcc-cookiegroups">
                            <?php
                            foreach ($iwcc->cookiegroups as $cookiegroup)
                            {
                                if ($cookiegroup['required'])
                                {
                                    echo '<div class="iwcc-cookiegroup-checkbox pretty p-icon p-curve p-locked">';
                                    echo '<input type="checkbox" data-action="toggle-cookie" data-uid="' . $cookiegroup['uid'] . '" data-cookie-uids=\'' . json_encode($cookiegroup['cookie_uids']) . '\' checked>';
                                    echo '<div class="state">';
                                    echo '<i class="icon icon-ok-1"></i>';
                                    echo '<label>' . $cookiegroup['name'] . '</label>';
                                    echo '</div>';
                                    if ($cookiegroup['script'])
                                    {
                                        echo '<div class="iwcc-script" data-script="' . $cookiegroup['script'] . '"></div>';
                                    }
                                    echo '</div>';
                                }
                                else
                                {
                                    echo '<div class="iwcc-cookiegroup-checkbox pretty p-icon p-curve">';
                                    echo '<input type="checkbox" data-uid="' . $cookiegroup['uid'] . '" data-cookie-uids=\'' . json_encode($cookiegroup['cookie_uids']) . '\'>';
                                    echo '<div class="state">';
                                    echo '<i class="icon icon-ok-1"></i>';
                                    echo '<label>' . $cookiegroup['name'] . '</label>';
                                    echo '</div>';
                                    if ($cookiegroup['script'])
                                    {
                                        echo '<div class="iwcc-script" data-script="' . $cookiegroup['script'] . '"></div>';
                                    }
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
                        foreach ($iwcc->cookiegroups as $cookiegroup)
                        {
                            echo '<div class="iwcc-cookiegroup-title iwcc-headline">';
                            echo $cookiegroup['name'] . ' <span>(' . count($cookiegroup['cookie']) . ')</span>';
                            echo '</div>';
                            echo '<div class="iwcc-cookiegroup-description">';
                            echo $cookiegroup['description'];
                            echo '</div>';
                            echo '<div class="iwcc-cookiegroup">';
                            foreach ($cookiegroup['cookie'] as $cookie)
                            {
                                echo '<div class="iwcc-cookie">';
                                echo '<span class="iwcc-cookie-name"><strong>' . $cookie['cookie_name'] . '</strong> (' . $cookie['service_name'] . ')</span>';
                                echo '<span class="iwcc-cookie-description">' . $cookie['description'] . '</span>';
                                echo '<span class="iwcc-cookie-description">' . $iwcc->texts['lifetime'] . ' ' . $cookie['cookie_lifetime'] . '</span>';
                                echo '<span class="iwcc-cookie-provider">' . $iwcc->texts['provider'] . ' ' . $cookie['provider'] . '</span>';
                                echo '<span class="iwcc-cookie-link-privacy-policy"><a href="' . $cookie['provider_link_privacy'] . '">' . $iwcc->texts['link_privacy'] . '</a></span>';
                                echo '</div>';
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>
                    <div class="iwcc-buttons-sitelinks">
                        <div class="iwcc-buttons">
                            <a class="iwcc-save-selection iwcc-close">Auswahl bestätigen</a>
                            <a class="iwcc-accept-all iwcc-close">Alle auswählen</a>
                        </div>
                        <div class="iwcc-sitelinks">
                            <?php
                            foreach ($iwcc->links as $v)
                            {
                                echo '<a href="' . rex_getUrl($v) . '">' . rex_article::get($v)->getName() . '</a>';
                            }
                            ?>
                        </div>
                    </div>
                    <a class="icon-cancel-circled iwcc-close iwcc-close-box"></a>
                </div>
            </div>
        </div>
    </script>
<?php endif; ?>