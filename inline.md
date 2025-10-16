# Consent Manager - Inline-Consent System

## 🌟 Überblick

Das **Inline-Consent-System** ermöglicht es, Inhalte von externen Diensten (YouTube, Vimeo, Google Maps, etc.) mit einem eleganten Platzhalter anzuzeigen und erst nach Consent zu laden.

 Consent nur bei Bedarf - perfekt für Seiten mit wenigen externen Inhalten.

---

## 🚀 Schnellstart Inline-Consent

### Problem und Lösung

**Problem:** 400 Artikel, aber nur 2 brauchen YouTube-Videos. Normale Consent-Banner nerven alle Besucher, obwohl 99% nie Videos sehen.

**Lösung:** Inline-Consent zeigt Platzhalter statt Videos. Der Consent-Dialog erscheint erst beim Klick auf "Video laden". (Keine Sorge: Rickrolls sind optional.)

### ⚠️ Wichtige Funktionsweise des Inline-Modus

**Medienspezifischer Consent:**
- ✅ **Inline-Consent aktiviert NUR das angeklickte Medium**
- ✅ Jedes Video/Embed wird **einzeln** freigeschaltet
- ✅ **Keine globale Aktivierung** aller Services einer Gruppe
- ✅ Maximaler Datenschutz durch minimale Consent-Erteilung

**Beispiel:** Klick auf "YouTube Video laden" → Nur dieses eine Video wird geladen, andere YouTube-Videos auf der Seite bleiben gesperrt.

**Globale Aktivierung über "Alle Einstellungen":**
- Der Button **"Alle Einstellungen"** (früher "Cookie-Details") öffnet das vollständige Consent-Manager-Fenster
- Dort lassen sich **alle Services einer Gruppe** global aktivieren
- **Hinweis:** Button-Texte sind über die **Texte-Verwaltung** vollständig anpassbar und übersetzbar

Optional steht eine Drei-Button-Variante zur Verfügung:
- "Einmal laden" (nur dieses Element)
- "Alle zulassen" (alle Services der betroffenen Gruppe aktivieren)
- "Alle Einstellungen" (vollständige Übersicht öffnen)

Aktivierung im Code (pro Element):

```php
echo doConsent('youtube', 'VIDEO_ID_ODER_URL', [
    'title' => 'Mein Video',
    'show_allow_all' => true // Drei-Button-Variante einschalten
]);
```

Hinweis: Der Button-Text wird über den Schlüssel `button_inline_allow_all` gesteuert (Texte-Verwaltung). Standard: „Alle zulassen“.

---

## 🎯 Grundlegende Verwendung

### YouTube-Videos einbetten

```php
<?php
// Template/Modul - statt direktem iframe
echo doConsent('youtube', 'dQw4w9WgXcQ', [
    'title' => 'Rick Astley - Never Gonna Give You Up',
    'width' => 560,
    'height' => 315
]);

// Funktioniert auch mit kompletten URLs
echo doConsent('youtube', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', [
    'title' => 'Mein Video'
]);

// Mit custom Attributen (z.B. für UIkit, Bootstrap, etc.)
echo doConsent('youtube', 'dQw4w9WgXcQ', [
    'title' => 'Responsive YouTube Video',
    'width' => 560,
    'height' => 315,
    'attributes' => [
        'class' => 'uk-responsive-width',
        'data-uk-video' => 'automute: true',
        'data-uk-responsive' => '' // Leere Werte werden als boolean attributes gerendert
    ]
]);

// Erweiterte Optionen
echo consent_manager_inline::doConsent('youtube', 'dQw4w9WgXcQ', [
    'title' => 'Video laden',
    'placeholder_text' => 'Video abspielen',
    'privacy_notice' => 'Für YouTube werden Tracking-Cookies verwendet.',
    'show_allow_all' => true
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
echo doConsent('google-maps', 'https://www.google.com/maps/embed?pb=!1m18!1m12...', [
    'title' => 'Unsere Adresse',
    'height' => 450
]);
?>
```

### Vimeo-Videos

```php
<?php
echo doConsent('vimeo', '123456789', [
    'title' => 'Mein Vimeo Video',
    'width' => 640,
    'height' => 360
]);

// Oder mit URL
echo doConsent('vimeo', 'https://vimeo.com/123456789', [
    'title' => 'Corporate Video'
]);

// Mit custom CSS Klassen und data-Attributen
echo doConsent('vimeo', '123456789', [
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
// Beliebige iframes
echo doConsent('custom-iframe', '<iframe src="https://example.com/widget"></iframe>', [
    'title' => 'External Widget',
    'privacy_notice' => 'Dieses Widget setzt Cookies für Funktionalität.'
]);

// JavaScript-Code
echo doConsent('google-analytics', '<script>gtag("config", "GA_MEASUREMENT_ID");</script>', [
    'title' => 'Google Analytics',
    'placeholder_text' => 'Analytics aktivieren'
]);
?>
```

---

## 🔌 Template-Integration

### CSS/JS einbinden (einmalig im Template)

```php
<?php echo rex_view::content('consent_manager_inline_cssjs.php'); ?>
```

### Oder manuell:

```html
<!-- Im <head>-Bereich -->
<?php
if (class_exists('consent_manager_inline')) {
    echo consent_manager_inline::getCSS();
    echo consent_manager_inline::getJavaScript();
}
?>
```

---

## ✨ Features der Inline-Lösung

**✅ Vollständige Integration:**
- Nutzt bestehende Service-Konfiguration
- Automatisches Logging aller Consent-Aktionen  
- **"Alle Einstellungen"-Button** öffnet das vollständige Consent-Manager-Fenster
- Optional: **Drei-Button-Variante** (Einmal laden, Alle zulassen, Alle Einstellungen)
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
- Ein `doConsent()` für alle Services
- Auto-Erkennung von Video-IDs aus URLs
- Flexible Optionen-Arrays
- Debug-Modus verfügbar

---

## 📄 Beispiel-Output

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

---

## 🆚 Vorteile gegenüber globalem Consent

| Global Consent | Inline Consent |
|----------------|----------------|
| ❌ Nervt alle Besucher | ✅ Nur bei tatsächlicher Nutzung |
| ❌ "Consent Fatigue" | ✅ Kontextuell und verständlich |
| ❌ Viele leere Zustimmungen | ✅ Bewusste Entscheidungen |
| ❌ Komplexe Setup für 2 Videos | ✅ Einfache Integration |

**Perfekt für:**
- Blogs mit gelegentlichen Videos
- Corporate Sites mit einzelnen Maps
- Landing Pages mit gezielten Embeds
- Alle Seiten wo < 10% der Inhalte Consent brauchen

---

## 🎨 CSS-Anpassungen

Das System verwendet **CSS Custom Properties** für maximale Flexibilität:

```css
:root {
    /* Hauptfarben */
    --consent-primary-color: #007bff;
    --consent-secondary-color: #6c757d;
    --consent-success-color: #28a745;
    
    /* Overlay */
    --consent-overlay-bg: rgba(0,0,0,0.8);
    --consent-overlay-padding: 2rem;
    --consent-overlay-border-radius: 12px;
    
    /* Buttons */
    --consent-button-border-radius: 8px;
    --consent-button-padding: 0.75rem 1.5rem;
    --consent-button-font-size: 1rem;
    
    /* Typography */
    --consent-title-font-size: 1.5rem;
    --consent-text-font-size: 1rem;
    
    /* Responsive */
    --consent-mobile-padding: 1rem;
    --consent-mobile-font-size: 0.9rem;
}
```

### Vordefinierte Themes:

```css
/* Dark Theme */
.consent-theme-dark {
    --consent-overlay-bg: rgba(33, 37, 41, 0.95);
    --consent-text-color: #ffffff;
    --consent-primary-color: #0d6efd;
}

/* Minimal Theme */
.consent-theme-minimal {
    --consent-overlay-bg: rgba(255, 255, 255, 0.98);
    --consent-overlay-border: 1px solid #dee2e6;
    --consent-overlay-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
```

---

## 🎯 Service-spezifische Handler und Optionen

### YouTube
```php
echo consent_manager_inline::doConsent('youtube', 'dQw4w9WgXcQ', [
    'width' => '560',
    'height' => '315',
    'thumbnail' => 'auto' // Automatisches Mediamanager-Caching
]);

// Oder mit eigener Thumbnail-URL
echo consent_manager_inline::doConsent('youtube', 'dQw4w9WgXcQ', [
    'thumbnail' => 'https://example.com/my-custom-thumb.jpg'
]);

// Oder mit Mediamanager-URL
echo consent_manager_inline::doConsent('youtube', 'dQw4w9WgXcQ', [
    'thumbnail' => rex_media_manager::getUrl('my_custom_type', 'my-thumb.jpg')
]);
```

### Vimeo
```php
echo consent_manager_inline::doConsent('vimeo', '123456789', [
    'width' => '640',
    'height' => '360',
    'thumbnail' => 'auto' // Automatisch über Mediamanager
]);
```

### Google Maps
```php
echo consent_manager_inline::doConsent('google-maps', 'EMBED_URL', [
    'width' => '600',
    'height' => '450',
    'title' => 'Karte laden'
]);
```

### Generisch
```php
echo consent_manager_inline::doConsent('custom-service', '<iframe src="..."></iframe>', [
    'title' => 'Externen Inhalt laden',
    'thumbnail' => '/media/preview.jpg'
]);
```

## 🖼️ Thumbnail-System mit Mediamanager

Das System nutzt den **REDAXO Mediamanager** für DSGVO-konforme Thumbnail-Verwaltung:

### ✅ Automatische Installation
Bei der AddOn-Installation wird automatisch erstellt:
- **Mediamanager-Type**: `consent_manager_thumbnail` (vollständig editierbar)
- **Effect 1**: `external_thumbnail` - Lädt YouTube/Vimeo Thumbnails automatisch herunter
- **Effect 2**: `resize` - Standardgröße 480x360px

### 🎛️ Vollständig anpassbar
Im **Mediamanager → Types → consent_manager_thumbnail** können Benutzer alles anpassen:
- **Thumbnail-Größe**: Resize-Effect bearbeiten (z.B. 1280x720 für HD)
- **Zusätzliche Effekte**: Crop, Filter, Wasserzeichen, Compress hinzufügen
- **Qualität**: JPEG-Qualität über Compress-Effect optimieren
- **Type-Name**: Umbenennung möglich für eigene Workflows

### 🔧 Funktionsweise
```php
// Automatisches Thumbnail-Caching
$thumbnailUrl = rex_consent_manager_thumbnail_mediamanager::getThumbnailUrl('youtube', 'dQw4w9WgXcQ');
// → https://example.com/media/consent_manager_thumbnail/youtube_dQw4w9WgXcQ_b279b658.jpg

// 1. Effect lädt Thumbnail von YouTube/Vimeo herunter
// 2. Mediamanager wendet weitere Effects an (Resize, etc.)
// 3. Optimiertes Thumbnail wird ausgeliefert und gecacht
// 4. Browser lädt NICHT direkt von YouTube = DSGVO-konform
```

### Eigene Thumbnail-URLs übergeben (exakt erklärt)

Je Element lässt sich ein individuelles Vorschaubild definieren. Unterstützt werden:

- Externe URL: `'thumbnail' => 'https://example.com/thumb.jpg'`
- Datei aus dem Medienpool (absoluter Pfad/URL): `'thumbnail' => '/media/thumb.jpg'`
- Mediamanager-URL: `'thumbnail' => rex_media_manager::getUrl('type', 'file.jpg')`

Beispiele:

```php
// 1) Externe Bild-URL (einfach, aber datenschutzrechtlich abwägen)
echo consent_manager_inline::doConsent('youtube', 'dQw4w9WgXcQ', [
    'title' => 'Video laden',
    'thumbnail' => 'https://cdn.example.com/previews/rick.jpg'
]);

// 2) Medienpool-Datei (lokal gehostet)
echo consent_manager_inline::doConsent('youtube', 'dQw4w9WgXcQ', [
    'thumbnail' => '/media/video_previews/rick.jpg'
]);

// 3) Mediamanager (empfohlen): zentral skalieren/optimieren
echo consent_manager_inline::doConsent('youtube', 'dQw4w9WgXcQ', [
    'thumbnail' => rex_media_manager::getUrl('consent_manager_thumbnail', 'rick.jpg')
]);
```

Hinweise:

- `'thumbnail' => 'auto'` nutzt – wo verfügbar (YouTube/Vimeo) – das automatische, lokale Caching via Mediamanager.
- Externe Thumbnail-Quellen können sofort vom Browser geladen werden. Für maximale DSGVO-Konformität lieber lokale Dateien oder den Mediamanager verwenden.
- Größe/Qualität zentral im Mediamanager-Type steuern (z. B. `consent_manager_thumbnail`).

## 🔧 Konfigurationsoptionen

### Basis-Optionen
```php
$options = [
    'title' => 'Titel des Platzhalters',
    'placeholder_text' => 'Button-Text',
    'privacy_notice' => 'Datenschutz-Hinweis',
    'show_allow_all' => true, // "Alle zulassen" Button
    'width' => '640',
    'height' => '360',
    
    // Verschiedene Thumbnail-Optionen:
    'thumbnail' => 'auto',                                    // Automatisch über Mediamanager
    // 'thumbnail' => '/media/my-thumb.jpg',                  // Lokale Datei
    // 'thumbnail' => 'https://example.com/thumb.jpg',        // Externe URL
    // 'thumbnail' => rex_media_manager::getUrl('type', 'file.jpg'), // Mediamanager-URL
    
    // Custom Attribute für iframe/embed
    'attributes' => [
        'class' => 'my-custom-class',
        'data-custom' => 'value',
        'id' => 'unique-id',
        'data-boolean' => '' // Leere Werte = boolean attributes (ohne ="")
    ]
];
```

### Icon-System
```php
$options = [
    'privacy_icon' => 'uk-icon:shield', // UIkit Icons
    'privacy_icon' => 'fa fa-shield',   // FontAwesome
    'privacy_icon' => '🛡️',            // Emoji (Fallback)
];
```

### Erweiterte Optionen
```php
$options = [
    'css_class' => 'custom-consent-style',
    'container_id' => 'unique-consent-id',
    'auto_height' => true,
    'responsive' => true,
    'fade_in' => true
];
```

## 🧾 Referenz: Optionen für `consent_manager_inline::doConsent()`

Folgende Optionen werden im dritten Parameter (Array) unterstützt:

### title
- Typ: string
- Standard: Text-Schlüssel `inline_title_fallback`
- Beschreibung: Überschrift/Titel im Platzhalter.
- Beispiel: `'title' => 'Mein Video'`

### placeholder_text
- Typ: string
- Standard: Text-Schlüssel `inline_placeholder_text`
- Beschreibung: Text des Haupt-Buttons (z. B. „Einmal laden“).
- Beispiel: `'placeholder_text' => 'Video abspielen'`

### privacy_notice
- Typ: string
- Standard: Text-Schlüssel `inline_privacy_notice`
- Beschreibung: Kurzer Datenschutz-Hinweis im Overlay.
- Beispiel: `'privacy_notice' => 'Für YouTube werden Cookies benötigt.'`

### show_allow_all
- Typ: bool
- Standard: `false`
- Beschreibung: Aktiviert die Drei-Button-Variante (Einmal laden, Alle zulassen, Alle Einstellungen).
- Beispiel: `'show_allow_all' => true`

### width
- Typ: int|string
- Standard: –
- Beschreibung: Breite des Embeds/Platzhalters (z. B. `560` oder `'100%'`).
- Beispiel: `'width' => 560`

### height
- Typ: int|string
- Standard: –
- Beschreibung: Höhe des Embeds/Platzhalters (z. B. `315` oder `'360'`).
- Beispiel: `'height' => 315`

### thumbnail
- Typ: string
- Standard: automatisch/abhängig vom Service
- Beschreibung: Vorschaubild-Quelle. Zulässig: `'auto'`, externe URL, Medienpool-Pfad oder Mediamanager-URL.
- Beispiel: `'thumbnail' => 'auto'`

### attributes
- Typ: array<string,string>
- Standard: `[]`
- Beschreibung: Zusätzliche iframe-Attribute. Leere Werte werden als Boolean-Attribute ohne `=""` gerendert.
- Beispiel: `'attributes' => ['loading' => 'lazy', 'allowfullscreen' => '']`

### css_class
- Typ: string
- Standard: `''`
- Beschreibung: Zusätzliche CSS-Klasse(n) für den Platzhalter-Container.
- Beispiel: `'css_class' => 'consent-theme-minimal'`

### container_id
- Typ: string
- Standard: automatisch
- Beschreibung: Feste ID für den Container (nützlich für Tests oder direkte Referenzen).
- Beispiel: `'container_id' => 'video-42'`

### auto_height
- Typ: bool
- Standard: –
- Beschreibung: Automatische Höhenanpassung (je nach Service/Theme).
- Beispiel: `'auto_height' => true`

### responsive
- Typ: bool
- Standard: –
- Beschreibung: Aktiviert responsive Darstellung (service-/themeabhängig).
- Beispiel: `'responsive' => true`

### fade_in
- Typ: bool
- Standard: –
- Beschreibung: Blend-Effekt beim Laden des Inhalts.
- Beispiel: `'fade_in' => true`

### privacy_icon
- Typ: string
- Standard: –
- Beschreibung: Icon neben dem Datenschutzhinweis (UIkit, FontAwesome, Emoji).
- Beispiel: `'privacy_icon' => '🛡️'`

Hinweise:

- Texte wie „Alle Einstellungen“, „Einmal laden“ usw. kommen standardmäßig aus der REDAXO Texte-Verwaltung und können dort pro Sprache angepasst werden.
- Für YouTube/Vimeo wird – sofern möglich – die Video-ID automatisch aus vollständigen URLs extrahiert.
- Sicherheit: Übergebene IDs/URLs werden intern sicher verarbeitet. Für eigene iframes/scripts in `custom-service` keine unvalidierten Nutzerdaten durchreichen.

---

## 🎛️ Button-Texte anpassen

Texte werden über die **REDAXO Texte-Verwaltung** konfiguriert:

### Standard-Texte
- `button_inline_details` → "Einstellungen"
- `inline_placeholder_text` → "Einmal laden"  
- `button_inline_allow_all` → "Alle zulassen"
- `inline_action_text` → "Aktion auswählen"
- `inline_privacy_notice` → "Für die Anzeige werden Cookies benötigt."
- `inline_title_fallback` → "Externes Medium"
- `inline_privacy_link_text` → "Datenschutzerklärung von"

### Mehrsprachigkeit
Alle Texte sind automatisch mehrsprachig verfügbar und können pro Sprache angepasst werden.

---

## 🌐 Domain-spezifische Konfiguration  

### Inline-Only Modus
```php
// In der Domain-Konfiguration
'inline_only_mode' => 'enabled' // Deaktiviert die normale Consent-Box
```

### Per Service
```php
consent_manager_inline::setDomainConfig('example.com', [
    'inline_only' => ['youtube', 'vimeo'], // Nur diese Services inline
    'always_ask' => ['google-maps']        // Diese immer fragen
]);
```

## 📱 Responsive Design

### Automatische Anpassungen
```css
@media (max-width: 768px) {
    .consent-inline-overlay {
        padding: var(--consent-mobile-padding);
        font-size: var(--consent-mobile-font-size);
    }
    
    .consent-inline-buttons {
        flex-direction: column;
        gap: 0.5rem;
    }
}
```

### Touch-optimiert
- Größere Button-Bereiche auf Mobilgeräten
- Touch-freundliche Abstände
- Optimierte Typography

## 🔐 Datenschutz & DSGVO

### Thumbnail-Caching
- **Lokale Speicherung**: Thumbnails werden über Mediamanager lokal gecacht
- **Keine direkten Requests**: Browser lädt nicht direkt von YouTube/Vimeo
- **TTL Cache**: Automatisches Aufräumen nach konfigurierbarer Zeit
- **IP-Anonymisierung**: Bei Consent-Logging wird IP anonymisiert

### Consent-Logging
- Einheitliches Logging über `rex_api_consent_manager`
- DSGVO-konforme IP-Speicherung
- Nachvollziehbare Consent-Historie

## 🎪 JavaScript API

### Events
```javascript
// Consent erteilt
document.addEventListener('consent-inline-accepted', function(e) {
    console.log('Consent für Service:', e.detail.service);
});

// Content geladen
document.addEventListener('consent-content-loaded', function(e) {
    console.log('Content geladen:', e.detail.elements);
});
```

### Manuelle Steuerung
```javascript
// Programmatisch Consent erteilen
window.consentManagerInline.acceptService('youtube');

// Allen Services zustimmen
window.consentManagerInline.allowAllServices();

// Platzhalter manuell aktualisieren
window.consentManagerInline.updateAllPlaceholders();
```

## 🧪 Debug & Entwicklung

### Debug-Modus aktivieren
```php
// In der Domain-Konfiguration Debug aktivieren
rex::isDebugMode() // Automatische Debug-Ausgaben
```

### Console-Logs
```javascript
// Browser-Konsole zeigt:
// - Cookie-Status
// - Event-Verarbeitung  
// - Content-Loading
// - Fehler-Details
```



## 🚀 Performance

### Optimierungen
- **Lazy Loading**: Inhalte werden erst nach Consent geladen
- **Mediamanager-Cache**: Optimierte Thumbnail-Auslieferung
- **Event-Delegation**: Effiziente Event-Handler
- **Mutation Observer**: Automatische DOM-Updates

### Best Practices
```php
// Thumbnail-Größe optimieren
'thumbnail' => 'auto', // Nutzt Mediamanager-Optimierung

// Responsive Einbettung
'responsive' => true,

// CSS-Variablen für Theme-Anpassung nutzen
'css_class' => 'consent-theme-minimal'
```

## 🎉 Fazit

Das Inline-Consent-System bietet:
- 🎨 **Maximale Anpassbarkeit** über CSS Custom Properties
- 🖼️ **Professionelle Thumbnail-Verwaltung** über Mediamanager
- 🌐 **Vollständige Mehrsprachigkeit** über REDAXO Texte-System
- 📱 **Responsive Design** out-of-the-box
- 🔐 **DSGVO-Compliance** durch lokales Caching
- ⚡ **Optimale Performance** durch intelligentes Loading

## 🧩 Externe Nutzung des Thumbnail-Systems

Das Thumbnail-System kann **unabhängig vom Inline-Consent** für eigene Projekte verwendet werden:

### 🚀 Schnellstart

```php
// Einfachste Verwendung - aus Video-URL direkt Thumbnail generieren
$thumbnailUrl = rex_consent_manager_thumbnail_mediamanager::getThumbnailUrlFromVideoUrl(
    'https://www.youtube.com/watch?v=dQw4w9WgXcQ'
);
echo '<img src="' . $thumbnailUrl . '" alt="YouTube Thumbnail" />';

// Oder mit Service + Video-ID
$thumbnailUrl = rex_consent_manager_thumbnail_mediamanager::getThumbnailUrl('youtube', 'dQw4w9WgXcQ');
```

### 🎯 Praktische Anwendungsbeispiele

#### Video-Galerie im Template
```php
<?php
// Helper-Funktion für Templates
function getVideoThumbnail($videoUrl) {
    return rex_consent_manager_thumbnail_mediamanager::getThumbnailUrlFromVideoUrl($videoUrl);
}

// Video-Liste
$videos = [
    'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'https://vimeo.com/123456789',
    'https://www.youtube.com/watch?v=oHg5SJYRHA0'
];
?>

<div class="video-grid">
<?php foreach ($videos as $videoUrl): ?>
    <?php $thumbnail = getVideoThumbnail($videoUrl); ?>
    <?php if ($thumbnail): ?>
    <div class="video-item">
        <img src="<?= $thumbnail ?>" loading="lazy" alt="Video Thumbnail" />
        <a href="<?= $videoUrl ?>" target="_blank">Video ansehen</a>
    </div>
    <?php endif; ?>
<?php endforeach; ?>
</div>
```

#### Eigener Mediamanager-Type für HD-Thumbnails
```php
// Setup-Script für große Thumbnails (z.B. in install.php)
$sql = rex_sql::factory();
$sql->setTable(rex::getTable('media_manager_type'));
$sql->setValue('name', 'youtube_hd_thumbnails');
$sql->setValue('description', 'HD YouTube Thumbnails 1280x720');
$sql->setValue('status', 0);
$sql->insert();

$typeId = $sql->getLastId();

// External Thumbnail Effect
$sql = rex_sql::factory();
$sql->setTable(rex::getTable('media_manager_type_effect'));
$sql->setValue('type_id', $typeId);
$sql->setValue('effect', 'external_thumbnail');
$sql->setValue('priority', 1);
$sql->setValue('parameters', json_encode(['rex_effect_external_thumbnail' => ['rex_effect_external_thumbnail_cache_ttl' => 336]]));
$sql->insert();

// Resize auf HD
$sql = rex_sql::factory();
$sql->setTable(rex::getTable('media_manager_type_effect'));
$sql->setValue('type_id', $typeId);
$sql->setValue('effect', 'resize');
$sql->setValue('priority', 2);
$sql->setValue('parameters', json_encode(['rex_effect_resize' => ['rex_effect_resize_width' => '1280', 'rex_effect_resize_height' => '720', 'rex_effect_resize_style' => 'maximum']]));
$sql->insert();
```

#### YForm/MForm Integration
```php
// In YForm TableManager oder MForm
<?php if ($video_url = $this->getValue('video_url')): ?>
    <?php $thumbnail = rex_consent_manager_thumbnail_mediamanager::getThumbnailUrlFromVideoUrl($video_url); ?>
    <div class="video-preview">
        <img src="<?= $thumbnail ?>" alt="Video" />
        <a href="<?= $video_url ?>" class="play-button">▶ Abspielen</a>
    </div>
<?php endif; ?>
```

#### Backend-Listen mit Thumbnails
```php
// In Backend-Listen (z.B. YForm TableManager)
public static function getVideoThumbnailColumn($params)
{
    $videoUrl = $params['value'];
    if (!$videoUrl) return '';
    
    $thumbnail = rex_consent_manager_thumbnail_mediamanager::getThumbnailUrlFromVideoUrl($videoUrl);
    if (!$thumbnail) return '';
    
    return '<img src="' . $thumbnail . '" style="max-width: 80px; height: auto;" />';
}
```

### 🔄 Cache-Management

```php
// Cache-Informationen abrufen
$cacheInfo = rex_consent_manager_thumbnail_mediamanager::getCacheSize();
echo "Gecachte Thumbnails: {$cacheInfo['files']}, Größe: " . rex_formatter::bytes($cacheInfo['size']);

// Cache für bestimmten Service löschen
rex_consent_manager_thumbnail_mediamanager::clearCache('youtube');

// Kompletten Thumbnail-Cache löschen
rex_consent_manager_thumbnail_mediamanager::clearCache();
```

### 💡 Vorteile der Mediamanager-Integration

- ✅ **DSGVO-konform**: Keine direkten Requests an YouTube/Vimeo
- ✅ **Flexibel**: Alle Mediamanager-Effects verwendbar (Crop, Filter, etc.)
- ✅ **Performance**: Lokales Caching mit automatischer Bereinigung
- ✅ **Konsistent**: Einheitliche Thumbnail-Größen und -Qualität
- ✅ **Skalierbar**: Eigene Types für verschiedene Anwendungsfälle
- ✅ **Wartbar**: Standard REDAXO-Komponente, keine proprietäre Lösung

