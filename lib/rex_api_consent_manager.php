<?php

class rex_api_consent_manager extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        $domain = rex_post('domain', 'string', false);
        $consentid = rex_post('consentid', 'string', false);
        $consent_manager = false;
        if (is_string(rex_request::cookie('consent_manager'))) {
            $consent_manager = (array) json_decode(rex_request::cookie('consent_manager'), true);
        }
        if (false === $domain || false === $consentid || false === $consent_manager) {
            exit;
        }
        if ($consent_manager['consentid'] === $consentid) {
            $anonymizedIp = '';
            if (is_string(rex_request::server('REMOTE_ADDR'))) {
                $ip = rex_request::server('REMOTE_ADDR');
                if (str_contains($ip, '.')) {
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
