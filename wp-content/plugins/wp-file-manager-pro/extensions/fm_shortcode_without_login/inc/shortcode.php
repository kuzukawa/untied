<?php /* Attributes starts */ if ( ! defined( 'ABSPATH' ) ) exit;
 $shortcodeData = shortcode_atts( array(
        'id' => '',
	'title' => '',
	), $atts );
/* End Attributes */

global $wpdb;
$shortcode_data = $wpdb->get_row(
	$wpdb->prepare(
		'SELECT * FROM ' . $wpdb->prefix . 'wpfm_shortcodes WHERE shotcode_key=%s',
		trim(esc_attr($shortcodeData["id"]))
	)
);
if(!empty($shortcode_data)){
	$shortcode_attributes = unserialize($shortcode_data->attributes);
	$shortcode_attributes = apply_filters( 'fm_shortcode_attr_'.$shortcode_data->shotcode_key, $shortcode_attributes );
	$opt = get_option('wp_filemanager_options');
	/* Ban Users start */
	$folderAccess = $shortcode_attributes['access_folder'];
	/* End Ban Users */	
	$allowedOperations = !empty($shortcode_attributes["allowed_operations"]) ? explode(',', strtolower($shortcode_attributes["allowed_operations"])) : array();
	$applied_theme_name = isset($shortcode_attributes['theme']) && !empty($shortcode_attributes['theme']) ? str_replace(" ","-",sanitize_text_field(strtolower($shortcode_attributes['theme']))) : '';
	mk_file_folder_manager::loadLibFiles($shortcode_attributes['lang'], strtolower($shortcode_attributes['theme']));
	mk_file_folder_manager::loadCodeMirror();
	$fmid = 'wp_file_manager_front'.rand(0,999);
	$fmarr = array(
		//'ajaxurl' => (empty($folderAccess)) ? admin_url('admin-ajax.php?action=mk_file_folder_manager') : admin_url('admin-ajax.php?action=mk_file_folder_manager_shortcode'),
		'ajaxurl' => admin_url('admin-ajax.php?action=mk_file_folder_manager_shortcode'),
		'adminajax' => admin_url('admin-ajax.php'),
		'uploadMaxChunkSize' => (isset($opt['fm_max_upload_size']) && !empty($opt['fm_max_upload_size'])) ? $opt['fm_max_upload_size'] * 1048576 : '1048576000000',
		'lang' => (isset($shortcode_attributes['lang']) && !empty($shortcode_attributes['lang'])) ? $shortcode_attributes['lang'] : 'en',
		'view' => (isset($shortcode_attributes['view']) && !empty($shortcode_attributes['view'])) ? strtolower($shortcode_attributes['view']) : 'grid',
		'dateformat' => (isset($shortcode_attributes['dateformat']) && !empty($shortcode_attributes['dateformat'])) ? $shortcode_attributes['dateformat'] : 'M d, Y h:i A',
		'data_key' => $shortcode_data->shotcode_key,
		'allow_upload_notifications' => (isset($opt['allow_upload_notifications']) && $opt['allow_upload_notifications'] == 'yes') ? 'yes' : 'no',
		'allow_download_notifications' => (isset($opt['allow_download_notifications']) && $opt['allow_download_notifications'] == 'yes') ? 'yes' : 'no',
		'allow_edit_notifications' => (isset($opt['allow_edit_notifications']) && $opt['allow_edit_notifications'] == 'yes') ? 'yes' : 'no',
		'userID' => '',
		'nonce' => wp_create_nonce('wp-file-manager-pro'),
		'code_editor_theme' => (!empty($opt['code_editor_theme']) && $opt['code_editor_theme'] != 'default') ? $opt['code_editor_theme'] : 'default',
		'disable_download_dcl' => !in_array('download',$allowedOperations) ? 'yes' : 'no',
		'openFullwidth' => isset($opt['allow_fullscreen']) && $opt['allow_fullscreen'] == 'yes' ? 'yes' : 'no',
	);
	if(!in_array('info',$allowedOperations)){
		$filemanagerReturn .= '<style>
		.theme-'.$applied_theme_name.' .elfinder .elfinder-contextmenu .elfinder-contextmenu-item.op-info{
	display:none !important;
	}
	</style>';
	}
	if(!in_array('download',$allowedOperations)){
		$filemanagerReturn .= '<style>
		.elfinder-dialog .ui-dialog-content table.elfinder-info-tb a.share-link {
			pointer-events: none;
			cursor: default;
			text-decoration: none;
		}
	</style>';
	}
	$filemanagerReturn .='<div class="wrap_file_manager">
	<div class="theme-'.$applied_theme_name.'"><div id="'.$fmid.'"></div></div>
	</div>';
	$filemanagerReturn .= '<script type="text/javascript">
	var selectedFilesArr = {};
		jQuery(document).ready(function () {
			var adminajax = "'.$fmarr["adminajax"].'";
			var allow_upload_notifications = "'.$fmarr["allow_upload_notifications"].'";
			var allow_download_notifications= "'.$fmarr["allow_download_notifications"].'";
			var allow_edit_notifications="'.$fmarr["allow_edit_notifications"].'";
			var fm_without_code = jQuery("#'.$fmid.'")
			  .elfinder({
				url: "'.$fmarr["ajaxurl"].'",
				uploadMaxChunkSize: "'.$fmarr["uploadMaxChunkSize"].'",
				lang: "'.$fmarr["lang"].'",
				defaultView: "'.$fmarr["view"].'",
				dateFormat: "'.$fmarr["dateformat"].'",
				customData: {
				  _wpnonce: "'.$fmarr["nonce"].'",
				  data_key: "'.$fmarr["data_key"].'",
				},
				uiOptions: {
					toolbarExtra : {
						preferenceInContextmenu: false,
					}
				},
				/* Start */
				handlers: {
					dblclick: function(event, elfinderInstance)
					{';
						 if($fmarr["disable_download_dcl"] == "yes") {
							
							$filemanagerReturn .= 'var fileData = elfinderInstance.file(event.data.file);
							if(fileData.mime != "directory"){
								return false;
							}';
						}
					$filemanagerReturn .= '},
					select : function(event, elfinderInstance) {
						var selected = event.data.selected;
						if(selected.length > 0){
							for (i in selected) {
								var file = elfinderInstance.file(selected[i]);
								selectedFilesArr[file.name] = elfinderInstance.url(selected[i]);
							}
						}
					},
				},
		  
				/* END */
				commandsOptions: {
				  edit: {
					mimes: [],
		  
					editors: [
					  {
						mimes: [
						  "text/plain",
						  "text/html",
						  "text/javascript",
						  "text/css",
						  "text/x-php",
						  "application/x-php",
						],
		  
						load: function (textarea) {
						  var mimeType = this.file.mime;
						  return CodeMirror.fromTextArea(textarea, {
							mode: mimeType,
							indentUnit: 4,
							lineNumbers: true,
							theme: "'.$fmarr["code_editor_theme"].'",
							viewportMargin: Infinity,
							lineWrapping: true,
						  });
						},
		  
						close: function (textarea, instance) {
						  this.myCodeMirror = null;
						},
					  },
					],
				  },
				  quicklook: {
					sharecadMimes: [
					  "image/vnd.dwg",
					  "image/vnd.dxf",
					  "model/vnd.dwf",
					  "application/vnd.hp-hpgl",
					  "application/plt",
					  "application/step",
					  "model/iges",
					  "application/vnd.ms-pki.stl",
					  "application/sat",
					  "image/cgm",
					  "application/x-msmetafile",
					],
					googleDocsMimes: [
					  "application/pdf",
					  "image/tiff",
					  "application/vnd.ms-office",
					  "application/msword",
					  "application/vnd.ms-word",
					  "application/vnd.ms-excel",
					  "application/vnd.ms-powerpoint",
					  "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
					  "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
					  "application/vnd.openxmlformats-officedocument.presentationml.presentation",
					  "application/postscript",
					  "application/rtf",
					],
					officeOnlineMimes: [
					  "application/vnd.ms-office",
					  "application/msword",
					  "application/vnd.ms-word",
					  "application/vnd.ms-excel",
					  "application/vnd.ms-powerpoint",
					  "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
					  "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
					  "application/vnd.openxmlformats-officedocument.presentationml.presentation",
					  "application/vnd.oasis.opendocument.text",
					  "application/vnd.oasis.opendocument.spreadsheet",
					  "application/vnd.oasis.opendocument.presentation",
					],
				  },
				},
			  })
			  .elfinder("instance");
			// mac fix
			if (navigator.userAgent.indexOf("Mac OS X") != -1) {
			  jQuery("body").addClass("mac");
			} else {
			  jQuery("body").addClass("windows");
			}';
			if($fmarr["openFullwidth"] == "yes"){
				$filemanagerReturn .= 'fm_without_code.bind("dialogopened", function(e) {
					var dialog = e.data.dialog;
					if (dialog.hasClass("elfinder-dialog-edit")) {
					  dialog.find(".elfinder-titlebar-button.elfinder-titlebar-full").trigger("mousedown");
					}
				  });';
			}
			$filemanagerReturn .= '});		  
		</script>';
}
else{
	$filemanagerReturn .= '<p>'.__('This shortcode has been expired. Please contact with the site admin.', 'wp-file-manager-pro').'</p>';
}
?>