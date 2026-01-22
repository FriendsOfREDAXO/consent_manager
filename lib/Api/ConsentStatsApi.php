<?php

namespace FriendsOfRedaxo\ConsentManager\Api;

use FriendsOfRedaxo\ConsentManager\ConsentStats;
use rex_api_function;
use rex_api_result;
use rex_response;

class ConsentStatsApi extends rex_api_function
{
    protected $published = false; // Only for backend users

    public function execute(): rex_api_result
    {
        $days = rex_request('days', 'int', 30);
        $stats = ConsentStats::getStats($days);
        
        rex_response::cleanOutputBuffers();
        rex_response::sendJson($stats);
        exit;
    }
}
