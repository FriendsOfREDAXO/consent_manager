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
    /** @var array<array<string,mixed>> $cookiegroups */
    public array $cookiegroups = [];

    /** @var array<array<string,mixed>> $cookies */
    public array $cookies = [];

    /** @var array<string,string> $texts */
    public array $texts = [];

    public string $domainName = '';

    /** @var array<string,mixed> $domainInfo */
    public array $domainInfo = [];

    /** @var array<string,int> $links */
    public array $links = [];

    /** @var array<string,string> $scripts */
    public array $scripts = [];

    /** @var array<string,string> $scriptsUnselect */
    public array $scriptsUnselect = [];

    public string $boxClass = '';

    /** @var array<int|string,mixed> $cache */
    public array $cache = [];

    public string $version = '';

    public string $cacheLogId = '';

    public function __construct(int $forceWrite = 0)
    {
        if (1 === $forceWrite) {
            Cache::forceWrite();
        }
        $this->cache = ConsentManager::getCache();
        $this->cacheLogId = ConsentManager::getCacheLogId();
        $this->version = ConsentManager::getVersion();
    }

    /**
     * @api
     */
    public static function getFragment(int $forceCache, int $forceReload, string $fragmentFilename): string
    {
        $fragment = new rex_fragment();
        $fragment->setVar('forceCache', $forceCache);
        $fragment->setVar('forceReload', $forceReload);
        $fragment->setVar('cspNonce', rex_response::getNonce());

        return $fragment->parse($fragmentFilename);
    }

    /**
     * @param array<string, mixed> $additionalVars
     * @api
     */
    public static function getFragmentWithVars(int $forceCache, int $forceReload, string $fragmentFilename, array $additionalVars = []): string
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
     * @api
     * @return void
     */
    public function setDomain(string $domain)
    {
        // Texte zuerst laden, unabhängig von Domain
        $clang = rex_request::request('lang', 'integer', 0);
        if (0 === $clang) {
            $clang = rex_clang::getCurrent()->getId();
        }
        if (isset($this->cache['texts'][$clang])) {
            $this->texts = $this->cache['texts'][$clang];
        }

        // Domain immer in Kleinbuchstaben normalisieren für den Lookup
        $domain = Utility::hostname();

        $domains = ConsentManager::getDomains();
        if ([] === $domains) {
            return;
        }

        // Zuerst exakte Domain suchen
        if (isset($domains[$domain])) {
            $this->domainName = $domain;
        } else {
            // Dann HTTP_HOST versuchen (für Fälle mit Port oder Subdomain)
            $httpHost = strtolower(rex_request::server('HTTP_HOST'));
            if (isset($domains[$httpHost])) {
                $this->domainName = $httpHost;
            } else {
                // Domain ohne Port versuchen
                $httpHostNoPort = preg_replace('/:\d+$/', '', $httpHost);
                if (isset($domains[$httpHostNoPort])) {
                    $this->domainName = $httpHostNoPort;
                } else {
                    return;
                }
            }
        }

        // Zusätzliche Sicherheitsabfrage
        if ('' === $this->domainName || !isset($domains[$this->domainName])) {
            return;
        }

        $domainData = $domains[$this->domainName];

        // Sicherstellen, dass Domain-Daten ein Array sind
        if (!is_array($domainData)) {
            return;
        }

        $this->domainInfo = $domainData;
        $this->links['privacy_policy'] = $domainData['privacy_policy'] ?? 0;
        $this->links['legal_notice'] = $domainData['legal_notice'] ?? 0;

        $article = rex_article::getCurrentId();

        if (in_array($article, [(int) $this->links['privacy_policy'], (int) $this->links['legal_notice']], true)) {
            $this->boxClass = 'consent_manager-initially-hidden';
        }
        if (isset($this->cache['cookies'][$clang]) && is_array($this->cache['cookies'][$clang])) {
            foreach ($this->cache['cookies'][$clang] as $uid => $cookie) {
                if (is_array($cookie) && '' === ($cookie['provider_link_privacy'] ?? '')) {
                    // Sicherstellen, dass das Array-Element existiert und veränderbar ist
                    if (isset($this->cache['cookies'][$clang][$uid]) && is_array($this->cache['cookies'][$clang][$uid])) {
                        $this->cache['cookies'][$clang][$uid]['provider_link_privacy'] = rex_getUrl($this->links['privacy_policy'], $clang);
                    }
                }
            }
        }
        if (isset($domainData['cookiegroups']) && is_array($domainData['cookiegroups'])) {
            foreach ($domainData['cookiegroups'] as $uid) {
                if (isset($this->cache['cookiegroups'][$clang][$uid])) {
                    $this->cookiegroups[$uid] = $this->cache['cookiegroups'][$clang][$uid];
                }
            }
        }
        foreach ($this->cookiegroups as $cookiegroup) {
            if (isset($cookiegroup['cookie_uids'])) {
                foreach ($cookiegroup['cookie_uids'] as $uid) {
                    if (isset($this->cache['cookies'][$clang][$uid])) {
                        $cookieData = $this->cache['cookies'][$clang][$uid];
                        $this->cookies[$uid] = $cookieData;
                        $this->scripts[$uid] = $cookieData['script'] ?? '';
                        $this->scriptsUnselect[$uid] = $cookieData['script_unselect'] ?? '';
                    }
                }
            }

            $this->scripts = array_map(trim(...), $this->scripts);
            $this->scripts = array_filter($this->scripts, strlen(...)); // @phpstan-ignore-line
            $this->scriptsUnselect = array_map(trim(...), $this->scriptsUnselect);
            $this->scriptsUnselect = array_filter($this->scriptsUnselect, strlen(...)); // @phpstan-ignore-line
        }
    }

    /**
     * @api
     */
    public function outputJavascript(): never
    {
        $addon = rex_addon::get('consent_manager');

        $clang = rex_request::request('lang', 'integer', 0);
        if (0 === $clang) {
            $clang = rex_clang::getCurrent()->getId();
        }
        rex_response::cleanOutputBuffers();
        header_remove();
        header('Content-Type: application/javascript; charset=utf-8');
        // Use ETag based on version and timestamp for proper caching
        $cacheVersion = rex_request::get('t', 'string', time());
        $etag = md5($addon->getVersion() . '-' . $cacheVersion);
        header('ETag: "' . $etag . '"');
        header('Cache-Control: max-age=604800, public');

        // Check if client has current version
        $clientEtag = rex_request::server('HTTP_IF_NONE_MATCH', 'string', '');
        if (trim($clientEtag, '"') === $etag) {
            http_response_code(304);
            exit;
        }

        $boxtemplate = '';
        ob_start();
        echo self::getFragment(0, 0, 'ConsentManager/box.php');
        $boxtemplate = (string) ob_get_contents();
        ob_end_clean();
        if ('' === $boxtemplate) {
            rex_logger::factory()->log('warning', 'Addon consent_manager: Keine Cookie-Gruppen / Cookies ausgewählt bzw. keine Domain zugewiesen! (' . Utility::hostname() . ')');
        }
        if (rex_addon::get('sprog')->isInstalled() && rex_addon::get('sprog')->isAvailable() && function_exists('sprogdown')) {
            /** @phpstan-ignore-next-line */
            $boxtemplate = \sprogdown($boxtemplate, $clang);
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
        // REXSTAN: meldet «Binary operation "." between array<string>|string and '\';' results in an error.»
        // Das ist definitiv falsch und eine Fehlinterpretation wegen obigem «$boxtemplate = str_replace(...»        /** @phpstan-ignore-next-line */        echo $boxtemplate . '\';' . PHP_EOL . PHP_EOL;

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

    /**
     * Get CSS URL for consent manager theme via Media Manager.
     * Returns a cacheable URL that serves GZIP-compressed CSS.
     *
     * @param array<string,mixed>|null $domainInfo Optional domain info array to use domain-specific theme
     * @api
     * @return string Media Manager URL or fallback to static asset
     */
    public static function getCssUrl(?array $domainInfo = null): string
    {
        $addon = rex_addon::get('consent_manager');
        
        // Prüfe erst ob Domain ein Custom-Theme hat, sonst global
        if (null !== $domainInfo && isset($domainInfo['theme']) && '' !== $domainInfo['theme']) {
            $themeConfig = $domainInfo['theme'];
        } else {
            $themeConfig = $addon->getConfig('theme', false);
        }
        
        // Prüfen ob Media Manager verfügbar ist und ein Theme konfiguriert ist
        if (rex_addon::get('media_manager')->isAvailable() && false !== $themeConfig && is_string($themeConfig) && '' !== $themeConfig) {
            // Theme über Media Manager laden (dynamisch kompiliert mit GZIP)
            return \rex_media_manager::getUrl('consent_manager_theme', $themeConfig);
        }
        
        // Fallback: Statisches CSS aus Assets
        return $addon->getAssetsUrl('consent_manager_frontend.css');
    }

    /**
     * @api
     */
    public static function getFrontendCss(): string
    {
        $addon = rex_addon::get('consent_manager');

        // Prüfen ob Media Manager verfügbar ist und ein Theme konfiguriert ist
        $themeConfig = $addon->getConfig('theme', false);
        
        if (rex_addon::get('media_manager')->isAvailable() && false !== $themeConfig && is_string($themeConfig) && '' !== $themeConfig) {
            // Theme über Media Manager laden (dynamisch kompiliert)
            $mediaManagerUrl = \rex_media_manager::getUrl('consent_manager_theme', $themeConfig);
            
            // CSS-Inhalt vom Media Manager abrufen
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                ],
            ]);
            
            $csscontent = @file_get_contents(\rex_url::frontendController() . $mediaManagerUrl, false, $context);
            
            if (false !== $csscontent && '' !== $csscontent) {
                return '/* Theme: ' . $themeConfig . ' (via Media Manager) */ ' . $csscontent;
            }
            
            // Fallback: Prüfen ob eine kompilierte Datei im Assets-Ordner existiert
            $cssfilename = str_replace('project:', 'project_', str_replace('.scss', '.css', $themeConfig));
            if (file_exists($addon->getAssetsPath($cssfilename))) {
                $fallbackContent = file_get_contents($addon->getAssetsPath($cssfilename));
                if (false !== $fallbackContent) {
                    return '/* Theme: ' . $cssfilename . ' (Fallback) */ ' . $fallbackContent;
                }
            }
        }
        
        // Standard-CSS laden
        $cssfilename = 'consent_manager_frontend.css';
        $csscontent = file_get_contents($addon->getAssetsPath($cssfilename));
        if (false === $csscontent) {
            return '';
        }
        return '/* ' . $cssfilename . ' */ ' . $csscontent;
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
     * @api
     *
     * TODO: sollte man das JS nicht besser als Fragment bereitstellen ... das hier ist etwas unübersichtlich
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
            // TODO: Prüfen,ob die Log-Meldungen engl. sein sollte wie an anderen Stellen bzw. nach .lang übertragen werden

            rex_logger::factory()->log('warning', 'Addon consent_manager: Keine Cookie-Gruppen / Cookies ausgewählt bzw. keine Domain zugewiesen! (' . Utility::hostname() . ')');
        }

        // Process with sprog if available
        if (rex_addon::get('sprog')->isInstalled() && rex_addon::get('sprog')->isAvailable() && function_exists('sprogdown')) {
            // @phpstan-ignore-next-line (sprogdown is optional dependency from sprog addon)
            $sprogResult = \sprogdown($boxtemplate, $clang);
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
     * @api
     */
    public static function getBox(): string
    {
        return self::getFragment(0, 0, 'ConsentManager/box.php');
    }
}
