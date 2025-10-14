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
    'thumbnail' => 'auto' // oder direkte URL zu eigenem Bild
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

### CSS-Anpassung

Das Standard-CSS kann überschrieben werden:

```css
/* Eigene Styles für Inline-Consent */
.consent-inline-container {
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    overflow: hidden;
}

.consent-inline-overlay {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.consent-inline-accept {
    background: #10b981;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.consent-inline-accept:hover {
    background: #059669;
    transform: translateY(-2px);
}
```

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
| `options` | array | Konfigurationsoptionen |
| `placeholderData` | array | Icon, Thumbnail, Service-Name |
| `content` | string | Original-Content zum Laden |

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