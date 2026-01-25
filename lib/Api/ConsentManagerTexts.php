<?php

namespace FriendsOfRedaxo\ConsentManager\Api;

use FriendsOfRedaxo\ConsentManager\ConsentManager;
use FriendsOfRedaxo\ConsentManager\Frontend;
use FriendsOfRedaxo\ConsentManager\Utility;
use rex_api_exception;
use rex_api_function;
use rex_api_result;
use rex_clang;
use rex_request;
use rex_response;

/**
 * API endpoint für Lazy Loading von Consent Manager Texten und Box-Template.
 *
 * Dieser Endpoint liefert:
 * - Alle übersetzten Texte für die aktuelle Sprache
 * - Das gerenderte Box-Template (HTML)
 * - Cache-Metadaten für Client-seitiges Caching
 *
 * Aufruf: ?rex-api-call=consent_manager_texts&clang=1
 * Registrierung erfolgt in boot.php via rex_api_function::register()
 *
 * @api
 */
class ConsentManagerTexts extends rex_api_function
{
    /** @var bool Erlaubt Frontend-Aufrufe (published API) */
    protected $published = true;

    /**
     * @throws rex_api_exception
     * @return rex_api_result
     */
    public function execute()
    {
        rex_response::cleanOutputBuffers();

        // Clang aus Request (mit Fallback auf Standard-Sprache)
        $clangId = rex_request::get('clang', 'int', rex_clang::getCurrentId());

        // Validiere Clang
        if (!rex_clang::exists($clangId)) {
            throw new rex_api_exception('Invalid clang parameter', 400);
        }

        // ETag für Client-seitiges Caching (basierend auf Version + Cache-Log)
        $version = ConsentManager::getVersion();
        $cacheLogId = ConsentManager::getCacheLogId();
        $etag = md5($version . '-' . $cacheLogId . '-lazy-' . $clangId);

        // Prüfe If-None-Match Header (Client hat bereits gecachte Version)
        $clientEtag = rex_request::server('HTTP_IF_NONE_MATCH', 'string', '');
        if ('' !== $clientEtag && $clientEtag === '"' . $etag . '"') {
            rex_response::setStatus(rex_response::HTTP_NOT_MODIFIED);
            rex_response::sendCacheControl('max-age=86400, public, immutable'); // 24h Cache
            header('ETag: "' . $etag . '"');
            exit;
        }

        // Lade Texte für Sprache
        $texts = ConsentManager::getTexts($clangId);

        // Rendere Box-Template
        $boxTemplate = $this->renderBoxTemplate($clangId);

        // Bereite Antwort vor
        $data = [
            'texts' => $texts,
            'boxTemplate' => $boxTemplate,
            'cache' => [
                'etag' => $etag,
                'version' => $version,
                'cacheLogId' => $cacheLogId,
            ],
        ];

        // Setze HTTP Headers für Caching
        rex_response::sendCacheControl('max-age=86400, public, immutable'); // 24h Cache
        header('ETag: "' . $etag . '"');
        header('Content-Type: application/json; charset=utf-8');

        // Sende JSON Response
        rex_response::sendJson($data);
        exit;
    }

    /**
     * Rendert das Box-Template für die angegebene Sprache.
     *
     * @param int $clangId
     * @return string
     */
    private function renderBoxTemplate($clangId)
    {
        // Setze temporär die aktuelle Sprache für Fragment-Rendering
        $originalClang = rex_clang::getCurrentId();
        rex_clang::setCurrentId($clangId);
        
        // Rendere Box via Fragment
        $boxTemplate = Frontend::getFragment(0, 0, 'ConsentManager/box.php');
        
        // Stelle Original-Sprache wieder her
        rex_clang::setCurrentId($originalClang);
        
        return $boxTemplate;
    }
}
