# Inline Consent - Bedarfsgerechter Consent für einzelne Medien

Das **Inline Consent System** ermöglicht es, Consent nicht global für die gesamte Website abzufragen, sondern gezielt nur dort, wo externe Inhalte (YouTube-Videos, Google Maps, etc.) eingebunden werden.

## 🎯 Anwendungsfälle

- **YouTube/Vimeo Videos**: Consent nur beim Klick auf Video-Platzhalter
- **Google Maps**: Karten werden erst nach individueller Zustimmung geladen
- **Social Media Embeds**: Twitter, Facebook, Instagram Posts mit individuellem Consent
- **Domain-spezifische Einstellungen**: Verschiedene Domains können unterschiedliche Modi verwenden

---

## 🔧 Einrichtung

### 1. Domain-spezifischen Inline-Only-Modus aktivieren

1. **Backend öffnen**: `Consent Manager` → `Domains`
2. **Domain bearbeiten** oder neue Domain anlegen
3. **Inline-Only-Modus** auf `Aktiviert` setzen
4. **Speichern**

> **Hinweis**: Wenn der Inline-Only-Modus aktiviert ist, wird der globale Consent-Banner auf dieser Domain ausgeblendet!

**✅ Neue Features seit v4.5.0:**
- **Privacy Policy Links:** Automatische Anzeige von Datenschutzerklärungen (🔒 Datenschutzerklärung von [Anbieter])
- **Keine Confirm-Alerts:** Benutzerfreundliche direkte Aktivierung ohne Browser-Dialoge
- **Verbesserte Service-Erkennung:** Robuste SQL-Queries und Daten-Normalisierung

### 2. Services konfigurieren

1. **Services anlegen**: `Consent Manager` → `Cookies` → Service anlegen (z.B. "youtube")
2. **UID setzen**: Eindeutige Kennung (z.B. "youtube") 
3. **Service-Name**: Anzeigename (z.B. "YouTube")
4. **Datenschutzerklärung**: Privacy-Link des Anbieters

---

## 💻 Verwendung in Modulen

### Basis-Beispiel

```php
<?php
// CSS/JS einbinden
echo consent_manager_inline::getCSS();
echo consent_manager_inline::getJavaScript();

// Inline Consent für YouTube-Video
$videoId = 'dQw4w9WgXcQ'; // oder komplette URL
echo consent_manager_inline::doConsent('youtube', $videoId, [
    'title' => 'Rick Astley - Never Gonna Give You Up',
    'width' => 560,
    'height' => 315,
    'thumbnail' => 'auto' // Automatischer lokaler Cache
]);
// ✅ Zeigt automatisch "🔒 Datenschutzerklärung von Google" wenn im Service konfiguriert
?>
```

### Erweiterte Optionen

```php
<?php
echo consent_manager_inline::doConsent('youtube', $videoId, [
    'title' => 'Video-Titel',
    'placeholder_text' => 'Video laden',
    'privacy_notice' => 'Für die Anzeige werden YouTube-Cookies benötigt.',
    'width' => 800,
    'height' => 450,
    'show_allow_all' => true, // Optionaler "Alle erlauben" Button
    'thumbnail' => 'auto', // oder direkte URL zu eigenem Bild
    'icon' => 'uk-icon:play-circle', // Haupt-Icon (FontAwesome oder UIkit)
    'icon_label' => 'YouTube Video starten', // Barrierefreiheit
    'privacy_icon' => 'fa fa-shield-alt' // Privacy-Link Icon
]);
?>
```

### Unterstützte Services

| Service | Handler | Beschreibung |
|---------|---------|--------------|
| `youtube` | Speziell | Automatische Video-ID-Erkennung, Thumbnail-Cache |
| `vimeo` | Speziell | Vimeo-API-Integration, Thumbnail-Cache |
| `google-maps` | Speziell | Google Maps Embed-Handler |
| Andere | Generisch | Universeller Handler für beliebige externe Inhalte |

### Beliebige iframes / Externe Inhalte

Für beliebige externe Inhalte (Twitter, Instagram, andere iframes):

```php
<?php
// CSS/JS einbinden
echo consent_manager_inline::getCSS();
echo consent_manager_inline::getJavaScript();

// Beispiel: OpenStreetMap Karte (Leaflet)
$mapEmbed = '<div id="osm-map-' . uniqid() . '" style="height: 400px;"></div>
<script>
var map = L.map("osm-map-' . uniqid() . '").setView([52.5200, 13.4050], 13);
L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "&copy; OpenStreetMap contributors"
}).addTo(map);
L.marker([52.5200, 13.4050]).addTo(map)
    .bindPopup("Berlin, Deutschland");
</script>';

echo consent_manager_inline::doConsent('openstreetmap', $mapEmbed, [
    'title' => 'Interaktive Karte anzeigen',
    'placeholder_text' => 'Karte laden',
    'privacy_notice' => 'Die Karte lädt Daten von OpenStreetMap-Servern.',
    'width' => '100%',
    'height' => 400,
    'icon' => 'fas fa-map', // FontAwesome Icon
    'service_name' => 'OpenStreetMap',
    'thumbnail' => '/assets/custom/map-berlin-preview.jpg'
]);

// Beispiel: Google Maps iframe
$mapsEmbed = '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d..."
                    width="600" height="450" style="border:0;" 
                    allowfullscreen="" loading="lazy"></iframe>';

echo consent_manager_inline::doConsent('google-maps', $mapsEmbed, [
    'title' => 'Karte von Berlin anzeigen',
    'placeholder_text' => 'Karte laden',
    'privacy_notice' => 'Für Google Maps werden Tracking-Cookies verwendet.',
    'icon' => 'fas fa-map-marker-alt',
    'service_name' => 'Google Maps',
    'thumbnail' => '/assets/custom/map-preview.jpg' // Eigenes Vorschaubild
]);

// Beispiel: CodePen Embed
$codePenEmbed = '<iframe height="400" style="width: 100%;" scrolling="no" 
    title="CSS Animation Demo" 
    src="https://codepen.io/username/embed/abcdef?height=400&theme-id=dark&default-tab=result" 
    frameborder="no" loading="lazy" allowtransparency="true" allowfullscreen="true">
</iframe>';

echo consent_manager_inline::doConsent('codepen', $codePenEmbed, [
    'title' => 'CSS Animation Demo',
    'placeholder_text' => 'CodePen Demo laden', 
    'privacy_notice' => 'CodePen kann Nutzungsdaten erfassen.',
    'icon' => 'fab fa-codepen',
    'service_name' => 'CodePen',
    'width' => '100%',
    'height' => 400,
    'thumbnail' => '/assets/custom/codepen-preview.png'
]);

// Beispiel: Beliebiger iframe (z.B. externe Tools)
$customIframe = '<iframe src="https://example.com/widget" 
                         width="100%" height="300" frameborder="0"></iframe>';

echo consent_manager_inline::doConsent('external-widget', $customIframe, [
    'title' => 'Externes Widget laden',
    'placeholder_text' => 'Widget aktivieren',
    'privacy_notice' => 'Das externe Widget kann Daten übertragen.',
    'icon' => 'fas fa-external-link-alt',
    'service_name' => 'Externes Tool',
    'thumbnail' => '/assets/custom/widget-preview.png'
]);
?>
```

**Wichtige Punkte für beliebige Inhalte:**

1. **Service-Konfiguration**: Für jeden externen Dienst muss ein Service im Backend angelegt werden
2. **Consent-Verhalten**: Der `$serviceKey` muss mit der Service-UID übereinstimmen  
3. **Flexible Inhalte**: Der zweite Parameter kann beliebiger HTML-Code sein
4. **JavaScript-Integration**: Scripts und interaktive Inhalte werden korrekt geladen
5. **Eigene Icons**: FontAwesome-Icons oder eigene Bilder als Platzhalter
6. **Custom Thumbnails**: Eigene Vorschaubilder für bessere UX
7. **Responsive Design**: Flexible Breiten mit `width: '100%'` möglich

---

## 🎨 Design-Anpassung

### Fragment überschreiben

Das komplette Markup kann über ein Fragment angepasst werden:

**Datei**: `/redaxo/templates/consent_inline_placeholder.php`

```php
<?php
// Variablen aus Fragment abrufen
$serviceKey = $this->getVar('serviceKey', '');
$consentId = $this->getVar('consentId', ''); 
$options = $this->getVar('options', []);
$placeholderData = $this->getVar('placeholderData', []);
$content = $this->getVar('content', '');

// Thumbnail-HTML generieren
$thumbnailHtml = '';
if (!empty($placeholderData['thumbnail'])) {
    $thumbnailHtml = '<img src="' . rex_escape($placeholderData['thumbnail']) . '" 
                           alt="' . rex_escape($options['title'] ?? 'Video') . '" 
                           class="my-custom-thumbnail" 
                           loading="lazy" />';
}
?>

<!-- Eigenes Design -->
<div class="my-consent-container" data-consent-id="<?= rex_escape($consentId) ?>" 
     data-service="<?= rex_escape($serviceKey) ?>">
    
    <div class="my-placeholder">
        <?= $thumbnailHtml ?>
        
        <div class="my-overlay">
            <h3><?= rex_escape($options['title'] ?? 'Inhalt laden') ?></h3>
            <p><?= rex_escape($options['privacy_notice'] ?? 'Für die Anzeige werden Cookies benötigt.') ?></p>
            
            <button type="button" class="my-button consent-inline-accept" 
                    data-consent-id="<?= rex_escape($consentId) ?>"
                    data-service="<?= rex_escape($serviceKey) ?>">
                <?= rex_escape($options['placeholder_text'] ?? 'Laden') ?>
            </button>
        </div>
    </div>
    
    <script type="text/plain" class="consent-content-data" 
            data-consent-code="<?= rex_escape($serviceKey) ?>">
        <?= $content ?>
    </script>
</div>
```

### CSS-Anpassung mit Custom Properties

Das System verwendet **CSS Custom Properties (Variablen)** für maximale Flexibilität:

#### **Methode 1: CSS-Variablen überschreiben**

```css
/* Einfache Anpassung über CSS-Variablen */
.consent-inline-placeholder {
    /* Design anpassen */
    --consent-bg-color: #2c3e50; 
    --consent-border-color: #3498db;
    --consent-border-radius: 15px;
    --consent-min-height: 400px;
    
    /* Overlay Design */
    --consent-overlay-bg: rgba(52, 152, 219, 0.95);
    --consent-overlay-border-radius: 15px;
    --consent-overlay-shadow: 0 8px 25px rgba(0,0,0,0.3);
    
    /* Button Colors */
    --consent-btn-accept-bg: #e74c3c;
    --consent-btn-accept-hover-bg: #c0392b;
    --consent-btn-details-bg: #95a5a6;
    
    /* Typography */
    --consent-icon-size: 4rem;
    --consent-icon-color: #3498db;
    --consent-title-size: 1.5rem;
    --consent-notice-color: #ecf0f1;
}
```

#### **Methode 2: Komplett eigenes CSS**

```php
<?php
// Standard-CSS deaktivieren und eigenes verwenden
echo consent_manager_inline::getCSS(true, '/assets/my-consent-styles.css');

// Oder Fragment im templates-Ordner: consent_inline_styles.css
echo consent_manager_inline::getCSS(true);
?>
```

#### **Verfügbare CSS-Variablen**

| Variable | Standard | Beschreibung |
|----------|----------|--------------|
| `--consent-bg-color` | `#f8f9fa` | Container-Hintergrund |
| `--consent-border-color` | `#dee2e6` | Rahmenfarbe |
| `--consent-border-radius` | `8px` | Ecken-Rundung |
| `--consent-min-height` | `300px` | Mindesthöhe (250px mobil) |
| `--consent-overlay-bg` | `rgba(255,255,255,0.95)` | Overlay-Hintergrund |
| `--consent-overlay-shadow` | `0 4px 6px rgba(0,0,0,0.1)` | Schatten |
| `--consent-btn-accept-bg` | `#28a745` | Accept-Button Farbe |
| `--consent-btn-details-bg` | `#6c757d` | Details-Button Farbe |
| `--consent-icon-size` | `3rem` | Icon-Größe |
| `--consent-icon-color` | `#6c757d` | Icon-Farbe |
| `--consent-title-size` | `1.25rem` | Titel-Schriftgröße |

#### **Theme-Beispiele**

**Dark Theme:**
```css
.consent-inline-placeholder {
    --consent-bg-color: #2c3e50;
    --consent-overlay-bg: rgba(44, 62, 80, 0.95);
    --consent-btn-accept-bg: #e74c3c;
    --consent-icon-color: #ecf0f1;
    --consent-notice-color: #bdc3c7;
}
```

**Corporate Theme:**
```css
.consent-inline-placeholder {
    --consent-border-color: #your-brand-color;
    --consent-btn-accept-bg: #your-brand-color;
    --consent-overlay-shadow: 0 10px 40px rgba(0,0,0,0.15);
    --consent-border-radius: 0; /* Eckige Form */
}
```

### Icon-Anpassung (FontAwesome & UIkit)

Das System unterstützt sowohl **FontAwesome** als auch **UIkit Icons** mit voller Barrierefreiheit:

```php
<?php
// FontAwesome Icons
echo consent_manager_inline::doConsent('youtube', $videoId, [
    'icon' => 'fab fa-youtube',
    'icon_label' => 'YouTube Video',
    'privacy_icon' => 'fa fa-shield-alt'
]);

// UIkit Icons
echo consent_manager_inline::doConsent('youtube', $videoId, [
    'icon' => 'uk-icon:play-circle',
    'icon_label' => 'Video abspielen',
    'privacy_icon' => 'uk-icon:shield'
]);

// Gemischte Nutzung möglich
echo consent_manager_inline::doConsent('maps', $mapEmbed, [
    'icon' => 'uk-icon:location',     // UIkit für Haupticon
    'privacy_icon' => 'fa fa-lock'      // FontAwesome für Privacy
]);
?>
```

**Standard-Icons:**
- **YouTube**: `uk-icon:play-circle`
- **Google Maps**: `uk-icon:location` 
- **Generic**: `fa fa-external-link-alt`
- **Privacy Links**: `uk-icon:shield`

**Barrierefreiheit:**
- **FontAwesome**: `aria-hidden="true"` + versteckter Screen Reader Text
- **UIkit**: `aria-label` für semantische Beschreibung
- **Icon Labels**: Individuelle Beschreibungen per `icon_label`

---

## 🔒 Datenschutz-Features

### Lokaler Thumbnail-Cache

YouTube- und Vimeo-Thumbnails werden automatisch lokal gespeichert:

- **Keine externen Requests** beim Seitenaufruf
- **7-Tage-Cache** für Thumbnails
- **Automatische Bereinigung** via Cronjob
- **SVG-Fallback** wenn Download fehlschlägt

### Cache-Verwaltung

```php
<?php
// Cache manuell bereinigen (Dateien älter als 30 Tage)
consent_manager_thumbnail_cache::cleanupCache(30 * 24 * 60 * 60);

// YouTube-Thumbnail manuell cachen
$localUrl = consent_manager_thumbnail_cache::cacheYouTubeThumbnail('dQw4w9WgXcQ');
?>
```

### Cronjob einrichten

Für automatische Cache-Bereinigung:

1. **Backend**: `System` → `Cronjobs` 
2. **Neuen Cronjob erstellen**
3. **Typ**: `Consent Manager Thumbnail Cache bereinigen`
4. **Intervall**: Täglich oder wöchentlich

---

## 🚀 Erweiterte Features

### Automatische Platzhalter-Ersetzung

Nach globaler Consent-Erteilung werden alle Inline-Platzhalter automatisch ersetzt:

- **Event-basiert**: Reagiert auf Consent Manager Events
- **Cookie-Monitoring**: Überwacht Cookie-Änderungen
- **DOM-Mutation-Observer**: Erkennt UI-Änderungen
- **Fallback-Timer**: Regelmäßige Prüfung als Backup

### Multi-Event-System

Das System hört auf verschiedene Events:

```javascript
// Standard Consent Manager Events
document.addEventListener('consent_manager_consent_given', handler);
document.addEventListener('consent_manager_updated', handler);

// Alternative Event-Namen (verschiedene Versionen)
document.addEventListener('consent-manager-consent-given', handler);
document.addEventListener('consentGiven', handler);
```

### Debug-Modus

Im Debug-Modus werden detaillierte Informationen ausgegeben:

```php
<?php
// Debug aktivieren in REDAXO-Config
rex::setProperty('debug', true);
?>
```

**Debug-Ausgaben enthalten:**
- Fragment-Variablen (serviceKey, consentId, options, placeholderData)
- Service-Daten aus Datenbank (provider_link_privacy, provider, etc.)
- Cookie-Parsing-Ergebnisse  
- Service-Erkennung und SQL-Query-Results
- Event-Triggering und Update-Zyklen
- Privacy Policy Link Generierung

**Hinweis:** Debug-Ausgaben erscheinen nur bei `rex::isDebugMode() === true` und sind **nicht** permanent sichtbar.

---

## 🛠️ Technische Details

### Wichtige CSS-Klassen

| Klasse | Zweck |
|--------|-------|
| `.consent-inline-container` | Haupt-Container |
| `.consent-inline-accept` | Accept-Button (Event-Handler) |
| `.consent-inline-details` | Details-Button |
| `.consent-inline-privacy-link` | Privacy Policy Link Container |
| `.consent-inline-icon` | Icon-Container (FontAwesome/UIkit) |
| `.sr-only` | Screen Reader Text (Barrierefreiheit) |
| `.consent-content-data` | Script-Tag mit Original-Content |
| `.consent-inline-placeholder` | Platzhalter-Container |
| `.consent-inline-overlay` | Overlay mit Buttons und Texten |

### JavaScript-API

```javascript
// Manuell alle Platzhalter aktualisieren
consentManagerInline.updateAllPlaceholders();

// Cookie-Daten abrufen
var cookieData = consentManagerInline.getCookieData();

// Content für spezifischen Container laden
consentManagerInline.loadContent(containerElement);
```

### Fragment-Variablen

| Variable | Typ | Beschreibung |
|----------|-----|--------------|
| `serviceKey` | string | Service-UID (z.B. 'youtube') |
| `consentId` | string | Eindeutige Consent-ID |
| `options` | array | Konfigurationsoptionen (icon, icon_label, privacy_icon) |
| `placeholderData` | array | Icon, Icon-Label, Thumbnail, Service-Name |
| `content` | string | Original-Content zum Laden |
| `privacy_icon` | string | Icon für Privacy-Links (FontAwesome/UIkit) |

---

## 📞 Support

Bei Fragen oder Problemen:

1. **Debug-Modus aktivieren** und Console-Logs prüfen
2. **Fragment-Debug** zeigt alle übertragenen Variablen
3. **Community-Support** im REDAXO-Slack-Channel #consent_manager

## 🔗 Weitere Ressourcen  

- [Consent Manager Hauptdokumentation](help)
- [Google Consent Mode Integration](#)
- [Theme-Anpassung](#)
- [Changelog](changelog)