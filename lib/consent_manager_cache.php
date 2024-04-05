<?php

class consent_manager_cache
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
     * @param rex_extension_point<object> $ep
     * @return bool
     * @api
     */
    public static function write(rex_extension_point $ep)
    {
        $form = $ep->getParams()['form'];
        if (!in_array($form->getTableName(), consent_manager_config::getTables(), true)) {
            return true;
        }
        $cache = new self();
        $cache->writeCache();
        touch(rex_addon::get('consent_manager')->getAssetsPath('consent_manager_frontend.js'));
        return true;
    }

    /**
     * @return void
     */
    private function writeCache()
    {
        $addon = rex_addon::get('consent_manager');
        $configFile = $addon->getDataPath('config.json');
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

    /**
     * @return void
     */
    private function fetchData()
    {
        $db = rex_sql::factory();
        $db->setTable(rex::getTable('consent_manager_domain'));
        $db->select('*');
        $domains = $db->getArray();
        foreach ($domains as $v) {
            $this->domains[$v['id']] = $v;
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

    /**
     * @param int $clangId
     * @return void
     */
    private function prepareCookie($clangId)
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
                $this->cookies[$clangId][$uid] = $cookie; /** @phpstan-ignore-line */
            }
        }
    }

    /**
     * @param int $clangId
     * @return void
     */
    private function prepareCookieGroups($clangId)
    {
        if (isset($this->cookiegroups[$clangId])) {
            foreach ((array) $this->cookiegroups[$clangId] as $uid => $cookiegroup) {
                $cookie_uids = [];
                $cookiegroup = (array) $cookiegroup;
                if (is_string($cookiegroup['cookie'])) {
                    foreach (array_filter(explode('|', $cookiegroup['cookie'])) as $cookieUid) {
                        if (isset($this->cookies[$clangId][$cookieUid])) { /** @phpstan-ignore-line */
                            $cookie_uids[] = $cookieUid;
                        }
                    }
                }
                if ('|1|' === $cookiegroup['required']) {
                    $cookie_uids[] = 'consent_manager';
                }
                $this->cookiegroups[$clangId][$uid]['cookie_uids'] = array_merge(array_filter(array_unique($cookie_uids))); /** @phpstan-ignore-line */
                $domainIds = array_filter(explode('|', $cookiegroup['domain']));
                foreach ($domainIds as $domainId) {
                    if (isset($this->domains[$domainId])) {
                        $this->domains[$domainId]['cookiegroups'][] = $uid; /** @phpstan-ignore-line */
                    }
                }
            }
        }
    }

    /**
     * @param int $clangId
     * @return void
     */
    private function setConfig($clangId)
    {
        if (isset($this->cookiegroups[$clangId])) {
            foreach ((array) $this->cookiegroups[$clangId] as $v) {
                $this->config['cookiegroups'][$v['clang_id']][$v['uid']] = $v; /** @phpstan-ignore-line */
            }
        }
        if (isset($this->cookies[$clangId])) {
            foreach ((array) $this->cookies[$clangId] as $v) {
                $this->config['cookies'][$v['clang_id']][$v['uid']] = $v; /** @phpstan-ignore-line */
            }
        }
        foreach ($this->domains as $v) {
            $this->config['domains'][$v['uid']] = $v; /** @phpstan-ignore-line */
        }
        if (isset($this->texts[$clangId])) {
            $this->config['texts'][$clangId] = $this->texts[$clangId]; /** @phpstan-ignore-line */
        }
        $this->config['majorVersion'] = rex_addon::get('consent_manager')->getVersion();
    }

    /**
     * @return void
     */
    public static function forceWrite()
    {
        $cache = new self();
        $cache->writeCache();
    }

    /**
     * @return array<int, string>
     */
    public static function read()
    {
        $addon = rex_addon::get('consent_manager');
        $configFile = $addon->getDataPath('config.json');

        return rex_file::getCache($configFile);
    }
}
