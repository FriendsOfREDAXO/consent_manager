<?php

declare(strict_types=1);

use PhpCsFixer\Finder;
use Redaxo\PhpCsFixerConfig\Config;

$finder = (new Finder())
    ->in(__DIR__)
    ->append([
        __FILE__,
    ])
;

return (new Config())
    ->setFinder($finder)
;
