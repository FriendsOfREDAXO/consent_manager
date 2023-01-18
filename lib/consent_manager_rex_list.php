<?php

class consent_manager_rex_list
{
    /**
     * format domains.
     *
     * @param array<string, string> $params
     * @return string
     * @api
     */
    public static function formatDomain($params)
    {
        $ids = array_filter(explode('|', $params['value']));
        if ([] !== $ids) {
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
     * @param array<string, string> $params
     * @return string
     * @api
     */
    public static function formatCookie($params)
    {
        if (isset($params['value'])) {
            $uids = array_filter(explode('|', $params['value']));
            if ([] !== $uids) {
                return implode('<br>', $uids);
            }
        }
        return '-';
    }
}
