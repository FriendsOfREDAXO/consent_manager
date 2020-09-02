<?php
$iwcc = new iwcc_frontend($this->getVar('forceCache'));
$iwcc->setDomain($_SERVER['HTTP_HOST']);
if ($this->getVar('debug')) {	
    dump($iwcc);
	print_r($_COOKIE);
}

$output = '';

if ($iwcc->cookiegroups) {
	
	// Cookie Consent + History
	$iwcc_cookie = isset($_COOKIE['iwcc']) ? json_decode($_COOKIE['iwcc'],1) : false;
	if ($iwcc_cookie) {
		
		$db = rex_sql::factory();
		$db->setDebug(false);
		$db->setQuery('SELECT '.rex::getTable('iwcc_consent_log').'.* 
						FROM '.rex::getTable('iwcc_consent_log').' 
						WHERE '.rex::getTable('iwcc_consent_log').'.cachelogid = :cachelogid
						ORDER BY '.rex::getTable('iwcc_consent_log').'.id DESC
						LIMIT 5'						
						,['cachelogid'=>$iwcc_cookie['cachelogid']]
					);
		$history = $db->getArray();
		
		$consents = json_decode($history[0]['consents']);
		//$consents_uids_output = implode(', ', $consents);
		$consents_service_names = array();
		foreach($consents as $consent) {
			$consents_service_names[] = $iwcc->cookies[$consent]['service_name'].' ('.$consent.')';
		}
		$consents_uids_output = implode(', ', $consents_service_names);
		
		$output .= '<h2>'.$iwcc->texts['headline_currentconsent'].'</h2>';
		$output .= '<p class="iwcc-history-date"><span>'.$iwcc->texts['consent_date'].':</span> '.$history[0]['createdate'].'</p>';		
		$output .= '<p class="iwcc-history-id"><span>'.$iwcc->texts['consent_id'].':</span> '.$history[0]['consentid'].'</p>';
		$output .= '<p class="iwcc-history-consents"><span>'.$iwcc->texts['consent_consents'].':</span> '.$consents_uids_output.'</p>';
		$output .= '<p><a class="iwcc-show-box">'.$iwcc->texts['edit_consent'].'</a></p>'; // mit iwcc-show-box-reload funktionierts nicht korrekt
		
		$output .= '<h2>'.$iwcc->texts['headline_historyconsent'].'</h2>';
		$output .= '<table class="iwcc-historytable">';
		$output .= '<tr>
						<th class="iwcc-history-date">'.$iwcc->texts['consent_date'].'</th>
						<th class="iwcc-history-id">'.$iwcc->texts['consent_id'].'</th>
						<th class="iwcc-history-consents">'.$iwcc->texts['consent_consents'].'</th>
					</tr>';
		foreach ($history as $historyentry) {	
			$consents = json_decode($historyentry['consents']);
			//$consents_uids_output = implode(', ', $consents);
			$consents_service_names = array();
			foreach($consents as $consent) {
				$consents_service_names[] = $iwcc->cookies[$consent]['service_name'].' ('.$consent.')';
			}
			$consents_uids_output = implode(', ', $consents_service_names);
			$output .= '<tr>';
			$output .= '<td class="iwcc-history-date">'.$historyentry['createdate'].'</td>';		
			$output .= '<td class="iwcc-history-id">'.$historyentry['consentid'].'</td>';
			$output .= '<td class="iwcc-history-consents">'.$consents_uids_output.'</td>';
			$output .= '</tr>';
		}
		$output .= '</table>';
	}
	
	// Cookies We May Use
	
	$output .= '<hr>';
	$output .= '<h2>'.$iwcc->texts['headline_mayusedcookies'].'</h2>';

	foreach ($iwcc->cookiegroups as $cookiegroup) {
		$output .= '<div class="iwcc-cookiegroup-title iwcc-headline">';
		$output .= $cookiegroup['name'].' <span>('.count($cookiegroup['cookie_uids']).')</span>';
		$output .= '</div>';
		$output .= '<div class="iwcc-cookiegroup-description">';
		$output .= $cookiegroup['description'];
		$output .= '</div>';
		$output .= '<div class="iwcc-cookiegroup">';
		$output .= '<table class="iwcc-cookietable">';
		$output .= '<tr>
						<th class="iwcc-cookie-name">'.$iwcc->texts['cookiename'].'</th>
						<th class="iwcc-cookie-provider">'.$iwcc->texts['provider'].'</th>
						<th class="iwcc-cookie-description">'.$iwcc->texts['usage'].'</th>
						<th class="iwcc-cookie-lifetime">'.$iwcc->texts['lifetime'].'</th>
						<th class="iwcc-cookie-service">'.$iwcc->texts['service'].'</th>
					</tr>';
		foreach ($cookiegroup['cookie_uids'] as $cookieUid) {
			$cookie = $iwcc->cookies[$cookieUid];
			foreach ($cookie['definition'] as $def) {
				$output .= '<tr>';
				$output .= '<td class="iwcc-cookie-name">'.$def['cookie_name'].'</td>';		
				$output .= '<td class="iwcc-cookie-provider"><a href="'.$cookie['provider_link_privacy'].'">'.$cookie['provider'].'</a></td>';
				$output .= '<td class="iwcc-cookie-description">'.$def['description'].'</td>';
				$output .= '<td class="iwcc-cookie-lifetime">'.$def['cookie_lifetime'].'</td>';
				$output .= '<td class="iwcc-cookie-service">'.$cookie['service_name'].'</td>';
				$output .= '</tr>';
			}
		}
		$output .= '</table>';
		$output .= '</div>';
	}

	// Cookies actually used
	
	$output .= '<hr>';
	$output .= '<h2>'.$iwcc->texts['headline_usedcookies'].'</h2>';
	
	foreach ($iwcc->cookies as $cookies) {
		foreach ($cookies['definition'] as $def) {
			$cookiedb[$def['cookie_name']] = array(			
				"service_name"=>$cookies['service_name'],
				"provider"=>$cookies['provider'],
				"lifetime"=>$def['cookie_lifetime'],
				"description"=>$def['description']					
			);
		}
	}
	
	$output .= '<div class="iwcc-cookiegroup">';
	$output .= '<table class="iwcc-cookietable">';
	$output .= '<tr>
						<th class="iwcc-cookie-name">'.$iwcc->texts['cookiename'].'</th>
						<th class="iwcc-cookie-provider">'.$iwcc->texts['provider'].'</th>
						<th class="iwcc-cookie-description">'.$iwcc->texts['usage'].'</th>
						<th class="iwcc-cookie-lifetime">'.$iwcc->texts['lifetime'].'</th>
						<th class="iwcc-cookie-service">'.$iwcc->texts['service'].'</th>
				</tr>';
	foreach ($_COOKIE as $cookiename => $cookieValue) {
		$output .= '<tr>';
		$output .= '<td class="iwcc-cookie-name">'.$cookiename.'</td>';		
		if (isset($cookiedb[$cookiename]) || array_key_exists($cookiename, $cookiedb)) {
			$output .= '<td class="iwcc-cookie-provider">'.$cookiedb[$cookiename]['provider'].'</td>';
			$output .= '<td class="iwcc-cookie-description">'.$cookiedb[$cookiename]['description'].'</td>';
			$output .= '<td class="iwcc-cookie-lifetime">'.$cookiedb[$cookiename]['lifetime'].'</td>';
			$output .= '<td class="iwcc-cookie-service">'.$cookiedb[$cookiename]['service_name'].'</td>';
		}
		else {
			$output .= '<td colspan="4">'.$iwcc->texts['missingdescription'].'</td>';
		}
		$output .= '</tr>';
	}
	$output .= '</table>';
	$output .= '</div>';

	echo $output;

}