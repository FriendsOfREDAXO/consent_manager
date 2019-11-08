<?php

class iwcc_config
{

    public static function getTables($multilangOnly = false)
    {
        $tables = [];
        foreach (iwcc_config::getKeys() as $key)
        {
            if ($multilangOnly && $key == 'domain')
            {
                continue;
            }
            $tables[] = rex::getTable('iwcc_' . $key);
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