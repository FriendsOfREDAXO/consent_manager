# Cookie-Gedöns
## ACHTUNG !
Die Anleitung bezieht sich auf iwcc 1.x die hier installierte Version 2.0 ist im Moment noch Beta und bringt einige Änderungen mit sich.
Hier gibt es ein [Beispielmodul](https://gist.github.com/IngoWinter/31df14685b45ad8980aadaec1e757363) zur aktuellen Version


![logo](https://github.com/FriendsOfREDAXO/iwcc/blob/assets/iwcc-logo.jpg?raw=true)


Stellt ein Opt-In Cookie Banner zur Verfügung. Cookies können in selbst definierte Gruppen zusammengefasst werden. Der Website Besucher bekommt eine Cookie-Box angezeigt in der er allen oder einzelnen Gruppen zustimmen kann. Es existiert eine Gruppe **Notwendig** die nicht deaktiviert werden kann. Die Cookie-Box kann erneut (zum Beispiel über einen Link im Impressum) aufgerufen werden um die Auswahl nachträglich zu ändern. Alle Texte sowie die Gestaltung der Cookie-Box sind anpassbar.

![Screenshot](https://github.com/FriendsOfREDAXO/iwcc/blob/assets/iwcc.jpg?raw=true)


## Kurzanleitung
1. AddOn iwcc über den Installer herunterladen und installieren. 
2. [Domains hinterlegen](#header-domains-hinzufuegen)
3. [Cookies anlegen](#header-cookies-anlegen)
4. [Cookie-Gruppen anlegen](#header-cookie-gruppen-anlegen)
5. Der jeweiligen Domain-Gruppe die gewünschten Domains und Cookies zuordnen und JS Scripte hinterlegen.
6. <code>REX_IWCC[]</code> in den <code>head</code>-Bereich in alle gewünschten [Templates einfügen](#header-in-template-einfuegen)
7. Alle weiteren Einstellungen sind optional.

## Einrichten

### Domains hinzufügen
Cookie-Gedöns kann für mehrere Domains einzeln gesteuert werden. Jede Domain der Redaxo-Instanz die Cookie-Gedöns nutzen soll muss einzeln hinterlegt werden. Zum Beispiel `www.meinedomain.de (ohne Protokoll http/https)`. Das gilt auch für Subdomains **(auch www)**.
Die Datenschutzerklärung und das Impressum wird für jede Domain hinterlegt. Die Seiten werden nachher automatisch in der Cookie-Box verlinkt.
Beim Aufruf wird die hier hinterlegte Domain mit `$_SERVER['HTTP_HOST']` verglichen und die Cookie-Box wird bei Übereinstimmung angezeigt.

### Cookies anlegen
Für jeden Dienst (zum Beispiel Google Analytics oder Matamo) wird ein einzelner Eintrag erstellt. Hat ein Dienst mehrere Cookies werden diese trotzdem in einem einzigen Eintrag beschrieben. **Alle Angaben dienen nur zur Information des Webseiten Besuchers und haben keinen Einfluss auf das Setzen/Löschen der Cookies bzw. deren Eigenschaften!**
Als Beispiel sind zwei Dienste  (google-analytics und matomo) angelegt, diese könnnen ggf. angepasst oder gelöscht werden. 
**Der Dienst iwcc wird zwingend von Cookie-Gedöns benötigt. Er ist der Gruppe Notwendig zugeordnet und kann nicht gelöscht werden. Hier werden die Einstellungen der Website Besucher gespeichert.**

![Screenshot](https://github.com/FriendsOfREDAXO/iwcc/blob/assets/iwcc-cookies.jpg?raw=true)

**Schlüssel:** ist zur internen Verwendung und darf keine Sonderzeichen/Leerzeichen enthalten.
**Dienstname:** wird später in der Cookie-Box angezeigt.
**Cookie Definitionen:** enthält die Beschreibung aller Cookies des Dienstes die in der Cookie-Box angezeigt werden soll. Die Beschreibung wird im *YAML-Format* hinterlegt, zum Beispiel:

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
**Datenschutzerklärung:** Standardmäßig wird die Datenschutzerklärung der Domain angezeigt. Exisitiert für den Dienst eine separate Datenschutzerklärung (zum Beispiel: https://policies.google.com/privacy) kann diese hier hinterlegt werden. Auch Redaxo-Links (redaxo://1) können genutzt werden.


### Cookie-Gruppen anlegen
Cookie-Gruppen sind die Gruppen, die der Websitebsucher später einzeln akzeptieren oder ablehnen kann. **Außerdem werden hier die Scripte hinterlegt, die geladen werden, sobald der Benutzer die Gruppe akzeptiert hat.**

![Screenshot](https://github.com/FriendsOfREDAXO/iwcc/blob/assets/iwcc-cookiegroups.jpg?raw=true)

**Schlüssel:** Zur internen Verwendung und darf keine Sonderzeichen/Leerzeichen enthalten.
**Checkbox Technisch notwenidge Cookies:** Ist die Checkbox aktiv, wird die Gruppe vorausgewählt und kann nicht deaktiviert werden (Sinnvoll ist nur eine Gruppe mit notwendigen Cookies).
**Domain:** Hier wird die zuvor angelegte Domain ausgewählt, bei deren Aufruf die Gruppe angezeigt werden soll.
**Reihenfolge:** Die Reihenfolge in der die Gruppen dem Website-Besucher angezeigt werden.
**Name:** Name der Gruppe (wird dem Website-Besucher angezeigt).
**Beschreibung:** Allgmeine Beschreibung der Gruppe (wird dem Website-Besucher angezeigt).
**Cookies:** Hier werden die zuvor angelegten Cookies ausgewählt, die der Gruppe angehören sollen
**Skripte, die nach Einverständnis geladen werden:** Hier werden alle Scripte (inklusive `<script>`-Tag hinterlegt, die geladen werden, sobald der Nutzer mit der Gruppe einverstanden ist). Zu Beachten ist, dass nur die Scripte eingebunden werden die zu den vorher ausgewählten Cookies gehören.

### In Template einfügen
Der Platzhalter `REX_IWCC[]` muss im `head`-Bereich des Templates eingefügt werden. Gibt es mehrere Templates mit `head`-Bereichen, muss der Platzhalter in allen Templates eingefügt werden, die die Cookie-Box aufrufen sollen. **Wichtig: der Platzhalter muss zwingend in ein Template kopiert werden und darf nicht über php include eingebunden werden.**

## Anpassen (optional)
Die folgenden Einstellungen sind optional. Mit ihnen kann man Cookie-Gedöns an die eigenen Bedürfnisse anpassen. Sie ändern jedoch nichts an der Funktionalität des AddOns.

### Cookie-Texte anpassen
Hier können alle allgemeinen Texte der CookieBox angepasst werden.

### Mehrsprachigkeit
Verfügt die Website über mehrere Sprachen oder wird eine neue Sprache angelegt, werden die Inhalte der Startsprache automatisch übertragen und können nachher angepasst werden. **Einige Felder wie Schlüssel, Scripte, Domain und Cookie-Auswahl können nur in der Startsprache geändert werden. Die Änderungen werden automatisch auf alle weiteren Sprachen übertragen.**

### Design anpassen
Das Design der Cookie-Box kann nach Belieben angepasst werden. HTML, CSS und Skripte der Cookie Box liegen im Fragment `/redaxo/src/addons/iwcc/fragments/iwcc_box.php`. Änderungen in dieser Datei werden aber beim nächsten Update überschrieben. Deshalb ist es empfehlenswert, das Fragment zu kopieren und zum Beispiel im Project oder Theme AddOn abzulgen 'theme/private/fragments/iwcc_box.php' und die Änderungen hier vorzunehmen.
Anschließend die Datei `iwcc_frontend.css` an einen beliebigen Ort kopieren, anpassen und im eigenen Fragment einbinden.


## Tipps & Tricks
Hast du eigene Tipps & Tricks? [Füge Sie auf Github direkt in die Readme hinzu](https://github.com/FriendsOfREDAXO/iwcc/blob/master/README.md) oder lege ein [Issue](https://github.com/FriendsOfREDAXO/iwcc/issues) an.

### Cookie-Box manuell aufrufen
Soll der Nutzer die Möglichkeit bekommen, seine Einstellungen nachträglich anzupassen (zum Beispiel im Impressum oder auf einer Cookie-Seite) ist das mit folgenden Links möglich:
`<a class="iwcc-show-box">Cookie Einstellungen bearbeiten</a>` öffnet die Cookie-Box.
`<a class="iwcc-show-box-reload">Cookie Einstellungen bearbeiten</a>` öffnet die Cookie-Box und erzwingt einen Page-Reload nach der Einwilligung. 

### Scripte mit PHP Laden
Neben der Einbindung der Scripte direkt über das Addon lassen sich Scripte auch per PHP einbinden. Somit kann man (am Beispiel GoogleMaps) eine Meldung ausgeben, dass bestimmte Cookies akzeptiert werden müssen um die Karte zu laden.
Problem dabei: öffnet man die Cookie-Box und akzeptiert die Cookies, wird zwar das Script geladen, aber ohne Page-Reload ändert sich der Inhalt der Seite nicht. Deshalb sollte man hier den Link: `<a class="iwcc-show-box-reload">Cookie Einstellungen bearbeiten</a>` verwenden.
 
```php 
// iwcc cookie auslesen und in Array umwandeln
$arr = json_decode($_COOKIE['iwcc'], true);  
// prüfe ob die GoogleMaps-Gruppe ausgewählt wurde
if ($arr['googlemaps']) 
{
  // Code Ausgabe bei akzeptierter CookieGruppe
  // GoogleMaps-Code
} else {
  // Code Ausgabe bei abgelehnter CookieGruppe
  // Warnhinweis + <a class="iwcc-show-box-reload">Cookie Einstellungen bearbeiten</a>
}
```

## Fehlerbehebung

### Die Cookie-Box wird nicht angzeigt
* Ist eine Domain hinterlegt und in der Cookie-Gruppe zugeordnet? - Bei mehreren Domains sind die Cookie-Gruppen für jede Domain einzeln anzulegen.
* Stimmt die zugeordnete Domain mit der aufgerufenen Domain überein? - www.meinedomain.de und meinedomain.de sind zwei verschiedene Domains.
* Ist die Website über die zugeordnete Domain (www.meinedomain.de) erreichbar? - Unterordner Installationen funktionieren nicht.
* Ist der Platzhalter REX_IWCC[] in einem Template im `head`-Bereich hinterlegt? - eine Integration über php include ist nicht möglich.

### Die Cookie-Box wird angezeigt, aber die Cookies werden nicht angezeigt
* Ist eine entsprechende Cookie-Gruppe angelegt?
* Wurde Dienst in der entsprechenden Gruppe aktiviert?

### Die Cookie-Box und Cookies werden angezeigt, Scripte aber nicht geladen.
* Sind die Scripte in der entsprechenden Cookie-Gruppe hinterlegt?
* Sind die Scripte inklusive `<script>...</script>`-Tag hinterlegt?

### Fehler melden
Du hast einen Fehler gefunden oder wünscht dir ein Feature? Lege ein [Issue auf Github an](https://github.com/FriendsOfREDAXO/iwcc/issues).


## Lizenz, Autor, Credits

### Lizenz
MIT Lizenz, siehe [LICENSE.md](https://github.com/FriendsOfREDAXO/iwcc/blob/master/LICENSE.md)  
[cookie.js](https://github.com/js-cookie/js-cookie): [MIT Lizenz](https://github.com/js-cookie/js-cookie/blob/master/LICENSE)  
[Font Awesome](https://fontawesome.com/v4.7.0/): [SIL Lizenz](https://fontawesome.com/v4.7.0/license/)  
[pretty checkbox](https://github.com/lokesh-coder/pretty-checkbox): [MIT Lizenz](https://github.com/lokesh-coder/pretty-checkbox/blob/master/LICENSE)  

### Autor
**Friends Of REDAXO**  
http://www.redaxo.org  
https://github.com/FriendsOfREDAXO  
**Projekt-Lead**  
[Ingo Winter](https://github.com/IngoWinter)

### Credits
First Release: [Ingo Winter](https://github.com/IngoWinter)  
[Thomas Blum](https://github.com/tbaddade/) wird eine Menge Code aus seinem [Sprog Addon](https://github.com/tbaddade/redaxo_sprog) in Cookie-Gedöns wiederfinden  
[Thomas Skerbis](https://github.com/skerbis) hat unermüdlich getestet und für die Entwicklung gespendet  
[Peter Bickel](https://github.com/polarpixel) hat für die Entwicklung gespendet   
[Oliver Kreischer](https://github.com/olien) hat den Keks gebacken
