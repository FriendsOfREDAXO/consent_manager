# Tutorial: So Geht's

Dieses Tutorial zeigt einen typischen, praxistauglichen Ablauf für eine neue Consent-Manager-Installation.

## Ziel

Am Ende hast du:

- eine konfigurierte Domain,
- aktive Consent-Box im Frontend,
- sinnvoll strukturierte Dienste und Gruppen,
- einen funktionierenden Link für Cookie-Einstellungen im Footer.

## Schritt 1: AddOn installieren

1. AddOn über den REDAXO-Installer installieren.
2. Danach `Consent Manager → Einstellungen` öffnen.
3. Setup Wizard starten.

## Schritt 2: Setup Wizard ausfüllen

Im Wizard typischerweise setzen:

- Domain
- Datenschutzerklärung
- Impressum
- Setup-Typ (`Standard` oder `Minimal`)
- Framework-Modus (UIkit/Bootstrap/Tailwind/Bulma)
- Auto-Inject aktiv

Empfehlung für den Start:

- Setup-Typ: **Standard**
- Auto-Inject: **Aktiv**
- Template-Auswahl: erst leer lassen (alle Templates)

Details: [installation_und_grundeinrichtung.md](installation_und_grundeinrichtung.md)

## Schritt 3: Dienste und Gruppen prüfen

1. `Consent Manager → Dienste` öffnen.
2. Service-Einträge prüfen und ggf. anpassen.
3. `Consent Manager → Gruppen` öffnen.
4. Dienste sauber Gruppen zuordnen (z. B. Statistik, Marketing, Externe Medien).

Details: [dienste_und_gruppen.md](dienste_und_gruppen.md)

## Schritt 4: Frontend testen

Im Inkognito-Fenster aufrufen und prüfen:

- Consent-Box erscheint.
- Speichern funktioniert.
- Dialog lässt sich erneut öffnen.

Optionaler Footer-Link:

```html
<a href="#" data-consent-action="settings">Cookie-Einstellungen</a>
```

Mit Reload-Variante:

```html
<a href="#" data-consent-action="settings,reload">Cookie-Einstellungen</a>
```

## Schritt 5: API im Projekt verwenden

### PHP-Check

```php
<?php
use FriendsOfRedaxo\ConsentManager\Utility;

if (Utility::has_consent('google-analytics')) {
    // Tracking laden
}
```

### JS-Check

```javascript
document.addEventListener('consent_manager-ready', function (e) {
  if (!e.detail.initialized) return;

  if (typeof consent_manager_hasconsent === 'function' && consent_manager_hasconsent('google-analytics')) {
    // Tracking laden
  }
});
```

Details: [api.md](api.md)

## Schritt 6: Backup der Konfiguration

Nach erfolgreicher Einrichtung:

1. `Consent Manager → Einstellungen → Konfiguration exportieren`
2. JSON sichern (Repository/Passwort-Manager/Backup-Ordner)

Für Rollout auf andere Instanzen:

- `JSON-Konfiguration importieren`
- Modus je nach Bedarf: `Komplett laden` oder `Nur Neue`

## Troubleshooting Kurzliste

- Box erscheint nicht: Domain-Zuordnung und Auto-Inject prüfen.
- Speichern schlägt fehl: Host/HTTPS/Origin prüfen.
- Falsche Ausgabe im Spezial-Template: Template-Whitelist oder manuelle Einbindung nutzen.

Weitere Details:

- [manuelle_einrichtung.md](manuelle_einrichtung.md)
- [erweiterte_konfiguration.md](erweiterte_konfiguration.md)
- [support_und_community.md](support_und_community.md)
