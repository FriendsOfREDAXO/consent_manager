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

    // Cookiegroup language mode toggle (inherit/custom services)
    var cookieModeSelectSelector = 'select[name*="cookie_mode"], select[id*="cookie_mode"], select[id*="cookie-mode"]';

    var initCookieModeToggle = function (selectElement) {
        var cookieModeSelect = $(selectElement);
        if (!cookieModeSelect.length || cookieModeSelect.data('cmCookieModeInit') === true) {
            return;
        }
        cookieModeSelect.data('cmCookieModeInit', true);

        var cookieModeForm = cookieModeSelect.closest('form');
        var cookieModeInheritSection = cookieModeForm.find('.cm-cookie-mode-section-inherit');
        var cookieModeCustomSection = cookieModeForm.find('.cm-cookie-mode-section-custom');
        var cookieModeInheritHint = cookieModeForm.find('.cm-cookie-mode-hint-inherit');
        var cookieModeCustomHint = cookieModeForm.find('.cm-cookie-mode-hint-custom');

        var customCheckboxes = cookieModeForm.find('input[type="checkbox"][name*="cookie_custom"]');
        var customCheckboxGroup = customCheckboxes.first().closest('dl.rex-form-group, .form-group');

        var copyInheritedSelectionToCustom = function () {
            if (customCheckboxGroup.data('inheritApplied') === true) {
                return;
            }

            if (customCheckboxes.filter(':checked').length > 0) {
                customCheckboxGroup.data('inheritApplied', true);
                return;
            }

            var inheritedCheckedLabels = {};
            cookieModeInheritSection.find('.checkbox input[type="checkbox"]:checked').each(function () {
                var labelText = $(this).closest('label').text().trim();
                if (labelText !== '') {
                    inheritedCheckedLabels[labelText] = true;
                }
            });

            customCheckboxes.each(function () {
                var checkbox = $(this);
                var customLabel = checkbox.closest('label').text().trim();
                checkbox.prop('checked', inheritedCheckedLabels[customLabel] === true);
            });

            customCheckboxGroup.data('inheritApplied', true);
        };

        var syncCookieModeView = function () {
            var isCustom = cookieModeSelect.val() === 'custom';

            cookieModeInheritSection.hide();
            cookieModeCustomSection.toggle(isCustom);
            cookieModeInheritHint.toggle(!isCustom);
            cookieModeCustomHint.toggle(isCustom);

            if (customCheckboxGroup.length) {
                customCheckboxGroup.toggle(isCustom);
            }

            customCheckboxes.prop('disabled', !isCustom);

            if (isCustom) {
                copyInheritedSelectionToCustom();
            }
        };

        cookieModeSelect.on('change', syncCookieModeView);
        syncCookieModeView();
    };

    container.find(cookieModeSelectSelector).each(function () {
        initCookieModeToggle(this);
    });

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
