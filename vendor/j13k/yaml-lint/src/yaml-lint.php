<?php

/** @noinspection PhpMissingParamTypeInspection */
/** @noinspection PhpMissingReturnTypeInspection */
/** @noinspection PhpDefineCanBeReplacedWithConstInspection */

/*
 * yaml-lint, a compact command line utility for checking YAML file syntax.
 *
 * Uses the parsing facility of the Symfony Yaml Component.
 *
 * For full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Composer\InstalledVersions;
use J13k\YamlLint\UsageException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

define('APP_NAME', 'yaml-lint');
define('APP_VERSION', '1.1.7');

define('ANSI_BLD', 01);
define('ANSI_UDL', 04);
define('ANSI_RED', 31);
define('ANSI_GRN', 32);

define('EXIT_NORMAL', 0);
define('EXIT_ERROR', 1);

define('YAML_PARSE_PARAM_NAME_EXCEPTION_ON_INVALID_TYPE', 'exceptionOnInvalidType');
define('YAML_PARSE_PARAM_NAME_FLAGS', 'flags');

// Init app name and args
$appStr = APP_NAME;
$argQuiet = false;
$argParseTags = false;
$argPath = null;
$argPaths = [];

try {
    // Composer bootstrap
    $pathToTry = null;
    foreach (['/../../../', '/../vendor/'] as $pathToTry) {
        if (is_readable(__DIR__ . $pathToTry . 'autoload.php')) {
            require __DIR__ . $pathToTry . 'autoload.php';

            break;
        }
    }
    if (!class_exists('\Composer\Autoload\ClassLoader')) {
        throw new Exception(_msg('composer'));
    }

    // Build app version string
    if (class_exists('\Composer\InstalledVersions')) {
        if (InstalledVersions::isInstalled('j13k/yaml-lint')) {
            $appStr .= ' ' . InstalledVersions::getPrettyVersion('j13k/yaml-lint');
        }
        if (InstalledVersions::isInstalled('symfony/yaml')) {
            $appStr .= ', symfony/yaml ' . InstalledVersions::getPrettyVersion('symfony/yaml');
        }
    } else {
        $appStr .= ' ' . APP_VERSION;
    }

    // Process and check args
    $argv = $_SERVER['argv'];
    array_shift($argv);
    foreach ($argv as $arg) {
        switch ($arg) {
            case '-h':
            case '--help':
                throw new UsageException();
            case '-V':
            case '--version':
                fwrite(STDOUT, $appStr . "\n");
                exit(EXIT_NORMAL);
            case '-q':
            case '--quiet':
                $argQuiet = true;

                break;
            case '-t':
            case '--parse-tags':
                $argParseTags = true;

                break;
            default:
                $argPaths[] = $arg;
        }
    }

    // Currently, only one input file or STDIN supported
    if (count($argPaths) < 1) {
        throw new UsageException('no input specified', EXIT_ERROR);
    }

    $lintPath = function ($path) use ($argQuiet, $argParseTags, $appStr) {
        $content = file_get_contents($path);
        if (strlen($content) < 1) {
            throw new ParseException('Input has no content');
        }

        // Do the thing (now accommodates changes to the Yaml::parse method introduced in v3)
        $yamlParseMethod = new ReflectionMethod('\Symfony\Component\Yaml\Yaml', 'parse');
        $yamlParseParams = $yamlParseMethod->getParameters();
        switch ($yamlParseParams[1]->name) {
            case YAML_PARSE_PARAM_NAME_EXCEPTION_ON_INVALID_TYPE:
                // Maintains original behaviour in v2
                Yaml::parse($content, true);
                break;
            case YAML_PARSE_PARAM_NAME_FLAGS:
                // Implements same behaviour in v3+ with optional custom tags support
                $flags = Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE;
                if ($argParseTags) {
                    $flags |= Yaml::PARSE_CUSTOM_TAGS;
                }
                Yaml::parse($content, $flags);
                break;
            default:
                // Param name unknown, fall back to the defaults
                Yaml::parse($content);
                break;
        }

        // Output app string and file path if allowed
        if (!$argQuiet) {
            fwrite(STDOUT, trim($appStr . ': parsing ' . $path));
            fwrite(STDOUT, sprintf(" [ %s ]\n", _ansify('OK', ANSI_GRN)));
        }
    };

    if ($argPaths[0] === '-') {
        $lintPath('php://stdin');
    } else {
        // Check input file(s)
        foreach ($argPaths as $argPath) {
            if (!file_exists($argPath)) {
                throw new RuntimeException(sprintf('File %s does not exist', $argPath));
            }
            if (!is_readable($argPath)) {
                throw new RuntimeException(sprintf('File %s is not readable', $argPath));
            }
            $lintPath($argPath);
        }
    }

    exit(EXIT_NORMAL);
} catch (UsageException $e) {
    // Usage message
    $outputStream = $e->getCode() > EXIT_NORMAL ? STDERR : STDOUT;
    fwrite($outputStream, $appStr);
    if ($e->getMessage()) {
        fwrite(
            $outputStream,
            sprintf(': %s', _ansify($e->getMessage(), ANSI_RED))
        );
    }
    fwrite($outputStream, sprintf("\n\n%s\n\n", _msg('usage')));
    exit($e->getCode());
} catch (ParseException $e) {
    // Syntax exception
    fwrite(STDERR, trim($appStr . ': parsing ' . $argPath));
    fwrite(STDERR, sprintf(" [ %s ]\n", _ansify('ERROR', ANSI_RED)));
    fwrite(STDERR, "\n" . $e->getMessage());

    // Check if the error is about custom tags and suggest the --parse-tags option
    if (strpos($e->getMessage(), 'Tags support is not enabled') !== false) {
        fwrite(STDERR, "\n" . _ansify(
            'Hint: Use --parse-tags or -t to enable custom YAML tag support (requires symfony/yaml 3+)', ANSI_UDL)
        );
    }

    fwrite(STDERR, "\n\n");
    exit(EXIT_ERROR);
} catch (Exception $e) {

    // The rest
    fwrite(STDERR, $appStr);
    fwrite(STDERR, sprintf(": %s\n", _ansify($e->getMessage(), ANSI_RED)));
    exit(EXIT_ERROR);
}

/**
 * Helper to wrap input string in ANSI colour code.
 *
 * @param string $str
 * @param int    $colourCode
 *
 * @return string
 */
function _ansify($str, $colourCode)
{
    $colourCode = max(0, $colourCode);
    $colourCode = min(255, $colourCode);

    return sprintf("\e[%dm%s\e[0m", $colourCode, $str);
}

/**
 * Wrapper for heredoc messages.
 *
 * @param string $str
 *
 * @return string
 */
function _msg($str)
{
    switch ($str) {
        case 'composer':
            return <<<EOD
Composer dependencies cannot be loaded; install Composer to remedy:
https://getcomposer.org/download/
EOD;
        case 'usage':
            return <<<EOD
usage: yaml-lint [options] [input source]

  input source      Path to file(s), or "-" to read from standard input

  -q, --quiet       Restrict output to syntax errors
  -t, --parse-tags  Enable parsing of custom YAML tags (symfony/yaml 3+ only)
  -h, --help        Display this help
  -V, --version     Display application version
EOD;
        default:
    }

    return '';
}
