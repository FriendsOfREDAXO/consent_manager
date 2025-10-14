<?php

/**
 * Demo-Modul: Inline-Consent für YouTube Videos
 * 
 * Ausgabe-Teil des Moduls
 */

// Werte aus dem Eingabeformular
$videoId = 'REX_VALUE[1]';
$videoTitle = 'REX_VALUE[2]' ?: 'YouTube Video';
$videoWidth = (int) 'REX_VALUE[3]' ?: 560;
$videoHeight = (int) 'REX_VALUE[4]' ?: 315;

// Nur ausgeben wenn Video-ID vorhanden
if (!empty($videoId)) {
    
    // CSS/JS für Inline-Consent einbinden (falls noch nicht geschehen)
    if (class_exists('consent_manager_inline')) {
        echo consent_manager_inline::getCSS();
        echo consent_manager_inline::getJavaScript();
        
        // Debug: Zeige alle verfügbaren Services
        if (rex::isBackend()) {
            $sql = rex_sql::factory();
            try {
                $sql->setQuery('SELECT uid, service_name FROM '.rex::getTable('consent_manager_cookie').' WHERE clang_id = ?', [rex_clang::getCurrentId()]);
                echo '<div class="alert alert-info"><strong>Verfügbare Services:</strong><br>';
                for ($i = 0; $i < $sql->getRows(); $i++) {
                    echo '- ' . $sql->getValue('uid') . ' (' . $sql->getValue('service_name') . ')<br>';
                    $sql->next();
                }
                echo '</div>';
            } catch (Exception $e) {
                echo '<div class="alert alert-danger"><strong>Debug:</strong> Datenbankfehler: ' . $e->getMessage() . '</div>';
            }
        }
        
        // Debug: doConsent Ergebnis anzeigen
        $result = consent_manager_inline::doConsent('youtube', $videoId, [
            'title' => $videoTitle,
            'width' => $videoWidth,
            'height' => $videoHeight,
            'thumbnail' => 'auto'
        ]);
        
        if (rex::isBackend()) {
            echo '<div class="alert alert-warning"><strong>Debug doConsent Output:</strong><br>';
            echo '<pre>' . htmlspecialchars(substr($result, 0, 500)) . (strlen($result) > 500 ? '...' : '') . '</pre>';
            echo '</div>';
        }
        
        echo $result;
    } else {
        echo '<div class="alert alert-danger">consent_manager_inline Klasse nicht gefunden!</div>';
    }
    
} else {
    // Backend-Preview falls keine Video-ID eingegeben
    if (rex::isBackend()) {
        echo '<div class="alert alert-warning">';
        echo '<i class="fa fa-youtube-play"></i> ';
        echo '<strong>YouTube Inline-Consent:</strong> Bitte Video-ID oder URL eingeben.';
        echo '</div>';
    }
}