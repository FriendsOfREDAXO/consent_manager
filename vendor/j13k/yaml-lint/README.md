# yaml-lint

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Total Downloads][ico-downloads]][link-downloads]
[![Monthly Downloads][ico-downloads-monthly]][link-downloads]
[![CI][ico-github-ci]][link-github-ci]

A compact command line linting tool for validating YAML files, using the parsing facility of
the [Symfony Yaml Component](https://github.com/symfony/yaml).

## Usage

```text
usage: yaml-lint [options] [input source]

  input source      Path to file(s), or "-" to read from standard input

  -q, --quiet       Restrict output to syntax errors
  -t, --parse-tags  Enable parsing of custom YAML tags (symfony/yaml 3+ only)
  -h, --help        Display this help
  -V, --version     Display application version
```

## Install

### Composer

To get started using yaml-lint in a project, install it with Composer:

```bash
composer require --dev j13k/yaml-lint
```

It can then be run from the project's `vendor/bin` directory.

To set up yaml-lint globally, install it in the Composer home directory:

```bash
composer global require j13k/yaml-lint
```

It can then be run from the `bin` directory of Composer home (typically  `~/.composer/vendor/bin`).

### Binary

A binary edition , `yaml-lint.phar`, is available for download
with [each release](https://github.com/j13k/yaml-lint/releases). This embeds the latest stable version of the Symfony
Yaml component that is current at the time of the release.

The binary can be conveniently installed using [PHIVE](https://phar.io/):

```
phive install yaml-lint
```

### Docker

yaml-lint is bundled in the [phpqa Docker image](https://hub.docker.com/r/jakzal/phpqa/), which provides a suite of
static analysis tools for PHP. See the [phpqa project](https://github.com/jakzal/phpqa) for [installation
and usage instructions](https://github.com/jakzal/phpqa#running-tools).

## Change log

Please see [CHANGELOG](CHANGELOG.md) for information on what has changed recently.

## Credits

- [yaml-lint contributors][link-contributors]
- [Symfony Yaml contributors](https://github.com/symfony/yaml/graphs/contributors)

## License

The MIT License (MIT). Please see [LICENCE](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/j13k/yaml-lint.svg?style=flat-square

[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square

[ico-downloads]: https://img.shields.io/packagist/dt/j13k/yaml-lint.svg?style=flat-square

[ico-downloads-monthly]: https://poser.pugx.org/j13k/yaml-lint/d/monthly

[ico-github-ci]: https://github.com/j13k/yaml-lint/actions/workflows/ci.yml/badge.svg

[link-packagist]: https://packagist.org/packages/j13k/yaml-lint

[link-downloads]: https://packagist.org/packages/j13k/yaml-lint/stats

[link-contributors]: https://github.com/j13k/yaml-lint/contributors

[link-github-ci]: https://github.com/j13k/yaml-lint/actions/workflows/ci.yml
