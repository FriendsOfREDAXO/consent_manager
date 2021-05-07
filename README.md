# Consent-Manager 3.0 für REDAXO CMS

![logo](https://github.com/FriendsOfREDAXO/consent_manager/blob/assets/consent_manager-logo.png?raw=true)

Stellt ein Opt-In Cookie-Banner zur Verfügung. Cookies können in selbst definierte Gruppen zusammengefasst werden. Der Website Besucher bekommt eine Cookie-Box angezeigt in der er allen oder einzelnen Gruppen zustimmen kann. Es existiert eine Gruppe **Notwendig**, die nicht deaktiviert werden kann. Die Cookie-Box kann erneut (zum Beispiel über einen Link im Impressum) aufgerufen werden, um die Auswahl nachträglich zu ändern. Alle Texte sowie die Gestaltung der Cookie-Box sind anpassbar.

![Screenshot](https://github.com/FriendsOfREDAXO/consent_manager/blob/assets/consent_manager.jpg?raw=true)

## Kurzanleitung

1. AddOn consent_manager über den Installer herunterladen und installieren.
2. [Domains hinterlegen](#domains-hinzufuegen)
3. [Cookies anlegen](#cookies-anlegen)
4. [Cookie-Gruppen anlegen](#cookie-gruppen-anlegen)
5. Der jeweiligen Domain-Gruppe die gewünschten Domains und Cookies zuordnen und JS Scripte hinterlegen.
6. `REX_CONSENT_MANAGER[]` in den `head`-Bereich in alle gewünschten [Templates einfügen](#in-template-einfuegen), bzw. `echo consent_manager_frontend::getFragment(false, 'consent_manager_box_cssjs.php');`, wenn via PHP.
7. Alle weiteren Einstellungen sind optional.

> **Hinweis:** Wird keine Cookie-Box angezeigt Punkte 2 bis 6 nochmal checken ... und/oder siehe [Fehlerbehebung](#fehlerbehebung)

## Einrichten

### Domains hinzufügen

Consent-Manager kann für mehrere Domains einzeln gesteuert werden. Jede Domain der REDAXO-Instanz die Consent-Manager nutzen soll muss einzeln hinterlegt werden. Zum Beispiel `www.meinedomain.de (ohne Protokoll http/https)`. Das gilt auch für Subdomains **(auch www)**.
Die Datenschutzerklärung und das Impressum wird für jede Domain hinterlegt. Die Seiten werden nachher automatisch in der Cookie-Box verlinkt.
Beim Aufruf wird die hier hinterlegte Domain mit `$_SERVER['HTTP_HOST']` verglichen und die Cookie-Box wird bei Übereinstimmung angezeigt.

### Cookies anlegen

Für jeden Dienst (zum Beispiel Google Analytics oder Matamo) wird ein einzelner Eintrag erstellt. Hat ein Dienst mehrere Cookies werden diese trotzdem in einem einzigen Eintrag beschrieben. **Alle Angaben dienen nur zur Information des Webseiten Besuchers und haben keinen Einfluss auf das Setzen/Löschen der Cookies bzw. deren Eigenschaften!**
Als Beispiel sind zwei Dienste  (google-analytics und matomo) angelegt, diese könnnen ggf. angepasst oder gelöscht werden.
**Der Dienst consent_manager wird zwingend von Consent-Manager benötigt. Er ist der Gruppe Notwendig zugeordnet und kann nicht gelöscht werden. Hier werden die Einstellungen der Website Besucher gespeichert.**

![Screenshot](https://github.com/FriendsOfREDAXO/consent_manager/blob/assets/consent_manager-cookies.jpg?raw=true)

**Schlüssel:** ist zur internen Verwendung und darf keine Sonderzeichen/Leerzeichen enthalten.

**Dienstname:** wird später in der Cookie-Box angezeigt.

**Cookie Definitionen:** enthält die Beschreibung aller Cookies des Dienstes die in der Cookie-Box angezeigt werden sollen. Die Beschreibung wird im *YAML-Format* hinterlegt, zum Beispiel:

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

**Anbieter:** Hier kann optional der Anbieter hinterlegt werden (zum Beispiel Google). Die Angaben werden in der Beschreibung angzeigt.

**Link Datenschutzerklärung:** Standardmäßig wird die Datenschutzerklärung der Domain angezeigt. Exisitiert für den Dienst eine separate Datenschutzerklärung (zum Beispiel: [https://policies.google.com/privacy](https://policies.google.com/privacy)) kann diese hier hinterlegt werden. Auch REDAXO-Links (redaxo://1) können genutzt werden.

**Platzhalter Text:** Hier kann optional ein Platzhalter Text hinterlegt werden

**Platzhalter Bild:** Hier kann optional ein Platzhalter Bild aus dem Medienpoolhinterlegt werden

### Cookie-Gruppen anlegen

Cookie-Gruppen sind die Gruppen, die der Websitebsucher später einzeln akzeptieren oder ablehnen kann. **Außerdem werden hier die Scripte hinterlegt, die geladen werden, sobald der Benutzer die Gruppe akzeptiert hat.**

![Screenshot](https://github.com/FriendsOfREDAXO/consent_manager/blob/assets/consent_manager-cookiegroups.jpg?raw=true)

**Schlüssel:** Zur internen Verwendung und darf keine Sonderzeichen/Leerzeichen enthalten.

**Checkbox Technisch notwenidge Cookies:** Ist die Checkbox aktiv, wird die Gruppe vorausgewählt und kann nicht deaktiviert werden (Sinnvoll ist nur eine Gruppe mit notwendigen Cookies).

**Domain:** Hier wird die zuvor angelegte Domain ausgewählt, bei deren Aufruf die Gruppe angezeigt werden soll.

**Reihenfolge:** Die Reihenfolge in der die Gruppen dem Website-Besucher angezeigt werden.

**Name:** Name der Gruppe (wird dem Website-Besucher angezeigt).

**Beschreibung:** Allgmeine Beschreibung der Gruppe (wird dem Website-Besucher angezeigt).

**Cookies:** Hier werden die zuvor angelegten Cookies ausgewählt, die der Gruppe angehören sollen

**Skripte, die nach Einverständnis geladen werden:** Hier werden alle Scripte (inklusive `<script>`-Tag hinterlegt, die geladen werden, sobald der Nutzer mit der Gruppe einverstanden ist). Zu Beachten ist, dass nur die Scripte eingebunden werden die zu den vorher ausgewählten Cookies gehören.

### Beispielkonfiguration importieren

Über den Menüpunkt **Setup** kann eine Beispielkonfiguration mit Cookiegruppen importiert werden. **Vorhandene Cookies und Cookiegruppen werden dabei gelöscht!**

### In Template einfügen

Der Platzhalter `REX_CONSENT_MANAGER[]` kann im `<head>`-Bereich des Templates oder vor dem `</body>`-Tag eingefügt werden.
Gibt es mehrere Templates die die Cookie-Box aufrufen sollen muss der Platzhalter entsprechend in allen Templates eingefügt werden.

**Wichtig: Der Platzhalter funktioniert ausschließlich in REDAXO-Templates, nicht innerhalb von php-includes oder Fragmenten.**

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
`echo consent_manager_frontend::getFragment(false, 'consent_manager_box_cssjs.php');`
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

### Cookie-Texte anpassen

Hier können alle allgemeinen Texte der CookieBox angepasst werden.

### Mehrsprachigkeit

Verfügt die Website über mehrere Sprachen oder wird eine neue Sprache angelegt, werden die Inhalte der Startsprache automatisch übertragen und können nachher angepasst werden. **Einige Felder wie Schlüssel, Scripte, Domain und Cookie-Auswahl können nur in der Startsprache geändert werden. Die Änderungen werden automatisch auf alle weiteren Sprachen übertragen.**

### Design anpassen

Das Design der Cookie-Box kann nach Belieben angepasst werden. Der HTML-Code der Cookie Box liegt im Fragment `/redaxo/src/addons/consent_manager/fragments/consent_manager_box.php`. Änderungen in dieser Datei werden aber beim nächsten Update überschrieben. Deshalb ist es empfehlenswert, das Fragment zu kopieren und zum Beispiel im Project oder Theme AddOn abzulegen 'theme/private/fragments/consent_manager_box.php' und die Änderungen hier vorzunehmen.
Anschließend die Datei `consent_manager_frontend.css` an einen beliebigen Ort kopieren, anpassen und im eigenen Template/CSS einbinden (Eigenes CSS verwenden in den Einstellungen aktivieren!).

### Ausgabe-Einstellungen

Über den Menüpunkt **Einstellungen** kann die Ausgabe für CSS und JavaScript im Frontend gesteuert werden.
Standardmäßig wird auf jeder Seite das benötigte JavaScript und die CSS-Datei `consent_manager_frontend.css` ausgegeben.

Der Platzhalter `REX_CONSENT_MANAGER[]` im Template wird durch folgenden Code ersetzt.

```html
<link rel="stylesheet" href="/assets/addons/consent_manager/consent_manager_frontend.css?v=1610997727">
<script>var consent_manager_parameters = {initially_hidden: false, domain: "domain.de", consentid: "6005e443914e75.55868698", cacheLogId: "46", version: "3", fe_controller: "/index.php", hidebodyscrollbar: true};</script>
<script src="/index.php?consent_manager_outputjs=1&amp;clang=1&amp;v=1610978112" id="consent_manager_script"></script>
```

Sind im eigenen Frontend-Theme Styles für die Consent-Box vorhanden kann hier die Ausgabe der CSS-Datei `consent_manager_frontend.css` durch aktivieren der Einstellung **Eigenes CSS verwenden** unterdrückt werden. Es werden dann nur die JavaScript-Zeilen ausgegeben.

Soll JavaScript für die Consent-Box nur ausgegeben werden wenn dies auch notwendig ist, kann das durch aktivieren der Einstellung **CSS + JavaScript im Frontend nur bei Bedarf ausgeben** erreicht werden.
JavaScript wird dann nur ausgegeben wenn noch kein Cookie gesetzt wurde, wenn Cookies entfallen oder neu hinzugefügt wurden, oder auf der Seite ein Link mit der Klasse `consent_manager-show-box` oder `consent_manager-show-box-reload` existiert.

## Tipps & Tricks

Hast du eigene Tipps & Tricks? [Füge Sie auf Github direkt in die Readme hinzu](https://github.com/FriendsOfREDAXO/consent_manager/blob/master/README.md) oder lege ein [Issue](https://github.com/FriendsOfREDAXO/consent_manager/issues) an.

### Cookie-Box manuell aufrufen

Soll der Nutzer die Möglichkeit bekommen, seine Einstellungen nachträglich anzupassen (zum Beispiel im Impressum oder auf einer Cookie-Seite) ist das mit folgenden Links möglich:
`<a class="consent_manager-show-box">Cookie Einstellungen bearbeiten</a>` öffnet die Cookie-Box.
`<a class="consent_manager-show-box-reload">Cookie Einstellungen bearbeiten</a>` öffnet die Cookie-Box und erzwingt einen Page-Reload nach der Einwilligung.

### Scripte mit PHP Laden

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
if ($check['googlemaps']) {
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

### Die Cookie-Box wird nicht angzeigt

* Ist eine Domain hinterlegt und in der Cookie-Gruppe zugeordnet? - Bei mehreren Domains sind die Cookie-Gruppen für jede Domain einzeln anzulegen.
* Stimmt die zugeordnete Domain mit der aufgerufenen Domain überein? - www.meinedomain.de und meinedomain.de sind zwei verschiedene Domains.
* Ist die Website über die zugeordnete Domain (www.meinedomain.de) erreichbar? - Unterordner Installationen funktionieren nicht.
* Ist der Platzhalter REX_CONSENT_MANAGER[] in einem Template im `head`-Bereich hinterlegt? - eine Integration über php include ist nicht möglich.
* Unter Einstellungen ist *Eigenes CSS verwenden* aktiviert aber es wird kein eigenes CSS eingebunden (HTML der Box wird am Seitenende angezeigt und nicht als Popup)

### Die Cookie-Box wird angezeigt, aber die Cookies werden nicht angezeigt

* Ist eine entsprechende Cookie-Gruppe angelegt?
* Wurde Dienst in der entsprechenden Gruppe aktiviert?

### Die Cookie-Box und Cookies werden angezeigt, Scripte aber nicht geladen.

* Sind die Scripte in der entsprechenden Cookies hinterlegt?
* Sind die Scripte inklusive `<script>...</script>`-Tag hinterlegt?

### Fehler melden

Du hast einen Fehler gefunden oder wünscht dir ein Feature? Lege ein [Issue auf Github an](https://github.com/FriendsOfREDAXO/consent_manager/issues).

## Lizenz, Autor, Credits

### Lizenz

MIT Lizenz, siehe [LICENSE.md](https://github.com/FriendsOfREDAXO/consent_manager/blob/master/LICENSE.md)
[cookie.js](https://github.com/js-cookie/js-cookie): [MIT Lizenz](https://github.com/js-cookie/js-cookie/blob/master/LICENSE)

### Autor

**Friends Of REDAXO**
[http://www.redaxo.org](http://www.redaxo.org)
[https://github.com/FriendsOfREDAXO](https://github.com/FriendsOfREDAXO)

**Projekt-Lead**
[Ingo Winter](https://github.com/IngoWinter)
[Andreas Eberhard](https://github.com/aeberhard)

### Credits

First Release: [Ingo Winter](https://github.com/IngoWinter)
[Thomas Blum](https://github.com/tbaddade/) wird eine Menge Code aus seinem [Sprog Addon](https://github.com/tbaddade/redaxo_sprog) in Consent-Manager wiederfinden
[Thomas Skerbis](https://github.com/skerbis) hat unermüdlich getestet und für die Entwicklung gespendet
[Peter Bickel](https://github.com/polarpixel) hat für die Entwicklung gespendet
[Oliver Kreischer](https://github.com/olien) hat den Keks gebacken
