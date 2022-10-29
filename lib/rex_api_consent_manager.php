<?php

class rex_api_consent_manager extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        $domain = rex_post('domain', 'string', false);
        $consentid = rex_post('consentid', 'string', false);
        $consent_manager = (null !== rex_request::cookie('consent_manager')) ? (array)json_decode(strval(rex_request::cookie('consent_manager')), true) : false;
        if (false === $domain || false === $consentid || false === $consent_manager) {
            exit;
        }
        if ($consent_manager['consentid'] === $consentid) {
            $ip = strval(rex_request::server('REMOTE_ADDR'));
            if (false !== strpos($ip, '.')) {
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
