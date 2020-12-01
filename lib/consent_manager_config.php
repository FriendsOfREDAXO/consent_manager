<?php

class consent_manager_config
{

    public static function getTables($multilangOnly = false)
    {
        $tables = [];
        foreach (consent_manager_config::getKeys() as $key)
        {
            if ($multilangOnly && $key == 'domain')
            {
                continue;
            }
            $tables[] = rex::getTable('consent_manager_' . $key);
        }

        return $tables;
    }

    public static function getKeys()
    {
        return [
            'cookie',
            'cookiegroup',
            'text',
            'domain'
        ];
    }

}