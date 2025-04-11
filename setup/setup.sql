TRUNCATE TABLE `%TABLE_PREFIX%consent_manager_cookie`;
TRUNCATE TABLE `%TABLE_PREFIX%consent_manager_cookiegroup`;
TRUNCATE TABLE `%TABLE_PREFIX%consent_manager_text`;

INSERT INTO `%TABLE_PREFIX%consent_manager_cookie` (`pid`, `id`, `clang_id`, `uid`, `service_name`, `provider`, `provider_link_privacy`, `definition`, `script`, `script_unselect`, `placeholder_text`, `placeholder_image`, `createuser`, `createdate`, `updateuser`, `updatedate`) VALUES
(1,	1,	1,	'consent_manager',	'Consent Manager',	'Diese Website',	'',	'-\n name: consent_manager\n time: 365\n desc: \"In diesem Cookie werden deine Einwilligungen bzw. Widerrufe der Einwilligung für die Verwendung von weiteren Diensten gespeichert.\"',	'',	'',	'',	'',	'',	'0000-00-00 00:00:00',	'',	'0000-00-00 00:00:00'),
(2,	2,	1,	'session',	'PHP-Session',	'Diese Website',	'',	'-\n name: PHPSESSID\n time: \"Session\"\n desc: \"Die sog. Session-ID ist ein zufällig ausgewählter Schlüssel, der die Sessiondaten auf dem Server eindeutig identifiziert. Dieser Schlüssel kann z.B. über Cookies oder als Bestandteil der URL an ein Folgescript übergeben werden, damit dieses die Sessiondaten auf dem Server wiederfinden kann.\"',	'',	'',	'',	'',	'',	'0000-00-00 00:00:00',	'',	'0000-00-00 00:00:00');

INSERT INTO `%TABLE_PREFIX%consent_manager_cookiegroup` (`pid`, `id`, `clang_id`, `domain`, `uid`, `prio`, `required`, `name`, `description`, `cookie`, `script`, `createuser`, `createdate`, `updateuser`, `updatedate`) VALUES
(1,	1,	1,	'',	'required',	1,	'|1|',	'Notwendig',	'Technisch notwendige Cookies ermöglichen grundlegende Funktionen und sind für den fehlerfreien Betrieb der Website erforderlich.',	'|consent_manager|,|session|',	'',	'',	'0000-00-00 00:00:00',	'',	'0000-00-00 00:00:00');

INSERT INTO `%TABLE_PREFIX%consent_manager_text` (`pid`, `id`, `clang_id`, `uid`, `text`, `createuser`, `createdate`, `updateuser`, `updatedate`) VALUES
(1,	1,	1,	'headline',	'Datenschutz-Einstellungen',	'',	'0000-00-00 00:00:00',	'',	'0000-00-00 00:00:00'),
(2,	2,	1,	'description',	'Unsere Website verwendet externe Dienste für verschiedene Zwecke, wie z.B. für Statistiken oder die Ausspielung von Multimedia. Durch Auswahl und Bestätigung der Dienste stimmen Sie der Übertragung von Daten an diese Dienste zu. Diese Dienste und auch diese Website setzen ggf. auch Cookies. Die  Auswahl kann jederzeit  geändert und widerrufen werden.',	'',	'0000-00-00 00:00:00',	'',	'0000-00-00 00:00:00'),
(3,	3,	1,	'toggle_details',	'Details anzeigen/ausblenden',	'',	'0000-00-00 00:00:00',	'',	'0000-00-00 00:00:00'),
(4,	4,	1,	'provider',	'Anbieter:',	'',	'0000-00-00 00:00:00',	'',	'0000-00-00 00:00:00'),
(5,	5,	1,	'link_privacy',	'Datenschutzerklärung',	'',	'0000-00-00 00:00:00',	'',	'0000-00-00 00:00:00'),
(6,	6,	1,	'lifetime',	'Laufzeit:',	'',	'0000-00-00 00:00:00',	'',	'0000-00-00 00:00:00'),
(7,	7,	1,	'button_accept',	'Auswahl bestätigen',	'',	'0000-00-00 00:00:00',	'',	'0000-00-00 00:00:00'),
(8,	8,	1,	'button_select_all',	'Alle auswählen',	'',	'0000-00-00 00:00:00',	'',	'0000-00-00 00:00:00'),
(9, 9, 1, 'button_select_none', 'Nur notwendige', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00'),
(10,	10,	1,	'service',	'Service:',	'',	'0000-00-00 00:00:00',	'',	'0000-00-00 00:00:00'),
(11,	11,	1,	'usage',	'Verwendungszweck:',	'',	'0000-00-00 00:00:00',	'',	'0000-00-00 00:00:00'),
(12,	12,	1,	'category',	'Kategorie:',	'',	'0000-00-00 00:00:00',	'',	'0000-00-00 00:00:00'),
(13,	13,	1,	'value',	'Wert:',	'',	'0000-00-00 00:00:00',	'',	'0000-00-00 00:00:00'),
(14,	14,	1,	'legal_notice',	'Impressum',	'',	'0000-00-00 00:00:00',	'',	'0000-00-00 00:00:00'),
(15,	15,	1,	'privacy_policy',	'Datenschutzerklärung',	'',	'0000-00-00 00:00:00',	'',	'0000-00-00 00:00:00');
