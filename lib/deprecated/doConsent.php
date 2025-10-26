<?php

use FriendsOfRedaxo\ConsentManager\InlineConsent;

/**
 * Globale Helper-Funktion für einfache Nutzung in Templates.
 * 
 * deprecated ab Version 5.0.0, nutze stattdessen FriendsOfRedaxo\ConsentManager\InlineConsent::doConsent() oder FriendsOfRedaxo\ConsentManager\doConsent().
 */
function doConsent($serviceKey, $content, $options = [])
{
    return InlineConsent::doConsent($serviceKey, $content, $options);
}
