<?php

class iwcc_frontend
{

    public $texts = [];
    public $cookiegroups = [];
    public $domainName = '';
    public $links = [];
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
                        $this->cookiegroups[$cgk]['cookie'][$ck]['provider_link_privacy'] = rex_getUrl($this->links['legal_notice']);
                    }
                }
                /*
                if ($v['required'] = '|1|') {
                    $this->cookiegroups[$k]['cookie'][0]['provider_link_privacy'] = rex_getUrl($this->links['legal_notice']);
                }
                */
            }
        }
    }

}