INSERT IGNORE INTO `%TABLE_PREFIX%iwcc_cookie` (`pid`, `id`, `clang_id`, `uid`, `service_name`, `provider`, `provider_link_privacy`, `definition`) VALUES
(1, 1, 1, 'iwcc', 'Datenschutz Cookie', 'diese Website', '', '-\r\n name: iwcc\r\n time: 1 Jahr\r\n desc: Speichert Ihre Auswahl bzgl. der Cookies.'),
(2, 2, 1, 'google-analytics', 'Google Analytics', 'Google LLC', 'https://policies.google.com/privacy', '-\r\n name: _ga\r\n time: 2 Jahre\r\n desc: Speichert für jeden Besucher der Website eine anonyme ID. Anhand der ID können Seitenaufrufe einem Besucher zugeordnet werden.\r\n-\r\n name: _gat\r\n time: 1 Tag\r\n desc: Verhindert, dass in zu schneller Folge Daten an den Analytics Server übertragen werden.\r\n-\r\n name: _gid\r\n time: 1 Tag\r\n desc: Speichert für jeden Besucher der Website eine anonyme ID. Anhand der ID können Seitenaufrufe einem Besucher zugeordnet werden.');

INSERT IGNORE INTO `%TABLE_PREFIX%iwcc_cookiegroup` (`pid`, `id`, `clang_id`, `domain`, `uid`, `prio`, `required`, `name`, `description`, `cookie`, `script`) VALUES
(1, 1, 1, '11', 'required', 1, '|1|', 'Notwendig', 'Notwendige Cookies ermöglichen grundlegende Funktionen und sind für die einwandfreie Funktion der Website erforderlich.', NULL, ''),
(2, 2, 1, '11', 'statistics', 2, NULL, 'Statistik', 'Statistik Cookies erfassen Informationen anonym. Diese Informationen helfen uns zu verstehen, wie unsere Besucher unsere Website nutzen.', '|google-analytics|', '');

INSERT IGNORE INTO `%TABLE_PREFIX%iwcc_text` (`pid`, `id`, `clang_id`, `uid`, `text`) VALUES
(1, 1, 1, 'headline', 'Cookie-Verwaltung'),
(2, 2, 1, 'description', 'Wir verwenden Cookies, um Ihnen ein optimales Webseiten-Erlebnis zu bieten. Dazu zählen Cookies, die für den Betrieb der Seite und für die Steuerung unserer kommerziellen Unternehmensziele notwendig sind, sowie solche, die lediglich zu anonymen Statistikzwecken, für Komforteinstellungen oder zur Anzeige personalisierter Inhalte genutzt werden. Sie können selbst entscheiden, welche Kategorien Sie zulassen möchten. Bitte beachten Sie, dass auf Basis Ihrer Einstellungen womöglich nicht mehr alle Funktionalitäten der Seite zur Verfügung stehen.'),
(3, 3, 1, 'toggle_details', 'Details anzeigen/ausblenden'),
(4, 4, 1, 'provider', 'Anbieter:'),
(5, 5, 1, 'link_privacy', 'Datenschutzerklärung'),
(6, 6, 1, 'lifetime', 'Laufzeit:');