function initConsentManagerConfigPage() {
    var modeSelect = document.getElementById('css-framework-mode-select');
    var frameworkPanel = document.getElementById('framework-options-panel');
    var outputOwnCssContainer = document.getElementById('output-own-css-container');

    var updateToggles = function () {
        if (!modeSelect) {
            return;
        }

        var frameworkActive = modeSelect.value !== '';

        if (frameworkPanel) {
            frameworkPanel.style.display = frameworkActive ? 'block' : 'none';
        }

        if (outputOwnCssContainer) {
            var row = outputOwnCssContainer.closest('.rex-form-row');
            if (row) {
                row.style.display = frameworkActive ? 'none' : 'block';
            }
        }
    };

    if (modeSelect && modeSelect.dataset.cmConfigBound !== '1') {
        modeSelect.dataset.cmConfigBound = '1';
        modeSelect.addEventListener('change', updateToggles);
    }

    updateToggles();

    var redirectMarker = document.getElementById('cm-config-redirect');
    if (!redirectMarker) {
        return;
    }

    if (redirectMarker.dataset.cmRedirectScheduled === '1') {
        return;
    }
    redirectMarker.dataset.cmRedirectScheduled = '1';

    var redirectUrl = redirectMarker.getAttribute('data-cm-config-redirect-url');
    var redirectDelay = parseInt(redirectMarker.getAttribute('data-cm-config-redirect-delay') || '2000', 10);

    if (!redirectUrl) {
        return;
    }

    window.setTimeout(function () {
        window.location.href = redirectUrl;
    }, Number.isNaN(redirectDelay) ? 2000 : redirectDelay);
}

(function ($) {
    $(document).on('rex:ready', initConsentManagerConfigPage);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initConsentManagerConfigPage, { once: true });
    } else {
        initConsentManagerConfigPage();
    }
})(jQuery);
