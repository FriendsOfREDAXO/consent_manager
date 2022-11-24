# REDAXO consent_manager - Changelog

## Version 4.0.0beta3 – 24.11.2022

### Breaking Changes

* **Achtung:** Das Template für die Consent-Box und CSS wurde angepasst (Fragment consent_manager_box.php)! Bei eigenen Fragmenten entsprechend anpassen!
* **Template für die Consent-Box angepasst** (fragments/consent_manager_box.php)
  * Buttons statt Links für die Buttons und den Close-Button
  * Tabindex(e) hinzugefügt
  * CSS angepasst (scss/consent_manager_frontend.scss)
  * Browser-Default-Checkboxen ohne SchnickSchnack (nur greyscale, und mit scale vergrössert), dadurch sind die Checkboxen auch per Tastatur erreichbar

### Features

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
* Code-Quality (rexstan) Extensions: REDAXO SuperGlobals, Bleeding-Edge, Strict-Mode, Strict-Mode, phpstan-dba, code complexity, dead code

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
