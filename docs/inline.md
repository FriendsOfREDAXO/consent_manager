# Consent Manager - Inline-Consent System

## 🌟 Überblick

Das **Inline-Consent-System** ermöglicht es, Inhalte von externen Diensten (YouTube, Vimeo, Google Maps, etc.) mit einem eleganten Platzhalter anzuzeigen und erst nach Consent zu laden.

## 🚀 Grundlegende Verwendung

```php
echo consent_manager_inline::doConsent('youtube', 'dQw4w9WgXcQ', [
    'title' => 'Video laden',
    'placeholder_text' => 'Video abspielen',
    'privacy_notice' => 'Für YouTube werden Tracking-Cookies verwendet.',
    'show_allow_all' => true
]);
```

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

## 🎯 Service-spezifische Handler

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

## 🔧 Konfigurationsoptionen

### Basis-Optionen
```php
$options = [
    'title' => 'Titel des Platzhalters',
    'placeholder_text' => 'Button-Text',
    'privacy_notice' => 'Datenschutz-Hinweis',
    'show_allow_all' => true, // "Alle erlauben" Button
    'width' => '640',
    'height' => '360',
    
    // Verschiedene Thumbnail-Optionen:
    'thumbnail' => 'auto',                                    // Automatisch über Mediamanager
    // 'thumbnail' => '/media/my-thumb.jpg',                  // Lokale Datei
    // 'thumbnail' => 'https://example.com/thumb.jpg',        // Externe URL
    // 'thumbnail' => rex_media_manager::getUrl('type', 'file.jpg'), // Mediamanager-URL
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

## 🎛️ Button-Texte anpassen

Texte werden über die **REDAXO Texte-Verwaltung** konfiguriert:

### Standard-Texte
- `button_inline_details` → "Einstellungen"
- `inline_placeholder_text` → "Einmal laden"  
- `button_inline_allow_all` → "Alle erlauben"
- `inline_action_text` → "Was möchten Sie tun?"
- `inline_privacy_notice` → "Für die Anzeige werden Cookies benötigt."
- `inline_title_fallback` → "Externes Medium"
- `inline_privacy_link_text` → "Datenschutzerklärung von"

### Mehrsprachigkeit
Alle Texte sind automatisch mehrsprachig verfügbar und können pro Sprache angepasst werden.

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

**→ Professionelle Video-Thumbnail-Lösung für REDAXO! 🎯**