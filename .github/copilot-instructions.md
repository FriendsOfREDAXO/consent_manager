# REDAXO Consent Manager - Copilot Instructions

## Project Overview

This is the **REDAXO Consent Manager AddOn** - a comprehensive GDPR/DSGVO-compliant cookie consent management system for REDAXO CMS. The project provides advanced consent management with inline-consent system, accessibility features, and extensive theming capabilities.

**Repository:** FriendsOfREDAXO/consent_manager  
**Main Language:** PHP (REDAXO CMS AddOn)  
**Frontend:** JavaScript, SCSS/CSS  
**Database:** MySQL/MariaDB  

## Architecture & Key Components

### Core Structure
```
├── boot.php              # AddOn bootstrap file
├── package.yml           # AddOn configuration
├── lib/                  # Core PHP classes
│   ├── consent_manager_frontend.php    # Frontend controller
│   ├── consent_manager_theme.php       # Theme system
│   ├── consent_manager_inline.php      # Inline consent system
│   └── consent_manager_*.php           # Various utility classes
├── pages/                # Backend administration pages
│   ├── theme.php         # Theme selection and preview
│   ├── theme_editor.php  # A11y theme editor with color picker
│   └── *.php            # Domain, service, text management
├── fragments/            # Template fragments
│   ├── consent_manager_box.php         # Main consent dialog
│   └── consent_inline_placeholder.php  # Inline consent placeholder
├── assets/              # Frontend assets
│   ├── *.js             # JavaScript files (main, polyfills, debug)
│   └── *.css            # Compiled CSS themes
├── scss/                # SCSS source files
│   ├── consent_manager_frontend*.scss  # Theme sources
│   └── consent_manager_frontend_a11y*.scss # Accessibility themes
└── setup/               # Setup configurations
    └── *.json           # Default setup data
```

### Database Schema
- `rex_consent_manager_domain` - Domain configurations
- `rex_consent_manager_domain_service` - Service assignments  
- `rex_consent_manager_service` - Service definitions
- `rex_consent_manager_text` - Multilingual texts
- `rex_consent_manager_log` - Consent logging

## Development Guidelines

### Code Standards
- **PHP:** Follow REDAXO conventions, use `rex_` prefixes for classes
- **Naming:** Use snake_case for files, camelCase for methods
- **Security:** Always use `rex_escape()` for output, `rex_request()` for input
- **i18n:** Use `$addon->i18n('key')` for all translatable strings
- **Database:** Use `rex_sql` class, never direct SQL queries

### Commit Messages
Use **Conventional Commits** format (configured in `.gitmessage`):
```
<type>(<scope>): <subject>

Types: feat, fix, docs, style, refactor, test, chore, perf
Scopes: theme, inline, a11y, frontend, backend, config
```

### CSS/SCSS Guidelines
- **Themes:** Create new themes in `scss/consent_manager_frontend_theme_*.scss`
- **Variables:** Use SCSS variables for colors, spacing, fonts
- **Accessibility:** Follow WCAG 2.1 AA guidelines (4.5:1 contrast ratio)
- **BEM-style:** Use `.consent_manager-` prefix for all classes

### JavaScript Guidelines  
- **ES5 Compatible:** Support older browsers
- **No Dependencies:** Vanilla JS only (except js.cookie.min.js)
- **Event Delegation:** Use `data-` attributes instead of `onclick`
- **Debugging:** Use `consent_debug.js` for development logging

## Build & Development

### Theme Development
```bash
# Compile SCSS themes
make theme-compile

# Watch for changes
sass --watch scss:assets

# Theme preview
/redaxo/index.php?page=consent_manager/theme&preview=theme_name
```

### Git Workflow
```bash
# Setup development environment
make setup-git

# Check commits quality
make lint-commits

# Create feature branch
git checkout -b feat/new-feature

# Theme editor available at
/redaxo/index.php?page=consent_manager/theme_editor
```

### Testing
- **Frontend:** Test in multiple browsers, check accessibility
- **Backend:** Test in REDAXO backend with different user permissions  
- **Inline System:** Test with YouTube/Vimeo embeds
- **Themes:** Verify all theme variants in preview

## Key Features & Systems

### 1. Inline Consent System
- **Fragment-based:** `consent_inline_placeholder.php` 
- **Service Detection:** YouTube, Vimeo, Google Maps auto-detection
- **Thumbnail Cache:** Local caching system for external media
- **Domain-specific:** Per-domain inline-only mode

### 2. Theme System
- **SCSS-based:** Compile-time theme generation
- **A11y Themes:** 5 accessibility-optimized themes
- **Theme Editor:** Visual color picker in `/pages/theme_editor.php`
- **Project Themes:** Custom themes in `project/consent_manager_themes/`

### 3. Accessibility (WCAG 2.1 AA)
- **Keyboard Navigation:** ESC, Tab, Space, Enter support
- **Screen Readers:** Proper ARIA attributes
- **Focus Management:** Clear focus indicators
- **High Contrast:** Support for contrast preferences
- **Touch Targets:** Minimum 44x44px buttons

### 4. Internationalization
- **Multi-language:** German, English, Swedish
- **Text Management:** Backend interface for all strings
- **Parameter Support:** Dynamic content with placeholders

## API & Integration

### REX_VARS (Deprecated in 5.x)
```php
// OLD (deprecated)
REX_CONSENT_MANAGER[service=youtube]

// NEW (recommended)  
<?php echo consent_manager_frontend::getFragment(); ?>
```

### JavaScript API
```javascript
// Show consent box manually
consent_manager_showBox();

// Check consent status
consent_manager_hasconsent('youtube');

// Debug console (development)
consentManagerDebug.show();
```

### Inline Consent Integration
```html
<!-- YouTube with inline consent -->
<div data-consent-service="youtube" data-consent-src="...">
    <!-- Placeholder content -->
</div>
```

## Common Patterns

### Creating New Themes
1. Copy existing theme SCSS file
2. Modify theme JSON metadata header
3. Adjust SCSS variables
4. Place in `scss/` (core) or `project/consent_manager_themes/` (custom)
5. Compile and test in preview

### Adding New Services
1. Add service in backend under "Services"
2. Configure cookies and scripts
3. Test with inline consent system
4. Add service-specific handlers if needed

### Text Management
- All texts stored in database with UID system
- Backend interface for editing
- Export/import via JSON setup files
- Fallback to English if translation missing

## Important Notes

- **Always test accessibility** with keyboard navigation and screen readers
- **Use `rex_escape()`** for ALL user output to prevent XSS
- **Follow REDAXO patterns** - don't reinvent existing functionality  
- **Test themes** in both light and dark modes
- **Validate SCSS** before committing theme changes
- **Check browser compatibility** for JavaScript features
- **Document breaking changes** in CHANGELOG.md

## Debugging

### Frontend Debug Mode
```javascript
// Enable in domain settings, shows debug console
consentManagerDebug.show();
// Displays: consent status, cookies, localStorage, Google Consent Mode
```

### Backend Debug
- Check REDAXO system log for PHP errors
- Use `rex::isDebugMode()` for development output
- Monitor consent log table for user interactions

## Files to Always Check When Making Changes

1. **Fragments:** May need updates for new features
2. **JavaScript:** Check browser compatibility  
3. **SCSS:** Verify compilation and accessibility
4. **Database:** Check migrations in `install.php`
5. **i18n:** Update language files for new strings
6. **CHANGELOG.md:** Document all changes
7. **README.md:** Update if public API changes

Trust these instructions and refer to existing code patterns before exploring. The codebase follows consistent REDAXO conventions throughout.