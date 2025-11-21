<?php

/**
 * Demo-Modul: Inline-Consent für Google Maps
 *
 * Eingabe-Teil des Moduls
 */

echo '
<fieldset>
    <legend>Google Maps Inline-Consent Demo</legend>
    
    <div class="form-group">
        <label for="maps_embed_url">Google Maps Embed URL:</label>
        <textarea class="form-control" 
                  id="maps_embed_url" 
                  name="REX_INPUT_VALUE[1]" 
                  rows="3" 
                  placeholder="https://www.google.com/maps/embed?pb=!1m18!1m12...">REX_VALUE[1]</textarea>
        <small class="help-block">
            Google Maps → Teilen → Karte einbetten → HTML-Code kopieren → Nur die URL aus dem src-Attribut hier einfügen
        </small>
    </div>
    
    <div class="form-group">
        <label for="maps_title">Karten Titel:</label>
        <input type="text" 
               class="form-control" 
               id="maps_title" 
               name="REX_INPUT_VALUE[2]" 
               value="REX_VALUE[2]" 
               placeholder="z.B. Unsere Adresse" />
    </div>
    
    <div class="form-group">
        <label for="maps_height">Höhe (px):</label>
        <input type="number" 
               class="form-control" 
               id="maps_height" 
               name="REX_INPUT_VALUE[3]" 
               value="REX_VALUE[3]" 
               placeholder="450" />
    </div>
    
    <div class="alert alert-info">
        <h4><i class="fa fa-map-marker"></i> Google Maps Inline-Consent</h4>
        <p>
            Dieses Modul bindet Google Maps mit Inline-Consent ein. 
            Die Karte wird erst geladen, wenn der Benutzer explizit zustimmt.
        </p>
        <p><strong>So funktioniert es:</strong></p>
        <ol>
            <li>Gehen Sie zu <a href="https://maps.google.com" target="_blank">Google Maps</a></li>
            <li>Suchen Sie nach Ihrer Adresse</li>
            <li>Klicken Sie auf "Teilen" → "Karte einbetten"</li>
            <li>Kopieren Sie die URL aus dem src-Attribut des iframe-Codes</li>
            <li>Fügen Sie die URL oben ein</li>
        </ol>
    </div>
</fieldset>
';
