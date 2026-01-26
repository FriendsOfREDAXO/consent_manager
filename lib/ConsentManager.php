<?php

namespace FriendsOfRedaxo\ConsentManager;

use rex_addon;
use rex_clang;

class ConsentManager
{
    /** @var array<mixed> */
    private static $cache = [];

    private static function initCache(): void
    {
        if (empty(self::$cache)) {
            self::$cache = Cache::read();
            // Ensure cache is populated if empty or version mismatch
            if (empty(self::$cache) || (rex_addon::get('consent_manager')->getVersion() !== (self::$cache['majorVersion'] ?? null))) {
                Cache::forceWrite();
                self::$cache = Cache::read();
            }
        }
    }

    /**
     * Returns the full cache array.
     *
     * @return array<mixed>
     * @api
     */
    public static function getCache(): array
    {
        self::initCache();
        return self::$cache;
    }

    /**
     * Returns the cookie data for a given cookie uid.
     *
     * @param string $uid The cookie uid
     * @param int|null $clangId The clang id (optional, defaults to current clang)
     * @return array<string, mixed>|null returns the cookie data array, or null if not found
     * @api
     */
    public static function getCookieData(string $uid, ?int $clangId = null): ?array
    {
        if (null === $clangId) {
            $clangId = rex_clang::getCurrentId();
        }

        self::initCache();

        return self::$cache['cookies'][$clangId][$uid] ?? null;
    }

    /**
     * Returns all domains from cache.
     *
     * @return array<string, mixed>
     * @api
     */
    public static function getDomains(): array
    {
        self::initCache();
        return self::$cache['domains'] ?? [];
    }

    /**
     * Returns data for a specific domain.
     *
     * @return array<string, mixed>|null
     * @api
     */
    public static function getDomain(string $domain): ?array
    {
        self::initCache();
        return self::$cache['domains'][$domain] ?? null;
    }

    /**
     * Returns cookie groups for a language.
     *
     * @return array<string, mixed>
     * @api
     */
    public static function getCookieGroups(?int $clangId = null): array
    {
        self::initCache();
        if (null === $clangId) {
            $clangId = rex_clang::getCurrentId();
        }
        return self::$cache['cookiegroups'][$clangId] ?? [];
    }

    /**
     * Returns cookies for a language.
     *
     * @return array<string, mixed>
     * @api
     */
    public static function getCookies(?int $clangId = null): array
    {
        self::initCache();
        if (null === $clangId) {
            $clangId = rex_clang::getCurrentId();
        }
        return self::$cache['cookies'][$clangId] ?? [];
    }

    /**
     * Returns texts for a language.
     *
     * @return array<string, string>
     * @api
     */
    public static function getTexts(?int $clangId = null): array
    {
        self::initCache();
        if (null === $clangId) {
            $clangId = rex_clang::getCurrentId();
        }
        return self::$cache['texts'][$clangId] ?? [];
    }

    /**
     * Returns the version from cache.
     *
     * @api
     */
    public static function getVersion(): string
    {
        self::initCache();
        return self::$cache['majorVersion'] ?? '';
    }

    /**
     * Returns the cache log ID.
     *
     * @api
     */
    public static function getCacheLogId(): string
    {
        self::initCache();
        return (string) (self::$cache['cacheLogId'] ?? '');
    }

    /**
     * Returns the placeholder data for a given cookie uid.
     *
     * @param string $uid The cookie uid
     * @param int|null $clangId The clang id (optional, defaults to current clang)
     * @return array{image: string, text: string}|null returns an array with 'image' and 'text' keys, or null if not found
     * @api
     */
    public static function getPlaceholderData(string $uid, ?int $clangId = null): ?array
    {
        $cookie = self::getCookieData($uid, $clangId);

        if ($cookie) {
            return [
                'image' => $cookie['placeholder_image'] ?? '',
                'text' => $cookie['placeholder_text'] ?? '',
            ];
        }

        return null;
    }
}
