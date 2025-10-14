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
        
        // Debug: Zeige alle verfügbaren Services (temporär auch im Frontend)
        $sql = rex_sql::factory();
        try {
            $sql->setQuery('SELECT uid, service_name FROM '.rex::getTable('consent_manager_cookie').' WHERE clang_id = ?', [rex_clang::getCurrentId()]);
            echo '<div style="background:#d1ecf1;border:1px solid #bee5eb;padding:10px;margin:10px 0;"><strong>DEBUG - Verfügbare Services:</strong><br>';
            for ($i = 0; $i < $sql->getRows(); $i++) {
                echo '- ' . $sql->getValue('uid') . ' (' . $sql->getValue('service_name') . ')<br>';
                $sql->next();
            }
            echo '</div>';
        } catch (Exception $e) {
            echo '<div style="background:#f8d7da;border:1px solid #f5c6cb;padding:10px;margin:10px 0;"><strong>DEBUG Datenbankfehler:</strong> ' . $e->getMessage() . '</div>';
        }
        
        // Debug: Mehr Details
        echo '<div style="background:#e2e3e5;border:1px solid #d6d8db;padding:10px;margin:10px 0;"><strong>DEBUG Details:</strong><br>';
        echo 'Service Key: youtube<br>';
        echo 'Video ID: ' . htmlspecialchars($videoId) . '<br>';
        echo 'Debug Mode: ' . (rex::isDebugMode() ? 'ON' : 'OFF') . '<br>';
        echo 'Class exists: ' . (class_exists('consent_manager_inline') ? 'YES' : 'NO') . '<br>';
        echo '</div>';
        
        // Debug: doConsent Ergebnis anzeigen
        $result = consent_manager_inline::doConsent('youtube', $videoId, [
            'title' => $videoTitle,
            'width' => $videoWidth,
            'height' => $videoHeight,
            'thumbnail' => 'auto'
        ]);
        
        echo '<div style="background:#fff3cd;border:1px solid #ffeaa7;padding:10px;margin:10px 0;"><strong>DEBUG doConsent Output:</strong><br>';
        echo 'Length: ' . strlen($result) . ' chars<br>';
        echo 'Contains HTML: ' . (strpos($result, '<') !== false ? 'YES' : 'NO') . '<br>';
        echo '<pre style="background:#f8f9fa;padding:10px;white-space:pre-wrap;">' . htmlspecialchars(substr($result, 0, 1000)) . (strlen($result) > 1000 ? '...' : '') . '</pre>';
        echo '</div>';
        
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