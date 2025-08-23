# JSON-basiertes Setup-System für Consent Manager

Das Consent Manager Addon wurde auf ein JSON-basiertes Setup-System umgestellt, um die Wartung zu vereinfachen und Community-Beiträge zu fördern.

## Vorteile des JSON-Systems

- **Wartbarkeit**: Einfacher zu bearbeiten und zu verstehen als SQL
- **Versionskontrolle**: JSON-Dateien lassen sich besser in Git verfolgen
- **Community-Beiträge**: Entwickler können einfach neue Services als JSON beitragen
- **Modularität**: Verschiedene Setup-Templates für unterschiedliche Anwendungsfälle
- **Validierung**: Strukturvalidierung beim Import

## Verfügbare Setup-Templates

### `default_setup.json`
Standard-Setup mit den wichtigsten Services für deutsche Websites:
- Google Analytics 4 (GA4)
- Google Tag Manager
- Matomo Analytics
- Google Maps
- YouTube
- Facebook Pixel
- Standard deutsche Cookie-Texte

### `business_setup.json`
Erweiterte Services für Business-Websites:
- Alle Services aus default_setup
- Hotjar
- Microsoft Clarity
- LinkedIn Insight Tag
- TikTok Pixel
- Vimeo
- Erweiterte Analytics-Services

### `contribution_template.json`
Template für neue Service-Beiträge mit:
- Vollständige Anleitung für Entwickler
- Beispiel-Services
- Best Practices
- Kategorien-Übersicht

## Setup verwenden

### 1. Über Backend
- Im Consent Manager Backend → Konfiguration
- Bereich "Setup Templates" 
- Gewünschtes Template auswählen und laden

### 2. Programmatisch
```php
// Standard-Setup laden
$result = consent_manager_json_setup::importSetup(
    rex_path::addon('consent_manager') . 'setup/default_setup.json',
    true // Bestehende Daten löschen
);

// Eigene JSON-Datei importieren
$result = consent_manager_json_setup::importSetup(
    '/pfad/zur/eigenen/setup.json',
    true
);
```

## Eigene Setup-Templates erstellen

### 1. Bestehende Konfiguration exportieren
```php
$exportData = consent_manager_json_setup::exportSetup(true);
file_put_contents('mein_setup.json', json_encode($exportData, JSON_PRETTY_PRINT));
```

### 2. Neue Services hinzufügen
Verwenden Sie das `contribution_template.json` als Basis:

```json
{
  "id": 100,
  "clang_id": 1,
  "uid": "mein-service",
  "service_name": "Mein Service",
  "provider": "Meine Firma",
  "provider_link_privacy": "https://meinfirma.de/datenschutz",
  "definition": "-\\n name: mein_cookie\\n time: \"1 Jahr\"\\n desc: \"Cookie-Beschreibung\"",
  "script": "<!-- Mein Service -->\\n<script>\\n// Service-Code hier\\n</script>"
}
```

## JSON-Struktur

### Meta-Informationen
```json
{
  "meta": {
    "export_version": "1.1",
    "export_date": "2025-01-23", 
    "addon_version": "2.8.0",
    "description": "Beschreibung des Setups",
    "type": "setup_type",
    "language": "de"
  }
}
```

### Cookie-Gruppen
```json
{
  "cookiegroups": [
    {
      "id": 1,
      "prio": 1,
      "uid": "required",
      "name": "Notwendig",
      "description": "Beschreibung der Gruppe",
      "required": "|1|",
      "cookie": "|service1|service2|"
    }
  ]
}
```

### Services/Cookies
```json
{
  "cookies": [
    {
      "id": 1,
      "uid": "service-uid",
      "service_name": "Service Name",
      "provider": "Anbieter",
      "provider_link_privacy": "https://...",
      "definition": "YAML Cookie-Definitionen",
      "script": "JavaScript Code",
      "script_unselect": "Code beim Deaktivieren"
    }
  ]
}
```

### Texte
```json
{
  "texts": [
    {
      "id": 1,
      "uid": "headline",
      "text": "Cookie-Einstellungen",
      "clang_id": 1
    }
  ]
}
```

## Community-Beiträge

### Service-Template erstellen
```php
$template = consent_manager_json_setup::createServiceTemplate(
    'Mein Service',
    'Meine Firma',
    'statistics'
);
file_put_contents('mein_service.json', json_encode($template, JSON_PRETTY_PRINT));
```

### Beitrag einreichen
1. Verwenden Sie `contribution_template.json` als Basis
2. Füllen Sie alle Platzhalter aus
3. Testen Sie das Setup gründlich
4. Erstellen Sie einen Pull Request mit:
   - JSON-Datei im `setup/` Verzeichnis
   - Beschreibung des Services
   - Screenshots der Funktionalität

### Naming-Konventionen
- **Dateiname**: `service-name_setup.json`
- **UID**: `service-name` (lowercase, mit Bindestrichen)
- **Platzhalter**: `YOUR_API_KEY`, `YOUR_ID`, etc.

## Migration von SQL

Das System unterstützt beide Formate parallel:
- JSON-Setups haben Priorität
- SQL-Setup wird als Fallback verwendet
- Bestehende Installationen funktionieren unverändert

### Legacy SQL → JSON migrieren
1. Bestehende Konfiguration exportieren:
   ```
   Backend → Consent Manager → Konfiguration → Export
   ```

2. JSON-Datei als Template verwenden:
   ```php
   $result = consent_manager_json_setup::importSetup('exported_config.json', true);
   ```

## API-Referenz

### Import
```php
consent_manager_json_setup::importSetup($file, $clearExisting);
// Returns: ['success' => bool, 'message' => string, 'meta' => array]
```

### Export  
```php
consent_manager_json_setup::exportSetup($includeMetadata);
// Returns: array (ready for JSON encoding)
```

### Available Templates
```php
consent_manager_json_setup::getAvailableSetups();
// Returns: array of available setup files with metadata
```

### Service Template Generator
```php
consent_manager_json_setup::createServiceTemplate($name, $provider, $category);
// Returns: template array for new service contributions
```

## Fehlerbehebung

### Häufige Probleme
1. **JSON Syntax Error**: Verwenden Sie einen JSON-Validator
2. **Fehlende Felder**: Alle required-Felder müssen vorhanden sein
3. **ID-Konflikte**: IDs müssen eindeutig sein
4. **Cookie-Definition**: YAML-Format in der `definition` verwenden

### Debug-Modus
```php
// Detaillierte Fehlerinformationen
$result = consent_manager_json_setup::importSetup($file, true);
if (!$result['success']) {
    rex_logger::logError('consent_manager', $result['message']);
}
```

## Lizenz & Copyright

Dieses System steht unter der gleichen Lizenz wie das Consent Manager Addon.
Community-Beiträge werden unter MIT-Lizenz veröffentlicht.
