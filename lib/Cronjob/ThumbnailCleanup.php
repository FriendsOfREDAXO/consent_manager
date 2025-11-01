<?php

namespace FriendsOfRedaxo\ConsentManager\Cronjob;

use Exception;
use FriendsOfRedaxo\ConsentManager\ThumbnailCache;
use rex_cronjob;

/**
 * Cronjob für Consent Manager Thumbnail Cache Bereinigung.
 */
class ThumbnailCleanup extends rex_cronjob
{
    public function execute()
    {
        try {
            // Cache bereinigen (Dateien älter als 30 Tage löschen)
            ThumbnailCache::cleanupCache(30 * 24 * 60 * 60);

            $this->setMessage('Consent Manager Thumbnail Cache wurde bereinigt');
            return true;
        } catch (Exception $e) {
            $this->setMessage('Fehler beim Bereinigen des Thumbnail Cache: ' . $e->getMessage());
            return false;
        }
    }

    public function getTypeName()
    {
        return 'Consent Manager Thumbnail Cache bereinigen';
    }

    public function getParamFields()
    {
        return [];
    }
}
