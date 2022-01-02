<?php if (!defined('ABSPATH')) {
    exit;
}
$this->fm_custom_assets();
global $wpdb;

if(isset($_REQUEST["delete_all"]) && wp_verify_nonce( $_POST['del_entries'], 'mk_del_entries' )){
    $wpdb->query("DELETE FROM ".$wpdb->prefix."wpfm_shortcodes WHERE id IN (".implode(',', $_REQUEST["chk_shortcodes"]).")");
    mk_file_folder_manager::redirect('?page=wp_file_manager_existing_shortcodes&msg=2');
}

if(isset($_REQUEST["id"]) && isset($_REQUEST["key"]) && wp_verify_nonce($_REQUEST["key"], 'shortcode_del_nonce')){
    $id = trim(intval($_REQUEST["id"]));
    $wpdb->delete( $wpdb->prefix.'wpfm_shortcodes', array('id' => $id));
    mk_file_folder_manager::redirect('?page=wp_file_manager_existing_shortcodes&msg=1');
}
$manage_shortcodes = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'wpfm_shortcodes ORDER BY id DESC');
?>
<div class="wrap">
	<div class="setting_pro_wrap fmInnerWrap fm-shcode">
		<h3 class="mainHeading">
			<span class="headingIcon"><img src="<?php echo plugins_url('images/fm-setting-icon.png', __FILE__); ?>"></span>
			<span class="headingText"><?php _e('Existing Shortcode(s)', 'wp-file-manager-pro'); ?></span>
            <a title="<?php _e('Click to go back to the shortcode generator', 'wp-file-manager-pro'); ?>" class="button button-primary" href="admin.php?page=wp_file_manager_shortcode_generator"><?php _e('Generate New Shortcode', 'wp-file-manager-pro'); ?></a>
		</h3>
	    <?php 
	    if(isset($_REQUEST["msg"]) && intval($_REQUEST["msg"]) == 1){
		    echo '<div class="notice notice-success shortcode-notice"><p>'.__("Shortcode has been deleted successfully.",'wp-file-manager-pro').'</p></div>';
	    } else if(isset($_REQUEST["msg"]) && intval($_REQUEST["msg"]) == 2){
            echo '<div class="notice notice-success shortcode-notice"><p>'.__("Shortcode(s) has been deleted successfully.",'wp-file-manager-pro').'</p></div>';
        }
	    ?>
        <form name="tbl_list" method="post" onsubmit="return submit_delete();">
            <?php wp_nonce_field('mk_del_entries', 'del_entries'); ?>
            <table name="tbl_list" id="tbl_list" class="wp-list-table widefat fixed striped">
                <thead class="tbl_shortcode_listing">
                    <tr>
                        <th class="manage-column" style="width:10%;"><span class="chk_span_outer">
                            <div class="chk_box styledCheckbox">
                                <input type="checkbox" id="chk_all"/><span class="fm_checkmark"></span>
                            </div>
                        </th>
                        <th class="manage-column" style="width:25%;"><?php _e('Title', 'wp-file-manager-pro'); ?></th>
                        <th class="manage-column" style="width:40%;"><?php _e('Shortcode', 'wp-file-manager-pro'); ?></th>
                        <th class="manage-column" style="width:25%;"><?php _e('Action', 'wp-file-manager-pro'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach($manage_shortcodes as $shortcode){
                        $shortcode_name = $shortcode->type == "loggedin" ? "wp_file_manager" : "wp_file_manager_without_login";
                        $shortcode_name = $shortcode_name." id='".$shortcode->shotcode_key."' title='".$shortcode->shotcode_title."'";
                        ?>
                        <tr>
                            <td> 
                                <div class="chk_box styledCheckbox">
                                    <input type="checkbox" class="chk_shrtcode" name="chk_shortcodes[]" value="<?php echo $shortcode->id;?>"/><span class="fm_checkmark"></span>
                                </div>
                            </td>
                            <td><input type="hidden" value="<?php echo $shortcode->id;?>"><strong><?php echo $shortcode->shotcode_title;?></strong></td>
                            <td class="fm_text_area_box"><textarea class="large-text code" name="wp_file_manager_admin" readonly="" class="fm_wp_file_manager_shortcode_1">[<?php echo $shortcode_name;?>]</textarea><button class="button button-primary" title="<?php _e('Copy to clipboard', 'wp-file-manager-pro'); ?>" type="button" onclick="copy_code(this)"><?php _e('Copy', 'wp-file-manager-pro'); ?></button></td>
                            <td><a class="button button-primary" href="admin.php?page=wp_file_manager_shortcode_generator&id=<?php echo $shortcode->id;?>"><?php _e('Edit', 'wp-file-manager-pro'); ?></a>
                            <a class="button button-default" onclick="return confirm('<?php _e("Are you sure you want to delete this shortcode?", "wp-file-manager-pro"); ?>')" href="admin.php?page=wp_file_manager_existing_shortcodes&id=<?php echo $shortcode->id;?>&key=<?php echo wp_create_nonce( 'shortcode_del_nonce' );?>"><?php _e('Delete', 'wp-file-manager-pro'); ?></a>
                            <a class="button button-primary cust-shortcode" href="javascript:void(0)" data-id="<?php echo $shortcode->shotcode_key;?>" data-key="<?php echo base64_encode(json_encode(unserialize($shortcode->attributes)));?>"><?php _e('Customize Shortcode', 'wp-file-manager-pro'); ?></a></td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </form>
    </div>
</div>
<div id="shortcode_customizer" class="fm_show_raw_html" style="display:none;">
    <div class="fm_raw_html fm-shcode">
        <div class="fm_raw_html_header">
            <h2><?php _e('Customize Shortcode','wp-file-manager-pro');?></h2>
            <a href="javascript:void(0)" class="fm_popup_close"><img src="<?php echo fm_plugin_url;?>/images/fm_updator_close.svg" /></a>
        </div>
        <div class="popup-content">
            <div class="fm_customize_content fm_text_area_box">
                <div class="fm-content">
                    <div class="large-text code" id="fm_customized_html"></div>
                    <button class="button button-primary" id="btn_copy_sc" title="<?php _e('Copy to clipboard', 'wp-file-manager-pro'); ?>" type="button" onclick="copy_customized_code('fm_customized_html')"><?php _e('Copy', 'wp-file-manager-pro'); ?></button>
                </div>
            </div>
            <p class="description"><?php _e('Copy above filter code and paste it in your theme\'s functions.php file or in your plugin file to customize shortcode values as per your requirement.','wp-file-manager-pro');?></p>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function(){
        <?php 
			if(isset($_REQUEST["msg"])){
				?>
				var admin_page_url = "<?php echo admin_url('admin.php?page=wp_file_manager_existing_shortcodes');?>";
				window.history.replaceState({}, document.title, admin_page_url);
				<?php
		}
		?>
        jQuery('.shortcode-notice').delay(10000).fadeOut('slow');
        jQuery("#tbl_list").DataTable({
            "dom": '<"fm_list_header">lfrtip',
            "pagingType": "simple_numbers",
            "language": {
                "info": "<?php _e('Showing','wp-file-manager-pro');?> _START_ <?php _e('to','wp-file-manager-pro');?> _END_ <?php _e('of','wp-file-manager-pro');?> _TOTAL_ <?php _e('entries','wp-file-manager-pro');?>",
                "infoFiltered": "",
                "lengthMenu": "<?php _e('Showing','wp-file-manager-pro');?> _MENU_ <?php _e('entries','wp-file-manager-pro');?>",
                "search": "<?php _e('Search','wp-file-manager-pro');?>",
                "zeroRecords": "<?php _e('No shortcode found','wp-file-manager-pro');?>",
                "infoEmpty": "<?php _e('Showing','wp-file-manager-pro');?> 0 <?php _e('to','wp-file-manager-pro');?> 0 <?php _e('of','wp-file-manager-pro');?> 0 <?php _e('entries','wp-file-manager-pro');?>",
                "paginate": {
                    "previous": "<?php _e('Prev','wp-file-manager-pro');?>",
                    "next": "<?php _e('Next','wp-file-manager-pro');?>"
                }
            },
            //"bSort": true,
            //"pageLength": 10,
            "order":[[0,"desc"]],
            "aoColumnDefs": [
                //{ "iDataSort": 1, "aTargets": [ 0 ] },
                { "targets": 0, "orderable": false },
                { "targets": 3, "orderable": false }
            ],
            //"aoColumnDefs": [{"bSortable": false}]
            "initComplete": function(){
                jQuery(".dataTables_length label").addClass("filter_select");
                jQuery(".dataTables_filter input").addClass("search_box");
                <?php 
                if(count($manage_shortcodes) > 0){
                    ?>
                    jQuery("div.fm_list_header").html('<button type="submit" name="delete_all" class="delete_all button button-default"><?php _e("Delete All Selected","wp-file-manager-pro")?></button>');
                    <?php
                }
                ?>                
            },
            "fnDrawCallback": function( oSettings ) {
                if(jQuery('.search_box').val() != ""){
                    jQuery('.search_box').css("background-image","none");
                }
                else{
                    jQuery('.search_box').removeAttr("style");
                }
            }
        });
        jQuery(document).on('click','.cust-shortcode', function(){
            var code_data = jQuery(this).attr('data-key');
            var key = jQuery(this).attr('data-id');
            var codeData = jQuery.parseJSON(atob(code_data));
            var ban_user = codeData.ban_user_ids != undefined ? '<span class="fm-blue">$attrs</span>[<span class="fm-maroon">"ban_user_ids"</span>] = "'+codeData.ban_user_ids+'";\n   ' : '';
            var allowed_user = codeData.allowed_roles != undefined ? '<span class="fm-blue">$attrs</span>[<span class="fm-maroon">"allowed_roles"</span>] = "'+codeData.allowed_roles+'";\n   ' : '';
            var fun_content = '<span class="fm-blue">function</span> <span class="fm-tint">fm_shortcode_customize_'+key+'</span>( <span class="fm-blue">$attrs</span> ) {\n   <span class="fm-blue">$attrs</span>[<span class="fm-maroon">"sc_id"</span>] = "'+key+'";\n   '+allowed_user+'<span class="fm-blue">$attrs</span>[<span class="fm-maroon">"access_folder"</span>] = "'+codeData.access_folder+'";\n   <span class="fm-blue">$attrs</span>[<span class="fm-maroon">"allowed_operations"</span>] = "'+codeData.allowed_operations+'";\n   '+ban_user+'<span class="fm-blue">$attrs</span>[<span class="fm-maroon">"dateformat"</span>] = "'+codeData.dateformat+'";\n   <span class="fm-blue">$attrs</span>[<span class="fm-maroon">"hide_files"</span>] = "'+codeData.hide_files+'";\n   <span class="fm-blue">$attrs</span>[<span class="fm-maroon">"lang"</span>] = "'+codeData.lang+'";\n   <span class="fm-blue">$attrs</span>[<span class="fm-maroon">"lock_extensions"</span>] = "'+codeData.lock_extensions+'";\n   <span class="fm-blue">$attrs</span>[<span class="fm-maroon">"theme"</span>] = "'+codeData.theme+'";\n   <span class="fm-blue">$attrs</span>[<span class="fm-maroon">"view"</span>] = "'+codeData.view+'";\n   <span class="fm-purple">return</span> <span class="fm-blue">$attrs</span>;\n}\n<span class="fm-tint">add_filter</span>( <span class="fm-maroon">"fm_shortcode_attr_'+key+'"</span>, <span class="fm-maroon">"fm_shortcode_customize_'+key+'"</span>);';
            jQuery("#fm_customized_html").html(fun_content);
            jQuery("#shortcode_customizer").show();
        });
        jQuery(document).on('click','.fm_popup_close',function(){
            jQuery("#shortcode_customizer").hide();
            jQuery("#fm_customized_html").html('');
            window.getSelection().removeAllRanges();
            jQuery("#btn_copy_sc").html("<?php _e('Copy', 'wp-file-manager-pro')?>");
            jQuery("#btn_copy_sc").attr("title","<?php _e('Copy to clipboard', 'wp-file-manager-pro'); ?>");
        });
    });

    function copy_customized_code(id){
        var r = document.createRange();
        r.selectNode(document.getElementById(id));
        window.getSelection().removeAllRanges();
        window.getSelection().addRange(r);
        document.execCommand('copy');
        jQuery("#btn_copy_sc").html("<?php _e('Copied', 'wp-file-manager-pro')?>");
        jQuery("#btn_copy_sc").attr("title","<?php _e('Copied to clipboard', 'wp-file-manager-pro'); ?>");

        setTimeout(function(){ 
            window.getSelection().removeAllRanges();
            jQuery("#btn_copy_sc").html("<?php _e('Copy', 'wp-file-manager-pro')?>");
            jQuery("#btn_copy_sc").attr("title","<?php _e('Copy to clipboard', 'wp-file-manager-pro'); ?>");
        }, 2000);
    }

    function copy_code(control){
        var input = jQuery(control).closest('td').find('textarea');
        //var input = document.getElementById("api_key");
        var isiOSDevice = navigator.userAgent.match(/ipad|iphone/i);

        if (isiOSDevice) {
        
            var editable = input.contentEditable;
            var readOnly = input.readOnly;

            input.contentEditable = true;
            input.readOnly = false;

            var range = document.createRange();
            range.selectNodeContents(input);

            var selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);

            input.setSelectionRange(0, 999999);
            input.contentEditable = editable;
            input.readOnly = readOnly;

        } else {
            input.select();
        }

        document.execCommand('copy');

        jQuery(control).html("<?php _e('Copied', 'wp-file-manager-pro')?>");
        jQuery(control).attr("title","<?php _e('Copied to clipboard', 'wp-file-manager-pro'); ?>");

        setTimeout(function(){ 
            window.getSelection().removeAllRanges();
            jQuery(control).html("<?php _e('Copy', 'wp-file-manager-pro')?>");
            jQuery(control).attr("title","<?php _e('Copy to clipboard', 'wp-file-manager-pro'); ?>");
         }, 2000);
    }
    function submit_delete(){
        if(jQuery("#tbl_list .chk_shrtcode:checked").length > 0 ){
            if(confirm('<?php _e("Are you sure you want to delete selected shortcode(s)?", "wp-file-manager-pro");?>')){
                return true;
            }
            else{
                return false;
            }
        }
        else{
            alert('<?php _e("Please select atleast one shortcode from the list.", "wp-file-manager-pro");?>')
            return false;
        }
    }
    jQuery(document).on('click','#chk_all', function() { // bulk checked
        var tableId = jQuery(this).closest('table').attr("id");
        if(jQuery("#tbl_list .chk_shrtcode").length > 0){
            var status = this.checked;
            jQuery("#tbl_list .chk_shrtcode").each( function() {
                jQuery(this).prop("checked",status);
            });
        }
        else{
            jQuery(this).prop("checked",false);
        }
    });

    jQuery(document).on("click", 'input.chk_shrtcode', function(){
        if(jQuery('#tbl_list tbody tr:visible .chk_shrtcode:checked').length < jQuery('#tbl_list tbody tr:visible').length){
            jQuery("#chk_all").prop("checked",false);
        } else if(jQuery('#tbl_list tbody tr:visible .chk_shrtcode:checked').length == jQuery('#tbl_list tbody tr:visible').length){
            jQuery("#chk_all").prop("checked",true);
        }
    });

    jQuery('#tbl_list').on( 'page.dt', function () {
        jQuery("#chk_all").prop('checked',false);
        jQuery(".dt-checkboxes").prop('checked',false);
    });
</script>