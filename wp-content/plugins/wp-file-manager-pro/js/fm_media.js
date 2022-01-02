jQuery('#insert_fm_shortcode').click(function(e) {
    e.preventDefault();
    jQuery("#fm_short_error").remove();
    var generated_shortcode = jQuery('#fm_shortcode').val();
    if(generated_shortcode != ""){
        window.send_to_editor(generated_shortcode);
        jQuery('.shortcode_plus_container_pop').hide();
        jQuery("#fm_shortcode").val('');
    } else {
        jQuery("#fm_shortcode").closest('td').append('<label id="fm_short_error" style="color: #c50909;display: block;">Please select a shortcode from the list.</label>');
    }
});
function showPopup(){
    setTimeout(() => {
        jQuery(".fm_pro_form").parent().parent().addClass('fm-popup');
    }, 10);
}