// Show theme preview
function consent_manager_show_preview(theme) {
    var modalIframe = $('.cm_modal-iframe');
    modalIframe.css('opacity', 0);
    modalIframe.attr('src', '?page=consent_manager/theme&preview=' + theme);
    
    modalIframe.one('load', function () {
        $('.cm_modal-overlay').addClass('is-open');
        modalIframe.css('opacity', 1).focus();
    });
}

// Close theme preview
function consent_manager_close_preview() {
    $(document).off('keydown.consentPreview');
    $('.cm_modal-overlay').removeClass('is-open');
    $('.cm_modal-iframe').attr('src', 'about:blank').css('opacity', 0);
}

$(document).on('rex:ready', function (event, container) {

    // Search on consent logfile
    var logSearchInput = $("#consent_manager_log_search .form-control");
    var logSearchButton = $("#consent_manager_log_search .input-group-addon");

    function consent_manager_setsearch() {
        if (!logSearchInput.length) return;
        
        var urlParams = new URLSearchParams(window.location.search);
        var searchValue = urlParams.get('Consent_Search');
        
        if (searchValue && logSearchInput.val() === '') {
            logSearchInput.val(searchValue);
        }
    }

    function consent_manager_dosearch(search) {
        var urlParams = new URLSearchParams(window.location.search);
        var newurl = window.location.pathname + '?';
        
        for (const [key, value] of urlParams.entries()) {
            if (key !== 'Consent_Search' && key !== 'Consent-Log_start') {
                newurl += '&' + encodeURIComponent(key) + '=' + encodeURIComponent(value);
            }
        }
        
        if (search !== '') {
            window.location.replace(newurl + '&Consent_Search=' + encodeURIComponent(search));
        } else {
            window.location.replace(newurl);
        }
    }

    consent_manager_setsearch();

    if (logSearchInput.length) {
        logSearchInput.on('keyup', function (event) {
            if (event.key === 'Enter') {
                var search = $(this).val().toLowerCase();
                consent_manager_dosearch(search);
            }
        });
    }

    if (logSearchButton.length) {
        logSearchButton.on('click', function (event) {
            var search = logSearchInput.val().toLowerCase();
            consent_manager_dosearch(search);
        });
    }

    rex_searchfield_init("#consent_manager_log_search");

    // Theme preview
    var modalOverlay = $('.cm_modal-overlay');
    var modalIframe = $('.cm_modal-iframe');
    var modalCloseBtn = $('.cm_modal-button-close');
    
    function consent_manager_show_preview(theme) {
        modalIframe.css('opacity', 0);
        modalIframe.attr('src', '?page=consent_manager/theme&preview=' + theme);
        
        modalIframe.one('load', function () {
            modalOverlay.addClass('is-open');
            modalIframe.css('opacity', 1).focus();
        });
    }
    
    function consent_manager_close_preview() {
        $(document).off('keydown.consentPreview');
        modalOverlay.removeClass('is-open');
        modalIframe.attr('src', 'about:blank').css('opacity', 0);
    }

    modalCloseBtn.on('click', function (e) {
        consent_manager_close_preview();
    });

    $('div.thumbnail-container, a.consent_manager-button-preview').on('click', function (e) {
        e.preventDefault();
        consent_manager_show_preview($(this).data('theme'));
        
        $(document).on('keydown.consentPreview', function (e) {
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
