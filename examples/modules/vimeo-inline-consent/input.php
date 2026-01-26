<?=/**
 * Demo-Modul: Inline-Consent für Vimeo Videos.
 *
 * Eingabe-Teil des Moduls
 */ '
<fieldset>
    <legend>Vimeo Video Inline-Consent Demo</legend>
    
    <div class="form-group">
        <label for="vimeo_video_id">Vimeo Video ID oder URL:</label>
        <input type="text" 
               class="form-control" 
               id="vimeo_video_id" 
               name="REX_INPUT_VALUE[1]" 
               value="REX_VALUE[1]" 
               placeholder="z.B. 123456789 oder https://vimeo.com/123456789" />
        <small class="help-block">Vimeo Video ID oder komplette Vimeo-URL eingeben</small>
    </div>
    
    <div class="form-group">
        <label for="vimeo_title">Video Titel:</label>
        <input type="text" 
               class="form-control" 
               id="vimeo_title" 
               name="REX_INPUT_VALUE[2]" 
               value="REX_VALUE[2]" 
               placeholder="z.B. Corporate Video 2024" />
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="vimeo_width">Breite (px):</label>
                <input type="number" 
                       class="form-control" 
                       id="vimeo_width" 
                       name="REX_INPUT_VALUE[3]" 
                       value="REX_VALUE[3]" 
                       placeholder="640" />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="vimeo_height">Höhe (px):</label>
                <input type="number" 
                       class="form-control" 
                       id="vimeo_height" 
                       name="REX_INPUT_VALUE[4]" 
                       value="REX_VALUE[4]" 
                       placeholder="360" />
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label for="vimeo_thumbnail">Custom Thumbnail URL (optional):</label>
        <input type="url" 
               class="form-control" 
               id="vimeo_thumbnail" 
               name="REX_INPUT_VALUE[5]" 
               value="REX_VALUE[5]" 
               placeholder="https://example.com/thumbnail.jpg" />
        <small class="help-block">Optional: Eigenes Vorschaubild verwenden</small>
    </div>
    
    <div class="alert alert-info">
        <h4><i class="fa fa-vimeo"></i> Vimeo Inline-Consent</h4>
        <p>
            Dieses Modul demonstriert Inline-Consent für Vimeo-Videos. 
            Professionelle Alternative zu YouTube mit erweiterten Datenschutz-Features.
        </p>
        <ul>
            <li>✅ Unterstützt Vimeo Player API</li>
            <li>✅ Custom Thumbnails möglich</li>
            <li>✅ Responsive Design</li>
            <li>✅ DSGVO-konforme Integration</li>
        </ul>
    </div>
</fieldset>
';
