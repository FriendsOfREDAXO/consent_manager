# Changelog

## [1.1.7] - 2025-10-24

### Added

* Add `--parse-tags` / `-t` option to enable custom YAML tag support (#166)
* Add helpful error hint when custom tags are encountered without the flag
* Add test fixture and comprehensive tests for custom tags functionality

### Fixed

* Fix CI compatibility with PHP 5.6-7.1 by removing `composer-runtime-api` dependency
* Fix dev dependencies interference in CI by removing them before composer update
* Fix test suite Symfony version detection to work reliably on PHP 5.6 (use installed.json)

### Updated

* Bump symfony/yaml from 7.0.3 to 7.3.3 (via Dependabot)
* Covered PHP 8.4 in CI job matrix
* Documentation updates for `--parse-tags` option
* Note: Custom tags feature requires Symfony YAML 3+, gracefully handles v2

## [1.1.6] - 2024-04-18

### Added

* Add tests covering quiet mode (https://github.com/j13k/yaml-lint/issues/51)

### Updated

* Add and test Symfony 7 support (https://github.com/j13k/yaml-lint/issues/99)
* Covered PHP 8.3 and PHPUnit 11 in CI job matrix (https://github.com/j13k/yaml-lint/issues/98)
* Documentation updates, including PHIVE and Docker installation methods

## [1.1.5] - 2023-04-20

### Added

- Symfony 6 support (https://github.com/j13k/yaml-lint/issues/45) via [@wow-apps](https://github.com/wow-apps)
- Unit tests (https://github.com/j13k/yaml-lint/issues/5)

### Updated

- Bump symfony/yaml from 5.2.5 to v6.2.7

### Refactor

- Updated .gitattributes

### Fixed

- Switched to using Composer\InstalledVersions for yaml component version (https://github.com/j13k/yaml-lint/issues/47)

## [1.1.4] - 2021-03-11

### Added

- Support for multiple files (resolves #3, via [@staabm](https://github.com/staabm))
- Support for Symfony 5 YAML component (via [@OndraM](https://github.com/OndraM))
- Branch alias for 1.1.x-dev
- Dependabot config

### Updated

- `composer.lock` tracks `symfony/yaml` v5.2.5
- Add compare links to changelog (resolves #14)
- Updated box.json to support changes in upstream requirements

### Fixed

- Stopped notice when Composer manifest name key is undefined (via [@SimonMacIntyre](https://github.com/SimonMacIntyre))

## [1.1.3] - 2018-03-27

### Updated

- `composer.lock` tracks `symfony/yaml` v4.0.6

### Fixed

- Added input args validation to check for multiple files and updated README
  (fixes #7)
- Improved syntax in README docs (resolves #4)

## [1.1.2] - 2017-12-07

### Added

- Added support for Symfony 4 YAML component
- New CLI option for displaying application version
- README documentation now includes 'dependencies' badge

### Updated

- Refactored custom 'UsageException' class into standalone file
- Updated application descriptions to emphasise 'compact' design of the application
- composer update now tracks latest Symfony 4 YAML in local sandbox (composer.lock)

### Fixed

- Fix to accommodate changes in the Yaml::parse method introduced in v3

## [1.1.1] - 2016-11-11

### Added

- Switched to full array notation, allowing legacy PHP support (via [neilime](https://github.com/neilime))
- composer update tracks latest Symfony 3 YAML in local sandbox (composer.lock)

## [1.1.0] - 2016-09-12

### Added

- Support for reading from stdin
- box.json manifest for building PHAR binaries
- Enabled support for Symfony 3 YAML component

## [1.0.0] - 2016-03-02

### Added

- Initial release

[1.0.0]: https://github.com/j13k/yaml-lint/compare/e2142c1..1.0.0

[1.1.0]: https://github.com/j13k/yaml-lint/compare/1.0.0..1.1.0

[1.1.1]: https://github.com/j13k/yaml-lint/compare/1.1.0..1.1.1

[1.1.2]: https://github.com/j13k/yaml-lint/compare/1.1.1..1.1.2

[1.1.3]: https://github.com/j13k/yaml-lint/compare/1.1.2..1.1.3

[1.1.4]: https://github.com/j13k/yaml-lint/compare/1.1.3..1.1.4

[1.1.5]: https://github.com/j13k/yaml-lint/compare/1.1.4..1.1.5

[1.1.6]: https://github.com/j13k/yaml-lint/compare/1.1.5..1.1.6

[1.1.x-dev]: https://github.com/j13k/yaml-lint/compare/1.1.6..HEAD
