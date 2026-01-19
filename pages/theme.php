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

    // Load background images from preview_images directory (not publicly accessible)
    $backgrounds = (array) glob($addon->getPath('preview_images/*.jpg'));
    $backgroundImageData = '';
    if ([] !== $backgrounds) {
        $randomImage = (string) $backgrounds[array_rand($backgrounds)];
        // Convert to base64 data URI to embed in CSS (keeps images private)
        $imageData = base64_encode((string) file_get_contents($randomImage));
        $backgroundImageData = 'data:image/jpeg;base64,' . $imageData;
    }

    $cmtheme = new Theme($preview);
    $theme_options = $cmtheme->getThemeInformation();
    if (count($theme_options) > 0) {
        $cmstyle = $cmtheme->getCompiledStyle();
        $cmbox = Frontend::getFragment(0, 0, 'ConsentManager/box.php');
    } else {
        $cmstyle = '';
        $cmbox = rex_view::error(rex_i18n::msg('consent_manager_error_css_notfound', $preview));
    }
    ?><!doctype html>
<html lang="de">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
::-moz-selection {
  color: inherit;
  background: transparent;
}

::selection {
  color: inherit;
  background: transparent;
}
html {
    min-height: 100%;
    <?php if ('' !== $backgroundImageData): ?>
    background-image: url(<?= $backgroundImageData ?>);
    <?php endif; ?>
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center center;
}
body {
    font-family: Verdana, Geneva, sans-serif;
    font-size: 14px;
    line-height: 1.5em;
    padding: 30px;
    margin: 0;
    min-height: 100%;
    height: 100%;
}
h1 {
    background-color: rgba(255,255,255,.6);
    padding: 15px 30px;
    display: inline-block;
    margin: 0 0 30px 0;
}
div.theme_description p {
    background-color: rgba(255,255,255,.6);
    padding: 5px 10px 5px 0;
    margin-bottom: 10px;
}
div.theme_description span {
    background-color: rgba(255,255,255,.3);
    display:inline-block;
    width: 150px;
    margin-right: 15px;
}
.alert-danger{color:#fff;background-color:#d9534f;border-color:#c9302c;padding: 20px 30px;margin-top:2em;}
.alert-info{color:#fff;background-color:#4b9ad9;border-color:#2a81c7;padding: 20px 30px;margin-top:2em;}
</style>
</head>
<body>

<h1 id="previewtitle"> <?= $theme_options['name'] ?? $preview ?></h1>

<?php if (false !== $cmstyle && [] !== $theme_options) { ?>
<div class="theme_description">
<p><span><?= rex_i18n::msg('consent_manager_theme_name') ?></span><?= $theme_options['name'] ?></p>
<p><span><?= rex_i18n::msg('consent_manager_theme_description') ?></span><?= $theme_options['description'] ?></p>
<p><span><?= rex_i18n::msg('consent_manager_theme_type') ?></span><?= $theme_options['type'] ?></p>
<p><span><?= rex_i18n::msg('consent_manager_theme_style') ?></span><?= $theme_options['style'] ?></p>
<p><span><?= rex_i18n::msg('consent_manager_theme_scssfile') ?></span><?= $preview ?></p>
<p><span><?= rex_i18n::msg('consent_manager_theme_autor') ?></span><?= $theme_options['autor'] ?></p>
</div>

<?php } ?>

<?php
        echo '<style>' . $cmstyle . '</style>' . PHP_EOL;
    echo $cmbox;
    if ('' !== $cmstyle) {
        echo rex_view::info(rex_i18n::msg('consent_manager_theme_preview_info'));
    }
    ?>

<script>
window.onload = function(event) {
    if (document.getElementById('consent_manager-background')) {
        consent_managerBox = document.getElementById('consent_manager-background');
        consent_managerBox.classList.remove('consent_manager-hidden');

        if (window.location.href.indexOf('nofocus') == -1) {
            var focusableEls = consent_managerBox.querySelectorAll('input[type="checkbox"]');//:not([disabled])
            var firstFocusableEl = focusableEls[0];
            consent_managerBox.focus();
            if (firstFocusableEl) {
                firstFocusableEl.focus();
            }
        }

        consent_managerBox.querySelectorAll('.consent_manager-sitelinks').forEach(function (el) {
            el.querySelectorAll('a').forEach(function (link) {
                link.removeAttribute("href");
            });
        });
        if (document.getElementById('consent_manager-toggle-details')) {
            document.getElementById('consent_manager-toggle-details').addEventListener('click', function () {
                document.getElementById('consent_manager-detail').classList.toggle('consent_manager-hidden');
            });
            document.getElementById('consent_manager-toggle-details').addEventListener('keydown', function (event) {
                if (event.key == 'Enter') {
                    document.getElementById('consent_manager-detail').classList.toggle('consent_manager-hidden');
                }
            });
        }
        consent_managerBox.querySelectorAll('.consent_manager-close').forEach(function (el) {
            el.addEventListener('click', function () {
                //document.getElementById('consent_manager-background').classList.add('consent_manager-hidden');
                consent_managerBox.classList.add('consent_manager-hidden');

                if (!document.getElementById('consent_manager-detail').classList.contains('consent_manager-hidden')) {
                    document.getElementById('consent_manager-detail').classList.toggle('consent_manager-hidden');
                }
            });
        });
        document.getElementById('previewtitle').onclick = function() {
            consent_managerBox.classList.remove('consent_manager-hidden');
        };
        document.onkeydown = function(evt) {
            evt = evt || window.event;
            if (evt.keyCode == 27) {
                parent.consent_manager_close_preview();
            }
            if (evt.keyCode == 13) {
                consent_managerBox.classList.remove('consent_manager-hidden');
            }
        };
        // for all dom elements on click
        document.onclick = function(evt) {
            if (evt.target.closest('.consent_manager-wrapper-inner')) {
                return;
            }
            document.getElementById('consent_manager-background').classList.remove('consent_manager-hidden');
        }
    }
}
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
        Theme::generateThemeAssets($theme);
        Theme::copyAllAssets();
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
