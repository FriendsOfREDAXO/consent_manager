<?php
/**
 * Demo Consent Box für Theme-Preview
 * Verwendet hart-kodierte Demo-Daten ohne DB-Zugriff.
 */

// Demo-Daten für Preview (aus minimal_setup.json)
$demo_texts = [
    'headline' => 'Datenschutz-Einstellungen',
    'description' => 'Diese Website verwendet Cookies, um Ihnen ein optimales Nutzererlebnis zu bieten. Sie können Ihre Einwilligung jederzeit ändern oder widerrufen.',
    'button_accept' => 'Auswahl bestätigen',
    'button_select_all' => 'Alle auswählen',
    'button_select_none' => 'Nur notwendige',
    'show_details' => 'Details',
    'hide_details' => 'Details',
];

$demo_cookiegroups = [
    [
        'uid' => 'required',
        'name' => 'Technisch notwendig',
        'description' => 'Diese Cookies sind für die Grundfunktionen der Website erforderlich und können nicht deaktiviert werden.',
        'required' => true,
        'cookie_uids' => ['consent_manager', 'phpsessid'],
    ],
    [
        'uid' => 'marketing',
        'name' => 'Marketing',
        'description' => 'Diese Cookies werden verwendet, um Ihnen relevante Werbung und Marketinginhalte anzuzeigen.',
        'required' => false,
        'cookie_uids' => ['google_analytics', 'facebook_pixel'],
    ],
];

$demo_cookies = [
    'consent_manager' => [
        'service_name' => 'Consent Manager Cookie',
        'provider' => 'Diese Website',
        'definition' => "- name: consent_manager\n  time: \"14 Tage\"\n  desc: \"Speichert Ihre Einwilligung zu Cookies.\"",
    ],
    'phpsessid' => [
        'service_name' => 'PHP Session Cookie',
        'provider' => 'Diese Website',
        'definition' => "- name: PHPSESSID\n  time: \"Session\"\n  desc: \"Technisch erforderlich für die Funktionalität der Website.\"",
    ],
    'google_analytics' => [
        'service_name' => 'Google Analytics',
        'provider' => 'Google LLC',
        'definition' => "- name: _ga\n  time: \"2 Jahre\"\n  desc: \"Wird verwendet, um Benutzer zu unterscheiden.\"",
    ],
    'facebook_pixel' => [
        'service_name' => 'Facebook Pixel',
        'provider' => 'Meta Platforms Ireland Limited',
        'definition' => "- name: _fbp\n  time: \"3 Monate\"\n  desc: \"Wird verwendet, um Werbemaßnahmen zu optimieren.\"",
    ],
];

// Dummy-Werte
$domainName = rex_request::server('HTTP_HOST', 'string', 'example.com');
$version = 5;
$cacheLogId = 1;
$boxClass = '';

?>
<script nonce="<?= rex_response::getNonce() ?>">
    // Demo-Konfiguration für Preview
    var consent_manager_parameters = {
        domain: "<?= rex_escape($domainName) ?>",
        version: <?= $version ?>,
        cacheLogId: <?= $cacheLogId ?>,
        cookieLifetime: 14,
        focus: "0",
        mode: "opt-in",
        texts: <?= json_encode($demo_texts, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        groups: <?= json_encode($demo_cookiegroups, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
    };
</script>
        <div tabindex="-1" class="consent_manager-background consent_manager-hidden <?= rex_escape($boxClass) ?>" id="consent_manager-background" data-domain-name="<?= rex_escape($domainName) ?>" data-version="<?= $version ?>" data-consentid="<?= uniqid('', true) ?>" data-cachelogid="<?= $cacheLogId ?>" data-nosnippet aria-hidden="true">
            <div class="consent_manager-wrapper" id="consent_manager-wrapper" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="consent_manager-headline">
                <div class="consent_manager-wrapper-inner">
                    <div class="consent_manager-summary" id="consent_manager-summary">
                        <p class="consent_manager-headline" id="consent_manager-headline"><?= rex_escape($demo_texts['headline']) ?></p>
                        <p class="consent_manager-text"><?= nl2br(rex_escape($demo_texts['description'])) ?></p>
                        <div class="consent_manager-cookiegroups">
                            <?php
                            foreach ($demo_cookiegroups as $cookiegroup) {
                                if (count($cookiegroup['cookie_uids']) >= 1) {
                                    if ($cookiegroup['required']) {
                                        echo '<div class="consent_manager-cookiegroup-checkbox">';
                                        echo '<label for="' . rex_escape($cookiegroup['uid']) . '"><input type="checkbox" disabled="disabled" data-action="toggle-cookie" id="' . rex_escape($cookiegroup['uid']) . '" data-uid="' . rex_escape($cookiegroup['uid']) . '" data-cookie-uids=\'' . json_encode($cookiegroup['cookie_uids']) . '\' checked>';
                                        echo '<span>' . rex_escape($cookiegroup['name']) . '</span></label>';
                                        echo '</div>' . PHP_EOL;
                                    } else {
                                        echo '<div class="consent_manager-cookiegroup-checkbox">';
                                        echo '<label for="' . rex_escape($cookiegroup['uid']) . '"><input type="checkbox" data-action="toggle-cookie" id="' . rex_escape($cookiegroup['uid']) . '" data-uid="' . rex_escape($cookiegroup['uid']) . '" data-cookie-uids=\'' . json_encode($cookiegroup['cookie_uids']) . '\'>';
                                        echo '<span>' . rex_escape($cookiegroup['name']) . '</span></label>';
                                        echo '</div>' . PHP_EOL;
                                    }
                                }
                            }
                            ?>
                        </div>
                        <div class="consent_manager-show-details"><a href="#consent_manager-detail"><?= rex_escape($demo_texts['show_details']) ?></a></div>
                        <div class="consent_manager-buttons">
                            <button class="consent_manager-btn consent_manager-btn-select-none"><?= rex_escape($demo_texts['button_select_none']) ?></button>
                            <button class="consent_manager-btn consent_manager-btn-select-all"><?= rex_escape($demo_texts['button_select_all']) ?></button>
                            <button class="consent_manager-btn consent_manager-btn-success consent_manager-btn-accept"><?= rex_escape($demo_texts['button_accept']) ?></button>
                        </div>
                    </div>
                    <div class="consent_manager-detail consent_manager-hidden" id="consent_manager-detail">
                        <p class="consent_manager-headline"><?= rex_escape($demo_texts['headline']) ?></p>
                        <p class="consent_manager-text"><?= nl2br(rex_escape($demo_texts['description'])) ?></p>
                        <div class="consent_manager-cookiegroups">
                            <?php
                            foreach ($demo_cookiegroups as $cookiegroup) {
                                if (count($cookiegroup['cookie_uids']) >= 1) {
                                    $countDefs = 0;
                                    foreach ($cookiegroup['cookie_uids'] as $cookieUid) {
                                        if (isset($demo_cookies[$cookieUid]['definition'])) {
                                            $defs = explode("\n-", $demo_cookies[$cookieUid]['definition']);
                                            $countDefs += count($defs);
                                        }
                                    }

                                    echo '<div class="consent_manager-cookiegroup-title consent_manager-headline">';
                                    echo rex_escape($cookiegroup['name']) . ' <span class="consent_manager-cookiegroup-number">(' . $countDefs . ')</span>';
                                    echo '</div>';
                                    echo '<div class="consent_manager-cookiegroup-description">';
                                    echo rex_escape($cookiegroup['description']);
                                    echo '</div>';
                                    echo '<div class="consent_manager-cookiegroup">';

                                    foreach ($cookiegroup['cookie_uids'] as $cookieUid) {
                                        if (isset($demo_cookies[$cookieUid])) {
                                            $cookie = $demo_cookies[$cookieUid];
                                            echo '<div class="consent_manager-cookie">';
                                            echo '<strong>' . rex_escape($cookie['service_name']) . '</strong>';
                                            if ('' !== $cookie['provider']) {
                                                echo '<span>Anbieter: ' . rex_escape($cookie['provider']) . '</span>';
                                            }
                                            if ('' !== $cookie['definition']) {
                                                // Definition als YAML-Liste parsen
                                                $definitions = explode("\n-", $cookie['definition']);
                                                foreach ($definitions as $def) {
                                                    if ('' !== trim($def) && '-' !== trim($def)) {
                                                        echo '<span>' . nl2br(rex_escape(trim($def, "- \n\r"))) . '</span>';
                                                    }
                                                }
                                            }
                                            echo '</div>';
                                        }
                                    }
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                        <div class="consent_manager-show-details"><a href="#consent_manager-summary"><?= rex_escape($demo_texts['hide_details']) ?></a></div>
                        <div class="consent_manager-buttons">
                            <button class="consent_manager-btn consent_manager-btn-select-none"><?= rex_escape($demo_texts['button_select_none']) ?></button>
                            <button class="consent_manager-btn consent_manager-btn-select-all"><?= rex_escape($demo_texts['button_select_all']) ?></button>
                            <button class="consent_manager-btn consent_manager-btn-success consent_manager-btn-accept"><?= rex_escape($demo_texts['button_accept']) ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
