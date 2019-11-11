# Cookie-Gedöns

Stellt ein Opt-In Cookie Banner zur Verfügung. Cookies werden in Gruppen zusammengefasst. Der Website Nutzer kann Cookies gruppenweise akzeptieren. Die Cookie Box kann über einen Klick auf ein Element mit der Klasse <code>iwcc-show-box</code> wieder geöffnet werden, zb. ein <code>&lt;a class="iwcc-show-box"&gt;Cookie Einstellungen bearbeiten&lt;/a&gt;</code> im Footer oder der Datenschutzerklärung.

![Screenshot](https://github.com/FriendsOfREDAXO/iwcc/blob/assets/iwcc.jpg?raw=true)

## Installation
Herunterladen und installieren. Nach der Installation steht die Variable <code>REX_IWCC[]</code> zur Verfügung. Diese im <code>head</code> der Seite einbinden.  
Ausserdem werden einige Cookie-/Cookie Gruppen Definitionen für einen leichteren Einstieg angelegt.

## Einrichtung

### Cookie Gruppen
Hier werden die Cookie Gruppen definiert, die der Nutzer akzeptieren kann. Pro Gruppe können Skripte hinterlegt werden, die nach Akzeptieren der Gruppe ausgeführt werden.

![Screenshot](https://github.com/FriendsOfREDAXO/iwcc/blob/assets/iwcc-cookiegroups.jpg?raw=true)

### Cookies
Der nicht löschbare Cookie <code>iwcc</code> speichert die Auswahl des Nutzers. Alle anderen hier definierten Cookies dienen nur der Information des Nutzers.

![Screenshot](https://github.com/FriendsOfREDAXO/iwcc/blob/assets/iwcc-cookies.jpg?raw=true)

### Texte
Die Texte der Cookie Box

### Domains
Das Addon erwartet die Domains im Format <code>meinedomain.de</code>. Im Fragment wird die hier hinterlegte Domain mit <code>$_SERVER['HTTP_HOST']</code> verglichen.

## Mehrsprachigkeit
Beim Anlegen einer neuen Sprache werden existierende Einträge in die neue Sprache kopiert. Bestimmte Felder (zb. Schlüssel, Skripte oder Cookie Namen) können nur in der ersten Sprache editiert werden.

## Design
HTML, CSS und Skripte der Cookie Box liegen im Fragment <code>/redaxo/src/addons/iwcc/fragments/iwcc_box.php</code>. Das Design kann nach Belieben angepasst werden. Dazu das mitgelieferte Stylesheet überschreiben oder komplett entfernen und was eigenes machen. Tipp hierzu: in <code>/redaxo/src/addons/iwcc/scss/</code> findet sich das Stylesheet als SCSS. Wenn man als Admin eingeloggt ist und der Debug-Mode aktiviert ist, wird das Stylesheet nach Änderungen neu generiert.

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

## Credits:
First Release: [Ingo Winter](https://github.com/IngoWinter)  
[Thomas Blum](https://github.com/tbaddade/) wird eine Menge Code aus seinem [Sprog Addon](https://github.com/tbaddade/redaxo_sprog) in Cookie-Gedöns wiederfinden  
 
