# REDAXO consent_manager - Changelog

## Version 5.3.0-beta5 - 25.01.2026

**🚀 Release-Highlights:**  
Setup-Wizard für Erstkonfiguration, Domain-spezifische Themes mit Live-Preview, moderne Theme-Vorschau mit 32 Varianten, Google Consent Mode v2 Optimierungen, vollständiges Security-Audit mit CSP-Nonce-Schutz, Multi-Language-Verbesserungen mit editierbaren Script-Feldern, automatische Frontend-Einbindung per Domain-Option mit Template-Positivliste, erweiterte Debug-Tools mit Cookie-Analyse, Lazy Loading der Consent-Box, CSS-Minifizierung mit Caching und umfassende Performance-Optimierungen.

---

### ⚡ Performance-Optimierungen (NEU)

**Lazy Loading der Consent-Box:**
- **On-Demand-Laden**: Box-Template wird erst bei Bedarf über API-Endpoint geladen
- **Reduzierte initiale Payload**: JavaScript-Datei ~40% kleiner beim ersten Seitenaufruf
- **HTTP-Caching**: API-Response nutzt ETag und Cache-Control für optimale Browser-Caching
- **304 Not Modified Support**: Browser-Cache wird automatisch validiert
- **Neuer API-Endpoint**: `index.php?rex-api-call=consent_manager_texts` liefert Texte und Box-Template als JSON
- **Cache-Log-ID basiertes Caching**: Stabiler Cache-Key verhindert unnötige Re-Downloads
- **Automatische Aktivierung**: Lazy Loading ist standardmäßig aktiv, keine Konfiguration nötig

**CSS-Minifizierung:**
- **Automatische Minifizierung**: Alle Theme-CSS-Dateien werden automatisch minifiziert
- **String-basierte Minifizierung**: Verwendet `str_replace()` statt Regex für maximale Zuverlässigkeit (kein PCRE Backtrack Limit)
- **Datei-basiertes Caching**: Minifiziertes CSS wird mit mtime-Hash gecacht
- **Automatische Cache-Invalidierung**: Bei CSS-Änderungen wird Cache automatisch neu generiert
- **~40-50% kleineres CSS**: Entfernt Kommentare, Whitespace und Zeilenumbrüche
- **Alte Cache-Cleanup**: Veraltete Cache-Dateien werden automatisch gelöscht
- **Keine Konfiguration nötig**: Funktioniert out-of-the-box für alle Themes

### 🛡️ Security-Verbesserungen

**Path-Traversal-Schutz für Themes:**
- **Theme-Namen-Validierung**: Regex-Prüfung gegen schädliche Zeichen (`..`, etc.)
- **Realpath-Prüfung**: Validiert dass Theme-Datei innerhalb des Assets-Verzeichnisses liegt
- **Security-Audit compliant**: Alle Theme-Zugriffe sind gegen Directory-Traversal abgesichert

### 👩‍💻 Für Developer

**Neue/Geänderte APIs:**
- **`Frontend::getFrontendCss()`** - Gibt jetzt automatisch minifiziertes und gecachtes CSS zurück
- **Neuer API-Endpoint** `index.php?rex-api-call=consent_manager_texts` für Lazy Loading:
  - Parameter: `clang` (int, required), `domain` (string, optional)
  - Response: JSON mit `texts`, `boxTemplate` und `cache` Informationen
  - Unterstützt HTTP-Caching (ETag, 304 Not Modified)
- **Neue JavaScript-API** `window.GoogleConsentModeV2.getCurrentSettings()` für Runtime-Zugriff auf Consent-Flags
- **Cache-Struktur**: Neue Dateien unter `/redaxo/cache/addons/consent_manager/css_*.css`

**Breaking Changes:** Keine

**Empfehlungen:**
- Theme-CSS wird automatisch minifiziert - keine Anpassungen nötig
- Lazy Loading ist standardmäßig aktiv - Box-Template wird on-demand geladen
- Custom Themes sollten die neue Theme-Editor-Funktion nutzen statt direkte Datei-Manipulation

---

### 🧙 Setup-Wizard (NEU)

Interaktiver Setup-Assistent für schnelle Erstkonfiguration:
- **Quickstart-Button** mit animiertem Farbverlauf-Rahmen auf der Konfigurationsseite
- **4-Schritt-Wizard**: Domain einrichten → Services importieren → Theme auswählen → Fertig
- **SSE-basierte Fortschrittsanzeige** mit Echtzeit-Feedback
- **YRewrite-Integration**: Automatische Auswahl aus vorhandenen YRewrite-Domains
- **Auto-Inject-Option**: Toggle-Switch für automatische Frontend-Einbindung
- **Template-Positivliste**: Multi-Select für Auswahl aktiver Templates, in denen Consent Manager eingebunden werden soll
- **Standard/Minimal Setup**: Auswahl zwischen 25 vorkonfigurierten Services oder nur notwendigen Cookies
- **Duplikat-Prävention**: Überspringt bereits vorhandene Services automatisch
- **Success-Screen mit Animation**: "HEUREKA!"-Banner mit Blau-Grün-Gradient und animiertem Emoji
- **Code-Generator**: Fertiger Footer-Link-Code zum Kopieren mit `data-consent-action="settings"` (empfohlen)
- **Required Group Assignment**: Die "Technisch notwendig"-Gruppe wird automatisch der neuen Domain zugeordnet

### 🎨 Domain-spezifische Themes

Jede Domain kann nun ein eigenes Theme verwenden:
- **2-Spalten-Layout** im Domain-Formular mit dedizierter Theme-Sidebar
- **Live-Preview mit Echtzeit-Aktualisierung**: Theme-Vorschau aktualisiert sich sofort beim Wechsel des Themes im Dropdown
- **Dynamisch skalierte iframe-Vorschau** passt sich automatisch an die Sidebar-Breite an
- Theme-Auswahl in der Sidebar mit allen verfügbaren Addon- und Project-Themes
- Support für Theme-Editor-Themes (Project-Addon) mit Stern-Markierung
- Frontend lädt Themes mit Priorität: Domain-Theme → Globales Theme → Standard-CSS
- Neue `theme`-Spalte in der `consent_manager_domain` Tabelle
- Themes werden im Cache gespeichert für optimale Performance
- Responsive Design: Sidebar wandert auf mobilen Geräten unter das Formular
- Sidebar-Widget mit subtilen Schatten und Rahmen, funktioniert in Dark- und Light-Themes

### 🎭 Moderne Theme-Preview

Komplett neu gestaltete Preview-Seite ohne Hintergrundbilder:
- **32 verschiedene Vorschau-Varianten**: Zufällige Kombination aus 8 Pastellfarben (Pink, Blau, Grün, Lavendel, Pfirsich, Gelb, Türkis, Violett) und 4 Layouts (Default, Centered, Sidebar, Split)
- **Professionelle SVG-Icons** statt Emojis für alle Navigationselemente
- **Echte Website-Integration**: Cookie-Box, Hell/Dunkel-Toggle und Schließen-Button als Teil der Navigation
- **Farblich abgestimmte Hintergründe** für harmonisches Gesamtbild
- **Verbesserte Thumbnail-Darstellung**: Overlay verhindert versehentliches Scrollen in der Vorschau
- Cookie-Box öffnet automatisch beim Laden der Preview
- Dynamisches iframe-Management im Modal für stabiles Schließverhalten

### �️ Security & XSS-Schutz

Vollständiges Security Audit durchgeführt und alle Inline-Scripts abgesichert:
- **CSP-Nonce-Schutz** für alle Inline-`<script>`-Tags im Backend (config.php, theme.php, theme_preview.php, domain.php, log.php)
- Alle Inline-Scripts verwenden `rex_response::getNonce()`
- Konsistente Verwendung von `rex_escape()` und `htmlspecialchars()` für sichere Ausgabe
- Keine direkte `$_GET`/`$_POST` Verwendung (ausschließlich `rex_request::`)
- CSRF-Token-Schutz für alle Formular-Aktionen

### 🔍 Debug-Widget Verbesserungen

Umfangreiche Erweiterungen des Debug-Widgets für bessere Fehlerdiagnose:
- **Cookie-Attribut-Details**: Anzeige von Größe (Bytes), Alter (Tage/Stunden), SameSite, Secure, Path und Domain
- **Cookie-Größe-Warning**: Automatische Warnung bei Cookies > 4KB (Browser-Limit) mit farblichen Hinweisen
- **Consent-Alter-Tracking**: Zeigt Alter des Consents an, warnt bei > 365 Tagen (DSGVO-Empfehlung)
- **Duplicate-Script-Detection**: Erkennt doppelt geladene externe Scripts (GA, GTM, Facebook Pixel, Matomo) mit GTM-Hinweis
- **Konfigurierter Cookie-Namen-Check**: Debug-Widget verwendet nun den im Backend konfigurierten Cookie-Namen statt hart kodiertem 'consentmanager'
- **Verbesserte Probleme-Erkennung**: Alle Checks verwenden zentrale Variablen-Deklaration zur Vermeidung von Code-Duplizierung

### 🛡️ Google Consent Mode v2 Optimierungen

Runtime-basierte Implementierung statt localStorage:
- **Runtime-Daten**: Google Consent Mode arbeitet nun komplett mit JavaScript Runtime-Daten (`currentConsentSettings`) statt localStorage
- **Neue API**: `window.GoogleConsentModeV2.getCurrentSettings()` für Zugriff auf aktuelle Consent-Flags
- **Debug-Widget-Integration**: Prüft auf `window.GoogleConsentModeV2` und `window.currentConsentSettings` statt localStorage
- **Automatische Updates**: `window.currentConsentSettings` wird bei jeder Consent-Änderung aktualisiert
- **Verbesserte Fehlermeldungen**: Warnung "Runtime-Daten fehlen" statt "localStorage fehlt"

### 🚫 Duplikat-Prävention

Automatische Verhinderung von doppelt geladenen Scripts:
- **Smart Script Loading**: Externe Scripts werden vor dem Laden auf Duplikate geprüft
- **DOM-Query-Check**: `document.querySelector('script[src="..."]')` verhindert mehrfaches Laden derselben URL
- **Console-Warnings**: Bei Duplikaten wird gewarnt: "Script bereits geladen - Duplikat verhindert"
- **GTM-Kompatibilität**: Verhindert Konflikte wenn Google Tag Manager bereits GA/Facebook Pixel geladen hat
- **Performance-Optimierung**: Reduziert unnötige HTTP-Requests und verhindert doppeltes Tracking

### 🌍 Multi-Language-Verbesserungen

Script-Felder nun in allen Sprachen editierbar mit automatischem Fallback:
- **Editierbare Script-Felder**: Script-Felder sind nun in allen Sprachen bearbeitbar (nicht mehr nur Start-Sprache)
- **Automatischer Fallback**: Leere Script-Felder in Nicht-Start-Sprachen fallen automatisch auf die Start-Sprache zurück
- **Sprachspezifische Tracking-IDs**: Ermöglicht unterschiedliche Google Analytics Property-IDs oder Facebook Pixel-IDs pro Sprache
- **Sprach-Switcher im Backend**: Direkter Wechsel zwischen Sprachen eines Services ohne erneute Suche
- **Use Case**: Perfekt für Multi-Language-Websites mit regionalisierten Analytics-Properties (z.B. DE: G-63VK6WGL5D, NL: G-0FT96PN7YQ)

### 🐛 Bugfixes

- **Rexstan-Konformität**: Type-Check in `GoogleConsentMode::getDomainConfig()` für strikte Typ-Prüfung (0 Rexstan-Fehler)
- **JavaScript-Syntax**: Behoben doppelte Deklaration von `cookieName` in `consent_debug.js`

### ⚡ Performance-Optimierungen

JavaScript-Dateien umfassend optimiert für bessere Performance:
- **Event-Listener optimiert**: Cookie-Link-Handler verwendet jetzt Event-Delegation statt mehrfacher `querySelectorAll`
- **Automatisches Link-Handling**: Links mit `data-consent-action="settings"` (empfohlen) oder `data-consent-action="settings,reload"` (mit Auto-Reload) öffnen automatisch Cookie-Box
- **dontshow Flag**: `data-consent-action="settings,dontshow"` verhindert automatisches Öffnen der Box beim ersten Besuch, Link funktioniert weiterhin per Klick
- **Legacy-Support**: Bestehende Klassen `.consent_manager-show-box` und `.consent_manager-show-box-reload` funktionieren weiterhin
- **DOM-Query-Caching**: Wiederholte `getElementById`/`querySelector`-Aufrufe durch Variablen-Caching ersetzt
- **Set statt indexOf**: `consents.indexOf()` durch `Set.has()` ersetzt für O(1) statt O(n) Lookup-Performance
- **Optimierte Schleifen**: `forEach` mit `every` kombiniert für frühen Abbruch bei negativen Checks
- **Reduziertes Debug-Logging**: Debug-Ausgaben nur noch bei aktiviertem Debug-Modus
- **Script-Duplikat-Check verbessert**: Externes Script-Set wird einmalig erstellt statt pro Script-Check
- **Event-Namespace für Preview**: Keydown-Listener für Theme-Preview nutzt Namespace `.consentPreview` für sauberes Cleanup
- **jQuery-Optimierungen im Backend**: DOM-Elemente gecacht, `one()` statt `on()`/`off()` für einmalige Events
- **Cookie-Parse-Optimierung**: Cookie-Wert wird einmalig geparst und wiederverwendet

### 🚀 Automatische Frontend-Einbindung

Neues Feature für einfachere Integration ohne Template-Anpassung:
- **Auto-Inject Option**: Pro Domain aktivierbare automatische Einbindung im Frontend
- **Template-Positivliste**: Multi-Select zur Auswahl aktiver Templates für gezielte Einbindung
  - Leer lassen = Consent Manager wird in allen Templates eingebunden (Standardverhalten)
  - Templates auswählen = nur in ausgewählten Templates wird eingebunden
  - Live-Search, Select All/Deselect All, Count-Display
  - Sinnvoll für Websites mit API-Endpoints, AJAX-Templates, Print-Versionen, RSS-Feeds
  - Neue Datenbankspalte: `auto_inject_include_templates` (TEXT, kommagetrennte Template-IDs)
- **OUTPUT_FILTER Integration**: Consent Manager wird automatisch vor `</head>` eingefügt
- **Keine Template-Änderung nötig**: Aktivierung per Checkbox in der Domain-Konfiguration
- **Intelligente Erkennung**: Nur bei HTML-Seiten mit `</head>` Tag aktiv
- **Kompatibel mit manueller Einbindung**: Kann parallel zu bestehenden Integrationen genutzt werden
- **Backend-UI**: Neue Spalte "Auto-Inject" in der Domain-Übersicht
- **Neue Datenbank-Spalten**: 
  - `auto_inject` - Aktivierung der automatischen Einbindung (tinyint)
  - `auto_inject_reload_on_consent` - Seite bei Consent-Änderung neu laden (tinyint)
  - `auto_inject_delay` - Verzögerung bis zur Anzeige in Sekunden (int)
  - `auto_inject_focus` - Fokus auf Consent-Box setzen (Barrierefreiheit) (tinyint)
  - `auto_inject_include_templates` - Template-IDs für Positivliste (text)

### 📝 Dokumentation

- **README kompakter**: Emojis aus Überschriften entfernt (außer deprecated-Warnung)
- **Cookie-Liste**: Nur noch PHP-Integration dokumentiert (`Frontend::getCookieList()`)
- **Footer-Link**: Dokumentation vereinfacht für data-attribute-basiertes Auto-Handling
- **Cookie-Einstellungen-Link**: `data-consent-action="settings"` (empfohlen) für automatisches Öffnen der Cookie-Box ohne onclick
- **Mit Auto-Reload**: `data-consent-action="settings,reload"` lädt Seite nach Consent-Änderung neu
- **dontshow Flag**: `data-consent-action="settings,dontshow"` verhindert Auto-Display beim ersten Besuch, Link funktioniert per Klick
- **Legacy-Support**: Bestehende Klassen `.consent_manager-show-box` und `.consent_manager-show-box-reload` weiterhin dokumentiert
  - Leer lassen = Consent Manager wird in allen Templates eingebunden (Standardverhalten)
  - Templates auswählen = nur in ausgewählten Templates wird eingebunden
  - Live-Search, Select All/Deselect All, Count-Display
  - Sinnvoll für Websites mit API-Endpoints, AJAX-Templates, Print-Versionen, RSS-Feeds
  - Neue Datenbankspalte: `auto_inject_include_templates` (TEXT, kommagetrennte Template-IDs)
- **OUTPUT_FILTER Integration**: Consent Manager wird automatisch vor `</head>` eingefügt
- **Keine Template-Änderung nötig**: Aktivierung per Checkbox in der Domain-Konfiguration
- **Intelligente Erkennung**: Nur bei HTML-Seiten mit `</head>` Tag aktiv
- **Kompatibel mit manueller Einbindung**: Kann parallel zu bestehenden Integrationen genutzt werden
- **Backend-UI**: Neue Spalte "🚀 Auto-Inject" in der Domain-Übersicht
- **Neue Datenbank-Spalten**: 
  - `auto_inject` - Aktivierung der automatischen Einbindung (tinyint)
  - `auto_inject_reload_on_consent` - Seite bei Consent-Änderung neu laden (tinyint)
  - `auto_inject_delay` - Verzögerung bis zur Anzeige in Sekunden (int)
  - `auto_inject_focus` - Fokus auf Consent-Box setzen (Barrierefreiheit) (tinyint)
  - `auto_inject_include_templates` - Template-IDs für Positivliste (text)

#### Auto-Inject Konfigurationsoptionen

**🔄 Reload bei Consent-Änderung**
- Automatisches Neuladen der Seite nach Consent-Speicherung
- Optimale Integration von Drittanbieter-Scripts die Reload benötigen
- Default: Deaktiviert

**⏱️ Verzögerte Anzeige**
- Optional: Verzögerung in Sekunden bis zur Consent-Box-Anzeige
- Verbessert First-Paint Performance
- Nützlich für bessere User Experience
- Default: 0 (sofortige Anzeige)

**♿ Fokus-Management**
- Automatischer Fokus auf Consent-Box (WCAG 2.1 konform)
- Verbessert Barrierefreiheit und Keyboard-Navigation
- Screen-Reader-freundlich
- Default: Aktiviert

**✨ Manuelle Integration unterstützt**
- Optionen auch bei Template-Integration nutzbar
- `window.consentManagerOptions` Object vor Frontend-JS setzen
- Überschreibt Auto-Inject-Einstellungen
- Vollständige Kontrolle über Verhalten

---

## Version 5.2.0 - 19.01.2026

### 🚀 Features

* **Neue Public API**: Einführung der Klasse `FriendsOfRedaxo\ConsentManager\ConsentManager` für den einfachen Zugriff auf gecachte Daten (Cookies, Gruppen, Texte, Domains)
* **Performance**: Interne Klassen (`Frontend`, `InlineConsent`, `GoogleConsentMode`) nutzen nun den Cache statt direkter SQL-Abfragen
* **Code-Qualität**: Refactoring der `InlineConsent` Klasse zur Vermeidung von Code-Duplizierung bei der Video-ID-Erkennung
* **Statistik**: Neue Auswertung der Consent-Logs im Backend (Tägliche Consents, Top-Services)
* **Privacy**: Dynamische Cookie-Laufzeit - Bei minimaler Zustimmung (nur notwendige Cookies) wird die Laufzeit auf 14 Tage begrenzt (Privacy by Design). Die Cookie-Beschreibung wird im Setup und Update automatisch angepasst ("14 Tage / 1 Jahr").
* **API Dokumentation**: Neue Dokumentation der öffentlichen API in der README.md

### 🛡️ Security

* **CSP**: Nonce-Schutz für Inline-Skripte im Backend-Log hinzugefügt

### 🐛 Bugfixes

* **Button-Layout responsive optimiert**: Buttons passen sich jetzt der Textlänge an und nutzen auf Desktop `flex: 1` für gleichmäßige Verteilung
* **Localization**: Fehlende Übersetzungen im Statistik-Modul ergänzt

---

## Version 5.1.3 - 18.12.2025

### 🐛 Bugfixes

* **Theme-Editor Button-Hintergrundfarbe**: Button-Hintergrundfarbe wird jetzt korrekt aus dem Theme-Editor übernommen (Fix: SCSS-Variable-Interpolation korrigiert)

---

## Version 5.1.2 - 17.12.2025

### 🎨 Theme-Editor Erweiterungen

* **Neue Design-Themes**: 5 neue Themes hinzugefügt (Autor: @skerbis):
  - **Light Glass v2**: iOS 26 Liquid Glass Design mit Prisma-Rahmen-Animation
  - **Dark Glass v2**: iOS 26 Liquid Glass Design in Dunkel mit Border-Glow
  - **Sand**: Neumorphismus-Style mit Inset-Buttons bei Hover
  - **Sand Dark**: Dunkle Variante des Sand-Themes
  - **Pill**: Kompaktes Bottom-Pill-Banner mit Slide-up Animation
* **Standard-Themes modernisiert**: Outline-Buttons, 5px Abrundung, prefers-contrast Support
* **Neue Theme-Basis-Varianten**: 5 neue Accessibility-Themes hinzugefügt:
  - **Banner Top**: Volle Breite, fixiert am oberen Bildschirmrand
  - **Banner Bottom**: Volle Breite, fixiert am unteren Bildschirmrand (dunkles Theme)
  - **Minimal**: Kompakte Ecke unten rechts, wenig aufdringlich
  - **Minimal Dark**: Kompakte Ecke in Anthrazit mit Outline-Buttons
  - **Fluid**: Responsive mit Glaseffekt (backdrop-filter), fluid Typography mit clamp()
  - **Fluid Dark**: Fluid-Theme in Anthrazit mit Glaseffekt
* **Glaseffekt-Transparenz**: Neue Opacity-Slider für Hintergrund und Details-Bereich beim Fluid-Theme
* **Barrierefreiheits-Warnung**: Hinweis beim Fluid-Theme über mögliche Einschränkungen bei Glaseffekten
* **`prefers-reduced-transparency` Support**: Automatischer Fallback auf undurchsichtige Hintergründe für Nutzer die Transparenz reduzieren möchten
* **Schriftgrößen-Einstellungen**: Neue Slider für allgemeine Schriftgröße (12-22px) und Button-Schriftgröße (12-20px)
* **DSGVO-konforme Buttons**: Alle vorinstallierten A11y-Themes zeigen jetzt gleichwertige Buttons ohne Hervorhebung - keine visuelle Bevorzugung von "Alle akzeptieren"

### 🐛 Bugfixes

* **Button-Text Umbruch**: `white-space: nowrap` verhindert mehrzeilige Button-Texte (Barrierefreiheit)
* **Button-Style Outline**: Outline-Button-Stil wird jetzt korrekt angewendet
* **Theme-Übersicht Headline-Überlappung**: Close-Button überdeckt nicht mehr den Titel bei kompakten Themes
* **Theme-Editor Slider-Updates**: Event-Listener reagieren jetzt korrekt auf `rex:ready` für PJAX-Navigation

### 🔒 Sicherheitsfixes

* **XSS-Schutz in Debug-Panel**: Alle Cookie- und LocalStorage-Werte werden jetzt mit `escapeHtml()` escaped, bevor sie im Debug-Panel angezeigt werden
* **URL-Parameter Encoding**: Alle URL-Parameter in der Backend-Suche werden jetzt mit `encodeURIComponent()` escaped
* **SQL Prepared Statements**: Queries in `update.php` auf Prepared Statements umgestellt
* **API Input-Validierung**: Längenbegrenzungen für alle API-Parameter (Domain max 255, Consent-ID max 30, UIDs max 50 Zeichen)

### 🧹 Code-Qualität

* **IE11-Polyfills entfernt**: Obsolete Polyfills für IE11 (classList, DOMParser, NodeList.forEach, etc.) entfernt - IE11 ist seit Juni 2022 End-of-Life
* **GitHub Security Action**: Neue automatisierte Sicherheitsprüfung mit CodeQL und Semgrep
* **Semgrep REDAXO-Regeln**: Custom Security-Regeln für REDAXO-spezifische Patterns (rex_escape, SQL Injection, etc.)

---

## Version 5.1.0 - 10.12.2025

> ⚠️ **DRINGEND - Sicherheitsupdate:** Diese Version enthält wichtige Sicherheitsfixes. Ein Update wird dringend empfohlen. Vielen Dank an die **Deutsche Telekom Security GmbH** für die verantwortungsvolle Meldung der Schwachstellen.

### 🎨 Neuer Theme-Editor

Der Theme-Editor wurde komplett überarbeitet und bietet jetzt umfangreiche Anpassungsmöglichkeiten für barrierefreie Consent-Dialoge:

* **WCAG 2.1 Kontrast-Prüfung**: Live-Badges zeigen Kontrastverhältnisse (AAA/AA/unzureichend)
* **Automatische Textfarben**: Berechnet optimale Text-/Hintergrund-Kombinationen per Klick
* **Details-Bereich anpassbar**: Separate Farben für den aufgeklappten Cookie-Details-Bereich
* **"Details anzeigen" Button**: Farbe, Hover-Farbe und Rahmen individuell einstellbar
* **Button-Styles**: Filled/Outline-Stil, individueller Eckenradius, Rahmenbreite und -farbe
* **Schatten-Effekte**: 5 Schatten-Stile (Kein/Dezent/Mittel/Stark/Schwebend) mit Farbauswahl
* **Live-Vorschauen**: Alle Änderungen sofort sichtbar in Vorschau-Elementen
* **Fragment-Architektur**: Code nach REDAXO-Konventionen refaktoriert (Page + Fragment)

### 🐛 Bugfixes

* **Debug-Widget Cookie-Name**: Das Debug-Widget suchte nach dem alten Cookie-Namen `consent_manager` statt `consentmanager`
* **Debug-Widget Versionsvergleich entfernt**: Die fehlerhafte Versions-Warnung wurde entfernt

### 🔒 Sicherheitsfixes

* **XSS in Theme-Editor (CVE pending)**: Unsichere Ausgabe von Konfigurationswerten im Theme-Editor behoben - alle Werte werden jetzt mit `rex_escape()` escaped
* **XSS in Service-Konfiguration**: Fehlende Escapierung bei der Ausgabe von Cookie/Service-Konfigurationen im Backend behoben

---

## Version 5.0.4 - 09.12.2025

### ♿ Barrierefreiheit / UX

* **Fokus auf Dialog statt Button:** Der initiale Fokus beim Öffnen des Consent-Dialogs liegt jetzt auf dem Dialog-Wrapper statt auf dem ersten Button. Dies verhindert, dass Nutzer unbewusst auf "Alle ablehnen" fokussiert werden und entspricht den WCAG 2.1 Richtlinien für modale Dialoge. Nutzer können weiterhin per Tab zu allen interaktiven Elementen navigieren.

## Version 5.0.3 - 09.12.2025

### 🐛 Bugfix

* **Consent-Log repariert:** Die API-Klasse `ConsentManager` und das Fragment `cookiedb.php` verwendeten noch den alten Cookie-Namen `consent_manager` statt des neuen `consentmanager`. Dadurch wurden Consents nicht mehr in die Datenbank-Tabelle `rex_consent_manager_consent_log` geschrieben und die Consent-Historie wurde nicht angezeigt.

## Version 5.0.2 - 09.12.2025

### 🐛 Bugfixes

* **InlineConsent Platzhalter repariert:** Die Methode `Utility::has_consent()` verwendete noch den alten Cookie-Namen `consent_manager` statt des neuen `consentmanager`. Dadurch funktionierte die serverseitige Consent-Prüfung für Inline-Platzhalter nicht korrekt.
* **Service-spezifische Platzhalter-Texte:** Die Felder `placeholder_text` und `placeholder_image` aus dem Cookie/Service-Eintrag werden jetzt im Inline-Consent-Fragment korrekt verwendet. Priorität: 1. Explizit übergebene Options, 2. Service-Daten aus DB, 3. Globaler Fallback aus Texte-Tabelle.

## Version 5.0.1 - 09.12.2025

### 🐛 Bugfix

* **Installer-Update repariert:** Die Cache-Klasse wird jetzt beim Update explizit geladen, da der Autoloader zu diesem Zeitpunkt noch nicht aktiv ist.

## Version 5.0.0 - 09.12.2025

### 🔒 Breaking Changes

* **Cookie-Name geändert:** Der Consent-Cookie heißt jetzt `consentmanager` statt `consent_manager` (ohne Unterstrich). Dies behebt Kompatibilitätsprobleme nach Updates und erzwingt eine Neubestätigung bei Benutzern, was DSGVO-konform bei größeren Änderungen empfohlen ist.
* **Legacy iwcc-Migration entfernt:** Der Migrations-Code für das alte `iwcc`-Addon wurde entfernt. Wer noch vom uralten `iwcc`-Addon migrieren muss, sollte erst auf Version 4.x updaten.

### 🚀 Neue Features & Verbesserungen

* **Async Fetch API:** Synchroner XMLHttpRequest durch asynchrone Fetch API ersetzt - verbessert Performance und beseitigt Browser-Warnungen
* **CSP-Kompatibilität:** `new Function()` Validierung entfernt für strikte Content Security Policy Kompatibilität
* **Verbesserte Fehlerbehandlung:** Detaillierte CSP/CORS-Fehlermeldungen mit Lösungshinweisen
* **URL Encoding:** Parameter werden jetzt korrekt URL-encoded für bessere Sicherheit
* **Code-Optimierungen:** Redundanter Code entfernt, Performance-Verbesserungen (68 Zeilen weniger)
* **Command log-delete:** aktiviert und verfügbar (in package.yml registriert)
* **Cronjob ThumbnailCleanup:** ab dieser Version aktiviert und verfügbar.

### 🛠️ Technische Änderungen

* **Namespace FriendsOfRedaxo\ConsentManager**

  Für eine Übergangszeit und um die Umstellung eigenen PHP-Codes auf Namespace-Klassen zu erleichtern,
  stehen die alten Klassennamen weiterhin zur Verfügung, tragen jedoch einen deprecated-Vermerk.

  In der Liste der geänderten Funktionen steht `...` als Abkürzung für `FriendsOfRedaxo\ConsentManager`
  * Datei und Klassennamne von `rex_api_consent_manager_inline_log` geändert in `...\Api\InlineLog`.  
    Externer API-Name `consent_manager_inline_log` beibehalten
  * Datei und Klassenname von `rex_api_consent_manager` geändert in `...\Api\ConsentManager`.  
    Externer API-Name `consent_manager` beibehalten
  * Datei und Klassenname von `consent_manager_clang` geändert in `...\CLang`
  * Datei und Klassenname von `consent_manager_inline` geändert in `...\InlineConsent`
  * Datei und Klassenname von `consent_manager_config` geändert in `...\Config`
  * Datei und Klassenname von `consent_manager_cache` geändert in `...\Cache`
  * Datei und Klassenname von `consent_manager_frontend` geändert in `...\Frontend`
  * Datei und Klassenname von `consent_manager_rex_form` geändert in `...\RexFormSupport`
  * Datei und Klassenname von `consent_manager_rex_list` geändert in `...\RexListSupport`
  * Datei und Klassenname von `consent_manager_util` geändert in `...\Utility`
  * Datei und Klassenname von `consent_manager_google_consent_mode` geändert in `...\GoogleConsentMode`
  * Datei und Klassenname von `consent_manager_json_setup` geändert in `...\JsonSetup`
  * Datei und Klassenname von `consent_manager_oembed_parser` geändert in `...\OEmbedParser`
  * Datei und Klassenname von `consent_manager_theme` geändert in `...\Theme`
  * Datei und Klassenname von `consent_manager_thumbnail_cache` geändert in `...\ThumbnailCache`
  * Datei und Klassenname von `rex_consent_manager_thumbnail_mediamanager` geändert in `...\ThumbnailMediaManager`
  * Datei und Klassenname von `rex_consent_manager_command_log_delete` geändert in `...\Command\LogDelete`
  * Datei und Klassenname von `rex_cronjob_log_delete` geändert in `...\Cronjob\LogDelete`.  
    (Unterverzeichnis `lib/Cronjob` für die Cronjob-Klassen eingerichtet)
  * Datei und Klassenname von `rex_cronjob_consent_manager_thumbnail_cleanup` geändert in `...\Cronjob\ThumbnailCleanup`
  * Shorthand-Funktion `doConsent` aus InlineConsent.php in eine eigene Datei doConsent.php verschoben.
* **consent_manager_google_consent_helper:** Datei und Klasse entfernt; nicht mehr in Benutzung 
* **Cronjob LogDelete (ex. rex_cronjob_log_delete):** vorhandene Cronjobs in Tabelle `rex_cronjob`  werden automatisch auf den neuen Namen inkl. Namespace geändert.
* **Namespace-Guide.md:** Hinweise zur Umstellung eigenen Codes auf Namespace-Klassen
* **`lib/deprecated`:** Verzeichnis mit Hilfsklassen (alter Klassenname) für die reibungslose Umstellung auf Namespace-Klassen
* **Globale Variablen:** Direkten Zugriff durch `rex_request::...` ersetzt
* **Fragmente:** Die Fragmente sind in ein Addon-spezifisches Unterverzeichnis `fragments/ConsentManager` verschoben. Alle interen Aufrufe sind angepasst (`$fragment->parse('ConsentManager/fragment.php')). Doku angepasst.

## Code-Refactoring und weiteres Bugfixing 

**PHP-Modernisierung und Verbesserungen:**

* Einführung von strikter Typisierung (int, string, array, bool) für alle Methoden und Eigenschaften
* Erweiterung von PHPStan-Annotationen mit detaillierten Typinformationen
* Verbesserung der InlineConsent-API mit dem Namespace `FriendsOfRedaxo\ConsentManager`
* Alle `doConsent()`-Aufrufe auf `InlineConsent::doConsent()` aktualisiert
* Rückgabewerte in allen Klassen korrekt deklariert
* Fehlerbehandlung mit Typprüfungen verbessert
* REDAXO-Anforderungen auf ^5.15 und PHP ^8.1 aktualisiert
* Cache-Handling mit typensicheren Operationen optimiert
* Nullable Typen in `ThumbnailCache` korrekt behandelt
* Cronjob-Klassen modernisiert mit Rückgabetypen
* PHPStan-Probleme durch bessere Typumwandlung und Validierung behoben
* Dokumentationsbeispiele auf neue Namespaced-API-Aufrufe aktualisiert

**Fehlerbehebungen und Optimierungen:**

* Media Manager Thumbnails repariert
* Rückgabewerte für `getName()` und `getParams()` im MediaManager-Effekt ergänzt
* Kommentare von GitHub Copilot Review umgesetzt
* `ThumbnailCleanup` Cronjob auf altersbasierte Bereinigung umgestellt
* Legacy-Cache-Kompatibilität in `install.php` korrigiert
* String-Vergleiche standardisiert (`$var !== ''`)
* Domain-ID-Typ von String auf Integer korrigiert
* Einheitliche Fehlerprüfmuster implementiert
* Debug-Logs mit Debug-Flag geschützt
* Deprecated-Klassen ignoriert
* #412 behoben
* Kommentierten Code entfernt
* `consent_inline.js` aktualisiert

**Frontend-spezifische Fixes:**

* Null-Safety-Prüfungen für Domain- und Cookie-Daten implementiert
* Array-Typvalidierung in `setDomain()` hinzugefügt
* Sichere Array-Zugriffe, um Warnungen zu vermeiden
* Null-Coalescing für Cookie-Skripte eingefügt
* Fehler bei fehlenden Domains verhindert
* Fehlerbehandlung bei unvollständiger Domain-Konfiguration verbessert

* **Cookie-Migration / Fix:** Vor dem Setzen eines neuen `consentmanager`-Cookies werden jetzt alte oder fehlerhafte `consent_manager*`-Cookies gelöscht. Das verhindert Fälle, in denen das Consent-Dialogfenster wiederholt geöffnet wird (Issue #424). **Hinweis:** Ab Version 5.0 wurde der Cookie-Name von `consent_manager` auf `consentmanager` geändert.


### 🐛 Bug Fixes

* **Doppelte Funktionsdefinition:** `consent_manager_showBox()` war zweimal definiert
* **Cookie-Name Konsistenz:** Alte minifizierte JS-Datei enthielt veralteten Cookie-Namen
* **DOM-Zugriffe optimiert:** `getAttribute('data-uid')` wird jetzt gecached statt mehrfach aufgerufen


### 📁 Neue Dateien

* `lib/deprecated`: Verzeichnis mit Hilfsklassen (alter Klassenname) für die reibungslose Umstellung auf Namespace-Klassen
* `fragments/ConsentManager`: neues Verzeichnis für die Fragmente; Fragmemt-Namen verkürzt (kein `consent_manager_` als Prefix)




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
