# Installation und Grundeinrichtung

## Installation

1. AddOn über den REDAXO-Installer installieren.
2. Setup-Wizard öffnen (`Consent Manager → Einstellungen`).
3. Grundkonfiguration durchführen.

## Setup Wizard im Detail

Der Setup Wizard führt durch die Erstkonfiguration und übernimmt die wichtigsten Einstellungen in einem Durchlauf.

### 1) Domain wählen

- Domain kann manuell eingetragen werden.
- Falls `yrewrite` aktiv ist, werden verfügbare Domains zur Auswahl angeboten.
- Bereits konfigurierte Domains werden nicht erneut als Vorschlag angezeigt.

### 2) Rechtliche Seiten zuweisen

Im Wizard können direkt REDAXO-Artikel für folgende Links gesetzt werden:

- Datenschutzerklärung (`privacy_policy`)
- Impressum (`legal_notice`)

### 3) Setup-Typ auswählen

- **Standard**: Import der Standard-Konfiguration (wenn noch keine Services vorhanden sind)
- **Minimal**: Minimal-Setup (wenn noch keine Services vorhanden sind)

Wichtig: Sind bereits Services in der Instanz vorhanden, wird kein erneuter Setup-Import erzwungen.

### 4) Framework-Modus festlegen

Im Wizard kann der CSS-Framework-Modus gesetzt werden:

- `uikit3`
- `bootstrap5`
- `tailwind`
- `bulma`

### 5) Auto-Inject konfigurieren

- Auto-Inject aktivieren/deaktivieren
- Optional: Template-Whitelist setzen (`include_templates`)

Hinweis:

- Keine Templates auswählen = Consent Manager in allen Templates aktiv
- Template-Whitelist ist besonders nützlich bei Spezial-Templates (API/AJAX/Print)

### 6) Setup ausführen

Nach dem Start führt der Wizard u. a. diese Schritte aus:

1. Domain anlegen/aktualisieren
2. Setup-Daten (Standard/Minimal) importieren
3. Cookie-Gruppen der Domain zuordnen
4. Cache neu schreiben
5. Ergebnis validieren

Details zur manuellen Pflege danach: [dienste_und_gruppen.md#gruppen-domains-zuordnen](dienste_und_gruppen.md#gruppen-domains-zuordnen)

## Typische Wizard-Ergebnisse

- Domain ist angelegt und konfiguriert
- Datenschutz/Impressum sind verknüpft (falls gesetzt)
- Auto-Inject ist entsprechend aktiv
- Gruppen/Services sind je nach Setup-Typ verfügbar

## Setup-Varianten

- **Minimal**: Nur technisch notwendige Cookies.
- **Standard**: Vollständige Service-Sammlung.

## Framework-Integration

Der Consent Manager folgt einem Framework-First Ansatz.

### Unterstützte Frameworks

- UIkit 3
- Bootstrap 5
- Tailwind CSS
- Bulma

Vorteil: Die Consent-Box integriert sich visuell in das bestehende Frontend ohne separate, konfliktanfällige Zusatzstyles.

## Domain konfigurieren

Backend: `Consent Manager → Domains → Domain hinzufügen`

- Domain ohne Protokoll eintragen (z. B. `beispiel.de`)
- Datenschutz/Impressum zuweisen
- Auto-Inject aktivieren (empfohlen)

## Auto-Inject aktivieren

Backend: `Consent Manager → Domains → Domain bearbeiten`

Empfohlene Startwerte:

- Status: Aktiviert
- Reload bei Consent-Änderung: Ja
- Delay: 0 Sekunden
- Fokus auf Box: Ja

## Template-Positivliste (Optional)

Über `Nur in bestimmten Templates einbinden` kann die Ausgabe eingeschränkt werden.

Sinnvoll für Spezial-Templates (API, AJAX, Print, RSS, PDF).

### Wichtig bei URL-Parametern

Template-Filter prüfen nur Templates, nicht URL-Parameter.

Typische Spezialfälle:

- `?print=1`
- `?ajax=1`
- `?popup=1`
- `?view=iframe`

Für solche Fälle ist oft die manuelle Einbindung besser geeignet: [manuelle_einrichtung.md](manuelle_einrichtung.md)

## Nächste Schritte

- Erweiterte Konfiguration: [erweiterte_konfiguration.md](erweiterte_konfiguration.md)
- Dienste & Gruppen: [dienste_und_gruppen.md](dienste_und_gruppen.md)
- Dev Kurzhilfe: [DEV_QUICKSTART.md](DEV_QUICKSTART.md)
