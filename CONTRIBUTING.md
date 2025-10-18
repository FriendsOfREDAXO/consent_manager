# Contributing to Consent Manager

[🇩🇪 Deutsche Version](#beiträge-zum-consent-manager)

Thank you for your interest in contributing to the Consent Manager! This document provides guidelines for contributing to this project.

## Table of Contents

- [Commit Message Guidelines](#commit-message-guidelines)
- [Branching Strategy](#branching-strategy)
- [Pull Requests](#pull-requests)
- [Code Style](#code-style)

## Commit Message Guidelines

We follow the [Conventional Commits](https://www.conventionalcommits.org/) specification. This leads to **more readable messages** that are easy to follow when looking through the project history.

### Commit Message Format

Each commit message consists of a **header**, an optional **body**, and an optional **footer**:

```
<type>(<scope>): <subject>

<body>

<footer>
```

The **header** is mandatory and must conform to the format above.

### Type

Must be one of the following:

- **feat**: A new feature
- **fix**: A bug fix
- **docs**: Documentation only changes
- **style**: Changes that do not affect the meaning of the code (white-space, formatting, missing semi-colons, etc)
- **refactor**: A code change that neither fixes a bug nor adds a feature
- **perf**: A code change that improves performance
- **test**: Adding missing tests or correcting existing tests
- **build**: Changes that affect the build system or external dependencies
- **ci**: Changes to our CI configuration files and scripts
- **chore**: Other changes that don't modify src or test files
- **revert**: Reverts a previous commit

### Scope

The scope should be the name of the affected module or component:

- **frontend**: Frontend components (JavaScript, CSS, fragments)
- **backend**: Backend/admin panel components
- **api**: API changes
- **inline**: Inline consent functionality
- **debug**: Debug functionality
- **setup**: Setup and installation
- **i18n**: Internationalization
- **a11y**: Accessibility improvements

### Subject

The subject contains a succinct description of the change:

- Use the imperative, present tense: "change" not "changed" nor "changes"
- Don't capitalize the first letter
- No dot (.) at the end
- Maximum 72 characters

### Body

The body should include the motivation for the change and contrast this with previous behavior. It is optional but recommended for complex changes.

### Footer

The footer should contain any information about **Breaking Changes** and is also the place to reference GitHub issues that this commit closes.

**Breaking Changes** should start with the word `BREAKING CHANGE:` with a space or two newlines.

### Examples

#### Simple feature addition
```
feat(inline): add support for TikTok embeds

Add TikTok embed support to the inline consent functionality.
This allows users to embed TikTok videos with consent placeholders.
```

#### Bug fix
```
fix(frontend): correct cookie expiration calculation

The cookie expiration was calculated incorrectly for dates
beyond 2038. This fix uses a more robust date calculation.

Fixes #123
```

#### Documentation update
```
docs: update README with inline consent examples

Add comprehensive examples for inline consent usage including
YouTube, Vimeo, and Google Maps integrations.
```

#### Breaking change
```
feat(api): change consent cookie structure

BREAKING CHANGE: The consent cookie structure has changed.
Migration is required for existing installations.

The new structure provides better performance and more
flexibility for multi-domain setups.
```

#### Multiple related changes
```
feat(a11y): improve keyboard navigation and screen reader support

- Add proper ARIA labels to all interactive elements
- Implement focus trap in consent dialog
- Add keyboard shortcuts documentation
- Improve contrast ratios for WCAG 2.1 AA compliance

This commit bundles all accessibility improvements for the
consent dialog to maintain context and provide a complete
feature update.

Closes #304, #326
```

## Bundling Related Changes

**Do bundle together:**
- Changes that belong to the same feature
- Related bug fixes that address the same issue
- Documentation updates for a specific feature
- Refactorings that affect the same module

**Don't bundle together:**
- Unrelated features
- Bug fixes for different issues
- Changes across completely different modules (unless they're part of one feature)

**Example of good bundling:**
```
feat(frontend): add new theme and update theme selector

- Add new "Modern Dark" theme with SCSS source
- Update theme selector with preview images
- Add theme documentation to README
- Include responsive breakpoints for mobile

All changes are part of the new theme feature.
```

**Example of bad bundling (avoid this):**
```
fix: various updates

- Fix cookie bug
- Update README
- Add new service
- Change CSS colors
```

## Branching Strategy

- `master` - stable release branch
- `develop` - development branch (if used)
- `feature/description` - for new features
- `fix/description` - for bug fixes
- `docs/description` - for documentation updates

## Pull Requests

1. **Create a meaningful PR title** following the commit message format
2. **Describe your changes** in the PR description
3. **Reference related issues** using `Fixes #123` or `Closes #456`
4. **Keep PRs focused** - one feature or fix per PR
5. **Update documentation** if your changes require it
6. **Add tests** if applicable

## Code Style

- Follow the existing code style in the project
- Use meaningful variable and function names
- Comment complex logic
- Keep functions small and focused
- Write self-documenting code

---

# Beiträge zum Consent Manager

[🇬🇧 English Version](#contributing-to-consent-manager)

Vielen Dank für dein Interesse, zum Consent Manager beizutragen! Dieses Dokument enthält Richtlinien für Beiträge zu diesem Projekt.

## Inhaltsverzeichnis

- [Commit-Message-Richtlinien](#commit-message-richtlinien)
- [Branching-Strategie](#branching-strategie)
- [Pull Requests](#pull-requests-1)
- [Code-Stil](#code-stil)

## Commit-Message-Richtlinien

Wir folgen der [Conventional Commits](https://www.conventionalcommits.org/) Spezifikation. Dies führt zu **besser lesbaren Nachrichten**, denen man einfach folgen kann, wenn man die Projekthistorie durchsieht.

### Commit-Message-Format

Jede Commit-Message besteht aus einem **Header**, einem optionalen **Body** und einem optionalen **Footer**:

```
<typ>(<bereich>): <betreff>

<body>

<footer>
```

Der **Header** ist verpflichtend und muss dem obigen Format entsprechen.

### Typ

Muss einer der folgenden sein:

- **feat**: Ein neues Feature
- **fix**: Eine Fehlerbehebung
- **docs**: Nur Dokumentationsänderungen
- **style**: Änderungen, die die Bedeutung des Codes nicht beeinflussen (Leerzeichen, Formatierung, fehlende Semikolons, etc.)
- **refactor**: Eine Code-Änderung, die weder einen Fehler behebt noch ein Feature hinzufügt
- **perf**: Eine Code-Änderung, die die Performance verbessert
- **test**: Hinzufügen fehlender Tests oder Korrektur bestehender Tests
- **build**: Änderungen am Build-System oder externen Abhängigkeiten
- **ci**: Änderungen an CI-Konfigurationsdateien und -Skripten
- **chore**: Andere Änderungen, die keine src- oder test-Dateien modifizieren
- **revert**: Macht einen vorherigen Commit rückgängig

### Bereich

Der Bereich sollte der Name des betroffenen Moduls oder der Komponente sein:

- **frontend**: Frontend-Komponenten (JavaScript, CSS, Fragmente)
- **backend**: Backend-/Admin-Panel-Komponenten
- **api**: API-Änderungen
- **inline**: Inline-Consent-Funktionalität
- **debug**: Debug-Funktionalität
- **setup**: Setup und Installation
- **i18n**: Internationalisierung
- **a11y**: Verbesserungen der Barrierefreiheit

### Betreff

Der Betreff enthält eine prägnante Beschreibung der Änderung:

- Verwende die Imperativ-Form, Präsens: "ändere" nicht "geändert" oder "ändert"
- Erster Buchstabe kleingeschrieben
- Kein Punkt (.) am Ende
- Maximum 72 Zeichen

### Body

Der Body sollte die Motivation für die Änderung enthalten und diese mit dem vorherigen Verhalten kontrastieren. Er ist optional, aber für komplexe Änderungen empfohlen.

### Footer

Der Footer sollte Informationen über **Breaking Changes** enthalten und ist auch der Ort, um GitHub-Issues zu referenzieren, die dieser Commit schließt.

**Breaking Changes** sollten mit dem Wort `BREAKING CHANGE:` mit einem Leerzeichen oder zwei Zeilenumbrüchen beginnen.

### Beispiele

#### Einfaches neues Feature
```
feat(inline): Unterstützung für TikTok-Embeds hinzufügen

Fügt TikTok-Embed-Unterstützung zur Inline-Consent-Funktionalität hinzu.
Dies ermöglicht es Benutzern, TikTok-Videos mit Consent-Platzhaltern
einzubetten.
```

#### Fehlerbehebung
```
fix(frontend): Cookie-Ablauf-Berechnung korrigieren

Die Cookie-Ablaufzeit wurde für Daten nach 2038 falsch berechnet.
Dieser Fix verwendet eine robustere Datumsberechnung.

Fixes #123
```

#### Dokumentations-Update
```
docs: README mit Inline-Consent-Beispielen aktualisieren

Fügt umfassende Beispiele für die Verwendung von Inline-Consent hinzu,
einschließlich YouTube-, Vimeo- und Google-Maps-Integrationen.
```

#### Breaking Change
```
feat(api): Cookie-Struktur für Consent ändern

BREAKING CHANGE: Die Consent-Cookie-Struktur hat sich geändert.
Eine Migration ist für bestehende Installationen erforderlich.

Die neue Struktur bietet bessere Performance und mehr Flexibilität
für Multi-Domain-Setups.
```

#### Mehrere zusammenhängende Änderungen
```
feat(a11y): Tastaturnavigation und Screen-Reader-Support verbessern

- Korrekte ARIA-Labels zu allen interaktiven Elementen hinzufügen
- Focus Trap im Consent-Dialog implementieren
- Dokumentation für Tastaturkürzel hinzufügen
- Kontrastverhältnisse für WCAG 2.1 AA Konformität verbessern

Dieser Commit bündelt alle Barrierefreiheits-Verbesserungen für den
Consent-Dialog, um Kontext zu bewahren und ein vollständiges
Feature-Update bereitzustellen.

Closes #304, #326
```

## Zusammengehörende Änderungen bündeln

**Zusammen bündeln:**
- Änderungen, die zum gleichen Feature gehören
- Zusammenhängende Bugfixes, die dasselbe Problem beheben
- Dokumentations-Updates für ein bestimmtes Feature
- Refactorings, die dasselbe Modul betreffen

**Nicht zusammen bündeln:**
- Unabhängige Features
- Bugfixes für verschiedene Issues
- Änderungen in völlig unterschiedlichen Modulen (außer sie sind Teil eines Features)

**Beispiel für gutes Bündeln:**
```
feat(frontend): neues Theme hinzufügen und Theme-Auswahl aktualisieren

- Neues "Modern Dark" Theme mit SCSS-Quelle hinzufügen
- Theme-Auswahl mit Vorschaubildern aktualisieren
- Theme-Dokumentation zur README hinzufügen
- Responsive Breakpoints für Mobile einbauen

Alle Änderungen sind Teil des neuen Theme-Features.
```

**Beispiel für schlechtes Bündeln (vermeiden):**
```
fix: verschiedene Updates

- Cookie-Bug beheben
- README aktualisieren
- Neuen Service hinzufügen
- CSS-Farben ändern
```

## Branching-Strategie

- `master` - stabiler Release-Branch
- `develop` - Development-Branch (falls verwendet)
- `feature/beschreibung` - für neue Features
- `fix/beschreibung` - für Bugfixes
- `docs/beschreibung` - für Dokumentations-Updates

## Pull Requests

1. **Aussagekräftigen PR-Titel erstellen**, der dem Commit-Message-Format folgt
2. **Änderungen in der PR-Beschreibung erläutern**
3. **Verwandte Issues referenzieren** mit `Fixes #123` oder `Closes #456`
4. **PRs fokussiert halten** - ein Feature oder Fix pro PR
5. **Dokumentation aktualisieren**, wenn die Änderungen es erfordern
6. **Tests hinzufügen**, falls zutreffend

## Code-Stil

- Folge dem bestehenden Code-Stil im Projekt
- Verwende aussagekräftige Variablen- und Funktionsnamen
- Kommentiere komplexe Logik
- Halte Funktionen klein und fokussiert
- Schreibe selbstdokumentierenden Code

---

## Getting Help

If you have questions, please:

1. Check the [README.md](README.md) for documentation
2. Search existing [issues](https://github.com/FriendsOfREDAXO/consent_manager/issues)
3. Ask in the [REDAXO Slack Community](https://redaxo.org/support/community/#slack)
4. Create a new issue if needed

## Hilfe erhalten

Bei Fragen:

1. Prüfe die [README.md](README.md) für Dokumentation
2. Durchsuche existierende [Issues](https://github.com/FriendsOfREDAXO/consent_manager/issues)
3. Frage in der [REDAXO Slack Community](https://redaxo.org/support/community/#slack)
4. Erstelle bei Bedarf ein neues Issue

---

Thank you for contributing! / Vielen Dank für deinen Beitrag!
