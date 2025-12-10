# REDAXO consent_manager - Changelog

## Version 4.3.1 - 10.12.2025

> ⚠️ **DRINGEND - Sicherheitsupdate:** Diese Version enthält wichtige Sicherheitsfixes. Ein Update wird dringend empfohlen. Vielen Dank an die **Deutsche Telekom Security GmbH** für die verantwortungsvolle Meldung der Schwachstellen.

### 🔒 Sicherheitsfixes

* **API Input-Validierung**: Alle Eingaben in `rex_api_consent_manager` werden jetzt strikt validiert:
  * Domain-Format wird auf gültige Hostname-Zeichen geprüft
  * ConsentID-Format wird auf das uniqid-Format beschränkt
  * Consent-UIDs werden auf alphanumerische Zeichen, Bindestriche und Unterstriche validiert
  * CacheLogID wird auf gültiges Format geprüft
  * Ungültige Werte werden verworfen, um XSS-Angriffe über manipulierte Cookies zu verhindern
* **Domain-Normalisierung**: Domains werden beim Speichern ins Log in Kleinbuchstaben normalisiert

---

## Version 4.3.0 - 11.04.2024

### Features

* Feld für Skripte eingeführt, die beim **Deselektieren** eines Dienstes geladen werden @bitshiftersgmbh
* Änderungen zur Verbesserung der Barrierefreiheit @skerbis

### Bugfixes

* Consent-Log zeigt falsche Domain an #309 @aeberhard
* Fix warnings in consent_manager_cache.php @tyrant88

## Version 4.2.0 - 12.10.2023

### Features

* Lebensdauer des Einstellungs-Cookies konfigurierbar #305 - Danke @xong

### Bugfixes

* $_COOKIE['consent_manager'] leer nach Consent und Aufruf von externer Seite #307 - Danke @paddle07

## Version 4.1.4 - 20.07.2023

## Updates

* Mindestversion REDAXO 5.12 @ingowinter
* Non-Secure Cookies @tyrant88
* `consent_manager_frontend.js` Cookie strict -> Strict, Update min-Version
* Function `consent_manager_util::consentConfigured()` erweitert um gesetzte Cookies @aeberhard
* `consent_manager_util::hostname()` überarbeitet
* `consent_manager_util::get_domaininfo()` hinzugefügt
* Code-Quality rexfactor/rexstan-Anpassungen
* Text Info-Meldung angepasst wenn keine Domain/Dienste zugeordnet sind

### Bugfixes

* Probleme bei Subdomains behoben @aeberhard
  * `consent_manager_util::hostname()` angepasst. Domain ohne Subdomain wurde durch die Anpassung #297 für locale Hosts nicht korrekt zurückgeliefert
* Sprachnavigation bei nur einer Sprache wurde nicht ausgeblendet

## Version 4.1.3 - 05.06.2023

**Hinweis:** Die Verwendung von REX_VARS ist ab jetzt `deprecated`! In der Version 5.x des Consent-Managers wird nur noch `REX_CONSENT_MANAGER[]` unterstützt, sollte aber nicht mehr verwendet werden. `REX_COOKIEDB[]` wird entfallen. Mehr Infos in der Version 5.x

## Updates

* README - Ergänzung für Skripte die nach Einverständnis geladen werden @skerbis

### Bugfixes

* TLD wurde bei .localhost nicht erkannt. #295 fixed by @skerbis mit #297
* Whoops bei Themes verhindern wenn das project-AddOn nicht existiert @TobiasKrais @aeberhard

## Version 4.1.2 – 16.05.2023

**Hinweis:** Die Verwendung von REX_VARS ist ab jetzt `deprecated`! In der Version 5.x des Consent-Managers wird nur noch `REX_CONSENT_MANAGER[]` unterstützt, sollte aber nicht mehr verwendet werden. `REX_COOKIEDB[]` wird entfallen. Mehr Infos in der Version 5.x

### Bugfixes

* Im Backend wurde bei Subdomains die Meldung angezeigt dass noch kein Consent konfiguriert ist. Das wurde behoben.

## Version 4.1.1 – 10.05.2023

**Hinweis:** Die Verwendung von REX_VARS ist ab jetzt `deprecated`! In der Version 5.x des Consent-Managers wird nur noch `REX_CONSENT_MANAGER[]` unterstützt, sollte aber nicht mehr verwendet werden. `REX_COOKIEDB[]` wird entfallen. Mehr Infos in der Version 5.x

* neue Methode `consent_manager_util::consentConfigured()` - prüft ob Consent konfiguriert ist

### Bugfixes

* Bestehendes Cookie aus vorheriger Version wurde nicht gelöscht und daher kam es zu Problemen beim speichern des neuen Cookies.
  Das Consent-Popup wurde immer wieder angezeigt.
* Fix #294 Undefined array key "majorVersion" @tbaddade
* Consent nicht einblenden wenn kein Consent konfiguriert ist (Mulditdomain)
  Danke an alle Melder/Tester und besonders Stefan @dpf-dd + Peter @bitshiftersgmbh + Thomas @tbaddade!

## Version 4.1.0 – 05.05.2023

**Hinweis:** Die Verwendung von REX_VARS ist ab jetzt `deprecated`! In der Version 5.x des Consent-Managers wird nur noch `REX_CONSENT_MANAGER[]` unterstützt, sollte aber nicht mehr verwendet werden. `REX_COOKIEDB[]` wird entfallen. Mehr Infos in der Version 5.x

### Features

* neue Methode `consent_manager_util::hostname()` - liefert Hostname ohne Subdomain und Port
* `consent_manager_frontend.js` überarbeitet @aeberhard
  * einheitliche Verarbeitung der Cookies durch Cookie-API mit `Cookies.withAttributes`
  * Cookie-Parameter `sameSite: 'strict'` und  `secure: true`
  * Code-Stabilität und Error-Handling verbessert
* Update js-cookie Version 3.0.5
* PHP Code-Quality
* Anpassung der Themes `glass` durch @skerbis, Blocksatz entfernt.
* Anpassung der Themes `olien` durch @aeberhard, Blocksatz entfernt.

### Bugfixes

* Es gab unter Umständen Fehler beim setzen der Cookies, das sollte jetzt behoben sein

## Version 4.0.3 – 13.03.2023

### Bugfixes

* Fix #289 - Bei neu angelegter Sprache werden die Domains aus der Gruppe nicht übernommen @clausbde
  Die Domains wurden übernommen aber nicht als ausgewählt angezeigt. Beim Speichern in der zusätzlichen Sprache wurden die Domains gelöscht.
* Wenn keine Dienste ausgewählt waren, wurden in den weiteren Sprachen keine Dienste angezeigt
* Label Domains+Dienste wurde in weiteren Sprachen nicht angezeigt

## Version 4.0.2 – 20.02.2023

* Wording: "Alles ablehnen" in "Nur notwendige" geändert

### Bugfixes

* update 4.0 -> 4.0.1 class not found #287, @skerbis

## Version 4.0.1 – 16.02.2023

### Bugfixes

* Beim Button **Alles ablehnen** die notwendigen Dienste setzen statt "leer", Ausgabe auch im Consent-Log
* Cookies mit www löschen, behebt evtl. #284 @alxndr-w
* Fragment `consent_manager_box.php`: Link **Details anzeigen** um `href=#` erweitert, Click-Ereignisse mit return false in `consent_manager_frontend.js`
* Fix #286 - Link Datenschutzerklärung nicht lokalisiert @clausbde
* removed .php-cs-fixer.dist.php

## Version 4.0.0 – 20.01.2023

### Breaking Changes

* **Achtung:** Das Template für die Consent-Box und CSS wurde angepasst (Fragment consent_manager_box.php)! Bei eigenen Fragmenten entsprechend anpassen!
* **Template für die Consent-Box angepasst** (fragments/consent_manager_box.php)
  * Buttons statt Links für die Buttons und den Close-Button
  * Neuer Button "Alles ablehnen" (@thorol)
  * Tabindex(e) hinzugefügt, Consent-Box ist jetzt auch per Tastatur bedienbar
  * SCSS angepasst, Variablen hinzugefügt und Style vereinfacht (scss/consent_manager_frontend.scss)
  * Browser-Default-Checkboxen ohne SchnickSchnack (nur greyscale/hue-rotate, und mit scale vergrössert), dadurch sind die Checkboxen auch per Tastatur erreichbar

### Features

* Themes sind jetzt möglich, SCSS mit Variablen, mehrere Standard-Themes sind beim Addon dabei, Theme-Vorschau im Backend, @aeberhard
  * Standard Theme Light, Light Bottom-Bar, Light Bottom-Right
  * Standard Theme Dark, Dark Bottom-Bar, Dark Bottom-Right
  * Olien's Dark Theme, Olien's Light Theme von @olien
  * Skerbis' Dark glass, Skerbis' Light glass von @skerbis
  * XOrange Themes von @aeberhard
* Eigene Themes können im project-Addon im Ordner **consent_manager_themes** gespeichert werden
* README.md grundlegend überarbeitet und verbessert, Danke @skerbis
* Hinweistext überarbeitet, Cookie* an vielen Stellen durch Dienste ersetzt, rechtliche Hinweise @skerbis
* CHANGELOG.md hinzugefügt, Anzeige im Backend (package.yml)
* Host-Validation angepasst (consent_manager_rex_form::validateHostname)
* JavaScript-Funktion **consent_manager_showBox** zum anzeigen der Consent-Box (assets/consent_manager_frontend.js) #230
* JavaScript-Funktion **consent_manager_hasconsent** zur Consent-Abfrage hinzugefügt
* Toggle Details anzeigen auch per Tastatur mit Enter (assets/consent_manager_frontend.js)
* Domain bei Setcookie hinzugefügt, Subdomains sollten damit auch möglich sein (assets/consent_manager_frontend.js) #110
* Consent-Log
  * IP-Adresse im Log ausgeben
  * Suchfeld hinzugefügt (Suche nach Datum, Host, IP, Cachelog-Id)
  * consent_manager_backend.js im Backend hinzugefügt
* Standard-Klassen für Listen hinzugefügt (table-striped, table-hover)
* Schlüsselfelder in den Listen verlinkt (editieren)
* Update js.cookie-3.0.1.min.js
* Reload der Seite kann erzwungen werden **REX_CONSENT_MANAGER[forceReload=1]**
* In der Übersicht der Gruppen eine Warning ausgeben wenn noch keine Domain zugeordnet wurde #257
* Validierungen in der Gruppenverwaltung verbessert
* Code-Quality (rexstan) Level 9, Extensions: REDAXO SuperGlobals, Bleeding-Edge, Strict-Mode, Deprecation Warnings, PHPUnit, phpstan-dba, report-mixed, dead code
* added .php-cs-fixer.dist.php, Code überarbeitet mit Coding Standards

### Bugfixes

* YAML validieren, bevor es gespeichert / übernommen wird #248
* fix target file name @alxndr-w PR #258

## Version 3.0.8 – 16.08.2022

### Changes

* fix warning by @tyrant88 in #240
* fix: Speichern einer Gruppe auch wenn noch keine domain existiert by @tyrant88 in #241

### Bugfixes

* PHP 8.1 - Bugfix by @tbaddade in #242

## Version 3.0.7 – 12.05.2022

### Changes

* Update package.yml by @tyrant88 in #238

### Bugfixes

* keine

## Version 3.0.6 – 12.05.2022

### Changes

* keine

### Bugfixes

* Fehler beim Installieren der Beispieldateien behoben @tyrant88

## Version 3.0.5 – 02.05.2022

### Changes

* Auch Unterstrich in der cookie-uid erlauben @tyrant88

### Bugfixes

* keine

## Version 3.0.4 – 09.01.2022

### Changes

* Eindeutige Kennung der Script-Container
  **Achtung:** Eigenes consent_manager_box.php-Fragment muss entsprechend angepasst werden! Siehe #210
* Textareas in Cookie-Verwaltung von text/javascript auf text/html umgestellt (wg. Codemirror) @alxndr-w
* Session entfernt
* Warnhinweis im Log und der Console um host erweitert
* Anzeigefunktion Cookie-Log-Tabelle
* Ausgabe JavaScript nicht mehr über OUTPUT_FILTER sondern über EP FE_OUTPUT

### Bugfixes

* Sprache bei REX_COOKIEDB[] wurde nicht korrekt berücksichtigt

## Version 3.0.3 – 29.11.2021

Danke an @TobiasKrais @skerbis @ynamite @marcohanke

### Changes

* Änderung der Script-Urls (ohne index.php)

### Bugfixes

* Session-Handling gefixed.

## Version 3.0.2 – 16.11.2021

### Changes

* Update der Standard-Styles für die Consent-Box. Buttons haben jetzt die gleiche Farbe.

### Bugfixes

* Bugfix Session-Cookie

## Version 3.0.1 – 03.11.2021

### Changes

* README angepasst
* Consent-Parameter nicht inline ausgeben, verlagert in JS-Datei

### Bugfixes

* Session nur starten wenn der Consent-Manager im Template auch eingebunden wird #188

## Version 3.0.0 – 30.06.2021

### Changes

* CSS und JavaScript Optimierung

### Bugfixes

* keine
