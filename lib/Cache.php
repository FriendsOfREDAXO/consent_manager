<?php

namespace FriendsOfRedaxo\ConsentManager;

use rex;
use rex_addon;
use rex_clang;
use rex_extension_point;
use rex_file;
use rex_form;
use rex_i18n;
use rex_logger;
use rex_path;
use rex_sql;
use rex_string;

use function in_array;
use function is_string;

class Cache
{
    /** @var array<mixed> */
    private $domains = [];
    /** @var array<mixed> */
    private $cookiegroups = [];
    /** @var array<mixed> */
    private $cookies = [];
    /** @var array<mixed> */
    private $texts = [];
    /** @var array<mixed> */
    private $config = [
        'domains' => [],
        'cookiegroups' => [],
        'cookies' => [],
        'texts' => [],
        'cacheLogId' => 0,
    ];

    /**
     * @api
     * @param rex_extension_point<bool> $ep
     */
    public static function write(rex_extension_point $ep): bool
    {
        /** @var rex_form $form */
        $form = $ep->getParam('form');
        if (in_array($form->getTableName(), Config::getTables(), true)) {
            $cache = new self();
            $cache->writeCache();
            touch(rex_addon::get('consent_manager')->getAssetsPath('consent_manager_frontend.js'));
        }
        return true;
    }

    private function writeCache(): void
    {
        $configFile = rex_path::addonData('consent_manager', 'config.json');
        $this->fetchData();
        foreach (rex_clang::getAllIds() as $clangId) {
            $this->prepareCookie($clangId);
            $this->prepareCookieGroups($clangId);
            $this->setConfig($clangId);
        }
        $db = rex_sql::factory();
        $db->setTable(rex::getTable('consent_manager_cache_log'));
        $db->setValue('consent', json_encode($this->config));
        $user = null !== rex::getUser() ? rex::getUser()->getLogin() : 'forceCache';
        $db->setValue('createuser', $user);
        $db->setRawValue('createdate', 'NOW()');
        $db->insert();
        $this->config['cacheLogId'] = $db->getLastId();
        if (!rex_file::putCache($configFile, $this->config)) {
            rex_logger::logError(1, rex_i18n::msg('consent_manager_cache_write_failed'), __FILE__, __LINE__);
        }
    }

    private function fetchData(): void
    {
        $db = rex_sql::factory();
        $db->setTable(rex::getTable('consent_manager_domain'));
        $db->select('*');
        $domains = $db->getArray();
        foreach ($domains as $v) {
            $domainId = (int) ($v['id'] ?? 0);
            if ($domainId > 0) {
                // Sicherstellen dass theme-Wert übernommen wird
                $this->domains[$domainId] = $v;
            }
        }

        $db = rex_sql::factory();
        $db->setTable(rex::getTable('consent_manager_cookiegroup'));
        $db->setWhere('1=1 ORDER BY prio ASC');
        $db->select('*');
        $cookiegroups = $db->getArray();
        foreach ($cookiegroups as $v) {
            $this->cookiegroups[$v['clang_id']][$v['uid']] = $v; /** @phpstan-ignore-line */
        }

        $db = rex_sql::factory();
        $db->setTable(rex::getTable('consent_manager_cookie'));
        $db->select('*');
        foreach ($db->getArray() as $v) {
            $this->cookies[$v['clang_id']][$v['uid']] = $v; /** @phpstan-ignore-line */
        }
        $db = rex_sql::factory();
        $db->setTable(rex::getTable('consent_manager_text'));
        $db->select('*');
        foreach ($db->getArray() as $v) {
            $this->texts[$v['clang_id']][$v['uid']] = $v['text']; /** @phpstan-ignore-line */
        }
    }

    private function prepareCookie(int $clangId): void
    {
        if (isset($this->cookies[$clangId])) {
            foreach ((array) $this->cookies[$clangId] as $uid => $cookie) {
                $defs = [];
                $cookie = (array) $cookie;
                if (is_string($cookie['definition'])) {
                    foreach (rex_string::yamlDecode($cookie['definition']) as $k => $v) {
                        $defs[$k]['cookie_name'] = $v['name'] ?? '';
                        $defs[$k]['cookie_lifetime'] = $v['time'] ?? '';
                        $defs[$k]['description'] = $v['desc'] ?? '';
                    }
                }
                $cookie['definition'] = $defs;
                $cookie['script'] = base64_encode($cookie['script']);
                $cookie['script_unselect'] = base64_encode($cookie['script_unselect']);
                $this->cookies[$clangId][$uid] = $cookie;
            }
        }
    }

    private function prepareCookieGroups(int $clangId): void
    {
        if (isset($this->cookiegroups[$clangId])) {
            foreach ((array) $this->cookiegroups[$clangId] as $uid => $cookiegroup) {
                $cookie_uids = [];
                $cookiegroup = (array) $cookiegroup;
                if (is_string($cookiegroup['cookie'])) {
                    foreach (array_filter(explode('|', $cookiegroup['cookie']), strlen(...)) as $cookieUid) { /** @phpstan-ignore-line */
                        if (isset($this->cookies[$clangId][$cookieUid])) {
                            $cookie_uids[] = $cookieUid;
                        }
                    }
                }
                if ('|1|' === $cookiegroup['required']) {
                    $cookie_uids[] = 'consent_manager';
                }
                $this->cookiegroups[$clangId][$uid]['cookie_uids'] = array_merge(array_filter(array_unique($cookie_uids))); /** @phpstan-ignore-line */
                $domainIds = array_filter(explode('|', $cookiegroup['domain']), strlen(...)); // @phpstan-ignore-line
                foreach ($domainIds as $domainId) {
                    if (isset($this->domains[$domainId])) {
                        $this->domains[$domainId]['cookiegroups'][] = $uid;
                    }
                }
            }
        }
    }

    private function setConfig(int $clangId): void
    {
        if (isset($this->cookiegroups[$clangId])) {
            foreach ((array) $this->cookiegroups[$clangId] as $v) {
                $this->config['cookiegroups'][$v['clang_id']][$v['uid']] = $v;
            }
        }
        if (isset($this->cookies[$clangId])) {
            foreach ((array) $this->cookies[$clangId] as $v) {
                $this->config['cookies'][$v['clang_id']][$v['uid']] = $v;
            }
        }
        foreach ($this->domains as $v) {
            $this->config['domains'][$v['uid']] = $v;
        }
        if (isset($this->texts[$clangId])) {
            $this->config['texts'][$clangId] = $this->texts[$clangId];
        }
        $this->config['majorVersion'] = rex_addon::get('consent_manager')->getVersion();
    }

    /**
     * @api
     */
    public static function forceWrite(): void
    {
        $cache = new self();
        $cache->writeCache();
    }

    /**
     * @api
     * @return array<int|string, mixed>
     */
    public static function read(): array
    {
        $configFile = rex_path::addonData('consent_manager', 'config.json');

        return rex_file::getCache($configFile, []);
    }
}
