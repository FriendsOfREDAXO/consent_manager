<?php

namespace FriendsOfRedaxo\ConsentManager;

use rex_addon;
use rex_article;
use rex_clang;
use rex_file;
use rex_fragment;
use rex_logger;
use rex_path;
use rex_request;
use rex_response;
use rex_url;

use function in_array;
use function is_string;

use const ENT_QUOTES;
use const JSON_UNESCAPED_SLASHES;
use const PHP_EOL;

/**
 * @api
 */

class Frontend
{
    public $cookiegroups = []; /** @phpstan-ignore-line */
    public $cookies = []; /** @phpstan-ignore-line */
    public $texts = []; /** @phpstan-ignore-line */
    public $domainName = ''; /** @phpstan-ignore-line */
    public $domainInfo = []; /** @phpstan-ignore-line */
    public $links = []; /** @phpstan-ignore-line */
    public $scripts = []; /** @phpstan-ignore-line */
    public $scriptsUnselect = []; /** @phpstan-ignore-line */
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
            Cache::forceWrite();
        }
        $this->cache = Cache::read();
        if ([] === $this->cache || ([] !== $this->cache && rex_addon::get('consent_manager')->getVersion() !== $this->cache['majorVersion'])) { /** @phpstan-ignore-line */
            Cache::forceWrite();
            $this->cache = Cache::read();
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
        $fragment->setVar('cspNonce', rex_response::getNonce());

        return $fragment->parse($fragmentFilename);
    }

    /**
     * @param int $forceCache
     * @param int $forceReload
     * @param string $fragmentFilename
     * @param array<string, mixed> $additionalVars
     * @return string
     * @api
     */
    public static function getFragmentWithVars($forceCache, $forceReload, $fragmentFilename, array $additionalVars = [])
    {
        $fragment = new rex_fragment();
        $fragment->setVar('forceCache', $forceCache);
        $fragment->setVar('forceReload', $forceReload);
        $fragment->setVar('cspNonce', rex_response::getNonce());

        // Zusätzliche Variablen setzen
        foreach ($additionalVars as $key => $value) {
            $fragment->setVar($key, $value);
        }

        return $fragment->parse($fragmentFilename);
    }

    /**
     * @param string $domain
     * @return void
     */
    public function setDomain($domain)
    {
        // Domain immer in Kleinbuchstaben normalisieren für den Lookup
        $domain = Utility::hostname();

        if (!isset($this->cache['domains'])) {
            return;
        }

        // Zuerst exakte Domain suchen
        if (isset($this->cache['domains'][$domain])) {
            $this->domainName = $domain;
        } else {
            // Dann HTTP_HOST versuchen (für Fälle mit Port oder Subdomain)
            $httpHost = strtolower(rex_request::server('HTTP_HOST'));
            if (isset($this->cache['domains'][$httpHost])) {
                $this->domainName = $httpHost;
            } else {
                // Domain ohne Port versuchen
                $httpHostNoPort = preg_replace('/:\d+$/', '', $httpHost);
                if (isset($this->cache['domains'][$httpHostNoPort])) {
                    $this->domainName = $httpHostNoPort;
                } else {
                    return;
                }
            }
        }

        // Zusätzliche Sicherheitsabfrage
        if ('' === $this->domainName || !isset($this->cache['domains'][$this->domainName])) {
            return;
        }

        $this->domainInfo = $this->cache['domains'][$this->domainName];
        $this->links['privacy_policy'] = $this->cache['domains'][$this->domainName]['privacy_policy'];
        $this->links['legal_notice'] = $this->cache['domains'][$this->domainName]['legal_notice'];

        $article = rex_article::getCurrentId();
        $clang = rex_request::request('lang', 'integer', 0);
        if (0 === $clang) {
            $clang = rex_clang::getCurrent()->getId();
        }

        if (in_array($article, [(int) $this->links['privacy_policy'], (int) $this->links['legal_notice']], true)) {
            $this->boxClass = 'consent_manager-initially-hidden';
        }
        if (isset($this->cache['cookies'][$clang])) {
            foreach ($this->cache['cookies'][$clang] as $uid => $cookie) {
                if (!$cookie['provider_link_privacy']) {
                    $this->cache['cookies'][$clang][$uid]['provider_link_privacy'] = rex_getUrl($this->links['privacy_policy'], $clang);
                }
            }
        }
        if (isset($this->cache['domains'][$this->domainName]['cookiegroups'])) {
            foreach ($this->cache['domains'][$this->domainName]['cookiegroups'] as $uid) {
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

            $this->scripts = array_map(trim(...), $this->scripts);
            $this->scripts = array_filter($this->scripts, strlen(...)); // @phpstan-ignore-line
            $this->scriptsUnselect = array_map(trim(...), $this->scriptsUnselect);
            $this->scriptsUnselect = array_filter($this->scriptsUnselect, strlen(...)); // @phpstan-ignore-line
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
        $addon = rex_addon::get('consent_manager');

        $clang = rex_request::request('lang', 'integer', 0);
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
        echo self::getFragment(0, 0, 'ConsentManager/box.php');
        $boxtemplate = ob_get_contents();
        ob_end_clean();
        if ('' === $boxtemplate) {
            rex_logger::factory()->log('warning', 'Addon consent_manager: Keine Cookie-Gruppen / Cookies ausgewählt bzw. keine Domain zugewiesen! (' . Utility::hostname() . ')');
        }
        if (rex_addon::get('sprog')->isInstalled() && rex_addon::get('sprog')->isAvailable()) {
            /** @phpstan-ignore-next-line */
            $boxtemplate = sprogdown($boxtemplate, $clang);
        }
        $boxtemplate = str_replace("'", "\\'", $boxtemplate);
        $boxtemplate = str_replace("\r", '', $boxtemplate);
        $boxtemplate = str_replace("\n", ' ', $boxtemplate);

        echo '/* --- Parameters --- */' . PHP_EOL;
        $consent_manager_parameters = [
            'initially_hidden' => 'true' === rex_request::get('i', 'string', 'false'),
            'domain' => Utility::hostname(),
            'consentid' => uniqid('', true),
            'cachelogid' => rex_request::get('cid', 'string', ''),
            'version' => rex_request::get('v', 'string', ''),
            'fe_controller' => rex_url::frontend(),
            'forcereload' => rex_request::get('r', 'int', 0),
            'hidebodyscrollbar' => 'true' === rex_request::get('h', 'string', 'false'),
            'cspNonce' => rex_response::getNonce(),
            'cookieSameSite' => $addon->getConfig('cookie_samesite', 'Lax'),
            'cookieSecure' => (bool) $addon->getConfig('cookie_secure', false),
        ];
        echo 'var consent_manager_parameters = ' . json_encode($consent_manager_parameters, JSON_UNESCAPED_SLASHES) . ';' . PHP_EOL . PHP_EOL;
        echo '/* --- Consent-Manager Box Template lang=' . $clang . ' --- */' . PHP_EOL;
        echo 'var consent_manager_box_template = \'';
        echo $boxtemplate . '\';' . PHP_EOL . PHP_EOL; /** @phpstan-ignore-line */

        $lifespan = $addon->getConfig('lifespan', 365);
        if ('' === $lifespan) {
            $lifespan = 365;
        }
        $content = 'const cmCookieExpires = ' . $lifespan . ';' . PHP_EOL . PHP_EOL;
        $filenames = [];
        $filenames[] = 'js.cookie.min.js';
        $filenames[] = 'consent_manager_polyfills.js';
        if (file_exists($addon->getAssetsPath('consent_manager_frontend.min.js'))) {
            $filenames[] = 'consent_manager_frontend.min.js';
        } else {
            $filenames[] = 'consent_manager_frontend.js';
        }
        foreach ($filenames as $filename) {
            $content .= '/* --- ' . rex_url::base('assets/addons/consent_manager/') . $filename . ' --- */' . PHP_EOL . rex_file::get(rex_path::addonAssets('consent_manager', $filename)) . PHP_EOL . PHP_EOL;
        }
        echo $content;
        exit;
    }

    public static function getFrontendCss(): string
    {
        $addon = rex_addon::get('consent_manager');

        $_cssfilename = 'consent_manager_frontend.css';
        if (false !== $addon->getConfig('theme', false) && is_string($addon->getConfig('theme', false))) {
            $_themecssfilename = $addon->getConfig('theme', false);
            $_themecssfilename = str_replace('project:', 'project_', str_replace('.scss', '.css', $_themecssfilename));
            if ('' !== $_themecssfilename && file_exists($addon->getAssetsPath($_themecssfilename))) {
                $_cssfilename = $_themecssfilename;
            }
        }

        $_csscontent = file_get_contents($addon->getAssetsPath($_cssfilename));
        if (false === $_csscontent) {
            return '';
        }
        return '/*' . $_cssfilename . '*/ ' . $_csscontent;
    }

    /**
     * Get nonce attribute for script tags using REDAXO's CSP nonce.
     *
     * @api
     */
    public static function getNonceAttribute(): string
    {
        $nonce = rex_response::getNonce();
        return ' nonce="' . htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') . '"';
    }

    /**
     * Get CSS output for consent manager
     * Alias for getFrontendCss() for consistency with Issue #282.
     *
     * @return string CSS content
     * @api
     */
    public static function getCSS(): string
    {
        return self::getFrontendCss();
    }

    /**
     * Get JavaScript output for consent manager
     * Returns complete JavaScript including parameters, box template and all required libraries.
     *
     * @return string JavaScript content
     * @api
     */
    public static function getJS(): string
    {
        $addon = rex_addon::get('consent_manager');
        $clang = rex_clang::getCurrentId();

        // Get box template
        $boxtemplate = '';
        ob_start();
        echo self::getFragment(0, 0, 'ConsentManager/box.php');
        $boxTemplateResult = ob_get_contents();
        ob_end_clean();
        
        // Ensure we have a string for further processing
        if (false === $boxTemplateResult) {
            $boxtemplate = '';
        } else {
            $boxtemplate = $boxTemplateResult;
        }

        if ('' === $boxtemplate) {
            rex_logger::factory()->log('warning', 'Addon consent_manager: Keine Cookie-Gruppen / Cookies ausgewählt bzw. keine Domain zugewiesen! (' . Utility::hostname() . ')');
        }

        // Process with sprog if available
        if (rex_addon::get('sprog')->isInstalled() && rex_addon::get('sprog')->isAvailable()) {
            // @phpstan-ignore-next-line (sprogdown is optional dependency from sprog addon)
            $sprogResult = sprogdown($boxtemplate, $clang);
            $boxtemplate = is_string($sprogResult) ? $sprogResult : $boxtemplate;
        }

        // Escape for JavaScript
        $boxtemplate = str_replace("'", "\\'", $boxtemplate);
        $boxtemplate = str_replace("\r", '', $boxtemplate);
        $boxtemplate = str_replace("\n", ' ', $boxtemplate);

        $output = '';

        // Parameters
        $output .= '/* --- Parameters --- */' . PHP_EOL;
        $consent_manager_parameters = [
            'initially_hidden' => false,
            'domain' => Utility::hostname(),
            'consentid' => uniqid('', true),
            'cachelogid' => '',
            'version' => $addon->getVersion(),
            'fe_controller' => rex_url::frontend(),
            'forcereload' => 0,
            'hidebodyscrollbar' => false,
            'cspNonce' => rex_response::getNonce(),
            'cookieSameSite' => $addon->getConfig('cookie_samesite', 'Lax'),
            'cookieSecure' => (bool) $addon->getConfig('cookie_secure', false),
        ];
        $output .= 'var consent_manager_parameters = ' . json_encode($consent_manager_parameters, JSON_UNESCAPED_SLASHES) . ';' . PHP_EOL . PHP_EOL;

        // Box template
        $output .= '/* --- Consent-Manager Box Template lang=' . $clang . ' --- */' . PHP_EOL;
        $output .= 'var consent_manager_box_template = \'';
        // $boxtemplate is guaranteed to be string after above checks
        $output .= $boxtemplate . '\';' . PHP_EOL . PHP_EOL;

        // Cookie expiration
        $lifespan = $addon->getConfig('lifespan', 365);
        if ('' === $lifespan) {
            $lifespan = 365;
        }
        $output .= 'const cmCookieExpires = ' . $lifespan . ';' . PHP_EOL . PHP_EOL;

        // JavaScript files
        $filenames = [];
        $filenames[] = 'js.cookie.min.js';
        $filenames[] = 'consent_manager_polyfills.js';
        if (file_exists($addon->getAssetsPath('consent_manager_frontend.min.js'))) {
            $filenames[] = 'consent_manager_frontend.min.js';
        } else {
            $filenames[] = 'consent_manager_frontend.js';
        }

        foreach ($filenames as $filename) {
            $output .= '/* --- ' . rex_url::base('assets/addons/consent_manager/') . $filename . ' --- */' . PHP_EOL;
            $output .= rex_file::get(rex_path::addonAssets('consent_manager', $filename)) . PHP_EOL . PHP_EOL;
        }

        return $output;
    }

    /**
     * Get HTML output for consent manager box
     * Returns only the box HTML without CSS or JavaScript.
     *
     * @return string HTML content
     * @api
     */
    public static function getBox(): string
    {
        return self::getFragment(0, 0, 'ConsentManager/box.php');
    }
}
