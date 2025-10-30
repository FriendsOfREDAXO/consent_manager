<?php

namespace FriendsOfRedaxo\ConsentManager;

use Exception;
use rex;
use rex_addon;
use rex_i18n;
use rex_path;
use rex_sql;

use function in_array;
use function is_array;

use const JSON_ERROR_NONE;
use const PATHINFO_FILENAME;

/**
 * Consent Manager JSON Setup Handler.
 *
 * Handles import/export and setup functionality for JSON-based configurations
 * Replaces SQL-based setup for better maintainability and community contributions
 */
class JsonSetup
{
    /**
     * Import JSON setup configuration.
     * 
     * @api
     * @param string $jsonFile Path to JSON setup file
     * @param bool $clearExisting Clear existing data before import
     * @param string $mode Import mode: 'replace' (default), 'update' (only add new)
     * @return array Result array with success status and message
     */
    public static function importSetup(string $jsonFile, bool $clearExisting = true, string $mode = 'replace'): array
    {
        try {
            if (!file_exists($jsonFile)) {
                return ['success' => false, 'message' => 'JSON setup file not found: ' . $jsonFile];
            }

            $jsonContent = file_get_contents($jsonFile);
            if (false === $jsonContent) {
                return ['success' => false, 'message' => 'Could not read JSON setup file'];
            }

            $setupData = json_decode($jsonContent, true);
            if (JSON_ERROR_NONE !== json_last_error()) {
                return ['success' => false, 'message' => 'Invalid JSON format: ' . json_last_error_msg()];
            }

            if (!is_array($setupData)) {
                return ['success' => false, 'message' => 'JSON file does not contain valid setup data'];
            }

            // Begin transaction
            $sql = rex_sql::factory();
            $sql->setQuery('START TRANSACTION');

            try {
                // Handle different import modes
                if ('replace' === $mode && $clearExisting) {
                    self::clearExistingData();
                }

                // Import data with mode-specific logic
                self::importCookieGroups($setupData['cookiegroups'] ?? [], $mode);
                self::importCookies($setupData['cookies'] ?? [], $mode);
                self::importTexts($setupData['texts'] ?? [], $mode);
                self::importDomains($setupData['domains'] ?? [], $mode);

                // Commit transaction
                $sql->setQuery('COMMIT');

                // Force cache rebuild
                if (class_exists(Cache::class)) {
                    Cache::forceWrite();
                }

                return [
                    'success' => true,
                    'message' => 'JSON setup imported successfully',
                    'meta' => $setupData['meta'] ?? [],
                ];
            } catch (Exception $e) {
                // Rollback on error
                $sql->setQuery('ROLLBACK');
                throw $e;
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Export current configuration as JSON.
     * 
     * @api
     */
    public static function exportSetup(bool $includeMetadata = true): array
    {
        $addon = rex_addon::get('consent_manager');

        $exportData = [];

        if ($includeMetadata) {
            $exportData['meta'] = [
                'export_version' => '1.1',
                'export_date' => date('Y-m-d H:i:s'),
                'addon_version' => $addon->getVersion(),
                'language' => rex_i18n::getLocale(),
            ];
        }

        // Export Cookie Groups
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . rex::getTable('consent_manager_cookiegroup') . ' ORDER BY prio, id');
        $cookiegroups = [];
        while ($sql->hasNext()) {
            $cookiegroups[] = $sql->getRow();
            $sql->next();
        }
        $exportData['cookiegroups'] = $cookiegroups;

        // Export Cookies/Services
        $sql->setQuery('SELECT * FROM ' . rex::getTable('consent_manager_cookie') . ' ORDER BY id');
        $cookies = [];
        while ($sql->hasNext()) {
            $cookies[] = $sql->getRow();
            $sql->next();
        }
        $exportData['cookies'] = $cookies;

        // Export Texts
        $sql->setQuery('SELECT * FROM ' . rex::getTable('consent_manager_text') . ' ORDER BY clang_id, id');
        $texts = [];
        while ($sql->hasNext()) {
            $texts[] = $sql->getRow();
            $sql->next();
        }
        $exportData['texts'] = $texts;

        // Export Domains
        $sql->setQuery('SELECT * FROM ' . rex::getTable('consent_manager_domain') . ' ORDER BY id');
        $domains = [];
        while ($sql->hasNext()) {
            $domains[] = $sql->getRow();
            $sql->next();
        }
        $exportData['domains'] = $domains;

        return $exportData;
    }

    /**
     * Get available setup templates.
     * 
     * @api
     */
    public static function getAvailableSetups(): array
    {
        $setups = [];
        $setupDir = rex_path::addon('consent_manager') . 'setup/';

        if (!is_dir($setupDir)) {
            return $setups;
        }

        $files = glob($setupDir . '*.json');
        foreach ($files as $file) {
            $filename = basename($file);

            // Skip certain files
            if (in_array($filename, ['contribution_template.json', 'example_export.json'], true)) {
                continue;
            }

            $jsonContent = file_get_contents($file);
            if (false === $jsonContent) {
                continue;
            }

            $setupData = json_decode($jsonContent, true);
            if (JSON_ERROR_NONE !== json_last_error() || !is_array($setupData)) {
                continue;
            }

            $setup = [
                'file' => $filename,
                'name' => $filename,
                'meta' => $setupData['meta'] ?? [],
            ];

            // Generate a nice name from the filename or meta
            if (isset($setupData['meta']['description'])) {
                $setup['name'] = pathinfo($filename, PATHINFO_FILENAME);
            } else {
                $setup['name'] = str_replace(['_', '-'], ' ', pathinfo($filename, PATHINFO_FILENAME));
                $setup['name'] = ucfirst($setup['name']);
            }

            $setups[] = $setup;
        }

        return $setups;
    }

    /**
     * Clear all existing data from consent manager tables.
     */
    private static function clearExistingData(): void
    {
        $sql = rex_sql::factory();

        $tables = [
            'consent_manager_cookie',
            'consent_manager_cookiegroup',
            'consent_manager_text',
            'consent_manager_domain',
        ];

        foreach ($tables as $table) {
            $sql->setQuery('DELETE FROM ' . rex::getTable($table));
        }
    }

    /**
     * Import cookie groups with mode support.
     */
    private static function importCookieGroups(array $cookiegroups, string $mode = 'replace'): void
    {
        foreach ($cookiegroups as $group) {
            if (!isset($group['uid'])) {
                continue;
            }

            // Check if group already exists
            $existingGroup = self::findExistingCookieGroup($group['uid']);

            // Decide action based on mode
            if ('update' === $mode && is_array($existingGroup)) {
                continue; // Skip existing groups in update mode
            }
            // Adjust ID if it conflicts in update mode
            if ('update' === $mode && isset($group['id']) && self::idExistsInTable('consent_manager_cookiegroup', $group['id'])) {
                $group['id'] = self::getNextAvailableId('consent_manager_cookiegroup');
            }
            // Insert new group (replace mode or new group)
            self::insertCookieGroup($group);
        }
    }

    /**
     * Import cookies/services with mode support.
     */
    private static function importCookies(array $cookies, string $mode = 'replace'): void
    {
        foreach ($cookies as $cookie) {
            if (!isset($cookie['uid'])) {
                continue;
            }

            // Check if cookie already exists
            $existingCookie = self::findExistingCookie($cookie['uid']);

            // Decide action based on mode
            if ('update' === $mode && is_array($existingCookie)) {
                continue; // Skip existing cookies in update mode
            }
            // Adjust ID if it conflicts in update mode
            if ('update' === $mode && isset($cookie['id']) && self::idExistsInTable('consent_manager_cookie', $cookie['id'])) {
                $cookie['id'] = self::getNextAvailableId('consent_manager_cookie');
            }
            // Insert new cookie (replace mode or new cookie)
            self::insertCookie($cookie);
        }
    }

    /**
     * Import texts with mode support.
     */
    private static function importTexts(array $texts, string $mode = 'replace'): void
    {
        foreach ($texts as $text) {
            if (!isset($text['uid'])) {
                continue;
            }

            // Check if text already exists
            $existingText = self::findExistingText($text['uid'], $text['clang_id'] ?? 1);

            // Decide action based on mode
            if ('update' === $mode && is_array($existingText)) {
                continue; // Skip existing texts in update mode
            }
            // Adjust ID if it conflicts in update mode
            if ('update' === $mode && isset($text['id']) && self::idExistsInTable('consent_manager_text', $text['id'])) {
                $text['id'] = self::getNextAvailableId('consent_manager_text');
            }
            // Insert new text (replace mode or new text)
            self::insertText($text);
        }
    }

    /**
     * Import domains with mode support.
     */
    private static function importDomains(array $domains, string $mode = 'replace'): void
    {
        foreach ($domains as $domain) {
            if (!isset($domain['uid'])) {
                continue;
            } // Domain table uses 'uid' not 'domain'

            // Check if domain already exists
            $existingDomain = self::findExistingDomain($domain['uid']);

            // Decide action based on mode
            if ('update' === $mode && is_array($existingDomain)) {
                continue; // Skip existing domains in update mode
            }
            // Adjust ID if it conflicts in update mode
            if ('update' === $mode && isset($domain['id']) && self::idExistsInTable('consent_manager_domain', $domain['id'])) {
                $domain['id'] = self::getNextAvailableId('consent_manager_domain');
            }
            // Insert new domain (replace mode or new domain)
            self::insertDomain($domain);
        }
    }

    // Helper methods to find existing records
    private static function findExistingCookieGroup(string $uid): ?array
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . rex::getTable('consent_manager_cookiegroup') . ' WHERE uid = ?', [$uid]);
        return $sql->getRows() > 0 ? $sql->getRow() : null;
    }

    private static function findExistingCookie(string $uid): ?array
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . rex::getTable('consent_manager_cookie') . ' WHERE uid = ?', [$uid]);
        return $sql->getRows() > 0 ? $sql->getRow() : null;
    }

    private static function findExistingText(string $uid, int $clangId): ?array
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . rex::getTable('consent_manager_text') . ' WHERE uid = ? AND clang_id = ?', [$uid, $clangId]);
        return $sql->getRows() > 0 ? $sql->getRow() : null;
    }

    private static function findExistingDomain(string $uid): ?array
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . rex::getTable('consent_manager_domain') . ' WHERE uid = ?', [$uid]);
        return $sql->getRows() > 0 ? $sql->getRow() : null;
    }

    // Insert methods for new records
    private static function insertCookieGroup(array $group): void
    {
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('consent_manager_cookiegroup'));

        $now = date('Y-m-d H:i:s');

        $fieldMapping = [
            'id' => 'id',
            'clang_id' => 'clang_id',
            'domain' => 'domain',
            'uid' => 'uid',
            'prio' => 'prio',
            'required' => 'required',
            'name' => 'name',
            'description' => 'description',
            'cookie' => 'cookie',
            'script' => 'script',
        ];

        foreach ($fieldMapping as $jsonField => $dbField) {
            if (isset($group[$jsonField])) {
                $sql->setValue($dbField, $group[$jsonField]);
            }
        }

        $createDate = (isset($group['createdate']) && '0000-00-00 00:00:00' !== $group['createdate']) ? $group['createdate'] : $now;
        $updateDate = (isset($group['updatedate']) && '0000-00-00 00:00:00' !== $group['updatedate']) ? $group['updatedate'] : $now;

        $sql->setValue('createdate', $createDate);
        $sql->setValue('updatedate', $updateDate);

        $sql->insert();
    }

    // Helper methods
    private static function idExistsInTable(string $table, int $id): bool
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT COUNT(*) as count FROM ' . rex::getTable($table) . ' WHERE id = ?', [$id]);
        return $sql->getValue('count') > 0;
    }

    private static function getNextAvailableId(string $table): int
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT MAX(id) as max_id FROM ' . rex::getTable($table));
        $maxId = (int) ($sql->getValue('max_id') ?? 0);
        return $maxId + 1;
    }

    private static function insertCookie(array $cookie): void
    {
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('consent_manager_cookie'));

        $now = date('Y-m-d H:i:s');

        $fieldMapping = [
            'id' => 'id',
            'clang_id' => 'clang_id',
            'uid' => 'uid',
            'service_name' => 'service_name',
            'provider' => 'provider',
            'provider_link_privacy' => 'provider_link_privacy',
            'definition' => 'definition',
            'script' => 'script',
            'script_unselect' => 'script_unselect',
            'placeholder_text' => 'placeholder_text',
            'placeholder_image' => 'placeholder_image',
        ];

        foreach ($fieldMapping as $jsonField => $dbField) {
            if (isset($cookie[$jsonField])) {
                $sql->setValue($dbField, $cookie[$jsonField]);
            }
        }

        $createDate = (isset($cookie['createdate']) && '0000-00-00 00:00:00' !== $cookie['createdate']) ? $cookie['createdate'] : $now;
        $updateDate = (isset($cookie['updatedate']) && '0000-00-00 00:00:00' !== $cookie['updatedate']) ? $cookie['updatedate'] : $now;

        $sql->setValue('createdate', $createDate);
        $sql->setValue('updatedate', $updateDate);

        $sql->insert();
    }

    private static function insertText(array $text): void
    {
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('consent_manager_text'));

        $now = date('Y-m-d H:i:s');

        $fieldMapping = [
            'id' => 'id',
            'clang_id' => 'clang_id',
            'uid' => 'uid',
            'text' => 'text',
        ];

        foreach ($fieldMapping as $jsonField => $dbField) {
            if (isset($text[$jsonField])) {
                $sql->setValue($dbField, $text[$jsonField]);
            }
        }

        $createDate = (isset($text['createdate']) && '0000-00-00 00:00:00' !== $text['createdate']) ? $text['createdate'] : $now;
        $updateDate = (isset($text['updatedate']) && '0000-00-00 00:00:00' !== $text['updatedate']) ? $text['updatedate'] : $now;

        $sql->setValue('createdate', $createDate);
        $sql->setValue('updatedate', $updateDate);

        $sql->insert();
    }

    private static function insertDomain(array $domain): void
    {
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('consent_manager_domain'));

        $now = date('Y-m-d H:i:s');

        $fieldMapping = [
            'id' => 'id',
            'uid' => 'uid',
            'privacy_policy' => 'privacy_policy',
            'legal_notice' => 'legal_notice',
            'google_consent_mode_enabled' => 'google_consent_mode_enabled',
            'google_consent_mode_config' => 'google_consent_mode_config',
            'google_consent_mode_debug' => 'google_consent_mode_debug',
        ];

        foreach ($fieldMapping as $jsonField => $dbField) {
            if (isset($domain[$jsonField])) {
                // Domain in Kleinbuchstaben normalisieren beim Import
                $value = $domain[$jsonField];
                if ('uid' === $dbField) {
                    $value = strtolower($value);
                }
                $sql->setValue($dbField, $value);
            }
        }

        $createDate = (isset($domain['createdate']) && '0000-00-00 00:00:00' !== $domain['createdate']) ? $domain['createdate'] : $now;
        $updateDate = (isset($domain['updatedate']) && '0000-00-00 00:00:00' !== $domain['updatedate']) ? $domain['updatedate'] : $now;

        $sql->setValue('createdate', $createDate);
        $sql->setValue('updatedate', $updateDate);

        $sql->insert();
    }
}
