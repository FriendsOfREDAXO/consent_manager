<?php

$searchvalue = rex_request::request('Consent_Search', 'string', '');
$where = '';

if ('' !== $searchvalue) {
    $dosearch = str_replace(['\'', ';', '"'], '', $searchvalue);
    $sql = rex_sql::factory();
    if (false !== DateTime::createFromFormat('d.m.Y', $searchvalue)) {
        $searchdate = strtotime($dosearch);
        if (false !== $searchdate) {
            $where = ' WHERE `createdate` LIKE ' . $sql->escape(date('Y-m-d', $searchdate) . '%') . ' ';
        }
    }
    $intbool = (bool) preg_match('/^[1-9][0-9]{0,15}$/', $dosearch);
    if (true === $intbool) {
        $where = ' WHERE `cachelogid` = ' . $sql->escape($dosearch) . ' ';
    }
    if ('' === $where) {
        $where = ' WHERE `domain` LIKE ' . $sql->escape($dosearch . '%') . ' OR `ip` LIKE ' . $sql->escape($dosearch . '%') . ' OR `consentid` LIKE ' . $sql->escape($dosearch . '%') . ' ';
    }
}

$list = rex_list::factory(
    '
    SELECT `id`, `createdate`, `domain`, `ip`, `consents`, `cachelogid`, `consentid`
    FROM ' . rex::getTable('consent_manager_consent_log') . ' ' . $where . '
    ORDER by `createdate` DESC, `cachelogid` ASC
    ',
    30,
    'Consent-Log',
    false,
);

if ('' !== $searchvalue) {
    $list->addParam('Consent_Search', $searchvalue);
}

$list->addTableColumnGroup([40, 160, 120, 120, '*', 40, 200]);

$list->setColumnSortable('id', 'asc');
$list->setColumnSortable('createdate', 'desc');
$list->setColumnSortable('domain', 'asc');
$list->setColumnSortable('cachelogid', 'asc');

$list->setColumnLabel('id', rex_i18n::msg('consent_manager_thead_id'));
$list->setColumnLabel('createdate', rex_i18n::msg('consent_manager_thead_createdate'));
$list->setColumnLabel('domain', rex_i18n::msg('consent_manager_thead_domain'));
$list->setColumnLabel('ip', rex_i18n::msg('consent_manager_thead_ip'));
$list->setColumnLabel('consents', rex_i18n::msg('consent_manager_thead_consents'));
$list->setColumnLabel('cachelogid', rex_i18n::msg('consent_manager_thead_cachelogid'));
$list->setColumnLabel('consentid', rex_i18n::msg('consent_manager_thead_consentid'));

$list->setColumnFormat('createdate', 'custom', static function ($params) {
    /** @var rex_list $list */
    $list = $params['list'];
    $str = date('d.m.Y H:i:s', (int) strtotime((string) $list->getValue('createdate')));
    return $str;
});
$list->setColumnFormat('consents', 'custom', static function ($params) {
    /** @var rex_list $list */
    $list = $params['list'];
    $consents = (array) json_decode((string) $list->getValue('consents'));
    $str = implode(', ', $consents);
    return $str;
});
$list->setNoRowsMessage(rex_i18n::msg('list_no_rows'));

$list->addTableAttribute('class', 'table table-striped table-hover');

$fragmentsearch = new rex_fragment();
$fragmentsearch->setVar('id', 'consent_manager_log_search');
$fragmentsearch->setVar('autofocus', false);
$fragmentsearch->setVar('value', $searchvalue);
$cmsearch = $fragmentsearch->parse('core/form/search.php');

$statsBtn = '<button class="btn btn-info" id="btn-consent-stats" style="margin-right: 10px;"><i class="rex-icon fa-bar-chart"></i> ' . rex_i18n::msg('consent_manager_stats') . '</button>';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('consent_manager_thead_title'));
$fragment->setVar('options', '<div style="display:flex; justify-content:flex-end; align-items:center;">' . $statsBtn . $cmsearch . '</div>', false);
$fragment->setVar('content', $list->get(), false);
echo $fragment->parse('core/page/section.php');

?>
<!-- Modal -->
<div class="modal fade" id="consent-stats-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?= rex_i18n::msg('consent_manager_stats_title') ?></h4>
      </div>
      <div class="modal-body" id="consent-stats-body">
        <div class="text-center"><i class="rex-icon fa-spinner fa-spin fa-3x"></i></div>
      </div>
    </div>
  </div>
</div>

<script nonce="<?= rex_response::getNonce() ?>">
$(document).on('click', '#btn-consent-stats', function() {
    $('#consent-stats-modal').modal('show');
    loadConsentStats();
});

function loadConsentStats() {
    $('#consent-stats-body').html('<div class="text-center"><i class="rex-icon fa-spinner fa-spin fa-3x"></i></div>');
    $.ajax({
        url: 'index.php?rex-api-call=consent_manager_stats&days=30',
        dataType: 'json',
        success: function(data) {
            renderStats(data);
        },
        error: function() {
            $('#consent-stats-body').html('<div class="alert alert-danger"><?= rex_i18n::msg('consent_manager_stats_error') ?></div>');
        }
    });
}

function renderStats(data) {
    var html = '';
    
    // Summary
    html += '<div class="row"><div class="col-md-12"><h4><?= rex_i18n::msg('consent_manager_stats_total') ?> ' + data.total + '</h4></div></div>';
    html += '<hr>';

    // Cookies
    html += '<div class="row"><div class="col-md-6">';
    html += '<h5><?= rex_i18n::msg('consent_manager_stats_top_cookies') ?></h5>';
    html += '<table class="table table-striped table-condensed">';
    html += '<thead><tr><th><?= rex_i18n::msg('consent_manager_stats_service') ?></th><th><?= rex_i18n::msg('consent_manager_stats_count') ?></th><th>%</th></tr></thead><tbody>';
    
    $.each(data.cookies, function(uid, count) {
        var percent = data.total > 0 ? Math.round((count / data.total) * 100) : 0;
        html += '<tr>';
        html += '<td>' + uid + '</td>';
        html += '<td>' + count + '</td>';
        html += '<td><div class="progress" style="margin-bottom:0"><div class="progress-bar progress-bar-info" role="progressbar" style="width: ' + percent + '%;">' + percent + '%</div></div></td>';
        html += '</tr>';
    });
    html += '</tbody></table>';
    html += '</div>';

    // Daily
    html += '<div class="col-md-6">';
    html += '<h5><?= rex_i18n::msg('consent_manager_stats_history') ?></h5>';
    html += '<table class="table table-striped table-condensed">';
    html += '<thead><tr><th><?= rex_i18n::msg('consent_manager_stats_date') ?></th><th><?= rex_i18n::msg('consent_manager_stats_count') ?></th></tr></thead><tbody>';
    $.each(data.daily, function(i, day) {
        html += '<tr>';
        html += '<td>' + day.date + '</td>';
        html += '<td>' + day.count + '</td>';
        html += '</tr>';
    });
    html += '</tbody></table>';
    html += '</div></div>';

    $('#consent-stats-body').html(html);
}
</script>
<?php
