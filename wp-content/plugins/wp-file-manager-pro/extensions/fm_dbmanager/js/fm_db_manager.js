jQuery(document).ready(function(e) {

	jQuery('.clone_data a').hide();
    jQuery('#add_new_row').click(function(e) {
		e.preventDefault();
		var newrow = '<tr class="newdata">'+jQuery('.clone_data').html()+'</tr>';
       jQuery('#records').append(newrow);
	   jQuery('.newdata a').show();
    });
	jQuery(document).on("click", '.newdata .removeRow', function(){
		var point = jQuery(this).parents('.newdata');
		point.fadeOut(500, function(){   
			jQuery(this).replaceWith('');
		});
    });
	jQuery('.save_tbl').click(function(e) {
		/* Table Name */
		if(jQuery('.table_name').val() == '')
		{
			alert(fm_ajax.table_name_validation);
			e.preventDefault();	
		}
		else
		{
			/* Table Fields */
			jQuery('.field_name').each(function(index, element) {
				var fieldNameVal = jQuery(this).val();
				if(fieldNameVal == '')
				{
					alert(fm_ajax.table_value_validation);
					e.preventDefault();
				}
			});
		}
	});	
	
var resize= jQuery(".db_main_left");
var containerWidth = jQuery("#db_main").width();    
jQuery(resize).resizable({
      handles: 'e',
      maxWidth: 600,
      minWidth: 120,
      resize: function(event, ui){
          var currentWidth = ui.size.width;          
          //var padding = 12;
          jQuery(this).width(currentWidth);          
          // set the content panel width
          //jQuery("#content").width(containerWidth - currentWidth - padding); 
		  jQuery(".db_main_right").width(containerWidth - currentWidth);            
      }
});
});