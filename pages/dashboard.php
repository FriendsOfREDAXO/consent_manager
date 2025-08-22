<?php

$addon = rex_addon::get('consent_manager');

// Dashboard - Übersicht und Schnelleinstieg
$content = '';

// Willkommensbereich mit Anleitung
$content .= '
<div class="rex-addon-output">
    <div class="panel panel-primary">
        <header class="panel-heading">
            <div class="panel-title">
                <i class="rex-icon fa-tachometer"></i> Dashboard
            </div>
        </header>
        <div class="panel-body">
            <p class="lead">Diese Anleitung hilft beim Einrichten des Cookie-Consent-Systems:</p>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <h5><i class="rex-icon fa-download text-primary"></i> 1. Standard-Services importieren</h5>
                            <p class="text-muted small">Import vorgefertigter Services wie Google Analytics, Facebook Pixel, YouTube etc.</p>
                            <a href="'.rex_url::currentBackendPage(['page' => 'consent_manager/setup']).'" class="btn btn-sm btn-primary">
                                <i class="rex-icon fa-arrow-right"></i> Zu Import/Export
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <h5><i class="rex-icon fa-globe text-info"></i> 2. Domain konfigurieren</h5>
                            <p class="text-muted small">Festlegung der Domains, für welche der Consent Manager aktiv sein soll.</p>
                            <a href="'.rex_url::currentBackendPage(['page' => 'consent_manager/domain']).'" class="btn btn-sm btn-info">
                                <i class="rex-icon fa-arrow-right"></i> Zu Domains
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <h5><i class="rex-icon fa-cogs text-warning"></i> 3. Services anpassen</h5>
                            <p class="text-muted small">Bearbeitung der importierten Services und Hinzufügen eigener Services.</p>
                            <a href="'.rex_url::currentBackendPage(['page' => 'consent_manager/cookie']).'" class="btn btn-sm btn-warning">
                                <i class="rex-icon fa-arrow-right"></i> Zu Services/Cookies
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <h5><i class="rex-icon fa-edit text-success"></i> 4. Texte anpassen</h5>
                            <p class="text-muted small">Anpassung der Texte der Consent-Box nach den eigenen Bedürfnissen.</p>
                            <a href="'.rex_url::currentBackendPage(['page' => 'consent_manager/text']).'" class="btn btn-sm btn-success">
                                <i class="rex-icon fa-arrow-right"></i> Zu Texte
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <h5><i class="rex-icon fa-paint-brush text-primary"></i> 5. Theme auswählen</h5>
                            <p class="text-muted small">Auswahl eines Designs für die Consent-Box.</p>
                            <a href="'.rex_url::currentBackendPage(['page' => 'consent_manager/theme']).'" class="btn btn-sm btn-primary">
                                <i class="rex-icon fa-arrow-right"></i> Zu Themes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>';

// Aktueller Status / Konfigurationsüberblick
$sql = rex_sql::factory();

// Services/Cookies zählen
$sql->setQuery('SELECT COUNT(*) as count FROM '.rex::getTable('consent_manager_cookie'));
$cookie_count = $sql->getValue('count');

// Cookie-Gruppen zählen
$sql->setQuery('SELECT COUNT(*) as count FROM '.rex::getTable('consent_manager_cookiegroup'));
$group_count = $sql->getValue('count');

// Domains zählen
$sql->setQuery('SELECT COUNT(*) as count FROM '.rex::getTable('consent_manager_domain'));
$domain_count = $sql->getValue('count');

// Texte zählen
$sql->setQuery('SELECT COUNT(*) as count FROM '.rex::getTable('consent_manager_text'));
$text_count = $sql->getValue('count');

$content .= '
<div class="rex-addon-output">
    <div class="panel panel-info">
        <header class="panel-heading">
            <div class="panel-title">
                <i class="rex-icon fa-info-circle"></i> Aktuelle Konfiguration
            </div>
        </header>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-3">
                    <div class="rex-addon-content-stats">
                        <i class="rex-icon fa-cookie-bite rex-addon-content-stats-icon"></i>
                        <div class="rex-addon-content-stats-number">'.$cookie_count.'</div>
                        <div class="rex-addon-content-stats-text">Services/Cookies</div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="rex-addon-content-stats">
                        <i class="rex-icon fa-layer-group rex-addon-content-stats-icon"></i>
                        <div class="rex-addon-content-stats-number">'.$group_count.'</div>
                        <div class="rex-addon-content-stats-text">Cookie-Gruppen</div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="rex-addon-content-stats">
                        <i class="rex-icon fa-globe rex-addon-content-stats-icon"></i>
                        <div class="rex-addon-content-stats-number">'.$domain_count.'</div>
                        <div class="rex-addon-content-stats-text">Domains</div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="rex-addon-content-stats">
                        <i class="rex-icon fa-edit rex-addon-content-stats-icon"></i>
                        <div class="rex-addon-content-stats-number">'.$text_count.'</div>
                        <div class="rex-addon-content-stats-text">Texte</div>
                    </div>
                </div>
            </div>';

if ($cookie_count == 0) {
    $content .= '
            <div class="alert alert-info" style="margin-top: 15px;">
                <strong><i class="rex-icon fa-info-circle"></i> Erste Schritte:</strong><br>
                Es sind noch keine Services konfiguriert. Beginnen mit dem <a href="'.rex_url::currentBackendPage(['page' => 'consent_manager/setup']).'">Import der Standard-Konfiguration</a>.
            </div>';
}

$content .= '
        </div>
    </div>
</div>';

echo $content;
