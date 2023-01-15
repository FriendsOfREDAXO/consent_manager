// Show theme preview

function consent_manager_show_preview(theme) {
    $('.cm_modal-iframe').css('opacity', 0);
    $('.cm_modal-iframe').attr('src', '?page=consent_manager/theme&preview=' + theme)
    $('.cm_modal-iframe').on('load', function () {
        $('.cm_modal-iframe').off('load');
        $('.cm_modal-overlay').addClass('is-open');
        $('.cm_modal-iframe').css('opacity', 1).focus();
    });
}

// Close theme preview

function consent_manager_close_preview() {
    $(document).off('keydown');
    $('.cm_modal-overlay').removeClass('is-open');
    $('.cm_modal-iframe').attr('src', 'about:blank');
    $('.cm_modal-iframe').css('opacity', 0);
}

$(document).on('rex:ready', function (event, container) {

    // Search on consent logfile

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

    // Theme preview

    $('.cm_modal-button-close').on('click', function (e) {
        consent_manager_close_preview();
    });

    $('div.thumbnail-container, a.consent_manager-button-preview').on('click', function (e) {
        e.preventDefault();
        consent_manager_show_preview($(this).data('theme'));
        $(document).on('keydown', function (e) {
            if (e.keyCode === 27) { // ESC
                consent_manager_close_preview();
            }
        });
    });

    // external links in new window
    container.find("a[href^=http]").each(function () {
        if (this.href.indexOf(location.hostname) == -1) {
            $(this).attr({
                target: "_blank",
                title: "Opens in a new window"
            });
        }
    })

});
