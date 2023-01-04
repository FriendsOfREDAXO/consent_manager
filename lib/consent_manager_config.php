<?php

class consent_manager_config
{
    /**
     * get consent_manager tables.
     *
     * @param bool $multilangOnly
     * @return array<int, string>
     */
    public static function getTables($multilangOnly = false)
    {
        $tables = [];
        foreach (consent_manager_config::getKeys() as $key) {
            if ($multilangOnly && 'domain' === $key) {
                continue;
            }
            $tables[] = rex::getTable('consent_manager_' . $key);
        }

        return $tables;
    }

    /**
     * get consent_manager keys.
     *
     * @return array<int, string>
     */
    public static function getKeys()
    {
        return [
            'cookie',
            'cookiegroup',
            'text',
            'domain',
        ];
    }
}
