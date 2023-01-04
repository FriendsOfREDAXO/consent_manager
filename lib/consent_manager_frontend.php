<?php

/**
 * @api
 */

class consent_manager_frontend
{
    public $cookiegroups = []; /** @phpstan-ignore-line */
    public $cookies = []; /** @phpstan-ignore-line */
    public $texts = []; /** @phpstan-ignore-line */
    public $domainName = ''; /** @phpstan-ignore-line */
    public $links = []; /** @phpstan-ignore-line */
    public $scripts = []; /** @phpstan-ignore-line */
    public $boxClass = ''; /** @phpstan-ignore-line */
    public $cache = []; /** @phpstan-ignore-line */
    public $version = ''; /** @phpstan-ignore-line */
    public $cacheLogId = ''; /** @phpstan-ignore-line */

    /**
     * @param int $forceWrite
     */
    public function __construct($forceWrite = 0)
    {
        if (1 === $forceWrite) {
            consent_manager_cache::forceWrite();
        }
        $this->cache = consent_manager_cache::read();
        if (null === $this->cache || (null !== $this->cache && rex_addon::get('consent_manager')->getVersion('%s') !== $this->cache['majorVersion'])) { /** @phpstan-ignore-line */
            consent_manager_cache::forceWrite();
            $this->cache = consent_manager_cache::read();
        }
        $this->cacheLogId = $this->cache['cacheLogId']; /** @phpstan-ignore-line */
        $this->version = $this->cache['majorVersion']; /** @phpstan-ignore-line */
    }

    /**
     * @param int $forceCache
     * @param int $forceReload
     * @param string $fragmentFilename
     * @return string
     * @api
     */
    public static function getFragment($forceCache, $forceReload, $fragmentFilename)
    {
        $fragment = new rex_fragment();
        $fragment->setVar('forceCache', $forceCache);
        $fragment->setVar('forceReload', $forceReload);

        return $fragment->parse($fragmentFilename);
    }

    /**
     * @param string $domain
     * @return void
     */
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

        $article = rex_article::getCurrentId();
        $clang = rex_request('lang', 'integer', 0);
        if (0 === $clang) {
            $clang = rex_clang::getCurrent()->getId();
        }

        if (in_array($article, [(int) $this->links['privacy_policy'], (int) $this->links['legal_notice']], true)) {
            $this->boxClass = 'consent_manager-initially-hidden';
        }
        if (isset($this->cache['cookies'][$clang])) {
            foreach ($this->cache['cookies'][$clang] as $uid => $cookie) {
                if (!$cookie['provider_link_privacy']) {
                    $this->cache['cookies'][$clang][$uid]['provider_link_privacy'] = rex_getUrl($this->links['privacy_policy']);
                }
            }
        }
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
                    }
                }
            }
            $this->scripts = array_filter($this->scripts);
        }
        if (isset($this->cache['texts'][$clang])) {
            $this->texts = $this->cache['texts'][$clang];
        }
    }

    /**
     * @param string $host
     * @param string $article_id
     * @return void
     */
    public function outputJavascript($host = null, $article_id = null)
    {
        $clang = rex_request('lang', 'integer', 0);
        if (0 === $clang) {
            $clang = rex_clang::getCurrent()->getId();
        }
        rex_response::cleanOutputBuffers();
        header_remove();
        header('Content-Type: application/javascript; charset=utf-8');
        header('Cache-Control: max-age=604800, public');
        // header('Pragma: cache');
        // header('Cache-Control: public');
        // header('Expires: ' . date('D, j M Y', strtotime('+1 week')) . ' 00:00:00 GMT');
        $boxtemplate = '';
        ob_start();
        echo self::getFragment(0, 0, 'consent_manager_box.php');
        $boxtemplate = ob_get_contents();
        ob_end_clean();
        if ('' === $boxtemplate) {
            rex_logger::factory()->log('warning', 'Addon consent_manager: Keine Cookie-Gruppen / Cookies ausgewählt bzw. keine Domain zugewiesen! (' . rex_request::server('HTTP_HOST') . ')');
        }
        if (rex_addon::get('sprog')->isInstalled() && rex_addon::get('sprog')->isAvailable()) {
            /** @phpstan-ignore-next-line */
            $boxtemplate = sprogdown($boxtemplate, $clang);
        }
        $boxtemplate = str_replace("'", "\'", $boxtemplate);
        $boxtemplate = str_replace("\r", '', $boxtemplate);
        $boxtemplate = str_replace("\n", ' ', $boxtemplate);

        echo '/* --- Parameters --- */' . PHP_EOL;
        echo 'var consent_manager_parameters = {initially_hidden: ' . rex_get('i', 'string', 'false') . ', domain: "' . rex_request::server('HTTP_HOST') . '", consentid: "' . uniqid('', true) . '", cachelogid: "' . rex_get('cid', 'string', '') . '", version: "' . rex_get('v', 'string', '') . '", fe_controller: "' . rex_url::frontend() . '", forcereload: ' . rex_get('r', 'int', 0) . ', hidebodyscrollbar: ' . rex_get('h', 'string', 'false') . '};' . PHP_EOL . PHP_EOL;
        echo '/* --- Consent-Manager Box Template lang=' . $clang . ' --- */' . PHP_EOL;
        echo 'var consent_manager_box_template = \'';
        echo strval($boxtemplate) . '\';' . PHP_EOL . PHP_EOL;

        $content = '';
        $filenames = [];
        $filenames[] = 'js.cookie.min.js';
        $filenames[] = 'consent_manager_polyfills.js';
        $filenames[] = 'consent_manager_frontend.js';
        foreach ($filenames as $filename) {
            $content .= '/* --- ' . rex_url::base('assets/addons/consent_manager/') . $filename . ' --- */' . PHP_EOL . rex_file::get(rex_path::addonAssets('consent_manager', $filename)) . PHP_EOL . PHP_EOL;
        }
        echo $content;
        exit;
    }

    /**
     * @return string|false
     */
    public static function getFrontendCss()
    {
        $addon = rex_addon::get('consent_manager');

        $_cssfilename = 'consent_manager_frontend.css';
        $_csscontent = file_get_contents($addon->getAssetsPath($_cssfilename));

        return $_csscontent;
    }
}
