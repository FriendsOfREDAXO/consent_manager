<?php

/**
 * Validiert Cookie-Namen nach RFC 6265
 *
 * @package FriendsOfRedaxo\ConsentManager
 */

namespace FriendsOfRedaxo\ConsentManager;

/**
 * Cookie-Namen Validator nach RFC 6265
 *
 * RFC 6265 Section 4.1.1 definiert erlaubte Zeichen für Cookie-Namen:
 * cookie-name = token
 * token = 1*<any CHAR except CTLs or separators>
 * separators = "(" | ")" | "<" | ">" | "@" | "," | ";" | ":" | "\" | <"> | "/" | "[" | "]" | "?" | "=" | "{" | "}" | SP | HT
 *
 * Erlaubt sind also: Alphanumerisch (a-z, A-Z, 0-9) und die Zeichen: ! # $ % & ' * + - . ^ _ ` | ~
 */
class CookieNameValidator
{
    /**
     * RFC 6265 konforme Cookie-Namen Validierung
     *
     * @param string $name Der zu validierende Cookie-Name
     * @return bool True wenn valide, false wenn nicht
     */
    public static function isValid(string $name): bool
    {
        // Leer ist nicht erlaubt
        if ('' === $name) {
            return false;
        }

        // Maximale Länge (pragmatischer Limit, nicht in RFC definiert aber Browser haben Limits)
        if (strlen($name) > 255) {
            return false;
        }

        // RFC 6265: Cookie-Namen dürfen nur "token" Zeichen enthalten
        // Erlaubt: Alphanumerisch + ! # $ % & ' * + - . ^ _ ` | ~
        // Verboten: Steuerzeichen, Leerzeichen, Separatoren ( ) < > @ , ; : \ " / [ ] ? = { }
        //
        // Regex erklärt:
        // ^ = Start
        // [a-zA-Z0-9!#\$%&'\*\+\-\.\^_`\|~]+ = 1 oder mehr erlaubte Zeichen
        // $ = Ende
        //
        // Wichtig: Keine Leerzeichen, keine Sonderzeichen wie ()<>@,;:\"/[]?={}
        return 1 === preg_match('/^[a-zA-Z0-9!#\$%&\'\*\+\-\.\^_`\|~]+$/', $name);
    }

    /**
     * Gibt eine Liste nicht erlaubter Zeichen zurück (falls vorhanden)
     *
     * @param string $name Der zu prüfende Cookie-Name
     * @return array<int, string> Liste der gefundenen nicht-erlaubten Zeichen
     */
    public static function getInvalidChars(string $name): array
    {
        if ('' === $name) {
            return [];
        }

        // Alle Zeichen finden, die NICHT in der erlaubten Menge sind
        $matches = [];
        preg_match_all('/[^a-zA-Z0-9!#\$%&\'\*\+\-\.\^_`\|~]/', $name, $matches);

        // Unique + sortiert zurückgeben
        return array_values(array_unique($matches[0]));
    }

    /**
     * Sanitize einen Cookie-Namen (entfernt ungültige Zeichen)
     *
     * @param string $name Der zu bereinigende Cookie-Name
     * @return string Der bereinigte Name
     */
    public static function sanitize(string $name): string
    {
        // Alle nicht-erlaubten Zeichen entfernen
        $sanitized = (string) preg_replace('/[^a-zA-Z0-9!#\$%&\'\*\+\-\.\^_`\|~]/', '', $name);

        // Maximal 255 Zeichen
        return substr($sanitized, 0, 255);
    }

    /**
     * Gibt eine menschenlesbare Fehlermeldung zurück
     *
     * @param string $name Der ungültige Cookie-Name
     * @return string Fehlermeldung
     */
    public static function getErrorMessage(string $name): string
    {
        if ('' === $name) {
            return 'Cookie-Name darf nicht leer sein.';
        }

        if (strlen($name) > 255) {
            return 'Cookie-Name ist zu lang (maximal 255 Zeichen).';
        }

        $invalidChars = self::getInvalidChars($name);
        if ([] !== $invalidChars) {
            $charList = implode(', ', array_map(static fn($char) => "'{$char}'", $invalidChars));
            return "Cookie-Name enthält ungültige Zeichen: {$charList}. " .
                   'Erlaubt sind: Buchstaben, Zahlen und ! # $ % & \' * + - . ^ _ ` | ~';
        }

        return 'Ungültiger Cookie-Name.';
    }
}
