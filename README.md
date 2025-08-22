# Consent-Manager für REDAXO CMS

![logo](https://github.com/FriendsOfREDAXO/consent_manager/blob/assets/consent_manager-logo.jpg?raw=true)

## Was ist der Consent-Manager?

Das AddOn stellt eine DSGVO-konforme Lösung für die Einholung von Einverständniserklärungen zu Cookies und externen Diensten bereit. Website-Besucher erhalten eine Consent-Box, in der einzelne Dienste-Gruppen akzeptiert oder abgelehnt werden können. Technisch notwendige Dienste bleiben dabei immer aktiv.

**Kernfunktionen:**
- Datenschutz-Opt-In-Banner für Dienste und Cookies
- Flexible Gruppierung von Diensten
- Nachträgliche Änderung der Einstellungen möglich
- Vollständig anpassbare Texte und Designs
- Google Consent Mode v2 Integration
- Mehrsprachig und Multi-Domain-fähig

![Screenshot](https://github.com/FriendsOfREDAXO/consent_manager/blob/assets/consent_manager.png?raw=true)

## ⚠️ Rechtlicher Hinweis

**Wichtiger Haftungsausschluss:** Die mitgelieferten Texte und Cookie-Definitionen sind ausschließlich Beispiele und können unvollständig oder nicht aktuell sein. 

**Rechtliche Verantwortung:** Website-Betreiber und Entwickler sind eigenverantwortlich dafür zuständig, dass:
- Die Funktionalität der Abfrage den rechtlichen Anforderungen entspricht
- Alle Texte, Dienste und Cookie-Beschreibungen korrekt und vollständig sind
- Die Integration ordnungsgemäß erfolgt
- Die Lösung der geltenden Rechtslage und den Datenschutzbestimmungen entspricht

**Empfehlung:** Für die Formulierung der Texte und Cookie-Listen sollten Datenschutzbeauftragte oder die Rechtsabteilung konsultiert werden.

---

## 🚀 Schnellstart

### 1. Installation und Grundkonfiguration
```bash
# AddOn über REDAXO Installer herunterladen und installieren
```

### 2. Domain konfigurieren
Unter **Domains** die Website-Domain hinterlegen (ohne Protokoll):
```
beispiel.de
www.beispiel.de
```

### 3. Template-Integration
**Grundcode für Templates:**
```php
REX_CONSENT_MANAGER[forceCache=0 forceReload=0]
```

**PHP-Ausgabe:**
```php
<?php echo consent_manager_frontend::getFragment(false, false, 'consent_manager_box_cssjs.php'); ?>
```

### 4. Cookie-Einstellungen nachträglich aufrufen
**HTML-Link:**
```html
<a class="consent_manager-show-box">Datenschutz-Einstellungen</a>
```

**Mit automatischem Reload:**
```html
<a class="consent_manager-show-box-reload">Datenschutz-Einstellungen</a>
```

**JavaScript-Aufruf:**
```javascript
consent_manager_showBox();
```

### 5. Consent-Status abfragen
**JavaScript:**
```javascript
if (consent_manager_hasconsent('youtube')) {
    // YouTube wurde akzeptiert
}
```

**PHP:**
```php
<?php
if (consent_manager_util::has_consent('youtube')) {
    // YouTube wurde akzeptiert
}
?>
```

---

## 📋 Detaillierte Konfiguration

### Domain-Verwaltung

Jede Domain der REDAXO-Instanz muss einzeln konfiguriert werden:
- Domain ohne Protokoll hinterlegen (z.B. `www.beispiel.de`)
- Datenschutzerklärung und Impressum je Domain
- Automatischer Abgleich mit `$_SERVER['HTTP_HOST']`

**Google Consent Mode v2 Integration:**
- Pro Domain aktivierbar
- GDPR-konforme Standard-Einstellungen
- Automatische Script-Integration
- Debug-Konsole verfügbar

### Dienste konfigurieren

Jeder externe Dienst (Analytics, Social Media, etc.) wird einzeln angelegt:

**Schlüssel:** Interne Bezeichnung ohne Sonderzeichen
**Dienstname:** Wird in der Consent-Box angezeigt
**Cookie-Definitionen:** YAML-Format für Cookie-Beschreibungen

**Beispiel Cookie-Definition:**
```yaml
-
 name: _ga
 time: 2 Jahre
 desc: Speichert für jeden Besucher eine anonyme ID für die Zuordnung von Seitenaufrufen.
-
 name: _gat
 time: 1 Tag
 desc: Verhindert zu schnelle Datenübertragung an Analytics-Server.
```

**JavaScript-Integration:**
```html
<script>
// Wird geladen, wenn Nutzer zustimmt
gtag('config', 'GA_MEASUREMENT_ID');
</script>
```

### Gruppen-Management

Dienste werden in Gruppen zusammengefasst, die einzeln akzeptiert werden können:

| Einstellung | Beschreibung |
|-------------|--------------|
| **Schlüssel** | Interne Bezeichnung ohne Sonderzeichen |
| **Technisch notwendig** | Gruppe ist immer aktiv und nicht deaktivierbar |
| **Domain** | Zuordnung zur entsprechenden Domain |
| **Name** | Anzeigename für Website-Besucher |
| **Beschreibung** | Erklärung der Gruppe |
| **Dienste** | Zugewiesene Services |

---

## 🎨 Design und Anpassung

### Theme-System

Das AddOn bietet verschiedene vorgefertigte Themes:

![Screenshot](https://github.com/FriendsOfREDAXO/consent_manager/blob/assets/themes.png?raw=true)

**Eigenes Theme erstellen:**
1. Bestehendes Theme kopieren
2. In `/project/consent_manager_themes/` ablegen
3. Dateiname: `consent_manager_frontend_theme_*.scss`
4. Anpassungen vornehmen
5. In Theme-Vorschau auswählen

**Theme-Vorschau testen:**
```
/redaxo/index.php?page=consent_manager/theme&preview=project:consent_manager_frontend_mein_theme.scss
```

### Individuelles Design

**Fragment anpassen:**
- Standard: `/redaxo/src/addons/consent_manager/fragments/consent_manager_box.php`
- Eigenes Fragment: `/theme/private/fragments/consent_manager_box.php`

**CSS-Ausgabe steuern:**
- Standardmäßig wird `consent_manager_frontend.css` ausgegeben
- Über Einstellungen deaktivierbar für eigene CSS-Implementierung

---

## 🔧 Erweiterte Features

### Google Consent Mode v2

**Automatische Integration:**
- GDPR-konforme Default-Einstellungen
- `analytics_storage: denied`
- `ad_storage: denied` 
- `ad_user_data: denied`
- `ad_personalization: denied`

**Debug-Konsole aktivieren:**
```
?debug_consent=1
```

**Debug-Informationen:**
- Consent-Status (aktuell und Standard)
- Service-Übersicht
- Cookie-Analyse
- localStorage-Inhalte

### Beispiel-Modul für nachträgliche Abfrage

**Eingabe-Modul mit MForm:**
```php
<?php
$mform = new mform();
$cookies = [];
$qry = 'SELECT uid,service_name FROM '.rex::getTable('consent_manager_cookie').' WHERE clang_id = '.rex_clang::getCurrentId();
foreach (rex_sql::factory()->getArray($qry) as $v) {
    if ($v['uid'] == 'consent_manager') continue;
    $cookies[$v['uid']] = $v['service_name'];
}
$mform->addSelectField(1);
$mform->setOptions($cookies);
$mform->setLabel('Dienst');
echo $mform->show();
?>
```

### Cookie-Historie anzeigen

**Template-Integration:**
```php
REX_COOKIEDB[]
```

Zeigt alle gesetzten Cookies und die Einwilligungshistorie an.

---

## 🔍 Debugging und Problemlösung

### Debug-Modus aktivieren

**Methoden:**
- REDAXO Debug-Modus: `rex::isDebugMode()`
- URL-Parameter: `?debug_consent=1`
- Programmatisch über API

**Debug-Konsole zeigt:**
- Consent-Status und Default-Werte
- Service-Konfiguration
- Cookie-Details
- Browser-Storage-Inhalte
- Google Consent Mode Status

### Häufige Probleme

**Consent-Box wird nicht angezeigt:**
- Domain korrekt hinterlegt und in Cookie-Gruppe zugeordnet?
- Domain-Übereinstimmung prüfen (`www.` vs. ohne)
- Template-Platzhalter im `<head>`-Bereich?
- Eigenes CSS aktiviert aber nicht eingebunden?

**Cookies angezeigt, Scripts nicht geladen:**
- Scripts in entsprechendem Service hinterlegt?
- `<script>`-Tags vollständig vorhanden?
- Consent tatsächlich erteilt?

**Seite ohne Consent-Box:**
Token in Einstellungen definieren und URL-Parameter verwenden:
```
https://beispiel.de/seite.html?skip_consent=MEINTOKEN
```

### Berechtigung für Redakteure

Nur Text-Bearbeitung erlauben:
- Recht `consent_manager[]` zuweisen
- Zusätzlich `consent_manager[texteditonly]` zuweisen

---

## 🌍 Mehrsprachigkeit

**Automatische Übertragung:**
- Inhalte der Startsprache werden automatisch in neue Sprachen übertragen
- Nachträgliche Anpassung möglich

**Sprachspezifische Felder:**
- Texte und Beschreibungen individuell anpassbar
- Schlüssel, Scripts und Domains nur in Startsprache änderbar

---

## 📊 API und JavaScript-Events

### Event-Listener

```javascript
// Reagiert auf Consent-Änderungen
$(document).on('consent_manager-saved', function(e) {
    var consents = JSON.parse(e.originalEvent.detail);
    // Verarbeitung der Consent-Daten
});
```

### PHP-Utility-Funktionen

```php
// Consent-Status prüfen
consent_manager_util::has_consent('service_key');

// Frontend-Instanz erstellen
$consent_manager = new consent_manager_frontend();
$consent_manager->setDomain($_SERVER['HTTP_HOST']);
```

---

## 📝 Setup und Import

### Beispielkonfiguration importieren

Über **Setup** → **Beispielkonfiguration importieren**
⚠️ **Achtung:** Überschreibt vorhandene Dienste und Gruppen!

### Vorgefertigte Dienste

Das AddOn enthält Vorlagen für gängige Dienste und externe Inhalte:

**Analytics & Tracking:**
- Google Analytics (Universal & GA4)
- Google Tag Manager
- Matomo/Piwik
- Adobe Analytics
- Hotjar
- Microsoft Clarity

**Maps & Geolocation:**
- Google Maps
- OpenStreetMap
- Mapbox

**Video & Media:**
- YouTube
- Vimeo
- Twitch

**Social Media:**
- Facebook (Pixel, Like-Button, Comments)
- Instagram (Embeds)
- Twitter/X (Tweets, Timeline)
- LinkedIn (Insights)
- TikTok (Pixel)
- WhatsApp (Business Chat)

**Marketing & Advertising:**
- Google Ads
- Facebook Ads
- Microsoft Advertising (Bing)
- Amazon DSP

**Communication & Support:**
- reCAPTCHA
- Intercom
- Zendesk Chat
- Calendly

**E-Commerce & Payment:**
- PayPal
- Stripe
- Shopify
- WooCommerce Tracking

**Weitere Dienste:**
- AddThis (Social Sharing)
- Disqus (Comments)
- Typeform
- Mailchimp
- Campaign Monitor

⚠️ **Wichtiger Hinweis:** Die Beispielkonfigurationen sind Vorlagen und müssen an die individuellen Anforderungen und aktuellen Datenschutzbestimmungen angepasst werden.

---

## 🛠️ Erweiterte Integration

### Conditional Loading mit PHP

```php
<?php
$arr = json_decode($_COOKIE['consent_manager'], true);
$consents = $arr ? array_flip($arr['consents']) : [];

if (isset($consents['google-maps'])) {
    // Google Maps laden
    echo '<div id="google-maps"></div>';
} else {
    // Platzhalter mit Consent-Link anzeigen
    echo '<div>Für Google Maps müssen Cookies akzeptiert werden.</div>';
    echo '<a class="consent_manager-show-box-reload">Cookie-Einstellungen</a>';
}
?>
```

### AJAX-Integration

```javascript
// Prüfung vor AJAX-Request
function loadExternalContent() {
    if (consent_manager_hasconsent('external-api')) {
        $.ajax({
            url: '/api/external-data',
            success: function(data) {
                // Daten verarbeiten
            }
        });
    }
}
```

---

## 📄 Lizenz und Credits

### Lizenz
MIT Lizenz - siehe [LICENSE.md](https://github.com/FriendsOfREDAXO/consent_manager/blob/master/LICENSE.md)

### Entwicklung
**Friends Of REDAXO:** [https://github.com/FriendsOfREDAXO](https://github.com/FriendsOfREDAXO)

**Projekt-Leads:**
- [Ingo Winter](https://github.com/IngoWinter)
- [Andreas Eberhard](https://github.com/aeberhard)

### Credits
**Contributors:** [Siehe GitHub](https://github.com/FriendsOfREDAXO/consent_manager/graphs/contributors)

**Danksagungen:**
- [Thomas Blum](https://github.com/tbaddade/) (Code aus Sprog AddOn)
- [Thomas Skerbis](https://github.com/skerbis) (Testing und Spende)
- [Peter Bickel](https://github.com/polarpixel) (Entwicklungsspende)
- [Oliver Kreischer](https://github.com/olien) (Cookie-Design)

**Externe Bibliotheken:**
- [cookie.js](https://github.com/js-cookie/js-cookie) - MIT Lizenz

---

## 🆘 Support und Community

**Issue melden:** [GitHub Issues](https://github.com/FriendsOfREDAXO/consent_manager/issues)

**Contributions:** Pull Requests sind willkommen - besonders eigene Themes mit Screenshot oder Demo-Link!

**Community:** Tipps und Tricks können direkt in die README eingefügt oder als Issue eingereicht werden.