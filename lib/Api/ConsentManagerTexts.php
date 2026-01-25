<?php

namespace FriendsOfRedaxo\ConsentManager\Api;

use FriendsOfRedaxo\ConsentManager\ConsentManager;
use FriendsOfRedaxo\ConsentManager\Utility;
use rex_addon;
use rex_api_function;
use rex_api_result;
use rex_clang;
use rex_fragment;
use rex_logger;
use rex_request;
use rex_response;
use rex_server;

/**
 * API Endpoint für Lazy Loading von Texten und Box-Template.
 *
 * Lädt nur die tatsächlich benötigten Texte on-demand,
 * reduziert initiale JavaScript-Größe um ~33%.
 *
 * @api
 */
class ConsentManagerTexts extends rex_api_function
{
    protected $published = true;

    public function execute(): rex_api_result
    {
        rex_response::cleanOutputBuffers();

        $clangId = rex_request::get('clang', 'int', rex_clang::getCurrentId());
        $domain = rex_request::get('domain', 'string', '');

        // Cache-Header setzen
        $addon = rex_addon::get('consent_manager');
        $cacheLogId = ConsentManager::getCacheLogId();
        $version = ConsentManager::getVersion();
        $etag = md5($version . '-' . $cacheLogId . '-' . $clangId);

        header('Content-Type: application/json; charset=utf-8');
        header('ETag: "' . $etag . '"');
        header('Cache-Control: max-age=86400, public'); // 24h Cache

        // 304 Not Modified Support
        $clientEtag = rex_server('HTTP_IF_NONE_MATCH', 'string', '');
        if (trim($clientEtag, '"') === $etag) {
            http_response_code(304);
            exit;
        }

        // Texte holen
        $texts = ConsentManager::getTexts($clangId);

        // Box-Template rendern
        $boxTemplate = $this->renderBoxTemplate($clangId);

        $data = [
            'texts' => $texts,
            'boxTemplate' => $boxTemplate,
            'cache' => [
                'version' => $version,
                'logId' => $cacheLogId,
                'etag' => $etag,
            ],
            'meta' => [
                'clang' => $clangId,
                'domain' => $domain,
                'timestamp' => time(),
            ],
        ];

        rex_response::sendJson($data);
        exit;
    }

    private function renderBoxTemplate(int $clangId): string
    {
        ob_start();
        $fragment = new rex_fragment();
        $fragment->setVar('forceCache', 0);
        $fragment->setVar('forceReload', 0);
        $fragment->setVar('cspNonce', rex_response::getNonce());
        echo $fragment->parse('ConsentManager/box.php');
        $boxTemplate = (string) ob_get_contents();
        ob_end_clean();

        if ('' === $boxTemplate) {
            rex_logger::factory()->log('warning', 'Addon consent_manager: Keine Cookie-Gruppen / Cookies ausgewählt bzw. keine Domain zugewiesen! (' . Utility::hostname() . ')');
            return '';
        }

        // Markdown-Processing (Sprog)
        if (rex_addon::exists('sprog') && rex_addon::get('sprog')->isAvailable() && function_exists('sprogdown')) {
            /** @phpstan-ignore-next-line */
            $boxTemplate = \sprogdown($boxTemplate, $clangId);
        }

        return $boxTemplate;
    }
}
