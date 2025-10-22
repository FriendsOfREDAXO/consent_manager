<?php

use FriendsOfRedaxo\ConsentManager\ThumbnailCache;

/**
 * Cronjob für Consent Manager Thumbnail Cache Bereinigung.
 */
class rex_cronjob_consent_manager_thumbnail_cleanup extends rex_cronjob
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
