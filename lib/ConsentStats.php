<?php

namespace FriendsOfRedaxo\ConsentManager;

use rex;
use rex_sql;

class ConsentStats
{
    /**
     * Returns statistics about consents.
     *
     * @param int $days Number of days to look back
     * @return array<string, mixed>
     */
    public static function getStats(int $days = 30): array
    {
        $sql = rex_sql::factory();
        
        // 1. Total consents per day
        $query = 'SELECT DATE(createdate) as date, COUNT(*) as count 
                  FROM ' . rex::getTable('consent_manager_consent_log') . ' 
                  WHERE createdate >= DATE_SUB(NOW(), INTERVAL ? DAY) 
                  GROUP BY DATE(createdate) 
                  ORDER BY date ASC';
        
        $dailyStats = $sql->getArray($query, [$days]);
        
        // 2. Consent distribution (parsing JSON is heavy in SQL, so we do it in PHP for now or use simple LIKE if possible, but JSON is better)
        // Since we don't know if the DB supports JSON functions (MySQL 5.7+), we fetch and process in PHP for compatibility, 
        // assuming the log isn't massive or we limit the query.
        // For better performance on large datasets, we should use a dedicated stats table or summary table, but for now:
        
        $query = 'SELECT consents FROM ' . rex::getTable('consent_manager_consent_log') . ' 
                  WHERE createdate >= DATE_SUB(NOW(), INTERVAL ? DAY)';
        
        $logs = $sql->getArray($query, [$days]);
        
        $cookieStats = [];
        $totalConsents = count($logs);
        
        foreach ($logs as $log) {
            $consentsJson = is_string($log['consents']) ? $log['consents'] : '';
            $consents = json_decode($consentsJson, true);
            if (is_array($consents)) {
                foreach ($consents as $uid) {
                    if (!isset($cookieStats[$uid])) {
                        $cookieStats[$uid] = 0;
                    }
                    $cookieStats[$uid]++;
                }
            }
        }
        
        // Sort by count desc
        arsort($cookieStats);
        
        return [
            'daily' => $dailyStats,
            'cookies' => $cookieStats,
            'total' => $totalConsents,
            'period_days' => $days
        ];
    }
}
