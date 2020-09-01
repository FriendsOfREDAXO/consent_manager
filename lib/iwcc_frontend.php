<?php

class iwcc_frontend
{

    public $cookiegroups = [];
    public $cookies = [];
    public $texts = [];
    public $domainName = '';
    public $links = [];
    public $scripts = [];
    public $boxClass = '';
    public $cache = [];
    public $version = '';

    public function __construct($forceWrite = 0)
    {
        if ($forceWrite) {
            iwcc_cache::forceWrite();
        }
        $this->cache = iwcc_cache::read();
        if (rex_addon::get('iwcc')->getVersion('%s') != $this->cache['majorVersion']) {
            iwcc_cache::forceWrite();
        }
        $this->cacheLogId = $this->cache['cacheLogId'];
        $this->version = $this->cache['majorVersion'];
    }

    public static function getFragment($debug, $forceCache, $fragmentFilename)
    {
        $fragment = new rex_fragment();
        $fragment->setVar('debug', $debug);
        $fragment->setVar('forceCache', $forceCache);

        return $fragment->parse($fragmentFilename);
    }

    public function setDomain($domain)
    {
        if (!isset($this->cache['domains'])) {
            return;
        }
        if (!isset($this->cache['domains'][$domain])) {
            return;
        }
        $this->domainName = $domain;
        $this->links['privacy_policy'] = $this->cache['domains'][$domain]['privacy_policy'];
        $this->links['legal_notice'] = $this->cache['domains'][$domain]['legal_notice'];

        if (in_array(rex_article::getCurrentId(), [$this->links['privacy_policy'], $this->links['legal_notice']])) {
            $this->boxClass = 'iwcc-initially-hidden';
        }
        foreach ($this->cache['cookies'][rex_clang::getCurrentId()] as $uid => $cookie) {
            if (!$cookie['provider_link_privacy']) {
                $this->cache['cookies'][rex_clang::getCurrentId()][$uid]['provider_link_privacy'] = rex_getUrl($this->links['privacy_policy']);
            }
        }
        foreach ($this->cache['domains'][$domain]['cookiegroups'] as $uid) {
            $this->cookiegroups[$uid] = $this->cache['cookiegroups'][rex_clang::getCurrentId()][$uid];
        }
        foreach ($this->cookiegroups as $cookiegroup) {
            foreach ($cookiegroup['cookie_uids'] as $uid) {
                if (isset($this->cache['cookies'][rex_clang::getCurrentId()][$uid])) {
                    $this->cookies[$uid] = $this->cache['cookies'][rex_clang::getCurrentId()][$uid];
                    $this->scripts[$uid] = $this->cache['cookies'][rex_clang::getCurrentId()][$uid]['script'];
                }
            }
            $this->scripts = array_filter($this->scripts);
        }
        if (isset($this->cache['texts'][rex_clang::getCurrentId()])) {
            $this->texts = $this->cache['texts'][rex_clang::getCurrentId()];
        }
    }

}
