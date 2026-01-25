# Consent-Manager für REDAXO CMS

![logo](https://github.com/FriendsOfREDAXO/consent_manager/blob/assets/consent_manager-logo.jpg?raw=true)

## Was ist der Consent-Manager?

Das AddOn stellt eine **DSGVO-konforme Lösung** für die Einholung von Einverständniserklärungen zu Cookies und externen Diensten bereit. Website-Besucher erhalten eine Consent-Box, in der einzelne Dienste-Gruppen akzeptiert oder abgelehnt werden können. Technisch notwendige Dienste bleiben dabei immer aktiv.

**Kernfunktionen:**
- ✅ Datenschutz-Opt-In-Banner für Dienste und Cookies
- ✅ Flexible Gruppierung von Diensten  
- ✅ Nachträgliche Änderung der Einstellungen möglich
- ✅ Vollständig anpassbare Texte und Designs
- ✅ Google Consent Mode v2 Integration
- ✅ Mehrsprachig und Multi-Domain-fähig
- ✅ Automatische Frontend-Einbindung (Auto-Inject)
- ✅ CKE5 oEmbed Integration (YouTube, Vimeo, etc.)
- ✅ Sprachspezifische Scripts mit automatischem Fallback

![Screenshot](https://github.com/FriendsOfREDAXO/consent_manager/blob/assets/consent_manager.png?raw=true)

### Rechtlicher Hinweis

**Haftungsausschluss:** Mitgelieferte Texte und Cookie-Definitionen sind Beispiele ohne Gewähr. Website-Betreiber sind eigenverantwortlich für rechtskonforme Integration und müssen alle Inhalte durch Datenschutzbeauftragte prüfen lassen.

---

## Schnellstart

### Installation

1. **AddOn installieren** über REDAXO Installer
2. **Setup-Wizard öffnen**: Nach der Installation automatisch oder später unter `Consent Manager → Einstellungen`
3. **Konfiguration durchführen**: Der 7-stufige Setup-Assistent führt durch die Einrichtung

**Setup-Varianten:**
- **Minimal**: Nur technisch notwendige Cookies
- **Standard**: Vollständige Service-Sammlung (Google Analytics, Facebook, YouTube, etc.)

### Grundeinrichtung

#### 1. Domain konfigurieren

**Backend:** `Consent Manager → Domains → Domain hinzufügen`

Domain **ohne Protokoll** eintragen:
```
beispiel.de
```

**Wichtige Einstellungen:**
- **Datenschutzerklärung**: Link zur Datenschutzseite
- **Impressum**: Link zur Impressumsseite
- **Automatische Frontend-Einbindung**: `Aktiviert` (empfohlen)

#### 2. Auto-Inject aktivieren (empfohlen)

Die **automatische Frontend-Einbindung** ist der einfachste Weg, den Consent Manager zu integrieren:

**Backend:** `Consent Manager → Domains → Domain bearbeiten`

**🚀 Automatische Frontend-Einbindung:**
- **Status**: `Aktiviert`
- **Seite neu laden bei Consent-Änderung**: `Ja` (empfohlen)
- **Verzögerung bis Anzeige**: `0` Sekunden (sofort)
- **Fokus auf Consent-Box setzen**: `Ja` (für Barrierefreiheit)
- **Nur in bestimmten Templates einbinden**: Scrollbare Checkbox-Liste mit allen aktiven Templates (nichts auswählen = alle)

✅ **Fertig!** Der Consent Manager wird automatisch vor `</head>` eingefügt - **keine Template-Anpassung nötig**.

<a id="template-positivliste"></a>

### Template-Positivliste (Optional)

In manchen Fällen möchtest du den Consent Manager nur in bestimmten Templates einbinden:

**Checkbox-Auswahl:** `Nur in diesen Templates einbinden`
- **Nichts auswählen**: Consent Manager wird in **allen Templates** eingebunden (Standardverhalten)
- **Templates auswählen**: Consent Manager wird **nur in ausgewählten Templates** eingebunden
- **Features**: Scrollbare Liste (max. 250px), "Alle auswählen" Checkbox, Safari-kompatibel

**Wann sinnvoll?**
- Websites mit vielen Spezial-Templates (API, AJAX, Print, RSS, etc.)
- Wenn du explizit kontrollieren möchtest, wo der Consent Manager erscheint
- Sicherheitskritische Setups mit sensiblen Endpoints

**Typische Szenarien für Template-Einschränkung:**
```
Template         Verwendung               Warum einschränken?
--------------   ----------------------   ---------------------
API-Endpoint     JSON/XML-Ausgabe         Keine HTML-Struktur
AJAX-Loader      Content-Fragmente        Nur Teilinhalte
Print-Version    Druckansicht             Ohne Cookie-UI
RSS-Feed         XML-Feed                 Kein HTML
PDF-Export       PDF-Generierung          Ohne JavaScript
iFrame-Content   Eingebettete Inhalte     Separate Integration
404-Fehler       Fehlerseite              Optional
```

**⚠️ Wichtig: Zusätzliche Parameter-Checks**

Die automatische Einbindung prüft nur das Template, **nicht aber URL-Parameter**. Probleme können auftreten bei:
- Print-Modus über URL-Parameter (`?print=1`)
- AJAX-Popups über Parameter (`?popup=1`, `?ajax=1`)
- Dynamische Varianten (`?view=iframe`, `?format=json`)

**👉 Empfehlung:** Bei komplexen Parameter-Checks → **[Manuelle Einrichtung](index.php?page=consent_manager/help#manuelle-einrichtung)** verwenden

Falls Auto-Inject trotzdem gewünscht:

```php
<?php
// Im Template: Auto-Inject deaktivieren wenn Parameter vorhanden
if (rex_request::get('print') == '1' 
    || rex_request::get('ajax') == '1' 
    || rex_request::get('popup') == '1') {
    // Auto-Inject wird ausgeführt, aber Template sollte <head> nicht rendern
    // ODER: Auto-Inject deaktivieren und manuell einbinden mit if-Bedingung
    // → Siehe: index.php?page=consent_manager/help#manuelle-einrichtung
}
?>
```

💡 **Tipp:** Bei URL-Parameter-Abhängigkeiten ist die manuelle Integration oft sauberer und wartbarer.

#### 3. Dienste und Gruppen anpassen

**Backend:** `Consent Manager → Dienste/Cookies` und `Cookie-Gruppen`

- Importierte Dienste überprüfen
- Texte und Cookie-Namen anpassen
- Eigene Dienste hinzufügen (z.B. Newsletter-Tools, Custom Scripts)

#### 4. Texte anpassen (optional)

**Backend:** `Consent Manager → Texte`

Alle Texte der Consent-Box anpassen (Überschriften, Beschreibungen, Button-Labels).

#### 5. Theme auswählen (optional)

**Backend:** `Consent Manager → Themes` oder in der Domain-Konfiguration

Vordefiniertes Theme auswählen oder eigenes erstellen.

#### 6. Footer-Links einrichten (empfohlen)

Für DSGVO-Konformität muss ein Link zu den Cookie-Einstellungen im Footer platziert werden, damit Nutzer ihre Einwilligung jederzeit ändern können:

```html
<!-- Cookie-Einstellungen Link (empfohlen) -->
<a href="#" data-consent-action="settings">Cookie-Einstellungen</a>
```

**💡 Tipp:** Das Data-Attribut `data-consent-action="settings"` wird automatisch vom Consent Manager JavaScript erkannt. Das Script öffnet beim Klick automatisch die Cookie-Box - kein manueller `onclick`-Handler nötig!

**📖 Ausführliche Dokumentation** mit weiteren Optionen → [Siehe unten](#cookie-einstellungen-link-im-footer)

---

## Weitergehende Konfiguration

### Google Consent Mode v2

**Google Consent Mode** ermöglicht anonymisierte Datenerfassung, auch wenn Nutzer Cookies ablehnen.

**Aktivierung:** `Consent Manager → Domains → Domain bearbeiten`

**Google Consent Mode v2:**
- **Deaktiviert**: Kein Google Consent Mode
- **Auto**: Automatische Integration (empfohlen)
- **Manual**: Manuelle Konfiguration

**Debug-Modus**: Aktivieren für detaillierte Consent-Informationen im Frontend (nur für angemeldete Backend-Benutzer).

### Inline-Only Modus

**Inline-Only Modus** unterdrückt das globale Cookie-Banner. Consent wird nur bei Bedarf über `doConsent()` JavaScript-Funktion abgefragt (z.B. bei eingebetteten Videos).

**Aktivierung:** `Consent Manager → Domains → Domain bearbeiten → Inline-Only Modus`

**Anwendungsfall:**
- Landing Pages ohne Tracking
- Einzelne Unterseiten mit Videos/Maps
- Progressive Consent (nur bei Bedarf)

### CKE5 oEmbed Integration

**Automatische Umwandlung** von YouTube/Vimeo-Links in datenschutzkonforme Blocker.

**Aktivierung:** `Consent Manager → Domains → Domain bearbeiten`

**CKE5 oEmbed Integration:**
- **Status**: `Aktiviert`
- **Video-Breite**: `640` Pixel
- **Video-Höhe**: `360` Pixel
- **Drei-Button-Variante**: `Ja` (zeigt "Alle zulassen" Button)

**Inline-Assets einbinden** (für Blocker-Darstellung):

```php
<!DOCTYPE html>
<html>
<head>
    <!-- Consent Manager Inline CSS -->
    <link rel="stylesheet" href="<?= rex_url::addonAssets('consent_manager', 'consent_inline.css') ?>">
</head>
<body>
    <?= $this->getArticle() ?>
    
    <!-- Consent Manager Inline JavaScript -->
    <script defer src="<?= rex_url::addonAssets('consent_manager', 'consent_inline.js') ?>"></script>
</body>
</html>
```

### Multi-Domain Setup

**Verschiedene Domains mit unterschiedlichen Konfigurationen:**

1. Mehrere Domains hinzufügen: `Consent Manager → Domains`
2. Jede Domain individuell konfigurieren (Dienste, Texte, Theme)
3. Der Consent Manager erkennt automatisch die aktuelle Domain

### Mehrsprachigkeit

**Sprachspezifische Inhalte** für internationale Websites:

**Backend:** `Consent Manager → Texte`

- Texte können pro Sprache angepasst werden
- Dienste können sprachspezifische Scripts haben
- Automatischer Fallback zur Start-Sprache

---

## 💾 Datensicherung

### Export der Konfiguration

**Backend:** `Consent Manager → Einstellungen → Konfiguration exportieren`

- Exportiert alle Domains, Dienste, Gruppen und Texte als JSON
- Verwendung für Backup oder Übertragung auf andere Instanzen

### Import der Konfiguration

**Backend:** `Consent Manager → Einstellungen → JSON-Konfiguration importieren`

- Importiert zuvor exportierte JSON-Datei
- Überschreibt bestehende Konfiguration oder fügt nur neue Elemente hinzu

**Import-Modi:**
- **Komplett laden**: Überschreibt alle Einstellungen
- **Nur Neue**: Fügt nur neue Services hinzu, bestehende bleiben unverändert

---

## Manuelle Einrichtung

Für Entwickler oder spezielle Anwendungsfälle ohne Auto-Inject.

### PHP-Integration im Template

```php
<?php 
use FriendsOfRedaxo\ConsentManager\Frontend;

// Standard-Integration (alles in einem)
echo Frontend::getFragment(0, 0, 'ConsentManager/box_cssjs.php'); 

// Oder Komponenten einzeln laden:
?>
<style><?php echo Frontend::getCSS(); ?></style>
<script<?php echo Frontend::getNonceAttribute(); ?>>
    <?php echo Frontend::getJS(); ?>
</script>
<?php echo Frontend::getBox(); ?>
```

#### Parameter von `Frontend::getFragment()`

Die Methode `Frontend::getFragment()` akzeptiert drei Parameter:

```php
Frontend::getFragment(int $forceCache, int $forceReload, string $fragmentFilename): string
```

| Parameter | Typ | Standard | Beschreibung |
|-----------|-----|----------|--------------|
| `$forceCache` | `int` | `0` | **Cache-Steuerung:**<br>`0` = Cache verwenden (empfohlen für Produktion)<br>`1` = Cache neu generieren (für Entwicklung/Debugging) |
| `$forceReload` | `int` | `0` | **Reload-Steuerung:**<br>`0` = Keine automatische Seitenaktualisierung<br>`1` = Seite wird bei Consent-Änderung neu geladen |
| `$fragmentFilename` | `string` | - | **Fragment-Datei:**<br>`'ConsentManager/box_cssjs.php'` = Komplette Einbindung (CSS + JS + Box)<br>`'ConsentManager/box.php'` = Nur Consent-Box HTML<br>Oder eigenes Custom-Fragment |

**Beispiele:**

```php
// Produktion: Cache verwenden, kein Auto-Reload
echo Frontend::getFragment(0, 0, 'ConsentManager/box_cssjs.php');

// Entwicklung: Cache neu generieren
echo Frontend::getFragment(1, 0, 'ConsentManager/box_cssjs.php');

// Mit Auto-Reload bei Consent-Änderung
echo Frontend::getFragment(0, 1, 'ConsentManager/box_cssjs.php');

// Eigenes Fragment verwenden
echo Frontend::getFragment(0, 0, 'MyProject/custom_consent_box.php');
```

**💡 Tipp:** In der Produktion sollte `$forceCache = 0` verwendet werden, um Performance zu optimieren. Der Cache wird automatisch aktualisiert, wenn Änderungen im Backend gespeichert werden.

**Optional: Alle verfügbaren Parameter manuell steuern**

Wenn du die manuelle Integration nutzt, kannst du verschiedene Einstellungen per JavaScript konfigurieren:

```php
<script<?php echo Frontend::getNonceAttribute(); ?>>
    // WICHTIG: Vor dem Frontend::getJS() oder Frontend::getFragment() aufrufen!
    window.consentManagerConfig = {
        // Auto-Inject Einstellungen
        autoInjectDelay: 2,           // Verzögerung bis Anzeige in Sekunden (Standard: 0)
        autoInjectReload: true,       // Seite bei Consent-Änderung neu laden (Standard: false)
        autoInjectFocus: true,        // Fokus auf Box setzen für A11y (Standard: true)
        
        // Box-Verhalten
        hideBodyScrollbar: true,      // Body-Scrollbar verstecken wenn Box offen (Standard: false)
        initiallyHidden: false,       // Box initial versteckt (Standard: false)
        
        // Debug & Entwicklung
        debug: true,                  // Console-Logs aktivieren (Standard: false)
        
        // Cookie-Konfiguration (selten nötig - Backend-Einstellungen haben Vorrang)
        cookieSameSite: 'Lax',        // Cookie SameSite: 'Strict', 'Lax', 'None' (Standard: 'Lax')
        cookieSecure: true,           // Cookie nur über HTTPS (Standard: false)
        cookieName: 'consentmanager', // Cookie-Name (Standard: 'consentmanager')
        cookieExpires: 365            // Cookie-Laufzeit in Tagen (Standard: 365)
    };
    
    <?php echo Frontend::getJS(); ?>
</script>
```

**Verfügbare Parameter im Detail:**

| Parameter | Typ | Standard | Beschreibung | Wann verwenden? |
|-----------|-----|----------|--------------|------------------|
| `autoInjectDelay` | `int` | `0` | Verzögerung in Sekunden bis Box erscheint | Performance-Optimierung, First Paint verbessern |
| `autoInjectReload` | `bool` | `false` | Seite bei Consent-Änderung neu laden | Wenn Scripts nach Consent sofort laden müssen |
| `autoInjectFocus` | `bool` | `true` | Fokus auf Box setzen (Barrierefreiheit) | Immer aktiviert lassen für A11y |
| `hideBodyScrollbar` | `bool` | `false` | Body-Scrollbar verstecken bei offener Box | Modal-Verhalten erzwingen |
| `initiallyHidden` | `bool` | `false` | Box initial versteckt (Inline-Only) | Nur bei manueller `showBox()`-Nutzung |
| `debug` | `bool` | `false` | Console-Logs aktivieren | Entwicklung und Debugging |
| `cookieSameSite` | `string` | `'Lax'` | Cookie SameSite-Attribut | Nur ändern bei Cross-Domain-Setup |
| `cookieSecure` | `bool` | `false` | Cookie nur über HTTPS | Automatisch `true` bei HTTPS |
| `cookieName` | `string` | `'consentmanager'` | Name des Consent-Cookies | Nur bei Cookie-Konflikten |
| `cookieExpires` | `int` | `365` | Laufzeit in Tagen | DSGVO: max. 1 Jahr empfohlen |

**⚠️ Hinweis:** JavaScript-Konfiguration überschreibt Backend-Einstellungen. Für normale Setups empfohlen: Backend-Einstellungen nutzen (`Consent Manager → Domains`).

**Typische Anwendungsfälle:**

```php
<!-- Performance: Box mit 1 Sekunde Verzögerung -->
<script<?php echo Frontend::getNonceAttribute(); ?>>
    window.consentManagerConfig = {
        autoInjectDelay: 1
    };
    <?php echo Frontend::getJS(); ?>
</script>

<!-- Debug-Modus für Entwicklung -->
<script<?php echo Frontend::getNonceAttribute(); ?>>
    <?php if (rex::isDebugMode()): ?>
    window.consentManagerConfig = {
        debug: true
    };
    <?php endif; ?>
    <?php echo Frontend::getJS(); ?>
</script>

<!-- Inline-Only Modus (Box wird manuell getriggert) -->
<script<?php echo Frontend::getNonceAttribute(); ?>>
    window.consentManagerConfig = {
        initiallyHidden: true,
        autoInjectFocus: false
    };
    <?php echo Frontend::getJS(); ?>
</script>
<!-- Später: window.consentManager.showBox(); -->
```

### ⚠️ REX_CONSENT_MANAGER Variable (DEPRECATED)

> **WICHTIG:** Die `REX_CONSENT_MANAGER` Variable ist **veraltet** und sollte **nicht mehr verwendet werden**.  
> Bitte nutze stattdessen die **PHP-Integration** (`Frontend::getFragment()`) oder **Auto-Inject**.

<details>
<summary>Alte Syntax (nur zur Referenz)</summary>

```php
REX_CONSENT_MANAGER[]
REX_CONSENT_MANAGER[forceCache=0 forceReload=0]
REX_CONSENT_MANAGER[inline=true]
REX_CONSENT_MANAGER[fragment=my_custom_box.php]
```

**Migration:** Ersetze `REX_CONSENT_MANAGER[]` durch `Frontend::getFragment()` (siehe oben).

</details>

<a id="cookie-einstellungen-link-im-footer"></a>

### Cookie-Einstellungen Link im Footer

### Cookie-Einstellungen Link im Footer

**DSGVO-Pflicht:** Link zu Cookie-Einstellungen im Footer erforderlich, damit Nutzer ihre Einwilligung jederzeit ändern können.

#### Unterstützte Klassen und Attribute

| Klasse/Attribut | Funktion | Auto-Display | Reload |
|-----------------|----------|--------------|--------|
| `data-consent-action="settings"` | Öffnet Cookie-Box **(empfohlen)** | Ja | Nein |
| `data-consent-action="settings,reload"` | Öffnet Cookie-Box mit Auto-Reload | Ja | **Ja** |
| `data-consent-action="settings,dontshow"` | Nur per Klick, kein Auto-Display | **Nein** | Nein |
| `data-consent-action="settings,reload,dontshow"` | Nur per Klick mit Auto-Reload | **Nein** | **Ja** |
| `consent_manager-show-box` | Öffnet Cookie-Box (Legacy) | Ja | Nein |
| `consent_manager-show-box-reload` | Öffnet Cookie-Box mit Auto-Reload (Legacy) | Ja | **Ja** |

**Auto-Display:** Wenn kein Consent vorhanden ist, öffnet sich die Box automatisch beim Seitenaufruf.

**dontshow Flag:** Verhindert das automatische Öffnen der Box beim ersten Besuch. Der Link funktioniert weiterhin per Klick. Nützlich für Websites, die dem User mehr Kontrolle geben möchten.

**Reload-Funktion:** Bei `data-consent-action="settings,reload"` oder der Legacy-Klasse `consent_manager-show-box-reload` wird die Seite nach dem Speichern automatisch neu geladen. Nützlich wenn externe Scripts (wie Analytics) einen Reload benötigen.

#### Beispiele

```html
<!-- Einfach (empfohlen) -->
<a href="#" data-consent-action="settings">Cookie-Einstellungen</a>

<!-- Mit Icon -->
<a href="#" data-consent-action="settings">
    <i class="fa fa-cookie-bite"></i> Cookie-Einstellungen
</a>

<!-- Ohne automatisches Öffnen beim ersten Besuch -->
<a href="#" data-consent-action="settings,dontshow">Cookie-Einstellungen</a>

<!-- Mit Reload nach Consent-Änderung -->
<a href="#" data-consent-action="settings,reload">Cookie-Einstellungen</a>

<!-- In Navigation -->
<nav>
    <ul>
        <li><a href="/datenschutz/">Datenschutz</a></li>
        <li><a href="/impressum/">Impressum</a></li>
        <li><a href="#" data-consent-action="settings">Cookie-Einstellungen</a></li>
    </ul>
</nav>
```

**JavaScript-Aufruf (für Custom-Implementierungen):**

```javascript
window.consentManager.showBox();
```

---

<a id="cookie-liste-datenschutz"></a>

### Cookie-Liste in Datenschutzerklärung automatisch ausgeben

**DSGVO-Pflicht:** In der Datenschutzerklärung müssen alle verwendeten Cookies aufgelistet werden. Der Consent Manager kann diese Liste automatisch generieren:

#### PHP-Integration in Templates

```php
<?php
use FriendsOfRedaxo\ConsentManager\Frontend;

// Standard: HTML-Tabelle mit allen Cookies der aktuellen Domain
echo Frontend::getCookieList();

// Als Definitionsliste (dl/dt/dd)
echo Frontend::getCookieList('dl');

// Für eine spezifische Domain
echo Frontend::getCookieList('table', 'beispiel.de');
?>
```

#### Ausgabe-Formate

**Tabellen-Format** (`format=table`, Standard):
- Übersichtliche Tabelle mit Cookie-Name, Service, Zweck, Laufzeit, Anbieter, Kategorie
- Nutzt Standard-HTML `<table>` mit Klasse `consent-manager-cookie-list`

**Definitionsliste** (`format=dl`):
- Gruppiert nach Cookie-Kategorien
- Kompaktere Darstellung mit `<dl>/<dt>/<dd>`-Elementen
- Besser für mobile Ansichten

**💡 Vorteile:**
- ✅ **Automatisch aktuell**: Änderungen im Backend erscheinen sofort
- ✅ **DSGVO-konform**: Vollständige Cookie-Dokumentation
- ✅ **Mehrsprachig**: Nutzt die aktuelle Sprache
- ✅ **Pflegeleicht**: Keine manuelle Pflege nötig

---

#### Einfache Variante (mit Frontend-Objekt)

```php
<?php
use FriendsOfRedaxo\ConsentManager\Frontend;

$consent = new Frontend();
$consent->setDomain(rex_request::server('HTTP_HOST'));

// Artikel-IDs aus Domain-Konfiguration abrufen
$privacyId = $consent->links['privacy_policy'] ?? 0;
$legalId = $consent->links['legal_notice'] ?? 0;
?>

<footer class="site-footer">
    <nav aria-label="Rechtliche Informationen">
        <ul>
            <?php if ($privacyId > 0): ?>
                <li><a href="<?= rex_getUrl($privacyId) ?>" rel="nofollow">Datenschutz</a></li>
            <?php endif; ?>
            
            <?php if ($legalId > 0): ?>
                <li><a href="<?= rex_getUrl($legalId) ?>" rel="nofollow">Impressum</a></li>
            <?php endif; ?>
            
            <li><a href="#" data-consent-action="settings">Cookie-Einstellungen</a></li>
        </ul>
    </nav>
</footer>
```

**💡 Tipps:**
- Links zu rechtlichen Seiten sollten immer `rel="nofollow"` haben
- Der "Cookie-Einstellungen" Link ermöglicht Nutzern, ihre Einwilligung jederzeit zu ändern (DSGVO-Pflicht!)
- `data-consent-action="settings"` wird automatisch erkannt - kein onclick-Handler nötig

---

## 👨‍💻 Für Developer

### JavaScript API

```javascript
// Consent-Status prüfen
if (consent_manager_hasconsent('youtube')) {
    // YouTube wurde akzeptiert
}

// Event bei Consent-Änderung
document.addEventListener('consent_manager-saved', function(e) {
    var consents = JSON.parse(e.detail);
    // Scripts nachladen, UI aktualisieren etc.
});

// Box öffnen
consent_manager_showBox();

// Erweiterte API
window.consentManager.doConsent('youtube', {
    callback: function(consentGiven) {
        if (consentGiven) loadYouTubeVideo();
    }
});
```

### PHP API

```php
<?php
use FriendsOfRedaxo\ConsentManager\{Utility, Domain, Cookie, CookieGroup};

// Consent-Status prüfen
if (Utility::has_consent('youtube')) {
    echo '<iframe src="https://youtube.com/..."></iframe>';
}

// Domain-Konfiguration
$domain = Domain::getCurrentDomain();
$autoInject = $domain->getValue('auto_inject');

// Services und Gruppen
$services = Cookie::getByDomain('example.com');
$groups = CookieGroup::getByDomain('example.com');
?>
```

### Custom Fragments erstellen

Eigene Consent-Box-Designs über Fragments:

**Pfad:** `redaxo/src/addons/project/fragments/my_custom_box.php`

```php
<?php
/** @var rex_fragment $this */
$css = $this->getVar('css');
$js = $this->getVar('js');
$box = $this->getVar('box');
?>

<style><?= $css ?></style>
<script<?= $this->getVar('nonce_attribute') ?>><?= $js ?></script>
<?= $box ?>
```

**Verwendung:**

```php
echo Frontend::getFragment(0, 0, 'my_custom_box.php');
```

### Hooks und Extension Points

```php
// Vor Output-Generierung
rex_extension::register('rex_consent_manager_before_output', function($ep) {
    $output = $ep->getSubject();
    // Output anpassen
    return $output;
});

// Nach Speichern
rex_extension::register('rex_consent_manager_after_save', function($ep) {
    $type = $ep->getParam('type'); // 'cookie', 'cookiegroup', 'domain'
    $id = $ep->getParam('id');
});
```

---

## 🎨 Design und Themes

### Verfügbare Themes

**Standard-Themes:**
- Light/Dark
- Bottom Bar/Bottom Right
- Accessibility (A11y)

**Theme auswählen:** `Consent Manager → Domains → Theme`

### Eigenes Theme erstellen

**Pfad:** `redaxo/src/addons/project/consent_manager_themes/my_theme.scss`

```scss
$consent-primary-color: #667eea;
$consent-background: #ffffff;

@import "base";

.consent_manager-box {
    border-radius: 12px;
}
```

**Theme zuweisen:** Domain-Einstellungen → Theme auswählen

---

## Dienste und Gruppen konfigurieren

### Dienste anlegen

**Backend:** `Consent Manager → Dienste`

| Feld | Beschreibung |
|------|--------------|
| **Schlüssel** | Eindeutiger Key ohne Sonderzeichen (z.B. `youtube`) |
| **Dienstname** | Anzeigename (z.B. "YouTube Videos") |
| **Anbieter** | Firmenname (z.B. "Google LLC") |
| **Cookies** | YAML-Liste der Cookies |
| **Script** | JavaScript-Code (lädt bei Consent) |

### Cookie-Definition (YAML)

```yaml
-
 name: _ga
 time: 2 Jahre
 desc: "Google Analytics ID"
-
 name: _gid
 time: 24 Stunden
 desc: "Session-Tracking"
```

### Gruppen erstellen

**Backend:** `Consent Manager → Gruppen`

- **Technisch notwendig:** Immer aktiv, nicht deaktivierbar
- **Dienste zuweisen:** Mehrere Services pro Gruppe
- **Domain:** Zuordnung zur Domain

---

## ✨ Best Practices

### Performance

```php
// Cache aktivieren (Produktion)
echo Frontend::getFragment(0, 0, 'ConsentManager/box_cssjs.php');

// defer-Attribut
<script defer src="consent_inline.js"></script>

// Auto-Inject mit Verzögerung
Domain → Auto-Inject → Verzögerung: 500ms
```

### DSGVO-Konformität

- ✅ Opt-In vor Tracking
- ✅ Cookie-Listen aktuell halten
- ✅ Link zu Einstellungen im Footer
- ✅ Datenschutzerklärung synchron

### Barrierefreiheit

- ✅ Accessibility Theme verwenden
- ✅ Fokus-Management aktivieren (`autoInjectFocus: true`)
- ✅ Tastatur-Navigation testen (Tab, Enter, Escape)
- ✅ Screen-Reader testen (NVDA, JAWS)

---

## 🔧 Tipps und Tricks

### CKE5 oEmbed
1. Inline-Assets im Template einbinden
2. Videos via URL in CKE5 einfügen
3. Automatische Blocker-Umwandlung

### Custom Blocker

```html
<div class="consent-blocker" 
     data-consent-group="marketing">
    <div class="consent-blocker-overlay">
        <button data-consent-action="load">Einmal laden</button>
    </div>
</div>
```

### Debugging

```javascript
// Console-Logs aktivieren
localStorage.setItem('consentManager.debug', 'true');
location.reload();

// Backend: Domain → Debug-Modus aktivieren
```

### Testing-Checkliste

- [ ] Box erscheint beim ersten Besuch
- [ ] Alle Buttons funktionieren
- [ ] Cookie wird gesetzt
- [ ] Videos/Maps laden nach Consent
- [ ] Einstellungen änderbar
- [ ] Mobile Darstellung OK
- [ ] Tastatur-Navigation funktioniert

---

## 🔒 Content Security Policy (CSP)

**CSP-Nonce-Unterstützung** für sichere Inline-Scripts:

### Frontend Integration

```php
<?php
use FriendsOfREDAXO\ConsentManager\Frontend;

// Automatisch mit Nonce-Attribut
echo Frontend::getFragment(0, 0, 'ConsentManager/box_cssjs.php');

// Oder manuell:
?>
<script<?php echo Frontend::getNonceAttribute(); ?>>
    <?php echo Frontend::getJS(); ?>
</script>
```

### CSP Header konfigurieren

**Apache (.htaccess):**
```apache
<IfModule mod_headers.c>
    Header set Content-Security-Policy "script-src 'self' 'nonce-<?php echo rex_response::getNonce(); ?>'"
</IfModule>
```

**Nginx:**
```nginx
add_header Content-Security-Policy "script-src 'self' 'nonce-$nonce'";
```

**PHP (im Template):**
```php
<?php
header("Content-Security-Policy: script-src 'self' 'nonce-" . rex_response::getNonce() . "'");
?>
```

### Externe Dienste in CSP erlauben

```
Content-Security-Policy: 
  script-src 'self' 'nonce-xyz123' 
    https://www.googletagmanager.com 
    https://www.google-analytics.com;
  img-src 'self' data: 
    https://www.google-analytics.com;
  connect-src 'self' 
    https://www.google-analytics.com;
```

**Wichtig:** Externe Scripts (Google Analytics, Facebook Pixel etc.) müssen explizit erlaubt werden.

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

**Besonderer Dank an:**
- [Christoph Böcker](https://github.com/Christophboecker) - Code refactoring, bug fixing und mehr
- [Thomas Blum](https://github.com/tbaddade/) - Code aus Sprog AddOn
- [Peter Bickel](https://github.com/polarpixel) - Entwicklungsspende
- [Oliver Kreischer](https://github.com/olien) - Cookie-Design

**Externe Bibliotheken:**
- [cookie.js](https://github.com/js-cookie/js-cookie) - MIT Lizenz

---

## 🆘 Support und Community

**Issue melden:** [GitHub Issues](https://github.com/FriendsOfREDAXO/consent_manager/issues)

**Contributions:** Pull Requests sind willkommen - besonders eigene Themes mit Screenshot oder Demo-Link!

**Community:**
- REDAXO Slack: `#consent-manager` Channel
- REDAXO Forum: [friendsofredaxo.github.io/forum](https://friendsofredaxo.github.io/forum/)

---

**Made with ❤️ by Friends Of REDAXO**
