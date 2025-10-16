# Inline Consent - Bedarfsgerechter Consent für einzelne Medien

Das **Inline Consent System** ermöglicht Consent gezielt nur dort abzufragen, wo externe Inhalte eingebunden werden - statt global für die gesamte Website.

## 🔧 Einrichtung

### 1. Domain konfigurieren
`Consent Manager` → `Domains` → **Inline-Only-Modus** auf `Aktiviert` setzen
> Blendet den globalen Consent-Banner aus!

### 2. Services anlegen  
`Consent Manager` → `Cookies` → Service mit eindeutiger UID anlegen (z.B. "youtube")

---

## 💻 Grundlegende Verwendung

```php
<?php
// Basis-Beispiel (2-Button-Modus)
echo consent_manager_inline::doConsent('youtube', $videoId, [
    'title' => 'Mein Video'
]);

// 3-Button-Modus mit "Alle erlauben"
echo consent_manager_inline::doConsent('youtube', $videoId, [
    'title' => 'Mein Video',
    'show_allow_all' => true
]);
?>
```

**CSS/JS einbinden (erforderlich):**
Das CSS/JS muss einmalig pro Seite eingefügt werden:

```php
<?php
// Im Template oder einmalig im Modul
echo consent_manager_inline::getCSS();
echo consent_manager_inline::getJavaScript();

// Schutz vor doppeltem Laden: 
// - Mehrfache Aufrufe geben nur HTML-Kommentare zurück
// - Sicher bei mehreren Modulen mit Inline-Consent
?>
```

### Button-Modi

| Modus | Buttons | Verwendung |
|-------|---------|------------|
| **2-Button** | "Einmal laden" + "Einstellungen" | Einzelne Inhalte |
| **3-Button** | + "Alle erlauben" | Viele Inhalte desselben Services |

### Button-Verhalten

| Button | Aktion | Cookie |
|--------|--------|--------|
| **Einmal laden** | Nur dieser Inhalt | ❌ Kein Cookie |
| **Alle erlauben** | Alle Inhalte des Services | ✅ Service-Cookie gesetzt |
| **Einstellungen** | Öffnet Consent-Manager | ⚙️ Individuelle Konfiguration |

## 🎬 Content-Typen & Beispiele

### Automatische URL-Konvertierung (nur diese 3!)
| Service | Input | Verhalten |
|---------|-------|-----------|
| **YouTube** | Video-ID oder URL | ✅ Auto-Iframe + Thumbnail |
| **Vimeo** | Video-ID oder URL | ✅ Auto-Iframe + Thumbnail |
| **Google Maps** | Embed-URL | ✅ Auto-Iframe |
| **Alle anderen** | Beliebiger Code | Direct HTML Output |

### Beispiele

```php
<?php
// ✅ Automatik: YouTube/Vimeo/Maps
echo consent_manager_inline::doConsent('youtube', 'dQw4w9WgXcQ', [
    'title' => 'Rick Astley Video'
]);
echo consent_manager_inline::doConsent('google-maps', 'https://www.google.com/maps/embed?pb=...', [
    'title' => 'Standort-Karte'
]);

// ✅ YouTube mit UIkit Attributen
echo consent_manager_inline::doConsent('youtube', 'dQw4w9WgXcQ', [
    'title' => 'Rick Astley Video',
    'attributes' => [
        'class' => 'uk-responsive-width',
        'data-uk-responsive' => '',
        'data-uk-video' => 'automute: true'
    ]
]);

// ✅ Vimeo mit custom CSS Klasse
echo consent_manager_inline::doConsent('vimeo', '123456789', [
    'title' => 'Corporate Video',
    'attributes' => [
        'class' => 'responsive-video',
        'data-video-analytics' => 'tracked'
    ]
]);

// ✅ Iframes (vollständiger Code erforderlich)
$spotify = '<iframe src="https://open.spotify.com/embed/playlist/..." 
                    width="100%" height="380" frameborder="0"></iframe>';
echo consent_manager_inline::doConsent('spotify', $spotify, [
    'title' => 'Spotify Playlist'
]);

// ✅ JavaScript & Tracking
$analytics = '<script>gtag("config", "GA_MEASUREMENT_ID");</script>';
echo consent_manager_inline::doConsent('google-analytics', $analytics, [
    'title' => 'Analytics aktivieren'
]);

// ✅ Beliebiger HTML-Code
$widget = '<div style="padding:20px; background:#f0f0f0;">
    <h3>Custom Widget</h3>
    <button onclick="alert(\'Hello!\')">Klick mich</button>
    <script>console.log("Widget geladen!");</script>
</div>';
echo consent_manager_inline::doConsent('custom-widget', $widget, [
    'title' => 'Widget aktivieren'
]);
?>
```

## ⚙️ Konfiguration

### Verfügbare Optionen
```php
echo consent_manager_inline::doConsent('service-key', $content, [
    // Button-Konfiguration
    'show_allow_all' => true,                    // 3-Button-Modus
    'placeholder_text' => 'Einmal laden',        // Button-Text
    
    // Anzeige
    'title' => 'Video-Titel',                    // Überschrift
    'privacy_notice' => 'Datenschutz-Hinweis',  // Info-Text
    'width' => 800,                              // Breite
    'height' => 450,                             // Höhe
    
    // Design & Icons
    'thumbnail' => 'auto',                       // Vorschaubild (auto/URL)
    'icon' => 'fab fa-youtube',                  // FontAwesome/UIkit Icon
    'icon_label' => 'Video starten',             // Barrierefreiheit
    'privacy_icon' => 'uk-icon:shield',          // Privacy-Link Icon
    
    // Custom Attributes (YouTube/Vimeo)
    'attributes' => [                            // Zusätzliche HTML-Attribute
        'class' => 'my-video-class',
        'data-uk-responsive' => '',
        'data-uk-video' => 'automute: true',
        'data-video-id' => '12345'
    ]
]);
```

**Wichtig:** Jeder `service-key` muss als Service im Backend angelegt sein!

---

## 🎨 Design-Anpassung

### CSS-Variablen überschreiben
```css
.consent-inline-placeholder {
    --consent-bg-color: #2c3e50;
    --consent-border-color: #3498db;
    --consent-btn-accept-bg: #e74c3c;
    --consent-overlay-bg: rgba(52, 152, 219, 0.95);
    --consent-icon-size: 4rem;
}
```

### Fragment überschreiben
**Datei**: `/redaxo/templates/consent_inline_placeholder.php` für komplette Markup-Anpassung

### Icon-System
- **FontAwesome**: `'icon' => 'fab fa-youtube'`
- **UIkit**: `'icon' => 'uk-icon:play-circle'`
- **Barrierefreiheit**: `'icon_label' => 'Video starten'`

## 🔒 Datenschutz & Features

### Thumbnail-Cache (YouTube/Vimeo)
- **Lokale Speicherung**: Keine externen Requests beim Seitenaufruf
- **Automatische Bereinigung**: 7-Tage-Cache via Cronjob
- **SVG-Fallback**: Bei Download-Fehlern

### Mehrsprachigkeit
Button-Texte über `Consent Manager` → `Texte` anpassbar:
- `inline_placeholder_text`: "Einmal laden"
- `button_inline_allow_all`: "Alle erlauben"  
- `button_inline_details`: "Einstellungen"

### Event-System
Automatische Platzhalter-Aktualisierung via:
- Consent Manager Events
- Cookie-Monitoring  
- DOM-Mutation-Observer
- Fallback-Timer

### Debug-Modus
`rex::setProperty('debug', true)` zeigt detaillierte Informationen zu Fragment-Variablen, Service-Daten und Cookie-Parsing.

---

## 🛠️ Technische Referenz

### CSS-Klassen (Event-Handler)
- `.consent-inline-once` → "Einmal laden" Button
- `.consent-inline-allow-all` → "Alle erlauben" Button  
- `.consent-inline-details` → "Einstellungen" Button
- `.consent-content-data` → Original-Content Container

### JavaScript-API
```javascript
consentManagerInline.updateAllPlaceholders();
consentManagerInline.getCookieData();
consentManagerInline.loadContent(container);
```