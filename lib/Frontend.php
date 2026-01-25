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
        // Domain immer in Kleinbuchstaben normalisieren für den Lookup
        $domain = Utility::hostname();

        $domains = ConsentManager::getDomains();
        if (empty($domains)) {
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
        $clang = rex_request::request('lang', 'integer', 0);
        if (0 === $clang) {
            $clang = rex_clang::getCurrent()->getId();
        }

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
                        
                        // Fallback zur Start-Sprache wenn Script-Felder leer sind
                        $script = $cookieData['script'] ?? '';
                        $scriptUnselect = $cookieData['script_unselect'] ?? '';
                        
                        // Wenn aktuelle Sprache leer ist, versuche Fallback zur Start-Sprache
                        if ('' === trim($script) && $clang !== rex_clang::getStartId()) {
                            $startLangId = rex_clang::getStartId();
                            if (isset($this->cache['cookies'][$startLangId][$uid]['script'])) {
                                $script = $this->cache['cookies'][$startLangId][$uid]['script'];
                            }
                        }
                        
                        if ('' === trim($scriptUnselect) && $clang !== rex_clang::getStartId()) {
                            $startLangId = rex_clang::getStartId();
                            if (isset($this->cache['cookies'][$startLangId][$uid]['script_unselect'])) {
                                $scriptUnselect = $this->cache['cookies'][$startLangId][$uid]['script_unselect'];
                            }
                        }
                        
                        $this->scripts[$uid] = $script;
                        $this->scriptsUnselect[$uid] = $scriptUnselect;
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
        
        // Stabiler Cache-Key basierend auf tatsächlichen Änderungen
        $cacheLogId = ConsentManager::getCacheLogId();
        $version = ConsentManager::getVersion();
        $cacheKey = $version . '-' . $cacheLogId . '-lazy';
        $etag = md5($cacheKey);
        
        header('ETag: "' . $etag . '"');
        header('Cache-Control: max-age=2592000, public, immutable'); // 30 Tage

        // 304 Not Modified Support
        $clientEtag = rex_server('HTTP_IF_NONE_MATCH', 'string', '');
        if (trim($clientEtag, '"') === $etag) {
            http_response_code(304);
            exit;
        }

        // Box-Template wird nicht mehr inline geladen (Lazy Loading via API)

        echo '/* --- Parameters --- */' . PHP_EOL;
        $consent_manager_parameters = [
            'initially_hidden' => 'true' === rex_request::get('i', 'string', 'false'),
            'domain' => Utility::hostname(),
            'consentid' => uniqid('', true),
            'cachelogid' => $cacheLogId,
            'version' => $version,
            'fe_controller' => rex_url::frontend(),
            'forcereload' => rex_request::get('r', 'int', 0),
            'hidebodyscrollbar' => 'true' === rex_request::get('h', 'string', 'false'),
            'cspNonce' => rex_response::getNonce(),
            'cookieSameSite' => $addon->getConfig('cookie_samesite', 'Lax'),
            'cookieSecure' => (bool) $addon->getConfig('cookie_secure', false),
            'cookieName' => $addon->getConfig('cookie_name', 'consentmanager'),
            'lazyLoad' => true,
            'apiEndpoint' => rex_url::frontend() . '?rex-api-call=consent_manager_texts',
            'clang' => $clang,
        ];
        echo 'var consent_manager_parameters = ' . json_encode($consent_manager_parameters, JSON_UNESCAPED_SLASHES) . ';' . PHP_EOL . PHP_EOL;
        
        echo '/* --- Lazy Loading aktiviert (Box-Template wird on-demand geladen) --- */' . PHP_EOL;
        echo 'var consent_manager_box_template = null;' . PHP_EOL . PHP_EOL;
        // Box-Template wird via API geladen (siehe consent_manager_box_template = null)

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
     * @api
     */
    public static function getFrontendCss(): string
    {
        $addon = rex_addon::get('consent_manager');
        
        // Standard CSS-Datei
        $_cssfilename = 'consent_manager_frontend.css';
        
        // 1. Prüfen ob Domain-spezifisches Theme existiert
        $domainTheme = null;
        if (is_string(rex_request::server('HTTP_HOST'))) {
            $frontend = new self(0);
            $frontend->setDomain(rex_request::server('HTTP_HOST'));
            
            if (!empty($frontend->domainInfo['theme'])) {
                $domainTheme = $frontend->domainInfo['theme'];
            }
        }
        
        // 2. Domain-Theme hat Priorität
        if ($domainTheme) {
            // Validiere Theme-Namen: Erlaube nur alphanumerisch, _, -, / und :
            // Verhindere Path-Traversal durch ..
            if (preg_match('/^[a-zA-Z0-9_\-\/:.]+$/', $domainTheme) && !str_contains($domainTheme, '..')) {
                $_themecssfilename = str_replace('project:', 'project_', str_replace('.scss', '.css', $domainTheme));
                // Normalisiere Pfad und prüfe dass er im Assets-Verzeichnis liegt
                $fullPath = $addon->getAssetsPath($_themecssfilename);
                $assetsPath = $addon->getAssetsPath();
                if ('' !== $_themecssfilename && file_exists($fullPath) && str_starts_with(realpath($fullPath), realpath($assetsPath))) {
                    $_cssfilename = $_themecssfilename;
                }
            }
        }
        // 3. Fallback: Globales Theme
        elseif (false !== $addon->getConfig('theme', false) && is_string($addon->getConfig('theme', false))) {
            $_themecssfilename = $addon->getConfig('theme', false);
            $_themecssfilename = str_replace('project:', 'project_', str_replace('.scss', '.css', $_themecssfilename));
            if ('' !== $_themecssfilename && file_exists($addon->getAssetsPath($_themecssfilename))) {
                $_cssfilename = $_themecssfilename;
            }
        }

        // Caching mit Datei-mtime als Cache-Key
        $cssPath = $addon->getAssetsPath($_cssfilename);
        $cacheKey = 'consent_manager_css_' . md5($_cssfilename . filemtime($cssPath));
        
        // Versuche aus Cache zu lesen
        $cached = rex_file::getCache($cacheKey);
        if (null !== $cached) {
            return $cached;
        }
        
        // CSS-Datei lesen
        $_csscontent = rex_file::get($cssPath);
        if ('' === $_csscontent) {
            return '';
        }
        
        // CSS minifizieren
        // 1. Kommentare entfernen
        $_csscontent = (string) preg_replace('/\/\*.*?\*\//s', '', $_csscontent);
        
        // 2. Mehrfaches Whitespace durch einzelnes Leerzeichen ersetzen
        $_csscontent = (string) preg_replace('/\s+/', ' ', $_csscontent);
        
        // 3. Whitespace um CSS-Zeichen entfernen
        $_csscontent = (string) preg_replace('/\s*([{}:;,>~+])\s*/', '$1', $_csscontent);
        
        // 4. Führendes/Abschließendes Whitespace entfernen
        $_csscontent = trim($_csscontent);
        
        // Mit Dateinamen-Kommentar (für Debugging)
        $output = '/*' . $_cssfilename . '*/ ' . $_csscontent;
        
        // In Cache schreiben
        rex_file::putCache($cacheKey, $output);
        
        return $output;
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

    /**
     * Get formatted cookie list for privacy policy pages.
     * Returns HTML table or definition list of all cookies used on the domain.
     *
     * @param string $format 'table' for HTML table, 'dl' for definition list
     * @param string|null $domainName Optional specific domain, null for current domain
     * @return string HTML output
     * @api
     */
    public static function getCookieList(string $format = 'table', ?string $domainName = null): string
    {
        $consent = new self(0);
        
        if (null === $domainName) {
            // Aktuelle Domain verwenden
            if (is_string(rex_request::server('HTTP_HOST'))) {
                $consent->setDomain(rex_request::server('HTTP_HOST'));
            }
        } else {
            // Spezifische Domain verwenden
            $consent->setDomain($domainName);
        }
        
        if (0 === count($consent->cookiegroups)) {
            return '<div class="alert alert-info"><i class="fa fa-info-circle"></i> Keine Cookie-Informationen verfügbar.</div>';
        }
        
        $clang = rex_clang::getCurrentId();
        $output = '';
        
        if ('dl' === $format) {
            // Definition List Format
            $output .= '<dl class="consent-manager-cookie-list">' . PHP_EOL;
            
            foreach ($consent->cookiegroups as $group) {
                if (!isset($group['cookie_uids']) || 0 === count($group['cookie_uids'])) {
                    continue;
                }
                
                $output .= '<dt class="consent-group-name"><strong>' . rex_escape($group['name']) . '</strong></dt>' . PHP_EOL;
                $output .= '<dd class="consent-group-description">' . $group['description'] . '</dd>' . PHP_EOL;
                
                foreach ($group['cookie_uids'] as $cookieUid) {
                    if (!isset($consent->cookies[$cookieUid])) {
                        continue;
                    }
                    
                    $cookie = $consent->cookies[$cookieUid];
                    
                    if (isset($cookie['definition']) && is_array($cookie['definition'])) {
                        foreach ($cookie['definition'] as $def) {
                            $output .= '<dt class="consent-cookie-name">' . rex_escape($def['cookie_name'] ?? '') . '</dt>' . PHP_EOL;
                            $output .= '<dd class="consent-cookie-details">' . PHP_EOL;
                            
                            if ('' !== ($cookie['service_name'] ?? '')) {
                                $output .= '<strong>Service:</strong> ' . rex_escape($cookie['service_name']) . '<br>' . PHP_EOL;
                            }
                            
                            if ('' !== ($def['cookie_purpose'] ?? '')) {
                                $output .= '<strong>Zweck:</strong> ' . rex_escape($def['cookie_purpose']) . '<br>' . PHP_EOL;
                            }
                            
                            if ('' !== ($def['cookie_lifetime'] ?? '')) {
                                $output .= '<strong>Laufzeit:</strong> ' . rex_escape($def['cookie_lifetime']) . '<br>' . PHP_EOL;
                            }
                            
                            if ('' !== ($cookie['provider'] ?? '')) {
                                $output .= '<strong>Anbieter:</strong> ' . rex_escape($cookie['provider']) . PHP_EOL;
                            }
                            
                            $output .= '</dd>' . PHP_EOL;
                        }
                    }
                }
            }
            
            $output .= '</dl>' . PHP_EOL;
        } else {
            // Table Format (default)
            $output .= '<table class="consent-manager-cookie-list table table-striped">' . PHP_EOL;
            $output .= '<thead>' . PHP_EOL;
            $output .= '<tr>' . PHP_EOL;
            $output .= '<th>Cookie-Name</th>' . PHP_EOL;
            $output .= '<th>Service</th>' . PHP_EOL;
            $output .= '<th>Zweck</th>' . PHP_EOL;
            $output .= '<th>Laufzeit</th>' . PHP_EOL;
            $output .= '<th>Anbieter</th>' . PHP_EOL;
            $output .= '<th>Kategorie</th>' . PHP_EOL;
            $output .= '</tr>' . PHP_EOL;
            $output .= '</thead>' . PHP_EOL;
            $output .= '<tbody>' . PHP_EOL;
            
            foreach ($consent->cookiegroups as $group) {
                if (!isset($group['cookie_uids']) || 0 === count($group['cookie_uids'])) {
                    continue;
                }
                
                foreach ($group['cookie_uids'] as $cookieUid) {
                    if (!isset($consent->cookies[$cookieUid])) {
                        continue;
                    }
                    
                    $cookie = $consent->cookies[$cookieUid];
                    
                    if (isset($cookie['definition']) && is_array($cookie['definition'])) {
                        foreach ($cookie['definition'] as $def) {
                            $output .= '<tr>' . PHP_EOL;
                            $output .= '<td>' . rex_escape($def['cookie_name'] ?? '') . '</td>' . PHP_EOL;
                            $output .= '<td>' . rex_escape($cookie['service_name'] ?? '') . '</td>' . PHP_EOL;
                            $output .= '<td>' . rex_escape($def['cookie_purpose'] ?? '') . '</td>' . PHP_EOL;
                            $output .= '<td>' . rex_escape($def['cookie_lifetime'] ?? '') . '</td>' . PHP_EOL;
                            $output .= '<td>' . rex_escape($cookie['provider'] ?? '') . '</td>' . PHP_EOL;
                            $output .= '<td>' . rex_escape($group['name']) . '</td>' . PHP_EOL;
                            $output .= '</tr>' . PHP_EOL;
                        }
                    }
                }
            }
            
            $output .= '</tbody>' . PHP_EOL;
            $output .= '</table>' . PHP_EOL;
        }
        
        return $output;
    }
}
