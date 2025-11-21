# Deprecated

use https://github.com/github/super-linter instead!


# linter
Linter commandline für REDAXO.

Mit diesem Tool ist es möglich beliebige Dateien bzgl. gängigen Fehlern zu überprüfen.
Aktuell werden folgende Dateien überprüft:

- PHP Dateien
- YAML Dateien
- JSON Dateien
- SQL  Dateien
- CSS Dateien

## Setup in Travis CI


### Datei `.travis.yml` im gewünschten github repository erzeugen

.. wenn man noch keine `.travis.yml` hat..

```yml
language: php

php:
    - '7.1' # REDAXO5.8+ min-php version

cache:
    directories:
        - $HOME/.composer/cache
  
before_install:
    - phpenv config-rm xdebug.ini || echo "xdebug not available"
    
script:
    - composer require --dev friendsofredaxo/linter
    - vendor/bin/rexlint
```

### Auf https://travis-ci.org via github-login anmelden und das Repository für TravisCI aktivieren.

Beispiel für FriendsOfREDAXO/minibar:

Account-Settings öffnen:
![image](https://user-images.githubusercontent.com/120441/55288765-b8268500-53bc-11e9-9139-6e904c4fa3c8.png)

Repository aktivieren:
![image](https://user-images.githubusercontent.com/120441/55288776-dc826180-53bc-11e9-9625-27a87c4d1544.png)

-> Wenn man jetzt ein neues Pull Request öffnet, laufen die Checks und man bekommt entweder ein OK oder ein KO:

![image](https://user-images.githubusercontent.com/120441/55288790-050a5b80-53bd-11e9-90aa-455464003fb8.png)


## Setup in GithubActions

```yml
# ... snip

jobs:

  rex-lint:
    name: REX Linting
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v2
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 7.1 # adjust accordingly
        extensions: intl
        coverage: none # disable xdebug, pcov
    - name: Install Dependencies
      run: composer install --prefer-dist
    - run: |
        composer require --dev friendsofredaxo/linter
        vendor/bin/rexlint
```
