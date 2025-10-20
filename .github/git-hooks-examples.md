# Git-Hooks für bessere Commit-Messages

## Pre-commit Hook Beispiel
# Kopiere nach .git/hooks/commit-msg und mache es ausführbar

#!/bin/sh
commit_regex='^(feat|fix|docs|style|refactor|test|chore|perf|ci|build|revert)(\(.+\))?: .{1,50}'

error_msg="Commit-Message entspricht nicht dem Format!

Format: <type>(<scope>): <subject>

Beispiele:
  feat(theme): Fügt neuen Theme-Editor hinzu
  fix(inline): Behebt Button-Attribute  
  docs: Aktualisiert README
  chore: Update dependencies

Erlaubte Types: feat, fix, docs, style, refactor, test, chore, perf, ci, build, revert"

if ! grep -qE "$commit_regex" "$1"; then
    echo "$error_msg" >&2
    exit 1
fi