<?php
class rex_api_consent_manager extends rex_api_function
{
    protected $published = true;

    function execute()
    {
        $domain = rex_post('domain', 'string', false);
        $consentid = rex_post('consentid', 'string', false);
        $consent_manager = isset($_COOKIE['consent_manager']) ? json_decode($_COOKIE['consent_manager'], 1) : false;
        if (!$domain || !$consentid || !$consent_manager) exit;
        if ((string)$consent_manager['consentid'] == $consentid) {
            $ip = $_SERVER['REMOTE_ADDR'];
            if (strpos($ip, '.') !== false) {
                $pieces = explode('.', $ip);
                $nPieces = count($pieces);
                $pieces[$nPieces - 1] = $pieces[$nPieces - 2] = 'XXX';
                $anonymizedIp = implode('.', $pieces);
            } else {
                $pieces = explode(':', $ip);
                $nPieces = count($pieces);
                $pieces[$nPieces - 1] = $pieces[$nPieces - 2] = 'XXXX';
                $anonymizedIp = implode(':', $pieces);
            }
            $db = rex_sql::factory();
            $db->setTable(rex::getTable('consent_manager_consent_log'));
            $db->setValue('domain', $domain);
            $db->setValue('consentid', $consentid);
            $db->setValue('consents', json_encode($consent_manager['consents']));
            $db->setValue('cachelogid', $consent_manager['cachelogid']);
            $db->setValue('ip', $anonymizedIp);
            $db->setRawValue('createdate', 'NOW()');
            $db->insert();
        }
        exit;
    }
}