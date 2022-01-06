(function ($, window, document, undefined) {

    // Listen for notice dismiss clicks and fire post request to handle any server side requirements for the particular
    // notice.
    $(function () {
        $('.acfcdt-dismissible').on('click', '.notice-dismiss', function () {
            let url = $(this).closest('.notice').data('dismiss-url');
            if (url) {
                $.post(url);
            }
        });
    });

})(jQuery, window, document);