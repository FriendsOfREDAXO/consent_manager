# Inline Consent - Bedarfsgerechter Consent für einzelne Medien

Das **Inline Consent System** ermöglicht es, Consent nicht global für die gesamte Website abzufragen, sondern gezielt nur dort, wo externe Inhalte (YouTube-Videos, Google Maps, etc.) eingebunden werden.

## 🎯 Anwendungsfälle

- **YouTube/Vimeo Videos**: Consent nur beim Klick auf Video-Platzhalter
- **Google Maps**: Karten werden erst nach individueller Zustimmung geladen
- **Social Media Embeds**: Twitter, Facebook, Instagram Posts mit individuellem Consent
- **Domain-spezifische Einstellungen**: Verschiedene Domains können unterschiedliche Modi verwenden

---

## � Button-Optionen

Das System bietet **flexible Button-Konfiguration** für verschiedene Anwendungsfälle:

### **2-Button-Modus (Standard)**
```php
echo consent_manager_inline::doConsent('youtube', $videoId, [
    'title' => 'Mein Video'
]);
```
➡️ Zeigt: **"Einmal laden"** + **"Einstellungen"**

### **3-Button-Modus (Erweitert)**
```php
echo consent_manager_inline::doConsent('youtube', $videoId, [
    'title' => 'Mein Video',
    'show_allow_all' => true
]);
```
➡️ Zeigt: **"Einmal laden"** + **"Alle erlauben"** + **"Einstellungen"**

### **Button-Verhalten**

| Button | Aktion | Cookie-Verhalten |
|--------|--------|-----------------|
| **Einmal laden** | Lädt nur diesen einen Inhalt | ❌ Kein Cookie gesetzt |
| **Alle erlauben** | Erlaubt alle Inhalte dieses Services | ✅ Service-Cookie wird gesetzt |
| **Einstellungen** | Öffnet Consent-Manager-Box | ⚙️ Individuelle Konfiguration |

**ℹ️ Wann welchen Modus verwenden?**
- **2 Buttons**: Einfache Seiten, einzelne Videos
- **3 Buttons**: Seiten mit vielen Videos/Inhalten desselben Services

---

## �🔧 Einrichtung

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

// Inline Consent für YouTube-Video (2-Button-Modus)
$videoId = 'dQw4w9WgXcQ'; // oder komplette URL
echo consent_manager_inline::doConsent('youtube', $videoId, [
    'title' => 'Rick Astley - Never Gonna Give You Up'
]);

// Mit 3-Button-Modus:
echo consent_manager_inline::doConsent('youtube', $videoId, [
    'title' => 'Rick Astley - Never Gonna Give You Up',
    'show_allow_all' => true // Aktiviert "Alle erlauben" Button
]);
// ✅ Zeigt automatisch "🔒 Datenschutzerklärung von Google" wenn im Service konfiguriert
?>
```

---

## 📱 Button-Konfiguration

### **2-Button-Modus (Standard)**
```php
echo consent_manager_inline::doConsent('youtube', $videoId, [
    'title' => 'Mein Video'
]);
```
➡️ Zeigt: **"Einmal laden"** + **"Einstellungen"**

### **3-Button-Modus (Erweitert)**
```php
echo consent_manager_inline::doConsent('youtube', $videoId, [
    'title' => 'Mein Video',
    'show_allow_all' => true
]);
```
➡️ Zeigt: **"Einmal laden"** + **"Alle erlauben"** + **"Einstellungen"**

### **Button-Verhalten**

| Button | Aktion | Cookie-Verhalten |
|--------|--------|--------------------|
| **Einmal laden** | Lädt nur diesen einen Inhalt | ❌ Kein Cookie gesetzt |
| **Alle erlauben** | Erlaubt alle Inhalte dieses Services | ✅ Service-Cookie wird gesetzt |
| **Einstellungen** | Öffnet Consent-Manager-Box | ⚙️ Individuelle Konfiguration |

**ℹ️ Wann welchen Modus verwenden?**
- **2 Buttons**: Einfache Seiten, einzelne Videos
- **3 Buttons**: Seiten mit vielen Videos/Inhalten desselben Services

---

### Erweiterte Optionen

```php
<?php
echo consent_manager_inline::doConsent('youtube', $videoId, [
    // Button-Konfiguration
    'show_allow_all' => true, // 3-Button-Modus aktivieren
    'placeholder_text' => 'Einmal laden', // Text für "Einmal laden" Button
    
    // Anzeige-Optionen
    'title' => 'Video-Titel',
    'privacy_notice' => 'Für die Anzeige werden YouTube-Cookies benötigt.',
    'width' => 800,
    'height' => 450,
    
    // Design & Icons
    'thumbnail' => 'auto', // oder direkte URL zu eigenem Bild
    'icon' => 'uk-icon:play-circle', // Haupt-Icon (FontAwesome oder UIkit)
    'icon_label' => 'YouTube Video starten', // Barrierefreiheit
    'privacy_icon' => 'fa fa-shield-alt' // Privacy-Link Icon
]);
?>
```

#### **Alle verfügbaren Optionen**

| Option | Typ | Standard | Beschreibung |
|--------|-----|----------|-------------|
| `show_allow_all` | bool | `false` | Zeigt zusätzlichen "Alle erlauben" Button |
| `placeholder_text` | string | "Einmal laden" | Text des Haupt-Buttons |
| `title` | string | "Externes Medium" | Überschrift des Platzhalters |
| `privacy_notice` | string | "Für die Anzeige..." | Hinweistext unter dem Titel |
| `thumbnail` | string | `auto` | Vorschaubild (auto = API, oder URL) |
| `icon` | string | Service-spezifisch | FontAwesome/UIkit Icon-Klasse |
| `icon_label` | string | Generiert | Alt-Text für Screenreader |
| `privacy_icon` | string | `uk-icon:shield` | Icon für Privacy-Link |
| `width` | int | Service-Standard | Breite des Inhalts |
| `height` | int | Service-Standard | Höhe des Inhalts |

### Unterstützte Services

| Service | Handler | Beschreibung |
|---------|---------|--------------|
| `youtube` | Speziell | Automatische Video-ID-Erkennung, Thumbnail-Cache |
| `vimeo` | Speziell | Vimeo-API-Integration, Thumbnail-Cache |
| `google-maps` | Speziell | Google Maps Embed-Handler |
| Andere | Generisch | Universeller Handler für beliebige externe Inhalte |

---

## 🎬 Unterstützte Content-Typen

### **1. YouTube & Vimeo (Automatik)**

```php
<?php
// YouTube - automatische Video-ID Erkennung + Thumbnail-Cache
echo consent_manager_inline::doConsent('youtube', 'dQw4w9WgXcQ', [
    'title' => 'Rick Astley - Never Gonna Give You Up',
    'width' => 560,
    'height' => 315
]);

// Vimeo - automatische Video-ID Erkennung + Thumbnail-Cache
echo consent_manager_inline::doConsent('vimeo', '123456789', [
    'title' => 'Vimeo Video',
    'width' => 640,
    'height' => 360
]);
?>
```

### **2. Google Maps (Automatik)**

```php
<?php
// Google Maps Embed-URL wird automatisch in Iframe umgewandelt
$mapUrl = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3...';

echo consent_manager_inline::doConsent('google-maps', $mapUrl, [
    'title' => 'Unsere Filiale in Berlin',
    'width' => '100%',
    'height' => 450,
    'privacy_notice' => 'Google Maps verwendet Cookies für Funktionalität und Personalisierung.'
]);
?>
```

### **3. Beliebige Iframes**

```php
<?php
// Spotify Playlist - WICHTIG: Vollständiger iframe erforderlich!
$spotifyEmbed = '<iframe src="https://open.spotify.com/embed/playlist/37i9dQZF1DXcBWIGoYBM5M" 
                        width="100%" height="380" frameborder="0" 
                        allow="encrypted-media"></iframe>';
echo consent_manager_inline::doConsent('spotify', $spotifyEmbed, [
    'title' => 'Spotify Playlist',
    'icon' => 'fab fa-spotify'
]);

// Twitter Embed - iframe erforderlich
$twitterEmbed = '<iframe src="https://platform.twitter.com/embed/Tweet.html?id=1234567890" 
                        width="500" height="600" frameborder="0"></iframe>';
echo consent_manager_inline::doConsent('twitter', $twitterEmbed, [
    'title' => 'Twitter Tweet anzeigen',
    'icon' => 'fab fa-twitter'
]);

// CodePen Embed - iframe erforderlich
$codePenEmbed = '<iframe height="400" style="width: 100%;" 
    src="https://codepen.io/username/embed/xyz123" 
    frameborder="0" allowfullscreen></iframe>';
echo consent_manager_inline::doConsent('codepen', $codePenEmbed, [
    'title' => 'CSS Animation Demo',
    'icon' => 'fab fa-codepen'
]);
?>
```

### **4. JavaScript & Tracking Scripts**

```php
<?php
// Google Analytics Tracking
$analyticsScript = '<script>
gtag("config", "GA_MEASUREMENT_ID", {
  page_title: "Homepage",
  page_location: "https://example.com/"
});
</script>';

echo consent_manager_inline::doConsent('google-analytics', $analyticsScript, [
    'title' => 'Analytics aktivieren',
    'placeholder_text' => 'Tracking erlauben',
    'privacy_notice' => 'Google Analytics erfasst anonymisierte Nutzungsdaten.',
    'icon' => 'fas fa-chart-line'
]);

// Facebook Pixel
$facebookPixel = '<script>
fbq("track", "PageView");
fbq("track", "ViewContent", {
  content_type: "product",
  content_ids: ["1234"]
});
</script>';

echo consent_manager_inline::doConsent('facebook-pixel', $facebookPixel, [
    'title' => 'Facebook Pixel aktivieren',
    'placeholder_text' => 'Marketing-Tracking erlauben',
    'icon' => 'fab fa-facebook'
]);

// Custom JavaScript Funktionen
$customScript = '<script>
// Eigene Tracking-Logik
window.customTracker = {
    init: function() {
        console.log("Custom Tracker initialized");
        this.trackPageView();
    },
    trackPageView: function() {
        // Sende Daten an eigenen Server
        fetch("/api/track", {
            method: "POST",
            body: JSON.stringify({
                page: window.location.pathname,
                timestamp: Date.now()
            })
        });
    }
};
window.customTracker.init();
</script>';

echo consent_manager_inline::doConsent('custom-tracking', $customScript, [
    'title' => 'Eigenes Tracking aktivieren',
    'placeholder_text' => 'Tracking starten'
]);
?>
```

### **5. Interaktive Widgets & Tools**

```php
<?php
// OpenStreetMap mit Leaflet
$osmWidget = '<div id="osm-map-' . uniqid() . '" style="height: 400px;"></div>
<script>
var mapId = "osm-map-' . uniqid() . '";
var map = L.map(mapId).setView([52.5200, 13.4050], 13);
L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "&copy; OpenStreetMap contributors"
}).addTo(map);
L.marker([52.5200, 13.4050]).addTo(map)
    .bindPopup("Berlin, Deutschland")
    .openPopup();
</script>';

echo consent_manager_inline::doConsent('openstreetmap', $osmWidget, [
    'title' => 'Interaktive Karte (OpenStreetMap)',
    'privacy_notice' => 'Die Karte lädt Daten von OpenStreetMap-Servern.',
    'icon' => 'fas fa-map'
]);

// Chat-Widget (z.B. Intercom, Zendesk)
$chatWidget = '<script>
window.Intercom("boot", {
  app_id: "your_app_id",
  name: "Besucher",
  created_at: ' . time() . '
});
</script>';

echo consent_manager_inline::doConsent('intercom-chat', $chatWidget, [
    'title' => 'Live-Chat aktivieren',
    'placeholder_text' => 'Chat starten',
    'privacy_notice' => 'Der Live-Chat überträgt Daten an Intercom.',
    'icon' => 'fas fa-comments'
]);

// Kalendar-Widget (Calendly, etc.)
$calendarEmbed = '<iframe src="https://calendly.com/your-username/30min" 
                         width="100%" height="600" frameborder="0"></iframe>';

echo consent_manager_inline::doConsent('calendly', $calendarEmbed, [
    'title' => 'Termin buchen',
    'placeholder_text' => 'Kalender laden',
    'privacy_notice' => 'Calendly kann persönliche Daten verarbeiten.',
    'icon' => 'fas fa-calendar-alt'
]);
?>
```

### **6. E-Commerce & Payment Widgets**

```php
<?php
// PayPal Button
$paypalButton = '<div id="paypal-button-container"></div>
<script>
paypal.Buttons({
    createOrder: function(data, actions) {
        return actions.order.create({
            purchase_units: [{
                amount: { value: "29.99" }
            }]
        });
    },
    onApprove: function(data, actions) {
        return actions.order.capture().then(function(details) {
            alert("Transaction completed by " + details.payer.name.given_name);
        });
    }
}).render("#paypal-button-container");
</script>';

echo consent_manager_inline::doConsent('paypal', $paypalButton, [
    'title' => 'PayPal Zahlung',
    'placeholder_text' => 'PayPal laden',
    'privacy_notice' => 'PayPal verarbeitet Zahlungsdaten.',
    'icon' => 'fab fa-paypal'
]);

// Stripe Payment Form
$stripeForm = '<form id="payment-form">
  <div id="card-element">
    <!-- Stripe Elements will create form elements here -->
  </div>
  <button id="submit-payment">Bezahlen</button>
</form>
<script>
var stripe = Stripe("pk_test_...");
var elements = stripe.elements();
var cardElement = elements.create("card");
cardElement.mount("#card-element");
</script>';

echo consent_manager_inline::doConsent('stripe', $stripeForm, [
    'title' => 'Kreditkarten-Zahlung',
    'placeholder_text' => 'Zahlungsformular laden',
    'privacy_notice' => 'Stripe verarbeitet Kreditkarten-Daten sicher.',
    'icon' => 'fab fa-stripe'
]);
?>
```

---

## 🔧 **Content-Type Behandlung**

### **Automatische URL-zu-Iframe Konvertierung**

Das System erkennt automatisch bestimmte URL-Patterns und wandelt sie in Iframes um:

| Content-Type | URL-Pattern | Automatische Konvertierung |
|--------------|-------------|----------------------------|
| **YouTube** | `youtube.com`, `youtu.be` | ✅ Video-ID → Embed-Iframe |
| **Vimeo** | `vimeo.com/123456` | ✅ Video-ID → Player-Iframe |
| **Google Maps** | `google.com/maps/embed` | ✅ URL → Maps-Iframe |
| **Alle anderen** | Spotify, Twitter, etc. | ❌ **Vollständiger iframe erforderlich!** |

⚠️ **Wichtig:** Nur YouTube, Vimeo und Google Maps unterstützen automatische URL-Konvertierung. Für alle anderen Services muss der **komplette iframe-Code** übergeben werden!

### **Direkte HTML-Ausgabe**

Für alle anderen Content-Typen wird der übergebene HTML-Code direkt ausgegeben:

```php
<?php
// ✅ RICHTIG: Vollständiger iframe
$iframe = '<iframe src="https://example.com/widget" width="100%" height="400"></iframe>';
echo consent_manager_inline::doConsent('service-key', $iframe, [...]);

// ❌ FALSCH: Nur URL (wird als Text ausgegeben)
$url = 'https://example.com/widget';
echo consent_manager_inline::doConsent('service-key', $url, [...]);

// ✅ JavaScript-Code wird direkt eingefügt und ausgeführt
$script = '<script>console.log("Hello World");</script>';
echo consent_manager_inline::doConsent('service-key', $script, [...]);

// ✅ Komplexe HTML-Strukturen
$widget = '<div class="widget">
    <h3>Externes Widget</h3>
    <iframe src="https://widget.example.com"></iframe>
    <script>initWidget();</script>
</div>';
echo consent_manager_inline::doConsent('service-key', $widget, [...]);
?>
```

**Wichtige Punkte:**

1. **Service-Registrierung erforderlich**: Jeder `serviceKey` muss im Consent Manager Backend angelegt sein
2. **Consent-Verhalten**: Ohne Consent wird Platzhalter angezeigt, mit Consent wird Content geladen
3. **JavaScript-Ausführung**: Scripts werden nach Consent automatisch ausgeführt
4. **Flexible Integration**: Beliebige HTML-Strukturen und interaktive Widgets möglich
5. **DSGVO-Konformität**: Externe Inhalte werden erst nach expliziter Zustimmung geladen

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

## 🌍 Internationalisierung (i18n)

Alle Button-Texte und Meldungen werden über die **Texte-Verwaltung** verwaltet und sind vollständig mehrsprachig:

### **Button-Texte anpassen**

1. **Backend**: `Consent Manager` → `Texte`
2. **Schlüssel bearbeiten**:
   - `inline_placeholder_text`: "Einmal laden" Button
   - `button_inline_allow_all`: "Alle erlauben" Button 
   - `button_inline_details`: "Einstellungen" Button
   - `inline_action_text`: Einleitungstext "Was möchten Sie tun?"
3. **Für jede Sprache** anpassen

### **Verfügbare Text-Schlüssel**

| Schlüssel | Standard-Text | Beschreibung |
|-----------|---------------|-------------|
| `inline_placeholder_text` | "Einmal laden" | Haupt-Button (nur dieser Inhalt) |
| `button_inline_allow_all` | "Alle erlauben" | Service-Button (alle Inhalte) |
| `button_inline_details` | "Einstellungen" | Consent-Manager öffnen |
| `inline_action_text` | "Was möchten Sie tun?" | Einleitungstext über Buttons |
| `inline_privacy_notice` | "Für die Anzeige..." | Allgemeiner Hinweistext |
| `inline_title_fallback` | "Externes Medium" | Standard-Titel falls nicht gesetzt |
| `inline_privacy_link_text` | "Datenschutzerklärung von" | Privacy-Link Präfix |

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
| `.consent-inline-once` | "Einmal laden" Button (Event-Handler) |
| `.consent-inline-allow-all` | "Alle erlauben" Button (Event-Handler) |
| `.consent-inline-details` | "Einstellungen" Button |
| `.consent-inline-actions` | Button-Container |
| `.consent-inline-action-text` | Einleitungstext "Was möchten Sie tun?" |
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
| `options` | array | Konfigurationsoptionen (show_allow_all, icon, icon_label, privacy_icon) |
| `placeholderData` | array | Icon, Icon-Label, Thumbnail, Service-Name |
| `content` | string | Original-Content zum Laden |
| `show_allow_all` | boolean | Steuert Anzeige des "Alle erlauben" Buttons |
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