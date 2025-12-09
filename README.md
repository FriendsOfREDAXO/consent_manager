# Consent-Manager für REDAXO CMS

![logo](https://github.com/FriendsOfREDAXO/consent_manager/blob/assets/consent_manager-logo.jpg?raw=true)

## Was ist der Consent-Manager?

Das AddOn stellt eine DSGVO-konforme Lösung für die Einholung von Einverständniserklärungen zu Cookies und externen Diensten bereit. Website-Besucher erhalten eine Consent-Box, in der einzelne Dienste-Gruppen akzeptiert oder abgelehnt werden können. Technisch notwendige Dienste bleiben dabei immer aktiv.

**Kernfunktionen:**
- Datenschutz-Opt-In-Banner für Dienste und Cookies
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

**Wichtig:** Assets müssen im Template eingebunden werden, damit der Consent Manager und die Inline-Blocker funktionieren!

#### 🔧 Standard Integration (Consent Manager Box)

**PHP-Aufruf (empfohlen):**
```php
<?php 
use FriendsOfRedaxo\ConsentManager\Frontend;

// Standard-Integration (alles in einem)
echo Frontend::getFragment(0, 0, 'ConsentManager/box_cssjs.php'); 

// Oder Komponenten einzeln laden (mehr Flexibilität):
?>
<style><?php echo Frontend::getCSS(); ?></style>
<script<?php echo Frontend::getNonceAttribute(); ?>>
    <?php echo Frontend::getJS(); ?>
</script>
<?php echo Frontend::getBox(); ?>

<?php
// Mit custom Fragment
echo Frontend::getFragment(0, 0, 'my_custom_box.php');

// Mit Inline-Modus
echo Frontend::getFragmentWithVars(0, 0, 'ConsentManager/box_cssjs.php', ['inline' => true]);
?>
```

#### 🎯 Inline-Consent Assets (CKE5 oEmbed & manuelle Blocker)

**Für Inline-Blocker (YouTube, Vimeo, Google Maps, etc.) müssen zusätzliche Assets geladen werden:**

```php
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title><?= rex_article::getCurrent()->getName() ?></title>
    
    <!-- Consent Manager Inline CSS (für Blocker-Platzhalter) -->
    <link rel="stylesheet" href="<?= rex_url::addonAssets('consent_manager', 'consent_inline.css') ?>">
    
    <!-- Optional: Vidstack CSS (nur wenn Vidstack installiert) -->
    <?php if (rex_addon::exists('vidstack') && rex_addon::get('vidstack')->isAvailable()): ?>
        <link rel="stylesheet" href="<?= rex_url::addonAssets('vidstack', 'vidstack.css') ?>">
    <?php endif; ?>
</head>
<body>
    <?php
    // Hauptinhalt mit automatischer oEmbed-Umwandlung
    echo rex_article::getCurrent()->getArticle();
    ?>
    
    <!-- Consent Manager Inline JavaScript (WICHTIG für Button-Funktionalität!) -->
    <script defer src="<?= rex_url::addonAssets('consent_manager', 'consent_inline.js') ?>"></script>
    
    <!-- Optional: Vidstack JavaScript (nur wenn Vidstack installiert) -->
    <?php if (rex_addon::exists('vidstack') && rex_addon::get('vidstack')->isAvailable()): ?>
        <script defer src="<?= rex_url::addonAssets('vidstack', 'vidstack.js') ?>"></script>
        <script defer src="<?= rex_url::addonAssets('vidstack', 'vidstack_helper.js') ?>"></script>
    <?php endif; ?>
</body>
</html>
```

**Was wird benötigt:**

| Asset | Wofür? | Zwingend? |
|-------|--------|-----------|
| `consent_inline.css` | Styling der Inline-Blocker (Thumbnails, Buttons, Overlay) | ✅ Ja |
| `consent_inline.js` | Button-Funktionalität ("Einmal laden", "Einstellungen") | ✅ Ja |
| `vidstack.css` | Styling für Vidstack Player (nur bei `player_mode: vidstack`) | Optional |
| `vidstack.js` | Vidstack Player Funktionalität | Optional |

**⚠️ Ohne diese Assets:**
- Inline-Blocker werden **nicht korrekt dargestellt**
- Buttons **funktionieren nicht** ("Einmal laden" macht nichts)
- Videos **laden nicht** nach Consent-Klick
- CKE5 oEmbed-Tags werden **nicht umgewandelt**

#### 📋 Komplett-Beispiel Template

```php
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= rex_article::getCurrent()->getName() ?></title>
    
    <!-- Consent Manager Inline CSS -->
    <link rel="stylesheet" href="<?= rex_url::addonAssets('consent_manager', 'consent_inline.css') ?>">
    
    <!-- Optional: Vidstack CSS -->
    <?php if (rex_addon::exists('vidstack') && rex_addon::get('vidstack')->isAvailable()): ?>
        <link rel="stylesheet" href="<?= rex_url::addonAssets('vidstack', 'vidstack.css') ?>">
    <?php endif; ?>
    
    <!-- Dein eigenes CSS -->
    <link rel="stylesheet" href="<?= rex_url::base('assets/css/styles.css') ?>">
</head>
<body>
    <?php
    // Consent Manager Box (globales Cookie-Banner)
    echo FriendsOfRedaxo\ConsentManager\Frontend::getFragment(0, 0, 'ConsentManager/box_cssjs.php');
    
    // Hauptinhalt mit automatischer oEmbed-Umwandlung
    echo rex_article::getCurrent()->getArticle();
    ?>
    
    <!-- Consent Manager Inline JavaScript (für Blocker-Buttons) -->
    <script defer src="<?= rex_url::addonAssets('consent_manager', 'consent_inline.js') ?>"></script>
    
    <!-- Optional: Vidstack JavaScript -->
    <?php if (rex_addon::exists('vidstack') && rex_addon::get('vidstack')->isAvailable()): ?>
        <script defer src="<?= rex_url::addonAssets('vidstack', 'vidstack.js') ?>"></script>
        <script defer src="<?= rex_url::addonAssets('vidstack', 'vidstack_helper.js') ?>"></script>
    <?php endif; ?>
    
    <!-- Dein eigenes JavaScript -->
    <script defer src="<?= rex_url::base('assets/js/scripts.js') ?>"></script>
</body>
</html>
```

**Verfügbare Parameter:**

| Parameter | Standard | Beschreibung |
|-----------|----------|--------------|
| `forceCache` | `0` im Frontend, `1` im Backend | Cache-Steuerung: `0` = Cache verwenden, `1` = Cache neu generieren |
| `forceReload` | `0` wenn `cache` Parameter gesetzt, sonst `1` | Reload-Steuerung der Consent-Box |
| `fragment` | `ConsentManager/box_cssjs.php` | Custom Fragment-Template-Datei |
| `vars` (Array) | `[]` | Zusätzliche Variablen (z.B. `['inline' => true]`) |

**REX_CONSENT_MANAGER Variable (alternativ):**
```php
REX_CONSENT_MANAGER[]
REX_CONSENT_MANAGER[forceCache=0 forceReload=0]
REX_CONSENT_MANAGER[inline=true]
REX_CONSENT_MANAGER[fragment=my_custom_box.php]
```

**Platzierung:**
- Im `<head>`-Bereich oder vor `</body>` (empfohlen für Performance)
- Für Inline-Consent-Modus: `['inline' => true]` als Parameter übergeben

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
if (FriendsOfRedaxo\ConsentManager\Utility::has_consent('youtube')) {
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

### Cookie-Einstellungen (SameSite & Secure)

**Konfigurierbare Cookie-Sicherheit** (seit Version 4.5.0):

Der Consent Manager unterstützt konfigurierbare Cookie-Einstellungen für maximale Sicherheit:

**Standardwerte:**
```yaml
cookie_samesite: 'Lax'    # Standard für gute Kompatibilität
cookie_secure: false      # false für HTTP-Seiten
```

**Empfohlene Werte für HTTPS-Seiten:**
```yaml
cookie_samesite: 'Strict' # Maximale Sicherheit
cookie_secure: true       # Nur über HTTPS übertragen
```

**SameSite Optionen:**
- `Strict`: Cookies werden nur bei direktem Besuch der Domain gesendet (höchste Sicherheit)
- `Lax`: Cookies werden auch bei Top-Level-Navigation gesendet (Standard, guter Kompromiss)
- `None`: Cookies werden immer gesendet (⚠️ erfordert `secure: true`)

**Secure Flag:**
- `true`: Cookie wird nur über HTTPS übertragen (empfohlen für Produktiv-Sites)
- `false`: Cookie wird auch über HTTP übertragen (nur für Entwicklung)

**Konfiguration in `package.yml`:**
```yaml
cookie_samesite: 'Strict'
cookie_secure: true
```

**⚠️ Wichtig für Subdomains:**
Seit Version 4.5.0 werden **keine Wildcard-Cookies** mehr gesetzt. Jede (Sub-)Domain erhält ihren eigenen Consent-Cookie. Dies ist DSGVO-konform, bedeutet aber:
- `example.com` und `shop.example.com` sind separate Domains
- Consent muss für jede Domain einzeln eingeholt werden
- Cookie gilt nur für die exakte Domain, nicht für Subdomains

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

**Verfügbare Themes:**
- Standard-Themes (Hell, Dunkel, Bottom Bar, Bottom Right)
- Community-Themes (Olien Dark/Light, Skerbis Glass, XOrange)
- **🆕 Accessibility Theme** (`consent_manager_frontend_a11y.css`) - Barrierefrei optimiert

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

### ♿ Barrierefreiheit (Accessibility)

**Issues #326, #304 - Optimierungen für Barrierefreiheit:**

Das neue **A11y-Theme** (`consent_manager_frontend_a11y.scss`) bietet umfassende Barrierefreiheit:

**WCAG 2.1 AA Konformität:**
- ✅ **Kontrastverhältnisse:** 4.5:1 für Text, 3:1 für UI-Komponenten
- ✅ **Focus-Indikatoren:** 3px blaue Umrandung für alle interaktiven Elemente
- ✅ **Touch-Targets:** Mindestens 44x44px für alle Buttons und Links
- ✅ **Screen Reader:** Korrekte ARIA-Attribute (Issue #304)
  - `role="dialog"` nur auf consent_manager-wrapper
  - `aria-modal="true"` für modalen Dialog
  - `aria-labelledby` verknüpft mit Überschrift
  - `aria-hidden` dynamisch bei Öffnen/Schließen
- ✅ **Tastatursteuerung:** Vollständige Navigation ohne Maus möglich
- ✅ **Focus Trap:** Tab-Navigation bleibt innerhalb des Modals
- ✅ **DSGVO-konform:** Alle 3 Buttons (ablehnen/auswählen/alle) visuell gleichwertig

**Modales Verhalten (Issue #326):**
- **Auto-Focus:** Beim Öffnen wird automatisch der erste Button fokussiert
- **Focus Trap:** Tab/Shift+Tab bleiben innerhalb des Consent-Dialogs
- **ESC funktioniert immer:** Schließt Dialog von jedem Element aus
- **Tastatur-Zugänglichkeit:** Kein Entkommen nur mit Maus nötig
- **ARIA-Management:** Hintergrund-Container ist für Screen Reader unsichtbar (Issue #304)

**Tastatursteuerung:**
```
ESC             → Consent Box schließen (von überall im Dialog)
Tab             → Vorwärts zwischen Elementen navigieren (bleibt im Dialog)
Shift+Tab       → Rückwärts zwischen Elementen navigieren (bleibt im Dialog)
Enter / Space   → Details ein-/ausklappen
Enter           → Buttons aktivieren
```

**Theme-Varianten:**
1. **Accessibility (WCAG 2.1 AA)** - Neutrales Grau, DSGVO-konforme Buttons
2. **Accessibility Blue** - Blauer Akzent (#0066cc)
3. **Accessibility Green** - Grüner Akzent (#025335)
4. **Accessibility Compact** - Platzsparende Version, Grau
5. **Accessibility Compact Blue** - Platzsparend mit blauem Akzent
Enter / Space   → Details ein-/ausklappen (Issue #326)
Enter           → Buttons aktivieren
```

**Implementierte Features:**
- **ESC-Taste:** Schließt die Consent Box ohne durch alle Felder zu tabben
- **Space-Taste:** Aktiviert den "Details anzeigen"-Button (zusätzlich zu Enter)
- **aria-expanded:** Zeigt Screen Readern den Zustand des Details-Bereichs
- **Reduzierte Bewegung:** Respektiert `prefers-reduced-motion` Einstellung
- **Hoher Kontrast:** Unterstützt `prefers-contrast: high` Modus
- **Focus-Management:** Automatischer Focus auf erste Checkbox beim Öffnen

**Verwendung:**
```php
<!-- Im Backend: Theme auf "consent_manager_frontend_a11y.css" setzen -->
<!-- Oder manuell: -->
<link rel="stylesheet" href="/assets/addons/consent_manager/consent_manager_frontend_a11y.css">
```

**Zusätzliche Empfehlungen:**
- Platzieren Sie den Cookie-Einstellungs-Link prominent im Footer
- Verwenden Sie beschreibende Linktexte (z.B. "Cookie-Einstellungen" statt "Klick hier")
- Testen Sie mit Screen Readern (NVDA, JAWS, VoiceOver)
- Prüfen Sie Keyboard-Navigation regelmäßig

### Individuelles Design

**Fragment anpassen:**
- Standard: `/redaxo/src/addons/consent_manager/fragments/ConsentManager/box.php`
- Eigenes Fragment: `..../theme/private/fragments/ConsentManager/box.php`
- Eigenes Fragment: `..../project/fragments/ConsentManager/box.php`

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
    // Skip system entries (not the cookie name itself)
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

**CSP (Content Security Policy) Kompatibilität:**
✅ **Gelöst ab Version 4.5.0:** Das Consent-Manager AddOn ist jetzt CSP-kompatibel!

**Implementierte Lösung:**
- **Automatische Nonce-Übergabe**: Nonce wird automatisch von `rex_response::getNonce()` geholt
- **Keine manuelle Konfiguration nötig**: Funktioniert out-of-the-box
- **Kein innerHTML mehr**: Scripts werden via `document.createElement()` und `textContent` eingefügt
- **CSP-freundlich**: Kompatibel mit `script-src 'nonce-XXX'` Policies

**CSP-Header Beispiel:**
```
Content-Security-Policy: script-src 'self' 'nonce-ZUFÄLLIGER_NONCE';
```

**Einfache Verwendung im Template:**
```php
<?php
// Einfach aufrufen - Nonce wird automatisch verwendet!
echo FriendsOfRedaxo\ConsentManager\Frontend::getFragment(0, 0, 'ConsentManager/box_cssjs.php');
?>
```

**Manuelle CSP-Header setzen (optional):**
```php
<?php
// Nur wenn du eigene CSP-Header setzen möchtest
$nonce = rex_response::getNonce();
header("Content-Security-Policy: script-src 'self' 'nonce-{$nonce}'");

echo FriendsOfRedaxo\ConsentManager\Frontend::getFragment(0, 0, 'ConsentManager/box_cssjs.php');
?>
```

**Externe Scripts bevorzugen:**
Für maximale CSP-Kompatibilität externe Scripts mit `src` Attribut verwenden:
```javascript
// ✅ Empfohlen: Externe Script-Dateien
<script src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>

// ⚠️ Weniger CSP-freundlich: Inline-Scripts
<script>
  gtag('config', 'GA_MEASUREMENT_ID');
</script>
```

**Hinweis zu Issue #320:**
Das ursprünglich gemeldete Problem mit dynamischen Script-Injektionen ist behoben. Die Lösung verwendet:
- `createElement()` statt `innerHTML`
- `textContent` statt `innerHTML` für Script-Content
- Automatische Nonce-Propagierung
- Anhängen an `document.body` statt versteckte Container

**Subdomain-Probleme und DSGVO-Konformität:**
✅ **Gelöst ab Version 4.5.0:** Subdomain-spezifische Consent-Verwaltung (Issue #317)

**Problem:**
- Alte Versionen verwendeten Wildcard-Cookies (`.example.com`)
- Consent von `example.com` galt fälschlicherweise auch für `shop.example.com`
- **DSGVO-Verstoß**: Consent muss domain-spezifisch sein!

**Lösung:**
- **Domain-spezifische Cookies**: Jede (Sub-)Domain erhält eigenen Consent
- **Keine Wildcard-Domains**: Cookie gilt nur für exakte Domain
- **Korrekte Subdomain-Erkennung**: `shop.example.com` wird als vollständige Domain behandelt

**Empfehlung für HTTPS-Seiten:**
```php
// In package.yml oder Einstellungen:
cookie_samesite: 'Strict'  # Empfohlen für HTTPS
cookie_secure: true        # Nur über HTTPS übertragen
```

**Wichtig für Multi-Domain-Setups:**
- Jede Domain benötigt eigene Consent-Manager Konfiguration
- `example.com` und `shop.example.com` sind DSGVO-rechtlich separate Websites
- Consent muss für jede Domain einzeln eingeholt werden

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

Der Consent Manager triggert mehrere JavaScript-Events für Custom-Integrationen:

**Verf\u00fcgbare Events:**
- `consent_manager-show` - Box wurde ge\u00f6ffnet
- `consent_manager-close` - Box wurde geschlossen
- `consent_manager-saved` - Consent wurde gespeichert (enth\u00e4lt Consent-Daten)

**JavaScript Event-Listener:**
```javascript
// Box wurde ge\u00f6ffnet
document.addEventListener('consent_manager-show', function() {
    console.log('Consent Box ge\u00f6ffnet');
    // Custom Analytics-Tracking
    // Custom Overlays/Animations
});

// Box wurde geschlossen
document.addEventListener('consent_manager-close', function() {
    console.log('Consent Box geschlossen');
    // Cleanup-Aktionen
});

// Consent wurde gespeichert
document.addEventListener('consent_manager-saved', function(e) {
    var consents = JSON.parse(e.detail);
    console.log('Gespeicherte Consents:', consents);
    // Verarbeitung der Consent-Daten
    // z.B. Analytics, Tag Manager Updates
});
```

**Praktische Beispiele:**

```javascript
// A/B-Testing: Tracking welche Nutzer Consent-Box sehen
document.addEventListener('consent_manager-show', function() {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'consent_box_shown');
    }
});

// Custom Animation beim Schließen
document.addEventListener('consent_manager-close', function() {
    document.body.classList.add('consent-box-closed');
});

// Conditional Script Loading basierend auf Consent
document.addEventListener('consent_manager-saved', function(e) {
    var consents = JSON.parse(e.detail);
    
    if (consents.includes('google-analytics')) {
        // Google Analytics wurde akzeptiert
        console.log('Analytics aktiviert');
    }
    
    if (consents.includes('marketing')) {
        // Marketing-Cookies wurden akzeptiert
        console.log('Marketing aktiviert');
    }
});
```

### PHP-Utility-Funktionen

```php
// Consent-Status prüfen
FriendsOfRedaxo\ConsentManager\Utility::has_consent('service_key');

// Frontend-Instanz erstellen
$consent_manager = new FriendsOfRedaxo\ConsentManager\Frontend();
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

### API-Methoden (Issue #282)

Seit Version 5.x stehen separate Methoden für CSS, JavaScript und Box-HTML zur Verfügung:

**`Frontend::getCSS()`**
```php
<?php
// Gibt nur das CSS zurück
$css = FriendsOfRedaxo\ConsentManager\Frontend::getCSS();
echo '<style>' . $css . '</style>';
?>
```
- **Return:** CSS-String mit Theme-Unterstützung
- **Use Case:** Inline-CSS oder separate CSS-Datei generieren
- **Performance:** Cached durch REDAXO

**`Frontend::getJS()`**
```php
<?php
// Gibt JavaScript inkl. Parameter und Box-Template zurück
$js = FriendsOfRedaxo\ConsentManager\Frontend::getJS();
?>
<script<?php echo FriendsOfRedaxo\ConsentManager\Frontend::getNonceAttribute(); ?>>
    <?php echo $js; ?>
</script>
```
- **Return:** Vollständiges JavaScript (js.cookie, polyfills, consent_manager_frontend.js)
- **Enthält:** Parameter, Box-Template, Cookie-Expiration
- **CSP:** Nonce-Attribut automatisch über `getNonceAttribute()` verfügbar
- **Use Case:** Inline-JavaScript oder separate JS-Datei

**`Frontend::getBox()`**
```php
<?php
// Gibt nur das Box-HTML zurück (ohne CSS/JS)
echo FriendsOfRedaxo\ConsentManager\Frontend::getBox();
?>
```
- **Return:** HTML der Consent-Box
- **Use Case:** AJAX-Loading, Custom Integration, SPA-Frameworks
- **Voraussetzung:** CSS und JS müssen separat geladen sein

**`Frontend::getNonceAttribute()`**
```php
<?php
// CSP-Nonce-Attribut für Script-Tags
?>
<script<?php echo FriendsOfRedaxo\ConsentManager\Frontend::getNonceAttribute(); ?>>
    // Ihr JavaScript-Code
</script>
```
- **Return:** ` nonce="XXX"` oder leerer String
- **CSP:** Automatische Integration mit REDAXO's CSP-Nonce
- **Use Case:** Inline-Scripts mit Content Security Policy

**Anwendungsbeispiele:**

```php
<?php
use FriendsOfRedaxo\ConsentManager\Frontend;

// Beispiel 1: Alles inline im Template
?>
<style><?php echo Frontend::getCSS(); ?></style>
<?php echo Frontend::getBox(); ?>
<script<?php echo Frontend::getNonceAttribute(); ?>>
    <?php echo Frontend::getJS(); ?>
</script>

<?php
// Beispiel 2: JavaScript in separate Datei schreiben
$jsFile = rex_path::assets('consent_manager_custom.js');
rex_file::put($jsFile, Frontend::getJS());
?>
<script src="<?php echo rex_url::assets('consent_manager_custom.js'); ?>"<?php echo Frontend::getNonceAttribute(); ?>></script>

<?php
// Beispiel 3: Für AJAX/SPA nur Box-HTML zurückgeben
if (rex_request::isAjaxRequest()) {
    rex_response::sendJson([
        'html' => Frontend::getBox(),
        'css' => Frontend::getCSS()
    ]);
    exit;
}
?>
```

### Conditional Loading mit PHP

```php
<?php
$arr = json_decode($_COOKIE['consentmanager'], true);
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

## 🎯 Inline-Consent für einzelne Medien

**Neu:** Consent nur bei Bedarf - perfekt für Seiten mit wenigen externen Inhalten.

### 🚀 Schnellstart Inline-Consent

**Problem:** Sie haben 400 Artikel, aber nur 2 brauchen YouTube-Videos. Normale Consent-Banner nerven alle Besucher, obwohl 99% nie Videos sehen.

**Lösung:** Inline-Consent zeigt Platzhalter statt Videos. Consent-Dialog erscheint erst beim Klick auf "Video laden".

### 📋 Voraussetzungen

**Template-Integration ZWINGEND erforderlich:**

Die folgenden Assets **müssen** im Template eingebunden sein, damit Inline-Blocker funktionieren:

```php
<!-- Im <head> -->
<link rel="stylesheet" href="<?= rex_url::addonAssets('consent_manager', 'consent_inline.css') ?>">

<!-- Vor </body> -->
<script defer src="<?= rex_url::addonAssets('consent_manager', 'consent_inline.js') ?>"></script>
```

**Optional für Vidstack Player (moderne Video-Wiedergabe):**

```php
<!-- Im <head> -->
<?php if (rex_addon::exists('vidstack') && rex_addon::get('vidstack')->isAvailable()): ?>
    <link rel="stylesheet" href="<?= rex_url::addonAssets('vidstack', 'vidstack.css') ?>">
<?php endif; ?>

<!-- Vor </body> -->
<?php if (rex_addon::exists('vidstack') && rex_addon::get('vidstack')->isAvailable()): ?>
    <script defer src="<?= rex_url::addonAssets('vidstack', 'vidstack.js') ?>"></script>
    <script defer src="<?= rex_url::addonAssets('vidstack', 'vidstack_helper.js') ?>"></script>
<?php endif; ?>
```

**⚠️ Ohne diese Assets funktionieren:**
- ❌ **Buttons nicht** ("Einmal laden", "Einstellungen")
- ❌ **Videos laden nicht** nach Consent
- ❌ **CKE5 oEmbed nicht** automatisch umgewandelt
- ❌ **Styling fehlt** (keine Thumbnails, kein Overlay)

### 🎬 CKE5 oEmbed Integration (automatisch!)

**Automatische Umwandlung von oEmbed-Tags:**

```html
<!-- Redakteur fügt in CKE5 ein: -->
<oembed url="https://www.youtube.com/watch?v=dQw4w9WgXcQ"></oembed>

<!-- Frontend zeigt automatisch: -->
<!-- ┌─────────────────────────────────┐ -->
<!-- │ 📺 Video-Thumbnail               │ -->
<!-- │ YouTube Video                    │ -->
<!-- │ Für Anzeige werden Cookies...   │ -->
<!-- │ [Einmal laden] [Einstellungen]  │ -->
<!-- └─────────────────────────────────┘ -->
```

**Unterstützte Plattformen:**
- ✅ YouTube (alle URL-Formate)
- ✅ Vimeo (alle URL-Formate)

**Services konfigurieren:**

Backend → Consent Manager → Dienste:

1. **YouTube Service:**
   - UID: `youtube`
   - Service-Name: YouTube
   - Datenschutz: `https://policies.google.com/privacy`

2. **Vimeo Service:**
   - UID: `vimeo`
   - Service-Name: Vimeo
   - Datenschutz: `https://vimeo.com/privacy`

**Player-Modi (Backend → Domains):**

| Modus | Beschreibung |
|-------|-------------|
| `auto` | Vidstack wenn verfügbar, sonst native iframe (Standard) |
| `vidstack` | Immer Vidstack Player (moderne UI, Accessibility) |
| `native` | Immer Standard YouTube/Vimeo iframe |

**Vorteile Vidstack Player:**
- ✅ Moderne, anpassbare Player-UI
- ✅ Kleinere Dateigröße als YouTube iframe
- ✅ Vollständige Accessibility (WCAG 2.1)
- ✅ Touch-optimiert
- ✅ Konsistente UI über alle Plattformen

### ⚠️ Wichtige Funktionsweise des Inline-Modus

**Medienspezifischer Consent:**
- ✅ **Inline-Consent aktiviert NUR das angeklickte Medium**
- ✅ Jedes Video/Embed wird **einzeln** freigeschaltet
- ✅ **Keine globale Aktivierung** aller Services einer Gruppe
- ✅ Maximaler Datenschutz durch minimale Consent-Erteilung

**Beispiel:** Nutzer klickt "YouTube Video laden" → Nur dieses eine Video wird geladen, andere YouTube-Videos auf der Seite bleiben gesperrt.

**Globale Aktivierung über "Alle Einstellungen":**
- Der Button **"Alle Einstellungen"** (früher "Cookie-Details") öffnet das vollständige Consent Manager Fenster
- Dort kann der Nutzer **alle Services einer Gruppe** global aktivieren
- **Wichtig:** Button-Text ist über die **Texte-Verwaltung** vollständig anpassbar und übersetzbar

### YouTube-Videos mit Inline-Consent

```php
<?php
use FriendsOfRedaxo\ConsentManager\InlineConsent;

// Template/Modul - statt direktem iframe
echo InlineConsent::doConsent('youtube', 'dQw4w9WgXcQ', [
    'title' => 'Rick Astley - Never Gonna Give You Up',
    'width' => 560,
    'height' => 315
]);

// Funktioniert auch mit kompletten URLs
echo InlineConsent::doConsent('youtube', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', [
    'title' => 'Mein Video'
]);

// Mit custom Attributen (z.B. für UIkit, Bootstrap, etc.)
echo InlineConsent::doConsent('youtube', 'dQw4w9WgXcQ', [
    'title' => 'Responsive YouTube Video',
    'width' => 560,
    'height' => 315,
    'attributes' => [
        'class' => 'uk-responsive-width',
        'data-uk-video' => 'automute: true',
        'data-uk-responsive' => ''
    ]
]);
?>
```

**✅ Automatische Privacy Policy Links:**
- Services mit hinterlegter `provider_link_privacy` zeigen automatisch den entsprechenden Datenschutz-Link
- Format: "🔒 Datenschutzerklärung von [Anbieter]" (z.B. "🔒 Datenschutzerklärung von Google")
- Link öffnet in neuem Tab/Fenster

### Google Maps einbetten

```php
<?php
use FriendsOfRedaxo\ConsentManager\InlineConsent;

echo InlineConsent::doConsent('google-maps', 'https://www.google.com/maps/embed?pb=!1m18!1m12...', [
    'title' => 'Unsere Adresse',
    'height' => 450
]);
?>
```

### Vimeo-Videos

```php
<?php
use FriendsOfRedaxo\ConsentManager\InlineConsent;

echo InlineConsent::doConsent('vimeo', '123456789', [
    'title' => 'Mein Vimeo Video',
    'width' => 640,
    'height' => 360
]);

// Oder mit URL
echo InlineConsent::doConsent('vimeo', 'https://vimeo.com/123456789', [
    'title' => 'Corporate Video'
]);

// Mit custom CSS Klassen und data-Attributen
echo InlineConsent::doConsent('vimeo', '123456789', [
    'title' => 'Corporate Video',
    'attributes' => [
        'class' => 'responsive-video my-custom-class',
        'data-video-id' => '123456789',
        'data-analytics' => 'tracked'
    ]
]);
?>
```

### Custom iframes/Scripts

```php
<?php
use FriendsOfRedaxo\ConsentManager\InlineConsent;

// Beliebige iframes
echo InlineConsent::doConsent('custom-iframe', '<iframe src="https://example.com/widget"></iframe>', [
    'title' => 'External Widget',
    'privacy_notice' => 'Dieses Widget setzt Cookies für Funktionalität.'
]);

// JavaScript-Code
echo InlineConsent::doConsent('google-analytics', '<script>gtag("config", "GA_MEASUREMENT_ID");</script>', [
    'title' => 'Google Analytics',
    'placeholder_text' => 'Analytics aktivieren'
]);
?>
```

### Template-Integration

**CSS/JS einbinden (einmalig im Template):**
```php
<?= (new rex_fragment())->parse('ConsentManager/inline_cssjs.php') ?>
```

**Oder manuell:**
```html
<!-- Im <head>-Bereich -->
<?php
use FriendsOfRedaxo\ConsentManager\InlineConsent;
if (class_exists(InlineConsent::class)) {
    echo InlineConsent::getCSS();
    echo InlineConsent::getJavaScript();
}
?>
```

### Features der Inline-Lösung

**✅ Vollständige Integration:**
- Nutzt bestehende Service-Konfiguration
- Automatisches Logging aller Consent-Aktionen  
- **"Alle Einstellungen"-Button** öffnet vollständiges Consent Manager Fenster
- Bereits erteilte Consents werden respektiert
- DSGVO-konforme Dokumentation
- **Button-Texte anpassbar:** "Alle Einstellungen" kann über Texte-Verwaltung geändert werden (z.B. "Cookie-Einstellungen", "Datenschutz-Optionen", etc.)
- **Privacy Policy Links:** Automatische Anzeige von Datenschutzerklärungen der Service-Anbieter
- **Keine Confirm-Alerts:** Direkte Consent-Aktivierung ohne störende Browser-Dialoge

**✅ Smart Service Detection:**
- YouTube: Automatische Thumbnail-Generierung
- Vimeo: Professionelle Platzhalter
- Google Maps: Karten-Icon und Hinweise
- Generic: Universell für alle anderen Services

**✅ User Experience:**
- Responsive Design
- Smooth Animations
- Accessibility-konform
- Mobile-optimiert
- **Vollständig übersetzbare Buttons** über REDAXO Texte-Verwaltung

**✅ Mehrsprachigkeit:**
- Alle Button-Texte über **Consent Manager → Texte** anpassbar
- Automatische Sprachen-Synchronisation
- Individuelle Anpassung pro Sprache möglich
- Standard-Buttons: "Video laden", "Alle Einstellungen", "Datenschutz"

**✅ Developer Experience:**
- Ein `InlineConsent::doConsent()` für alle Services
- Auto-Erkennung von Video-IDs aus URLs
- Flexible Optionen-Arrays
- Debug-Modus verfügbar

### Beispiel-Output

Der Inline-Consent generiert ansprechende Platzhalter:

```html
<!-- YouTube-Platzhalter -->
<div class="consent-inline-placeholder">
    <img src="https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg" />
    <div class="consent-inline-overlay">
        <div class="consent-inline-icon">🎥</div>
        <h4>Rick Astley - Never Gonna Give You Up</h4>
        <p>Für YouTube werden Cookies benötigt.</p>
        <button onclick="...">YouTube Video laden</button>
        <button onclick="...">Cookie-Details</button>
    </div>
</div>
```

### Vorteile gegenüber globalem Consent

| Global Consent | Inline Consent |
|----------------|----------------|
| ❌ Nervt alle Besucher | ✅ Nur bei tatsächlicher Nutzung |
| ❌ "Consent Fatigue" | ✅ Kontextuell und verständlich |
| ❌ Viele leere Zustimmungen | ✅ Bewusste Entscheidungen |
| ❌ Komplexe Setup für 2 Videos | ✅ Einfache Integration |

**Perfect für:**
- Blogs mit gelegentlichen Videos
- Corporate Sites mit einzelnen Maps
- Landing Pages mit gezielten Embeds
- Alle Seiten wo < 10% der Inhalte Consent brauchen

## 🔍 Debug-Modus

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
- **Neu:** Inline-Consent-Tracking

---

## 📄 Lizenz und Credits

### Lizenz
MIT Lizenz - siehe [LICENSE.md](https://github.com/FriendsOfREDAXO/consent_manager/blob/master/LICENSE.md)

### Entwicklung
**Friends Of REDAXO:** [https://github.com/FriendsOfREDAXO](https://github.com/FriendsOfREDAXO)

**Projekt-Leads:**
- [Ingo Winter](https://github.com/IngoWinter)
- [Andreas Eberhard](https://github.com/aeberhard)
- [Thomas Skerbis](https://github.com/skerbis) 

### Credits
**Contributors:** [Siehe GitHub](https://github.com/FriendsOfREDAXO/consent_manager/graphs/contributors)

**Danksagungen:**
- [Christoph Böcker](https://github.com/Christophboecker) (Code refactoring, bug fixing und mehr) 
- [Thomas Blum](https://github.com/tbaddade/) (Code aus Sprog AddOn)
- [Peter Bickel](https://github.com/polarpixel) (Entwicklungsspende)
- [Oliver Kreischer](https://github.com/olien) (Cookie-Design)

**Externe Bibliotheken:**
- [cookie.js](https://github.com/js-cookie/js-cookie) - MIT Lizenz

---

## 📦 Repository & Release‑Packaging (Wichtig)

Wir haben die Repo‑Packaging Regeln aktualisiert, damit Entwickler‑/Test‑Artefakte nicht versehentlich in AddOn Release‑ZIPs landen.

- composer.phar wurde aus dem Repository entfernt. Bitte committe keine lokalen composer.phar binaries in dieses Repo.
- `.gitattributes` enthält jetzt mehrere export-ignore Regeln (z. B. `node_modules/`, `assets/tests/`, `tests/`, `vendor/*/tests/`, `.php-cs-fixer.*`, `composer.phar`), sodass `git archive` / GitHub‑Source‑Zips diese Dateien nicht enthalten.
- `package.yml` wurde erweitert (installer_ignore) — das REDAXO AddOn Packager/Installer ignoriert ebenfalls dev‑Artefakte beim Erstellen der AddOn‑ZIPs.

CI‑Schutz:
- Es gibt einen neuen Workflow `.github/workflows/packaging-verify.yml`, der bei PRs/Pushes prüft, dass generierte Git‑Archive keine verbotenen Dev‑Artefakte enthalten. Falls du lokale Tests/Debugging‑Binaries verwendest (z. B. composer.phar), halte diese bitte lokal und füge sie nicht zum Repo hinzu.

Wie du lokal ein Release‑ZIP prüfst:

```bash
# Erzeugt ein ZIP vom aktuellen HEAD (simuliert das Release‑Archiv)
git archive --format=zip --output=/tmp/consent_manager_release_test.zip HEAD

# Auflisten und prüfen ob verbotene Dateien auftauchen
unzip -l /tmp/consent_manager_release_test.zip | egrep "composer.phar|node_modules|assets/tests|tests/|phpunit|php-cs-fixer" || true
```

Wenn dabei `composer.phar` oder andere dev‑Artefakte angezeigt werden, bitte prüfen, ob sie versehentlich ins Repo gelangen — entferne sie und wiederhole die Prüfung.

Tip: Wenn du Composer auf CI brauchst, nutze den offiziellen Installer (https://getcomposer.org) oder den System‑Composer; die Repo‑Konvention vermeidet das Einchecken der Binary in das Projekt.

---

## 🆘 Support und Community

**Issue melden:** [GitHub Issues](https://github.com/FriendsOfREDAXO/consent_manager/issues)

**Contributions:** Pull Requests sind willkommen - besonders eigene Themes mit Screenshot oder Demo-Link!
