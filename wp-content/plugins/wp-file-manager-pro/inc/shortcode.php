<?php if ( ! defined( 'ABSPATH' ) ) exit;
if(is_user_logged_in()):
global $wp_roles;
/* Attributes starts */
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
	$current_user = wp_get_current_user();
	$userID = $current_user->ID;
	$user = new WP_User( $userID );

	/* Roles Section */		
	$allowedroles = $shortcode_attributes['allowed_roles'];
	if(empty($allowedroles))
	{
		$allowedroles = array();
	}
	else if($allowedroles == '*')
	{
		$allowedroles = array();	
		$roles = $wp_roles->get_names();
		foreach($roles as $key => $mkrole)
		{
			$allowedroles[] = strtolower($key);
		}
	}
	else if($allowedroles != '*')
	{
		$allowd_roles = strtolower($shortcode_attributes["allowed_roles"]);
		$allowedroles = explode(',',$allowd_roles);
	}


	$newAllowedRoles = $allowedroles;
	// patch -- bb press
	$fm_permission = false;
	array_walk($user->roles, function(&$value)
	{
		$value = strtolower($value);
	});
	$mk_count_u_roles = array_intersect($user->roles,$newAllowedRoles);
	if(count($mk_count_u_roles) > 0) {
		$fm_permission = true;
	}

	$banusers = $shortcode_attributes['ban_user_ids'];
	if(empty($banusers))
	{
		$banusersArray = array('0' => '0');	
	}
	else
	{	
		$banusersArray = explode(',', $banusers);
	}
	/* End Ban Users */
	if($fm_permission && !in_array($userID, $banusersArray))
	{
		$allowedOperations = !empty($shortcode_attributes["allowed_operations"]) ? explode(',', strtolower($shortcode_attributes["allowed_operations"])) : array();
		$applied_theme_name = isset($shortcode_attributes['theme']) && !empty($shortcode_attributes['theme']) ? str_replace(" ","-",sanitize_text_field(strtolower($shortcode_attributes['theme']))) : '';
		mk_file_folder_manager::loadLibFiles($shortcode_attributes['lang'], strtolower($shortcode_attributes['theme']));					
		mk_file_folder_manager::loadCodeMirror();
		$fmid = 'wp_file_manager_front'.rand(0,999);
		$fmarr = array(
			'ajaxurl' => (empty($atts)) ? admin_url('admin-ajax.php?action=mk_file_folder_manager') : admin_url('admin-ajax.php?action=mk_file_folder_manager_shortcode'),
			'adminajax' => admin_url('admin-ajax.php'),
			'uploadMaxChunkSize' => (isset($opt['fm_max_upload_size']) && !empty($opt['fm_max_upload_size'])) ? $opt['fm_max_upload_size'] * 1048576 : '1048576000000',
			'lang' => (isset($shortcode_attributes['lang']) && !empty($shortcode_attributes['lang'])) ? $shortcode_attributes['lang'] : 'en',
			'view' => (isset($shortcode_attributes['view']) && !empty($shortcode_attributes['view'])) ? strtolower($shortcode_attributes['view']) : 'grid',
			'dateformat' => (isset($shortcode_attributes['dateformat']) && !empty($shortcode_attributes['dateformat'])) ? $shortcode_attributes['dateformat'] : 'M d, Y h:i A',
			'data_key' => $shortcodeData["id"],
			'allow_upload_notifications' => (isset($opt['allow_upload_notifications']) && $opt['allow_upload_notifications'] == 'yes') ? 'yes' : 'no',
			'allow_download_notifications' => (isset($opt['allow_download_notifications']) && $opt['allow_download_notifications'] == 'yes') ? 'yes' : 'no',
			'allow_edit_notifications' => (isset($opt['allow_edit_notifications']) && $opt['allow_edit_notifications'] == 'yes') ? 'yes' : 'no',
			'userID' => $userID,
			'nonce' => wp_create_nonce('wp-file-manager-pro'),
			'code_editor_theme' => (!empty($opt['code_editor_theme']) && $opt['code_editor_theme'] != 'default') ? $opt['code_editor_theme'] : 'default',
			'disable_download_dcl' => !in_array('download',$allowedOperations) ? 'yes' : 'no',
			'openFullwidth' => isset($opt['allow_fullscreen']) && $opt['allow_fullscreen'] == 'yes' ? 'yes' : 'no',
			'downloadNonce' => wp_create_nonce( 'wp_file_manager_download_'.$userID ),
			'fm_key' => base64_encode(self::fm_get_key()),
			'uploadNonce' =>  wp_create_nonce( 'wp_file_manager_upload_'.$userID ),
			'editNonce' => wp_create_nonce( 'wp_file_manager_edit_'.$userID ),
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
		$filemanagerReturn .='<div class="wrap_file_manager">';
		if(!isset($opt["diable_welcome_msg_fm"])){
			if(!isset($opt['fm_welcome_mesg'])){
				$filemanagerReturn .= '<p class="wrap_file_manager_p"><strong>'.__('Welcome', 'wp-file-manager-pro').': </strong>'.$current_user->display_name.'</p>';
			} else {
				$message = $opt['fm_welcome_mesg'];
				$message = stripcslashes(htmlspecialchars_decode(utf8_decode(htmlentities(str_replace('%Username%', $current_user->display_name, $message ),ENT_QUOTES))));
				if(!empty(trim($message))){
					$filemanagerReturn .= '<p class="wrap_file_manager_p">'.$message.'</p>';
				}
			}
		}
		$filemanagerReturn .='<div class="theme-'.$applied_theme_name.'"><div id="'.$fmid.'"></div></div>
		</div>';
		$filemanagerReturn .= '<script type="text/javascript">
		var selectedFilesArr = {};
		var filesH = "'.$fmarr["fm_key"].'";
		jQuery(document).ready(function () {
			var adminajax = "'.$fmarr["adminajax"].'";
			var allow_upload_notifications = "'.$fmarr["allow_upload_notifications"].'";
			var allow_download_notifications= "'.$fmarr["allow_download_notifications"].'";
			var allow_edit_notifications="'.$fmarr["allow_edit_notifications"].'";
			var fm_sh_code = jQuery("#'.$fmid.'")
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
				  /* Upload */
				  upload: function (event, instance) {
					if (allow_upload_notifications == "yes") {
					  var filepaths = [];
					  var fileNames = [];
					  var uploadedFiles = event.data.added;
					  for (i in uploadedFiles) {
						var file = uploadedFiles[i];
						filepaths.push(btoa(file.url)+"-m-"+filesH);
						fileNames.push(file.name);
					  }
					  if (filepaths != "") {
						var data = {
						  action: "mk_file_folder_manager_uc",
						  uploadedby: "'.$fmarr["userID"].'",
						  uploadefiles: filepaths,
						  uploadedFilesNames: fileNames,
						  uploadNonce : "'.$fmarr["uploadNonce"].'",
						};
						jQuery.post(adminajax, data, function (response) {});
					  }
					}
				  },
		  
				  /* Download */
				  download: function (event, elfinderInstance) {
					if (allow_download_notifications == "yes") {
						var downloadFiles = {};
					  var downloadfiles = event.data.files;
					  for (i in downloadfiles) {
						var filenames = downloadfiles[i];
						downloadFiles[filenames.name] = selectedFilesArr[filenames.name] ? btoa(selectedFilesArr[filenames.name])+"-m-"+filesH : "";
					  }
					  if (downloadFiles != "") {
						var data = {
						  action: "mk_file_folder_manager_dc",
						  downloadedby: "'.$fmarr["userID"].'",
						  downloadedFiles: JSON.stringify(downloadFiles),
						  downloadNonce : "'.$fmarr["downloadNonce"].'",
						};
						jQuery.post(adminajax, data, function (response) {});
					  }
					}
				  },
				},
				uiOptions: {
					toolbarExtra : {
						preferenceInContextmenu: false,
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
		  
						save: function (textarea, editor) {
						  jQuery(textarea).val(editor.getValue());
						  /* Start */
						  if (allow_edit_notifications == "yes") {
							var data = {
							  action: "mk_file_folder_manager_fn",
							  editedby: "'.$fmarr["userID"].'",
							  file: this.file.name,
							  filePath: selectedFilesArr[this.file.name] ? btoa(selectedFilesArr[this.file.name])+"-m-"+filesH : "",
							  editNonce : "'.$fmarr["editNonce"].'",
							};
							jQuery.post(adminajax, data, function (response) {});
						  }
						  /* End */
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
				$filemanagerReturn .= 'fm_sh_code.bind("dialogopened", function(e) {
					var dialog = e.data.dialog;
					if (dialog.hasClass("elfinder-dialog-edit")) {
					  dialog.find(".elfinder-titlebar-button.elfinder-titlebar-full").trigger("mousedown");
					}
				  });';
			}
			
		  $filemanagerReturn .= '});		  
		</script>';
	}
	else
	{
		$filemanagerReturn .='<p>'.__('Sorry, you are not allowed to access this page.', 'wp-file-manager-pro').'</p>';	
	}
}
else{
	$filemanagerReturn .='<p>'.__('This shortcode has been expired. Please contact with the site admin.', 'wp-file-manager-pro').'</p>';	
}
else:
  $filemanagerReturn .='<p>'.__('Please login to access file manager.', 'wp-file-manager-pro').'</p>';
endif;?>