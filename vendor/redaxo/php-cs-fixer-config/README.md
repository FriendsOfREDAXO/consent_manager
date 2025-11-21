# [PHP-CS-Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer) config for [REDAXO](https://github.com/redaxo/redaxo)

### Installation

```
composer require --dev redaxo/php-cs-fixer-config
```

Example `.php-cs-fixer.dist.php`:

```php
<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
;

return (Redaxo\PhpCsFixerConfig\Config::redaxo5()) // or `::redaxo6()`
    ->setFinder($finder)
;

```
