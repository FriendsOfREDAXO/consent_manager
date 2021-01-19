<?php

class consent_manager_frontend
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
	public $cacheLogId = '';

    public function __construct($forceWrite = 0)
    {
        if ($forceWrite) {
            consent_manager_cache::forceWrite();
        }
        $this->cache = consent_manager_cache::read();
        if (!$this->cache || rex_addon::get('consent_manager')->getVersion('%s') != $this->cache['majorVersion']) {
            consent_manager_cache::forceWrite();
            $this->cache = consent_manager_cache::read();
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

        if (in_array($_SESSION['consent_manager']['article'], [$this->links['privacy_policy'], $this->links['legal_notice']])) {
            $this->boxClass = 'consent_manager-initially-hidden';
        }
        if (isset($this->cache['cookies'][rex_clang::getCurrentId()])) {
            foreach ($this->cache['cookies'][rex_clang::getCurrentId()] as $uid => $cookie) {
            if (!$cookie['provider_link_privacy']) {
                $this->cache['cookies'][rex_clang::getCurrentId()][$uid]['provider_link_privacy'] = rex_getUrl($this->links['privacy_policy']);
            }
            }
        }
        if (isset($this->cache['domains'][$domain]['cookiegroups'])) {
            foreach ($this->cache['domains'][$domain]['cookiegroups'] as $uid) {
                $this->cookiegroups[$uid] = $this->cache['cookiegroups'][rex_clang::getCurrentId()][$uid];
            }
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

    public static function outputJavascript($host = null, $article_id = null) {
        rex_response::cleanOutputBuffers();
        header('Content-Type: application/javascript');
        header("Cache-Control: max-age=604800, public");
        //header('Pragma: cache');
        //header('Cache-Control: public');
        //header('Expires: ' . date('D, j M Y', strtotime('+1 week')) . ' 00:00:00 GMT');
        $boxtemplate = '';
        if (isset($_SESSION['consent_manager'])) {
            ob_start();
            echo self::getFragment($_SESSION['consent_manager']['debug'], 0, 'consent_manager_box.php');
            $boxtemplate = ob_get_contents();
            ob_end_clean();
            $boxtemplate = str_replace("'", "\'", $boxtemplate);
            $boxtemplate = str_replace("\r", "", $boxtemplate);
            $boxtemplate = str_replace("\n", " ", $boxtemplate);            
        }
        echo '/* --- Consent-Manager Box Template --- */' . PHP_EOL;
        echo 'var consent_manager_box_template = \'';
        echo $boxtemplate . '\';' . PHP_EOL . PHP_EOL;

        $content = '';
        $filenames = [];
        $filenames[] = 'js.cookie-2.2.1.min.js';
        $filenames[] = 'consent_manager_polyfills.js';
        $filenames[] = 'consent_manager_frontend.js';
        foreach ($filenames as $filename) {
            $content .= '/* --- ' . rex_url::base('assets/addons/consent_manager/') . $filename . ' --- */' . PHP_EOL . rex_file::get(rex_path::addonAssets('consent_manager', $filename)) . PHP_EOL . PHP_EOL;
        }
        echo $content;
        exit;
    }

}
