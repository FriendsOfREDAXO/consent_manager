# Consent-Manager für REDAXO CMS

![logo](https://github.com/FriendsOfREDAXO/consent_manager/blob/assets/consent_manager-logo.jpg?raw=true)

## Was ist der Consent-Manager?

Das AddOn stellt eine DSGVO-konforme Lösung für die Einholung von Einverständniserklärungen zu Cookies und externen Diensten bereit. Website-Besucher erhalten eine Consent-Box, in der einzelne Dienste-Gruppen akzeptiert oder abgelehnt werden können. Technisch notwendige Dienste bleiben dabei immer aktiv.

**Kernfunktionen:**
- Datenschutz-Opt-In-Banner für Dien## 🔍 Debug-Modus

## 🔍 Debug-Modus

**Consent-Debug-Panel:** Seit Version 4.4.0 verfügbar für Entwickler und Troubleshooting.

**Aktivierung:**
- **Domain-Konfiguration**: Debug-Modus in Domain-Einstellungen aktivieren
- **Backend-Login erforderlich**: Nur für angemeldete Administrator sichtbar

**Features:**
- Live-Anzeige aller Cookie-Stati
- Google Consent Mode Integration
- LocalStorage-Übersicht
- Service-Status-Monitor
- Menü-Indikator mit <i class="fa fa-bug"></i> Symbol bei aktivem Debug-Modusg-Panel:** Seit Version 4.4.0 verfügbar für Entwickler und Troubleshooting.

**Aktivierung:**
- **Domain-Konfiguration**: In **Domains** → Debug-Modus auf "Aktiviert" setzen
- **Backend-Login erforderlich**: Debug-Panel nur für angemeldete Backend-Benutzer sichtbar

**Features:**
- **🎯 Google Consent Mode v2 Status**: Zeigt aktiven Modus (Deaktiviert ❌ / Automatisch 🔄 / Manuell ⚙️)
- **Live-Anzeige aller Consent-Stati**: analytics_storage, ad_storage, ad_user_data, etc.
- **Service-Status-Monitor**: Welche Services sind aktiv und welchen Consent-Gruppen zugeordnet
- **Cookie-Analyse**: Strukturierte Darstellung aller Cookies mit JSON-Parsing
- **LocalStorage-Übersicht**: Einblick in alle gespeicherten Consent-Daten
- **Echtzeit-Updates**: Status ändert sich live bei Consent-Änderungen
- **Menü-Indikator**: <i class="fa fa-bug"></i> Symbol im Backend-Menü bei aktivem Debug-Modus

**Sicherheit:** Debug-Panel ist aus Sicherheitsgründen nicht für normale Website-Besucher verfügbar.s
- Flexible Gruppierung von Diensten
- Nachträgliche Änderung der Einstellungen möglich
- Vollständig anpassbare Texte und Designs
- Google Consent Mode v2 Integration
- Mehrsprachig und Multi-Domain-fähig

![Screenshot](https://github.com/FriendsOfREDAXO/consent_manager/blob/assets/consent_manager.png?raw=true)

## ⚠️ Rechtlicher Hinweis

**Wichtiger Haftungsausschluss:** Die mitgelieferten Texte und Cookie-Definitionen sind ausschließlich Beispiele und können unvollständig oder nicht aktuell sein. 

**Rechtliche Verantwortung:** Website-Betreiber und Entwickler sind eigenverantwortlich dafür zuständig, dass:
- Die Funktionalität der Abfrage den rechtlichen Anforderungen entspricht
- Alle Texte, Dienste und Cookie-Beschreibungen korrekt und vollständig sind
- Die Integration ordnungsgemäß erfolgt
- Die Lösung der geltenden Rechtslage und den Datenschutzbestimmungen entspricht

**Empfehlung:** Für die Formulierung der Texte und Cookie-Listen sollten Datenschutzbeauftragte oder die Rechtsabteilung konsultiert werden.

---

## 🚀 Schnellstart

### 1. Installation und Setup-Assistent
```bash
# AddOn über REDAXO Installer herunterladen und installieren
```

**Quickstart-Assistent:** Beim ersten Aufruf der Konfiguration führt Sie ein **7-stufiger Assistent** durch das komplette Setup - von der Domain-Konfiguration bis zur Theme-Auswahl.

**Setup-Varianten wählen:**
- **Minimal:** Nur essentieller Service für datenschutz-minimale Websites  
- **Standard:** Vollständige Service-Sammlung für umfassende Cookie-Verwaltung

### 2. Domain konfigurieren
Unter **Domains** die Website-Domain hinterlegen (ohne Protokoll):
```
beispiel.de
www.beispiel.de
```

### 3. Template-Integration
**Grundcode für Templates:**
```php
REX_CONSENT_MANAGER[forceCache=0 forceReload=0]
```

**PHP-Ausgabe:**
```php
<?php echo consent_manager_frontend::getFragment(false, false, 'consent_manager_box_cssjs.php'); ?>
```

### 4. Cookie-Einstellungen nachträglich aufrufen

**⚠️ Wichtiger Hinweis:** Stellen Sie sicher, dass Nutzer die Cookie-Einstellungen jederzeit wieder aufrufen können! Das ist rechtlich erforderlich und sollte gut sichtbar auf jeder Seite verfügbar sein.

**Empfohlene Integration:**
- **Footer-Link:** Platzieren Sie einen dauerhaften Link im Website-Footer
- **Datenschutz-Seite:** Verlinken Sie aus der Datenschutzerklärung
- **Barrierefreiheit:** Der Link sollte immer erreichbar sein

**HTML-Link:**
```html
<a class="consent_manager-show-box">Datenschutz-Einstellungen</a>
```

**Mit automatischem Reload:**
```html
<a class="consent_manager-show-box-reload">Datenschutz-Einstellungen</a>
```

**JavaScript-Aufruf:**
```javascript
consent_manager_showBox();
```

**Beispiel Footer-Integration:**
```html
<footer>
    <div class="footer-links">
        <a href="/impressum/">Impressum</a>
        <a href="/datenschutz/">Datenschutz</a>
        <a class="consent_manager-show-box">Cookie-Einstellungen</a>
    </div>
</footer>
```

### 5. Consent-Status abfragen
**JavaScript:**
```javascript
if (consent_manager_hasconsent('youtube')) {
    // YouTube wurde akzeptiert
}
```

**PHP:**
```php
<?php
if (consent_manager_util::has_consent('youtube')) {
    // YouTube wurde akzeptiert
}
?>
```

---

## 📋 Detaillierte Konfiguration

### Domain-Verwaltung

Jede Domain der REDAXO-Instanz muss einzeln konfiguriert werden:
- Domain ohne Protokoll hinterlegen (z.B. `www.beispiel.de`)
- Datenschutzerklärung und Impressum je Domain
- Automatischer Abgleich mit `$_SERVER['HTTP_HOST']`

**Google Consent Mode v2 Integration:**
- Pro Domain aktivierbar
- GDPR-konforme Standard-Einstellungen
- Automatische Script-Integration
- Debug-Konsole verfügbar

### Dienste konfigurieren

Jeder externe Dienst (Analytics, Social Media, etc.) wird einzeln angelegt:

**Schlüssel:** Interne Bezeichnung ohne Sonderzeichen
**Dienstname:** Wird in der Consent-Box angezeigt
**Cookie-Definitionen:** YAML-Format für Cookie-Details

### Cookie-Definitionen mit YAML

Das AddOn verwendet YAML-Format für die Definition von Cookie-Details:

**Beispiel Cookie-Definition:**
```yaml
-
 name: _ga
 time: "2 Jahre"
 desc: "Speichert für jeden Besucher eine anonyme ID für die Zuordnung von Seitenaufrufen."
-
 name: _gat
 time: "1 Tag"
 desc: "Verhindert zu schnelle Datenübertragung an Analytics-Server."
```

**JavaScript-Integration:**
```html
<script>
// Wird geladen, wenn Nutzer zustimmt
gtag('config', 'GA_MEASUREMENT_ID');
</script>
```

### Gruppen-Management

Dienste werden in Gruppen zusammengefasst, die einzeln akzeptiert werden können:

| Einstellung | Beschreibung |
|-------------|--------------|
| **Schlüssel** | Interne Bezeichnung ohne Sonderzeichen |
| **Technisch notwendig** | Gruppe ist immer aktiv und nicht deaktivierbar |
| **Domain** | Zuordnung zur entsprechenden Domain |
| **Name** | Anzeigename für Website-Besucher |
| **Beschreibung** | Erklärung der Gruppe |
| **Dienste** | Zugewiesene Services |

---

## 🎨 Design und Anpassung

### Theme-System

Das AddOn bietet verschiedene vorgefertigte Themes:

![Screenshot](https://github.com/FriendsOfREDAXO/consent_manager/blob/assets/themes.png?raw=true)

**Eigenes Theme erstellen:**
1. Bestehendes Theme kopieren
2. In `/project/consent_manager_themes/` ablegen
3. Dateiname: `consent_manager_frontend_theme_*.scss`
4. Anpassungen vornehmen
5. In Theme-Vorschau auswählen

**Theme-Vorschau testen:**
```
/redaxo/index.php?page=consent_manager/theme&preview=project:consent_manager_frontend_mein_theme.scss
```

### Individuelles Design

**Fragment anpassen:**
- Standard: `/redaxo/src/addons/consent_manager/fragments/consent_manager_box.php`
- Eigenes Fragment: `/theme/private/fragments/consent_manager_box.php`

**CSS-Ausgabe steuern:**
- Standardmäßig wird `consent_manager_frontend.css` ausgegeben
- Über Einstellungen deaktivierbar für eigene CSS-Implementierung

---

## 🔧 Erweiterte Features

### Google Consent Mode v2

Der Consent Manager bietet **drei Implementierungswege** für Google Consent Mode v2:

#### ❌ **Deaktiviert**
Google Consent Mode wird nicht verwendet - Standard GDPR-Verhalten ohne gtag-Integration.

#### 🤖 **Automatisch (Empfohlen)**

**Domain-Aktivierung:**
- In **Domains** → Google Consent Mode v2 auf "Automatisch (Auto-Mapping)" setzen
- System erkennt automatisch Services und mappt sie zu Consent-Flags
- Keine manuelle Programmierung erforderlich

**Debug-Konsole für Entwickler:**
```
Domain-Konfiguration → Debug-Modus "Aktiviert"
```
- **Domain-Konfiguration**: Debug-Modus in Domain-Einstellungen aktivieren
- **Backend-Login erforderlich**: Nur für angemeldete Administrator sichtbar

**Automatische Service-Mappings:**
```
Google Analytics     → analytics_storage
Google Tag Manager   → analytics_storage, ad_storage, ad_user_data, ad_personalization
Google Ads          → ad_storage, ad_user_data, ad_personalization  
Facebook Pixel      → ad_storage, ad_user_data, ad_personalization
YouTube             → ad_storage, personalization_storage
Google Maps         → functionality_storage, personalization_storage
Matomo/Hotjar       → analytics_storage
```

**Service-Schlüssel für automatische Erkennung:**
Die automatische Erkennung funktioniert über den **Service-Schlüssel (UID)**. Verwende diese Schlüssel:

| Service | Empfohlener Schlüssel | Alternative |
|---------|---------------------|-------------|
| **Google Analytics** | `google-analytics` | `analytics`, `ga` |
| **Google Tag Manager** | `google-tag-manager` | `gtm`, `tag-manager` |
| **Google Ads** | `google-ads` | `adwords`, `google-adwords` |
| **Facebook Pixel** | `facebook-pixel` | `facebook`, `meta-pixel` |
| **YouTube** | `youtube` | `yt` |
| **Google Maps** | `google-maps` | `maps`, `gmaps` |
| **Matomo** | `matomo` | `piwik` |
| **Hotjar** | `hotjar` | - |
| **Microsoft Clarity** | `microsoft-clarity` | `clarity` |

**🔧 Flexible UID-Struktur (Multidomain/Multilanguage):**

Das Auto-Mapping funktioniert mit **Partial String Matching** - Suffixes sind erlaubt:

✅ **Funktioniert perfekt:**
```
google-analytics        → Erkannt als Google Analytics
google-analytics-shop   → Erkannt als Google Analytics  
google-analytics-de     → Erkannt als Google Analytics
facebook-pixel-checkout → Erkannt als Facebook Pixel
matomo-staging          → Erkannt als Matomo
youtube-embeds          → Erkannt als YouTube
```

❌ **Funktioniert NICHT:**
```
shop-google-analytics   → NICHT erkannt (Prefix stört)
custom-analytics        → NICHT erkannt (fehlt "google-analytics")
```

**💡 Empfehlung für Multidomain/Multilanguage:**
- `google-analytics-de`, `google-analytics-shop`, `google-analytics-en`
- `facebook-pixel-landing`, `facebook-pixel-checkout`  
- `matomo-domain1`, `matomo-domain2`
- `youtube-videos-de`, `youtube-videos-en`

**Beispiel Service-Anlage:**
1. **Dienste** → **Service hinzufügen**
2. **Schlüssel:** `google-tag-manager` ⭐
3. **Dienstname:** `Google Tag Manager`
4. **Scripts:** Dein GTM-Code
5. **Gruppe zuweisen:** z.B. "Marketing"

➡️ System erkennt automatisch "google-tag-manager" und mappt zu `analytics_storage`, `ad_storage`, etc.

**Funktionsweise:**
1. System generiert automatisch `gtag('consent', 'default', {...})` mit GDPR-konformen Defaults (alle 'denied')
2. Bei Consent-Änderungen wird automatisch `gtag('consent', 'update', {...})` aufgerufen
3. Services werden basierend auf UID/Namen automatisch erkannt und gemappt

#### ⚙️ **Manuell (Experten)**

**Eigene gtag-Integration in Service-Scripts:**
```javascript
<script>
// Google Consent Mode wird initialisiert, aber Service-Scripts müssen 
// gtag('consent', 'update') selbst implementieren
gtag('consent', 'update', {
    'analytics_storage': 'granted'
});
</script>
```

#### 🛠️ **Technische Details**

**GDPR-konforme Standard-Einstellungen:**
- `analytics_storage: denied`
- `ad_storage: denied` 
- `ad_user_data: denied`
- `ad_personalization: denied`
- `functionality_storage: denied`
- `security_storage: denied`
- `personalization_storage: denied`

**Debug-Konsole zeigt:**
- **🎯 Google Consent Mode v2 Status**: Aktueller Modus (Deaktiviert/Automatisch/Manuell)
- **Consent-Status**: Alle gtag-Flags mit aktuellen Werten
- **Service-Übersicht**: Erkannte Services und deren Zuordnung
- **Cookie-Analyse**: Detaillierte Cookie-Informationen
- **localStorage**: Consent-Daten-Speicherung

**Aktivierung Debug-Konsole:**
```
?debug_consent=1
```

**Vorteile automatischer Modus:**
- ✅ Keine Programmierung erforderlich
- ✅ Automatische Service-Erkennung
- ✅ GDPR-konforme Defaults
- ✅ Wartungsfreie Updates
- ✅ Debug-Konsole für Troubleshooting

### Beispiel-Modul für nachträgliche Abfrage

**Eingabe-Modul mit MForm:**
```php
<?php
$mform = new mform();
$cookies = [];
$qry = 'SELECT uid,service_name FROM '.rex::getTable('consent_manager_cookie').' WHERE clang_id = '.rex_clang::getCurrentId();
foreach (rex_sql::factory()->getArray($qry) as $v) {
    if ($v['uid'] == 'consent_manager') continue;
    $cookies[$v['uid']] = $v['service_name'];
}
$mform->addSelectField(1);
$mform->setOptions($cookies);
$mform->setLabel('Dienst');
echo $mform->show();
?>
```

### Cookie-Historie anzeigen

**Template-Integration:**
```php
REX_COOKIEDB[]
```

Zeigt alle gesetzten Cookies und die Einwilligungshistorie an.

---

## 🔍 Debugging und Problemlösung

### Debug-Modus aktivieren

**Aktivierung:**
- **Domain-Konfiguration**: In **Domains** → Debug-Modus auf "Aktiviert" setzen
- **REDAXO Debug-Modus**: `rex::isDebugMode()` (automatisch aktiv)
- **Backend-Login erforderlich**: Nur für angemeldete Backend-Benutzer

**Debug-Konsole zeigt:**
- Consent-Status und Default-Werte
- Service-Konfiguration
- Cookie-Details
- Browser-Storage-Inhalte
- Google Consent Mode Status

### Häufige Probleme

**Consent-Box wird nicht angezeigt:**
- Domain korrekt hinterlegt und in Cookie-Gruppe zugeordnet?
- Domain-Übereinstimmung prüfen (`www.` vs. ohne)
- Template-Platzhalter im `<head>`-Bereich?
- Eigenes CSS aktiviert aber nicht eingebunden?

**Cookies angezeigt, Scripts nicht geladen:**
- Scripts in entsprechendem Service hinterlegt?
- `<script>`-Tags vollständig vorhanden?
- Consent tatsächlich erteilt?

**Seite ohne Consent-Box:**
Token in Einstellungen definieren und URL-Parameter verwenden:
```
https://beispiel.de/seite.html?skip_consent=MEINTOKEN
```

### Berechtigungen für Redakteure

**Vollzugriff für Redakteure:**
- Recht `consent_manager[]` zuweisen
- Zusätzlich `consent_manager[editor]` zuweisen
- **Zugriff auf alle Funktionen** (Domains, Services, Gruppen, Texte, Konfiguration)

**Nur Text-Bearbeitung erlauben:**
- Recht `consent_manager[]` zuweisen
- Zusätzlich `consent_manager[texteditonly]` zuweisen
- **Nur Zugriff auf Texte** (eingeschränkter Modus)

**Administratoren:**
- Haben immer vollen Zugriff auf alle Funktionen

---

## 🌍 Mehrsprachigkeit

**Automatische Übertragung:**
- Inhalte der Startsprache werden automatisch in neue Sprachen übertragen
- Nachträgliche Anpassung möglich

**Sprachspezifische Felder:**
- Texte und Beschreibungen individuell anpassbar
- Schlüssel, Scripts und Domains nur in Startsprache änderbar

---

## 📊 API und JavaScript-Events

### Event-Listener

```javascript
// Reagiert auf Consent-Änderungen
$(document).on('consent_manager-saved', function(e) {
    var consents = JSON.parse(e.originalEvent.detail);
    // Verarbeitung der Consent-Daten
});
```

### PHP-Utility-Funktionen

```php
// Consent-Status prüfen
consent_manager_util::has_consent('service_key');

// Frontend-Instanz erstellen
$consent_manager = new consent_manager_frontend();
$consent_manager->setDomain($_SERVER['HTTP_HOST']);
```

---

## 📝 Setup und Import

### Quickstart-Assistent für neue Nutzer

Beim ersten Aufruf der Konfiguration wird ein **7-stufiger Quickstart-Assistent** angezeigt:

1. **Willkommen** - Übersicht über das Setup
2. **Domain konfigurieren** - Website-Domain hinterlegen
3. **Services wählen** - Zwischen Minimal- und Standard-Setup entscheiden
4. **Gruppen** - Cookie-Gruppen verwalten
5. **Texte** - Frontend-Texte anpassen
6. **Design** - Theme auswählen
7. **Testen** - Frontend-Integration testen

**Mehrsprachig:** Der Assistent ist vollständig in Deutsch und Englisch verfügbar.

### JSON-basiertes Setup-System

Das neue **JSON-basierte Setup-System** ersetzt das alte SQL-Format und bietet:

**Vorteile:**
- ✅ Bessere Versionskontrolle und Nachvollziehbarkeit
- ✅ Einfacher Export/Import von Konfigurationen
- ✅ Strukturierte Datenhaltung
- ✅ Flexible Anpassung an individuelle Bedürfnisse

### Setup-Varianten

**Minimal-Setup:** Grundkonfiguration mit nur essentiellem Service
- Domain-Gruppe "Technisch notwendig"
- Basic Consent Manager Service
- Ideal für datenschutz-minimale Websites

**Standard-Setup:** Vollständige Service-Sammlung (⚠️ **Überschreibt vorhandene Daten!**)
- Alle gängigen Services vorkonfiguriert
- Strukturierte Cookie-Gruppen
- Ready-to-use für die meisten Websites

### Konfiguration exportieren/importieren

**Export der aktuellen Konfiguration:**
```
Configuration → Export aktuelle Konfiguration → JSON-Datei herunterladen
```

**Import einer JSON-Konfiguration:**
```
Configuration → JSON-Datei hochladen → Import bestätigen
```

⚠️ **Wichtiger Hinweis:** Import überschreibt **alle** bestehenden Domains, Cookie-Gruppen und Services!

### Vorgefertigte Dienste

Das Standard-Setup enthält eine umfangreiche Sammlung mit **25 vorkonfigurierten Services** für moderne Websites, darunter gängige Dienste wie beispielsweise Google Analytics, Facebook Pixel, YouTube, Google Maps, Matomo, HubSpot, WhatsApp Business, LinkedIn, TikTok, Pinterest, Booking.com und viele weitere.

Die Services sind bereits strukturiert in Kategorien wie Analytics, Marketing, externe Medien, Kommunikation und technisch notwendige Dienste organisiert.

⚠️ **Wichtiger Hinweis:** Die Beispielkonfigurationen sind Vorlagen und müssen an die individuellen Anforderungen angepasst werden:
- **API-Keys ersetzen:** Alle Platzhalter müssen durch echte IDs/Keys ersetzt werden
- **Rechtliche Prüfung:** Cookie-Beschreibungen und Datenschutzlinks sollten von Datenschutzbeauftragten oder der Rechtsabteilung geprüft werden
- **Aktualität:** Dienste-Definitionen entsprechen dem aktuellen Stand der Datenschutzbestimmungen

---

## 🛠️ Erweiterte Integration

### Conditional Loading mit PHP

```php
<?php
$arr = json_decode($_COOKIE['consent_manager'], true);
$consents = $arr ? array_flip($arr['consents']) : [];

if (isset($consents['google-maps'])) {
    // Google Maps laden
    echo '<div id="google-maps"></div>';
} else {
    // Platzhalter mit Consent-Link anzeigen
    echo '<div>Für Google Maps müssen Cookies akzeptiert werden.</div>';
    echo '<a class="consent_manager-show-box-reload">Cookie-Einstellungen</a>';
}
?>
```

### AJAX-Integration

```javascript
// Prüfung vor AJAX-Request
function loadExternalContent() {
    if (consent_manager_hasconsent('external-api')) {
        $.ajax({
            url: '/api/external-data',
            success: function(data) {
                // Daten verarbeiten
            }
        });
    }
}
```

---

## � Debug-Modus

**Consent-Debug-Panel:** Seit Version 4.4.0 verfügbar für Entwickler und Troubleshooting.

**Aktivierung:**
```
?debug_consent=1
```

**Features:**
- Live-Anzeige aller Cookie-Stati
- Google Consent Mode Integration
- LocalStorage-Übersicht
- Service-Status-Monitor

---

## �📄 Lizenz und Credits

### Lizenz
MIT Lizenz - siehe [LICENSE.md](https://github.com/FriendsOfREDAXO/consent_manager/blob/master/LICENSE.md)

### Entwicklung
**Friends Of REDAXO:** [https://github.com/FriendsOfREDAXO](https://github.com/FriendsOfREDAXO)

**Projekt-Leads:**
- [Ingo Winter](https://github.com/IngoWinter)
- [Andreas Eberhard](https://github.com/aeberhard)

### Credits
**Contributors:** [Siehe GitHub](https://github.com/FriendsOfREDAXO/consent_manager/graphs/contributors)

**Danksagungen:**
- [Thomas Blum](https://github.com/tbaddade/) (Code aus Sprog AddOn)
- [Thomas Skerbis](https://github.com/skerbis) (Testing und Spende)
- [Peter Bickel](https://github.com/polarpixel) (Entwicklungsspende)
- [Oliver Kreischer](https://github.com/olien) (Cookie-Design)

**Externe Bibliotheken:**
- [cookie.js](https://github.com/js-cookie/js-cookie) - MIT Lizenz

---

## 🆘 Support und Community

**Issue melden:** [GitHub Issues](https://github.com/FriendsOfREDAXO/consent_manager/issues)

**Contributions:** Pull Requests sind willkommen - besonders eigene Themes mit Screenshot oder Demo-Link!

**Community:** Tipps und Tricks können direkt in die README eingefügt oder als Issue eingereicht werden.