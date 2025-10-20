# Makefile für Git-Setup und Commit-Validierung

.PHONY: setup-git lint-commits check-branch clean-branches

# Git-Template und Hooks einrichten
setup-git:
	@echo "🛠️  Richte Git-Template ein..."
	git config commit.template .gitmessage
	@echo "✅ Git-Template aktiviert (.gitmessage)"
	@echo "💡 Verwende 'git commit' (ohne -m) für Template"
	@echo ""
	@echo "🔧 Optional: Git-Hook für Validierung:"
	@echo "   cp .github/git-hooks-examples.md .git/hooks/commit-msg"
	@echo "   chmod +x .git/hooks/commit-msg"

# Letzte Commits auf Format prüfen
lint-commits:
	@echo "🔍 Prüfe letzte 10 Commit-Messages..."
	@git log --oneline -10 | while read line; do \
		commit=$$(echo "$$line" | cut -d' ' -f1); \
		message=$$(echo "$$line" | cut -d' ' -f2-); \
		if echo "$$message" | grep -qE '^(feat|fix|docs|style|refactor|test|chore|perf|ci|build|revert)(\(.+\))?: .{1,50}'; then \
			echo "✅ $$commit: $$message"; \
		else \
			echo "❌ $$commit: $$message"; \
		fi; \
	done

# Aktueller Branch Status
check-branch:
	@echo "📋 Git Status:"
	@echo "Branch: $$(git branch --show-current)"
	@echo "Commits ahead of main: $$(git rev-list --count main..HEAD)"
	@echo "Modified files: $$(git status --porcelain | wc -l | tr -d ' ')"

# Bereinige merged Branches
clean-branches:
	@echo "🧹 Bereinige merged Branches..."
	git branch --merged main | grep -v main | xargs -n 1 git branch -d || true
	@echo "✅ Lokale merged Branches entfernt"

# Zeige Beispiel-Commits
examples:
	@echo "📝 Beispiel-Commits:"
	@echo ""
	@echo "feat(theme): Fügt A11y Theme Editor hinzu"
	@echo "fix(inline): Behebt Button-Attribute in Placeholder"  
	@echo "docs: Aktualisiert README mit neuen Features"
	@echo "style(css): Formatiert SCSS-Dateien mit Prettier"
	@echo "refactor(api): Extrahiert Cache-Logic in eigene Klasse"
	@echo "test: Fügt Unit-Tests für Theme-System hinzu"
	@echo "chore: Update dependencies to latest versions"
	@echo "perf(inline): Optimiert Thumbnail-Cache Performance"