<?php

/**
 * Demo-Modul: Inline-Consent für YouTube Videos
 *
 * Eingabe-Teil des Moduls
 */

echo '
<fieldset>
    <legend>YouTube Video Inline-Consent Demo</legend>
    
    <div class="form-group">
        <label for="youtube_video_id">YouTube Video ID oder URL:</label>
        <input type="text" 
               class="form-control" 
               id="youtube_video_id" 
               name="REX_INPUT_VALUE[1]" 
               value="REX_VALUE[1]" 
               placeholder="z.B. dQw4w9WgXcQ oder https://www.youtube.com/watch?v=dQw4w9WgXcQ" />
        <small class="help-block">YouTube Video ID oder komplette YouTube-URL eingeben</small>
    </div>
    
    <div class="form-group">
        <label for="video_title">Video Titel:</label>
        <input type="text" 
               class="form-control" 
               id="video_title" 
               name="REX_INPUT_VALUE[2]" 
               value="REX_VALUE[2]" 
               placeholder="z.B. Rick Astley - Never Gonna Give You Up" />
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="video_width">Breite (px):</label>
                <input type="number" 
                       class="form-control" 
                       id="video_width" 
                       name="REX_INPUT_VALUE[3]" 
                       value="REX_VALUE[3]" 
                       placeholder="560" />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="video_height">Höhe (px):</label>
                <input type="number" 
                       class="form-control" 
                       id="video_height" 
                       name="REX_INPUT_VALUE[4]" 
                       value="REX_VALUE[4]" 
                       placeholder="315" />
            </div>
        </div>
    </div>
    
    <div class="alert alert-info">
        <h4><i class="fa fa-info-circle"></i> Inline-Consent Demo</h4>
        <p>
            Dieses Modul demonstriert die neue <strong>Inline-Consent</strong> Funktionalität. 
            Das Video wird erst geladen, wenn der Benutzer explizit zustimmt - kein globaler Consent-Banner nötig!
        </p>
        <ul>
            <li>✅ Zeigt YouTube-Thumbnail als Platzhalter</li>
            <li>✅ Consent-Dialog erscheint erst bei Klick</li>
            <li>✅ Vollständiges Logging wie beim normalen Consent Manager</li>
            <li>✅ Integration in Cookie-Details-System</li>
        </ul>
    </div>
</fieldset>
';
