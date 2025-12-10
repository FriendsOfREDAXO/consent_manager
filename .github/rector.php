<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/../lib',
        __DIR__ . '/../pages',
        __DIR__ . '/../fragments',
        __DIR__ . '/../boot.php',
        __DIR__ . '/../install.php',
        __DIR__ . '/../uninstall.php',
        __DIR__ . '/../update.php',
    ]);

    $rectorConfig->skip([
        __DIR__ . '/../vendor',
        __DIR__ . '/../.github/vendor',
    ]);

    // PHP 8.1 Kompatibilität
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
    ]);
};
