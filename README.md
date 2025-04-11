# Consent-Manager für das [REDAXO CMS](https://redaxo.org)

Cookie-Einverständnis und Cookie-Verwaltung für das REDAXO CMS - optimiert für die ePrivacy-Verordnung der EU und die DSGVO.

## Features

- Implementiert als Web Component für bessere Wartbarkeit und Isolation
- Google Consent Mode V2 Integration
- Volle Unterstützung für Mehrsprachigkeit
- Vollständig individualisierbare Texte und Einstellungen
- Optimiertes Performance durch Shadow DOM
- Barrierefreie Bedienung über Tastatur und Screenreader
- Konfigurierbare Cookie-Lebensdauer
- Flexible Cookie-Gruppierung
- Logging von Consent-Entscheidungen
- Integrierte Skip-Token Funktion

## Installation

1. Im REDAXO Backend unter `Installer` den Consent Manager installieren
2. Eine Domain in der Verwaltung anlegen
3. Cookie-Gruppen und Cookies/Dienste anlegen oder importieren
4. Das Fragment im Template einbinden

## Template Einbindung

```php
REX_CONSENT_MANAGER[]
```

## Einstellungen und Optionen

### Dienste-Texte anpassen

Im Backend können unter "Texte" sämtliche Ausgaben angepasst werden.

### Mehrsprachigkeit

Der Consent Manager unterstützt mehrere Sprachen. Texte können für alle im System aktivierten Sprachen gepflegt werden.

### Web Component Attribute

Die `<consent-manager>` Web Component unterstützt folgende Attribute:

- `domain`: Die aktuelle Domain
- `version`: Version des Consent Managers
- `cookie-lifetime`: Lebensdauer des Consent-Cookies in Tagen
- `force-reload`: Seite nach Consent-Änderung neu laden
- `hide-body-scrollbar`: Scrollbar bei geöffnetem Dialog ausblenden
- `initially-hidden`: Consent-Dialog initial ausblenden

### JavaScript API

Die Web Component bietet folgende JavaScript API:

```javascript
// Consent Box manuell anzeigen
document.querySelector('consent-manager').show();

// Consent Box verstecken
document.querySelector('consent-manager').hide();

// Prüfen ob ein bestimmter Cookie-Consent vorliegt
document.querySelector('consent-manager').hasConsent('youtube');

// Event wenn Consent gespeichert wurde
document.querySelector('consent-manager').addEventListener('consent-saved', (e) => {
    console.log('Gespeicherte Consents:', e.detail);
});
```

### Google Consent Mode V2

Der Consent Manager unterstützt den Google Consent Mode V2. Aktiviere dazu in der Domain-Konfiguration die Option "Google Consent Mode V2".

Die folgenden Consent-Typen werden unterstützt:

- `ad_storage`: Marketing-Cookies
- `ad_user_data`: Marketing-Cookies  
- `ad_personalization`: Marketing-Cookies
- `analytics_storage`: Statistik-Cookies
- `functionality_storage`: Technisch notwendige Cookies (immer aktiv)
- `personalization_storage`: Technisch notwendige Cookies (immer aktiv) 
- `security_storage`: Technisch notwendige Cookies (immer aktiv)

## Technischer Aufbau

Der Consent Manager basiert auf folgenden Technologien:

- Web Components für die UI
- Shadow DOM für Style-Isolation
- Custom Events für die Kommunikation
- Vanilla JavaScript ohne externe Dependencies
- PHP 7.4+ Backend

## Lizenz

MIT License, siehe [LICENSE.md](LICENSE.md)

## Autor

- [Friends Of REDAXO](https://github.com/FriendsOfREDAXO)
