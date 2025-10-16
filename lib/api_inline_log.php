<?php

/**
 * API für Consent Manager Inline Logging
 * 
 * @package redaxo\consent-manager
 */

class rex_api_consent_manager_inline_log extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        $inputData = json_decode(file_get_contents('php://input'), true);
        
        $consentId = $inputData['consent_id'] ?? '';
        $service = $inputData['service'] ?? '';
        $action = $inputData['action'] ?? '';
        
        if (empty($consentId) || empty($service) || empty($action)) {
            return new rex_api_result(false, ['error' => 'Missing required parameters']);
        }

        try {
            // Log in Consent Manager Tabelle schreiben
            $sql = rex_sql::factory();
            
            
            $sql->setTable(rex::getTable('consent_manager_consent_log'));
            $sql->setValue('consent_id', $consentId);
            $sql->setValue('service', $service);
            $sql->setValue('action', $action);
            $sql->setValue('domain', rex_request::server('HTTP_HOST', 'string', ''));
            $sql->setValue('ip_address', $this->getClientIP());
            $sql->setValue('user_agent', rex_request::server('HTTP_USER_AGENT', 'string', ''));
            $sql->setValue('referer', rex_request::server('HTTP_REFERER', 'string', ''));
            $sql->setValue('timestamp', date('Y-m-d H:i:s'));
            $sql->setValue('consent_type', 'inline'); // Kennzeichnung für Inline-Consent
            $sql->insert();

            return new rex_api_result(true, ['status' => 'logged']);
            
        } catch (Exception $e) {
            return new rex_api_result(false, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Client IP ermitteln (DSGVO-konform anonymisiert)
     */
    private function getClientIP()
    {
        $ip = rex_request::server('REMOTE_ADDR', 'string', '');
        
        // IP anonymisieren für DSGVO-Konformität
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // IPv4: letztes Oktett entfernen
            $ip = preg_replace('/\.\d+$/', '.0', $ip);
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // IPv6: letzte 64 Bit entfernen
            $ip = preg_replace('/:[0-9a-f]{1,4}:[0-9a-f]{1,4}:[0-9a-f]{1,4}:[0-9a-f]{1,4}$/i', ':0:0:0:0', $ip);
        }
        
        return $ip;
    }

    /**
     * Log-Tabelle erstellen falls nicht vorhanden
     */
    private function createLogTable()
    {
        rex_sql_table::get(rex::getTable('consent_manager_consent_log'))
            ->ensurePrimaryIdColumn()
            ->ensureColumn(new rex_sql_column('consent_id', 'varchar(255)'))
            ->ensureColumn(new rex_sql_column('service', 'varchar(255)'))
            ->ensureColumn(new rex_sql_column('action', 'varchar(50)'))
            ->ensureColumn(new rex_sql_column('domain', 'varchar(255)'))
            ->ensureColumn(new rex_sql_column('ip_address', 'varchar(45)'))
            ->ensureColumn(new rex_sql_column('user_agent', 'text'))
            ->ensureColumn(new rex_sql_column('referer', 'text'))
            ->ensureColumn(new rex_sql_column('timestamp', 'datetime'))
            ->ensureColumn(new rex_sql_column('consent_type', 'varchar(50)', false, 'global'))
            ->ensureIndex(new rex_sql_index('consent_id', ['consent_id']))
            ->ensureIndex(new rex_sql_index('service', ['service']))
            ->ensureIndex(new rex_sql_index('timestamp', ['timestamp']))
            ->ensure();
    }
}