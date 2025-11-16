<?php

namespace FriendsOfRedaxo\ConsentManager;

use rex;

class Config
{
    /**
     * get consent_manager tables.
     *
     * @api
     * @return array<int, string>
     */
    public static function getTables(bool $multilangOnly = false)
    {
        $tables = [];
        foreach (self::getKeys() as $key) {
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
     * @api
     * @return string[]
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
