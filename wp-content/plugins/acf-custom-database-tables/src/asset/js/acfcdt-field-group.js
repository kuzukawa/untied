(function ($, window, document, undefined) {


    $(function () {

        var $toggle = $('#acf_field_group-acfcdt_manage_table_definition');

        if (!$toggle.length)
            return;

        var supported_params = ['post_type', 'user_form'];
        var $rule_groups = $('.rule-groups');
        var $disabled_notice = $('.acfcdt-meta-box-deactivation-note');
        var $acf_fields_wrap = $('.acfcdt-acf-fields');

        $.extend($toggle, {
            is_switched_on: function () {
                return $(this).is(':checked');
            },
            switch_off: function () {
                var $this = $(this);
                if (this.is_switched_on()) {
                    $this.trigger('click');
                }
            },
            switch_on: function () {
                var $this = $(this);
                if (!this.is_switched_on()) {
                    $this.trigger('click');
                }
            },
            disable_interaction: function () {
                $(this).attr('disabled', true);
            },
            enable_interaction: function () {
                $(this).attr('disabled', false);
            },
            show_disabled_notice: function () {
                $acf_fields_wrap.hide();
                $disabled_notice.show();
            },
            hide_disabled_notice: function () {
                $acf_fields_wrap.show();
                $disabled_notice.hide();
            }
        });

        delayed_evaluate();

        $rule_groups
            .on('change', delayed_evaluate)
            .on('click', function (e) {
                if ($(e.target).is('button, a')) {
                    delayed_evaluate();
                }
            });

        $(document).ajaxComplete(function () {
            evaluate();
        });

        function delayed_evaluate () {
            setTimeout(evaluate, 160);
        }

        var was_checked = false;

        function evaluate () {
            var $rules = $rule_groups.find('tr');

            if (!$rules.length)
                return;

            var selected_object_types = [];

            $rules.each(function () {
                var $this = $(this);

                if ($this.find('.operator').find('select').val() === '==') {
                    var param = $this.find('.param').find('select').val();

                    if ($.inArray(param, supported_params) > -1) {

                        var object_type = $this.find('.value').find('select').val();
                        if ($.inArray(object_type, selected_object_types) === -1) {
                            selected_object_types.push(object_type)
                        }

                    }
                }
            });

            if (selected_object_types.length === 1) {
                $toggle.enable_interaction();
                $toggle.hide_disabled_notice();
                if (was_checked) {
                    $toggle.switch_on();
                }
            } else {
                was_checked = $toggle.is_switched_on();
                $toggle.switch_off();
                $toggle.disable_interaction();
                $toggle.show_disabled_notice();
            }

        }

    });


})(jQuery, window, document);