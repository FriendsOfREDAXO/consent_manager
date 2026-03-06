# Consent Manager – Dev Kurzhilfe (Schnellstart)

Kurz und praktisch: Einbindung, Consent-Abfrage und typische Snippets.

## 1) Einbindung wählen

### Option A: Auto-Inject (empfohlen)
1. Backend öffnen: `Consent Manager -> Domains -> Domain bearbeiten`
2. `Automatische Frontend-Einbindung` auf **Aktiviert** setzen
3. Speichern

Ergebnis: Der Consent Manager wird automatisch vor `</head>` eingebunden.

### Option B: Manuell im Template (komplett)

```php
<?php
use FriendsOfRedaxo\ConsentManager\Frontend;

echo Frontend::getFragment(0, 0, 'ConsentManager/box_cssjs.php');
```

### Option C: Manuell, Komponenten getrennt

```php
<?php
use FriendsOfRedaxo\ConsentManager\Frontend;
?>
<style><?= Frontend::getCSS() ?></style>
<script<?= Frontend::getNonceAttribute() ?>>
<?= Frontend::getJS() ?>
</script>
<?= Frontend::getBox() ?>
```

---

## 2) Consent in PHP abfragen

```php
<?php
use FriendsOfRedaxo\ConsentManager\Utility;

if (Utility::has_consent('google-analytics')) {
    // Script/Markup nur bei erteiltem Consent ausgeben
}
```

---

## 3) Consent in JavaScript abfragen

Wichtig: Eigene Skripte sollten warten, bis der Consent Manager initialisiert ist.

```javascript
document.addEventListener('consent_manager-ready', function (e) {
  if (!e.detail.initialized) {
    console.warn('Consent Manager nicht bereit:', e.detail.reason);
    return;
  }

  if (typeof consent_manager_hasconsent === 'function' && consent_manager_hasconsent('google-analytics')) {
    console.log('GA darf geladen werden');
  }
});
```

Direkte Abfrage (wenn bereits sicher initialisiert):

```javascript
if (typeof consent_manager_hasconsent === 'function' && consent_manager_hasconsent('google-analytics')) {
  // Consent vorhanden
}
```

---

## 4) Auf Consent-Änderung reagieren

```javascript
document.addEventListener('consent_manager-saved', function (e) {
  var consents = JSON.parse(e.detail);
  // z. B. Tracking dynamisch starten/stoppen
});
```

---

## 5) Einstellungen-Dialog per Link öffnen

Standard (ohne Reload):

```html
<a href="#" data-consent-action="settings">Cookie-Einstellungen</a>
```

Variante mit Reload nach Speichern:

```html
<a href="#" data-consent-action="settings,reload">Cookie-Einstellungen</a>
```

Tipp: `reload` ist sinnvoll, wenn externe Skripte keinen sauberen Live-Reinit unterstützen und erst nach einem Seiten-Reload korrekt starten/stoppen.

---

## 6) Häufige Stolpersteine

- `consent_manager_hasconsent is not defined`: Consent Manager ist noch nicht geladen oder gar nicht eingebunden.
- `TypeError: Load failed` beim Speichern: meist URL-/Host-/HTTPS-Mismatch im Setup; aktuelle Version nutzt same-origin Logging.
- Falsche Quotes in JS vermeiden: normale `'` oder `"` verwenden, keine typografischen Anführungszeichen.

---

## 7) Spezialfälle (Tipps)

### A) Mehrere Services gleichzeitig prüfen

```javascript
function hasAllConsents(serviceKeys) {
  if (typeof consent_manager_hasconsent !== 'function') return false;
  return serviceKeys.every(function (key) {
    return consent_manager_hasconsent(key);
  });
}

document.addEventListener('consent_manager-ready', function () {
  if (hasAllConsents(['google-analytics', 'google-tag-manager'])) {
    // Nur wenn beide erlaubt sind
  }
});
```

### B) Script erst nach Consent dynamisch laden

```javascript
function loadScript(src) {
  var script = document.createElement('script');
  script.src = src;
  script.async = true;
  document.head.appendChild(script);
}

document.addEventListener('consent_manager-ready', function () {
  if (typeof consent_manager_hasconsent === 'function' && consent_manager_hasconsent('google-analytics')) {
    loadScript('https://www.googletagmanager.com/gtag/js?id=G-XXXXXXX');
  }
});
```

### C) Reaktion auf spätere Änderungen (Opt-in/Opt-out)

```javascript
document.addEventListener('consent_manager-saved', function () {
  if (typeof consent_manager_hasconsent !== 'function') return;

  if (consent_manager_hasconsent('google-analytics')) {
    // Tracking aktivieren
  } else {
    // Tracking deaktivieren / keine neuen Events senden
  }
});
```

### D) Fallback, wenn eigenes Script sehr früh läuft

```javascript
function onConsentManagerReady(callback) {
  document.addEventListener('consent_manager-ready', function (e) {
    if (e.detail.initialized) callback();
  });

  document.addEventListener('DOMContentLoaded', function () {
    if (typeof consent_manager_hasconsent === 'function') {
      callback();
    }
  });
}

onConsentManagerReady(function () {
  // hier sicher mit consent_manager_hasconsent arbeiten
});
```
