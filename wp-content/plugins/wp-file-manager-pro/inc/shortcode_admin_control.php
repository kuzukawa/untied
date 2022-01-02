<?php if ( ! defined( 'ABSPATH' ) ) exit; 
if(is_user_logged_in()):
$fmkey = get_option('fm_key');
$opt = get_option('wp_filemanager_options');
$theme = isset($opt['theme']) && !empty($opt['theme']) ? sanitize_text_field($opt['theme']) : '';
$applied_theme_name = $theme != "" ? str_replace(" ","-",$theme) : '';                  
$lang = isset($opt['lang']) && !empty($opt['lang']) ? sanitize_text_field($opt['lang']) : 'en';
$current_user = wp_get_current_user();
$permissions = false;
			
            $allowedroles = isset($opt['fm_user_roles']) ? $opt['fm_user_roles'] : array();
			if(empty($allowedroles))
			{
				$allowedroles = array();
			}
         
			$userLogin = $current_user->user_login;
			$userID = $current_user->ID; 
			$user = new WP_User( $userID );
			if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
				foreach ( $user->roles as $role ):
					$role;
				endforeach;	
			}
			$mk_count_u_roles = array_intersect($user->roles,$allowedroles);
			if($role == 'administrator'):
				$permissions = true;
			elseif(in_array('administrator', $user->roles)):
				$permissions = true;
			elseif(in_array($role, $allowedroles)):
				$permissions = true;
			elseif(count($mk_count_u_roles) > 0):
				$permissions = true;	
			else:
				$permissions = false;
			endif;
if($permissions == true && $permissions) {
			$hide_toolbar = false;
			$hide_context_menu = false;
			$file_operations = array();
            $selected_user = (isset($opt['select_users']) && !empty($opt['select_users'])) ? $opt['select_users'] : array();
			$select_user_roles = (isset($opt['select_user_roles']) && !empty($opt['select_user_roles'])) ? $opt['select_user_roles'] : array();	
            if(in_array($userLogin, $selected_user)){
					$key = array_search($userLogin, $selected_user);
					if(!empty($opt['user_disable_toolbar_'.$key]) && $opt['user_disable_toolbar_'.$key] == 'yes'):		                      $hide_toolbar = true;
			        endif;					
					if(!empty($opt['user_disable_context_'.$key]) && $opt['user_disable_context_'.$key] == 'yes'):                      $hide_context_menu = true;
					endif;
					foreach($selected_user as $mkKey => $mkUser) {              
						if($mkUser == $userLogin) {
							$file_operations = isset($opt['users_fileoperations_'.$mkKey]) ? array_merge($opt['users_fileoperations_'.$mkKey],$file_operations) : $file_operations;
						}
					}
			} else if(in_array($role, $select_user_roles)) {
				    $key = array_search($role, $select_user_roles);
					if(!empty($opt['user_role_disable_toolbar_'.$key]) && $opt['user_role_disable_toolbar_'.$key] == 'yes'):		    $hide_toolbar = true;
                    endif;
					if(!empty($opt['user_role_disable_context_'.$key]) && $opt['user_role_disable_context_'.$key] == 'yes'):			$hide_context_menu = true;
					endif;
					foreach($select_user_roles as $mkRKey => $mkUserRole) {
						if($mkUserRole == $role) {
							if(isset($opt['userrole_fileoperations_'.$mkRKey])){
								$file_operations = isset($opt['userrole_fileoperations_'.$mkRKey]) ? array_merge($opt['userrole_fileoperations_'.$mkRKey],$file_operations) : $file_operations;
							}
						}
					}
			}
mk_file_folder_manager::loadLibFiles($lang, $theme);
mk_file_folder_manager::loadCodeMirror();
wp_register_script( "file_manager_pro_shortcode_admin", plugins_url('js/file_manager_pro_shortcode_admin.js', dirname( __FILE__ ) ), array('jquery'), mk_file_folder_manager::FILE_MANAGER_VERSION);
wp_localize_script( 'file_manager_pro_shortcode_admin', 'fmparams', array(
	'ajaxurl' => admin_url('admin-ajax.php'),
	'uploadMaxChunkSize' => (isset($opt['fm_max_upload_size']) && !empty($opt['fm_max_upload_size'])) ? $opt['fm_max_upload_size'] * 1048576 : '1048576000000',
	'lang' => $lang,
	'hide_toolbar' => $hide_toolbar,
	'hide_context_menu' => $hide_context_menu,
	'allow_upload_notifications' => (isset($opt['allow_upload_notifications']) && $opt['allow_upload_notifications'] == 'yes') ? 'yes' : 'no',
	'allow_download_notifications' => (isset($opt['allow_download_notifications']) && $opt['allow_download_notifications'] == 'yes') ? 'yes' : 'no',
	'allow_edit_notifications' => (isset($opt['allow_edit_notifications']) && $opt['allow_edit_notifications'] == 'yes') ? 'yes' : 'no',
	'userID' => $userID,
    'code_editor_theme' => (!empty($opt['code_editor_theme']) && $opt['code_editor_theme'] != 'default') ? $opt['code_editor_theme'] : 'default',
	'view' => (isset($opt['wp_fm_view']) && !empty($opt['wp_fm_view'])) ? $opt['wp_fm_view'] : 'list',
	'disable_download_dcl' => in_array('download',$file_operations) ? 'yes' : 'no',
	'openFullwidth' => isset($opt['allow_fullscreen']) && $opt['allow_fullscreen'] == 'yes' ? 'yes' : 'no',
	'uploadNonce' => wp_create_nonce( 'wp_file_manager_upload_'.$current_user->ID ),
	'downloadNonce' => wp_create_nonce( 'wp_file_manager_download_'.$current_user->ID ),
	'editNonce' => wp_create_nonce( 'wp_file_manager_edit_'.$current_user->ID ),
	'fmkey' => base64_encode($fmkey)
	)
);        
wp_enqueue_script( 'file_manager_pro_shortcode_admin' );
if(in_array('info',$file_operations)){
	$filemanagerReturn .= '<style>
	.theme-'.$applied_theme_name.' .elfinder .elfinder-contextmenu .elfinder-contextmenu-item.op-info{
display:none !important;
}
</style>';
}
if(in_array('download',$file_operations)){
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
			$filemanagerReturn .='<p class="wrap_file_manager_p">'.$message.'</p>';
		}
	}
}
$filemanagerReturn .='<div class="theme-'.$applied_theme_name.'"><div id="wp_file_manager"></div></div>
</div>';
}
else
{
  $filemanagerReturn .='<p>'.__('Sorry, you are not allowed to access this page.', 'wp-file-manager-pro').'</p>';	
}
else:
  $filemanagerReturn .='<p>'.__('Please login to access file manager.', 'wp-file-manager-pro').'</p>';
endif;?>