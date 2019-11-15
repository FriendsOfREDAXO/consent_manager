<?php

class iwcc_cache
{

    private $domains = [];
    private $cookiegroups = [];
    private $cookies = [];
    private $texts = [];
    private $config = [
        'domains' => [],
        'cookiegroups' => [],
        'texts' => [],
    ];

    public static function write(rex_extension_point $ep)
    {
        $form = $ep->getParams()['form'];
        if (!in_array($form->getTableName(), iwcc_config::getTables()))
        {
            return true;
        }
        $cache = new self();
        $cache->writeCache();
    }

    private function writeCache()
    {
        $addon = rex_addon::get('iwcc');
        $configFile = $addon->getDataPath('config.json');
        $this->fetchData();
        $this->prepareData();
        $this->setConfig();
        if (!rex_file::putCache($configFile, $this->config))
        {
            rex_logger::logError(1, rex_i18n::msg('iwcc_cache_write_failed'), __FILE__, __LINE__);
        }
    }

    private function fetchData()
    {
        $db = rex_sql::factory();
        $db->setTable(rex::getTable('iwcc_domain'));
        $db->select('id,uid,privacy_policy,legal_notice');
        $domains = $db->getArray();
        foreach ($domains as $v)
        {
            $this->domains[$v['id']] = $v;
        }

        $db = rex_sql::factory();
        $db->setTable(rex::getTable('iwcc_cookiegroup'));
        $db->setWhere('1=1 ORDER BY prio ASC');
        $db->select('pid,id,clang_id,domain,uid,required,name,description,cookie,script');
        $cookiegroups = $db->getArray();
        foreach ($cookiegroups as $v)
        {
            $this->cookiegroups[$v['pid']] = $v;
        }

        $db = rex_sql::factory();
        $db->setTable(rex::getTable('iwcc_cookie'));
        $db->select('clang_id,uid,service_name,provider,provider_link_privacy,definition');
        foreach ($db->getArray() as $v)
        {
            $this->cookies[$v['uid']][] = $v;
        }

        $db = rex_sql::factory();
        $db->setTable(rex::getTable('iwcc_text'));
        $db->select('id,clang_id,uid,text');
        foreach ($db->getArray() as $v)
        {
            $this->texts[$v['clang_id']][$v['uid']] = $v['text'];
        }
    }

    private function prepareData()
    {
        $iwccCookie = [];
        foreach ($this->cookies as $uid => $cookies)
        {
            if ($uid == 'iwcc')
            {
                foreach ($cookies as $yamlCookie)
                {
                    $cookieDefinitions = rex_string::yamlDecode($yamlCookie['definition']);
                    unset($yamlCookie['definition']);
                    foreach ($cookieDefinitions as $k => $v)
                    {
                        $cookies[$k] = $yamlCookie;
                        $cookies[$k]['cookie_name'] = 'iwcc';
                        $cookies[$k]['cookie_lifetime'] = $v['time'];
                        $cookies[$k]['description'] = $v['desc'];
                    }
                    $iwccCookie[$yamlCookie['clang_id']] = $cookies[$k];
                }
            }
        }
        foreach ($this->cookiegroups as $k => $cookiegroup)
        {
            $cookies = [];
            $cookie_uids = [];
            if ($cookiegroup['required'] == '|1|')
            {
                $cookies[] = $iwccCookie[$cookiegroup['clang_id']];
                $cookie_uids[] = 'iwcc';
            }
            $cookies = array_merge($cookies, $this->getCookiesForGroup($k));
            foreach ($cookies as $v)
            {
                $cookie_uids[] = $v['uid'];
            }
            if ($cookies)
            {
                $this->cookiegroups[$k]['cookie'] = $cookies;
                $this->cookiegroups[$k]['cookie_uids'] = array_merge(array_unique($cookie_uids));
                $this->cookiegroups[$k]['script'] = base64_encode($cookiegroup['script']);
            }
            else
            {
                unset($this->cookiegroups[$k]);
            }

        }
    }

    private function getCookiesForGroup($groupId)
    {
        $yamlCookies = [];
        foreach (array_filter(explode('|', $this->cookiegroups[$groupId]['cookie'])) as $cookieUid)
        {
            if (!isset($this->cookies[$cookieUid]))
            {
                continue;
            }
            foreach ($this->cookies[$cookieUid] as $cookie)
            {
                if ($cookie['clang_id'] != $this->cookiegroups[$groupId]['clang_id'])
                {
                    continue;
                }
                if (!trim($cookie['definition']))
                {
                    continue;
                }
                $yamlCookies[] = $cookie;
            }
        }
        $cookies = [];
        $i = 0;
        foreach ($yamlCookies as $yamlCookie)
        {
            $cookieDefinitions = rex_string::yamlDecode($yamlCookie['definition']);
            unset($yamlCookie['definition']);
            foreach ($cookieDefinitions as $v)
            {
                $cookies[$i] = $yamlCookie;
                $cookies[$i]['cookie_name'] = $v['name'];
                $cookies[$i]['cookie_lifetime'] = $v['time'];
                $cookies[$i]['description'] = $v['desc'];
                $i++;
            }
        }
        return $cookies;
    }

    private function setConfig()
    {
        foreach ($this->cookiegroups as $v)
        {
            if (!$v['domain'] || !isset($this->domains[$v['domain']]))
            {
                continue;
            }
            $this->config['cookiegroups'][$v['clang_id']][$v['domain']][] = $v;
        }
        foreach ($this->domains as $v)
        {
            //$this->config['domains'][$v['id']] = $v['uid'];
            $this->config['domains'][$v['id']] = $v;
        }
        $this->config['texts'] = $this->texts;
    }

    public static function forceWrite()
    {
        $cache = new self();
        $cache->writeCache();
    }

    public static function read()
    {
        $addon = rex_addon::get('iwcc');
        $configFile = $addon->getDataPath('config.json');
        return rex_file::getCache($configFile);
    }
}