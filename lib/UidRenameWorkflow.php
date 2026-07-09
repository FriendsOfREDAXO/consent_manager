<?php

namespace FriendsOfRedaxo\ConsentManager;

use rex;
use rex_clang;
use rex_sql;

use function array_values;
use function count;
use function in_array;
use function preg_match;
use function trim;

class UidRenameWorkflow
{
    /**
     * @return array<string, mixed>
     */
    public static function dryRun(string $type, string $oldUid, string $newUid): array
    {
        $normalizedType = self::normalizeType($type);
        $oldUid = trim($oldUid);
        $newUid = trim($newUid);

        $errors = [];
        $warnings = [];
        $manualActions = [];

        if (null === $normalizedType) {
            $errors[] = 'Ungueltiger Bereich. Erlaubt: cookie, cookiegroup, text.';
            return self::result(false, $errors, $warnings, $manualActions, []);
        }

        if ('' === $oldUid || '' === $newUid) {
            $errors[] = 'Alte und neue UID muessen gesetzt sein.';
            return self::result(false, $errors, $warnings, $manualActions, []);
        }

        if ($oldUid === $newUid) {
            $errors[] = 'Alte und neue UID sind identisch.';
            return self::result(false, $errors, $warnings, $manualActions, []);
        }

        if (!self::isValidUid($normalizedType, $newUid)) {
            $errors[] = 'Neue UID ist ungueltig (erlaubt sind a-z, 0-9, - und je nach Bereich _).';
            return self::result(false, $errors, $warnings, $manualActions, []);
        }

        if ('cookie' === $normalizedType && in_array($oldUid, ['consent_manager', 'consentmanager'], true)) {
            $errors[] = 'System-Cookie kann nicht per Rename-Workflow umbenannt werden.';
            return self::result(false, $errors, $warnings, $manualActions, []);
        }

        $mainTable = self::tableForType($normalizedType);

        $sourceCount = self::countByUid($mainTable, $oldUid);
        $targetCount = self::countByUid($mainTable, $newUid);

        if (0 === $sourceCount) {
            $errors[] = 'Die Quell-UID wurde im gewaehlten Bereich nicht gefunden.';
        }

        if ($targetCount > 0) {
            $errors[] = 'Die Ziel-UID existiert bereits im gewaehlten Bereich.';
        }

        $sourcePerClang = self::countByUidPerClang($mainTable, $oldUid);
        $affectedClangCount = count($sourcePerClang);

        $impact = [
            'type' => $normalizedType,
            'table' => $mainTable,
            'old_uid' => $oldUid,
            'new_uid' => $newUid,
            'source_rows' => $sourceCount,
            'target_rows' => $targetCount,
            'affected_clangs' => $affectedClangCount,
            'per_clang' => $sourcePerClang,
            'cookiegroup_cookie_refs' => 0,
            'consent_log_refs' => 0,
        ];

        if ('cookie' === $normalizedType) {
            $impact['cookiegroup_cookie_refs'] = self::countCookiegroupRefs($oldUid);
            if ((int) $impact['cookiegroup_cookie_refs'] > 0) {
                $warnings[] = 'Cookie-Gruppen-Referenzen werden beim Apply automatisch angepasst.';
            }
            $manualActions[] = 'Pruefen Sie Inhalte/Module mit data-service="' . $oldUid . '" und data-consent-service="' . $oldUid . '".';
        }

        if ('cookiegroup' === $normalizedType) {
            $impact['consent_log_refs'] = self::countConsentLogRefs($oldUid);
            if ((int) $impact['consent_log_refs'] > 0) {
                $warnings[] = 'Consent-Log-Eintraege koennen beim Apply mit umbenannt werden.';
            }
            $manualActions[] = 'Bestehende Browser-Cookies bei Endnutzern koennen nicht serverseitig migriert werden.';
        }

        if ('text' === $normalizedType) {
            $manualActions[] = 'Pruefen Sie ggf. manuelle Referenzen in eigenen Templates/Modulen auf die Text-UID.';
        }

        if ($affectedClangCount < count(rex_clang::getAllIds())) {
            $warnings[] = 'UID ist nicht in allen Sprachen vorhanden. Rename wirkt nur auf vorhandene Sprachdatensaetze.';
        }

        return self::result([] === $errors, $errors, $warnings, $manualActions, $impact);
    }

    /**
     * @return array<string, mixed>
     */
    public static function apply(string $type, string $oldUid, string $newUid, bool $updateConsentLogs = true): array
    {
        $dryRun = self::dryRun($type, $oldUid, $newUid);
        if (!($dryRun['ok'] ?? false)) {
            return $dryRun;
        }

        $normalizedType = (string) $dryRun['impact']['type'];
        $mainTable = (string) $dryRun['impact']['table'];
        $oldUid = (string) $dryRun['impact']['old_uid'];
        $newUid = (string) $dryRun['impact']['new_uid'];

        $sql = rex_sql::factory();
        $changed = [
            'main_rows' => 0,
            'cookiegroup_cookie_refs' => 0,
            'consent_log_refs' => 0,
        ];

        try {
            $sql->setQuery('START TRANSACTION');

            $mainUpdate = rex_sql::factory();
            $mainUpdate->setQuery('UPDATE ' . $mainTable . ' SET uid = ? WHERE uid = ?', [$newUid, $oldUid]);
            $changed['main_rows'] = $mainUpdate->getRows();

            if ('cookie' === $normalizedType) {
                $tokenOld = '|' . $oldUid . '|';
                $tokenNew = '|' . $newUid . '|';

                $cookieGroupUpdate = rex_sql::factory();
                $cookieGroupUpdate->setQuery(
                    'UPDATE ' . rex::getTable('consent_manager_cookiegroup') . ' SET cookie = REPLACE(cookie, ?, ?) WHERE cookie LIKE ?',
                    [$tokenOld, $tokenNew, '%|' . $oldUid . '|%'],
                );
                $changed['cookiegroup_cookie_refs'] = $cookieGroupUpdate->getRows();
            }

            if ('cookiegroup' === $normalizedType && $updateConsentLogs) {
                $logOldToken = '"' . $oldUid . '"';
                $logNewToken = '"' . $newUid . '"';

                $logUpdate = rex_sql::factory();
                $logUpdate->setQuery(
                    'UPDATE ' . rex::getTable('consent_manager_consent_log') . ' SET consents = REPLACE(consents, ?, ?) WHERE consents LIKE ?',
                    [$logOldToken, $logNewToken, '%"' . $oldUid . '"%'],
                );
                $changed['consent_log_refs'] = $logUpdate->getRows();
            }

            $sql->setQuery('COMMIT');
        } catch (\Throwable $e) {
            $sql->setQuery('ROLLBACK');
            return self::result(false, ['Apply fehlgeschlagen: ' . $e->getMessage()], [], [], $dryRun['impact']);
        }

        Cache::forceWrite();

        $result = self::result(true, [], $dryRun['warnings'], $dryRun['manual_actions'], $dryRun['impact']);
        $result['changed'] = $changed;

        return $result;
    }

    private static function normalizeType(string $type): ?string
    {
        $type = trim($type);
        if (in_array($type, ['cookie', 'cookiegroup', 'text'], true)) {
            return $type;
        }

        return null;
    }

    private static function tableForType(string $type): string
    {
        if ('cookie' === $type) {
            return rex::getTable('consent_manager_cookie');
        }
        if ('cookiegroup' === $type) {
            return rex::getTable('consent_manager_cookiegroup');
        }

        return rex::getTable('consent_manager_text');
    }

    private static function isValidUid(string $type, string $uid): bool
    {
        if ('cookiegroup' === $type) {
            return 1 === preg_match('/^[a-z0-9-]+$/', $uid);
        }

        return 1 === preg_match('/^[a-z0-9-_]+$/', $uid);
    }

    private static function countByUid(string $table, string $uid): int
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT COUNT(*) AS cnt FROM ' . $table . ' WHERE uid = ?', [$uid]);
        return (int) $sql->getValue('cnt');
    }

    /**
     * @return array<int, int>
     */
    private static function countByUidPerClang(string $table, string $uid): array
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT clang_id, COUNT(*) AS cnt FROM ' . $table . ' WHERE uid = ? GROUP BY clang_id ORDER BY clang_id', [$uid]);

        $result = [];
        foreach ($sql->getArray() as $row) {
            $result[(int) $row['clang_id']] = (int) $row['cnt'];
        }

        return $result;
    }

    private static function countCookiegroupRefs(string $cookieUid): int
    {
        $sql = rex_sql::factory();
        $sql->setQuery(
            'SELECT COUNT(*) AS cnt FROM ' . rex::getTable('consent_manager_cookiegroup') . ' WHERE cookie LIKE ?',
            ['%|' . $cookieUid . '|%'],
        );

        return (int) $sql->getValue('cnt');
    }

    private static function countConsentLogRefs(string $cookiegroupUid): int
    {
        $sql = rex_sql::factory();
        $sql->setQuery(
            'SELECT COUNT(*) AS cnt FROM ' . rex::getTable('consent_manager_consent_log') . ' WHERE consents LIKE ?',
            ['%"' . $cookiegroupUid . '"%'],
        );

        return (int) $sql->getValue('cnt');
    }

    /**
     * @param array<string> $errors
     * @param array<string> $warnings
     * @param array<string> $manualActions
     * @param array<string, mixed> $impact
     * @return array<string, mixed>
     */
    private static function result(bool $ok, array $errors, array $warnings, array $manualActions, array $impact): array
    {
        return [
            'ok' => $ok,
            'errors' => array_values($errors),
            'warnings' => array_values($warnings),
            'manual_actions' => array_values($manualActions),
            'impact' => $impact,
        ];
    }
}
