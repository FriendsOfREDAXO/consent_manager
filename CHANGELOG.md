# REDAXO consent_manager - Changelog

## Version x.y.z - xx.yy.2025

* **Namespace FriendsOfRedaxo\ConsentManager**
  * Datei und Klassennamne von `rex_api_consent_manager_inline_log` geändert in `...\Api\InlineLog`
    Externer API-Name `consent_manager_inline_log` beibehalten
  * Datei und Klassenname von `rex_api_consent_manager` geändert in `...\Api\ConsentManager`
    Externer API-Name `consent_manager` beibehalten
  * Datei und Klassenname von `consent_manager_clang` geändert in `...\CLang`
  * Datei und Klassenname von `consent_manager_inline` geändert in `...\InlineConsent`
  * Datei und Klassenname von `consent_manager_config` geändert in `...\Config`
  * Datei und Klassenname von `consent_manager_cache` geändert in `...\Cache`
  * Datei und Klassenname von `consent_manager_frontend` geändert in `...\Frontend`
  * Datei und Klassenname von `consent_manager_rex_form` eändert in `...\RexFormSupport`
  * Datei und Klassenname von `consent_manager_rex_list` geändert in `...\RexListSupport`

## Version 4.5.0 - 14.10.2025

### 🚀 Neue Features

* **Inline Consent System**: Vollständig neues System für bedarfsgerechten Consent einzelner Medien/Services
  * **Domain-spezifischer Inline-Only-Modus**: Pro Domain konfigurierbar - globaler Consent-Banner wird ausgeblendet
  * **Datenschutz-konforme Thumbnails**: YouTube/Vimeo-Thumbnails werden lokal gecacht statt externe Requests
  * **Fragment-basiertes Design**: Vollständig anpassbares Markup über `consent_inline_placeholder.php`
  * **Automatische Platzhalter-Ersetzung**: Nach globaler Consent-Erteilung werden alle Inline-Elemente ohne Reload ersetzt
  * **Multi-Event-Erkennung**: Robustes Event-System mit Cookie-Monitoring und MutationObserver
  * **YouTube & Vimeo Integration**: Spezielle Handler mit automatischer Video-ID-Erkennung und Thumbnail-Caching
  * **Thumbnail-Cache-System**: Lokale Speicherung externer Bilder mit automatischer Bereinigung via Cronjob
  * **Service-spezifische Handler**: YouTube, Vimeo, Google Maps mit individueller Konfiguration
  * **Erweiterte Debugging-Tools**: Umfassende Console-Logs und Debug-Ausgaben für Entwicklung

* **🎨 A11y Theme Editor**: Visueller Editor für barrierefreie Themes
  * **Drag & Drop Interface**: Intuitive Farbauswahl mit Color-Picker und Live-Vorschau
  * **Basis-Theme-Varianten**: Normal und Compact als Ausgangspunkt für eigene Themes
  * **Custom Theme Export**: Automatische Speicherung in `project/consent_manager_themes/`
  * **SCSS-Generierung**: Vollautomatische SCSS-Erstellung mit Accessibility-Features
  * **Live-Kompilierung**: Themes werden automatisch kompiliert und in Theme-Auswahl verfügbar

* **♿ Barrierefreiheit (Issue #326)**: Umfassende Accessibility-Optimierungen
  * **5 neue A11y-Themes**: Accessibility, Blue, Green, Compact, Compact Blue
   * **WCAG 2.1 AA Konformität**: 4.5:1 Kontraste für Text, 3:1 für UI-Komponenten
   * **Erweiterte Tastatursteuerung**: ESC (Dialog schließen), Space (Details toggle), Tab-Navigation
   * **Fokus-Management**: 3px blaue Fokus-Indikatoren, automatischer Fokus auf erste Checkbox
   * **Touch-Targets**: Mindestens 44x44px für alle interaktiven Elemente
   * **Screen Reader Support**: Korrekte ARIA-Attribute (`role="dialog"`, `aria-modal="true"`)
   * **Reduced Motion**: Respektiert `prefers-reduced-motion` und `prefers-contrast: more`
  * **High Contrast Mode**: Spezielle Styles für hohen Kontrast-Modus

### 🔧 Verbesserungen

* **Modernisierte JavaScript-Architektur**: Event-Delegation statt onclick-Attribute
* **Robuste Cookie-Erkennung**: Unterstützung verschiedener Cookie-Formate und URL-Dekodierung
* **Erweiterte Service-Erkennung**: String-basierte Fallback-Erkennung bekannter Services
* **Verbesserte Fehlerbehandlung**: Detaillierte Debug-Ausgaben und Fallback-Mechanismen

### 🐛 Bugfixes

* **Inline-Consent Button-Attribute**: Behebt GitHub Copilot Review-Feedback aus PR #386
  * "Alle erlauben" Button verwendet jetzt `data-service` statt `data-consent-id` für service-weite Consent-Logik
  * Einstellungen-Button verwendet `data-service` statt hardcodiertes `onclick` für bessere Event-Delegation
  * Icon für "Alle erlauben" Button zurück zu `fa-check-circle` für bessere visuelle Konsistenz

### 🛠️ Technische Änderungen

* **Neue Datenbankstruktur**: `inline_only_mode` Spalte in `consent_manager_domain` Tabelle
* **Fragment-System**: Vollständig anpassbare Templates für Inline-Consent-UI
* **Thumbnail-Cache-API**: `consent_manager_thumbnail_cache` Klasse mit SVG-Fallbacks
* **Cronjob-Integration**: Automatische Cache-Bereinigung für Thumbnail-Dateien
* **Theme-Compiler-System**: Automatische SCSS-Kompilierung und Asset-Generierung
* **A11y-Framework**: Basis-Framework für barrierefreie Theme-Entwicklung
* **Project-Addon-Integration**: Eigene Themes in `project/consent_manager_themes/` Ordner

### 📁 Neue Dateien

* `pages/theme_editor.php`: Visueller Theme-Editor für A11y-Themes
* `scss/consent_manager_frontend_a11y*.scss`: 5 neue Accessibility-Themes
* `assets/consent_manager_frontend_a11y.css`: Kompiliertes A11y-CSS
* `lib/consent_manager_thumbnail_cache.php`: Thumbnail-Cache-Management
* `fragments/consent_inline_placeholder.php`: Inline-Consent-Template
* `inline.md`: Vollständige Dokumentation für Inline-Consent-System

## Version 4.4.0 - 24.08.2025

### 🚀 Neue Features

* **Google Consent Mode v2 Integration**: Vollständige Unterstützung für Google Consent Mode v2 mit flexiblem 3-Modi-System
  * **Drei Modi**: Deaktiviert ❌ / Automatisch 🔄 (Auto-Mapping) / Manuell ⚙️
  * **Auto-Mapping**: Services werden automatisch erkannt (google-analytics, facebook-pixel, etc.) und entsprechende gtag('consent', 'update') Aufrufe generiert
  * **Manueller Modus**: Nur gtag('consent', 'default') wird gesetzt - gtag('consent', 'update') muss in Service-Scripts selbst implementiert werden
  * **GDPR-konforme Default-Einstellungen**: Alle Consent-Modi standardmäßig auf 'denied' gesetzt (auch functionality_storage und security_storage)
  * **Domain-spezifische Konfiguration**: Separater Modus pro Domain mit UI-Select-Field
  * **Service-Detection**: Automatische Erkennung von Services über UID-Mappings (12 vorkonfigurierte Services)
  * **Debug-Konsole**: Live-Anzeige des aktiven Google Consent Mode Status mit Modus-Icon
* **Revolutionärer Quickstart-Assistent**: Komplett neuer 7-stufiger Setup-Wizard mit modernem Timeline-Design
  * **Timeline-UI**: Ersetzt "grässliche" Panel-Darstellung durch elegante Timeline-Optik mit Schritt-für-Schritt-Navigation (@lus)
  * **Theme-Kompatibilität**: Vollständige Unterstützung für REDAXO Light- und Dark-Themes mit CSS Custom Properties
  * **Copy-to-Clipboard**: Integrierte clipboard-copy Web Components für Template-Code und Privacy-Links
  * **Externe CSS-Architektur**: `consent_quickstart.css` mit bedingtem Laden nur wo benötigt
  * **Footer-Integration**: Hinweise auf dauerhafte Cookie-Einstellungen-Links im Footer
* **JSON-basiertes Setup-System**: Komplett überarbeitetes Import-/Export-System für Konfigurationsdaten
  * **4.3.0-Kompatibilität**: Alle 23 originalen Text-UIDs aus CSV-Export übernommen
  * **Setup-Varianten**: "Minimal" (essentieller Service) und "Standard" (25 vorkonfigurierte Services)
  * **GDPR-konforme Beschreibungen**: Erweiterte Texte mit Hinweisen auf Widerrufsrecht und externe Dienste
  * **Export-Funktionalität**: Backup bestehender Konfigurationen als JSON
* **Umfassende Debug-Konsole**: Entwickler-Tools zur Überwachung des Consent-Status (nur für eingeloggte Backend-User)
  * **🎯 Google Consent Mode v2 Status**: Live-Anzeige des aktiven Modus (Deaktiviert ❌ / Automatisch 🔄 / Manuell ⚙️)
  * **Google Consent Mode Monitoring**: Live-Anzeige aller Consent-Flags (analytics_storage, ad_storage, etc.)
  * **Service-Status**: Detaillierte Übersicht über erkannte Services und deren Zuordnung
  * **Cookie-Analyse**: Strukturierte Darstellung aller Cookies mit JSON-Parsing
  * **localStorage-Monitoring**: Einblick in gespeicherte Consent-Daten
  * **Echtzeit-Updates**: Status ändert sich live bei Consent-Änderungen
  * **Domain-basierte Aktivierung**: Debug-Script wird über OUTPUT_FILTER direkt in HTML eingefügt (ersetzt URL-Parameter-System)
  * **Backend-User-Berechtigung**: Nur eingeloggte Backend-Benutzer sehen Debug-Panel im Frontend
  * **Menü-Indikator**: <i class="fa fa-bug"></i> Symbol im Backend-Menü bei aktivem Debug-Modus
  * **Visuelle Statusanzeige**: Bug-Icons in Domain-Liste für Debug-Status
* **Cookie Definition Builder**: Intuitive Benutzeroberfläche für Cookie-Verwaltung
  * **Tabellen-Interface**: Drag & Drop mit "Cookie hinzufügen/entfernen" Buttons
  * **YAML-Generator**: Automatische YAML-Generierung im Hintergrund
  * **Syntax-Fehler-Elimination**: Kein manuelles YAML mehr erforderlich

### 🎨 UI/UX Verbesserungen

* **Timeline-Design**: Moderne 7-Schritte-Darstellung mit visuellen Verbindungen und Fortschrittsanzeige
* **REDAXO-Theme-Integration**: 
  * `.rex-theme-dark` und `.rex-theme-light` CSS-Klassen
  * CSS Custom Properties für dynamische Theme-Anpassung
  * `prefers-color-scheme` Media Query Support
* **Copy-Funktionalität**: 
  * Ein-Klick-Kopieren für Template-Code und Privacy-Links
  * Visuelle Success-Tooltips
  * Clipboard-Copy Web Component Integration
* **Responsive Design**: Mobile-optimierte Timeline-Darstellung mit Breakpoints
* **Accessibility**: Verbesserte Screenreader-Unterstützung und Tastaturnavigation

### 🌍 Internationalisierung

* **Vollständige i18n-Implementation**: 
  * **75+ neue Übersetzungsstrings** für alle UI-Komponenten
  * **Quickstart-Modal**: Alle 7 Schritte komplett übersetzt
  * **Config-Seite**: Alle Beschreibungen und Labels
  * **Google Consent Mode**: Labels und Hilfstexte
  * **Service-Beschreibungen**: Standardisierte Übersetzungen
  * **Parameter-Platzhalter**: Dynamische Inhalte mit Parametern
  * **Formelle Ansprache**: Konsistente "Sie"-Form

### 🔧 Technische Verbesserungen

* **Domain-Tabelle erweitert**: `google_consent_mode_enabled` als varchar(20) für drei Modi ('disabled'/'auto'/'manual')
* **Google Consent Mode JavaScript-Integration**: 
  * Konfiguration wird immer exportiert via `window.consentManagerGoogleConsentMode.getDomainConfig()`
  * Debug-Konsole kann Domain-Konfiguration direkt aus PHP laden
  * Korrekte WHERE-Bedingungen: `uid` statt `domain` für Datenbankzugriffe
* **Externe CSS-Dateien**: `consent_quickstart.css` mit bedingtem Laden
* **Erweiterte Nutzerrechte**: 
  * `consent_manager[editor]` für Redakteure mit Vollzugriff
  * `consent_manager[texteditonly]` für eingeschränkten Textzugriff
* **JavaScript-Optimierung**: 
  * Externe Dateien statt Inline-Code
  * Minifizierte Versionen für Performance
  * Auto-Mapping-Logik in separaten Modulen
* **Service-Detection**: Verbesserte UID-basierte Erkennung für Auto-Mapping (12 vorkonfigurierte Service-Mappings)
* **Update-Migration**: Automatische Migration bestehender boolean zu varchar Werte
* **Fragment-Integration**: Google Consent Mode Script wird nur geladen wenn `!== 'disabled'`

### 🐛 Bugfixes

* **Google Consent Mode Datenbankzugriffe**: SQL-Abfragen verwenden jetzt korrekt `WHERE uid =` statt `WHERE domain =`
* **Fragment-Aktivierungsprüfung**: Google Consent Mode wird nur geladen wenn `!== 'disabled'` (nicht mehr `== '1'`)
* **Google Consent Mode Defaults**: Korrigierte JavaScript-Defaults - alle Consent-Modi standardmäßig 'denied' (GDPR-konform)
  * `functionality_storage` und `security_storage` ebenfalls auf 'denied' gesetzt (vorher automatisch 'granted')
* **Debug-Konsole Status-Anzeige**: Zeigt nun korrekt den aktiven Google Consent Mode Modus aus Domain-Konfiguration
* **Domain-Konfiguration Export**: JavaScript-Konfiguration wird immer verfügbar gemacht, auch für deaktivierte Modi  
* **Debug-Modal**: Zeigt nun korrekte Consent-Status auch vor erster Zustimmung
* **Setup-JSON-Kompatibilität**: Alle Text-UIDs entsprechen jetzt exakt der 4.3.0 CSV-Export-Struktur
* **CSS-Loading**: Externes CSS wird nur geladen wenn Quickstart-Modal verwendet wird
* **Theme-Switching**: Dynamische Theme-Updates ohne Seiten-Reload
* **Cookie-Parsing**: Verbesserte JSON-Dekodierung in Debug-Konsole
* **Z-Index-Konflikte**: Timeline-Modal über anderen Elementen positioniert

### ⚠️ Breaking Changes

* **Domain-Tabelle**: `google_consent_mode_enabled` geändert von `tinyint(1)` zu `varchar(20)`
  * Migration: `0` → `'disabled'`, `1` → `'auto'`
  * Neue Option: `'manual'` für manuelles Google Consent Mode
* **JavaScript-APIs**: Google Consent Mode Defaults geändert - alle auf 'denied'
  * `functionality_storage` und `security_storage` nicht mehr automatisch 'granted'
  * Services müssen explizit zugestimmt werden

### 📁 Neue Dateien

* `assets/consent_quickstart.css`: Externe Timeline-Styles mit Theme-Support
* `assets/google_consent_mode_v2.js`: Überarbeitete GMv2-Implementation mit korrekten Defaults
* `setup/minimal_setup.json`: Minimale Konfiguration mit 4.3.0-kompatiblen Text-UIDs
* `setup/default_setup.json`: Standard-Konfiguration mit 25 vorkonfigurierten Services

### 🗑️ Entfernte Features

* Legacy SQL-basierte Setup-Beispiele entfernt zugunsten JSON-System
* Inline-CSS aus Quickstart-Modal in externe Datei ausgelagert

### 📄 Dokumentation

* **README erweitert**: Footer-Integration-Hinweise für dauerhafte Cookie-Einstellungen-Links
* **Setup-Beispiele**: Vollständige Anleitung für JSON-Import/Export
* **Google Consent Mode**: Detaillierte Erklärung der drei Modi (disabled/auto/manual)n
  * Media Query Support für `prefers-color-scheme`
* **Copy-Funktionalität**: 
  * Clipboard-Copy Web Component für Template-Code
  * Ein-Klick-Kopieren für Privacy-Settings-Links
  * Tooltip-Feedback für Nutzer
* **Responsive Design**: Mobile-optimierte Timeline-Darstellung
* **Accessibility**: Verbesserte Screenreader-Unterstützung

### Internationalisierung

* **Vollständige i18n-Implementation**: 
  * 50+ neue Übersetzungsstrings für Quickstart-Modal
  * 25+ neue Strings für Config-Seite
  * Alle hardcodierten deutschen Texte durch i18n-Calls ersetzt
  * Parameter-Platzhalter für dynamische Inhalte
  * Formelle Ansprache statt "Du/Sie" Mix

### Technische Verbesserungen

* **Externe CSS-Dateien**: `consent_quickstart.css` mit bedingtem Laden nur wo benötigt
* **Verbesserte Nutzerrechte**: `rex_perm::register('consent_manager[editor]')` für Redakteure
* **JavaScript-Optimierung**: Externe Dateien statt Inline-Code für bessere Performance
* **Enhanced Service-Detection**: Verbessertes Service-Detection mit Default-Status-Anzeige
* **Cookie-Parsing**: Erweiterte Cookie-Analyse mit URL-Dekodierung und JSON-Formatierung

### Benutzererfahrung

* **Setup-Wizard-Reorder**: Standard-Setup als erste Option für bessere Nutzerführung
* **Visuelles Feedback**: Statusanzeigen und Progress-Indikatoren
* **Intuitive Navigation**: Klare Schrittabfolge mit visuellen Hinweisen

* Domain-Tabelle erweitert um `google_consent_mode_v2` Feld für domainspezifische GMv2-Aktivierung

### Dateien hinzugefügt

* `assets/google_consent_mode_v2.min.js`: Minifizierte Google Consent Mode v2 Implementierung
* `assets/consent_debug.js`: Umfassende Debug-Konsole für Entwickler

### Bugfixes

* Debug-Panel zeigt nun aussagekräftige Informationen auch vor Consent-Erteilung
* Verbesserte Fehlerbehandlung bei Service-Funktionsaufrufen
* Z-Index-Konflikte mit Consent-Dialog behoben

## Version 4.3.1 - 17.04.2024

**ACHTUNG:** In Version 4.3.0 wurde das Fragment, welches den Frontend-Code zusammenbaut (`consent_manager_box.php`),
angepasst. Sollte das Fragment **überschrieben** worden sein, muss es entsprechend angepasst werden. Bitte führt in
diesem Fall einen Merge durch.

### Bugfixes

* Unselect-Skripte werden nun bei jedem Page-Load ausgeführt (Fix für Setups, wo der CM mit Reload eingestellt ist) @bitshiftersgmbh

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
