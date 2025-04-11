<?php

use rex_article;

/**
 * @api
 */

class consent_manager_frontend
{
    public $cookiegroups = [];
    public $cookies = [];
    public $texts = [];
    public $domainName = '';
    public $links = [];
    public $scripts = [];
    public $scriptsUnselect = [];
    public $boxClass = '';
    public $cache = [];
    public $version = '';
    public $cacheLogId = '';

    public function __construct($forceWrite = 0)
    {
        if (1 === $forceWrite) {
            consent_manager_cache::forceWrite();
        }
        $this->cache = consent_manager_cache::read();
        if ([] === $this->cache || ([] !== $this->cache && rex_addon::get('consent_manager')->getVersion() !== $this->cache['majorVersion'])) {
            consent_manager_cache::forceWrite();
            $this->cache = consent_manager_cache::read();
        }
        $this->cacheLogId = $this->cache['cacheLogId'];
        $this->version = $this->cache['majorVersion'];
    }

    public static function getFragment($forceCache, $forceReload, $fragmentFilename)
    {
        $fragment = new rex_fragment();
        $fragment->setVar('forceCache', $forceCache);
        $fragment->setVar('forceReload', $forceReload);
        return $fragment->parse($fragmentFilename);
    }

    public function setDomain($domain)
    {
        if (!isset($this->cache['domains']) || !isset($this->cache['domains'][$domain])) {
            return;
        }

        $this->domainName = $domain;
        $this->links['privacy_policy'] = $this->cache['domains'][$domain]['privacy_policy'];
        $this->links['legal_notice'] = $this->cache['domains'][$domain]['legal_notice'];

        $clang = rex_clang::getCurrentId();

        if (isset($this->cache['domains'][$domain]['cookiegroups'])) {
            foreach ($this->cache['domains'][$domain]['cookiegroups'] as $uid) {
                $this->cookiegroups[$uid] = $this->cache['cookiegroups'][$clang][$uid];
            }
        }

        foreach ($this->cookiegroups as $cookiegroup) {
            if (isset($cookiegroup['cookie_uids'])) {
                foreach ($cookiegroup['cookie_uids'] as $uid) {
                    if (isset($this->cache['cookies'][$clang][$uid])) {
                        $this->cookies[$uid] = $this->cache['cookies'][$clang][$uid];
                        $this->scripts[$uid] = $this->cache['cookies'][$clang][$uid]['script'];
                        $this->scriptsUnselect[$uid] = $this->cache['cookies'][$clang][$uid]['script_unselect'];
                    }
                }
            }
            $this->scripts = array_filter($this->scripts);
            $this->scriptsUnselect = array_filter($this->scriptsUnselect);
        }
        
        if (isset($this->cache['texts'][$clang])) {
            $this->texts = $this->cache['texts'][$clang];
        }
    }
}
