# Cookie-Gedöns

Stellt ein Opt-In Cookie Banner zur Verfügung. Cookies werden in Gruppen zusammengefasst. Der Website Nutzer kann Cookies gruppenweise akzeptieren. Die Cookie Box kann über einen Klick auf ein Element mit der Klasse <code>iwcc-show-box</code> wieder geöffnet werden, z.B. ein <code>&lt;a class="iwcc-show-box"&gt;Cookie Einstellungen bearbeiten&lt;/a&gt;</code> im Footer oder der Datenschutzerklärung.

![Screenshot](https://github.com/FriendsOfREDAXO/iwcc/blob/assets/iwcc.jpg?raw=true)

## Installation

1. Über den Installer herunterladen und installieren. 
2. Domains hinterlegen, bei denen die Cookie Box aktiviert werden soll
3. In den Cookie-Gruppen die gewünschten Domains zuweisen, ggf. zusätzliche Cookie-Gruppen pro Domain anlegen und gewünschtes JavaScript hinterlegen.
4. Ggf. weitere Dienste unter `Cookie` anlegen und in den Cookie-Gruppen zuordnen.
5. Den Platzhalter <code>REX_IWCC[]</code> in allen gewünschten Templates im <code>head</code>-Bereich des HTML-Quellcodes.

Für den leichteren Einstieg wurden einige Cookie-/ und Cookie-Gruppen bereits angelegt.

## Einrichtung

### Cookie Gruppen
Hier werden die Cookie Gruppen definiert, die der Nutzer akzeptieren kann. Pro Gruppe können Skripte hinterlegt werden, die nach Akzeptieren der Gruppe ausgeführt werden.

![Screenshot](https://github.com/FriendsOfREDAXO/iwcc/blob/assets/iwcc-cookiegroups.jpg?raw=true)

### Cookies
Pro Eintrag wird ein Dienst (mit einer beliebigen Anzahl Cookies) definiert, zb Google Analytics oder Matomo.

![Screenshot](https://github.com/FriendsOfREDAXO/iwcc/blob/assets/iwcc-cookies.jpg?raw=true)

Die einzelnen Cookies des Dienstes werden im YAML Format hinterlegt, zb:

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

Es gibt einen nicht löschbaren Cookie <code>iwcc</code>. In diesem speichert das Addon die Auswahl des Nutzers.
**Alles was im Tab Cookies eingegeben wird dient nur zur Information des Nutzers und hat auf das Setzen/Löschen der Cookies oder deren Eigenschaften kein Einfluss.**


#### iwcc Cookie per PHP auswerten 

und Inhalte / Skripte entsprechend der Zustimmung darstellen

```php 
// iwcc cookie auslesen und in Array umwandeln
$arr = json_decode($_COOKIE['iwcc'], true);  
// prüfe ob die Googlemaps-Gruppe ausgewählt wurde
if ($arr['googlemaps']) 
{
  // gewünschten Code ausgeben
}
```

### Texte
Die Texte der Cookie Box

### Domains
Das Addon reagiert auf die hinterlegte Domain, z.B.: <code>meinedomain.de</code>. Für Subdomains **(auch www)** ist ein gesonderter Eintrag erforderlich. Im Fragment wird die hier hinterlegte Domain mit <code>$_SERVER['HTTP_HOST']</code> verglichen.

## Mehrsprachigkeit
Beim Anlegen einer neuen Sprache werden existierende Einträge in die neue Sprache kopiert. Bestimmte Felder (zb. Schlüssel, Skripte oder Cookie Namen) können nur in der ersten Sprache editiert werden.

## Design
HTML, CSS und Skripte der Cookie Box liegen im Fragment <code>/redaxo/src/addons/iwcc/fragments/iwcc_box.php</code>. Das Design kann nach Belieben angepasst werden. Dazu das mitgelieferte Stylesheet überschreiben oder komplett entfernen und was eigenes machen. Tipp hierzu: in <code>/redaxo/src/addons/iwcc/scss/</code> findet sich das Stylesheet als SCSS. Wenn man als Admin eingeloggt ist und der Debug-Mode aktiviert ist, wird das Stylesheet nach Änderungen neu generiert.


## Troubleshooting
* Ist <code>REX_IWCC[]</code> in einem Redaxo Template hinterlegt? Nur in einem Redaxo Template werden auch die REX_VARs ersetzt, in eigenen PHP includes nicht.
* Ist eine Domain hinterlegt und den Cookie Gruppen zugeordnet?
* Stimmt die hinterlegte Domain mit der Frontend Domain überein (www.meinedomain.de ist etwas anderes als meinedomain.de)?
* Ist die Website über eine Domain (meinedomain.de) erreichbar? Unterordner Installationen funktionieren nicht. 
* <code>REX_IWCC[forceCache=1 debug=1]</code> zeigt im Frontend nützliche Informationen an.

## Fehler gefunden?
Du hast einen Fehler gefunden oder ein nettes Feature was du gerne hättest? [Lege ein Issue an](https://github.com/FriendsOfREDAXO/iwcc/issues)

## Lizenz
MIT Lizenz, siehe [LICENSE.md](https://github.com/FriendsOfREDAXO/iwcc/blob/master/LICENSE.md)  
[cookie.js](https://github.com/js-cookie/js-cookie): [MIT Lizenz](https://github.com/js-cookie/js-cookie/blob/master/LICENSE)  
[Font Awesome](https://fontawesome.com/v4.7.0/): [SIL Lizenz](https://fontawesome.com/v4.7.0/license/)  
[pretty checkbox](https://github.com/lokesh-coder/pretty-checkbox): [MIT Lizenz](https://github.com/lokesh-coder/pretty-checkbox/blob/master/LICENSE)  

## Autor
**Friends Of REDAXO**  
http://www.redaxo.org  
https://github.com/FriendsOfREDAXO  
**Projekt-Lead**  
[Ingo Winter](https://github.com/IngoWinter)

## Credits
First Release: [Ingo Winter](https://github.com/IngoWinter)  
[Thomas Blum](https://github.com/tbaddade/) wird eine Menge Code aus seinem [Sprog Addon](https://github.com/tbaddade/redaxo_sprog) in Cookie-Gedöns wiederfinden  
[Thomas Skerbis](https://github.com/skerbis) hat unermüdlich getestet und für die Entwicklung gespendet  
[Peter Bickel](https://github.com/polarpixel) hat für die Entwicklung gespendet  
