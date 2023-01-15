# Consent-Manager für [REDAXO CMS](https://redaxo.org)

![logo](https://github.com/FriendsOfREDAXO/consent_manager/blob/assets/consent_manager-logo.jpg?raw=true)

Stellt Datenschutz-Opt-In-Banner für Dienste und ggf. deren zugehörige Cookies zur Verfügung. Die Dienste können in selbst definierte Gruppen zusammengefasst werden. Der Website Besucher bekommt eine Consent-Box angezeigt in der er allen oder einzelnen Dienste-Gruppen zustimmen kann. Es existiert eine Gruppe **Notwendig**, die nicht deaktiviert werden kann. Die Consent-Box kann erneut (zum Beispiel über einen Link im Impressum, oder Footer)aufgerufen werden. So können nachträglich Änderungen durchgeführt. Alle Texte sowie die Gestaltung der Consent-Box sind frei konfigurierbar. Eine Themeauswahl bietet unterschiedliche Designs für den Start.

![Screenshot](https://github.com/FriendsOfREDAXO/consent_manager/blob/assets/consent_manager.png?raw=true)

## Kurzanleitung

1. AddOn `consent_manager` über den Installer herunterladen und installieren.
2. [Domains hinterlegen](#domains-hinzufuegen)
3. [Dienste anlegen](#cookies-anlegen)
4. [Dienste-Gruppen anlegen](#cookie-gruppen-anlegen)
5. Der jeweiligen Domain-Gruppe die gewünschten Domains und Cookies zuordnen und JS Scripte hinterlegen.
6. `REX_CONSENT_MANAGER[forceCache=0 forceReload=0]` in den `<head>`-Bereich der gewünschten [Templates einfügen](#in-template-einfuegen), bzw.<br>`echo consent_manager_frontend::getFragment(false, false, 'consent_manager_box_cssjs.php');`,<br> wenn via PHP.
7. Alle weiteren Einstellungen sind optional.

> **Hinweis:** Wird keine Auswahbox angezeigt Punkte 2 bis 6 nochmal checken ... und/oder siehe [Fehlerbehebung](#fehlerbehebung)

## Einrichten

### Domains hinzufügen

Consent-Manager kann für mehrere Domains einzeln gesteuert werden. Jede Domain der REDAXO-Instanz die Consent-Manager nutzen soll muss einzeln hinterlegt werden. Zum Beispiel `www.meinedomain.de (ohne Protokoll http/https)`. Das gilt auch für Subdomains **(auch www)**.
Die Datenschutzerklärung und das Impressum wird für jede Domain hinterlegt. Die Seiten werden nachher automatisch in der Consent-Box verlinkt.
Beim Aufruf wird die hier hinterlegte Domain mit `$_SERVER['HTTP_HOST']` verglichen und die Consent-Box wird bei Übereinstimmung angezeigt.

### Dienste anlegen

Für jeden Dienst (zum Beispiel Google Analytics oder Matamo) wird ein einzelner Eintrag erstellt. Hat ein Dienst mehrere Cookies werden diese trotzdem in einem einzigen Eintrag beschrieben. **Alle Angaben dienen nur zur Information des Webseiten Besuchers und haben keinen Einfluss auf das Setzen/Löschen der Cookies bzw. deren Eigenschaften!. Im Reiter **Setup** besteht die Möglichkeit einen Import gängiger Dienste durchzuführen.

![Screenshot](https://github.com/FriendsOfREDAXO/consent_manager/blob/assets/consent_manager-cookies.png?raw=true)

**Schlüssel:** ist zur internen Verwendung und darf keine Sonderzeichen/Leerzeichen enthalten.

**Dienstname:** wird später in der Consent-Box angezeigt.

**Cookie Definitionen:** enthält die Beschreibung aller Cookies des Dienstes die in der Consent-Box angezeigt werden sollen. Die Beschreibung wird im *YAML-Format* hinterlegt, zum Beispiel:

```yaml
-
 name: _ga
 time: 2 Jahre
 desc: Speichert für jeden Besucher der Website eine anonyme ID. Anhand der ID können Seitenaufrufe einem Besucher zugeordnet werden.
-
 name: _gat
 time: 1 Tag
 desc: Verhindert, dass in zu schneller Folge Daten an den Analytics Server übertragen werden.
-
 name: _gid
 time: 1 Tag
 desc: Speichert für jeden Besucher der Website eine anonyme ID. Anhand der ID können Seitenaufrufe einem Besucher zugeordnet werden.
```

**Anbieter:** Hier kann optional der Anbieter hinterlegt werden (zum Beispiel Google). Die Angaben werden in der Beschreibung angzeigt.

**Link Datenschutzerklärung:** Standardmäßig wird die Datenschutzerklärung der Domain angezeigt. Exisitiert für den Dienst eine separate Datenschutzerklärung (zum Beispiel: [https://policies.google.com/privacy](https://policies.google.com/privacy)) kann diese hier hinterlegt werden. Auch REDAXO-Links (redaxo://1) können genutzt werden.

**Platzhalter Text:** Hier kann optional ein Platzhalter Text hinterlegt werden

**Platzhalter Bild:** Hier kann optional ein Platzhalter Bild aus dem Medienpoolhinterlegt werden

**Skripte, die nach Einverständnis geladen werden:** Hier werden alle Scripte (inklusive `<script>`-Tag hinterlegt, die geladen werden, sobald der Nutzer mit der Gruppe einverstanden ist). Werden unterschiedliche Skripte je Domain benötigt, muss je Domain der Dienst extra angelegt werden. 

### Gruppen anlegen

Gruppen sind die Gruppen, die der Websitebsucher später einzeln akzeptieren oder ablehnen kann. **Außerdem werden hier die Scripte hinterlegt, die geladen werden, sobald der Benutzer die Gruppe akzeptiert hat.**

![Screenshot](https://github.com/FriendsOfREDAXO/consent_manager/blob/assets/consent_manager-cookiegroups.png?raw=true)

| Feld | Beschreibung |
| ---- | ------------ |
| Schlüssel | Zur internen Verwendung und darf keine Sonderzeichen/Leerzeichen enthalten |
| Technisch notwendige Dienste: | Wenn aktiv, wird die Gruppe vorausgewählt und kann nicht deaktiviert werden Dienste |
| Domain | Hier wird die zuvor angelegte Domain ausgewählt, bei deren Aufruf die Gruppe angezeigt werden soll. |
| Name | Name der Gruppe (wird dem Website-Besucher angezeigt). |
| Beschreibung | Allgmeine Beschreibung der Gruppe (wird dem Website-Besucher angezeigt). |
| Dienste | Hier werden die zuvor angelegten Dienste ausgewählt, die der Gruppe angehören sollen |

### Beispielkonfiguration importieren

Über den Menüpunkt **Setup** kann eine Beispielkonfiguration mit Cookiegruppen importiert werden. **Vorhandene Cookies und Cookiegruppen werden dabei gelöscht!**

### In Template einfügen

Der Platzhalter `REX_CONSENT_MANAGER[]` kann im `<head>`-Bereich des Templates oder vor dem `</body>`-Tag eingefügt werden.
Gibt es mehrere Templates die die Cookie-Box aufrufen sollen muss der Platzhalter entsprechend in allen Templates eingefügt werden.

**Wichtig: Der Platzhalter funktioniert ausschließlich in REDAXO-Templates, nicht innerhalb von php-includes, Modulen oder Fragmenten.**

Durch den Parameter `forceReload=1` kann ein Reload der Webseite bei Auswahl der Cookies erzwungen werden. `REX_CONSENT_MANAGER[forceReload=1]`

**Beispiel:**

```php
<head>
    <meta charset="UTF-8">
    <title>Meine Webseite</title>
    ...
    <link rel="stylesheet" href="<?php echo template_asset_url('theme/css/meincss.min.css'); ?>">
REX_CONSENT_MANAGER[]
</head>
```

oder

```php
<head>
    <meta charset="UTF-8">
    <title>Meine Webseite</title>
    ...
    <link rel="stylesheet" href="<?php echo template_asset_url('theme/css/meincss.min.css'); ?>">
</head>
<body>
    ...
REX_CONSENT_MANAGER[]
</body>
```

**Beispiel PHP-Ausgabe**

```php
...
<body>
...
<?php echo consent_manager_frontend::getFragment(false, false, 'consent_manager_box_cssjs.php'); ?>
...
</body>
...
```

### Beispiel-Modul zur nachträglichen Abfrage

#### Eingabe-Modul (mit MForm)

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
$mform->setSize(1);
$mform->setLabel('Dienst');

$mform->addTextAreaField(2, ['label' => 'HTML/JS das bei Consent geladen wird']);
$mform->addCheckboxField(5, [1 => 'Seitenreload nötig']);

$mform->addTextAreaField(3, ['label' => 'Platzhaltertext']);
$mform->addMediaField(1, ['label' => 'Platzhalterbild']);

echo $mform->show();
```

#### Ausgabe-Modul

```php
<?php
$serviceName = '';
$cookieUid = 'REX_VALUE[1]';
$needsReload = (bool)'REX_VALUE[5]' ? '-reload' : '';
$consented = false;
$placeholderImage = '';
$placeholderText = '';

$consent_manager = new consent_manager_frontend();
$consent_manager->setDomain($_SERVER['HTTP_HOST']);

// "globale" platzhalter aus dem addon setzen
if (isset($consent_manager->cookies[$cookieUid])) {
    $placeholderImage = $consent_manager->cookies[$cookieUid]['placeholder_image'];
    $placeholderText = $consent_manager->cookies[$cookieUid]['placeholder_text'];
}

if (isset($_COOKIE['consent_manager'])) {
    $cookieData = json_decode($_COOKIE['consent_manager'], true);
    foreach ($cookieData['consents'] as $consent) {
        if ($cookieUid == $consent) {
            $consented = true;
            break;
        }
    }

}
?>

<?php if (rex::isFrontend()): ?>
    <?php if ($consented): ?>
        <div class="consent_manager-module" data-uid="<?= $cookieUid ?>">
            REX_VALUE[2 output=html]
        </div>
    <?php else: ?>
        <div class="consent_manager-module" data-payload="<?= base64_encode('REX_VALUE[2 output=html]') ?>" data-uid="<?= $cookieUid ?>">
            <div class="consent_manager-module__placeholder">
                <div class="consent_manager-module__placeholder-image">
                    <img src="/media/<?= ('REX_MEDIA[1]' ? 'REX_MEDIA[1]' : $placeholderImage) ?>" alt="">
                </div>
                <div class="consent_manager-module__placeholder-text">
                    <div class="consent_manager-module__placeholder-text-background">
                        <?= nl2br('REX_VALUE[3 output=html]' ? 'REX_VALUE[3 output=html]' : $placeholderText) ?>
                        <div class="consent_manager-show-box<?= $needsReload ?>"><b>Datenschutz-Einstellungen anpassen</b></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>
<?php else: ?>
    <label><?= $serviceName ?></label>
    <textarea readonly disabled style="width:100%;" id="consent_manager-module-textarea-REX_SLICE_ID">REX_VALUE[2 output=html]</textarea>
<?php endif ?>
```

#### zusätzliches JS

```js
jQuery(function ($) {
    'use strict';
    $('.consent_manager-module').each(function () {
        var $this = $(this),
            uid = $this.data('uid');
        $(document).on('consent_manager-saved', function (e) {
            var consents = JSON.parse(e.originalEvent.detail);
            consents.forEach(function (v) {
                if (v === uid) {
                    $this.html(window.atob($this.data('payload')));
                }
            });
        });
    });
});
```

## Anpassen (optional)

Die folgenden Einstellungen sind optional. Mit ihnen kann man Consent-Manager an die eigenen Bedürfnisse anpassen. Sie ändern jedoch nichts an der Funktionalität des AddOns.

### Dienste-Texte anpassen

Hier können alle allgemeinen Texte der Consent-Box angepasst werden.

### Mehrsprachigkeit

Verfügt die Website über mehrere Sprachen oder wird eine neue Sprache angelegt, werden die Inhalte der Startsprache automatisch übertragen und können nachher angepasst werden. **Einige Felder wie Schlüssel, Scripte, Domain und Cookie-Auswahl können nur in der Startsprache geändert werden. Die Änderungen werden automatisch auf alle weiteren Sprachen übertragen.**

### Themes für die Consent-Box

![Screenshot](https://github.com/FriendsOfREDAXO/consent_manager/blob/assets/themes.png?raw=true)

Das AddOn liefert bereits eine Auswahl Themes mit, die nur ausgewählt und aktiviert werden müssen.

Um eigene Themes zu erstellen am besten ein bereits bestehendes Theme kopieren (im AddOn-Verzeichnis `/consent_manager/scss/`)
und im Project-AddOn im Verzeichnis `/project/consent_manager_themes/` ablegen.
Hier dann die gewünschten Anpassungen vornehmen und dann unter **Themes** in der Theme-Vorschau das eigene Theme **anwenden**.


### Individuelles Design

Reicht die Lösung über ein Theme nicht, kann die Box auch vollständig individualisert werden. 
Der HTML-Code der Cookie Box liegt im Fragment `/redaxo/src/addons/consent_manager/fragments/consent_manager_box.php`. Änderungen in dieser Datei werden beim nächsten Update überschrieben. Deshalb ist es empfehlenswert, das Fragment zu kopieren und zum Beispiel im Project oder Theme AddOn abzulegen 'theme/private/fragments/consent_manager_box.php' und die Änderungen hier vorzunehmen. Das eigene CSS stellt man entweder über ein Theme scss bereit oder individuell im Template als eigene CSS-Datei. 


### Ausgabe-Einstellungen

Über den Menüpunkt **Einstellungen** kann die Ausgabe für CSS und JavaScript im Frontend gesteuert werden.
Standardmäßig wird auf jeder Seite das benötigte JavaScript und die CSS-Datei `consent_manager_frontend.css` ausgegeben.

Der Platzhalter `REX_CONSENT_MANAGER[]` im Template wird durch folgenden Code ersetzt.

```html
<style><style>/*consent_manager_frontend.css*/ @keyframes fadeIn{0%{opacity:0}100%{opacity:1}}</style>
<script src="./index.php?consent_manager_outputjs=1&amp;lang=1&amp;a=6&amp;i=false&amp;h=false&amp;cid=43&amp;v=4&amp;r=0&amp;t=16732118931" id="consent_manager_script" defer></script>
```

Sind im eigenen Frontend-Theme Styles für die Consent-Box vorhanden kann hier die Ausgabe der CSS-Datei `consent_manager_frontend.css` durch aktivieren der Einstellung **Eigenes CSS verwenden** unterdrückt werden. Es wird dann nur die JavaScript-Zeile ausgegeben.

## Gesetzte Cookies / Einwilligungshistorie

Mit Hilfe des Platzhalters `REX_COOKIEDB[]` können alle derzeit gesetzten Cookies sowie die Einwilligungshistorie z.B. in der Datenschutzerklärung ausgegeben werden.

## Tipps & Tricks

Hast du eigene Tipps & Tricks? [Füge Sie auf Github direkt in die Readme hinzu](https://github.com/FriendsOfREDAXO/consent_manager/blob/master/README.md) oder lege ein [Issue](https://github.com/FriendsOfREDAXO/consent_manager/issues) an.

### Cookie-Box manuell aufrufen

Soll der Nutzer die Möglichkeit bekommen, seine Einstellungen nachträglich anzupassen (zum Beispiel im Impressum oder auf einer Cookie-Seite) ist das mit folgenden Links möglich:
`<a class="consent_manager-show-box">Cookie Einstellungen bearbeiten</a>` öffnet die Cookie-Box.
`<a class="consent_manager-show-box-reload">Cookie Einstellungen bearbeiten</a>` öffnet die Cookie-Box und erzwingt einen Page-Reload nach der Einwilligung.

Die Cookie-Box kann auch durch einen JavaScript-Aufruf geöffnet werden `consent_manager_showBox()`.

```php
// Achtung hier mit Unterstrichen da sonst der Beispiel-Code verstümmelt wird.
// Unterstriche bei `on_click` und `java_script` müssen entfernt werden!
<button on_click="java_script:consent_manager_showBox();">Cookie-Einstellungen</button>
```

### Consent mit JavaScript abfragen

Um mit JavaScript einen Consent abzufragen die Funktion `consent_manager_hasconsent()` verwenden.

```js
<script>
window.addEventListener('load', (event) => {
    if (true === consent_manager_hasconsent('youtube')) {
        alert('youtube Ok');
    }
});
</script>
```

### Consent mit PHP abfragen

Um mit PHP einen Consent abzufragen die Klassen-Funktion `consent_manager_util::has_consent()` verwenden.

```php
<?php
if (true === consent_manager_util::has_consent('youtube')) {
    echo('youtube Ok');
}
?>
```


### Seite ohne Consent-Box

Um z.B. einen Link zu teilen bei dem keine Consent-Box erscheinen soll kann in den Einstellungen ein **TOKEN** hinterlegt werden.
An die URL dann einfach `skip_consent=MEINTOKEN` anhängen.

z.B.: `https://meinedomain.de/SeiteOhneToken.html?skip_consent=MEINTOKEN`

### Scripte mit PHP laden

Neben der Einbindung der Scripte direkt über das Addon lassen sich Scripte auch per PHP einbinden. Somit kann man (am Beispiel GoogleMaps) eine Meldung ausgeben, dass bestimmte Cookies akzeptiert werden müssen um die Karte zu laden.
Problem dabei: öffnet man die Cookie-Box und akzeptiert die Cookies, wird zwar das Script geladen, aber ohne Page-Reload ändert sich der Inhalt der Seite nicht. Deshalb sollte man hier den Link: `<a class="consent_manager-show-box-reload">Cookie Einstellungen bearbeiten</a>` verwenden.

```php
$arr = json_decode($_COOKIE['consent_manager'], true);
$check = [];
if ($arr)
{
$check = array_flip($arr['consents']);
}
#dump($arr);
if ($check['google-maps']) {
  // Code Ausgabe bei akzeptierter CookieGruppe
  // GoogleMaps-Code
} else {
  // Code Ausgabe bei abgelehnter CookieGruppe
  // Warnhinweis + <a class="consent_manager-show-box-reload">Cookie Einstellungen bearbeiten</a>
}
```

### Berechtigung für Redakteure

Um die Cookie-Texte auch für Redakteure zur Änderung bereitzustellen muss diesen das Recht `consent_manager[]` und zusätzlich das Recht `consent_manager[texteditonly]` zugewiesen werden. Die Redakteure können dann nur die Cookie-Texte ändern, alle anderen Funktionen werden ausgeblendet.

## Fehlerbehebung

### Die Consent-Box wird nicht angzeigt

* Ist eine Domain hinterlegt und in der Cookie-Gruppe zugeordnet? - Bei mehreren Domains sind die Gruppen für jede Domain einzeln anzulegen.
* Stimmt die zugeordnete Domain mit der aufgerufenen Domain überein? - www.meinedomain.de und meinedomain.de sind zwei verschiedene Domains.
* Ist die Website über die zugeordnete Domain (www.meinedomain.de) erreichbar? - Unterordner Installationen funktionieren nicht.
* Sind der Platzhalter REX_CONSENT_MANAGER[] oder der PHP-Code in einem Template im `head`-Bereich hinterlegt? .
* Unter Einstellungen ist *Eigenes CSS verwenden* aktiviert aber es wird kein eigenes CSS eingebunden (HTML der Box wird am Seitenende angezeigt und nicht als Popup)
* Ist der Startkartikel der Seite auch als Not Found Artikel (404) konfiguriert? - Die Cookie-Box wird beim 404 Artikel nicht ausgegeben

### Die Consent-Box wird angezeigt, aber die Cookies werden nicht angezeigt

* Ist eine entsprechende Cookie-Gruppe angelegt?
* Wurde Dienst in der entsprechenden Gruppe aktiviert?

### Die Consent-Box und Cookies werden angezeigt, Scripte aber nicht geladen.

* Sind die Scripte in der entsprechenden Cookies hinterlegt?
* Sind die Scripte inklusive `<script>...</script>`-Tag hinterlegt?

### Fehler melden

Du hast einen Fehler gefunden oder wünscht dir ein Feature? Lege ein [Issue auf Github an](https://github.com/FriendsOfREDAXO/consent_manager/issues).

## Lizenz, Autor, Credits, Sponsoren

### Lizenz

MIT Lizenz, siehe [LICENSE.md](https://github.com/FriendsOfREDAXO/consent_manager/blob/master/LICENSE.md)
[cookie.js](https://github.com/js-cookie/js-cookie): [MIT Lizenz](https://github.com/js-cookie/js-cookie/blob/master/LICENSE)

### Autor

**Friends Of REDAXO**
[https://github.com/FriendsOfREDAXO](https://github.com/FriendsOfREDAXO)

**Projekt-Leads**
[Ingo Winter](https://github.com/IngoWinter), [Andreas Eberhard](https://github.com/aeberhard)

### Credits

[Contributors:](https://github.com/FriendsOfREDAXO/consent_manager/graphs/contributors)

First Release: [Ingo Winter](https://github.com/IngoWinter).

### Sponoren:

[Thomas Blum](https://github.com/tbaddade/) wird eine Menge Code aus seinem [Sprog Addon](https://github.com/tbaddade/redaxo_sprog) in Consent-Manager wiederfinden.
[Thomas Skerbis](https://github.com/skerbis) hat unermüdlich getestet und für die Entwicklung gespendet,
[Peter Bickel](https://github.com/polarpixel) hat für die Entwicklung gespendet,
[Oliver Kreischer](https://github.com/olien) hat den Keks gebacken.
