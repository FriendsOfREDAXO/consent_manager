# Erweiterte Konfiguration

## Google Consent Mode v2

Backend: `Consent Manager → Domains → Domain bearbeiten`

Modi:

- Deaktiviert
- Auto (empfohlen)
- Manual

Hinweis: Der Helper ist typischerweise nur im Manual-Modus nötig.

## Inline-Only Modus

Unterdrückt das globale Banner; Consent wird bei Bedarf inline abgefragt.

Typische Anwendungsfälle:

- Seiten ohne allgemeines Tracking
- Einzelne Unterseiten mit Videos/Maps
- Progressive Consent-Strategien

## Auto-Blocking für manuell eingefügtes HTML

Backend: `Consent Manager → Einstellungen → Auto-Blocking für manuelles HTML aktivieren`

Einsatzgebiet:

- Manuell eingebettete Scripts/iFrames
- CKE5/Redactor-Inhalte
- Redaktions-Workflows mit wiederkehrenden Embeds

Vertiefung: [inline.md](inline.md)

## CKE5 oEmbed Integration

YouTube/Vimeo-Links können automatisch in datenschutzkonforme Platzhalter umgewandelt werden.

### So aktivierst du CKE5 Auto-Embed

1. Backend öffnen: `Consent Manager → Domains → Domain bearbeiten`
2. CKE5 oEmbed Integration aktivieren
3. Optional Breite/Höhe und Drei-Button-Variante konfigurieren
4. Speichern

### Voraussetzungen

- Inline-Consent-Assets müssen im Frontend verfügbar sein
- Die betroffenen Services (z. B. YouTube/Vimeo) sind als Dienste konfiguriert
- Domain ist korrekt zugewiesen

Vertiefung zur Inline-Integration: [inline.md](inline.md)

### Verhalten im Editor

- Redakteure fügen YouTube-/Vimeo-Links im CKE5 ein
- Im Frontend wird daraus ein datenschutzkonformer Platzhalter
- Das eigentliche Medium wird erst nach Consent geladen

### Schneller Funktionstest

1. Inkognito-Fenster öffnen
2. Seite mit CKE5-oEmbed-Link aufrufen
3. Prüfen, ob Platzhalter statt direktem Embed erscheint
4. Consent erteilen und erneutes Laden prüfen

### Häufige Ursachen, wenn es nicht greift

- Inline-Assets fehlen
- Dienst nicht in Gruppen/Domain aktiv
- Domain- oder Template-Einschränkung blockiert Ausgabe

### Aktivierung

Backend: `Consent Manager → Domains → Domain bearbeiten`

Typische Einstellungen:

- CKE5 oEmbed Integration: Aktiviert
- Video-Breite: z. B. `640`
- Video-Höhe: z. B. `360`
- Drei-Button-Variante: optional

### Voraussetzungen

- CKE5 ist im Einsatz
- Inhalte werden als oEmbed-Link eingefügt (nicht nur als statisches HTML)
- Inline-Assets sind eingebunden, damit Platzhalter sauber dargestellt und bedient werden können

Beispiel im Template:

```php
<link rel="stylesheet" href="<?= rex_url::addonAssets('consent_manager', 'consent_inline.css') ?>">
...
<script defer src="<?= rex_url::addonAssets('consent_manager', 'consent_inline.js') ?>"></script>
```

### Redaktionsablauf

1. YouTube- oder Vimeo-Link in CKE5 einfügen
2. Ausgabe im Frontend prüfen
3. Ohne Consent erscheint ein Platzhalter
4. Nach Zustimmung wird der Inhalt geladen

### Typische Fehlerquellen

- Kein Platzhalter sichtbar: Inline-Assets fehlen
- Embed lädt sofort: Dienst/Gruppenzuordnung prüfen
- Falsche Darstellung: Breite/Höhe und Theme-Einstellungen kontrollieren

Vertiefung:

- Inline-Consent: [inline.md](inline.md)
- Dienste & Gruppen: [dienste_und_gruppen.md](dienste_und_gruppen.md)

## Multi-Domain

Mehrere Domains können getrennt konfiguriert werden (Services, Texte, Themes).

## Mehrsprachigkeit

Texte und Service-Skripte können sprachspezifisch gepflegt werden, inkl. Fallback.

## Datensicherung, Import und Export

Backend:

- Export: `Consent Manager → Einstellungen → Konfiguration exportieren`
- Import: `Consent Manager → Einstellungen → JSON-Konfiguration importieren`

Import-Modi:

- **Komplett laden**
- **Nur Neue**

Ausführliche Praxishinweise: [dienste_und_gruppen.md](dienste_und_gruppen.md)
