<?php

class consent_manager_rex_list
{

    public static function formatDomain($params)
    {
        $ids = array_filter(explode('|', $params['value']));
        if ($ids)
        {
            $domains = [];
            $db = rex_sql::factory();
            $db->setTable(rex::getTable('consent_manager_domain'));
            $db->setWhere('id IN(' . implode(',', $ids) . ') ORDER BY uid ASC');
            $db->select('uid');
            foreach ($db->getArray() as $v)
            {
                $domains[] = $v['uid'];
            }
            return implode('<br>', $domains);
        };
        return '-';
    }

    public static function formatCookie($params)
    {
        $uids = array_filter(explode('|', $params['value']));
        if ($uids)
        {
            return implode('<br>', $uids);
        };
        return '-';
    }

}