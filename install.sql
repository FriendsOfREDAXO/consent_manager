INSERT INTO `%TABLE_PREFIX%iwcc_cookie` (`pid`, `id`, `clang_id`, `uid`, `service_name`, `provider`, `provider_link_privacy`, `definition`) VALUES
(1, 1, 1, 'iwcc', 'Datenschutz Cookie', 'diese Website', '', '-\n name: iwcc\n time: 1 Jahr\n desc: Speichert Ihre Auswahl bzgl. der Cookies.'),
(2, 2, 1, 'google-analytics', 'Google Analytics', '', '', '-\n name: _ga\n time: 2 Jahre\n desc: Speichert für jeden Besucher der Website eine anonyme ID. Anhand der ID können Seitenaufrufe einem Besucher zugeordnet werden.\n-\n name: _gat\n time: 1 Tag\n desc: Verhindert, dass in zu schneller Folge Daten an den Analytics Server übertragen werden.\n-\n name: _gid\n time: 1 Tag\n desc: Speichert für jeden Besucher der Website eine anonyme ID. Anhand der ID können Seitenaufrufe einem Besucher zugeordnet werden.'),
(5, 3, 1, 'matomo', 'Matomo', '', '', '-\n name: _pk_id\n time: 13 Monate\n desc: Speichert für jeden Besucher der Website eine anonyme ID. Anhand der ID können Seitenaufrufe einem Besucher zugeordnet werden.\n-\n name: _pk_ref\n time: 6 Monate\n desc: Zur Speicherung des Verweises der ursprünglich zum Besuch der Website verwendet wurde.\n-\n name: _pk_ses\n time: 30 Minuten\n desc: Hilfscookie für temporäre Daten\n-\n name: _pk_cvar\n time: 30 Minuten\n desc: Hilfscookie für temporäre Daten\n-\n name: _pk_hsr\n time: 30 Minuten\n desc: Hilfscookie für temporäre Daten');

INSERT INTO `%TABLE_PREFIX%iwcc_cookiegroup` (`pid`, `id`, `clang_id`, `domain`, `uid`, `prio`, `required`, `name`, `description`, `cookie`, `script`) VALUES
(1, 1, 1, '', 'required', 1, '|1|', 'Notwendig', 'Notwendige Cookies ermöglichen grundlegende Funktionen und sind für die einwandfreie Funktion der Website erforderlich.', NULL, ''),
(2, 2, 1, '', 'statistics', 2, NULL, 'Statistik', 'Statistik Cookies erfassen Informationen anonym. Diese Informationen helfen uns zu verstehen, wie unsere Besucher unsere Website nutzen.', NULL, '');

INSERT INTO `%TABLE_PREFIX%iwcc_text` (`pid`, `id`, `clang_id`, `uid`, `text`) VALUES
(1, 1, 1, 'headline', 'Cookie-Verwaltung'),
(2, 2, 1, 'description', 'Wir verwenden Cookies, um Ihnen ein optimales Webseiten-Erlebnis zu bieten. Dazu zählen Cookies, die für den Betrieb der Seite und für die Steuerung unserer kommerziellen Unternehmensziele notwendig sind, sowie solche, die lediglich zu anonymen Statistikzwecken, für Komforteinstellungen oder zur Anzeige personalisierter Inhalte genutzt werden. Sie können selbst entscheiden, welche Kategorien Sie zulassen möchten. Bitte beachten Sie, dass auf Basis Ihrer Einstellungen womöglich nicht mehr alle Funktionalitäten der Seite zur Verfügung stehen.'),
(3, 3, 1, 'toggle_details', 'Details anzeigen/ausblenden'),
(4, 4, 1, 'provider', 'Anbieter:'),
(5, 5, 1, 'link_privacy', 'Datenschutzerklärung'),
(6, 6, 1, 'lifetime', 'Laufzeit:');
