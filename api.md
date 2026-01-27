# Consent Manager - API Dokumentation

## JavaScript API

### doConsent()

Die globale Funktion `doConsent()` ermöglicht es, programmatisch Consent-Anfragen zu stellen.

```javascript
doConsent(serviceKey, callback);
```

**Parameter:**
- `serviceKey` (string): Der Service-Schlüssel (z.B. 'youtube', 'google-analytics')
- `callback` (function): Callback-Funktion, die nach der Consent-Entscheidung ausgeführt wird

**Rückgabewert:**
- `true`: User hat bereits zugestimmt
- `false`: Consent-Modal wird angezeigt, Callback wird nach Entscheidung aufgerufen

**Beispiel:**

```javascript
// YouTube Video laden, wenn Consent erteilt
if (doConsent('youtube', function() {
    console.log('User hat YouTube zugestimmt!');
    loadYouTubeVideo();
})) {
    // Consent bereits vorhanden
    loadYouTubeVideo();
}
```

### ConsentManagerCore

Das globale `ConsentManagerCore` Objekt bietet erweiterte Funktionen.

#### hasConsent()

Prüft, ob für einen Service bereits Consent vorliegt.

```javascript
ConsentManagerCore.hasConsent(serviceKey);
```

**Beispiel:**

```javascript
if (ConsentManagerCore.hasConsent('google-analytics')) {
    console.log('Google Analytics ist aktiv');
}
```

#### getAllConsents()

Gibt alle erteilten Consents zurück.

```javascript
var consents = ConsentManagerCore.getAllConsents();
console.log(consents); // z.B. ['consent_manager', 'youtube', 'google-analytics']
```

#### openSettings()

Öffnet das Consent-Modal zur Änderung der Einstellungen.

```javascript
ConsentManagerCore.openSettings();
```

#### revokeAllConsents()

Widerruft alle Consents und lädt die Seite neu.

```javascript
ConsentManagerCore.revokeAllConsents();
```

---

## PHP API

### InlineConsent

Die `InlineConsent` Klasse bietet Methoden zur automatischen Blockierung von externen Inhalten.

```php
use FriendsOfRedaxo\ConsentManager\InlineConsent;
```

#### scanAndReplaceConsentElements()

Scannt HTML-Code und ersetzt blockierbare Elemente durch Consent-Platzhalter.

```php
$html = '<iframe src="https://www.youtube.com/embed/VIDEO_ID"></iframe>';
$processedHtml = InlineConsent::scanAndReplaceConsentElements($html);
```

**Parameter:**
- `$content` (string): HTML-Code zum Scannen

**Rückgabewert:**
- (string): Verarbeiteter HTML-Code mit Consent-Platzhaltern

**Unterstützte Dienste:**
- YouTube (`youtube.com`, `youtu.be`)
- Vimeo (`vimeo.com`, `player.vimeo.com`)
- Google Maps (`google.com/maps`, `maps.google.com`)

#### renderYouTubePlaceholder()

Erzeugt einen Consent-Platzhalter für YouTube Videos.

```php
$placeholder = InlineConsent::renderYouTubePlaceholder(
    'VIDEO_ID',
    ['title' => 'Mein Video']
);
```

**Parameter:**
- `$videoId` (string): YouTube Video-ID
- `$options` (array): Optionale Konfiguration
  - `title`: Custom Titel
  - `privacy_notice`: Custom Text
  - `provider`: Anbieter-Name
  - `privacy_url`: Link zur Datenschutzerklärung

#### renderVimeoPlaceholder()

Erzeugt einen Consent-Platzhalter für Vimeo Videos.

```php
$placeholder = InlineConsent::renderVimeoPlaceholder(
    'VIDEO_ID',
    ['title' => 'Vimeo Video']
);
```

#### renderGoogleMapsPlaceholder()

Erzeugt einen Consent-Platzhalter für Google Maps.

```php
$placeholder = InlineConsent::renderGoogleMapsPlaceholder(
    'EMBED_URL',
    ['title' => 'Standortkarte']
);
```

#### renderGenericPlaceholder()

Erzeugt einen generischen Consent-Platzhalter.

```php
$placeholder = InlineConsent::renderGenericPlaceholder(
    'custom-service',
    '<div>Original Content</div>',
    [
        'title' => 'Externer Dienst',
        'privacy_notice' => 'Für die Anzeige wird Ihre Zustimmung benötigt.',
        'provider' => 'Anbieter GmbH',
        'privacy_url' => 'https://example.com/privacy'
    ]
);
```

---

## RESTful API

### Consent Statistics

**Endpoint:** `/index.php?rex-api-call=consent_stats`

**Methode:** GET

**Authentifizierung:** Backend-User muss eingeloggt sein

**Antwort:**

```json
{
    "success": true,
    "total": 1234,
    "top_services": [
        {"service": "youtube", "count": 450},
        {"service": "google-analytics", "count": 320}
    ],
    "history": [
        {"date": "2025-01-26", "count": 45},
        {"date": "2025-01-25", "count": 38}
    ]
}
```

**Beispiel:**

```javascript
fetch('?rex-api-call=consent_stats')
    .then(response => response.json())
    .then(data => {
        console.log('Total Consents:', data.total);
    });
```

---

## Extension Points

### CONSENT_MANAGER_BEFORE_OUTPUT

Wird aufgerufen, bevor der Consent-Manager im Frontend ausgegeben wird.

```php
rex_extension::register('CONSENT_MANAGER_BEFORE_OUTPUT', function($ep) {
    $config = $ep->getSubject();
    // Konfiguration anpassen
    return $config;
});
```

### CONSENT_MANAGER_INLINE_PLACEHOLDER

Ermöglicht die Anpassung von Inline-Platzhaltern.

```php
rex_extension::register('CONSENT_MANAGER_INLINE_PLACEHOLDER', function($ep) {
    $params = $ep->getParams();
    $serviceKey = $params['serviceKey'];
    $content = $params['content'];
    
    // Custom Platzhalter zurückgeben
    if ($serviceKey === 'custom-service') {
        return '<div>Custom Placeholder</div>';
    }
});
```

---

## Events

Der Consent Manager löst folgende Custom Events im Browser aus:

### consentmanager:ready

Wird ausgelöst, wenn der Consent Manager initialisiert ist.

```javascript
document.addEventListener('consentmanager:ready', function() {
    console.log('Consent Manager ready');
});
```

### consentmanager:consent-given

Wird ausgelöst, wenn ein Consent erteilt wurde.

```javascript
document.addEventListener('consentmanager:consent-given', function(event) {
    console.log('Consent erteilt für:', event.detail.service);
});
```

### consentmanager:consent-revoked

Wird ausgelöst, wenn ein Consent widerrufen wurde.

```javascript
document.addEventListener('consentmanager:consent-revoked', function(event) {
    console.log('Consent widerrufen für:', event.detail.service);
});
```

---

## Google Consent Mode v2

Der Consent Manager unterstützt automatisch Google Consent Mode v2.

**Konfiguration:**

In `Consent Manager → Einstellungen → Google Consent Mode` aktivieren.

**Verfügbare Consent-Typen:**
- `ad_storage`: Werbe-Cookies
- `analytics_storage`: Analytics-Cookies
- `ad_user_data`: Nutzerdaten für Werbung
- `ad_personalization`: Personalisierte Werbung
- `functionality_storage`: Funktionale Cookies
- `personalization_storage`: Personalisierungs-Cookies
- `security_storage`: Sicherheits-Cookies

**Beispiel - Service mit Google Consent Mode verknüpfen:**

```yaml
# In Service-Definition (Cookie → Definitions YAML)
gcm_purposes:
  - analytics_storage
  - ad_storage
```

---

## Migration & Import/Export

### Export

Services und Einstellungen können als JSON exportiert werden:

```php
$config = rex_config::get('consent_manager');
$json = json_encode($config, JSON_PRETTY_PRINT);
```

### Import

```php
$data = json_decode($json, true);
foreach ($data as $key => $value) {
    rex_config::set('consent_manager', $key, $value);
}
```

---

## Best Practices

### 1. Consent prüfen bevor externe Scripte geladen werden

```javascript
if (doConsent('google-analytics', function() {
    loadGoogleAnalytics();
})) {
    loadGoogleAnalytics();
}
```

### 2. Inline-Consent für Content-Elemente verwenden

```html
<iframe 
    data-consent-block="true"
    data-consent-service="youtube"
    data-consent-title="YouTube Video"
    src="https://www.youtube.com/embed/VIDEO_ID">
</iframe>
```

### 3. Services in Cookie-Gruppen organisieren

Gruppieren Sie verwandte Services (z.B. alle Google-Dienste) in Cookie-Gruppen für bessere UX.

### 4. Thumbnails für bessere Platzhalter

Verwenden Sie Video-Thumbnails für YouTube/Vimeo Platzhalter zur besseren User Experience.

---

## Framework-Integration (Entwickler)

In Version 5.3.0 wurde das System auf einen **Framework-First** Ansatz umgestellt. Dies ermöglicht es, die Consent-Box nativ in Ihrem Framework (Bootstrap, UIkit, Tailwind, etc.) zu rendern, ohne dass das Addon eigenes CSS mitbringen muss.

### Ein neues Framework hinzufügen

Um ein eigenes Framework zu unterstützen, sind folgende Schritte notwendig:

#### 1. Registrierung im Backend
Fügen Sie in der Datei `pages/config.php` den neuen Framework-Identifier (z.B. `my-framework`) zum Select-Feld `css_framework_mode` hinzu.

#### 2. Fragment erstellen
Erstellen Sie ein neues Fragment unter:
`fragments/ConsentManager/box_my-framework.php`

Dieses Fragment wird automatisch von `fragments/ConsentManager/box.php` geladen, wenn der Modus auf `my-framework` steht. Nutzen Sie hier die nativen Klassen Ihres Frameworks. Die Standardvariablen (Textinhalte, Services) stehen im Fragment zur Verfügung.

**Beispiel Struktur eines Sub-Fragments:**
```php
<?php
/** @var rex_fragment $this */
$is_modern = true; // Empfohlen für neue Integrationen
?>
<!-- HTML Struktur Ihres Frameworks -->
<div class="my-modal">
    <h3><?= $this->getVar('headline') ?></h3>
    <p><?= $this->getVar('description') ?></p>
    <!-- Buttons etc -->
</div>
```

#### 3. Setup-Wizard erweitern (Optional)
Damit User Ihr Framework direkt beim Onboarding wählen können, erweitern Sie:
- `fragments/ConsentManager/setup_wizard.php` (UI Karte hinzufügen)
- `lib/Api/consent_manager_setup_wizard.php` (Validierung anpassen)

#### 4. Sprachvariablen
Fügen Sie die entsprechenden Übersetzungen für den Namen des Frameworks in den `.lang` Dateien hinzu:
`consent_manager_config_css_framework_mode_my-framework = My Framework`

---

## Troubleshooting

### Problem: Consent wird nicht gespeichert

**Lösung:** Prüfen Sie, ob Cookies im Browser aktiviert sind und ob die Domain korrekt konfiguriert ist.

### Problem: Inline-Consent funktioniert nicht

**Lösung:** Stellen Sie sicher, dass `consent_inline.js` geladen wird und das `data-consent-block="true"` Attribut gesetzt ist.

### Problem: Service erscheint nicht in der Liste

**Lösung:** Service muss unter `Consent Manager → Services` angelegt und aktiviert sein.

---

## Support

Bei Fragen oder Problemen:
- **GitHub Issues:** https://github.com/FriendsOfREDAXO/consent_manager/issues
- **REDAXO Slack:** #addon-consent-manager
- **Forum:** https://redaxo.org/support/community/
