CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%iwcc_cookie` (
  `pid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id` int(10) unsigned NOT NULL,
  `clang_id` int(10) unsigned NOT NULL,
  `uid` varchar(255) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `provider` varchar(255) NOT NULL,
  `provider_link_privacy` varchar(255) NOT NULL,
  `cookie_name` varchar(255) NOT NULL,
  `cookie_lifetime` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `createdate` datetime NOT NULL,
  `updatedate` datetime NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%iwcc_cookiegroup` (
  `pid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id` int(10) unsigned NOT NULL,
  `clang_id` int(10) unsigned NOT NULL,
  `domain` varchar(255) NOT NULL,
  `uid` varchar(255) NOT NULL,
  `prio` int(10) unsigned NOT NULL,
  `required` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `cookie` varchar(255) DEFAULT NULL,
  `script` text NOT NULL,
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `createdate` datetime NOT NULL,
  `updatedate` datetime NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%iwcc_domain` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` varchar(255) NOT NULL,
  `privacy_policy` int(10) unsigned NOT NULL,
  `legal_notice` int(10) unsigned NOT NULL,
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `createdate` datetime NOT NULL,
  `updatedate` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%iwcc_text` (
  `pid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id` int(10) unsigned NOT NULL,
  `clang_id` int(10) unsigned NOT NULL,
  `uid` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `createdate` datetime NOT NULL,
  `updatedate` datetime NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `%TABLE_PREFIX%iwcc_cookie` (`pid`, `id`, `clang_id`, `uid`, `service_name`, `provider`, `provider_link_privacy`, `cookie_name`, `cookie_lifetime`, `description`) VALUES
(1, 1, 1, 'iwcc', 'Datenschutz Cookie', 'diese Website', '', 'iwcc', '1 Jahr', 'Speichert Ihre Auswahl bzgl. der Cookies.'),
(2, 2, 1, 'google-analytics', 'Google Analytics', 'Google LLC', 'https://policies.google.com/privacy', '_ga', '2 Jahre', 'Speichert für jeden Besucher der Website eine anonyme ID. Anhand der ID können Seitenaufrufe einem Besucher zugeordnet werden.'),
(3, 3, 1, 'google-analytics', 'Google Analytics', 'Google LLC', 'https://policies.google.com/privacy', '_gat', '1 Tag', 'Verhindert, dass in zu schneller Folge Daten an den Analytics Server übertragen werden.'),
(4, 4, 1, 'google-analytics', 'Google Analytics', 'Google LLC', 'https://policies.google.com/privacy', '_gid', '1 Tag', 'Speichert für jeden Besucher der Website eine anonyme ID. Anhand der ID können Seitenaufrufe einem Besucher zugeordnet werden.');

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