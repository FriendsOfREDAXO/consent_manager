<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 *
 * Fragment: Einrichtungs-Checkliste
 * Wird auf allen Unterseiten angezeigt, solange Domain, Gruppen oder Dienste fehlen.
 *
 * Fragment-Schnittstelle:
 * - Erwartete Variablen via `$this->getVar(...)`: keine
 */

$sql = rex_sql::factory();
$count = static function (string $table) use ($sql): int {
    return (int) $sql->setQuery('SELECT COUNT(*) AS cnt FROM ' . rex::getTable($table))->getValue('cnt');
};

$steps = [
    'domain' => ['done' => $count('consent_manager_domain') > 0, 'page' => 'consent_manager/domain'],
    'groups' => ['done' => $count('consent_manager_cookiegroup') > 0, 'page' => 'consent_manager/cookiegroup'],
    'services' => ['done' => $count('consent_manager_cookie') > 0, 'page' => 'consent_manager/cookie'],
];

if (!in_array(false, array_column($steps, 'done'), true)) {
    return;
}

$items = '';
foreach ($steps as $key => $step) {
    $icon = $step['done']
        ? '<i class="rex-icon fa-solid fa-circle-check text-success" aria-hidden="true"></i> '
        : '<i class="rex-icon fa-regular fa-circle text-muted" aria-hidden="true"></i> ';
    $items .= '<li>' . $icon
        . '<a href="' . rex_url::backendPage($step['page']) . '"><strong>' . rex_i18n::msg('consent_manager_onboarding_step_' . $key) . '</strong></a>'
        . ' – ' . rex_i18n::msg('consent_manager_onboarding_step_' . $key . '_desc')
        . '</li>';
}

$body = '<p>' . rex_i18n::msg('consent_manager_onboarding_intro') . '</p>'
    . '<ol>' . $items . '</ol>'
    . '<p>' . rex_i18n::rawMsg('consent_manager_onboarding_afterwards', rex_url::backendPage('consent_manager/text'), rex_url::backendPage('consent_manager/help')) . '</p>'
    . '<p><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#setup-wizard-modal"><i class="rex-icon fa-magic"></i> ' . rex_i18n::msg('consent_manager_onboarding_wizard') . '</button>'
    . ' <span class="text-muted">' . rex_i18n::msg('consent_manager_onboarding_manual') . '</span></p>';

$section = new rex_fragment();
$section->setVar('title', rex_i18n::msg('consent_manager_onboarding_title'));
$section->setVar('body', $body, false);
echo $section->parse('core/page/section.php');
