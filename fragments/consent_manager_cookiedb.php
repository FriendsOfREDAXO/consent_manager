<?php
$consent_manager = new consent_manager_frontend($this->getVar('forceCache'));
$consent_manager->setDomain($_SERVER['HTTP_HOST']);

$output = '';

if ($consent_manager->cookiegroups) {

    // Cookie Consent + History
    $consent_manager_cookie = isset($_COOKIE['consent_manager']) ? json_decode($_COOKIE['consent_manager'],1) : false;
    if ($consent_manager_cookie) {

        $db = rex_sql::factory();
        $db->setDebug(false);
        $db->setQuery('SELECT '.rex::getTable('consent_manager_consent_log').'.*
                        FROM '.rex::getTable('consent_manager_consent_log').'
                        WHERE '.rex::getTable('consent_manager_consent_log').'.cachelogid = :cachelogid
                        ORDER BY '.rex::getTable('consent_manager_consent_log').'.id DESC
                        LIMIT 5'
                        ,['cachelogid'=>$consent_manager_cookie['cachelogid']]
                    );
        $history = $db->getArray();
        $consents = [];
        if (isset($history[0]['consents'])) {
            $consents = json_decode($history[0]['consents']);
        }

        //$consents_uids_output = implode(', ', $consents);
        $consents_service_names = array();
        foreach($consents as $consent) {
            // XSS-Schutz: Service-Namen escapen
            $consents_service_names[] = rex_escape($consent_manager->cookies[$consent]['service_name']).' ('.rex_escape($consent).')';
        }
        $consents_uids_output = implode(', ', $consents_service_names);

        $output .= '<h2>'.$consent_manager->texts['headline_currentconsent'].'</h2>';
        // XSS-Schutz: Daten aus consent_log escapen
        $output .= '<p class="consent_manager-history-date"><span>'.$consent_manager->texts['consent_date'].':</span> '.rex_escape($history[0]['createdate'] ?? '-').'</p>';
        $output .= '<p class="consent_manager-history-id"><span>'.$consent_manager->texts['consent_id'].':</span> '.rex_escape($history[0]['consentid'] ?? '-').'</p>';
        $output .= '<p class="consent_manager-history-consents"><span>'.$consent_manager->texts['consent_consents'].':</span> '.$consents_uids_output.'</p>';
        $output .= '<p><a class="consent_manager-show-box">'.$consent_manager->texts['edit_consent'].'</a></p>'; // mit consent_manager-show-box-reload funktionierts nicht korrekt

        $output .= '<h2>'.$consent_manager->texts['headline_historyconsent'].'</h2>';
        $output .= '<table class="consent_manager-historytable">';
        $output .= '<tr>
                        <th class="consent_manager-history-date">'.$consent_manager->texts['consent_date'].'</th>
                        <th class="consent_manager-history-id">'.$consent_manager->texts['consent_id'].'</th>
                        <th class="consent_manager-history-consents">'.$consent_manager->texts['consent_consents'].'</th>
                    </tr>';
        foreach ($history as $historyentry) {
            $consents = json_decode($historyentry['consents']);
            //$consents_uids_output = implode(', ', $consents);
            $consents_service_names = array();
            foreach($consents as $consent) {
                // XSS-Schutz: Service-Namen escapen
                $consents_service_names[] = rex_escape($consent_manager->cookies[$consent]['service_name']).' ('.rex_escape($consent).')';
            }
            $consents_uids_output = implode(', ', $consents_service_names);
            $output .= '<tr>';
            // XSS-Schutz: History-Daten escapen
            $output .= '<td class="consent_manager-history-date">'.rex_escape($historyentry['createdate']).'</td>';
            $output .= '<td class="consent_manager-history-id">'.rex_escape($historyentry['consentid']).'</td>';
            $output .= '<td class="consent_manager-history-consents">'.$consents_uids_output.'</td>';
            $output .= '</tr>';
        }
        $output .= '</table>';
    }

    // Cookies We May Use

    $output .= '<hr>';
    $output .= '<h2>'.$consent_manager->texts['headline_mayusedcookies'].'</h2>';

    foreach ($consent_manager->cookiegroups as $cookiegroup) {
        if (count($cookiegroup['cookie_uids']) >= 1) {
            $output .= '<div class="consent_manager-cookiegroup-title consent_manager-headline">';
            // XSS-Schutz: Name escapen
            $output .= rex_escape($cookiegroup['name']).' <span>('.count($cookiegroup['cookie_uids']).')</span>';
            $output .= '</div>';
            $output .= '<div class="consent_manager-cookiegroup-description">';
            // Description darf HTML enthalten (bewusste Entscheidung)
            $output .= $cookiegroup['description'];
            $output .= '</div>';
            $output .= '<div class="consent_manager-cookiegroup">';
            $output .= '<table class="consent_manager-cookietable">';
            $output .= '<tr>
                            <th class="consent_manager-cookie-name">'.$consent_manager->texts['cookiename'].'</th>
                            <th class="consent_manager-cookie-provider">'.$consent_manager->texts['provider'].'</th>
                            <th class="consent_manager-cookie-description">'.$consent_manager->texts['usage'].'</th>
                            <th class="consent_manager-cookie-lifetime">'.$consent_manager->texts['lifetime'].'</th>
                            <th class="consent_manager-cookie-service">'.$consent_manager->texts['service'].'</th>
                        </tr>';
            foreach ($cookiegroup['cookie_uids'] as $cookieUid) {
                $cookie = $consent_manager->cookies[$cookieUid];
                foreach ($cookie['definition'] as $def) {
                    $output .= '<tr>';
                    // XSS-Schutz: Alle DB-Werte escapen
                    $output .= '<td class="consent_manager-cookie-name">'.rex_escape($def['cookie_name']).'</td>';
                    $output .= '<td class="consent_manager-cookie-provider"><a href="'.rex_escape($cookie['provider_link_privacy']).'">'.rex_escape($cookie['provider']).'</a></td>';
                    // Description darf HTML enthalten (bewusste Entscheidung)
                    $output .= '<td class="consent_manager-cookie-description">'.$def['description'].'</td>';
                    $output .= '<td class="consent_manager-cookie-lifetime">'.rex_escape($def['cookie_lifetime']).'</td>';
                    $output .= '<td class="consent_manager-cookie-service">'.rex_escape($cookie['service_name']).'</td>';
                    $output .= '</tr>';
                }
            }
            $output .= '</table>';
            $output .= '</div>';
        }
    }

    // Cookies actually used

    $output .= '<hr>';
    $output .= '<h2>'.$consent_manager->texts['headline_usedcookies'].'</h2>';

    foreach ($consent_manager->cookies as $cookies) {
        foreach ($cookies['definition'] as $def) {
            $cookiedb[$def['cookie_name']] = array(
                "service_name"=>$cookies['service_name'],
                "provider"=>$cookies['provider'],
                "lifetime"=>$def['cookie_lifetime'],
                "description"=>$def['description']
            );
        }
    }

    $output .= '<div class="consent_manager-cookiegroup">';
    $output .= '<table class="consent_manager-cookietable">';
    $output .= '<tr>
                    <th class="consent_manager-cookie-name">'.$consent_manager->texts['cookiename'].'</th>
                    <th class="consent_manager-cookie-provider">'.$consent_manager->texts['provider'].'</th>
                    <th class="consent_manager-cookie-description">'.$consent_manager->texts['usage'].'</th>
                    <th class="consent_manager-cookie-lifetime">'.$consent_manager->texts['lifetime'].'</th>
                    <th class="consent_manager-cookie-service">'.$consent_manager->texts['service'].'</th>
                </tr>';
    foreach ($_COOKIE as $cookiename => $cookieValue) {
        $output .= '<tr>';
        // XSS-Schutz: Cookie-Name escapen da aus externer Quelle ($_COOKIE)
        $output .= '<td class="consent_manager-cookie-name">'.htmlspecialchars($cookiename, ENT_QUOTES, 'UTF-8').'</td>';
        if (isset($cookiedb[$cookiename]) || array_key_exists($cookiename, $cookiedb)) {
            // XSS-Schutz: DB-Werte escapen
            $output .= '<td class="consent_manager-cookie-provider">'.rex_escape($cookiedb[$cookiename]['provider']).'</td>';
            // Description darf HTML enthalten (bewusste Entscheidung)
            $output .= '<td class="consent_manager-cookie-description">'.$cookiedb[$cookiename]['description'].'</td>';
            $output .= '<td class="consent_manager-cookie-lifetime">'.rex_escape($cookiedb[$cookiename]['lifetime']).'</td>';
            $output .= '<td class="consent_manager-cookie-service">'.rex_escape($cookiedb[$cookiename]['service_name']).'</td>';
        }
        else {
            $output .= '<td colspan="4">'.$consent_manager->texts['missingdescription'].'</td>';
        }
        $output .= '</tr>';
    }
    $output .= '</table>';
    $output .= '</div>';

    echo $output;

}
