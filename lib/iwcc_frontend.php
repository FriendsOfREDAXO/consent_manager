<?php

class iwcc_frontend
{

    public $texts = [];
    public $cookiegroups = [];
    public $domainName = '';
    public $links = [];
    public $boxClass = '';
    private $cache = [];
    private $domainId = 0;

    public function __construct($forceWrite = 0)
    {
        if ($forceWrite) {
            iwcc_cache::forceWrite();
        }
        $this->cache = iwcc_cache::read();
    }

    public function setDomain($domain)
    {
        foreach ($this->cache['domains'] as $k => $v)
        {
            if ($v['uid'] == $domain)
            {
                $this->domainId = $k;
                $this->domainName = $v['uid'];
                $this->links['privacy_policy'] = $v['privacy_policy'];
                $this->links['legal_notice'] = $v['legal_notice'];
                break;
            }
        }
        if ($this->domainId)
        {
            if(in_array(rex_article::getCurrentId(),[$this->links['privacy_policy'], $this->links['legal_notice']])) {
                $this->boxClass = 'iwcc-initially-hidden';
            }
            if (isset($this->cache['cookiegroups'][rex_clang::getCurrentId()][$this->domainId]))
            {
                $this->cookiegroups = $this->cache['cookiegroups'][rex_clang::getCurrentId()][$this->domainId];
            }
            if (isset($this->cache['texts'][rex_clang::getCurrentId()]))
            {
                $this->texts = $this->cache['texts'][rex_clang::getCurrentId()];
            }
            foreach ($this->cookiegroups as $cgk => $cgv) {
                foreach ($cgv['cookie'] as $ck => $cv) {
                    if (!$cv['provider_link_privacy']) {
                        $this->cookiegroups[$cgk]['cookie'][$ck]['provider_link_privacy'] = rex_getUrl($this->links['privacy_policy']);
                    }
                }
            }
        }
    }

    public static function getFragment($debug,$forceCache) {
        $fragment = new rex_fragment();
        $fragment->setVar('debug', $debug);
        $fragment->setVar('forceCache', $forceCache);
        return $fragment->parse('iwcc_box.php');
    }

}