(function ($) {
    var initialized = false;

    function initHelpPage() {
        if (initialized) {
            return;
        }
        initialized = true;

        var $input = $('#cm-toc-filter');
        var $list = $('#cm-toc-list');

        if ($input.length && $list.length) {
            $input.on('keyup', function () {
                var value = ($(this).val() || '').toString().toLowerCase();
                $list.find('a').each(function () {
                    var $link = $(this);
                    $link.toggle($link.text().toLowerCase().indexOf(value) > -1);
                });
            });

            $input.on('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    return false;
                }
            });
        }

        $('.rex-docs pre').each(function () {
            var $pre = $(this);
            if ($pre.find('.btn-copy-code').length) {
                return;
            }

            var $code = $pre.find('code');
            if (!$code.length) {
                return;
            }

            var $button = $('<button class="btn-copy-code" title="Copy to clipboard" type="button"><i class="rex-icon fa-clipboard"></i></button>');
            $pre.append($button);

            $button.on('click', function () {
                var codeText = $code.text();
                var textarea = document.createElement('textarea');
                textarea.value = codeText;
                document.body.appendChild(textarea);
                textarea.select();

                try {
                    document.execCommand('copy');
                    $button.addClass('copied').html('<i class="rex-icon fa-check"></i>');
                    window.setTimeout(function () {
                        $button.removeClass('copied').html('<i class="rex-icon fa-clipboard"></i>');
                    }, 2000);
                } catch (error) {
                    console.error('Failed to copy text', error);
                }

                document.body.removeChild(textarea);
            });
        });
    }

    $(document).on('rex:ready', initHelpPage);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHelpPage, { once: true });
    } else {
        initHelpPage();
    }
})(jQuery);
