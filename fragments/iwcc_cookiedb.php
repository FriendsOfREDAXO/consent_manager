<?php
$iwcc = new iwcc_frontend($this->getVar('forceCache'));
$iwcc->setDomain($_SERVER['HTTP_HOST']);
if ($this->getVar('debug'))
{
	print_r($_COOKIE);
}

if ($iwcc->cookiegroups) {

	$cookieDatabase = array();
	
	foreach ($iwcc->cookiegroups as $cookiegroup) {
		foreach ($cookiegroup['cookie_uids'] as $cookieUid) {
			$cookie = $iwcc->cookies[$cookieUid];
			foreach ($cookie['definition'] as $def) {
				$cookieDatabase[$def['cookie_name']]=array(			
					"service_name"=>$cookie['service_name'],
					"provider"=>$cookie['provider'],
					"category"=>$cookiegroup['name'],
					"required"=>$cookiegroup['required'],
					"lifetime"=>$def['cookie_lifetime'],
					"description"=>$def['description']					
				);
			}
		}
	}

	$outputDatabase = '<table class="cookieInfoTable">';
	$outputDatabase .= '<tr>
							<th class="cookieName">'.$iwcc->texts['cookiename'].'</th>
							<th class="cookieService">'.$iwcc->texts['service'].'</th>
							<th class="cookieProvider">'.$iwcc->texts['provider'].'</th>
							<th class="cookieLifetime">'.$iwcc->texts['lifetime'].'</th>
							<th class="cookieDescription">'.$iwcc->texts['usage'].'</th>
							<th class="cookieCategory">'.$iwcc->texts['category'].'</th>
							<th class="cookieValue">'.$iwcc->texts['value'].'</th>
						</tr>';

	foreach ($_COOKIE as $cookieName => $cookieValue) {
		$outputDatabase .= '<tr>';
		$outputDatabase .= '<td class="cookieName">'.$cookieName.'</td>';
		if (isset($cookieDatabase[$cookieName]) || array_key_exists($cookieName, $cookieDatabase)) {
			$outputDatabase .= '<td class="cookieService">'.$cookieDatabase[$cookieName]['service_name'].'</td>';
			$outputDatabase .= '<td class="cookieProvider">'.$cookieDatabase[$cookieName]['provider'].'</td>';
			$outputDatabase .= '<td class="cookieLifetime">'.$cookieDatabase[$cookieName]['lifetime'].'</td>';
			$outputDatabase .= '<td class="cookieDescription">'.$cookieDatabase[$cookieName]['description'].'</td>';
			$outputDatabase .= '<td class="cookieCategory">'.$cookieDatabase[$cookieName]['category'].'</td>';	
			$outputDatabase .= '<td class="cookieValue">'.$cookieValue.'</td>';
		}
		else {
			$outputDatabase .= '<td colspan="6">'.$iwcc->texts['missingdescription'].'</td>';
		}
		$outputDatabase .= '</tr>';
	}

	$outputDatabase .= '</table>';

	echo $outputDatabase;

}