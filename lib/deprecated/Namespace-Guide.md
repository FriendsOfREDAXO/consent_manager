# Hinweise zur Umstellung auf Namespace-Klassen

Mit Version 5.0.0 des Consent Managers wurden alle Klassen und Funktionen in den Namespace
`FriendsOfRedaxo\ConsentManager` überführt. Einige der Klassen liegen in Sub-Namespaces
`Api`, `Command` und `Cronjob`.

## Übersicht der alten und neuen Klassen- und Funktionsnamen

| alter Name                                    | Namespace                              | neuer Name            |
|-----------------------------------------------|----------------------------------------|-----------------------|
| consent_manager_cache                         | FriendsOfRedaxo\ConsentManager         | Cache                 |
| consent_manager_clang                         | FriendsOfRedaxo\ConsentManager         | CLang                 |
| consent_manager_config                        | FriendsOfRedaxo\ConsentManager         | Config                |
| consent_manager_frontend                      | FriendsOfRedaxo\ConsentManager         | Frontend              |
| consent_manager_inline                        | FriendsOfRedaxo\ConsentManager         | InlineConsent         |
| consent_manager_json_setup                    | FriendsOfRedaxo\ConsentManager         | JsonSetup             |
| consent_manager_oembed_parser                 | FriendsOfRedaxo\ConsentManager         | OEmbedParser          |
| consent_manager_rex_form                      | FriendsOfRedaxo\ConsentManager         | RexFormSupport        |
| consent_manager_rex_list                      | FriendsOfRedaxo\ConsentManager         | RexListSupport        |
| consent_manager_theme                         | FriendsOfRedaxo\ConsentManager         | Theme                 |
| consent_manager_thumbnail_cache               | FriendsOfRedaxo\ConsentManager         | ThumbnailCache        |
| consent_manager_util                          | FriendsOfRedaxo\ConsentManager         | Utility               |
| consent_manager_google_consent_mode           | FriendsOfRedaxo\ConsentManager         | GoogleConsentMode     |
| rex_api_consent_manager_inline_log            | FriendsOfRedaxo\ConsentManager\Api     | InlineLog             |
| rex_api_consent_manager                       | FriendsOfRedaxo\ConsentManager\Api     | ConsentManager        |
| rex_consent_manager_command_log_delete        | FriendsOfRedaxo\ConsentManager\Command | LogDelete             |
| rex_consent_manager_thumbnail_mediamanager    | FriendsOfRedaxo\ConsentManager         | ThumbnailMediaManager |
| rex_cronjob_consent_manager_thumbnail_cleanup | FriendsOfRedaxo\ConsentManager\Cronjob | ThumbnailCleanup      |
| rex_cronjob_log_delete                        | FriendsOfRedaxo\ConsentManager\Cronjob | LogDelete             |
| doConsent()                                   | FriendsOfRedaxo\ConsentManager         | doConsent()           |

## Hinweise zur Umstellung des eigenen Codes

Grundwissen über Namespaces in PHP-Code wird vorausgesetzt.

Eine grundlegende Anleitung ist auf GitHub in den FOR-Tricks zu finden ([Das Addon hat "Namespace"! Und Nun?](https://friendsofredaxo.github.io/tricks/development/namespace_a)).

Die bisherigen Klassen außerhalb eines Namespace sind weiterhin als Erweiterung der neuen Klassen verfügbar. Das Addon
sollte daher auch ohne Code-Umstellung funktionieren. Sollte es in Einzelfällen Probleme geben, müsste an den Stellen
die Namespace-Umstellung vorgezogen werden.

Die Hilfsklassen sind mit einen @deprecated-Vermerk versehen. Ab Version 6.0.0 werden die Klassen nach vosichtiger
Planung aus dem Addon-Code entfernt; bis dahin muss die Umstellung erfolgt sein.

```php
use FriendsOfRedaxo\ConsentManager\Frontend;
/** @deprecated 6.0.0 since version 5.0.0 use FriendsOfRedaxo\ConsentManager\Frontend instead */
class consent_manager_frontend extends Frontend {}
```

Generell empfiehlt es sich, die Umstellung auf die Namespace-Klassen zügig anzugehen. 
