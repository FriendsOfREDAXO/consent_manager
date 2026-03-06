# Themes, Best Practices und CSP

## Themes

Backend: `Consent Manager → Domains → Theme`

Typische Theme-Varianten:

- Light / Dark
- Bottom Bar / Bottom Right
- Accessibility

## Eigenes Theme erstellen

Beispielpfad:

`redaxo/src/addons/project/consent_manager_themes/my_theme.scss`

```scss
$consent-primary-color: #667eea;
$consent-background: #ffffff;

@import "base";

.consent_manager-box {
    border-radius: 12px;
}
```

## Best Practices

### Performance

- Cache in Produktion aktiv lassen
- Nur benötigte Assets laden
- Auto-Inject-Delay bei Bedarf setzen

### Datenschutz

- Opt-In vor externem Tracking
- Cookie-Liste aktuell halten
- Footer-Link zu Einstellungen anbieten

### Barrierefreiheit

- Fokus-Management aktiv lassen
- Tastatur-Navigation testen
- Screenreader-Kompatibilität prüfen

## Content Security Policy (CSP)

Die Ausgabe unterstützt Nonce-basierte Einbindung.

```php
<script<?= Frontend::getNonceAttribute() ?>>
    <?= Frontend::getJS() ?>
</script>
```

Externe Quellen (z. B. Analytics) müssen in der CSP explizit freigegeben werden.

## Weiterführend

- Inline-Consent: [inline.md](inline.md)
- API: [api.md](api.md)
