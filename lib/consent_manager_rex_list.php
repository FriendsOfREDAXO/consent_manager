<?php

class consent_manager_rex_list
{

    /**
     * format domains
     *
     * @param array<string, string> $params
     * @return string
     * @api
     */
    public static function formatDomain($params)
    {
        $ids = array_filter(explode('|', $params['value']));
        if ($ids) /** @phpstan-ignore-line */
        {
            $domains = [];
            $db = rex_sql::factory();
            $db->setTable(rex::getTable('consent_manager_domain'));
            $db->setWhere('id IN(' . $db->escape(implode(',', $ids)) . ') ORDER BY uid ASC'); /** @phpstan-ignore-line */
            $db->select('uid');
            foreach ($db->getArray() as $v)
            {
                $domains[] = $v['uid'];
            }
            return implode('<br>', $domains);
        };
        return '-';
    }

    /**
     * format cookies
     *
     * @param array<string, string> $params
     * @return string
     * @api
     */
    public static function formatCookie($params)
    {
        $uids = array_filter(explode('|', $params['value']));
        if ($uids) /** @phpstan-ignore-line */
        {
            return implode('<br>', $uids);
        };
        return '-';
    }

}