<?php

namespace J13k\YamlLint;

/*
 * yaml-lint, a compact command line utility for checking YAML file syntax.
 *
 * Uses the parsing facility of the Symfony Yaml Component.
 *
 * For full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use RuntimeException;

/**
 * Runtime exception for triggering usage message.
 *
 * @property int $code Exception code is passed through as script exit code
 */
class UsageException extends RuntimeException
{
}
