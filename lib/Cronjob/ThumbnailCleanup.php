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
    public function execute(): bool
    {
        ThumbnailCache::cleanupCache();
        return true;
    }

    public function getTypeName(): string
    {
        return 'Consent Manager Thumbnail Cache bereinigen';
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getParamFields(): array
    {
        return [];
    }
}
