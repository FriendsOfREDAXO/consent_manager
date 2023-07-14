<?php

$addon = rex_addon::get('consent_manager');

$preview = rex_request('preview', 'string', '');
$clang_id = rex_clang::getStartId();

// Theme-Preview

if ('' !== $preview) {
    rex_response::cleanOutputBuffers();

    $backgrounds = (array) glob($addon->getAssetsPath('*.jpg'));
    $backgroundimage = basename((string) $backgrounds[array_rand($backgrounds)]);

    $cmtheme = new consent_manager_theme($preview);
    $theme_options = $cmtheme->getThemeInformation();
    if (count($theme_options) > 0) {
        $cmstyle = $cmtheme->getCompiledStyle();
        $cmbox = consent_manager_frontend::getFragment(0, 0, 'consent_manager_box.php');
    } else {
        $cmstyle = '';
        $cmbox = rex_view::error($addon->i18n('error_css_notfound', $preview));
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
    background-image: url(../assets/addons/consent_manager/<?= $backgroundimage ?>);
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
<p><span><?= $addon->i18n('theme_name') ?></span><?= $theme_options['name'] ?></p>
<p><span><?= $addon->i18n('theme_description') ?></span><?= $theme_options['description'] ?></p>
<p><span><?= $addon->i18n('theme_type') ?></span><?= $theme_options['type'] ?></p>
<p><span><?= $addon->i18n('theme_style') ?></span><?= $theme_options['style'] ?></p>
<p><span><?= $addon->i18n('theme_scssfile') ?></span><?= $preview ?></p>
<p><span><?= $addon->i18n('theme_autor') ?></span><?= $theme_options['autor'] ?></p>
</div>

<?php } ?>

<?php
    echo '<style>' . $cmstyle . '</style>' . PHP_EOL;
    echo $cmbox;
    if ('' !== $cmstyle) {
        echo rex_view::info($addon->i18n('theme_preview_info'));
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
    echo rex_view::error($addon->i18n('config_owncss_active'));
}

// check Konfiguration
if (false === consent_manager_util::consentConfigured()) {
    echo rex_view::warning($addon->i18n('consent_manager_cookiegroup_nodomain_notice'));
}

$content = '';
$buttons = '';

// csrf-Schutz

$csrfToken = rex_csrf_token::factory('consent_manager');

$theme = '';
if ('1' === rex_post('formsubmit', 'string')) {
    $theme = rex_post('theme', 'string', 'consent_manager_frontend.scss');
}

// Formular abgesendet - Einstellungen speichern

if ('1' === rex_post('formsubmit', 'string') && !$csrfToken->isValid()) {
    echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
} elseif ('1' === rex_post('formsubmit', 'string')) {
    $cmtheme = new consent_manager_theme();
    $theme_options = $cmtheme->getThemeInformation($theme);
    if (0 === count($theme_options)) {
        echo rex_view::error($addon->i18n('config_invalid_theme'));
    } else {
        $addon->setConfig('theme', $theme);
        echo rex_view::success($addon->i18n('config_saved'));
        consent_manager_theme::generateThemeAssets($theme);
        consent_manager_theme::copyAllAssets();
    }
}

// dump($_POST);
// dump($addon->getConfig('theme', false));
// Ausgabe der Themes

$themes = (array) glob($addon->getPath('scss/consent_manager_frontend*.scss'));
natsort($themes);
if (count($themes) > 0) {
    echo '<h2>' . $addon->i18n('themes_addon') . '</h2>';
    echo '<p>' . $addon->i18n('themes_addon_info') . '</p>';
    $cmtheme = new consent_manager_theme();
    foreach ($themes as $themefile) {
        $output = '';
        $content = '';
        $class = '';
        $titleactive = '';

        $themeid = basename((string) $themefile);
        $theme_options = $cmtheme->getThemeInformation($themeid);

        if (count($theme_options) > 0) {
            $authorlinks = [];
            $authors = explode(',', $theme_options['autor']);
            foreach ($authors as $author) {
                $url = 'https://github.com/' . str_replace('@', '', trim($author));
                $authorlinks[] = '<a href="' . $url . '" target="_blank">' . $author . '</a>';
            }

            $formElements = [];

            $n = [];
            $n['field'] = '';

            $n['field'] .= '<p class="theme_description">';
            $n['field'] .= '<span>' . $addon->i18n('theme_name') . '</span> ' . $theme_options['name'] . '<br>';
            $n['field'] .= '<span>' . $addon->i18n('theme_description') . '</span> ' . $theme_options['description'] . '<br>';
            $n['field'] .= '<span>' . $addon->i18n('theme_type') . '</span> ' . $theme_options['type'] . '<br>';
            $n['field'] .= '<span>' . $addon->i18n('theme_style') . '</span> ' . $theme_options['style'] . '<br>';
            $n['field'] .= '<span>' . $addon->i18n('theme_scssfile') . '</span> ' . $themeid . '<br>';
            $n['field'] .= '<span>' . $addon->i18n('theme_autor') . '</span> ' . implode(', ', $authorlinks) . '<br>';
            $n['field'] .= '</p>';

            $n['field'] .= '<div class="thumbnail-container" title="' . $theme_options['name'] . '" data-theme="' . $themeid . '">';
            $n['field'] .= '  <div class="thumbnail">';
            $n['field'] .= '   <iframe loading="lazy" width="1440px" height="900px" class="thumbnailframe" src="?page=consent_manager/theme&preview=' . $themeid . '&nofocus" data-theme="' . $themeid . '" onload="this.style.opacity = 1"></iframe>';
            $n['field'] .= '  </div>';
            $n['field'] .= '</div>';

            $formElements[] = $n;

            $fragment = new rex_fragment();
            $fragment->setVar('elements', $formElements, false);
            $content = $fragment->parse('core/form/form.php');

            // Buttons
            $confirmmsg = $addon->i18n('config_confirm_select', $theme_options['name']);
            $formElements = [];
            $n = [];
            $n['field'] = '<a href="?page=consent_manager/theme&preview=' . $themeid . '" class="btn rex-form-aligned btn-info consent_manager-button-preview" data-theme="' . $themeid . '">' . $addon->i18n('config_btn_preview') . '</a> ';
            $n['field'] .= '<button class="btn btn-save" type="submit" name="save" data-confirm="' . $confirmmsg . '" value="' . $addon->i18n('config_btn_select', $theme_options['name']) . '">' . $addon->i18n('config_btn_select', $theme_options['name']) . '</button>';
            $formElements[] = $n;
            $fragment = new rex_fragment();
            $fragment->setVar('elements', $formElements, false);
            $buttons = $fragment->parse('core/form/submit.php');
            $buttons = '<fieldset class="rex-form-action">' . $buttons . '</fieldset>';

            // dump($themeid, $addon->getConfig('theme', ''));
            $configtheme = $addon->getConfig('theme', 'consent_manager_frontend.scss');
            if ('' === $configtheme || false === $configtheme) {
                $configtheme = 'consent_manager_frontend.scss';
            }
            if ($themeid === $configtheme) {
                $class = 'edit';
                $titleactive = ' - ' . $addon->i18n('config_active_theme');
            }

            // Ausgabe des Formulars mit csrf-Schutz
            $fragment = new rex_fragment();
            $fragment->setVar('class', $class);
            $fragment->setVar('title', $theme_options['name'] . $titleactive);
            $fragment->setVar('body', $content, false);
            $fragment->setVar('buttons', $buttons, false);
            $output = $fragment->parse('core/page/section.php');

            $output = '
<form action="' . rex_url::currentBackendPage() . '#' . $themeid . '" method="post" id="' . $themeid . '">
<input type="hidden" name="formsubmit" value="1" />
<input type="hidden" name="theme" value="' . $themeid . '" />
    ' . $csrfToken->getHiddenField() . '
    ' . $output . '
</form>
';

            echo $output;
        }
    }
} else {
    echo rex_view::error($addon->i18n('consent_manager_error_no_themes', $addon->getPath('scss/')));
}

// Themes im project-Addon
if (true === rex_addon::exists('project')) {
    $themes = (array) glob(rex_addon::get('project')->getPath('consent_manager_themes/consent_manager_frontend*.scss'));
    natsort($themes);
    if (count($themes) > 0) {
        echo '<h2>' . $addon->i18n('themes_project_addon') . '</h2>';
        echo '<p>' . $addon->i18n('themes_project_addon_info') . '</p>';
        $cmtheme = new consent_manager_theme();
        foreach ($themes as $themefile) {
            $output = '';
            $content = '';
            $class = '';
            $titleactive = '';

            $themeid = 'project:' . basename((string) $themefile);
            $theme_options = $cmtheme->getThemeInformation($themeid);
            if (count($theme_options) > 0) {
                $authorlinks = [];
                $authors = explode(',', $theme_options['autor']);
                foreach ($authors as $author) {
                    $url = 'https://github.com/' . str_replace('@', '', trim($author));
                    $authorlinks[] = '<a href="' . $url . '" target="_blank">' . $author . '</a>';
                }

                $formElements = [];

                $n = [];
                $n['field'] = '';

                $n['field'] .= '<p class="theme_description">';
                $n['field'] .= '<span>' . $addon->i18n('theme_name') . '</span> ' . $theme_options['name'] . '<br>';
                $n['field'] .= '<span>' . $addon->i18n('theme_description') . '</span> ' . $theme_options['description'] . '<br>';
                $n['field'] .= '<span>' . $addon->i18n('theme_type') . '</span> ' . $theme_options['type'] . '<br>';
                $n['field'] .= '<span>' . $addon->i18n('theme_style') . '</span> ' . $theme_options['style'] . '<br>';
                $n['field'] .= '<span>' . $addon->i18n('theme_scssfile') . '</span> ' . $themeid . '<br>';
                $n['field'] .= '<span>' . $addon->i18n('theme_autor') . '</span> ' . implode(', ', $authorlinks) . '<br>';
                $n['field'] .= '</p>';

                $n['field'] .= '<div class="thumbnail-container" title="' . $theme_options['name'] . '" data-theme="' . $themeid . '">';
                $n['field'] .= '  <div class="thumbnail">';
                $n['field'] .= '   <iframe loading="lazy" width="1440px" height="900px" class="thumbnailframe" src="?page=consent_manager/theme&preview=' . $themeid . '&nofocus" data-theme="' . $themeid . '" onload="this.style.opacity = 1"></iframe>';
                $n['field'] .= '  </div>';
                $n['field'] .= '</div>';

                $formElements[] = $n;

                $fragment = new rex_fragment();
                $fragment->setVar('elements', $formElements, false);
                $content = $fragment->parse('core/form/form.php');

                // Buttons
                $confirmmsg = $addon->i18n('config_confirm_select', $theme_options['name']);
                $formElements = [];
                $n = [];
                $n['field'] = '<a href="?page=consent_manager/theme&preview=' . $themeid . '" class="btn rex-form-aligned btn-info consent_manager-button-preview" data-theme="' . $themeid . '">' . $addon->i18n('config_btn_preview') . '</a> ';
                $n['field'] .= '<button class="btn btn-save" type="submit" name="save" data-confirm="' . $confirmmsg . '" value="' . $addon->i18n('config_btn_select', $theme_options['name']) . '">' . $addon->i18n('config_btn_select', $theme_options['name']) . '</button>';
                $formElements[] = $n;
                $fragment = new rex_fragment();
                $fragment->setVar('elements', $formElements, false);
                $buttons = $fragment->parse('core/form/submit.php');
                $buttons = '<fieldset class="rex-form-action">' . $buttons . '</fieldset>';

                // dump($themeid, $addon->getConfig('theme', ''));
                $configtheme = $addon->getConfig('theme', 'consent_manager_frontend.scss');
                if ('' === $configtheme) {
                    $configtheme = 'consent_manager_frontend.scss';
                }
                if ($themeid === $configtheme) {
                    $class = 'edit';
                    $titleactive = ' - ' . $addon->i18n('config_active_theme');
                }

                // Ausgabe des Formulars mit csrf-Schutz
                $fragment = new rex_fragment();
                $fragment->setVar('class', $class);
                $fragment->setVar('title', $theme_options['name'] . $titleactive);
                $fragment->setVar('body', $content, false);
                $fragment->setVar('buttons', $buttons, false);
                $output = $fragment->parse('core/page/section.php');

                $output = '
    <form action="' . rex_url::currentBackendPage() . '#' . str_replace(':', '-', $themeid) . '" method="post" id="' . str_replace(':', '-', $themeid) . '">
    <input type="hidden" name="formsubmit" value="1" />
    <input type="hidden" name="theme" value="' . $themeid . '" />
        ' . $csrfToken->getHiddenField() . '
        ' . $output . '
    </form>
    ';

                echo $output;
            }
        }
    }
}
?>

<div class="cm_modal-overlay">
  <button class="btn btn-default cm_modal-button-close"><i class="fa fa-close"></i></button>
  <iframe src="about:blank" class="cm_modal-iframe"></iframe>
</div>
