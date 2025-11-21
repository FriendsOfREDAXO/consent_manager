<?php

use FriendsOfRedaxo\ConsentManager\InlineConsent;

/**
 * Globale Helper-Funktion für einfache Nutzung in Templates.
 *
 * @deprecated ab Version 5.0.0, nutze stattdessen FriendsOfRedaxo\ConsentManager\InlineConsent::doConsent() oder FriendsOfRedaxo\ConsentManager\doConsent().
 * @param string $serviceKey
 * @param string $content
 * @param array<string, mixed> $options
 * @return string
 */
function doConsent(string $serviceKey, string $content, array $options = []): string
{
    return InlineConsent::doConsent($serviceKey, $content, $options);
}
