<?php

namespace FriendsOfRedaxo\ConsentManager;

/**
 * Globale Helper-Funktion für einfache Nutzung in Templates.
 * 
 * @param string $serviceKey Service-Schlüssel
 * @param string $content Content (Video-ID, URL, etc.)
 * @param array<string, mixed> $options Zusätzliche Optionen
 * @return string HTML-Output
 */
function doConsent(string $serviceKey, string $content, array $options = []): string
{
    return InlineConsent::doConsent($serviceKey, $content, $options);
}
