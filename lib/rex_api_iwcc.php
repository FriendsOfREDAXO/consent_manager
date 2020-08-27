<?php
class rex_api_iwcc extends rex_api_function
{
    protected $published = true;

    function execute()
    {
        $consentid = rex_post('consentid', 'string', false);
        $iwcc = isset($_COOKIE['iwcc']) ? json_decode($_COOKIE['iwcc'],1) : false;
        if (!$consentid || !$iwcc) exit;
        if ((string)$iwcc['consentid'] == $consentid) {
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
            $db->setTable(rex::getTable('iwcc_consent_log'));
            $db->setValue('consentid', $consentid);
            $db->setValue('consents', json_encode($iwcc['consents']));
            $db->setValue('cachelogid', $iwcc['cachelogid']);
            $db->setValue('ip', $anonymizedIp);
            $db->setRawValue('createdate', 'NOW()');
            $db->insert();
        }
        exit;
    }
}