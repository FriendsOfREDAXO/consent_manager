$(document).on('rex:ready', function () {

    function consent_manager_setsearch() {
        const urlParams = new URLSearchParams(window.location.search);
        entries = urlParams.entries();
        for (const entry of entries) {
            if (entry[0] == 'Consent_Search' && $("#consent_manager_log_search .form-control").val() == '') {
                $("#consent_manager_log_search .form-control").val(entry[1]);
            }
        }
    }

    function consent_manager_dosearch(search) {
        const urlParams = new URLSearchParams(window.location.search);
        entries = urlParams.entries();
        newurl = window.location.pathname + '?';
        for (const entry of entries) {
            if ((entry[0] != 'Consent_Search') && (entry[0] != 'Consent-Log_start')) {
                newurl = newurl + '&' + entry[0] + '=' + entry[1];
            }
        }
        if (search != '') {
            window.location.replace(newurl + '&Consent_Search=' + search);
        } else {
            window.location.replace(newurl);
        }
    }

    consent_manager_setsearch();

    $("#consent_manager_log_search .form-control").keyup(function (event) {
        var keycode = event.key;
        if (keycode == 'Enter') {
            var search = $(this).val().toLowerCase();
            consent_manager_dosearch(search);
        }
    });

    $("#consent_manager_log_search .input-group-addon").click(function (event) {
        search = $("#consent_manager_log_search .form-control").val().toLowerCase();
        $("#consent_manager_log_search .form-control").val(search);
        consent_manager_dosearch(search);
    });

    rex_searchfield_init("#consent_manager_log_search");
});
