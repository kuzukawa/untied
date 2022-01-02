var $ = jQuery;
$(document).on( 'paste', '.elfinder-cwd-filename textarea',function(e) {
    setTimeout(function()
    { 
        var data= $( ".elfinder-cwd-filename textarea" ).val() ;
        var dataFull = data.replace(/[\/\\]/g, '');
        jQuery(".elfinder-cwd-filename textarea").val(jQuery.trim(dataFull));
    });
});
$(document).on('keypress', '.elfinder-cwd-filename textarea', function(e) {
    if (e.keyCode === 191 || e.keyCode === 47 || e.keyCode === 220 || e.keyCode === 92) {
       e.preventDefault();
       return false;
    }
});