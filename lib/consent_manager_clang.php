<?php

class consent_manager_clang
{
    /**
     * @param string $table
     * @param int $pid
     * @return string
     */
    public static function deleteDataset($table, $pid)
    {
        $msg = rex_view::success(rex_i18n::msg('consent_manager_successfully_deleted'));
        $db = rex_sql::factory();
        $db->setTable($table);
        $db->setWhere('pid = :pid', ['pid' => $pid]);
        $db->select('id');
        foreach ($db->getArray() as $v) {
            $db = rex_sql::factory();
            $db->setTable($table);
            $db->setWhere('id = :id', ['id' => $v['id']]);
            $db->delete();
        }
        consent_manager_cache::forceWrite();
        return $msg;
    }

    /**
     * @param int $pid
     * @return string
     */
    public static function deleteCookie($pid)
    {
        $msg = rex_view::success(rex_i18n::msg('consent_manager_successfully_deleted'));
        $db = rex_sql::factory();
        $db->setTable(rex::getTable('consent_manager_cookie'));
        $db->setWhere('pid = :pid', ['pid' => $pid]);
        $db->select('uid');
        foreach ($db->getArray() as $v) {
            if ('consent_manager' === $v['uid']) {
                $msg = rex_view::error(rex_i18n::msg('consent_manager_not_deletable'));
                break;
            }
            $db = rex_sql::factory();
            $db->setTable(rex::getTable('consent_manager_cookie'));
            $db->setWhere('uid = :uid', ['uid' => $v['uid']]);
            $db->delete();
        }
        return $msg;
    }

    /**
     * @param rex_extension_point<object> $ep
     * @return void
     * @api
     */
    public static function addLangNav(rex_extension_point $ep)
    {
        if (rex::isBackend() && null !== rex::getUser()) {
            foreach (consent_manager_config::getKeys() as $key) {
                if ('domain' === $key) {
                    continue;
                }
                $page = rex_be_controller::getPageObject('consent_manager/' . $key);
                if (null === $page) {
                    continue;
                }
                if (null !== rex_be_controller::getCurrentPagePart(3, '')) {
                    $clang_id = (int) str_replace('clang', '', rex_be_controller::getCurrentPagePart(3, ''));
                } else {
                    $clang_id = rex_clang::getStartId();
                }
                foreach (rex_clang::getAll() as $id => $clang) {
                    $page->addSubpage((new rex_be_page('clang' . $id, $clang->getName()))
                        ->setSubPath(rex_path::addon('consent_manager', 'pages/' . $key . '.php'))
                        ->setIsActive($id === $clang_id),
                    );
                }
            }
        }
    }

    /**
     * @param rex_extension_point<object> $ep
     * @return bool
     * @api
     */
    public static function formSaved(rex_extension_point $ep)
    {
        $form = $ep->getParams()['form'];
        $params = $ep->getParams();
        if (!in_array($form->getTableName(), consent_manager_config::getTables(true), true)) {
            return true;
        }
        if (!$form->isEditMode()) {
            self::insertDataset($form, $params);
        }
        if ($form->isEditMode() && (int) $form->getSql()->getValue('clang_id') === rex_clang::getStartId()) {
            self::updateDataset($form);
        }
        return false;
    }

    /**
     * @param rex_form $form
     * @param array<rex_sql> $params
     * @return void
     */
    private static function insertDataset($form, $params)
    {
        $db = rex_sql::factory();
        $db->setTable($form->getTableName());
        $db->setWhere('pid = ' . $db->escape($params['sql']->getLastId())); /** @phpstan-ignore-line */
        $db->select('*');
        $inserted = $db->getArray()[0];
        foreach (rex_clang::getAllIds() as $clangId) {
            if ((int) $inserted['clang_id'] === $clangId) {
                continue;
            }

            $db = rex_sql::factory();
            $db->setTable($form->getTableName());
            foreach ($inserted as $k => $v) {
                if ('pid' === $k) {
                    continue;
                }
                if ('clang_id' === $k) {
                    $db->setValue($k, $clangId);
                } else {
                    $db->setValue($k, $v);
                }
            }
            $db->insert();
        }
    }

    /**
     * @param rex_form $form
     * @return bool
     */
    private static function updateDataset($form)
    {
        $fields2Update = [];
        $newValues = [];
        if (rex::getTable('consent_manager_cookiegroup') === $form->getTableName()) {
            $fields2Update = ['domain', 'uid', 'prio', 'required', 'cookie', 'script'];
            $db = rex_sql::factory();
            $db->setTable($form->getTableName());
            $db->setWhere('pid = :pid', ['pid' => $form->getSql()->getValue('pid')]);
            $db->select(implode(',', $fields2Update));
            $newValues = $db->getArray()[0];
        } elseif (rex::getTable('consent_manager_cookie') === $form->getTableName()) {
            $fields2Update = ['uid', 'script'];
            $db = rex_sql::factory();
            $db->setTable($form->getTableName());
            $db->setWhere('pid = :pid', ['pid' => $form->getSql()->getValue('pid')]);
            $db->select(implode(',', $fields2Update));
            $newValues = $db->getArray()[0];
        } else {
            return true;
        }
        foreach (rex_clang::getAllIds() as $clangId) {
            if ($form->getSql()->getValue('clang_id') === $clangId) {
                continue;
            }
            $db = rex_sql::factory();
            $db->setTable($form->getTableName());
            $db->setWhere('clang_id = :clang_id AND id = :id', ['clang_id' => $clangId, 'id' => $form->getSql()->getValue('id')]);
            foreach ($fields2Update as $v) {
                $db->setValue($v, $newValues[$v]);
            }
            $db->update();
        }
        return false;
    }

    /**
     * @param rex_extension_point<object> $ep
     * @return void
     * @api
     */
    public static function clangDeleted(rex_extension_point $ep)
    {
        foreach (consent_manager_config::getTables(true) as $table) {
            $deleteLang = rex_sql::factory();
            $deleteLang->setQuery('DELETE FROM ' . $table . ' WHERE clang_id=?', [$ep->getParam('clang')->getId()]); /** @phpstan-ignore-line */
            consent_manager_cache::forceWrite();
        }
    }

    /**
     * @return bool
     */
    public static function addonJustInstalled()
    {
        $clangIds = rex_clang::getAllIds();
        if (1 === count($clangIds)) {
            return true;
        }
        foreach ($clangIds as $clangId) {
            if ($clangId === rex_clang::getStartId()) {
                continue;
            }
            self::addClang($clangId);
        }
        return false;
    }

    /**
     * @param int $clangId
     * @return void
     */
    private static function addClang($clangId)
    {
        foreach (consent_manager_config::getTables(true) as $table) {
            $firstLang = rex_sql::factory();
            $firstLang->setTable($table);
            $firstLang->setWhere('clang_id='.rex_clang::getStartId());
            $firstLang->select();
            $fields = $firstLang->getFieldnames();

            $newLang = rex_sql::factory();
            foreach ($firstLang as $firstLangEntry) {
                $newLang->setTable($table);
                foreach ($fields as $key => $value) {
                    if ('pid' === $value) {
                        echo '';
                    } elseif ('clang_id' === $value) {
                        $newLang->setValue('clang_id', $clangId);
                    } else {
                        $newLang->setValue($value, $firstLangEntry->getValue($value));
                    }
                }
                $newLang->insert();
            }
        }
    }

    /**
     * @param rex_extension_point<object> $ep
     * @return void
     * @api
     */
    public static function clangAdded(rex_extension_point $ep)
    {
        self::addClang($ep->getParam('clang')->getId()); /** @phpstan-ignore-line */
        consent_manager_cache::forceWrite();
    }
}
