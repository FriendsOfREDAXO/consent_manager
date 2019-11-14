<?php

class iwcc_clang
{

    public static function deleteDataset($table, $pid)
    {
        $msg = rex_view::success(rex_i18n::msg('iwcc_successfully_deleted'));
        $db = rex_sql::factory();
        $db->setTable($table);
        $db->setWhere('pid = :pid', ['pid' => $pid]);
        $db->select('id');
        foreach ($db->getArray() as $v)
        {
            $db = rex_sql::factory();
            $db->setTable($table);
            $db->setWhere('id = :id', ['id' => $v['id']]);
            $db->delete();
        }
        iwcc_cache::forceWrite();
        return $msg;
    }

    public static function deleteCookie($pid)
    {
        $msg = rex_view::success(rex_i18n::msg('iwcc_successfully_deleted'));
        $db = rex_sql::factory();
        $db->setTable(rex::getTable('iwcc_cookie'));
        $db->setWhere('pid = :pid', ['pid' => $pid]);
        $db->select('uid');
        foreach ($db->getArray() as $v)
        {
            if ($v['uid'] == 'iwcc')
            {
                $msg = rex_view::error(rex_i18n::msg('iwcc_not_deletable'));
                break;
            }
            $db = rex_sql::factory();
            $db->setTable(rex::getTable('iwcc_cookie'));
            $db->setWhere('uid = :uid', ['uid' => $v['uid']]);
            $db->delete();
        }
        return $msg;
    }

    public static function addLangNav(rex_extension_point $ep)
    {
        if (rex::isBackend() && rex::getUser())
        {
            foreach (iwcc_config::getKeys() as $key)
            {
                if ($key == 'domain') continue;
                $page = rex_be_controller::getPageObject('iwcc/' . $key);
                $clang_id = str_replace('clang', '', rex_be_controller::getCurrentPagePart(3));
                foreach (rex_clang::getAll() as $id => $clang)
                {
                    $page->addSubpage((new rex_be_page('clang' . $id, $clang->getName()))
                        ->setSubPath(rex_path::addon('iwcc', 'pages/' . $key . '.php'))
                        ->setIsActive($id == $clang_id)
                    );
                }
            }

        }
    }

    public static function formSaved(rex_extension_point $ep)
    {
        $form = $ep->getParams()['form'];
        $params = $ep->getParams();
        if (!in_array($form->getTableName(), iwcc_config::getTables(1)))
        {
            return true;
        }
        if (!$form->isEditMode())
        {
            self::insertDataset($form, $params);
        }
        if ($form->isEditMode() && $form->getSql()->getValue('clang_id') == rex_clang::getStartId())
        {
            self::updateDataset($form, $params);
        }
    }

    private static function insertDataset($form, $params)
    {

        $db = rex_sql::factory();
        $db->setTable($form->getTableName());
        $db->setWhere('pid = ' . $params['sql']->getLastId());
        $db->select('*');
        $inserted = $db->getArray()[0];
        foreach (rex_clang::getAllIds() as $clangId)
        {
            if ($inserted['clang_id'] == $clangId)
            {
                continue;
            }

            $db = rex_sql::factory();
            $db->setTable($form->getTableName());
            foreach ($inserted as $k => $v)
            {
                if ($k == 'pid')
                {
                    continue;
                }
                if ($k == 'clang_id')
                {
                    $db->setValue($k, $clangId);
                }
                else
                {
                    $db->setValue($k, $v);
                }
            }
            $db->insert();
        }
    }

    private static function updateDataset($form)
    {
        $fields2Update = [];
        $newValues = [];
        if (rex::getTable('iwcc_cookiegroup') == $form->getTableName())
        {
            $fields2Update = ['domain', 'uid', 'prio', 'required', 'cookie', 'script'];
            $db = rex_sql::factory();
            $db->setTable($form->getTableName());
            $db->setWhere('pid = :pid', ['pid' => $form->getSql()->getValue('pid')]);
            $db->select(implode(',', $fields2Update));
            $newValues = $db->getArray()[0];
        }
        elseif (rex::getTable('iwcc_cookie') == $form->getTableName())
        {
            $fields2Update = ['uid'];
            $db = rex_sql::factory();
            $db->setTable($form->getTableName());
            $db->setWhere('pid = :pid', ['pid' => $form->getSql()->getValue('pid')]);
            $db->select(implode(',', $fields2Update));
            $newValues = $db->getArray()[0];
        }
        foreach (rex_clang::getAllIds() as $clangId)
        {
            if ($form->getSql()->getValue('clang_id') == $clangId)
            {
                continue;
            }
            $db = rex_sql::factory();
            $db->setTable($form->getTableName());
            $db->setWhere('clang_id = :clang_id AND id = :id', ['clang_id' => $clangId, 'id' => $form->getSql()->getValue('id')]);
            foreach ($fields2Update as $v)
            {
                $db->setValue($v, $newValues[$v]);

            }
            $db->update();
        }
    }

    public static function clangDeleted(rex_extension_point $ep)
    {
        foreach (iwcc_config::getTables(1) as $table)
        {
            $deleteLang = rex_sql::factory();
            $deleteLang->setQuery('DELETE FROM ' . $table . ' WHERE clang_id=?', [$ep->getParam('clang')->getId()]);
        }
    }

    public static function addonJustInstalled()
    {
        $clangIds = rex_clang::getAllIds();
        if (count($clangIds) == 1)
        {
            return true;
        }
        foreach ($clangIds as $clangId)
        {
            if ($clangId == rex_clang::getStartId())
            {
                continue;
            }
            self::addClang($clangId);
        }
    }

    private static function addClang($clangId)
    {
        foreach (iwcc_config::getTables(1) as $table)
        {
            $firstLang = rex_sql::factory();
            $firstLang->setQuery('SELECT * FROM ' . $table . ' WHERE clang_id=?', [rex_clang::getStartId()]);
            $fields = $firstLang->getFieldnames();

            $newLang = rex_sql::factory();
            $newLang->setDebug(false);
            foreach ($firstLang as $firstLangEntry)
            {
                $newLang->setTable($table);
                foreach ($fields as $key => $value)
                {
                    if ($value == 'pid')
                    {
                        echo '';
                    }
                    elseif ($value == 'clang_id')
                    {
                        $newLang->setValue('clang_id', $clangId);
                    }
                    else
                    {
                        $newLang->setValue($value, $firstLangEntry->getValue($value));
                    }
                }
                $newLang->insert();
            }
        }
    }

    public static function clangAdded(rex_extension_point $ep)
    {
        self::addClang($ep->getParam('clang')->getId());
    }

}
