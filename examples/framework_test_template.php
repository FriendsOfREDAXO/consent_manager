<?php
/**
 * Test-Template für den Consent Manager
 * 
 * Erkennt automatisch das gewählte Framework und lädt die entsprechenden CDN-Assets.
 * Zeigt eine Mockup-Seite zum Testen der Consent-Box.
 * 
 * Anleitung:
 * 1. Erstellen Sie ein neues Template in REDAXO oder kopieren Sie diesen Code in ein bestehendes.
 * 2. Weisen Sie das Template einem Artikel zu.
 * 3. Rufen Sie den Artikel im Frontend auf.
 */

$addon = rex_addon::get('consent_manager');
$mode = rex_config::get('consent_manager', 'css_framework_mode', '');
$pageTitle = 'REDAXO - Consent Manager Framework Test';

// CDN Links definieren
$frameworkAssets = [
    'uikit3' => [
        'css' => ['https://cdn.jsdelivr.net/npm/uikit@3.17.11/dist/css/uikit.min.css'],
        'js' => ['https://cdn.jsdelivr.net/npm/uikit@3.17.11/dist/js/uikit.min.js', 'https://cdn.jsdelivr.net/npm/uikit@3.17.11/dist/js/uikit-icons.min.js']
    ],
    'bootstrap5' => [
        'css' => ['https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'],
        'js' => ['https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js']
    ],
    'tailwind' => [
        'css' => [],
        'js' => ['https://cdn.tailwindcss.com']
    ],
    'bulma' => [
        'css' => ['https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css'],
        'js' => []
    ]
];

$activeAssets = $frameworkAssets[$mode] ?? ['css' => [], 'js' => []];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> (<?= $mode ?: 'Theme-Modus' ?>)</title>
    
    <?php foreach ($activeAssets['css'] as $css): ?>
        <link rel="stylesheet" href="<?= $css ?>">
    <?php endforeach; ?>
    
    <?php if ($mode === 'tailwind'): ?>
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            primary: '#0066cc',
                        }
                    }
                }
            }
        </script>
    <?php endif; ?>

    <style>
        body { background-color: #f8f9fa; }
        .test-info-box {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #333;
            color: #fff;
            padding: 10px 15px;
            border-radius: 4px;
            z-index: 9999;
            font-family: sans-serif;
            font-size: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

<div class="test-info-box">
    <strong>Framework-Testmodus</strong><br>
    Modus: <?= $mode ?: 'Standard (Addon Themes)' ?><br>
    Addon: v<?= $addon->getVersion() ?>
</div>

<?php if ($mode === 'uikit3'): ?>
    <!-- UIkit Mockup -->
    <nav class="uk-navbar-container uk-margin" uk-navbar>
        <div class="uk-navbar-left">
            <a class="uk-navbar-item uk-logo" href="#">Consent-Test</a>
            <ul class="uk-navbar-nav">
                <li class="uk-active"><a href="#">Home</a></li>
                <li><a href="#">Features</a></li>
            </ul>
        </div>
    </nav>

    <div class="uk-section uk-section-primary uk-preserve-color">
        <div class="uk-container">
            <div class="uk-panel uk-light uk-margin-medium">
                <h1 class="uk-heading-medium">UIkit 3 Framework Integration</h1>
                <p class="uk-text-lead">Das ist ein Test-Layout für das UIkit 3 Fragment des Consent Managers.</p>
                <div class="uk-margin-medium-top">
                    <button class="uk-button uk-button-default uk-button-large uk-margin-small-right consent_manager-show-box">Cookie-Einstellungen öffnen</button>
                    <a class="uk-button uk-button-primary uk-button-large" href="https://github.com/FriendsOfREDAXO/consent_manager" target="_blank">Addon auf GitHub</a>
                </div>
            </div>
        </div>
    </div>

    <div class="uk-section uk-section-default">
        <div class="uk-container">
            <div class="uk-grid-match uk-child-width-1-3@m" uk-grid>
                <div>
                    <div class="uk-card uk-card-default uk-card-body uk-box-shadow-medium">
                        <h3 class="uk-card-title">Feature 1</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.</p>
                    </div>
                </div>
                <div>
                    <div class="uk-card uk-card-default uk-card-body uk-box-shadow-medium">
                        <h3 class="uk-card-title">Feature 2</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.</p>
                    </div>
                </div>
                <div>
                    <div class="uk-card uk-card-default uk-card-body uk-box-shadow-medium">
                        <h3 class="uk-card-title">Feature 3</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($mode === 'bootstrap5'): ?>
    <!-- Bootstrap Mockup -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container">
        <a class="navbar-brand" href="#">Consent-Test</a>
        <div class="collapse navbar-collapse">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Features</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="bg-primary text-white py-5 mb-5 shadow-sm">
      <div class="container py-5">
        <h1 class="display-4 fw-bold">Bootstrap 5 Integration</h1>
        <p class="lead">Das ist ein Test-Layout für das Bootstrap 5 Fragment des Consent Managers.</p>
        <div class="mt-4">
            <button class="btn btn-outline-light btn-lg me-2 consent_manager-show-box">Cookie-Einstellungen öffnen</button>
            <a class="btn btn-light btn-lg" href="https://github.com/FriendsOfREDAXO/consent_manager" target="_blank">Addon auf GitHub</a>
        </div>
      </div>
    </div>

    <div class="container">
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card h-100 shadow-sm border-0">
            <div class="card-body p-4">
              <h5 class="card-title fw-bold">Feature 1</h5>
              <p class="card-text text-muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm border-0">
            <div class="card-body p-4">
              <h5 class="card-title fw-bold">Feature 2</h5>
              <p class="card-text text-muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm border-0">
            <div class="card-body p-4">
              <h5 class="card-title fw-bold">Feature 3</h5>
              <p class="card-text text-muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

<?php elseif ($mode === 'tailwind'): ?>
    <!-- Tailwind Mockup -->
    <nav class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
          <div class="flex items-center space-x-8">
            <span class="text-xl font-bold text-gray-900 uppercase tracking-wider">Consent-Test</span>
            <div class="hidden sm:flex space-x-4">
              <a href="#" class="text-gray-900 border-b-2 border-blue-500 px-1 pt-1 text-sm font-medium">Home</a>
              <a href="#" class="text-gray-500 hover:text-gray-700 hover:border-gray-300 px-1 pt-1 text-sm font-medium">Features</a>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <div class="bg-blue-600 text-white py-20 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <h1 class="text-5xl font-extrabold tracking-tight mb-4">Tailwind CSS Integration</h1>
        <p class="text-xl max-w-2xl">Das ist ein Test-Layout für das Tailwind Fragment des Consent Managers.</p>
        <div class="mt-8 flex space-x-4">
            <button class="bg-transparent border-2 border-white hover:bg-white hover:text-blue-600 px-6 py-3 rounded-md text-lg font-semibold transition-colors consent_manager-show-box">Cookie-Einstellungen öffnen</button>
            <a class="bg-white text-blue-600 px-6 py-3 rounded-md text-lg font-semibold shadow-lg hover:bg-gray-100" href="https://github.com/FriendsOfREDAXO/consent_manager" target="_blank">Addon auf GitHub</a>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:px-8 lg:py-24">
      <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
        <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 p-8">
          <h3 class="text-lg font-bold text-gray-900 mb-2">Feature 1</h3>
          <p class="text-gray-600">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.</p>
        </div>
        <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 p-8">
          <h3 class="text-lg font-bold text-gray-900 mb-2">Feature 2</h3>
          <p class="text-gray-600">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.</p>
        </div>
        <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 p-8">
          <h3 class="text-lg font-bold text-gray-900 mb-2">Feature 3</h3>
          <p class="text-gray-600">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.</p>
        </div>
      </div>
    </div>

<?php elseif ($mode === 'bulma'): ?>
    <!-- Bulma Mockup -->
    <nav class="navbar is-dark" role="navigation" aria-label="main navigation">
      <div class="container">
        <div class="navbar-brand">
          <a class="navbar-item font-weight-bold" href="#">Consent-Test</a>
        </div>
        <div class="navbar-menu">
          <div class="navbar-start">
            <a class="navbar-item is-active">Home</a>
            <a class="navbar-item">Features</a>
          </div>
        </div>
      </div>
    </nav>

    <section class="hero is-link is-bold is-medium">
      <div class="hero-body">
        <div class="container">
          <h1 class="title is-1">Bulma CSS Integration</h1>
          <p class="subtitle is-3">Das ist ein Test-Layout für das Bulma Fragment des Consent Managers.</p>
          <div class="buttons mt-5">
              <button class="button is-light is-outlined is-large consent_manager-show-box">Cookie-Einstellungen öffnen</button>
              <a class="button is-white is-large" href="https://github.com/FriendsOfREDAXO/consent_manager" target="_blank">Addon auf GitHub</a>
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="columns is-multiline">
          <div class="column is-4">
            <div class="card shadow">
              <div class="card-content">
                <p class="title is-4">Feature 1</p>
                <p class="subtitle is-6">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.</p>
              </div>
            </div>
          </div>
          <div class="column is-4">
            <div class="card shadow">
              <div class="card-content">
                <p class="title is-4">Feature 2</p>
                <p class="subtitle is-6">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.</p>
              </div>
            </div>
          </div>
          <div class="column is-4">
            <div class="card shadow">
              <div class="card-content">
                <p class="title is-4">Feature 3</p>
                <p class="subtitle is-6">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

<?php else: ?>
    <!-- Theme-Modus / Standard Layout -->
    <div style="font-family: sans-serif; padding: 50px; text-align: center;">
        <h1>Consent Manager - Theme-Modus</h1>
        <p>Sie haben kein spezielles Framework ausgewählt. Die Box verwendet eines der Addon-Themes.</p>
        <button class="consent_manager-show-box" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Cookie-Einstellungen öffnen</button>
    </div>
<?php endif; ?>

<?php foreach ($activeAssets['js'] as $js): ?>
    <script src="<?= $js ?>"></script>
<?php endforeach; ?>

<?php
// REDAXO Consent Manager Fragment aufrufen
// Dies lädt die CSS/JS Logik des Addons und die Box selbst
$fragment = new rex_fragment();
echo $fragment->parse('ConsentManager/box_cssjs.php');
?>

</body>
</html>
