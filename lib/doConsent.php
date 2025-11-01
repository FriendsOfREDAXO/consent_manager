<?php

namespace FriendsOfRedaxo\ConsentManager;

/**
 * Globale Helper-Funktion für einfache Nutzung in Templates.
 */
function doConsent($serviceKey, $content, $options = [])
{
    return InlineConsent::doConsent($serviceKey, $content, $options);
}
