# Consent Manager - API Referenz

Öffentliche API für JavaScript und PHP im Consent Manager Addon.

---

## JavaScript API

### Globale Funktionen

#### `consent_manager_showBox()`

Zeigt die Consent-Box manuell an. Lädt fehlende Inhalte automatisch nach (Lazy Loading).

```javascript
// Consent-Box anzeigen
consent_manager_showBox();

// Mit Link/Button
<a href="#" onclick="consent_manager_showBox(); return false;">Cookie-Einstellungen ändern</a>
```

**Best Practice mit Data-Attributen (empfohlen):**
```html
<!-- Nur Box anzeigen -->
<a href="#" data-consent-action="settings">Cookie-Einstellungen</a>

<!-- Box anzeigen + Reload nach Consent -->
<a href="#" data-consent-action="settings,reload">Cookie-Einstellungen</a>
```

---

#### `consent_manager_hasconsent(serviceUid)`

Prüft, ob für einen Service bereits Consent erteilt wurde.

**Parameter:**
- `serviceUid` (string): UID des Services (z.B. `'youtube'`, `'google-analytics'`)

**Return:** `boolean`

```javascript
// Consent prüfen
if (consent_manager_hasconsent('youtube')) {
    // YouTube-Video laden
    loadYouTubeVideo();
} else {
    // Placeholder anzeigen
    showPlaceholder();
}

// Google Analytics bedingt laden
if (consent_manager_hasconsent('google-analytics')) {
    gtag('config', 'GA_MEASUREMENT_ID');
}
```

---

### Globale Variablen

#### `consent_manager_parameters`

Enthält alle Konfigurationsparameter der Consent-Box.

```javascript
// Verfügbare Properties
consent_manager_parameters.domain          // Aktuelle Domain
consent_manager_parameters.clang           // Aktuelle Sprach-ID
consent_manager_parameters.consentid       // Consent-Konfiguration ID
consent_manager_parameters.cachelogid      // Cache-Version
consent_manager_parameters.version         // Addon-Version
consent_manager_parameters.lazyLoad        // Lazy Loading aktiv? (boolean)
consent_manager_parameters.apiEndpoint     // API-URL für Lazy Loading
consent_manager_parameters.forcereload     // Automatischer Reload nach Consent (0/1)
consent_manager_parameters.hidebodyscrollbar // Body-Scrollbar verstecken (boolean)
consent_manager_parameters.cookieName      // Cookie-Name (Standard: 'consentmanager')
consent_manager_parameters.cookieSameSite  // SameSite-Attribut ('Lax', 'Strict', 'None')
consent_manager_parameters.cookieSecure    // Secure-Cookie? (boolean)

// Beispiel: Domain-spezifisches Verhalten
if (consent_manager_parameters.domain === 'example.com') {
    // Spezielle Konfiguration für diese Domain
}
```

---

#### `consent_manager_texts`

Enthält alle lokalisierten Texte der Consent-Box.

```javascript
// Beispiel-Texte
consent_manager_texts.box_title            // "Cookie-Einstellungen"
consent_manager_texts.box_text_top         // Intro-Text
consent_manager_texts.btn_accept_all       // "Alle akzeptieren"
consent_manager_texts.btn_reject_all       // "Alle ablehnen"
consent_manager_texts.btn_save             // "Speichern"
```

---

### Custom Events

#### `consent_manager-show`

Wird ausgelöst, wenn die Consent-Box angezeigt wird.

```javascript
document.addEventListener('consent_manager-show', function() {
    console.log('Consent-Box wurde angezeigt');
    // Tracking, Analytics, etc.
});
```

---

#### `consent_manager-saved`

Wird ausgelöst, nachdem Consent-Einstellungen gespeichert wurden.

**Event Detail:** JSON-String mit Array der erteilten Consents

```javascript
document.addEventListener('consent_manager-saved', function(e) {
    var consents = JSON.parse(e.detail);
    console.log('Folgende Consents wurden erteilt:', consents);
    
    // Prüfen ob Analytics erlaubt
    if (consents.indexOf('google-analytics') !== -1) {
        // GA initialisieren
        initGoogleAnalytics();
    }
});
```

---

### Cookies API

Zugriff auf die js-cookie Library (über `cmCookieAPI`):

```javascript
// Cookie lesen
var consentData = cmCookieAPI.get(cmCookieName);

// Cookie schreiben (normalerweise nicht nötig - wird automatisch gemacht)
cmCookieAPI.set(cmCookieName, JSON.stringify(data));

// Cookie löschen
cmCookieAPI.remove(cmCookieName);
```

---

### Google Consent Mode v2 Integration

```javascript
// Google Consent Mode prüfen
if (typeof window.GoogleConsentModeV2 !== 'undefined') {
    // Consent Mode verfügbar
    window.GoogleConsentModeV2.setConsent({
        'ad_storage': 'denied',
        'analytics_storage': 'granted',
        'ad_user_data': 'denied',
        'ad_personalization': 'denied'
    });
}
```

---

## PHP API

### Frontend-Klasse

Namespace: `FriendsOfRedaxo\ConsentManager\Frontend`

#### `Frontend::getFragment(int $forceCache, int $forceReload, string $fragmentFilename)`

Rendert ein Fragment (CSS, JS, Box).

**Parameter:**
- `$forceCache` (int): Cache erzwingen (0/1)
- `$forceReload` (int): Reload nach Consent erzwingen (0/1)
- `$fragmentFilename` (string): Fragment-Datei (z.B. `'ConsentManager/box_cssjs.php'`)

**Return:** `string` - HTML/CSS/JS Output

```php
use FriendsOfRedaxo\ConsentManager\Frontend;

// Im Template: CSS + JS + Box einbinden
echo Frontend::getFragment(0, 0, 'ConsentManager/box_cssjs.php');

// Nur CSS
echo Frontend::getFragment(0, 0, 'ConsentManager/box_css.php');

// Nur JS
echo Frontend::getFragment(0, 0, 'ConsentManager/box_js.php');
```

---

#### `Frontend::getCSS()`

Liefert nur das CSS der Consent-Box.

```php
// <head>-Bereich
echo '<style>' . Frontend::getCSS() . '</style>';
```

---

#### `Frontend::getJS()`

Liefert nur das JavaScript der Consent-Box.

```php
// Vor </body>
echo '<script>' . Frontend::getJS() . '</script>';
```

---

#### `Frontend::getBox()`

Liefert nur das HTML der Consent-Box (ohne CSS/JS).

```php
// Box-HTML irgendwo einfügen
echo Frontend::getBox();
```

---

#### `Frontend::getCookieList(string $format = 'table', ?string $domain = null)`

Generiert Cookie-Liste für die aktuelle Domain.

**Parameter:**
- `$format` (string): Ausgabeformat (`'table'`, `'list'`, `'json'`)
- `$domain` (string|null): Domain (Standard: aktuelle Domain)

**Return:** `string` - HTML-Tabelle, Liste oder JSON

```php
// Cookie-Tabelle für Datenschutzerklärung
echo Frontend::getCookieList('table');

// Als Liste
echo Frontend::getCookieList('list');

// Als JSON
$json = Frontend::getCookieList('json', 'example.com');
$cookies = json_decode($json, true);
```

---

#### `Frontend::getNonceAttribute()`

Liefert Nonce-Attribut für CSP (Content Security Policy).

```php
// Script-Tag mit Nonce
echo '<script ' . Frontend::getNonceAttribute() . '>';
echo Frontend::getJS();
echo '</script>';
```

---

### Inline Consent

Namespace: `FriendsOfRedaxo\ConsentManager\InlineConsent`

#### `InlineConsent::doConsent(string $serviceKey, string $content, array $options = [])`

Erstellt Inline-Consent-Blocker für externen Content (YouTube, Vimeo, Google Maps, etc.).

**Parameter:**
- `$serviceKey` (string): Service-UID (z.B. `'youtube'`, `'vimeo'`, `'google-maps'`)
- `$content` (string): Original-Content (iframe, embed-Code)
- `$options` (array): Optionale Parameter (`title`, `width`, `height`, `thumbnail`)

**Return:** `string` - HTML mit Consent-Blocker oder Original-Content (bei erteiltem Consent)

```php
use FriendsOfRedaxo\ConsentManager\InlineConsent;

// YouTube-Video blockieren
$youtubeIframe = '<iframe src="https://www.youtube.com/embed/VIDEO_ID"></iframe>';
echo InlineConsent::doConsent('youtube', $youtubeIframe);

// Mit Custom-Optionen
echo InlineConsent::doConsent('youtube', $youtubeIframe, [
    'title' => 'Mein Video-Titel',
    'width' => '100%',
    'height' => '500px',
    'thumbnail' => 'auto' // oder URL zu eigenem Thumbnail
]);

// Google Maps
$mapsIframe = '<iframe src="https://www.google.com/maps/embed?..."></iframe>';
echo InlineConsent::doConsent('google-maps', $mapsIframe);

// Vimeo
$vimeoIframe = '<iframe src="https://player.vimeo.com/video/VIDEO_ID"></iframe>';
echo InlineConsent::doConsent('vimeo', $vimeoIframe, [
    'title' => 'Mein Vimeo-Video'
]);
```

---

### Utility-Klasse

Namespace: `FriendsOfRedaxo\ConsentManager\Utility`

#### `Utility::has_consent(string $cookieUid)`

Prüft, ob Benutzer Consent für einen Service erteilt hat (serverseitig).

**Parameter:**
- `$cookieUid` (string): Service-UID

**Return:** `bool`

```php
use FriendsOfRedaxo\ConsentManager\Utility;

// Consent prüfen
if (Utility::has_consent('google-analytics')) {
    // Analytics-Code einbinden
    echo '<script>/* GA Code */</script>';
}

// Mehrere Services
if (Utility::has_consent('facebook-pixel')) {
    echo '<!-- Facebook Pixel -->';
}

if (Utility::has_consent('matomo')) {
    echo '<!-- Matomo Code -->';
}
```

---

#### `Utility::consentConfigured()`

Prüft, ob für die aktuelle Domain ein Consent konfiguriert ist.

**Return:** `bool`

```php
// Prüfen ob Consent Manager aktiv
if (Utility::consentConfigured()) {
    // Consent-Box anzeigen
    echo Frontend::getFragment(0, 0, 'ConsentManager/box_cssjs.php');
}
```

---

#### `Utility::hostname()`

Gibt den aktuellen Hostnamen zurück (normalisiert, ohne www).

**Return:** `string`

```php
// Hostname ermitteln
$domain = Utility::hostname();
// z.B. "example.com" (auch wenn www.example.com aufgerufen wurde)

// Domain-spezifische Logik
if (Utility::hostname() === 'example.com') {
    // Spezielle Konfiguration
}
```

---

#### `Utility::get_domaininfo(string $url)`

Extrahiert Domain-Informationen aus einer URL.

**Parameter:**
- `$url` (string): URL

**Return:** `array` - Array mit `['domain', 'subdomain', 'tld']`

```php
$info = Utility::get_domaininfo('https://www.example.com/page');
// ['domain' => 'example.com', 'subdomain' => 'www', 'tld' => 'com']
```

---

### ConsentManager-Klasse

Namespace: `FriendsOfRedaxo\ConsentManager\ConsentManager`

#### `ConsentManager::getCookieData(string $uid, ?int $clangId = null)`

Liefert Cookie-Daten für eine Service-UID.

**Parameter:**
- `$uid` (string): Service-UID
- `$clangId` (int|null): Sprach-ID (Standard: aktuelle Sprache)

**Return:** `array|null` - Cookie-Daten oder `null`

```php
use FriendsOfRedaxo\ConsentManager\ConsentManager;

// Service-Daten abrufen
$service = ConsentManager::getCookieData('youtube');
if ($service) {
    echo $service['service_name']; // "YouTube"
    echo $service['service_url'];  // "https://www.youtube.com"
}

// Für spezifische Sprache
$serviceDE = ConsentManager::getCookieData('youtube', 1);
$serviceEN = ConsentManager::getCookieData('youtube', 2);
```

---

#### `ConsentManager::getTexts(?int $clangId = null)`

Liefert alle lokalisierten Texte.

**Return:** `array`

```php
$texts = ConsentManager::getTexts();
echo $texts['box_title']; // "Cookie-Einstellungen"
```

---

#### `ConsentManager::getDomain(string $domain)`

Liefert Domain-Konfiguration.

**Return:** `array|null`

```php
$domainConfig = ConsentManager::getDomain('example.com');
if ($domainConfig) {
    $consentId = $domainConfig['id'];
    $googleConsentMode = $domainConfig['google_consent_mode_enabled'];
}
```

---

#### `ConsentManager::getCookies(?int $clangId = null)`

Liefert alle Services/Cookies.

**Return:** `array`

```php
$cookies = ConsentManager::getCookies();
foreach ($cookies as $uid => $cookie) {
    echo $cookie['service_name'];
    echo $cookie['cookie_uid'];
}
```

---

### Cache-Klasse

Namespace: `FriendsOfRedaxo\ConsentManager\Cache`

#### `Cache::forceWrite()`

Schreibt Cache neu (z.B. nach Änderungen im Backend).

```php
use FriendsOfRedaxo\ConsentManager\Cache;

// Cache manuell erneuern
Cache::forceWrite();
```

---

#### `Cache::read()`

Liest den kompletten Cache.

**Return:** `array`

```php
$cache = Cache::read();
$version = $cache['majorVersion'];
$cookies = $cache['cookies'];
```

---

### Google Consent Mode

Namespace: `FriendsOfRedaxo\ConsentManager\GoogleConsentMode`

#### `GoogleConsentMode::getDomainConfig(string $domain)`

Liefert Google Consent Mode Konfiguration für eine Domain.

**Return:** `array`

```php
use FriendsOfRedaxo\ConsentManager\GoogleConsentMode;

$config = GoogleConsentMode::getDomainConfig('example.com');
if ($config['enabled']) {
    $autoMapping = $config['auto_mapping']; // boolean
    $flags = $config['flags']; // ['ad_storage' => 'denied', ...]
}
```

---

#### `GoogleConsentMode::getCookieConsentMappings(int $clangId)`

Liefert Cookie-zu-Google-Consent-Flag Mappings.

**Return:** `array`

```php
$mappings = GoogleConsentMode::getCookieConsentMappings(1);
// ['youtube' => ['ad_storage', 'personalization_storage'], ...]
```

---

### oEmbed Parser

Namespace: `FriendsOfRedaxo\ConsentManager\OEmbedParser`

#### `OEmbedParser::register(?string $domain = null)`

Registriert automatischen oEmbed → Consent-Blocker Parser.

**Parameter:**
- `$domain` (string|null): Optional nur für spezifische Domain

```php
use FriendsOfRedaxo\ConsentManager\OEmbedParser;

// In boot.php - für alle Domains
OEmbedParser::register();

// Nur für spezifische Domain
OEmbedParser::register('example.com');
```

**CKEditor5 Integration:**
Wandelt automatisch oEmbed-Tags in Consent-Blocker um:
```html
<!-- Input (CKE5 oEmbed) -->
<oembed url="https://www.youtube.com/watch?v=VIDEO_ID"></oembed>

<!-- Output (mit Consent-Blocker) -->
<div class="consent-manager-inline-blocker">
    <!-- Consent-Placeholder -->
</div>
```

---

#### `OEmbedParser::parse(string $content, ?string $domain = null)`

Parst HTML-Content und ersetzt oEmbed-Tags.

**Return:** `string`

```php
$html = '<oembed url="https://www.youtube.com/watch?v=dQw4w9WgXcQ"></oembed>';
$parsed = OEmbedParser::parse($html);
// Enthält jetzt Consent-Blocker statt oEmbed-Tag
```

---

### JSON Setup

Namespace: `FriendsOfRedaxo\ConsentManager\JsonSetup`

#### `JsonSetup::importSetup(string $jsonFile, bool $clearExisting = true, string $mode = 'replace')`

Importiert eine JSON-Setup-Datei.

**Parameter:**
- `$jsonFile` (string): Pfad zur JSON-Datei (relativ zu `/data/addons/consent_manager/`)
- `$clearExisting` (bool): Bestehende Daten löschen?
- `$mode` (string): Import-Modus (`'insert'`, `'update'`, `'replace'`)

**Return:** `array` - Status-Informationen

```php
use FriendsOfRedaxo\ConsentManager\JsonSetup;

// Standard-Setup importieren
$result = JsonSetup::importSetup('standard-setup.json');

// Nur hinzufügen (nicht löschen)
$result = JsonSetup::importSetup('custom.json', false, 'insert');
```

---

#### `JsonSetup::exportSetup(bool $includeMetadata = true)`

Exportiert aktuelle Konfiguration als JSON.

**Return:** `array`

```php
$export = JsonSetup::exportSetup();
rex_file::put('backup.json', json_encode($export, JSON_PRETTY_PRINT));
```

---

#### `JsonSetup::getAvailableSetups()`

Listet verfügbare Setup-Dateien.

**Return:** `array`

```php
$setups = JsonSetup::getAvailableSetups();
// ['standard-setup.json', 'minimal-setup.json', ...]
```

---

### REX_VAR Variablen

Für die Verwendung in Templates und Modulen.

#### `REX_CONSENT_MANAGER[]`

Bindet Consent Manager CSS, JS und Box ein.

```php
// Standard: CSS + JS + Box
REX_CONSENT_MANAGER[]

// Mit Cache erzwingen (im Modul-Input)
REX_CONSENT_MANAGER[forceCache=1]

// Mit Reload nach Consent
REX_CONSENT_MANAGER[forceReload=1]

// Inline-Modus (ohne <script>/<style> Tags)
REX_CONSENT_MANAGER[inline=true]

// Custom Fragment
REX_CONSENT_MANAGER[fragment="ConsentManager/box_css.php"]
```

---

#### `REX_COOKIEDB[]`

Bindet nur die Cookie-Datenbank (Liste) ein.

```php
// Cookie-Liste anzeigen
REX_COOKIEDB[]

// Mit Cache-Optionen
REX_COOKIEDB[forceCache=1, forceReload=0]
```

---

## API Endpoint (Lazy Loading)

**URL:** `index.php?rex-api-call=consent_manager_texts`

**Parameter:**
- `clang` (int): Sprach-ID (erforderlich)
- `domain` (string): Domain-Name (optional)

**Methode:** GET

**Response:** JSON

```json
{
  "texts": {
    "box_title": "Cookie-Einstellungen",
    "box_text_top": "...",
    "btn_accept_all": "Alle akzeptieren",
    ...
  },
  "boxTemplate": "<div class=\"consent_manager-box\">...</div>",
  "cache": {
    "logId": 123,
    "version": "5.4.0",
    "expires": "Sat, 25 Jan 2025 23:59:59 GMT"
  }
}
```

**Beispiel (JavaScript Fetch):**
```javascript
fetch('index.php?rex-api-call=consent_manager_texts&clang=1&domain=example.com')
  .then(res => res.json())
  .then(data => {
    console.log(data.texts.box_title);
    console.log(data.boxTemplate);
  });
```

---

## Häufige Anwendungsfälle

### 1. Consent-Box im Template einbinden

```php
<?php
use FriendsOfRedaxo\ConsentManager\Frontend;

// Am Ende des Templates (vor </body>)
echo Frontend::getFragment(0, 0, 'ConsentManager/box_cssjs.php');
?>
```

**Alternative mit REX_VAR:**
```php
REX_CONSENT_MANAGER[]
```

---

### 2. Service-spezifische Skripte laden

```php
<?php
use FriendsOfRedaxo\ConsentManager\Utility;

// Google Analytics nur mit Consent laden
if (Utility::has_consent('google-analytics')) {
    echo '<script async src="https://www.googletagmanager.com/gtag/js?id=GA_ID"></script>';
    echo '<script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag("js", new Date());
        gtag("config", "GA_ID");
    </script>';
}
?>
```

**Alternative (JavaScript):**
```javascript
if (consent_manager_hasconsent('google-analytics')) {
    // GA laden
    var script = document.createElement('script');
    script.src = 'https://www.googletagmanager.com/gtag/js?id=GA_ID';
    document.head.appendChild(script);
}
```

---

### 3. YouTube-Video mit Consent-Blocker

```php
<?php
use FriendsOfRedaxo\ConsentManager\InlineConsent;

$video = '<iframe width="560" height="315" src="https://www.youtube.com/embed/VIDEO_ID"></iframe>';
echo InlineConsent::doConsent('youtube', $video, [
    'title' => 'Tutorial-Video',
    'width' => '100%',
    'height' => '500px'
]);
?>
```

---

### 4. Cookie-Liste für Datenschutzerklärung

```php
<?php
use FriendsOfRedaxo\ConsentManager\Frontend;

// In Datenschutz-Artikel
echo '<h2>Verwendete Cookies</h2>';
echo Frontend::getCookieList('table');
?>
```

---

### 5. Event-basierte Initialisierung

```javascript
// Auf Consent-Speicherung reagieren
document.addEventListener('consent_manager-saved', function(e) {
    var consents = JSON.parse(e.detail);
    
    if (consents.indexOf('google-analytics') !== -1) {
        initGoogleAnalytics();
    }
    
    if (consents.indexOf('facebook-pixel') !== -1) {
        initFacebookPixel();
    }
    
    if (consents.indexOf('matomo') !== -1) {
        initMatomo();
    }
});
```

---

### 6. Custom Consent-Button

```html
<!-- Link mit Data-Attribut (empfohlen) -->
<a href="#" data-consent-action="settings" class="btn btn-primary">
    Cookie-Einstellungen ändern
</a>

<!-- Legacy: Link mit onclick -->
<button onclick="consent_manager_showBox(); return false;" class="btn btn-secondary">
    Einstellungen
</button>

<!-- Mit Reload -->
<a href="#consent" data-consent-action="settings,reload">
    Einstellungen ändern und Seite neu laden
</a>
```

---

### 7. Programmatische Consent-Prüfung

```php
<?php
use FriendsOfRedaxo\ConsentManager\ConsentManager;

// Alle Services mit Consent
$cookies = ConsentManager::getCookies();
foreach ($cookies as $uid => $service) {
    if (\FriendsOfRedaxo\ConsentManager\Utility::has_consent($uid)) {
        echo "✓ Consent für {$service['service_name']} erteilt<br>";
    } else {
        echo "✗ Consent für {$service['service_name']} nicht erteilt<br>";
    }
}
?>
```

---

## Migration & Deprecated APIs

### Alte Namensgebung (< 5.0)

Falls noch alte Klassen-Namen im Code:

```php
// ALT (deprecated)
consent_manager_frontend::getFragment();
consent_manager_util::has_consent();

// NEU (seit 5.0)
\FriendsOfRedaxo\ConsentManager\Frontend::getFragment();
\FriendsOfRedaxo\ConsentManager\Utility::has_consent();
```

**Hinweis:** Die alten Klassen-Namen funktionieren noch (via `lib/deprecated.php`), sollten aber ersetzt werden.

---

## Debugging

### Debug-Modus aktivieren

```javascript
// Im Template vor dem Consent Manager Script
<script>
window.consentManagerDebugConfig = {
    debug_enabled: true
};
</script>
```

**Output in Browser-Konsole:**
```
Consent Manager: Script loaded
Consent Manager: Lazy loading enabled
Consent Manager: Loading content via API...
Consent Manager: Content loaded successfully
```

---

### Backend Debug

```php
<?php
if (rex::isDebugMode()) {
    dump(\FriendsOfRedaxo\ConsentManager\Cache::read());
    dump(\FriendsOfRedaxo\ConsentManager\ConsentManager::getCookies());
}
?>
```

---

## Best Practices

1. **Verwende Data-Attribute statt Legacy-Klassen:**
   ```html
   <!-- Gut -->
   <a href="#" data-consent-action="settings">Einstellungen</a>
   
   <!-- Veraltet (funktioniert noch) -->
   <a href="#" class="consent_manager-show-box">Einstellungen</a>
   ```

2. **Lazy Loading nutzen:**
   - Aktiviere Lazy Loading im Backend für bessere Performance
   - Reduziert initiales JavaScript-Payload um ~40%

3. **Server-seitige Consent-Prüfung:**
   ```php
   // Server-seitig prüfen (verhindert Flash of Unconsented Content)
   if (Utility::has_consent('google-analytics')) {
       echo '<script>/* GA */</script>';
   }
   ```

4. **Event-basierte Initialisierung:**
   ```javascript
   // Services erst nach Consent laden
   document.addEventListener('consent_manager-saved', function(e) {
       // Services initialisieren
   });
   ```

5. **Google Consent Mode v2:**
   - Automatisches Mapping aktivieren (Backend)
   - Consent-Flags werden automatisch an Google übermittelt

---

## Support & Weitere Informationen

- **GitHub:** https://github.com/FriendsOfREDAXO/consent_manager
- **REDAXO Forum:** https://friendsofredaxo.slack.com
- **Dokumentation:** Siehe `README.md` und `docs/` Ordner

---

*Stand: Version 5.4.0 | Januar 2025*
