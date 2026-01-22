<?php

use FriendsOfRedaxo\ConsentManager\Frontend;
use FriendsOfRedaxo\ConsentManager\Theme;
use FriendsOfRedaxo\ConsentManager\Utility;

$addon = rex_addon::get('consent_manager');

$preview = rex_request::request('preview', 'string', '');
$clang_id = rex_clang::getStartId();

// Theme-Preview

if ('' !== $preview) {
    rex_response::cleanOutputBuffers();

    $cmtheme = new Theme($preview);
    $theme_options = $cmtheme->getThemeInformation();
    
    $cmbox = '';
    if (count($theme_options) > 0) {
        $cmbox = Frontend::getFragment(0, 0, 'ConsentManager/box.php');
    } else {
        $cmbox = rex_view::error(rex_i18n::msg('consent_manager_error_css_notfound', $preview));
    }
    
    // Use Media Manager for CSS - remove project: prefix for filename
    $themeFile = str_replace('project:', '', $preview);
    $cssUrl = rex_media_manager::getUrl('consent_manager_theme', $themeFile) . '?t=' . time();

    ?><!doctype html>
<html lang="de">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($theme_options['name'] ?? $preview) ?></title>
<link rel="stylesheet" href="<?= $cssUrl ?>">
<style>
/* Basic Reset and Preview Styles */
::-moz-selection { background: rgba(0,0,0,0.1); }
::selection { background: rgba(0,0,0,0.1); }

body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
    font-size: 14px;
    line-height: 1.5;
    padding: 0;
    margin: 0;
    min-height: 100vh;
    background-color: #f0f0f0;
    color: #333;
    transition: background 0.5s ease, color 0.3s;
}

/* Zufällige Hintergrund-Varianten */
body.bg-gradient-1 { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
body.bg-gradient-2 { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
body.bg-gradient-3 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
body.bg-gradient-4 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
body.bg-gradient-5 { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
body.bg-gradient-6 { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }
body.bg-gradient-7 { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }
body.bg-gradient-8 { background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); }
body.bg-gradient-9 { background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); }
body.bg-gradient-10 { background: linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%); }

body.dark-mode { background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%); color: #f0f0f0; }
body.dark-mode.bg-gradient-1 { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); }
body.dark-mode.bg-gradient-2 { background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%); }
body.dark-mode.bg-gradient-3 { background: linear-gradient(135deg, #141e30 0%, #243b55 100%); }
body.dark-mode.bg-gradient-4 { background: linear-gradient(135deg, #000000 0%, #434343 100%); }
body.dark-mode.bg-gradient-5 { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); }
body.dark-mode.bg-gradient-6 { background: linear-gradient(135deg, #232526 0%, #414345 100%); }
body.dark-mode.bg-gradient-7 { background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%); }
body.dark-mode.bg-gradient-8 { background: linear-gradient(135deg, #4e54c8 0%, #8f94fb 100%); }
body.dark-mode.bg-gradient-9 { background: linear-gradient(135deg, #373b44 0%, #4286f4 100%); }
body.dark-mode.bg-gradient-10 { background: linear-gradient(135deg, #36d1dc 0%, #5b86e5 100%); }

#preview-header {
    background: rgba(255,255,255,0.95);
    padding: 15px 20px;
    border-bottom: 1px solid rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 99999;
    backdrop-filter: blur(10px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
body.dark-mode #preview-header {
    background: rgba(30,30,30,0.95);
    border-bottom: 1px solid rgba(255,255,255,0.1);
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.preview-header-left {
    display: flex;
    gap: 20px;
    align-items: center;
}

.theme-meta h1 {
    margin: 0 0 5px 0;
    font-size: 1.25rem;
}
.theme-meta p {
    margin: 0;
    font-size: 0.85rem;
    opacity: 0.7;
}

.preview-controls {
    display: flex;
    gap: 8px;
    align-items: center;
}

.preview-controls button {
    background: #fff;
    border: 1px solid #ddd;
    color: #333;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s;
}

body.dark-mode .preview-controls button {
    background: #2a2a2a;
    border-color: #444;
    color: #f0f0f0;
}

.preview-controls button:hover {
    background: #f5f5f5;
    border-color: #999;
    transform: translateY(-1px);
}

body.dark-mode .preview-controls button:hover {
    background: #333;
    border-color: #666;
}

.preview-controls button.active {
    background: #0066cc;
    color: #fff;
    border-color: #0066cc;
}

/* Consent Manager Box wird normal angezeigt wie im Original */
.consent_manager-hidden { 
    display: none !important; 
}

/* Padding für fixed header */
body {
    padding-top: 70px;
}
</style>
</head>
<body>

<div id="preview-header">
    <div class="preview-header-left">
        <div class="preview-controls">
            <button type="button" class="mode-toggle active" data-mode="light">☀️ Light</button>
            <button type="button" class="mode-toggle" data-mode="dark">🌙 Dark</button>
        </div>
        <div class="theme-meta">
            <h1><?= $theme_options['name'] ?? $preview ?></h1>
            <?php if (!empty($theme_options['description'])): ?>
                <p><?= $theme_options['description'] ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $cmbox ?>

<script>
window.onload = function() {
    // Zufälligen Hintergrund auswählen
    var gradients = ['bg-gradient-1', 'bg-gradient-2', 'bg-gradient-3', 'bg-gradient-4', 'bg-gradient-5', 
                     'bg-gradient-6', 'bg-gradient-7', 'bg-gradient-8', 'bg-gradient-9', 'bg-gradient-10'];
    var randomGradient = gradients[Math.floor(Math.random() * gradients.length)];
    document.body.classList.add(randomGradient);
    
    // Mode toggle buttons (immer initialisieren)
    var modeButtons = document.querySelectorAll('.mode-toggle');
    modeButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var mode = this.getAttribute('data-mode');
            
            console.log('Mode toggle clicked:', mode);
            
            // Update button states
            modeButtons.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            
            // Toggle body class
            if (mode === 'dark') {
                document.body.classList.add('dark-mode');
                console.log('Dark mode activated');
            } else {
                document.body.classList.remove('dark-mode');
                console.log('Light mode activated');
            }
        });
    });
    
    // Consent Manager Box
    var box = document.getElementById('consent_manager-background');
    if (box) {
        box.classList.remove('consent_manager-hidden');
        
        // Disable links
        box.querySelectorAll('.consent_manager-sitelinks a').forEach(function(link) {
            link.removeAttribute('href');
            link.style.cursor = 'default';
        });

        // Details toggle
        var detailsToggle = document.getElementById('consent_manager-toggle-details');
        var detailsDiv = document.getElementById('consent_manager-detail');
        if (detailsToggle && detailsDiv) {
            detailsToggle.addEventListener('click', function() {
                detailsDiv.classList.toggle('consent_manager-hidden');
            });
        }
        
        // Close buttons
        var closeBtns = box.querySelectorAll('.consent_manager-close');
        closeBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                box.classList.add('consent_manager-hidden');
                setTimeout(function() {
                    box.classList.remove('consent_manager-hidden');
                }, 500);
            });
        });
    } else {
        console.error('Consent Manager box not found');
    }
};
</script>

</body>
</html>
<?php
            exit;
}

// Eigenes CSS verwenden darf nicht aktiviert sein

if ('|1|' === $addon->getConfig('outputowncss', false)) {
    echo rex_view::error(rex_i18n::msg('consent_manager_config_owncss_active'));
}

// check Konfiguration
if (false === Utility::consentConfigured()) {
    echo rex_view::warning(rex_i18n::msg('consent_manager_cookiegroup_nodomain_notice'));
}

$content = '';
$buttons = '';

// csrf-Schutz

$csrfToken = rex_csrf_token::factory('consent_manager');

$theme = '';
if ('1' === rex_request::post('formsubmit', 'string')) {
    $theme = rex_request::post('theme', 'string', 'consent_manager_frontend.scss');
}

// Formular abgesendet - Theme löschen
if ('1' === rex_request::post('formsubmit', 'string') && '1' === rex_request::post('delete', 'string') && $csrfToken->isValid()) {
    $themeToDelete = rex_request::post('theme', 'string', '');
    $currentTheme = $addon->getConfig('theme', 'consent_manager_frontend.scss');
    
    // Nur project-Themes löschen und nicht das aktive Theme
    if (str_starts_with($themeToDelete, 'project:') && $themeToDelete !== $currentTheme) {
        $scssFile = rex_addon::get('project')->getPath('consent_manager_themes/' . str_replace('project:', '', $themeToDelete));
        $cssFile = $addon->getAssetsPath(str_replace(['project:', '.scss'], ['project_', '.css'], $themeToDelete));
        
        $deleted = false;
        if (file_exists($scssFile)) {
            rex_file::delete($scssFile);
            $deleted = true;
        }
        if (file_exists($cssFile)) {
            rex_file::delete($cssFile);
        }
        // Auch aus public assets löschen
        $publicCss = rex_path::addonAssets('consent_manager', str_replace(['project:', '.scss'], ['project_', '.css'], $themeToDelete));
        if (file_exists($publicCss)) {
            rex_file::delete($publicCss);
        }
        
        if ($deleted) {
            echo rex_view::success(rex_i18n::msg('consent_manager_theme_deleted'));
        } else {
            echo rex_view::error(rex_i18n::msg('consent_manager_theme_delete_error'));
        }
    }
}

// Formular abgesendet - Einstellungen speichern

if ('1' === rex_request::post('formsubmit', 'string') && !$csrfToken->isValid()) {
    echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
} elseif ('1' === rex_request::post('formsubmit', 'string') && '1' !== rex_request::post('delete', 'string')) {
    $cmtheme = new Theme();
    $theme_options = $cmtheme->getThemeInformation($theme);
    if (0 === count($theme_options)) {
        echo rex_view::error(rex_i18n::msg('consent_manager_config_invalid_theme'));
    } else {
        $addon->setConfig('theme', $theme);
        echo rex_view::success(rex_i18n::msg('consent_manager_config_saved'));
    }
}

// dump($_POST);
// dump($addon->getConfig('theme', false));

// Aktuell aktives Theme ermitteln
$configtheme = $addon->getConfig('theme', 'consent_manager_frontend.scss');
if ('' === $configtheme || false === $configtheme) {
    $configtheme = 'consent_manager_frontend.scss';
}

$cmtheme = new Theme();

// Hilfsfunktion für Theme-Kachel
$renderThemeCard = static function (string $themeid, array $theme_options, string $configtheme, rex_csrf_token $csrfToken, bool $isProjectTheme = false): string {
    $isActive = ($themeid === $configtheme);
    $activeClass = $isActive ? ' cm-theme-card--active' : '';
    $activeBadge = $isActive ? '<span class="label label-success cm-theme-badge">' . rex_i18n::msg('consent_manager_config_active_theme') . '</span>' : '';
    $projectBadge = $isProjectTheme ? '<span class="label label-info cm-theme-badge">' . rex_i18n::msg('consent_manager_theme_custom') . '</span>' : '';

    $authorlinks = [];
    $authors = explode(',', $theme_options['autor']);
    foreach ($authors as $author) {
        $url = 'https://github.com/' . str_replace('@', '', trim($author));
        $authorlinks[] = '<a href="' . $url . '" target="_blank">' . trim($author) . '</a>';
    }

    $confirmmsg = rex_i18n::msg('consent_manager_config_confirm_select', $theme_options['name']);
    $deletemsg = rex_i18n::msg('consent_manager_config_confirm_delete', $theme_options['name']);
    $formId = str_replace([':', '.'], '-', $themeid);

    // Delete button nur für project themes die nicht aktiv sind
    $deleteButton = '';
    if ($isProjectTheme && !$isActive) {
        $deleteButton = '<button class="btn btn-xs btn-danger" type="submit" name="delete" value="1" data-confirm="' . rex_escape($deletemsg) . '" title="' . rex_i18n::msg('consent_manager_config_btn_delete') . '">
                <i class="rex-icon fa-trash"></i>
            </button>';
    }

    $output = '
<div class="cm-theme-card' . $activeClass . '">
    <form action="' . rex_url::currentBackendPage() . '#' . $formId . '" method="post" id="' . $formId . '">
        <input type="hidden" name="formsubmit" value="1" />
        <input type="hidden" name="theme" value="' . rex_escape($themeid) . '" />
        ' . $csrfToken->getHiddenField() . '
        
        <div class="cm-theme-preview" title="' . rex_escape($theme_options['name']) . '" data-theme="' . rex_escape($themeid) . '">
            <div class="cm-theme-thumbnail">
                <iframe loading="lazy" class="cm-theme-iframe" src="?page=consent_manager/theme&preview=' . rex_escape($themeid) . '&nofocus" data-theme="' . rex_escape($themeid) . '"></iframe>
            </div>
        </div>
        
        <div class="cm-theme-content">
            <div class="cm-theme-info">
                <div class="cm-theme-badges">' . $activeBadge . $projectBadge . '</div>
                <h4 class="cm-theme-title">' . rex_escape($theme_options['name']) . '</h4>
                <p class="cm-theme-desc">' . rex_escape($theme_options['description']) . '</p>
                <div class="cm-theme-meta">
                    <span class="cm-theme-meta-item"><i class="rex-icon fa-paint-brush"></i> ' . rex_escape($theme_options['style']) . '</span>
                    <span class="cm-theme-meta-item"><i class="rex-icon fa-desktop"></i> ' . rex_escape($theme_options['type']) . '</span>
                </div>
                <div class="cm-theme-author">
                    <i class="rex-icon fa-user"></i> ' . implode(', ', $authorlinks) . '
                </div>
            </div>
            
            <div class="cm-theme-actions">
                <div class="cm-theme-actions-left">
                    <a href="?page=consent_manager/theme&preview=' . rex_escape($themeid) . '" class="btn btn-xs btn-default consent_manager-button-preview" data-theme="' . rex_escape($themeid) . '">
                        <i class="rex-icon fa-eye"></i> ' . rex_i18n::msg('consent_manager_config_btn_preview') . '
                    </a>
                    ' . ($isActive ? '' : '<button class="btn btn-xs btn-primary" type="submit" name="save" data-confirm="' . rex_escape($confirmmsg) . '">
                        <i class="rex-icon fa-check"></i> ' . rex_i18n::msg('consent_manager_config_btn_activate') . '
                    </button>') . '
                </div>
                ' . $deleteButton . '
            </div>
        </div>
    </form>
</div>';

    return $output;
};

// Sammle alle Themes
$projectThemes = [];
$addonThemes = [];

// Eigene Themes aus project-Addon (werden zuerst angezeigt)
if (true === rex_addon::exists('project')) {
    $themes = (array) glob(rex_addon::get('project')->getPath('consent_manager_themes/consent_manager_frontend*.scss'));
    natsort($themes);
    foreach ($themes as $themefile) {
        $themeid = 'project:' . basename((string) $themefile);
        $theme_options = $cmtheme->getThemeInformation($themeid);
        if (count($theme_options) > 0) {
            $projectThemes[$themeid] = $theme_options;
        }
    }
}

// Addon-Themes
$themes = (array) glob($addon->getPath('scss/consent_manager_frontend*.scss'));
natsort($themes);
foreach ($themes as $themefile) {
    $themeid = basename((string) $themefile);
    $theme_options = $cmtheme->getThemeInformation($themeid);
    if (count($theme_options) > 0) {
        $addonThemes[$themeid] = $theme_options;
    }
}

// CSS für kompaktes Grid-Layout mit Dark/Light Theme Support
echo '<style>
/* === CSS Variables - Dark Theme (Default) === */
.cm-theme-grid {
    --cm-bg-card: #2d3748;
    --cm-bg-actions: #1a202c;
    --cm-border: #4a5568;
    --cm-text: #e2e8f0;
    --cm-text-muted: #a0aec0;
    --cm-link: #63b3ed;
}

/* === System Light Theme === */
@media (prefers-color-scheme: light) {
    body:not(.rex-theme-dark) .cm-theme-grid {
        --cm-bg-card: #fff;
        --cm-bg-actions: #f9f9f9;
        --cm-border: #ddd;
        --cm-text: #333;
        --cm-text-muted: #666;
        --cm-link: #0066cc;
    }
}

/* === REDAXO Light Theme === */
body.rex-theme-light .cm-theme-grid {
    --cm-bg-card: #fff !important;
    --cm-bg-actions: #f9f9f9 !important;
    --cm-border: #ddd !important;
    --cm-text: #333 !important;
    --cm-text-muted: #666 !important;
    --cm-link: #0066cc !important;
}

/* === REDAXO Dark Theme === */
body.rex-theme-dark .cm-theme-grid {
    --cm-bg-card: #2d3748 !important;
    --cm-bg-actions: #1a202c !important;
    --cm-border: #4a5568 !important;
    --cm-text: #e2e8f0 !important;
    --cm-text-muted: #a0aec0 !important;
    --cm-link: #63b3ed !important;
}

.cm-theme-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}
@media (max-width: 1400px) {
    .cm-theme-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 900px) {
    .cm-theme-grid {
        grid-template-columns: 1fr;
    }
}
.cm-theme-card {
    background: var(--cm-bg-card);
    border: 1px solid var(--cm-border);
    border-radius: 4px;
    overflow: hidden;
    transition: box-shadow 0.2s, border-color 0.2s;
    color: var(--cm-text);
    display: flex;
    flex-direction: column;
}
.cm-theme-card form {
    display: flex;
    flex-direction: column;
    height: 100%;
}
.cm-theme-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
}
.cm-theme-card--active {
    border-color: #3bb594;
    border-width: 2px;
    box-shadow: 0 0 0 3px rgba(59,181,148,0.2);
}
.cm-theme-card--active:hover {
    border-color: #3bb594;
}
.cm-theme-preview {
    cursor: pointer;
    border-bottom: 1px solid var(--cm-border);
    width: 100%;
    padding-bottom: 62.5%;
    position: relative;
    overflow: hidden;
    background: #1a1a1a;
}
.cm-theme-thumbnail {
    position: absolute;
    top: 0;
    left: 0;
    width: 1440px;
    height: 900px;
    transform-origin: 0 0;
}
.cm-theme-iframe {
    width: 1440px;
    height: 900px;
    border: 0;
    opacity: 0;
    transition: opacity 0.3s;
}
.cm-theme-iframe.loaded {
    opacity: 1;
}
.cm-theme-content {
    display: flex;
    flex-direction: column;
    flex: 1;
}
.cm-theme-info {
    padding: 12px 15px 8px;
    flex: 1;
}
.cm-theme-badges {
    margin-bottom: 5px;
    min-height: 22px;
}
.cm-theme-badge {
    font-size: 10px;
    margin-right: 5px;
}
.cm-theme-title {
    margin: 0 0 5px 0;
    font-size: 14px;
    font-weight: 600;
    color: var(--cm-text);
}
.cm-theme-desc {
    margin: 0 0 8px 0;
    font-size: 12px;
    color: var(--cm-text-muted);
    line-height: 1.4;
}
.cm-theme-meta {
    display: flex;
    gap: 15px;
    margin-bottom: 5px;
}
.cm-theme-meta-item {
    font-size: 11px;
    color: var(--cm-text-muted);
}
.cm-theme-meta-item i {
    margin-right: 3px;
}
.cm-theme-author {
    font-size: 11px;
    color: var(--cm-text-muted);
}
.cm-theme-author i {
    margin-right: 3px;
}
.cm-theme-author a {
    color: var(--cm-link);
}
.cm-theme-actions {
    padding: 10px 15px;
    background: var(--cm-bg-actions);
    border-top: 1px solid var(--cm-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    margin-top: auto;
}
.cm-theme-actions-left {
    display: flex;
    gap: 8px;
}
.cm-theme-section-title {
    margin: 25px 0 15px 0;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--cm-border);
    color: var(--cm-text);
}
.cm-theme-section-title:first-child {
    margin-top: 0;
}
.cm-theme-section-info {
    margin-bottom: 15px;
    color: var(--cm-text-muted);
    font-size: 13px;
}
</style>';

// Ausgabe: Eigene Themes zuerst
if (count($projectThemes) > 0) {
    echo '<h2 class="cm-theme-section-title"><i class="rex-icon fa-star"></i> ' . rex_i18n::msg('consent_manager_themes_project_addon') . '</h2>';
    echo '<p class="cm-theme-section-info">' . rex_i18n::msg('consent_manager_themes_project_addon_info') . '</p>';
    echo '<div class="cm-theme-grid">';
    foreach ($projectThemes as $themeid => $theme_options) {
        echo $renderThemeCard($themeid, $theme_options, $configtheme, $csrfToken, true);
    }
    echo '</div>';
}

// Addon-Themes
if (count($addonThemes) > 0) {
    echo '<h2 class="cm-theme-section-title"><i class="rex-icon fa-th-large"></i> ' . rex_i18n::msg('consent_manager_themes_addon') . '</h2>';
    echo '<p class="cm-theme-section-info">' . rex_i18n::msg('consent_manager_themes_addon_info') . '</p>';
    echo '<div class="cm-theme-grid">';
    foreach ($addonThemes as $themeid => $theme_options) {
        echo $renderThemeCard($themeid, $theme_options, $configtheme, $csrfToken, false);
    }
    echo '</div>';
} elseif (0 === count($projectThemes)) {
    echo rex_view::error(rex_i18n::msg('consent_manager_error_no_themes', $addon->getPath('scss/')));
}
?>

<div class="cm_modal-overlay">
  <button class="btn btn-default cm_modal-button-close"><i class="fa fa-close"></i></button>
  <iframe src="about:blank" class="cm_modal-iframe"></iframe>
</div>

<script>
// Scale thumbnails to fit container width
function scaleThumbnails() {
    document.querySelectorAll('.cm-theme-preview').forEach(function(container) {
        var thumbnail = container.querySelector('.cm-theme-thumbnail');
        if (thumbnail) {
            var containerWidth = container.offsetWidth;
            var scale = containerWidth / 1440;
            thumbnail.style.transform = 'scale(' + scale + ')';
        }
    });
}

// Run on load and resize
window.addEventListener('load', scaleThumbnails);
window.addEventListener('resize', scaleThumbnails);
// Run immediately
setTimeout(scaleThumbnails, 100);

// Mark iframes as loaded
document.querySelectorAll('.cm-theme-iframe').forEach(function(iframe) {
    iframe.addEventListener('load', function() {
        this.classList.add('loaded');
        scaleThumbnails();
    });
});
</script>
