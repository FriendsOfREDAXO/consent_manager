# Migration von SQL zu JSON Setup

Das Consent Manager Addon verwendet jetzt ausschließlich JSON-basierte Setups. Die alte SQL-Datei `setup.sql` wurde entfernt.

## Was ist neu?

✅ **JSON-Setup als Standard** - Einfacher zu bearbeiten und zu verstehen
✅ **Multiple Templates** - Verschiedene Setups für verschiedene Anwendungsfälle  
✅ **Community-freundlich** - Entwickler können einfach neue Services beitragen
✅ **Bessere Wartung** - Versionskontrolle und Struktur-Validierung

## Verfügbare Templates:

- `default_setup.json` - Standard-Setup mit wichtigsten Services
- `business_setup.json` - Erweiterte Services für Business-Websites
- `minimal_setup.json` - Nur notwendige Cookies (DSGVO-konform)
- `contribution_template.json` - Template für neue Service-Beiträge

## Für bestehende Installationen:

Die Migration erfolgt automatisch beim nächsten Setup-Import. Ihre bestehenden Daten bleiben unverändert.

## Für Entwickler:

Nutzen Sie `contribution_template.json` als Basis für neue Services und erstellen Sie Pull Requests mit JSON-Dateien statt SQL-Dumps.
