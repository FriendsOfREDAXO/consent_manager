<?php
$addon = rex_addon::get('consent_manager');

if (0 === rex_article::getCurrentId()) {
    return;
}

$frontend = new consent_manager_frontend(1);
$frontend->setDomain(consent_manager_util::hostname());

// Generiere Consent Component
$clang = rex_clang::getCurrentId();

$consents = [];
foreach ($frontend->cookiegroups as $cookiegroup) {
    $cookies = [];
    if (isset($cookiegroup['cookie_uids'])) {
        foreach ($cookiegroup['cookie_uids'] as $uid) {
            $cookies[] = $frontend->cookies[$uid] ?? null;
        }
    }
    $cookies = array_filter($cookies);

    if (!empty($cookies)) {
        $consents[] = [
            'uid' => $cookiegroup['uid'],
            'name' => $cookiegroup['name'],
            'description' => $cookiegroup['description'],
            'required' => $cookiegroup['required'] === '|1|',
            'cookies' => array_values($cookies),
            'scripts' => array_map(function($cookie) use ($frontend) {
                return [
                    'onAccept' => $frontend->scripts[$cookie['uid']] ?? '',
                    'onReject' => $frontend->scriptsUnselect[$cookie['uid']] ?? ''
                ];
            }, $cookies)
        ];
    }
}

$data = [
    'headline' => $frontend->texts['headline'] ?? '',
    'description' => $frontend->texts['description'] ?? '',
    'privacyPolicy' => $frontend->links['privacy_policy'] ?? '#',
    'legalNotice' => $frontend->links['legal_notice'] ?? '#',
    'cookieGroups' => $consents,
    'texts' => [
        'privacyPolicy' => $frontend->texts['privacy_policy'] ?? 'Datenschutzerklärung',
        'legalNotice' => $frontend->texts['legal_notice'] ?? 'Impressum',
        'buttonAcceptAll' => $frontend->texts['button_accept_all'] ?? 'Alle akzeptieren',
        'buttonAcceptTechnical' => $frontend->texts['button_accept_none'] ?? 'Nur technisch notwendige',
        'buttonSaveSelection' => $frontend->texts['button_save'] ?? 'Auswahl speichern',
        'cookieDetails' => $frontend->texts['toggle_details'] ?? 'Details anzeigen/ausblenden',
        'providedBy' => $frontend->texts['provider'] ?? 'Anbieter:',
        'link' => $frontend->texts['link_privacy'] ?? 'Datenschutzerklärung',
        'lifetime' => $frontend->texts['lifetime'] ?? 'Laufzeit:',
        'purpose' => $frontend->texts['usage'] ?? 'Verwendungszweck:',
        'group' => $frontend->texts['category'] ?? 'Kategorie:'
    ]
];

$attributes = [
    'domain' => consent_manager_util::hostname(),
    'version' => $frontend->version,
    'cache-log-id' => $frontend->cacheLogId,
    'cookie-lifetime' => $addon->getConfig('lifespan', 365),
    'force-reload' => $addon->getConfig('forcereload', false) ? 'true' : null,
    'hide-body-scrollbar' => $addon->getConfig('hidebodyscrollbar', false) ? 'true' : null
];

echo '<consent-manager';
foreach ($attributes as $key => $value) {
    if ($value !== null) {
        echo ' ' . $key . '="' . htmlspecialchars($value) . '"';
    }
}
echo '>';

echo '<script type="application/json">';
echo htmlspecialchars(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo '</script>';

echo '</consent-manager>';
