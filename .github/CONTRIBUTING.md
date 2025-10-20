# Git-Konfiguration für bessere Commits

Dieses Repo verwendet **Conventional Commits** für bessere Git-Historie.

## 🛠️ Setup

**1. Commit-Message-Template aktivieren:**
```bash
git config commit.template .gitmessage
```

**2. Auto-Setup (einmalig):**
```bash
# Im Repo-Root ausführen:
make setup-git
```

## 📝 Commit-Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types:
- `feat`: Neue Funktionalität
- `fix`: Bugfix  
- `docs`: Dokumentation
- `style`: Formatierung
- `refactor`: Code-Umbau
- `test`: Tests
- `chore`: Build/Tools
- `perf`: Performance
- `ci`: CI/CD
- `build`: Build-System

### Scopes (optional):
- `theme`: Theme-System
- `inline`: Inline-Consent
- `a11y`: Accessibility  
- `frontend`: Frontend-Code
- `backend`: Backend-Code
- `config`: Konfiguration

## ✅ Gute Beispiele:

```bash
feat(theme): Fügt A11y Theme Editor hinzu

Ermöglicht visuelle Anpassung von Accessibility-Themes
mit Live-Vorschau und automatischem Export.

Fixes #326
```

```bash
fix(inline): Behebt Button-Attribute in Placeholder

- "Alle erlauben" Button verwendet data-service
- Einstellungen-Button entfernt hardcodiertes onclick
- Icon zurück zu fa-check-circle

Closes #386
```

## ❌ Schlechte Beispiele:

- `gib gas`
- `debugg lang`  
- `a11y2`
- `update`
- `fixes`

## 🔧 Git-Hooks (optional)

Für automatische Validierung:

```bash
# Kopiere Beispiel-Hook
cp .github/git-hooks-examples.md .git/hooks/commit-msg
chmod +x .git/hooks/commit-msg
```

## 📋 Makefile-Commands

```bash
make setup-git    # Git-Template und Hooks einrichten
make lint-commits # Letzte 10 Commits prüfen
```