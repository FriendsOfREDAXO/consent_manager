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

INSERT INTO `%TABLE_PREFIX%template` (`id`, `name`, `content`, `active`, `createuser`, `updateuser`, `createdate`, `updatedate`, `attributes`, `revision`) VALUES
(null, '_iwcc', '<?php\n$iwcc = new iwcc_frontend(); // force cache write: new iwcc_frontend(1)\n$iwcc->setDomain($_SERVER[\'HTTP_HOST\']);\n?>\n<?php if ($iwcc->cookiegroups): ?>\n <link href=\"/assets/addons/iwcc/fontello/css/fontello.css\" rel=\"stylesheet\" type=\"text/css\">\n <link href=\"/assets/addons/iwcc/pretty-checkbox.min.css\" rel=\"stylesheet\" type=\"text/css\">\n <link href=\"/assets/addons/iwcc/iwcc_frontend.css\" rel=\"stylesheet\" type=\"text/css\">\n <script src=\"/assets/addons/iwcc/js.cookie-2.2.1.min.js\"></script>\n <script src=\"/assets/addons/iwcc/iwcc_frontend.js\"></script>\n <script id=\"iwcc-template\" type=\"text/template\">\n <div class=\"iwcc-background iwcc-hidden\" id=\"iwcc-background\" data-domain-name=\"<?= $iwcc->domainName ?>\">\n <div class=\"iwcc-wrapper\" id=\"iwcc-wrapper\">\n <div class=\"iwcc-wrapper-inner\">\n <div class=\"iwcc-summary\" id=\"iwcc-summary\">\n <p class=\"iwcc-headline\"><?= $iwcc->texts[\'headline\'] ?></p>\n <p class=\"iwcc-text\"><?= nl2br($iwcc->texts[\'description\']) ?></p>\n <div class=\"iwcc-cookiegroups\">\n <?php\n foreach ($iwcc->cookiegroups as $cookiegroup)\n {\n if ($cookiegroup[\'required\'])\n {\n echo \'<div class=\"iwcc-cookiegroup-checkbox pretty p-icon p-curve p-locked\">\';\n echo \'<input type=\"checkbox\" data-action=\"toggle-cookie\" data-uid=\"\' . $cookiegroup[\'uid\'] . \'\" data-cookie-uids=\\\'\' . json_encode($cookiegroup[\'cookie_uids\']) . \'\\\' checked>\';\n echo \'<div class=\"state\">\';\n echo \'<i class=\"icon icon-ok-1\"></i>\';\n echo \'<label>\' . $cookiegroup[\'name\'] . \'</label>\';\n echo \'</div>\';\n if ($cookiegroup[\'script\'])\n {\n echo \'<div class=\"iwcc-script\" data-script=\"\' . $cookiegroup[\'script\'] . \'\"></div>\';\n }\n echo \'</div>\';\n }\n else\n {\n echo \'<div class=\"iwcc-cookiegroup-checkbox pretty p-icon p-curve\">\';\n echo \'<input type=\"checkbox\" data-uid=\"\' . $cookiegroup[\'uid\'] . \'\" data-cookie-uids=\\\'\' . json_encode($cookiegroup[\'cookie_uids\']) . \'\\\'>\';\n echo \'<div class=\"state\">\';\n echo \'<i class=\"icon icon-ok-1\"></i>\';\n echo \'<label>\' . $cookiegroup[\'name\'] . \'</label>\';\n echo \'</div>\';\n if ($cookiegroup[\'script\'])\n {\n echo \'<div class=\"iwcc-script\" data-script=\"\' . $cookiegroup[\'script\'] . \'\"></div>\';\n }\n echo \'</div>\';\n }\n }\n ?>\n </div>\n <div class=\"iwcc-show-details\">\n <a id=\"iwcc-toggle-details\" class=\"icon-info-circled\"><?= $iwcc->texts[\'toggle_details\'] ?></a>\n </div>\n </div>\n <div class=\"iwcc-detail iwcc-hidden\" id=\"iwcc-detail\">\n <?php\n foreach ($iwcc->cookiegroups as $cookiegroup)\n {\n echo \'<div class=\"iwcc-cookiegroup-title iwcc-headline\">\';\n echo $cookiegroup[\'name\'] . \' <span>(\' . count($cookiegroup[\'cookie\']) . \')</span>\';\n echo \'</div>\';\n echo \'<div class=\"iwcc-cookiegroup-description\">\';\n echo $cookiegroup[\'description\'];\n echo \'</div>\';\n echo \'<div class=\"iwcc-cookiegroup\">\';\n foreach ($cookiegroup[\'cookie\'] as $cookie)\n {\n echo \'<div class=\"iwcc-cookie\">\';\n echo \'<span class=\"iwcc-cookie-name\"><strong>\' . $cookie[\'cookie_name\'] . \'</strong> (\' . $cookie[\'service_name\'] . \')</span>\';\n echo \'<span class=\"iwcc-cookie-description\">\' . nl2br($cookie[\'description\']) . \'</span>\';\n echo \'<span class=\"iwcc-cookie-description\">\' . $iwcc->texts[\'lifetime\'] . \' \' . $cookie[\'cookie_lifetime\'] . \'</span>\';\n echo \'<span class=\"iwcc-cookie-provider\">\' . $iwcc->texts[\'provider\'] . \' \' . $cookie[\'provider\'] . \'</span>\';\n echo \'<span class=\"iwcc-cookie-link-privacy-policy\"><a href=\"\' . $cookie[\'provider_link_privacy\'] . \'\">\' . $iwcc->texts[\'link_privacy\'] . \'</a></span>\';\n echo \'</div>\';\n }\n echo \'</div>\';\n }\n ?>\n </div>\n <div class=\"iwcc-buttons-sitelinks\">\n <div class=\"iwcc-buttons\">\n <a class=\"iwcc-save-selection iwcc-close\">Auswahl bestätigen</a>\n <a class=\"iwcc-accept-all iwcc-close\">Alle auswählen</a>\n </div>\n <div class=\"iwcc-sitelinks\">\n <?php\n foreach ($iwcc->links as $v)\n {\n echo \'<a href=\"\' . rex_getUrl($v) . \'\">\' . rex_article::get($v)->getName() . \'</a>\';\n }\n ?>\n </div>\n </div>\n <a class=\"icon-cancel-circled iwcc-close iwcc-close-box\"></a>\n </div>\n </div>\n </div>\n </script>\n<?php endif; ?>', 0, '', 'winteringo@gmail.com', '0000-00-00 00:00:00', '2019-11-10 22:26:30', '{\"ctype\":[],\"modules\":{\"1\":{\"all\":\"1\"}},\"categories\":{\"all\":\"1\"}}', 0);

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