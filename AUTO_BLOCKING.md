# Auto-Blocking für manuell eingefügtes HTML

## Übersicht

Das Inline-Consent-System kann jetzt automatisch Scripts, iframes und Divs erkennen und blocken, die mit speziellen `data-consent-*` Attributen versehen sind.

**✅ Neu in Version 5.3.0:**
- Automatisches Scannen und Blocken von externem Code
- Einfache Aktivierung über die Einstellungen
- Code-Assistent zum Vorbereiten von Scripts/iframes
- Keine PHP-Kenntnisse erforderlich

## Aktivierung

### 1. Auto-Blocking aktivieren

**Backend:** `Consent Manager → Einstellungen`

✅ **Auto-Blocking für manuell eingefügtes HTML** aktivieren

### 2. Code-Assistent nutzen (empfohlen)

**Direkt in den Einstellungen:** Button **"Code-Assistent öffnen"** klicken

Der interaktive Assistent öffnet sich als Modal und hilft beim Vorbereiten von externem Code:
1. Original-Code einfügen
2. Service aus Dropdown auswählen (oder eigenen eingeben)
3. Optional: Anbieter, Datenschutz-URL, Titel, Platzhalter-Text
4. **Code generieren** klicken
5. Generierter Code kopieren und einfügen

**Hinweis:** Redakteure können nur aus bereits angelegten Services wählen. Admins können über "Eigenen Service-Schlüssel eingeben" auch neue Services verwenden (müssen diese aber später in den Einstellungen anlegen).

## Manuelle Verwendung

### 1. HTML mit data-consent-Attributen markieren

Fügen Sie folgende Attribute zu externen Scripts/iframes hinzu:

```html
<iframe src="https://www.youtube.com/embed/VIDEO_ID" 
        width="560" height="315"
        data-consent-block="true"
        data-consent-service="youtube"
        data-consent-provider="YouTube"
        data-consent-privacy="https://policies.google.com/privacy"
        data-consent-title="Video abspielen"></iframe>
```

### 2. Pflicht-Attribute

- **`data-consent-block="true"`** - Aktiviert das Blocking (Pflicht)
- **`data-consent-service="servicekey"`** - Service-Schlüssel aus dem Consent Manager (Pflicht)

### 3. Optionale Attribute

- **`data-consent-provider="Name"`** - Anbieter-Name (z.B. "YouTube")
- **`data-consent-privacy="URL"`** - Link zur Datenschutzerklärung
- **`data-consent-title="Titel"`** - Titel für den Placeholder
- **`data-consent-text="Text"`** - Individueller Platzhalter-Text (z.B. "Zur Buchung verwenden wir diesen Dienst. Bitte stimmen Sie zu.")

### 4. Automatisches Scannen aktivieren

~~Fügen Sie in Ihrem Template den Scanner hinzu:~~ **NICHT MEHR NÖTIG!**

Das Auto-Blocking wird automatisch aktiviert, sobald die Einstellung in `Consent Manager → Einstellungen` aktiviert ist. Kein Template-Code erforderlich!

~~```php
<?php
// Am Ende des Templates, vor </body>
if (class_exists('\FriendsOfRedaxo\ConsentManager\InlineConsent')) {
    // CSS/JS einbinden
    echo \FriendsOfRedaxo\ConsentManager\InlineConsent::getCSS();
    echo \FriendsOfRedaxo\ConsentManager\InlineConsent::getJavaScript();
}
?>
```~~

~~Und verwenden Sie den OUTPUT_FILTER zum automatischen Scannen:~~ **BEREITS INTEGRIERT!**

~~```php
<?php
// In boot.php des Project-Addons oder im Template
rex_extension::register('OUTPUT_FILTER', function(rex_extension_point $ep) {
    $content = $ep->getSubject();
    
    if (class_exists('\FriendsOfRedaxo\ConsentManager\InlineConsent')) {
        $content = \FriendsOfRedaxo\ConsentManager\InlineConsent::scanAndReplaceConsentElements($content);
    }
    
    return $content;
});
?>
```~~

✅ **Der OUTPUT_FILTER ist bereits in der boot.php integriert** - einfach in den Einstellungen aktivieren!

## Beispiele

### YouTube Video

```html
<iframe src="https://www.youtube.com/embed/VIDEO_ID" 
        width="560" height="315"
        data-consent-block="true"
        data-consent-service="youtube"
        data-consent-provider="YouTube"
        data-consent-privacy="https://policies.google.com/privacy"
        data-consent-title="Video abspielen"></iframe>
```

### Calendly Widget

```html
<div data-consent-block="true"
     data-consent-service="calendly"
     data-consent-provider="Calendly"
     data-consent-privacy="https://calendly.com/privacy">
    <script src="https://assets.calendly.com/assets/external/widget.js"></script>
</div>
```

### Custom iframe

```html
<iframe src="https://example.com/widget" 
        width="100%" 
        height="500"
        data-consent-block="true"
        data-consent-service="custom-widget"
        data-consent-provider="Example Inc."
        data-consent-privacy="https://example.com/privacy"
        data-consent-title="External Widget"></iframe>
```

## Service im Consent Manager anlegen

Vergessen Sie nicht, den Service (z.B. "youtube") im Consent Manager zu konfigurieren:

1. **Consent Manager → Cookies**
2. **Neuen Cookie anlegen**
3. **Service-Schlüssel:** `youtube` (muss mit `data-consent-service` übereinstimmen)
4. **Service-Name:** YouTube
5. **Cookie-Gruppe** zuweisen (z.B. "Marketing" oder "Externe Medien")

## Vorteile

✅ **Automatisch** - Kein manueller PHP-Code nötig  
✅ **Flexibel** - Funktioniert mit Scripts, iframes und Divs  
✅ **DSGVO-konform** - Inhalte werden erst nach Zustimmung geladen  
✅ **Benutzerfreundlich** - Klare Platzhalter mit Anbieter-Infos  
✅ **Einfach** - Nur Attribute im HTML hinzufügen

## Technische Details

- Der Scanner läuft über einen OUTPUT_FILTER
- Regex-Pattern erkennt Tags mit `data-consent-block="true"`
- Tags werden durch `InlineConsent::doConsent()` Platzhalter ersetzt
- Original-Tags werden im Platzhalter gespeichert
- Nach Consent-Erteilung werden die Original-Tags geladen
