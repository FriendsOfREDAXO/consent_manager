# Dienste und Gruppen konfigurieren

Diese Seite beschreibt die vollständige Pflege von Services (Diensten/Cookies) und Gruppen im Consent Manager – inklusive sicherem Import/Export für Backups und Rollouts auf weitere Instanzen.

## Überblick: Was gehört wohin?

- **Dienste** beschreiben den einzelnen Drittanbieter (z. B. YouTube, Maps, Analytics)
- **Gruppen** bündeln mehrere Dienste für die Consent-UI (z. B. „Statistik“, „Marketing“)
- **Domain-Zuordnung** steuert über die Gruppen, auf welchen Domains die enthaltenen Dienste aktiv sind
- **Variante / Zweck** unterscheidet gleichnamige Dienste mit abweichender technischer Konfiguration

Empfohlene Reihenfolge:

1. Dienste anlegen
2. Cookie-Definitionen sauber pflegen
3. Dienste in Gruppen zuordnen
4. Domain prüfen
5. Konfiguration exportieren (Backup)

## Dienste anlegen

Backend: `Consent Manager → Dienste`

| Feld | Beschreibung |
|------|--------------|
| Schlüssel | Eindeutiger Key ohne Sonderzeichen (z. B. `youtube`) |
| Dienstname | Anzeigename in der Consent-Box |
| Variante / Zweck | Optionales Unterscheidungsmerkmal im Backend (z. B. `Domain A`, `Lokale Instanz`, `EU-Server`) |
| Anbieter | Firma/Anbieter des Dienstes |
| Cookies | YAML-Liste der gesetzten Cookies |
| Script | JavaScript-Code, der erst nach Consent ausgeführt wird |

### Best Practices für Service-Keys

- Keys klein und stabil halten (`youtube`, `google-analytics`, `meta-pixel`)
- Keys nachträglich möglichst nicht ändern, damit bestehende Integrationen stabil bleiben
- Pro technischer Konfiguration einen eigenen Dienst mit eindeutigem Schlüssel verwenden

## Gleichnamige Dienste in Multi-Domain-Setups

Benötigt derselbe Anbieter je nach Domain ein anderes Script, werden mehrere Dienste mit jeweils eindeutigem Schlüssel angelegt. Der sichtbare Dienstname kann gleich bleiben; das Feld **Variante / Zweck** dient zur Unterscheidung im Backend.

Beispiel:

| Schlüssel | Dienstname | Variante / Zweck |
|-----------|------------|-------------------|
| `youtube-domain-a` | YouTube | Domain A |
| `youtube-domain-b` | YouTube | Domain B |

In der Dienstauswahl einer Gruppe erscheinen diese Einträge als **YouTube (Domain A)** und **YouTube (Domain B)**. Die Variante wird in allen aktiven und schreibgeschützten Auswahlzuständen angezeigt und von der Live-Suche berücksichtigt. Im Frontend bleibt der für Besucher bestimmte Dienstname unverändert.

Vorgehen:

1. Für jede technische Variante einen Dienst mit eigener UID anlegen oder einen vorhandenen Dienst duplizieren.
2. Den gemeinsamen, für Besucher bestimmten Dienstnamen eintragen.
3. Unter **Variante / Zweck** die Domain oder den technischen Zweck hinterlegen.
4. Jede Variante der passenden Gruppe zuweisen.
5. Die Gruppe den vorgesehenen Domains zuordnen.

## Cookie-Definition (YAML)

Beispiel:

```yaml
-
 name: _ga
 time: 2 Jahre
 desc: "Google Analytics ID"
-
 name: _gid
 time: 24 Stunden
 desc: "Session-Tracking"
```

Hinweise:

- `name`: Cookie-Name
- `time`: Speicherdauer / Laufzeit
- `desc`: Kurze, verständliche Beschreibung für die Doku/Transparenz

## Gruppen erstellen

Backend: `Consent Manager → Gruppen`

- **Technisch notwendig**: immer aktiv, nicht deaktivierbar
- **Dienste zuweisen**: mehrere Services pro Gruppe möglich
- **Domain**: Gruppenzuordnung zu einer Domain

## Dienstzuweisung in mehreren Sprachen

Backend: `Consent Manager → Einstellungen → Allgemein`

Mit **Sprachspezifische Dienstzuweisung pro Gruppe** wird global festgelegt, ob übersetzte Gruppen eine eigene Dienstauswahl erhalten dürfen. Die Option ist standardmäßig aktiviert.

In einer Nicht-Primärsprache stehen pro Gruppe zwei Modi zur Verfügung:

- **Aus Primärsprache erben**: Änderungen an der Dienstauswahl werden ausschließlich in der Primärsprache vorgenommen und automatisch übernommen.
- **Eigene Dienstzuweisung für diese Sprache**: Für die aktuelle Sprache kann eine abweichende Auswahl gespeichert werden. Beim ersten Umschalten wird die Auswahl der Primärsprache als Ausgangspunkt übernommen.

Ist die globale Option deaktiviert, erben alle Übersetzungen die Dienstzuweisung der Primärsprache. Bleibt eine eigene Auswahl leer, greift ebenfalls die Auswahl der Primärsprache als Fallback.

Ändert sich durch einen Sprachwechsel der tatsächlich wirksame Satz an Dienst-UIDs, fordert der Consent Manager die Einwilligung erneut an. Reine Übersetzungsänderungen ohne veränderte Dienstzuweisung lösen keinen erneuten Consent aus.

### Empfohlene Gruppenstruktur

- **Essenziell/Technisch notwendig**: nur zwingend erforderliche Dienste
- **Statistik**: Analytics/Reporting
- **Marketing**: Ads, Retargeting, Conversion-Tracking
- **Externe Medien**: YouTube, Vimeo, Karten, Embeds

## Gruppen Domains zuordnen

Backend: `Consent Manager → Gruppen`

So gehst du vor:

1. Gewünschte Gruppe öffnen (z. B. `Statistik`)
2. Im Feld **Domain** die Ziel-Domain(s) zuweisen
3. Gruppe speichern
4. Für weitere Gruppen wiederholen

Wichtig:

- Eine Gruppe wirkt nur auf den zugeordneten Domains
- Bei Multi-Domain-Setups sollten alle relevanten Gruppen pro Domain geprüft werden
- Nach Änderungen kurz Frontend testen (Consent-Box + Service-Verhalten)

### Hinweis zum Setup Wizard

Im Setup Wizard wird die technisch notwendige Gruppe (`required`) automatisch der gewählten Domain zugeordnet. Zusätzliche Gruppen sollten danach manuell geprüft und bei Bedarf ergänzt werden.

## Import/Export (wichtig für Backup und Deployment)

Backend:

- Export: `Consent Manager → Einstellungen → Konfiguration exportieren`
- Import: `Consent Manager → Einstellungen → JSON-Konfiguration importieren`

### Export

Der Export enthält die komplette Konfiguration (u. a. Domains, Dienste, Gruppen, Texte) als JSON.

Typische Anwendungsfälle:

- Backup vor größeren Änderungen
- Übernahme von DEV → Stage → Produktion
- Migration auf andere REDAXO-Instanzen

### Import

Beim Import stehen zwei Modi zur Verfügung:

- **Komplett laden**: überschreibt vorhandene Einstellungen
- **Nur Neue**: ergänzt neue Einträge, bestehende bleiben erhalten

### Empfehlung für sichere Änderungen

1. Vor jeder größeren Änderung Export als Backup ziehen
2. Änderungen zuerst in Testumgebung durchführen
3. Export in Zielumgebung importieren
4. Dienste/Gruppen und Domain-Zuordnung direkt prüfen

## Verwandte Doku

- Schnellstart: [DEV_QUICKSTART.md](DEV_QUICKSTART.md)
- API und Events: [api.md](api.md)
- Inline-Consent: [inline.md](inline.md)
- Gesamtüberblick: [README.md](../README.md)
