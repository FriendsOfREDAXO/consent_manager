<?php

/**
 * Deprecated Classes für Backward Compatibility
 * 
 * Alle alten Klassennamen (ohne Namespace) als Wrapper für die neuen Namespace-Klassen.
 * Diese Datei ersetzt die 18 einzelnen deprecated/*.php Dateien.
 *
 * @deprecated 6.0.0 Wird in Version 6.0 entfernt
 * @see lib/deprecated/Namespace-Guide.md für Migration-Guide
 */

use FriendsOfRedaxo\ConsentManager\Cache;
use FriendsOfRedaxo\ConsentManager\CLang;
use FriendsOfRedaxo\ConsentManager\Config;
use FriendsOfRedaxo\ConsentManager\Frontend;
use FriendsOfRedaxo\ConsentManager\GoogleConsentMode;
use FriendsOfRedaxo\ConsentManager\InlineConsent;
use FriendsOfRedaxo\ConsentManager\JsonSetup;
use FriendsOfRedaxo\ConsentManager\OEmbedParser;
use FriendsOfRedaxo\ConsentManager\RexFormSupport;
use FriendsOfRedaxo\ConsentManager\RexListSupport;
use FriendsOfRedaxo\ConsentManager\Theme;
use FriendsOfRedaxo\ConsentManager\ThumbnailCache;
use FriendsOfRedaxo\ConsentManager\ThumbnailMediaManager;
use FriendsOfRedaxo\ConsentManager\Utility;
use FriendsOfRedaxo\ConsentManager\Api\ConsentManager as ApiConsentManager;
use FriendsOfRedaxo\ConsentManager\Command\LogDelete as CommandLogDelete;
use FriendsOfRedaxo\ConsentManager\Cronjob\LogDelete as CronjobLogDelete;
use FriendsOfRedaxo\ConsentManager\Cronjob\ThumbnailCleanup;

// ============================================================================
// Core Classes
// ============================================================================

/** @deprecated 6.0.0 Use FriendsOfRedaxo\ConsentManager\Cache instead */
class consent_manager_cache extends Cache {}

/** @deprecated 6.0.0 Use FriendsOfRedaxo\ConsentManager\CLang instead */
class consent_manager_clang extends CLang {}

/** @deprecated 6.0.0 Use FriendsOfRedaxo\ConsentManager\Config instead */
class consent_manager_config extends Config {}

/** @deprecated 6.0.0 Use FriendsOfRedaxo\ConsentManager\Frontend instead */
class consent_manager_frontend extends Frontend {}

/** @deprecated 6.0.0 Use FriendsOfRedaxo\ConsentManager\Utility instead */
class consent_manager_util extends Utility {}

// ============================================================================
// Feature Classes
// ============================================================================

/** @deprecated 6.0.0 Use FriendsOfRedaxo\ConsentManager\GoogleConsentMode instead */
class consent_manager_google_consent_mode extends GoogleConsentMode {}

/** @deprecated 6.0.0 Use FriendsOfRedaxo\ConsentManager\InlineConsent instead */
class consent_manager_inline extends InlineConsent {}

/** @deprecated 6.0.0 Use FriendsOfRedaxo\ConsentManager\JsonSetup instead */
class consent_manager_json_setup extends JsonSetup {}

/** @deprecated 6.0.0 Use FriendsOfRedaxo\ConsentManager\OEmbedParser instead */
class consent_manager_oembed_parser extends OEmbedParser {}

/** @deprecated 6.0.0 Use FriendsOfRedaxo\ConsentManager\Theme instead */
class consent_manager_theme extends Theme {}

/** @deprecated 6.0.0 Use FriendsOfRedaxo\ConsentManager\ThumbnailCache instead */
class consent_manager_thumbnail_cache extends ThumbnailCache {}

// ============================================================================
// Backend Support Classes
// ============================================================================

/** @deprecated 6.0.0 Use FriendsOfRedaxo\ConsentManager\RexFormSupport instead */
class consent_manager_rex_form extends RexFormSupport {}

/** @deprecated 6.0.0 Use FriendsOfRedaxo\ConsentManager\RexListSupport instead */
class consent_manager_rex_list extends RexListSupport {}

// ============================================================================
// API Classes
// ============================================================================

/** @deprecated 6.0.0 Use FriendsOfRedaxo\ConsentManager\Api\ConsentManager instead */
class rex_api_consent_manager extends ApiConsentManager {}

// ============================================================================
// Media Manager
// ============================================================================

/** @deprecated 6.0.0 Use FriendsOfRedaxo\ConsentManager\ThumbnailMediaManager instead */
class rex_consent_manager_thumbnail_mediamanager extends ThumbnailMediaManager {}

// ============================================================================
// Console Commands
// ============================================================================

/** @deprecated 6.0.0 Use FriendsOfRedaxo\ConsentManager\Command\LogDelete instead */
class rex_consent_manager_command_log_delete extends CommandLogDelete {}

// ============================================================================
// Cronjobs
// ============================================================================

/** @deprecated 6.0.0 Use FriendsOfRedaxo\ConsentManager\Cronjob\LogDelete instead */
class rex_cronjob_log_delete extends CronjobLogDelete {}

/** @deprecated 6.0.0 Use FriendsOfRedaxo\ConsentManager\Cronjob\ThumbnailCleanup instead */
class rex_cronjob_consent_manager_thumbnail_cleanup extends ThumbnailCleanup {}

// ============================================================================
// Global Functions
// ============================================================================

if (!function_exists('doConsent')) {
    /**
     * Global helper function for inline consent blocking.
     *
     * @deprecated 6.0.0 Use FriendsOfRedaxo\ConsentManager\InlineConsent::doConsent() 
     *                   or FriendsOfRedaxo\ConsentManager\doConsent() instead
     *
     * @param string $serviceKey Service identifier (youtube, vimeo, etc.)
     * @param string $content Original content (iframe, script, etc.)
     * @param array<string, mixed> $options Additional options
     * @return string HTML output with consent blocker
     */
    function doConsent(string $serviceKey, string $content, array $options = []): string
    {
        return InlineConsent::doConsent($serviceKey, $content, $options);
    }
}
