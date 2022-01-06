(function ($, window, document, undefined) {


    $(function () {
        var $textarea = $('#acfcdt-diagnostic-data'),
            visibleModifier = 'acfcdt-diagnostic-data--visible',
            $visBtn = $('#acfcdt-toggle-diagnostics'),
            ignoreFocus = false,
            toggleDiagnosticData = function () {
                if ($textarea.hasClass(visibleModifier)) {
                    $textarea.removeClass(visibleModifier);
                    $visBtn.text('Show Diagnostic Data');
                } else {
                    $textarea.addClass(visibleModifier);
                    $visBtn.text('Hide Diagnostic Data');
                }
            };
        $visBtn.click(function (e) {
            e.preventDefault();
            toggleDiagnosticData();
        });
        var $a = $('#acfcdt-diagnostic-copy');
        if (!document.queryCommandSupported('copy')) {
            return $a.remove();
        }
        $a.on('click', function (e) {
            e.preventDefault();
            $textarea.get(0).select();
            try {
                var copy = document.execCommand('copy');
                if (!copy) return;
                $('#acfcdt-copy-success').slideDown(160);
            } catch (err) {
            }
        });
    });


    $(function () {
        $('.acfcdt-doc-block').each(function () {
            var $this = $(this);
            var $title = $this.find('.acfcdt-doc-block-title');
            var $content = $this.find('.acfcdt-doc-block-content');
            $content.slideUp(0);
            $this.addClass('acfcdt-doc-block--closed');
            $title.click(function () {
                $content.slideToggle(160);
                $this.toggleClass('acfcdt-doc-block--closed acfcdt-doc-block--open');
            });
        });
    });


    $(function () {
        $('.acfcdt-reset-defaults-link').on('click', function (event) {
            event.preventDefault();

            $('input[data-default]').each(function () {
                let $this = $(this);
                if ($this.data('default') === 'checked') {
                    $this.prop('checked', true);
                } else {
                    $this.prop('checked', false);
                }
            });

            $('#submit').trigger('click');
        });
    });


    $(function () {
        var $progress = $('.acfcdt-progress');
        if (!$progress.length) {
            return;
        }

        $progress.each(function () {
            var $this = $(this);
            var $bar = $this.find('.acfcdt-progress__progress');
            //var $stripes = $this.find('.acfcdt-progress__stripes');
            var $info = $this.find('.acfcdt-progress__info');
            var ajax_status_url = $this.data('ajax-status-url');
            var $logs = $this.find('.acfcdt-progress__log-output');

            function check_status() {
                // Prepare data with nonce.
                var data = {};
                var nonce = $this.data('nonce');
                data[nonce.name] = nonce.value;

                return $.post({
                    type: "POST",
                    url: ajax_status_url,
                    data: data,
                    dataType: 'json',
                    success: function (data, status, jqxhr) {
                        if (data.hasOwnProperty('percentage')) {
                            $this.data('progress', data.percentage);
                            if (data.hasOwnProperty('info')) {
                                $info.text(data.info);
                            }
                            if (data.hasOwnProperty('logs')) {
                                //$logs.append("\r\n" + data.logs.join("\r\n"));
                                $logs.text(data.logs.join("\r\n"));
                                $logs.scrollTop($logs[0].scrollHeight);
                            }
                            if (!data.complete) {
                                $bar.css('width', data.percentage + '%');
                                setTimeout(function () {
                                    check_status();
                                }, 250);
                            } else {
                                $bar.css('width', '100%');
                                $this.addClass($this.data('done-class'));
                                $this.trigger('acfcdt/progress/complete');
                            }
                        } else {
                            show_error('Data received while retrieving a status update is missing expected information.');
                        }
                    },
                    error: function (jqxhr, status, error) {
                        show_error(jqxhr.responseJSON.data.message);
                    },
                });
            }

            check_status();

            function show_error(message) {
                $this.find('.acfcdt-progress__error').show();
                $this.find('.acfcdt-progress__error-message').text(message);
            }
        })
    });

    $(function () {
        const $cancel_btn = $('.acfcdt-migrate-cancel');
        if ($cancel_btn.length) {
            $('.acfcdt-progress').on('acfcdt/progress/complete', function () {
                $cancel_btn.hide();
            });
        }
    });


})(jQuery, window, document);