TRUNCATE `rex_consent_manager_cookie`;
INSERT INTO `rex_consent_manager_cookie` (`pid`, `id`, `clang_id`, `uid`, `service_name`, `provider`, `provider_link_privacy`, `definition`, `script`, `placeholder_text`, `placeholder_image`, `createuser`, `updateuser`, `createdate`, `updatedate`) VALUES
(1,	1,	1,	'consent_manager',	'Datenschutz Cookie',	'Diese Website',	'',	'-\n name: consent_manager\n time: \"1 Jahr\"\n desc: \"Speichert Ihre Auswahl bzgl. der Cookies.\"',	'',	'',	'',	'',	'',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00'),
(2,	2,	1,	'google-analytics',	'Google Analytics',	'Google',	'',	'-\r\n name: _ga\r\n time: \"2 Jahre\"\r\n desc: \"Wird verwendet, um Nutzer zu unterscheiden.\"\r\n-\r\n name: _gid\r\n time: \"1 Tag\"\r\n desc: \"Wird verwendet, um Nutzer zu unterscheiden.\"\r\n-\r\n name: _ga_<container-id>\r\n time: \"2 Jahre\"\r\n desc: \"Wird verwendet, um den Sitzungsstatus zu erhalten.\"\r\n-\r\n name: _gac_gb_<container-id>\r\n time: \"90 Tage\"\r\n desc: \"Enthält kampagnenbezogene Informationen."',	'// \'G-XXXXXXXXXX\' durch eigenes Property ersetzen\r\n<script async src=\"https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXX\"></script>\r\n<script>\r\n	// Beispiel für das Nachladen von Google Analytics (GA4)\r\n	window.dataLayer = window.dataLayer || [];\r\n	function gtag(){dataLayer.push(arguments);}\r\n	gtag(\'js\', new Date());\r\n	gtag(\'config\', \'G-XXXXXXXXX\', {\'anonymize_ip\': true});\r\n</script>',	'',	'',	'',	'',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00'),
(3,	3,	1,	'matomo',	'Matomo',	'',	'',	'-\n name: _pk_id\n time: \"13 Monate\"\n desc: \"Speichert für jeden Besucher der Website eine anonyme ID. Anhand der ID können Seitenaufrufe einem Besucher zugeordnet werden.\"\n-\n name: _pk_ref\n time: \"6 Monate\"\n desc: \"Zur Speicherung des Verweises der ursprünglich zum Besuch der Website verwendet wurde.\"\n-\n name: _pk_ses\n time: \"30 Minuten\"\n desc: \"Hilfscookie für temporäre Daten\"\n-\n name: _pk_cvar\n time: \"30 Minuten\"\n desc: \"Hilfscookie für temporäre Daten\"\n-\n name: _pk_hsr\n time: \"30 Minuten\"\n desc: \"Hilfscookie für temporäre Daten\"',	'',	'',	'',	'',	'',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00'),
(4,	4,	1,	'google-maps',	'Google Maps',	'Google LLC',	'',	'-\r\n name: /\r\n time: \"-\"\r\n desc: \"Google Maps ist ein Online-Kartendienst des US-amerikanischen Unternehmens Google LLC. Über die Google Maps API lassen sich Karten auf der Website einbetten.\"',	'<script type=\"text/javascript\">\r\n	// Beispiel für Nachladen von Google Maps via callback-Aufruf:\r\n	// initGmaps() muss definiert sein und den API-Aufruf starten\r\n	// key=XXX durch eigenen API-Key ersetzen\r\n	if ($(\'#deinGoogleMapsDivContainer\').length > 0) {\r\n		var script = document.createElement(\'script\');\r\n		script.type = \'text/javascript\';\r\n		script.src = \'https://maps.google.com/maps/api/js?key=XXX&callback=initGmaps\';\r\n		document.head.appendChild(script);\r\n	}\r\n</script>',	'',	'',	'',	'',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00'),
(5,	5,	1,	'facebook',	'Facebook Pixel',	'Facebook',	'',	'-\n name: _fbp\n time: \"3 Monate\"\n desc: \"Wird genutzt, um eine Reihe von Werbeprodukten anzuzeigen, zum Beispiel Echtzeitgebote dritter Werbetreibender.\"',	'',	'',	'',	'',	'',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00'),
(6,	6,	1,	'phpsessid',	'Session',	'Diese Website',	'',	'-\n name: PHPSESSID\n time: \"Session\"\n desc: \"Die sog. Session-ID ist ein zufällig ausgewählter Schlüssel, der die Sessiondaten auf dem Server eindeutig identifiziert. Dieser Schlüssel kann z.B. über Cookies oder als Bestandteil der URL an ein Folgescript übergeben werden, damit dieses die Sessiondaten auf dem Server wiederfinden kann.\"',	'',	'',	'',	'',	'',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00'),
(7,	7,	1,	'youtube',	'YouTube',	'Google',	'',	'-\n name: YSC\n time: \"Session\"\n desc: \"Registriert eine eindeutige ID, um Statistiken der Videos von YouTube, die der Benutzer gesehen hat, zu behalten.\"\n-\n name: VISITOR_INFO1_LIVE\n time: \"179 Tage\"\n desc: \"Versucht, die Benutzerbandbreite auf Seiten mit integrierten YouTube-Videos zu schätzen.\"\n-\n name: GPS\n time: \"Session\"\n desc: \"Registriert eine eindeutige ID auf mobilen Geräten, um Tracking basierend auf dem geografischen GPS-Standort zu ermöglichen.\"',	'',	'',	'',	'',	'',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00');

TRUNCATE `rex_consent_manager_cookiegroup`;
INSERT INTO `rex_consent_manager_cookiegroup` (`pid`, `id`, `clang_id`, `domain`, `uid`, `prio`, `required`, `name`, `description`, `cookie`, `script`, `createuser`, `updateuser`, `createdate`, `updatedate`) VALUES
(1,	1,	1,	'',	'required',	1,	'|1|',	'Notwendig',	'Notwendige Cookies ermöglichen grundlegende Funktionen und sind für die einwandfreie Funktion der Website erforderlich.',	NULL,	'',	'',	'',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00'),
(2,	2,	1,	'',	'statistics',	2,	NULL,	'Statistik',	'Statistik Cookies erfassen Informationen anonym. Diese Informationen helfen uns zu verstehen, wie unsere Besucher unsere Website nutzen.',	'|google-analytics|',	'',	'',	'',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00'),
(3,	3,	1,	'',	'external',	4,	NULL,	'Externe Medien',	'Inhalte von Videoplattformen oder Kartendiensten sind standardmäßig deaktiviert. Wenn Cookies von externen Medien akzeptiert werden, bedarf der Zugriff auf diese Inhalte keiner manuellen Zustimmung mehr.',	'|google-maps|youtube|',	'',	'',	'',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00'),
(4,	4,	1,	'',	'marketing',	3,	NULL,	'Marketing',	'Diese Cookies helfen uns bei der Analyse von Verbindungen zu und von Partnern sowie Kampagnen auf unserer Webseite. Durch diese Technologie werden Nutzer, die unsere Webseite bereits besucht und sich für unsere Webseite interessiert haben, durch zielgerichtete Werbung auf den Seiten der Partner erneut angesprochen.',	'|facebook|',	'',	'',	'',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00');

TRUNCATE `rex_consent_manager_text`;
INSERT INTO `rex_consent_manager_text` (`pid`, `id`, `clang_id`, `uid`, `text`) VALUES
(1, 1, 1, 'headline', 'Datenschutz-Einstellungen'),
(2, 2, 1, 'description', 'Unsere Website verwendet externe Dienste für verschiedene Zwecke, wie z.B. für Statistiken oder die Ausspielung von Multimedia. Durch Auswahl und Bestätigung der Dienste stimmen Sie der Übertragung von Daten an diese Dienste zu. Diese Dienste und auch diese Website setzen ggf. auch Cookies. Die  Auswahl kann jederzeit  geändert und widerrufen werden.'),
(3, 3, 1, 'toggle_details', 'Details anzeigen/ausblenden'),
(4, 4, 1, 'provider', 'Anbieter:'),
(5, 5, 1, 'link_privacy', 'Datenschutzerklärung'),
(6, 6, 1, 'lifetime', 'Laufzeit:'),
(7, 7, 1, 'button_accept', 'Auswahl bestätigen'),
(8, 8, 1, 'button_select_all', 'Alle auswählen'),
(9, 9, 1, 'cookiename', 'Cookie-Name:'),
(10, 10, 1, 'service', 'Service:'),
(11, 11, 1, 'usage', 'Verwendungszweck:'),
(12, 12, 1, 'category', 'Kategorie:'),
(13, 13, 1, 'value', 'Wert:'),
(14, 14, 1, 'missingdescription', 'Keine Informationen vorhanden'),
(15, 15, 1, 'headline_usedcookies', 'Aktuell verwendet diese Website folgende Cookies'),
(16, 16, 1, 'headline_mayusedcookies', 'Cookies, die diese Website verwenden kann'),
(17, 17, 1, 'headline_currentconsent', 'Ihre aktuelle Einwilligung'),
(18, 18, 1, 'headline_historyconsent', 'Ihr Einwilligungs-Verlauf'),
(19, 19, 1, 'consent_date', 'Einwilligungsdatum'),
(20, 20, 1, 'consent_id', 'Einwilligungs-ID'),
(21, 21, 1, 'consent_consents', 'Einwilligungen'),
(22, 22, 1, 'edit_consent', 'Cookie Einstellungen bearbeiten'),
(23, 23, 1, 'button_select_none', 'Nur notwendige');
