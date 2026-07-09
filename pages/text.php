<?php

use FriendsOfRedaxo\ConsentManager\CLang;
use FriendsOfRedaxo\ConsentManager\RexFormSupport;
use FriendsOfRedaxo\ConsentManager\UidRenameWorkflow;

$showlist = true;
$pid = rex_request::request('pid', 'int', 0);
$func = rex_request::request('func', 'string');
$csrf = rex_csrf_token::factory('consent_manager_text');
$clang_id = (int) str_replace('clang', '', rex_be_controller::getCurrentPagePart(3) ?? '');
$table = rex::getTable('consent_manager_text');
$startClangId = rex_clang::getStartId();
$msg = '';
$renameOpen = false;
$renameMode = '';
$renameResult = null;
$renameOldUid = '';
$renamePid = rex_request::request('rename_pid', 'int', 0);
$approvedDryrunToken = '';
$buildRenameToken = static function (string $oldUid, string $newUid): string {
    return hash('sha256', 'text|' . $oldUid . '|' . $newUid);
};

if ('add' === $func && $clang_id !== $startClangId) {
    header('Location: ' . rex_url::backendPage('consent_manager/text/clang' . $startClangId, ['func' => 'add', 'uid_primary_only' => 1]));
    exit;
}

if (1 === rex_request::request('uid_primary_only', 'int', 0)) {
    $msg .= rex_view::warning(rex_i18n::msg('consent_manager_uid_primary_only_notice'));
}

if ('uid_rename_open' === $func) {
    if ($renamePid > 0) {
        $uidSql = rex_sql::factory();
        $uidSql->setQuery('SELECT uid FROM ' . $table . ' WHERE pid = ? LIMIT 1', [$renamePid]);
        if ($uidSql->getRows() > 0) {
            $renameOldUid = (string) $uidSql->getValue('uid');
            $renameOpen = true;
        }
    }
    $func = '';
}

if ('uid_rename_dryrun' === $func || 'uid_rename_apply' === $func) {
    if (!$csrf->isValid()) {
        $msg = rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } elseif ($clang_id !== $startClangId) {
        $msg = rex_view::error(rex_i18n::msg('consent_manager_uid_primary_only_notice'));
    } else {
        $renameOldUid = rex_request::post('old_uid', 'string', '');
        $renameNewUid = rex_request::post('new_uid', 'string', '');
        $expectedToken = $buildRenameToken($renameOldUid, $renameNewUid);
        $postedDryrunToken = rex_request::post('dryrun_token', 'string', '');

        if ('uid_rename_dryrun' === $func) {
            $renameResult = UidRenameWorkflow::dryRun('text', $renameOldUid, $renameNewUid);
            $renameMode = 'dryrun';
            if (is_array($renameResult) && ($renameResult['ok'] ?? false)) {
                $approvedDryrunToken = $expectedToken;
            }
        } else {
            if ($postedDryrunToken !== $expectedToken) {
                $renameResult = UidRenameWorkflow::dryRun('text', $renameOldUid, $renameNewUid);
                $renameMode = 'dryrun';
                if (is_array($renameResult) && ($renameResult['ok'] ?? false)) {
                    $approvedDryrunToken = $expectedToken;
                }
                $msg = rex_view::error('Vor der Umbenennung muss ein Dry-Run fuer genau diesen Schluessel ausgefuehrt werden. Bitte Hinweise und moegliche Nacharbeit pruefen.');
                $renameOpen = true;
                $func = '';
            } else {
                $renameResult = UidRenameWorkflow::apply('text', $renameOldUid, $renameNewUid);
                $renameMode = 'apply';
            }
        }

        if ('' === $msg) {
            if (is_array($renameResult) && ($renameResult['ok'] ?? false)) {
                $msg = rex_view::success('dryrun' === $renameMode ? 'Dry-Run erfolgreich. Bitte Hinweise pruefen.' : 'Umbenennung erfolgreich ausgefuehrt.');
            } else {
                $msg = rex_view::error('Dry-Run/Umbenennung fehlgeschlagen. Details siehe Dialog.');
            }
        }

        $renameOpen = true;
    }
    $func = '';
}
if ('delete' === $func) {
    CLang::deleteDataset($table, $pid);
} elseif ('add' === $func || 'edit' === $func) {
    $formDebug = false;
    $showlist = false;
    $form = rex_form::factory($table, '', 'pid = ' . $pid, 'post', $formDebug);
    $form->addParam('pid', $pid);
    $form->addParam('sort', rex_request::request('sort', 'string', ''));
    $form->addParam('sorttype', rex_request::request('sorttype', 'string', ''));
    $form->addParam('start', rex_request::request('start', 'int', 0));
    $form->setApplyUrl(rex_url::currentBackendPage());
    $form->addHiddenField('clang_id', $clang_id);
    RexFormSupport::getId($form, $table);

    if ($form->isEditMode()) {
        $field = $form->addReadOnlyField('uid_readonly', (string) $form->getSql()->getValue('uid'));
        $field->setLabel(rex_i18n::msg('consent_manager_uid'));
        $form->addHiddenField('uid', $form->getSql()->getValue('uid'));
    } else {
        $field = $form->addTextField('uid');
        $field->setLabel(rex_i18n::msg('consent_manager_uid_with_hint'));
        $field->getValidator()->add('notEmpty', rex_i18n::msg('consent_manager_uid_empty_msg'));
        $field->getValidator()->add('match', rex_i18n::msg('consent_manager_uid_malformed_msg'), '/^[a-z0-9-_]+$/');
    }

    $field = $form->addTextAreaField('text');
    $field->setLabel(rex_i18n::msg('consent_manager_text'));

    $title = $form->isEditMode() ? rex_i18n::msg('consent_manager_text_edit') : rex_i18n::msg('consent_manager_text_add');
    $content = $form->get();

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', $title);
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
}

if ($showlist) {
    $isTranslatedTextUid = static function (string $uid) use ($table, $clang_id): bool {
        if ($clang_id === rex_clang::getStartId()) {
            return false;
        }

        static $cache = [];
        $cacheKey = $clang_id . '|' . $uid;
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $sqlCompare = rex_sql::factory();
        $sqlCompare->setQuery('SELECT text FROM ' . $table . ' WHERE uid = ? AND clang_id = ? ORDER BY pid ASC LIMIT 1', [$uid, $clang_id]);
        if ($sqlCompare->getRows() === 0) {
            $cache[$cacheKey] = false;
            return false;
        }
        $targetText = trim((string) $sqlCompare->getValue('text'));

        $sqlBase = rex_sql::factory();
        $sqlBase->setQuery('SELECT text FROM ' . $table . ' WHERE uid = ? AND clang_id = ? ORDER BY pid ASC LIMIT 1', [$uid, rex_clang::getStartId()]);
        if ($sqlBase->getRows() === 0) {
            $cache[$cacheKey] = false;
            return false;
        }
        $startText = trim((string) $sqlBase->getValue('text'));

        $cache[$cacheKey] = '' !== $targetText && $targetText !== $startText;
        return $cache[$cacheKey];
    };

    $listDebug = false;
    $sql = 'SELECT pid,uid,text FROM ' . $table . ' WHERE clang_id = ' . $clang_id;

    $list = rex_list::factory($sql, 100, '', $listDebug);
    $list->addParam('page', rex_be_controller::getCurrentPage());
    $list->addTableAttribute('class', 'table table-striped table-hover consent_manager-table consent_manager-table-text');

    $list->removeColumn('pid');

    $tdIcon = '<i class="rex-icon rex-icon-edit"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '"' . rex::getAccesskey(rex_i18n::msg('add'), 'add') . '><i class="rex-icon rex-icon-add"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'pid' => '###pid###']);

    $list->setColumnLabel('uid', rex_i18n::msg('consent_manager_uid'));
    $list->setColumnParams('uid', ['func' => 'edit', 'pid' => '###pid###']);
    $list->setColumnFormat('uid', 'custom', static function (array $params): string {
        return rex_escape((string) $params['value']);
    });

    $translationStatusHeader = '<i class="rex-icon fa-language" title="Uebersetzung"></i>';
    $list->addColumn($translationStatusHeader, '', 2, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnFormat($translationStatusHeader, 'custom', static function (array $params) use ($isTranslatedTextUid): string {
        $uid = (string) $params['list']->getValue('uid');
        $translated = $isTranslatedTextUid($uid);
        $color = $translated ? '#3cba54' : '#9aa0a6';
        $title = $translated ? 'Uebersetzt' : 'Nicht uebersetzt';

        return '<i class="rex-icon fa-language" title="' . $title . '" style="color:' . $color . ';"></i>';
    });

    $list->setColumnLabel('text', rex_i18n::msg('consent_manager_text'));

    $list->addColumn(rex_i18n::msg('function'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('function'), ['<th class="rex-table-action" colspan="3">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('function'), ['pid' => '###pid###', 'func' => 'edit', 'start' => rex_request::request('start', 'string')]);

    $list->addColumn(rex_i18n::msg('consent_manager_rename'), '<i class="rex-icon fa-exchange"></i> ' . rex_i18n::msg('consent_manager_rename'));
    $list->setColumnLayout(rex_i18n::msg('consent_manager_rename'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('consent_manager_rename'), ['func' => 'uid_rename_open', 'rename_pid' => '###pid###', 'start' => rex_request::request('start', 'string')]);

    $content = $list->get();

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('consent_manager_cookies'));
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');

    $renameNewUidValue = rex_request::post('new_uid', 'string', '');
    $currentDryrunToken = $approvedDryrunToken;
    if ('' === $currentDryrunToken && 'dryrun' === $renameMode && is_array($renameResult) && ($renameResult['ok'] ?? false) && '' !== $renameOldUid && '' !== $renameNewUidValue) {
        $currentDryrunToken = $buildRenameToken($renameOldUid, $renameNewUidValue);
    }
    $applyDisabled = '' === $currentDryrunToken;
    if ($renameOpen):
?>
<div class="modal fade in" id="cm-text-rename-modal" tabindex="-1" role="dialog" aria-hidden="false" style="display:block;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="window.location='<?= rex_url::currentBackendPage(['func' => '', 'rename_pid' => 0, 'start' => rex_request::request('start', 'string')]) ?>';"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="rex-icon fa-exchange"></i> <?= rex_i18n::msg('consent_manager_rename') ?></h4>
            </div>
            <div class="modal-body">
                <form action="<?= rex_url::currentBackendPage() ?>" method="post" id="cm-text-rename-form">
                    <?= $csrf->getHiddenField() ?>
                    <input type="hidden" name="old_uid" value="<?= rex_escape($renameOldUid) ?>">
                    <input type="hidden" name="func" id="cm-text-rename-func" value="">
                    <input type="hidden" name="dryrun_token" id="cm-text-rename-token" value="<?= rex_escape($currentDryrunToken) ?>">
                    <input type="hidden" name="start" value="<?= rex_escape(rex_request::request('start', 'string')) ?>">

                    <div class="alert alert-info" style="margin-bottom: 12px;">
                        Vor dem Umbenennen ist ein Dry-Run verpflichtend. Bitte pruefen Sie Auswirkungen, Hinweise und moegliche manuelle Nacharbeit.
                    </div>

                    <div class="form-group">
                        <label>Aktueller Schluessel</label>
                        <input type="text" class="form-control" value="<?= rex_escape($renameOldUid) ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="cm-text-rename-new">Neuer Schluessel</label>
                        <input id="cm-text-rename-new" type="text" class="form-control" name="new_uid" value="<?= rex_escape($renameNewUidValue) ?>" required>
                    </div>
                </form>

                <?php if (is_array($renameResult)): ?>
                    <hr>
                    <?php $impact = is_array($renameResult['impact'] ?? null) ? $renameResult['impact'] : []; ?>
                    <div class="well" style="margin-bottom:10px; padding:10px;">
                        <div><strong>Modus:</strong> <?= rex_escape($renameMode) ?></div>
                        <div><strong>Treffer Datensaetze:</strong> <?= (int) ($impact['source_rows'] ?? 0) ?></div>
                        <div><strong>Betroffene Sprachen:</strong> <?= (int) ($impact['affected_clangs'] ?? 0) ?></div>
                    </div>

                    <?php $errors = is_array($renameResult['errors'] ?? null) ? $renameResult['errors'] : []; ?>
                    <?php if ([] !== $errors): ?>
                        <div class="alert alert-danger"><strong>Fehler</strong><ul style="margin:8px 0 0 18px;"><?php foreach ($errors as $error): ?><li><?= rex_escape((string) $error) ?></li><?php endforeach ?></ul></div>
                    <?php endif ?>

                    <?php $warnings = is_array($renameResult['warnings'] ?? null) ? $renameResult['warnings'] : []; ?>
                    <?php if ([] !== $warnings): ?>
                        <div class="alert alert-warning"><strong>Hinweise</strong><ul style="margin:8px 0 0 18px;"><?php foreach ($warnings as $warning): ?><li><?= rex_escape((string) $warning) ?></li><?php endforeach ?></ul></div>
                    <?php endif ?>

                    <?php $manualActions = is_array($renameResult['manual_actions'] ?? null) ? $renameResult['manual_actions'] : []; ?>
                    <?php if ([] !== $manualActions): ?>
                        <div class="alert alert-info"><strong>Moegliche manuelle Nacharbeit</strong><ul style="margin:8px 0 0 18px;"><?php foreach ($manualActions as $manualAction): ?><li><?= rex_escape((string) $manualAction) ?></li><?php endforeach ?></ul></div>
                    <?php endif ?>
                <?php endif ?>
            </div>
            <div class="modal-footer">
                <a class="btn btn-default" href="<?= rex_url::currentBackendPage(['func' => '', 'rename_pid' => 0, 'start' => rex_request::request('start', 'string')]) ?>">Schliessen</a>
                <button type="button" class="btn btn-warning" onclick="document.getElementById('cm-text-rename-func').value='uid_rename_dryrun'; document.getElementById('cm-text-rename-form').submit();"><i class="rex-icon fa-search"></i> Dry-Run</button>
                <button type="button" class="btn btn-danger<?= $applyDisabled ? ' disabled' : '' ?>"<?= $applyDisabled ? ' title="Bitte zuerst Dry-Run ausfuehren." aria-disabled="true"' : '' ?> onclick="if (this.classList.contains('disabled')) { return false; } if (confirm('Umbenennung jetzt ausfuehren? Hinweise wurden geprueft?')) { document.getElementById('cm-text-rename-func').value='uid_rename_apply'; document.getElementById('cm-text-rename-form').submit(); }"><i class="rex-icon fa-play"></i> Umbenennen</button>
            </div>
        </div>
    </div>
</div>
<div class="modal-backdrop fade in"></div>
<script nonce="<?= rex_response::getNonce() ?>">
    jQuery(function () {
        var input = document.getElementById('cm-text-rename-new');
        if (input) {
            input.focus();
            input.select();
        }
    });
</script>
<?php
    endif;
}

echo $msg;
