# Namespace-Guide für consent_manager 5.0

Ab Version 5.0 verwendet der Consent Manager den Namespace `FriendsOfRedaxo\ConsentManager`.

## Übersicht der Klassen-Änderungen

| Alter Klassenname | Neuer Klassenname |
|-------------------|-------------------|
| `consent_manager_frontend` | `FriendsOfRedaxo\ConsentManager\Frontend` |
| `consent_manager_util` | `FriendsOfRedaxo\ConsentManager\Utility` |
| `consent_manager_config` | `FriendsOfRedaxo\ConsentManager\Config` |
| `consent_manager_cache` | `FriendsOfRedaxo\ConsentManager\Cache` |
| `consent_manager_clang` | `FriendsOfRedaxo\ConsentManager\CLang` |
| `consent_manager_inline` | `FriendsOfRedaxo\ConsentManager\InlineConsent` |
| `consent_manager_theme` | `FriendsOfRedaxo\ConsentManager\Theme` |
| `consent_manager_google_consent_mode` | `FriendsOfRedaxo\ConsentManager\GoogleConsentMode` |
| `consent_manager_json_setup` | `FriendsOfRedaxo\ConsentManager\JsonSetup` |
| `consent_manager_oembed_parser` | `FriendsOfRedaxo\ConsentManager\OEmbedParser` |
| `consent_manager_thumbnail_cache` | `FriendsOfRedaxo\ConsentManager\ThumbnailCache` |
| `consent_manager_rex_form` | `FriendsOfRedaxo\ConsentManager\RexFormSupport` |
| `consent_manager_rex_list` | `FriendsOfRedaxo\ConsentManager\RexListSupport` |
| `rex_api_consent_manager` | `FriendsOfRedaxo\ConsentManager\Api\ConsentManager` |
| `rex_api_consent_manager_inline_log` | `FriendsOfRedaxo\ConsentManager\Api\InlineLog` |
| `rex_cronjob_log_delete` | `FriendsOfRedaxo\ConsentManager\Cronjob\LogDelete` |
| `rex_cronjob_consent_manager_thumbnail_cleanup` | `FriendsOfRedaxo\ConsentManager\Cronjob\ThumbnailCleanup` |
| `rex_consent_manager_thumbnail_mediamanager` | `FriendsOfRedaxo\ConsentManager\ThumbnailMediaManager` |
| `rex_consent_manager_command_log_delete` | `FriendsOfRedaxo\ConsentManager\Command\LogDelete` |

## Migration deines Codes

### Vorher (alt)

```php
$frontend = new consent_manager_frontend(0);
$frontend->showBox();

consent_manager_util::hostname();
```

### Nachher (neu)

```php
use FriendsOfRedaxo\ConsentManager\Frontend;
use FriendsOfRedaxo\ConsentManager\Utility;

$frontend = new Frontend(0);
$frontend->showBox();

Utility::hostname();
```

### Alternative mit vollqualifiziertem Namen

```php
$frontend = new \FriendsOfRedaxo\ConsentManager\Frontend(0);
\FriendsOfRedaxo\ConsentManager\Utility::hostname();
```

## Übergangszeit

Die alten Klassennamen funktionieren weiterhin, sind aber als `@deprecated` markiert. Du bekommst also keine Fehler, solltest aber deinen Code bei Gelegenheit anpassen.

Die Deprecated-Klassen findest du in `lib/deprecated/` – sie leiten einfach auf die neuen Namespace-Klassen weiter.

## doConsent() Funktion

Die Shorthand-Funktion `doConsent()` bleibt unverändert verfügbar:

```php
// Das funktioniert weiterhin
echo doConsent('youtube', '<iframe src="..."></iframe>');
```

Alternativ mit der neuen Klasse:

```php
use FriendsOfRedaxo\ConsentManager\InlineConsent;

echo InlineConsent::doConsent('youtube', '<iframe src="..."></iframe>');
```

## Fragmente

Die Fragmente wurden in ein Unterverzeichnis verschoben:

| Vorher | Nachher |
|--------|---------|
| `consent_manager_box.php` | `ConsentManager/box.php` |
| `consent_manager_consent_box.php` | `ConsentManager/consent_box.php` |

```php
// Neu
$fragment = new rex_fragment();
$fragment->setVar('domain', $domain);
echo $fragment->parse('ConsentManager/box.php');
```

## Fragen?

Bei Fragen oder Problemen: [GitHub Issues](https://github.com/FriendsOfREDAXO/consent_manager/issues) oder [REDAXO Slack](https://redaxo.org/slack/)
