<?php

namespace FriendsOfRedaxo\ConsentManager;

use rex;
use rex_sql;

class RexListSupport
{
    /**
     * format domains.
     *
     * @api
     * @param array<string, string> $params
     */
    public static function formatDomain($params): string
    {
        $ids = array_map(trim(...), explode('|', $params['value']));
        $ids = array_filter($ids, strlen(...)); // @phpstan-ignore-line
        if ([] !== $ids) {
            // Ensure we have a proper list (reindex array to remove gaps)
            $ids = array_values($ids);
            $domains = [];
            $db = rex_sql::factory();
            $db->setTable(rex::getTable('consent_manager_domain'));
            $db->setWhere('id IN(' . $db->in($ids) . ') ORDER BY uid ASC');
            $db->select('uid');
            foreach ($db->getArray() as $v) {
                $domains[] = $v['uid'];
            }
            return implode('<br>', $domains);
        }
        return '-';
    }

    /**
     * format cookies.
     *
     * @api
     * @param array<string, string> $params
     */
    public static function formatCookie($params): string
    {
        if (isset($params['value'])) {
            $uids = array_map(trim(...), explode('|', $params['value']));
            $uids = array_filter($uids, strlen(...)); // @phpstan-ignore-line));
            if ([] !== $uids) {
                return implode('<br>', $uids);
            }
        }
        return '-';
    }
}
