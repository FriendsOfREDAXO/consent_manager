<?php

namespace FriendsOfRedaxo\ConsentManager;

use rex;
use rex_be_controller;
use rex_be_page;
use rex_clang;
use rex_extension_point;
use rex_form;
use rex_i18n;
use rex_path;
use rex_sql;
use rex_view;

use function count;
use function in_array;

class CLang
{
    /**
     * @api
     */
    public static function deleteDataset(string $table, int $pid): string
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
        Cache::forceWrite();
        return $msg;
    }

    /**
     * @api
     */
    public static function deleteCookie(int $pid): string
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
     * @param rex_extension_point<array<string,rex_be_page>> $ep
     * @api
     */
    public static function addLangNav(rex_extension_point $ep): void
    {
        if (rex::isBackend() && null !== rex::getUser()) {
            foreach (Config::getKeys() as $key) {
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
                    $tabTitle = $clang->getName() . self::getLangStatusSuffix($key, (int) $id);
                    $page->addSubpage(
                        (new rex_be_page('clang' . $id, $tabTitle))
                        ->setSubPath(rex_path::addon('consent_manager', 'pages/' . $key . '.php'))
                        ->setIsActive($id === $clang_id),
                    );
                }
            }
        }
    }

    private static function getLangStatusSuffix(string $pageKey, int $clangId): string
    {
        $status = self::getLangStatus($pageKey, $clangId);
        if (null === $status) {
            return '';
        }

        return ' ' . rex_i18n::rawMsg('consent_manager_lang_status_badge_' . $status);
    }

    private static function getLangStatus(string $pageKey, int $clangId): ?string
    {
        $startClangId = rex_clang::getStartId();
        if ($clangId === $startClangId) {
            return 'primary';
        }

        $table = self::getTableForPageKey($pageKey);
        if (null === $table) {
            return null;
        }

        $translatableFields = self::getTranslatableFields($table);
        if ([] === $translatableFields) {
            return null;
        }

        $startRows = self::getRowsByDatasetKey($table, $startClangId, $translatableFields);
        if ([] === $startRows) {
            return 'translated';
        }

        $targetRows = self::getRowsByDatasetKey($table, $clangId, $translatableFields);

        $translatedCount = 0;
        $totalCount = count($startRows);
        foreach ($startRows as $datasetKey => $startRow) {
            if (!isset($targetRows[$datasetKey])) {
                continue;
            }

            if (self::isDatasetTranslated($startRow, $targetRows[$datasetKey], $translatableFields)) {
                ++$translatedCount;
            }
        }

        if (0 === $translatedCount) {
            return 'primary_fallback';
        }
        if ($translatedCount === $totalCount) {
            return 'translated';
        }

        return 'partial';
    }

    private static function getTableForPageKey(string $pageKey): ?string
    {
        if ('cookie' === $pageKey) {
            return rex::getTable('consent_manager_cookie');
        }
        if ('cookiegroup' === $pageKey) {
            return rex::getTable('consent_manager_cookiegroup');
        }
        if ('text' === $pageKey) {
            return rex::getTable('consent_manager_text');
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private static function getTranslatableFields(string $table): array
    {
        if ($table === rex::getTable('consent_manager_cookie')) {
            return ['service_name', 'variant', 'provider', 'provider_link_privacy', 'definition', 'script', 'script_unselect', 'placeholder_text'];
        }
        if ($table === rex::getTable('consent_manager_cookiegroup')) {
            return ['name', 'description', 'script'];
        }
        if ($table === rex::getTable('consent_manager_text')) {
            return ['text'];
        }

        return [];
    }

    /**
     * @param list<string> $fields
     * @return array<string, array<string, mixed>>
     */
    private static function getRowsByDatasetKey(string $table, int $clangId, array $fields): array
    {
        $sql = rex_sql::factory();
        $sql->setTable($table);
        $sql->setWhere('clang_id = :clang_id', ['clang_id' => $clangId]);
        $sql->select('id,uid,' . implode(',', $fields));

        $rowsByKey = [];
        foreach ($sql->getArray() as $row) {
            if (!isset($row['id'])) {
                continue;
            }

            $datasetKey = '';
            if (isset($row['uid']) && '' !== trim((string) $row['uid'])) {
                $datasetKey = 'uid:' . trim((string) $row['uid']);
            } else {
                $datasetKey = 'id:' . (string) ((int) $row['id']);
            }

            $rowsByKey[$datasetKey] = $row;
        }

        return $rowsByKey;
    }

    /**
     * @param array<string, mixed> $startRow
     * @param array<string, mixed> $targetRow
     * @param list<string> $fields
     */
    private static function isDatasetTranslated(array $startRow, array $targetRow, array $fields): bool
    {
        foreach ($fields as $field) {
            $startValue = trim((string) ($startRow[$field] ?? ''));
            $targetValue = trim((string) ($targetRow[$field] ?? ''));

            if ('' !== $targetValue && $targetValue !== $startValue) {
                return true;
            }
        }

        return false;
    }

    /**
     * @api
     * @param rex_extension_point<bool> $ep
     */
    public static function formSaved(rex_extension_point $ep): bool
    {
        /** @var rex_form $form $form */
        $form = $ep->getParams()['form'];
        $params = $ep->getParams();
        if (!in_array($form->getTableName(), Config::getTables(true), true)) {
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
     * @param array<rex_sql> $params
     */
    private static function insertDataset(rex_form $form, array $params): void
    {
        $db = rex_sql::factory();
        $db->setTable($form->getTableName());
        $db->setWhere('pid = :pid', [':pid' => $params['sql']->getLastId()]);
        $db->select('*');
        $inserted = $db->getArray()[0] ?? [];
        if ([] === $inserted || !isset($inserted['id'], $inserted['clang_id'])) {
            return;
        }

        $datasetId = (int) $inserted['id'];
        $datasetUid = isset($inserted['uid']) ? trim((string) $inserted['uid']) : '';
        foreach (rex_clang::getAllIds() as $clangId) {
            if ((int) $inserted['clang_id'] === $clangId) {
                continue;
            }

            if (self::datasetExists($form->getTableName(), $datasetId, $clangId, $datasetUid)) {
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

    private static function updateDataset(rex_form $form): bool
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
     * @api
     * @param rex_extension_point<void> $ep
     */
    public static function clangDeleted(rex_extension_point $ep): void
    {
        foreach (Config::getTables(true) as $table) {
            $deleteLang = rex_sql::factory();
            $deleteLang->setQuery('DELETE FROM ' . $table . ' WHERE clang_id=?', [$ep->getParam('clang')->getId()]); /** @phpstan-ignore-line */
            Cache::forceWrite();
        }
    }

    /**
     * @api
     */
    public static function addonJustInstalled(): bool
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
     * @param list<int> $targetClangIds
     * @param list<string> $tables
     * @return array{inserted: int, per_table: array<string, int>}
     */
    public static function syncMissingFromSource(int $sourceClangId, array $targetClangIds, array $tables = []): array
    {
        $availableClangIds = rex_clang::getAllIds();
        if (!in_array($sourceClangId, $availableClangIds, true)) {
            return ['inserted' => 0, 'per_table' => []];
        }

        $validTargets = [];
        foreach ($targetClangIds as $targetClangId) {
            if ($targetClangId === $sourceClangId) {
                continue;
            }

            if (in_array($targetClangId, $availableClangIds, true)) {
                $validTargets[] = $targetClangId;
            }
        }

        if ([] === $validTargets) {
            return ['inserted' => 0, 'per_table' => []];
        }

        $allowedTables = Config::getTables(true);
        $tables2Sync = [];
        if ([] === $tables) {
            $tables2Sync = $allowedTables;
        } else {
            foreach ($tables as $table) {
                if (in_array($table, $allowedTables, true)) {
                    $tables2Sync[] = $table;
                }
            }
        }

        $insertedTotal = 0;
        $insertedPerTable = [];

        foreach ($tables2Sync as $table) {
            $sourceRowsSql = rex_sql::factory();
            $sourceRowsSql->setTable($table);
            $sourceRowsSql->setWhere('clang_id = :clang_id', ['clang_id' => $sourceClangId]);
            $sourceRowsSql->select('*');
            $sourceRows = $sourceRowsSql->getArray();

            if ([] === $sourceRows) {
                $insertedPerTable[$table] = 0;
                continue;
            }

            $insertedPerTable[$table] = 0;

            foreach ($validTargets as $targetClangId) {
                foreach ($sourceRows as $sourceRow) {
                    if (!isset($sourceRow['id'])) {
                        continue;
                    }

                    $datasetId = (int) $sourceRow['id'];
                    $datasetUid = isset($sourceRow['uid']) ? trim((string) $sourceRow['uid']) : '';
                    if (self::datasetExists($table, $datasetId, $targetClangId, $datasetUid)) {
                        continue;
                    }

                    $insertSql = rex_sql::factory();
                    $insertSql->setTable($table);
                    foreach ($sourceRow as $fieldName => $fieldValue) {
                        if ('pid' === $fieldName) {
                            continue;
                        }
                        if ('clang_id' === $fieldName) {
                            $insertSql->setValue('clang_id', $targetClangId);
                        } else {
                            $insertSql->setValue($fieldName, $fieldValue);
                        }
                    }
                    $insertSql->insert();
                    ++$insertedTotal;
                    ++$insertedPerTable[$table];
                }
            }
        }

        if ($insertedTotal > 0) {
            Cache::forceWrite();
        }

        return ['inserted' => $insertedTotal, 'per_table' => $insertedPerTable];
    }

    private static function addClang(int $clangId): void
    {
        foreach (Config::getTables(true) as $table) {
            $firstLang = rex_sql::factory();
            $firstLang->setTable($table);
            $firstLang->setWhere('clang_id=' . rex_clang::getStartId());
            $firstLang->select();
            $fields = $firstLang->getFieldnames();

            $newLang = rex_sql::factory();
            foreach ($firstLang as $firstLangEntry) {
                $datasetId = (int) $firstLangEntry->getValue('id');
                $datasetUid = trim((string) $firstLangEntry->getValue('uid'));
                if (self::datasetExists($table, $datasetId, $clangId, $datasetUid)) {
                    continue;
                }

                $newLang->setTable($table);
                foreach ($fields as $key => $value) {
                    if ('pid' === $value) {
                        continue;
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

    private static function datasetExists(string $table, int $datasetId, int $clangId, string $datasetUid = ''): bool
    {
        $check = rex_sql::factory();
        $check->setTable($table);

        if ('' !== $datasetUid) {
            $check->setWhere('uid = :uid AND clang_id = :clang_id', ['uid' => $datasetUid, 'clang_id' => $clangId]);
        } else {
            $check->setWhere('id = :id AND clang_id = :clang_id', ['id' => $datasetId, 'clang_id' => $clangId]);
        }

        $check->select('pid');

        return $check->getRows() > 0;
    }

    /**
     * @api
     * @param rex_extension_point<void> $ep
     */
    public static function clangAdded(rex_extension_point $ep): void
    {
        self::addClang($ep->getParam('clang')->getId()); /** @phpstan-ignore-line */
        Cache::forceWrite();
    }
}
