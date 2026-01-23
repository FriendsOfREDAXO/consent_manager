<?php
/**
 * Modern Theme Preview with Website Mockup
 * Shows consent manager themes in a realistic browser mockup with switchable dark/light mode
 */

use FriendsOfRedaxo\ConsentManager\Frontend;
use FriendsOfRedaxo\ConsentManager\Theme;

$addon = rex_addon::get('consent_manager');
$preview = rex_request::request('preview', 'string', '');
$clang_id = rex_clang::getStartId();

if ('' === $preview) {
    exit('No theme specified');
}

// Get theme info and compiled CSS
$cmtheme = new Theme($preview);
$theme_options = $cmtheme->getThemeInformation();
$cmstyle = $cmtheme->getCompiledStyle();
$cmbox = Frontend::getFragment(0, 0, 'ConsentManager/box.php');

// Pastel color schemes for mockup variety
$colorSchemes = [
    ['primary' => '#f4a5ae', 'secondary' => '#ffc4d0', 'accent' => '#e88098', 'bg' => '#fff5f7', 'card' => '#fffbfc'], // Pastel Pink
    ['primary' => '#a8d8ea', 'secondary' => '#d4f1f9', 'accent' => '#7eb3d1', 'bg' => '#f5fbfd', 'card' => '#fbfeff'], // Pastel Blue
    ['primary' => '#b0e8b0', 'secondary' => '#d4f4d4', 'accent' => '#8cd98c', 'bg' => '#f6fdf6', 'card' => '#fbfefb'], // Pastel Green
    ['primary' => '#d4b5e0', 'secondary' => '#edd4f5', 'accent' => '#b896cc', 'bg' => '#faf7fc', 'card' => '#fdfbfe'], // Pastel Lavender
    ['primary' => '#ffc5a1', 'secondary' => '#ffe4d0', 'accent' => '#f5a674', 'bg' => '#fff9f5', 'card' => '#fffdfb'], // Pastel Peach
    ['primary' => '#ffe5a1', 'secondary' => '#fff4d0', 'accent' => '#f5d574', 'bg' => '#fffdf5', 'card' => '#fffffb'], // Pastel Yellow
    ['primary' => '#a1e5e5', 'secondary' => '#d0f4f4', 'accent' => '#74d5d5', 'bg' => '#f5fdfd', 'card' => '#fbfefe'], // Pastel Turquoise
    ['primary' => '#e5c1e5', 'secondary' => '#f4d9f4', 'accent' => '#d59dd5', 'bg' => '#fdf7fd', 'card' => '#fefbfe'], // Pastel Violet
];
$scheme = $colorSchemes[array_rand($colorSchemes)];

// Layout variants
$layouts = ['default', 'centered', 'sidebar', 'split'];
$layout = $layouts[array_rand($layouts)];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($theme_options['name'] ?? $preview) ?> - Theme Preview</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-light: <?= $scheme['bg'] ?>;
            --bg-dark: #1a1a1a;
            --card-light: <?= $scheme['card'] ?>;
            --card-dark: #2d2d2d;
            --text-light: #1a1a1a;
            --text-dark: #f5f5f5;
            --border-light: #e0e0e0;
            --border-dark: #404040;
            --primary: <?= $scheme['primary'] ?>;
            --secondary: <?= $scheme['secondary'] ?>;
            --accent: <?= $scheme['accent'] ?>;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: var(--bg-light);
            color: var(--text-light);
            margin: 0;
            min-height: 100vh;
            transition: background 0.3s, color 0.3s;
            position: relative;
        }

        body.dark-mode {
            background: var(--bg-dark);
            color: var(--text-dark);
        }

        /* Consent Manager Box über der gesamten Seite */
        .consent_manager-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 100;
        }

        /* Navigation */
        .site-nav {
            background: var(--card-light);
            border-bottom: 1px solid var(--border-light);
            padding: 0 20px;
            position: sticky;
            top: 0;
            z-index: 50;
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        body.dark-mode .site-nav {
            background: var(--card-dark);
            border-bottom-color: var(--border-dark);
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 60px;
        }

        .site-logo {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
        }

        .nav-menu {
            display: flex;
            gap: 4px;
            align-items: center;
        }

        .nav-item {
            background: transparent;
            color: var(--text-light);
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        body.dark-mode .nav-item {
            color: var(--text-dark);
        }

        .nav-item:hover {
            background: var(--bg-light);
        }

        body.dark-mode .nav-item:hover {
            background: var(--bg-dark);
        }

        .nav-item svg {
            width: 18px;
            height: 18px;
            fill: currentColor;
        }

        .nav-item.close-btn {
            color: #ef4444;
        }

        .nav-item.close-btn:hover {
            background: #fef2f2;
        }

        body.dark-mode .nav-item.close-btn:hover {
            background: #7f1d1d;
        }

        /* Main Content */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 60px 20px;
        }

        /* Layout: Centered */
        body.layout-centered .main-content {
            max-width: 900px;
            text-align: center;
        }

        body.layout-centered .theme-badges {
            justify-content: center;
        }

        body.layout-centered .features-grid {
            grid-template-columns: 1fr;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Layout: Sidebar */
        body.layout-sidebar .main-content {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 40px;
            max-width: 1600px;
            align-items: start;
        }

        body.layout-sidebar .sidebar-wrapper {
            position: sticky;
            top: 80px;
        }

        body.layout-sidebar .content-wrapper {
            min-width: 0;
        }

        /* Layout: Split */
        body.layout-split .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            max-width: 1600px;
        }

        body.layout-split .left-column {
            display: flex;
            flex-direction: column;
            gap: 40px;
        }

        body.layout-split .right-column {
            display: flex;
            flex-direction: column;
            gap: 40px;
        }

        /* Theme Info Section */
        .theme-info-section {
            background: var(--card-light);
            padding: 40px;
            border-radius: 12px;
            margin-bottom: 40px;
            border: 1px solid var(--border-light);
            transition: all 0.3s;
        }

        body.dark-mode .theme-info-section {
            background: var(--card-dark);
            border-color: var(--border-dark);
        }

        .theme-info-section h1 {
            font-size: 32px;
            margin-bottom: 12px;
            color: var(--primary);
        }

        .theme-info-section p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
            opacity: 0.8;
        }

        .theme-badges {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .theme-badge {
            background: var(--bg-light);
            color: var(--text-light);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            border: 1px solid var(--border-light);
        }

        body.dark-mode .theme-badge {
            background: var(--bg-dark);
            color: var(--text-dark);
            border-color: var(--border-dark);
        }

        /* Hero Section */
        .hero-section {
            text-align: center;
            padding: 80px 20px;
            margin-bottom: 60px;
        }

        .hero-section h2 {
            font-size: 48px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-section p {
            font-size: 18px;
            opacity: 0.7;
            margin-bottom: 30px;
        }

        .cta-button {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 16px 40px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-size: 16px;
            transition: all 0.2s;
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .feature-card {
            background: var(--card-light);
            padding: 30px;
            border-radius: 12px;
            border: 1px solid var(--border-light);
            transition: all 0.3s;
        }

        body.dark-mode .feature-card {
            background: var(--card-dark);
            border-color: var(--border-dark);
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .feature-icon svg {
            width: 32px;
            height: 32px;
            fill: white;
        }

        .feature-card h3 {
            font-size: 20px;
            margin-bottom: 12px;
            color: var(--text-light);
        }

        body.dark-mode .feature-card h3 {
            color: var(--text-dark);
        }

        .feature-card p {
            font-size: 14px;
            line-height: 1.6;
            opacity: 0.7;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-menu {
                gap: 2px;
            }

            .nav-item {
                padding: 6px 10px;
                font-size: 12px;
            }

            .main-content {
                padding: 30px 15px;
            }

            .theme-info-section {
                padding: 25px;
            }

            .theme-info-section h1 {
                font-size: 24px;
            }

            .hero-section {
                padding: 50px 15px;
            }

            .hero-section h2 {
                font-size: 32px;
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            body.layout-sidebar .main-content,
            body.layout-split .main-content {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <?php if ($cmstyle): ?>
        <style><?= $cmstyle ?></style>
    <?php endif; ?>
</head>
<body class="layout-<?= $layout ?>">
    <!-- Navigation -->
    <nav class="site-nav">
        <div class="nav-container">
            <div class="site-logo">Preview</div>
            <div class="nav-menu">
                <button class="nav-item" onclick="showConsentBox()" title="Cookie-Einstellungen anzeigen">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8 0-.29.02-.58.05-.86 2.36-1.05 4.23-2.98 5.21-5.37C11.07 8.33 14.05 10 17.42 10c.78 0 1.53-.09 2.25-.26.21.71.33 1.47.33 2.26 0 4.41-3.59 8-8 8z"/>
                        <circle cx="8.5" cy="9.5" r="1.5"/>
                        <circle cx="15" cy="9" r="1"/>
                        <circle cx="12" cy="15" r="1.5"/>
                        <circle cx="17" cy="13" r="1"/>
                    </svg>
                    <span>Cookie-Box</span>
                </button>
                <button class="nav-item" onclick="toggleBackground()" title="Hintergrund wechseln">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span id="bgState">Hell</span>
                </button>
                <button class="nav-item close-btn" onclick="closePreview()" title="Vorschau schließen">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span>Schließen</span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <?php if ($layout === 'sidebar'): ?>
            <!-- Sidebar Layout -->
            <div class="sidebar-wrapper">
                <section class="theme-info-section">
                    <h1><?= htmlspecialchars($theme_options['name'] ?? $preview) ?></h1>
                    <p><?= htmlspecialchars($theme_options['description'] ?? 'Theme-Vorschau für Consent Manager') ?></p>
                    <div class="theme-badges">
                        <span class="theme-badge">Type: <?= htmlspecialchars($theme_options['type'] ?? 'Modal') ?></span>
                        <span class="theme-badge">Style: <?= htmlspecialchars($theme_options['style'] ?? 'Modern') ?></span>
                        <span class="theme-badge">Author: <?= htmlspecialchars($theme_options['autor'] ?? 'Unknown') ?></span>
                    </div>
                </section>
            </div>
            <div class="content-wrapper">
                <section class="hero-section">
                    <h2>Beispiel Website</h2>
                    <p>Diese Seite dient als Vorschau für das Cookie-Consent-Theme</p>
                    <button class="cta-button" onclick="showConsentBox()">Cookie-Einstellungen öffnen</button>
                </section>
                <section class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                        </div>
                        <h3>Theme-Design</h3>
                        <p>Dieses Theme zeigt, wie die Cookie-Einstellungen auf Ihrer Website aussehen werden.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7 10l5 5 5-5z"/>
                                <path d="M12 4v16"/>
                            </svg>
                        </div>
                        <h3>Hintergrund-Modus</h3>
                        <p>Mit dem Hintergrund-Toggle im Menü können Sie zwischen hellem und dunklem Hintergrund wechseln.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </div>
                        <h3>Cookie-Verwaltung</h3>
                        <p>Das Theme kann individuell angepasst werden. Farben, Schriften und Layout werden über den Theme-Editor konfiguriert.</p>
                    </div>
                </section>
            </div>
        <?php elseif ($layout === 'split'): ?>
            <!-- Split Layout -->
            <div class="left-column">
                <section class="theme-info-section">
                    <h1><?= htmlspecialchars($theme_options['name'] ?? $preview) ?></h1>
                    <p><?= htmlspecialchars($theme_options['description'] ?? 'Theme-Vorschau für Consent Manager') ?></p>
                    <div class="theme-badges">
                        <span class="theme-badge">Type: <?= htmlspecialchars($theme_options['type'] ?? 'Modal') ?></span>
                        <span class="theme-badge">Style: <?= htmlspecialchars($theme_options['style'] ?? 'Modern') ?></span>
                        <span class="theme-badge">Author: <?= htmlspecialchars($theme_options['autor'] ?? 'Unknown') ?></span>
                    </div>
                </section>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                    <h3>Theme-Design</h3>
                    <p>Dieses Theme zeigt, wie die Cookie-Einstellungen auf Ihrer Website aussehen werden.</p>
                </div>
            </div>
            <div class="right-column">
                <section class="hero-section">
                    <h2>Beispiel Website</h2>
                    <p>Diese Seite dient als Vorschau für das Cookie-Consent-Theme</p>
                    <button class="cta-button" onclick="showConsentBox()">Cookie-Einstellungen öffnen</button>
                </section>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 10l5 5 5-5z"/>
                            <path d="M12 4v16"/>
                        </svg>
                    </div>
                    <h3>Hintergrund-Modus</h3>
                    <p>Mit dem Hintergrund-Toggle im Menü können Sie zwischen hellem und dunklem Hintergrund wechseln.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <h3>Cookie-Verwaltung</h3>
                    <p>Das Theme kann individuell angepasst werden.</p>
                </div>
            </div>
        <?php else: ?>
            <!-- Default & Centered Layout -->
            <section class="theme-info-section">
                <h1><?= htmlspecialchars($theme_options['name'] ?? $preview) ?></h1>
                <p><?= htmlspecialchars($theme_options['description'] ?? 'Theme-Vorschau für Consent Manager') ?></p>
                <div class="theme-badges">
                    <span class="theme-badge">Type: <?= htmlspecialchars($theme_options['type'] ?? 'Modal') ?></span>
                    <span class="theme-badge">Style: <?= htmlspecialchars($theme_options['style'] ?? 'Modern') ?></span>
                    <span class="theme-badge">Author: <?= htmlspecialchars($theme_options['autor'] ?? 'Unknown') ?></span>
                </div>
            </section>

            <section class="hero-section">
                <h2>Beispiel Website</h2>
                <p>Diese Seite dient als Vorschau für das Cookie-Consent-Theme</p>
                <button class="cta-button" onclick="showConsentBox()">Cookie-Einstellungen öffnen</button>
            </section>

            <section class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                    <h3>Theme-Design</h3>
                    <p>Dieses Theme zeigt, wie die Cookie-Einstellungen auf Ihrer Website aussehen werden. Klicken Sie auf "Cookie-Box" im Menü, um die Consent-Box zu öffnen.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 10l5 5 5-5z"/>
                            <path d="M12 4v16"/>
                        </svg>
                    </div>
                    <h3>Hintergrund-Modus</h3>
                    <p>Mit dem Hintergrund-Toggle im Menü können Sie zwischen hellem und dunklem Hintergrund wechseln, um das Theme in verschiedenen Kontexten zu testen.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <h3>Cookie-Verwaltung</h3>
                    <p>Das Theme kann individuell angepasst werden. Farben, Schriften und Layout werden über den Theme-Editor konfiguriert.</p>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <!-- Consent Manager Box -->
    <?= $cmbox ?>

    <script>
        // Close Preview (go back to theme page)
        function closePreview() {
            if (window.parent && window.parent.consent_manager_close_preview) {
                window.parent.consent_manager_close_preview();
            } else {
                window.location.href = '?page=consent_manager/theme';
            }
        }

        // Preview Background Toggle (affects only preview background, not the theme)
        function toggleBackground() {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            const stateBtn = document.getElementById('bgState');
            if (stateBtn) {
                stateBtn.textContent = isDark ? 'Dunkel' : 'Hell';
            }
            localStorage.setItem('previewDarkMode', isDark ? 'true' : 'false');
        }

        // Load saved preference
        if (localStorage.getItem('previewDarkMode') === 'true') {
            toggleBackground();
        }

        // Show Consent Box
        function showConsentBox() {
            const consentBox = document.getElementById('consent_manager-background');
            if (consentBox) {
                consentBox.classList.remove('consent_manager-hidden');
                
                // Focus first checkbox
                const firstCheckbox = consentBox.querySelector('input[type="checkbox"]');
                if (firstCheckbox) {
                    setTimeout(() => firstCheckbox.focus(), 100);
                }
            }
        }

        // Setup Consent Manager
        window.addEventListener('load', function() {
            const consentBox = document.getElementById('consent_manager-background');
            if (!consentBox) return;

            // Remove link hrefs in preview
            consentBox.querySelectorAll('.consent_manager-sitelinks a').forEach(link => {
                link.removeAttribute('href');
            });

            // Details toggle
            const toggleBtn = document.getElementById('consent_manager-toggle-details');
            const detailSection = document.getElementById('consent_manager-detail');
            if (toggleBtn && detailSection) {
                toggleBtn.addEventListener('click', () => {
                    detailSection.classList.toggle('consent_manager-hidden');
                });
            }

            // Close buttons
            consentBox.querySelectorAll('.consent_manager-close').forEach(btn => {
                btn.addEventListener('click', () => {
                    consentBox.classList.add('consent_manager-hidden');
                    if (detailSection && !detailSection.classList.contains('consent_manager-hidden')) {
                        detailSection.classList.add('consent_manager-hidden');
                    }
                });
            });

            // ESC key to close
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    consentBox.classList.add('consent_manager-hidden');
                    if (detailSection && !detailSection.classList.contains('consent_manager-hidden')) {
                        detailSection.classList.add('consent_manager-hidden');
                    }
                }
            });

            // Immer Consent-Box in Preview anzeigen
            showConsentBox();
        });
    </script>
</body>
</html>
