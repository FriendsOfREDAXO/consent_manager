# Manuelle Einrichtung

Für Spezialfälle ohne Auto-Inject oder bei fein granularer Kontrolle.

## Standard-Integration

```php
<?php
use FriendsOfRedaxo\ConsentManager\Frontend;

echo Frontend::getFragment(0, 0, 'ConsentManager/box_cssjs.php');
```

## Komponenten getrennt laden

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

## Parameter von `Frontend::getFragment()`

```php
Frontend::getFragment(int $forceCache, int $forceReload, string $fragmentFilename): string
```

- `$forceCache`: Cache-Verhalten
- `$forceReload`: Reload bei Consent-Änderung
- `$fragmentFilename`: Fragment-Auswahl

## Cookie-Einstellungen-Link im Footer

Empfohlen:

```html
<a href="#" data-consent-action="settings">Cookie-Einstellungen</a>
```

Mit Reload:

```html
<a href="#" data-consent-action="settings,reload">Cookie-Einstellungen</a>
```

## Cookie-Liste in Datenschutzseite ausgeben

```php
<?php
use FriendsOfRedaxo\ConsentManager\Frontend;

echo Frontend::getCookieList();
```

## Veraltete Variable

`REX_CONSENT_MANAGER[...]` gilt als veraltet. Bitte `Frontend::getFragment()` bzw. Auto-Inject verwenden.

## Siehe auch

- Installation und Grundeinrichtung: [installation_und_grundeinrichtung.md](installation_und_grundeinrichtung.md)
- API: [api.md](api.md)
- Dev Kurzhilfe: [DEV_QUICKSTART.md](DEV_QUICKSTART.md)
