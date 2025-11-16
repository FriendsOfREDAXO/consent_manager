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
    /**
     * @api
     */
    public function execute(): bool
    {
        ThumbnailCache::cleanupCache();
        return true;
    }

    /**
     * @api
     */
    public function getTypeName(): string
    {
        /* TODO: Text nach *.lang verlagern (rex_18n) */
        return 'Consent Manager Thumbnail Cache bereinigen';
    }

    /**
     * @api
     * @return list<array<string, mixed>>
     */
    public function getParamFields(): array
    {
        return [];
    }
}
