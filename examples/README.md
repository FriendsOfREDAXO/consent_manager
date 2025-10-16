# Consent Manager - Inline-Consent Beispielmodule

## 📋 Übersicht

Diese Beispielmodule demonstrieren die **Inline-Consent-Funktionalität** des Consent Managers:

### 🎥 YouTube Inline-Consent
**Pfad:** `/examples/modules/youtube-inline-consent/`
- Automatische YouTube-Thumbnail-Generierung
- Video-ID Extraktion aus URLs
- Responsive Player-Einbindung

### 📍 Google Maps Inline-Consent  
**Pfad:** `/examples/modules/maps-inline-consent/`
- Google Maps Embed-URL Integration
- Konfigurierbare Kartenhöhe
- Datenschutz-optimierte Einbindung

### 🎬 Vimeo Inline-Consent
**Pfad:** `/examples/modules/vimeo-inline-consent/`
- Vimeo Player API Support
- Custom Thumbnail-Option
- Professionelle Video-Einbindung

### 🔧 Custom Inline-Consent
**Pfad:** `/examples/modules/custom-inline-consent/`
- Universell für beliebige Services
- Booking.com, Calendly, Typeform, etc.
- Flexible iframe/Script-Integration

## 🛠️ Installation der Module

1. **Module kopieren** in REDAXO Backend
2. **Services konfigurieren** (Consent Manager → Services)
3. **Template erweitern** mit CSS/JS Fragment
4. **Module in Artikeln verwenden**

## ⚠️ Wichtige Voraussetzungen

**Backend-Services müssen existieren:**
- `youtube` Service für YouTube-Modul
- `google-maps` Service für Maps-Modul  
- `vimeo` Service für Vimeo-Modul
- Entsprechender Service für Custom-Modul

**Template-Integration:**
```php
<?php echo rex_view::content('consent_manager_inline_cssjs.php'); ?>
```

## 🎯 Verwendung

**Einfach:**
```php
<?php echo doConsent('youtube', 'dQw4w9WgXcQ'); ?>
```

**Mit Optionen:**
```php
<?php
echo doConsent('youtube', 'dQw4w9WgXcQ', [
    'title' => 'Mein Video',
    'width' => 560,
    'height' => 315
]);
?>
```

Die Module sind **sofort einsatzbereit** und demonstrieren alle Features der Inline-Consent-Funktionalität!