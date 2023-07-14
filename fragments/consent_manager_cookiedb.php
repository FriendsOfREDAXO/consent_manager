<?php

$consent_manager = new consent_manager_frontend($this->getVar('forceCache'));
if (is_string(rex_request::server('HTTP_HOST'))) {
    $consent_manager->setDomain(rex_request::server('HTTP_HOST'));
}

$output = '';

if ($consent_manager->cookiegroups) { /** phpstan-ignore-line */
    // Cookie Consent + History
    $cookiedata = [];
    if (is_string(rex_request::cookie('consent_manager'))) {
        $cookiedata = (array) json_decode(rex_request::cookie('consent_manager'), true);
    }
    $consent_manager_cookie = null !== rex_request::cookie('consent_manager') ? $cookiedata : null;
    if (null !== $consent_manager_cookie && isset($consent_manager_cookie['cachelogid'])) {
        $db = rex_sql::factory();
        $db->setDebug(false);
        $db->setQuery('SELECT ' . rex::getTable('consent_manager_consent_log') . '.*
                        FROM ' . rex::getTable('consent_manager_consent_log') . '
                        WHERE ' . rex::getTable('consent_manager_consent_log') . '.cachelogid = :cachelogid
                        ORDER BY ' . rex::getTable('consent_manager_consent_log') . '.id DESC
                        LIMIT 5', ['cachelogid' => $consent_manager_cookie['cachelogid']],
        );
        $history = $db->getArray();
        $consents = [];
        if (isset($history[0]['consents'])) {
            $consents = (array) json_decode((string) $history[0]['consents']);
        }

        $consents_service_names = [];
        foreach ($consents as $consent) {
            $consents_service_names[] = $consent_manager->cookies[$consent]['service_name'] . ' (' . $consent . ')';
        }
        $consents_uids_output = implode(', ', $consents_service_names);

        $output .= '<h2>' . $consent_manager->texts['headline_currentconsent'] . '</h2>';
        $output .= '<p class="consent_manager-history-date"><span>' . $consent_manager->texts['consent_date'] . ':</span> ' . ($history[0]['createdate'] ?? '-') . '</p>';
        $output .= '<p class="consent_manager-history-id"><span>' . $consent_manager->texts['consent_id'] . ':</span> ' . ($history[0]['consentid'] ?? '-') . '</p>';
        $output .= '<p class="consent_manager-history-consents"><span>' . $consent_manager->texts['consent_consents'] . ':</span> ' . $consents_uids_output . '</p>';
        $output .= '<p><a class="consent_manager-show-box">' . $consent_manager->texts['edit_consent'] . '</a></p>'; // mit consent_manager-show-box-reload funktionierts nicht korrekt

        $output .= '<h2>' . $consent_manager->texts['headline_historyconsent'] . '</h2>';
        $output .= '<table class="consent_manager-historytable">';
        $output .= '<tr>
                        <th class="consent_manager-history-date">' . $consent_manager->texts['consent_date'] . '</th>
                        <th class="consent_manager-history-id">' . $consent_manager->texts['consent_id'] . '</th>
                        <th class="consent_manager-history-consents">' . $consent_manager->texts['consent_consents'] . '</th>
                    </tr>';
        foreach ($history as $historyentry) {
            $consents = (array) json_decode((string) $historyentry['consents']);
            $consents_service_names = [];
            foreach ($consents as $consent) {
                $consents_service_names[] = $consent_manager->cookies[$consent]['service_name'] . ' (' . $consent . ')';
            }
            $consents_uids_output = implode(', ', $consents_service_names);
            $output .= '<tr>';
            $output .= '<td class="consent_manager-history-date">' . $historyentry['createdate'] . '</td>';
            $output .= '<td class="consent_manager-history-id">' . $historyentry['consentid'] . '</td>';
            $output .= '<td class="consent_manager-history-consents">' . $consents_uids_output . '</td>';
            $output .= '</tr>';
        }
        $output .= '</table>';
    }

    // Cookies We May Use

    $output .= '<hr>';
    $output .= '<h2>' . $consent_manager->texts['headline_mayusedcookies'] . '</h2>';

    foreach ($consent_manager->cookiegroups as $cookiegroup) {
        if (count($cookiegroup['cookie_uids']) >= 1) {
            $output .= '<div class="consent_manager-cookiegroup-title consent_manager-headline">';
            $output .= $cookiegroup['name'] . ' <span>(' . count($cookiegroup['cookie_uids']) . ')</span>';
            $output .= '</div>';
            $output .= '<div class="consent_manager-cookiegroup-description">';
            $output .= $cookiegroup['description'];
            $output .= '</div>';
            $output .= '<div class="consent_manager-cookiegroup">';
            $output .= '<table class="consent_manager-cookietable">';
            $output .= '<tr>
                            <th class="consent_manager-cookie-name">' . $consent_manager->texts['cookiename'] . '</th>
                            <th class="consent_manager-cookie-provider">' . $consent_manager->texts['provider'] . '</th>
                            <th class="consent_manager-cookie-description">' . $consent_manager->texts['usage'] . '</th>
                            <th class="consent_manager-cookie-lifetime">' . $consent_manager->texts['lifetime'] . '</th>
                            <th class="consent_manager-cookie-service">' . $consent_manager->texts['service'] . '</th>
                        </tr>';
            foreach ($cookiegroup['cookie_uids'] as $cookieUid) {
                $cookie = $consent_manager->cookies[$cookieUid];
                foreach ($cookie['definition'] as $def) {
                    $output .= '<tr>';
                    $output .= '<td class="consent_manager-cookie-name">' . $def['cookie_name'] . '</td>';
                    $output .= '<td class="consent_manager-cookie-provider"><a href="' . $cookie['provider_link_privacy'] . '">' . $cookie['provider'] . '</a></td>';
                    $output .= '<td class="consent_manager-cookie-description">' . $def['description'] . '</td>';
                    $output .= '<td class="consent_manager-cookie-lifetime">' . $def['cookie_lifetime'] . '</td>';
                    $output .= '<td class="consent_manager-cookie-service">' . $cookie['service_name'] . '</td>';
                    $output .= '</tr>';
                }
            }
            $output .= '</table>';
            $output .= '</div>';
        }
    }

    // Cookies actually used

    $output .= '<hr>';
    $output .= '<h2>' . $consent_manager->texts['headline_usedcookies'] . '</h2>';
    $cookiedb = [];

    foreach ($consent_manager->cookies as $cookies) {
        foreach ($cookies['definition'] as $def) {
            $cookiedb[$def['cookie_name']] = [
                'service_name' => $cookies['service_name'],
                'provider' => $cookies['provider'],
                'lifetime' => $def['cookie_lifetime'],
                'description' => $def['description'],
            ];
        }
    }

    $output .= '<div class="consent_manager-cookiegroup">';
    $output .= '<table class="consent_manager-cookietable">';
    $output .= '<tr>
                    <th class="consent_manager-cookie-name">' . $consent_manager->texts['cookiename'] . '</th>
                    <th class="consent_manager-cookie-provider">' . $consent_manager->texts['provider'] . '</th>
                    <th class="consent_manager-cookie-description">' . $consent_manager->texts['usage'] . '</th>
                    <th class="consent_manager-cookie-lifetime">' . $consent_manager->texts['lifetime'] . '</th>
                    <th class="consent_manager-cookie-service">' . $consent_manager->texts['service'] . '</th>
                </tr>';
    foreach ($_COOKIE as $cookiename => $cookieValue) { /** @phpstan-ignore-line */
        $output .= '<tr>';
        $output .= '<td class="consent_manager-cookie-name">' . $cookiename . '</td>';
        if (isset($cookiedb[$cookiename]) || array_key_exists($cookiename, $cookiedb)) {
            $output .= '<td class="consent_manager-cookie-provider">' . $cookiedb[$cookiename]['provider'] . '</td>';
            $output .= '<td class="consent_manager-cookie-description">' . $cookiedb[$cookiename]['description'] . '</td>';
            $output .= '<td class="consent_manager-cookie-lifetime">' . $cookiedb[$cookiename]['lifetime'] . '</td>';
            $output .= '<td class="consent_manager-cookie-service">' . $cookiedb[$cookiename]['service_name'] . '</td>';
        } else {
            $output .= '<td colspan="4">' . $consent_manager->texts['missingdescription'] . '</td>';
        }
        $output .= '</tr>';
    }
    $output .= '</table>';
    $output .= '</div>';

    echo $output;
}
