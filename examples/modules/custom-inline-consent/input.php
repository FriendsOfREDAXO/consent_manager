<?php

/**
 * Demo-Modul: Inline-Consent für Custom iframes/Scripts
 * 
 * Eingabe-Teil des Moduls
 */

echo '
<fieldset>
    <legend>Custom Inline-Consent Demo</legend>
    
    <div class="form-group">
        <label for="service_key">Service-Schlüssel:</label>
        <input type="text" 
               class="form-control" 
               id="service_key" 
               name="REX_INPUT_VALUE[1]" 
               value="REX_VALUE[1]" 
               placeholder="z.B. custom-widget, calendly, typeform" />
        <small class="help-block">Muss einem Service im Consent Manager entsprechen</small>
    </div>
    
    <div class="form-group">
        <label for="embed_code">Embed-Code (iframe/script):</label>
        <textarea class="form-control" 
                  id="embed_code" 
                  name="REX_INPUT_VALUE[2]" 
                  rows="6" 
                  placeholder="<iframe src=&quot;https://example.com/widget&quot;></iframe>
oder
<script src=&quot;https://example.com/script.js&quot;></script>">REX_VALUE[2]</textarea>
        <small class="help-block">Kompletter HTML-Code des einzubindenden Elements</small>
    </div>
    
    <div class="form-group">
        <label for="content_title">Titel:</label>
        <input type="text" 
               class="form-control" 
               id="content_title" 
               name="REX_INPUT_VALUE[3]" 
               value="REX_VALUE[3]" 
               placeholder="z.B. Booking Widget, Kontaktformular" />
    </div>
    
    <div class="form-group">
        <label for="button_text">Button-Text:</label>
        <input type="text" 
               class="form-control" 
               id="button_text" 
               name="REX_INPUT_VALUE[4]" 
               value="REX_VALUE[4]" 
               placeholder="z.B. Widget laden, Formular anzeigen" />
    </div>
    
    <div class="form-group">
        <label for="privacy_notice">Datenschutz-Hinweis:</label>
        <textarea class="form-control" 
                  id="privacy_notice" 
                  name="REX_INPUT_VALUE[5]" 
                  rows="2" 
                  placeholder="Für dieses Widget werden Cookies des Anbieters gesetzt.">REX_VALUE[5]</textarea>
    </div>
    
    <div class="alert alert-info">
        <h4><i class="fa fa-code"></i> Custom Inline-Consent</h4>
        <p>
            Dieses Modul ermöglicht Inline-Consent für beliebige externe Inhalte:
        </p>
        <ul>
            <li><strong>Booking.com Widgets</strong> - Hotelbuchungen</li>
            <li><strong>Calendly</strong> - Terminbuchungen</li>
            <li><strong>Typeform</strong> - Interaktive Formulare</li>
            <li><strong>HubSpot</strong> - CRM Widgets</li>
            <li><strong>Custom APIs</strong> - Eigene Services</li>
        </ul>
        <p><small><strong>Wichtig:</strong> Der Service-Schlüssel muss im Consent Manager als Service konfiguriert sein!</small></p>
    </div>
</fieldset>
';