<?php if (!defined('ABSPATH')) {
    exit;
}
$this->fm_custom_assets();
global $wpdb;
// Updating
$opt = get_option('wp_filemanager_options');
$settings = get_option('wp_file_manager_pro_settings');
$path = str_replace('\\', '/', ABSPATH);
$shortcodes = $wpdb->get_results('SELECT id,shotcode_title FROM ' . $wpdb->prefix . 'wpfm_shortcodes');
$shortcode_titles = array();
if(!empty($shortcodes)){
	foreach($shortcodes as $row){
		$arry = array("id" => $row->id,"name" => $row->shotcode_title);
		$shortcode_titles[] = $arry;
	}
}
if (isset($_POST['save_shortcode']) && ((isset($_REQUEST['wp_filemanager_loggedin_nonce_field']) && wp_verify_nonce($_REQUEST['wp_filemanager_loggedin_nonce_field'], 'wp_filemanager_loggedin_shortcode')) || (isset($_REQUEST['wp_filemanager_nonloggedin_nonce_field']) && wp_verify_nonce($_REQUEST['wp_filemanager_nonloggedin_nonce_field'], 'wp_filemanager_nonloggedin_shortcode')) || (isset($_REQUEST['wp_filemanager_existing_nonce_field']) && wp_verify_nonce($_REQUEST['wp_filemanager_existing_nonce_field'], 'wp_filemanager_existing_shortcode')))){
	$type = sanitize_text_field($_REQUEST["shortcode_type"]);
	switch($type){
		case "loggedin":
			$allowed_operations = isset($_REQUEST["logged_allowed_operations"]) ? wp_unslash($_REQUEST["logged_allowed_operations"]) : '';
			$ban_user_ids = isset($_REQUEST["ban_user_ids"]) ? wp_unslash($_REQUEST["ban_user_ids"]) : '';
			$tab="loggedin";
		break;
		case "non_loggedin":
			$allowed_operations = isset($_REQUEST["nonlogged_allowed_operations"]) ? wp_unslash($_REQUEST["nonlogged_allowed_operations"]) : '';
			$tab="non_loggedin";
		break;
		case "existing":
			$existing_shortcode = trim(sanitize_text_field($_REQUEST['shortcode_added']));
			if(!empty($existing_shortcode)){
				$existing_shortcode = str_replace("'", "", $existing_shortcode);
				$existing_shortcode = str_replace('"', "", $existing_shortcode);
				$existing_shortcode = str_replace('[', "", $existing_shortcode);
				$existing_shortcode = str_replace(']', "", $existing_shortcode);
				$existing_shortcode = str_replace(' = ', "=", $existing_shortcode);
				$existing_shortcode = str_replace(' =', "=", $existing_shortcode);
				$existing_shortcode = str_replace('= ', "=", $existing_shortcode);
			}
			else {
				$error = __("Error in creating shortcode. Invalid shortcode.", 'wp-file-manager-pro');
			}
			$tab="existing";
		break;
	}
	if(!isset($error)){
		if($type == "existing") {
			if(strpos($existing_shortcode, 'wp_file_manager_without_login') !== false){
				$type = "non_loggedin";
				$existing_shortcode = str_replace('wp_file_manager_without_login ', "", $existing_shortcode);
				$param_arr = array('access_folder','view','dateformat','lang','theme','hide_files','lock_extensions','allowed_operations');
			} 
			else if(strpos($existing_shortcode, 'wp_file_manager_admin') !== false){
				$error = __("Error in creating shortcode. Invalid shortcode.", 'wp-file-manager-pro');
			}
			else if(strpos($existing_shortcode, 'wp_file_manager') !== false){
				$type = "loggedin";
				$existing_shortcode = str_replace('wp_file_manager ', "", $existing_shortcode);
				$param_arr = array('access_folder','view','dateformat','lang','theme','hide_files','lock_extensions','allowed_operations','allowed_roles','ban_user_ids');
			} else {
				$error = __("Error in creating shortcode. Invalid shortcode.", 'wp-file-manager-pro');
			}
			if(!isset($error)){
				$shortcode_attrs = explode("\ ",$existing_shortcode);
				if(!empty($shortcode_attrs)){
					$attr = array();					
					foreach($shortcode_attrs as $attr_val){
						$attr_arr = explode("=",$attr_val);
						$value = rtrim($attr_arr[1],"\/");
						$value = ltrim($value,"\/");
						$attr_key = trim($attr_arr[0]);
						if (($key = array_search($attr_key, $param_arr)) !== false) {
							if($attr_key == "allowed_operations" && $value == "*"){
								$operations = array('mkdir','mkfile','rename','duplicate', 'paste','archive','extract','copy', 'cut', 'edit', 'rm', 'download', 'upload', 'search','info','empty','resize');
								$attr[$attr_key] = implode(',',$operations);
							} else {
								array_splice($param_arr, $key, 1);
								$attr[$attr_key] = $value;
							}
						}
					}
					
					if(!empty($param_arr)){
						foreach($param_arr as $parm){
							switch($parm){
								case "view":
									$attr[$parm] = "list";
								break;
								case "theme":
									$attr[$parm] = "default";
								break;
								case "lang":
									$attr[$parm] = "en";
								break;
								case "allowed_operations":
									$operations = array('mkdir','mkfile','rename','duplicate', 'paste','archive','extract','copy', 'cut', 'edit', 'rm', 'download', 'upload', 'search','info','empty','resize');
									$attr[$parm] = implode(',',$operations);
								break;
								case "allowed_roles":
									$attr[$parm] = array('*');
								break;
								default:
									$attr[$parm] = "";
								break;
							}
						}
					}
				}
			}
		}
		else {
			
			$allowed_roles = isset($_REQUEST["allowed_roles"]) ? wp_unslash($_REQUEST["allowed_roles"]) : '';
			if($allowed_roles !=''){
				if(in_array("*",$allowed_roles)){
					$allowed_roles = array('*');
				}
			}
			
			$access_folder = str_replace('..', '', trim(sanitize_text_field($_REQUEST["access_folder"])));
			$hide_files = str_replace('..', '', trim(sanitize_text_field($_REQUEST["hide_files"])));
			$extensions = trim(sanitize_text_field($_REQUEST["lock_extensions"]));
			$lang = trim(sanitize_text_field($_REQUEST["lang"]));
			$wp_fm_view = trim(sanitize_text_field($_REQUEST["wp_fm_view"]));
			$fm_theme = trim(sanitize_text_field($_REQUEST["theme"]));
			$date_format = trim(sanitize_text_field($_REQUEST['date_format']));

			$attr = array(
				"access_folder" => $access_folder,
				"allowed_operations" => is_array($allowed_operations) ? implode(",",$allowed_operations) : '',
				"hide_files" => $hide_files,
				"lock_extensions" => $extensions,
				"lang" => $lang,
				"view" => $wp_fm_view,
				"theme" => $fm_theme,
				"dateformat" => $date_format,
			);
			if($type == "loggedin"){
				$attr["allowed_roles"] = is_array($allowed_roles) ? implode(",",$allowed_roles) : '';
				$attr["ban_user_ids"] = is_array($ban_user_ids) ? implode(",",$ban_user_ids) : '';
			}
		}
	}
	
	if(!isset($error)){
		_e('<strong>Saving Please wait...</strong>', 'wp-file-manager-pro');
		$shortcode_title = isset($_REQUEST["shortcode_title"]) ? sanitize_text_field($_REQUEST["shortcode_title"]) : '';
		$id = isset($_REQUEST["shortcode_id"]) ? intval(sanitize_text_field($_REQUEST["shortcode_id"])) : 0;
		if($id !== 0){
			//update
			$result = array(
				"shotcode_title" => $shortcode_title,
				"attributes" => serialize($attr)
			);
			$wpdb->update( $wpdb->prefix."wpfm_shortcodes", $result, array('id' => $id) );
		} else {
			$str=rand(); 
			$short_key = md5($str);
			$result = array(
				"shotcode_key" => $short_key,
				"shotcode_title" => $shortcode_title,
				"type" => $type,
				"attributes" => serialize($attr)
			);
			$wpdb->insert( $wpdb->prefix."wpfm_shortcodes", $result );
			$lastid = $wpdb->insert_id;
		}
		if(isset($lastid)){
			mk_file_folder_manager::redirect('?page=wp_file_manager_shortcode_generator&msg=1&exid='.$lastid.'&tb='.base64_encode($tab));
		}
		else{
			if($id != 0){
				mk_file_folder_manager::redirect('?page=wp_file_manager_shortcode_generator&msg=2&id='.$id.'&tb='.base64_encode($tab));
			} else {
				mk_file_folder_manager::redirect('?page=wp_file_manager_shortcode_generator&msg=1&tb='.base64_encode($tab));
			}
		}
	}
}

if(isset($_REQUEST["id"]) || isset($_REQUEST["exid"])){
	$request_id = isset($_REQUEST["id"]) ? trim(intval($_REQUEST["id"])) : trim(intval($_REQUEST["exid"]));
	$shortcode_data = $wpdb->get_row(
		$wpdb->prepare(
			'SELECT * FROM ' . $wpdb->prefix . 'wpfm_shortcodes WHERE id=%d',
			$request_id
		)
	);
	
	$shortcode_attributes = unserialize($shortcode_data->attributes);
	$attr_allowed_roles = !empty($shortcode_attributes["allowed_roles"]) ? explode(",",$shortcode_attributes["allowed_roles"]) : '';
	$attr_allowed_operations = !empty($shortcode_attributes["allowed_operations"]) ? explode(",",$shortcode_attributes["allowed_operations"]) : '';
	if($shortcode_data->type == "loggedin"){
		$attr_ban_user_ids = !empty($shortcode_attributes["ban_user_ids"]) ? explode(",",$shortcode_attributes["ban_user_ids"]) : '';
	}
}

if(isset($_REQUEST['tb'])){
	$selected_tab = base64_decode(trim($_REQUEST['tb']));
}
elseif(isset($shortcode_data)){
	$selected_tab = $shortcode_data->type;
}

global $wp_roles;
$roles = $wp_roles->get_names();
$allusers = get_users();
$file_operations = array(
	__('Make Directory', 'wp-file-manager-pro') => 'mkdir', 
	__('Make File', 'wp-file-manager-pro') => 'mkfile', 
	__('Rename', 'wp-file-manager-pro') => 'rename', 
	__('Duplicate', 'wp-file-manager-pro') => 'duplicate', 
	__('Paste', 'wp-file-manager-pro') => 'paste',
	__('Archive', 'wp-file-manager-pro') => 'archive',
	__('Extract', 'wp-file-manager-pro') => 'extract', 
	__('Copy', 'wp-file-manager-pro') => 'copy', 
	__('Cut', 'wp-file-manager-pro') => 'cut', 
	__( 'Edit', 'wp-file-manager-pro') => 'edit', 
	__('Delete', 'wp-file-manager-pro') => 'rm', 
	__('Download', 'wp-file-manager-pro') => 'download', 
	__('Upload', 'wp-file-manager-pro') => 'upload', 
	__('Search', 'wp-file-manager-pro') => 'search', 
	__('Info', 'wp-file-manager-pro') => 'info', 
	__('Empty', 'wp-file-manager-pro') => 'empty', 
	__('Resize', 'wp-file-manager-pro') => 'resize');
?>
<style>
.checklist{
	min-height: 250px;
}
.error{
	border: 1px solid #bb0000 !important;
}
.err-msg, .highlighted{
	color: #bb0000 !important;   
}
.err-msg {
	padding: 2px 5px;
	border: 1px solid #bb0000;
}
</style>

<div class="wrap">
	<div class="setting_pro_wrap fmInnerWrap fm_short_cgen">

		<h3 class="mainHeading">
			<span class="headingIcon"><img src="<?php echo plugins_url('images/fm-setting-icon.png', __FILE__); ?>"></span>
			<span class="headingText"><?php _e('Shortcode Generator', 'wp-file-manager-pro'); ?></span>
			<a title="<?php _e('Click to get list of all shortcodes', 'wp-file-manager-pro'); ?>" class="button button-primary" href="admin.php?page=wp_file_manager_existing_shortcodes"><?php _e('All Shortcodes', 'wp-file-manager-pro'); ?></a>
			<?php 
			if(isset($_REQUEST["id"])){
				?><a title="<?php _e('Click to create new shortcode', 'wp-file-manager-pro'); ?>" class="button button-primary" href="admin.php?page=wp_file_manager_shortcode_generator" style="float:right;"><?php _e('Generate New Shortcode', 'wp-file-manager-pro'); ?></a><?php
			}
			?>	
		</h3>

		<div class="set_tab_dv">
			<ul class="setting_pro_tab">
				<li <?php if(!isset($error)) { echo (isset($shortcode_data) && $shortcode_data->type == 'loggedin' && $selected_tab != 'existing') || (isset($selected_tab) && $selected_tab == 'loggedin') || (!isset($shortcode_data) && !isset($selected_tab)) ? 'class="current"' : ''; }?>>
					<a href="#loggedIn">
						<span class="icon"><img src="<?php echo plugins_url('images/user-icon.png', dirname(__FILE__)); ?>"/></span>
						<?php _e('Logged in users', 'wp-file-manager-pro'); ?>
					</a>
				</li>
				<li <?php if(!isset($error)){ echo (isset($shortcode_data) && $shortcode_data->type == 'non_loggedin' && $selected_tab != 'existing' ) || ((isset($selected_tab) && $selected_tab == 'non_loggedin')) ? 'class="current"' : '';}?>>
					<a href="#nonLoggedIn">
						<span class="icon"><img src="<?php echo plugins_url('images/user-icon.png', dirname(__FILE__)); ?>"/></span>
						<?php _e('Non logged in users', 'wp-file-manager-pro'); ?>
					</a>
				</li>
				<li <?php echo (isset($error) || (isset($selected_tab) && $selected_tab == 'existing')) ? 'class="current"' : '';?>>
					<a href="#existingOne">
						<span class="icon"><img src="<?php echo plugins_url('images/code-editor-icon.png', dirname(__FILE__)); ?>"/></span>
						<?php _e('From existing shortcode', 'wp-file-manager-pro'); ?>
					</a>
				</li>
			</ul>
			<div class="setting_pro_tab_content" id="loggedIn" <?php if(!isset($error)){ echo (isset($shortcode_data) && $shortcode_data->type == 'loggedin' && $selected_tab != 'existing') || (isset($selected_tab) && $selected_tab == 'loggedin') || (!isset($shortcode_data) && !isset($selected_tab)) ? 'style="display:block;"' : '';}?>>
				<form action="" method="post" id="ffm_logged_in">
					<?php  wp_nonce_field('wp_filemanager_loggedin_shortcode', 'wp_filemanager_loggedin_nonce_field'); ?>
					<input type="hidden" name="shortcode_type" value="loggedin"/>
					<input type="hidden" name="shortcode_id" value="<?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'loggedin' ? $shortcode_data->id : 0;?>"/>
					<?php
					if((isset($_REQUEST["exid"]) && isset($shortcode_data) && isset($shortcode_data->type) && $shortcode_data->type == 'loggedin') && (isset($selected_tab) && $selected_tab != 'existing')){
						$shortcode_name = $shortcode_data->type == "loggedin" ? "wp_file_manager" : "wp_file_manager_without_login";
						$shortcode_name = "[".$shortcode_name." id='".$shortcode_data->shotcode_key."' title='".$shortcode_data->shotcode_title."']";
						echo '<div class="notice notice-success shortcode-notice"><p>'.sprintf(__("Your shortcode <span style='background-color:#e5e5e5;'>%s</span> has been generated successfully. Copy this shortcode and paste it in your pages.",'wp-file-manager-pro'),$shortcode_name).'</p></div>';
					} else if(isset($_REQUEST["msg"]) && intval($_REQUEST["msg"]) == 2 && (isset($selected_tab) && $selected_tab == 'loggedin')){
						echo '<div class="notice notice-success shortcode-notice"><p>'.__("Your shortcode settings has been updated successfully.",'wp-file-manager-pro').'</p></div>';
					}
					?>

					<div class="frm_grp">
						<label for="shortcode_title" class="label_heading"><?php _e('Shortcode Title', 'wp-file-manager-pro'); ?></label>
						<div class="frm_grp_inner posRelative">
							<input type="text" maxlength="50" name="shortcode_title" id="shortcode_title" placeholder="<?php _e('Enter shortcode title', 'wp-file-manager-pro'); ?>" value="<?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'loggedin' ? $shortcode_data->shotcode_title : '';?>"/>
							<p class="mtop10 mbot10 description"><?php _e('It is required to identify the shortcode you are going to use.', 'wp-file-manager-pro'); ?></p>
							<p class="err-msg" id="err_msg_title" style="display:none;"><?php _e('This field can not be empty.', 'wp-file-manager-pro'); ?></p>
							<p class="err-msg" id="err_msg_existing_title" style="display:none;"><?php _e('Title with this name already exists. Please choose another title.', 'wp-file-manager-pro'); ?></p>
						</div> <!--frm_grp_inner-->
					</div> <!--frm_grp-->
					<div class="frm_grp">
						<label for="allowed_roles" class="label_heading"><?php _e('Allowed Roles', 'wp-file-manager-pro'); ?></label>
						<div class="frm_grp_inner">
							<span class="chk_span_outer">
								<span class="chk_box_span styledCheckbox">
									<input type="checkbox" id="chk_all" value="*"  name="allowed_roles[]" 
									<?php 
									if(!isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'loggedin' && isset($attr_allowed_roles)){
										if(is_array($attr_allowed_roles) && in_array('*',$attr_allowed_roles)){
											echo 'checked="checked"';
										}
									}?>
									/> 
									<span class="fm_checkmark"></span> 
								</span> <!--chk_box_span--> 
								<span class="chk_box_txt"> <?php _e('All', 'wp-file-manager-pro'); ?></span> 
							</span>
						<?php 
						foreach ($roles as $key => $role):
							?>
							<span class="chk_span_outer">
								<span class="chk_box_span styledCheckbox">
									<input class="chk-roles" type="checkbox" value="<?php echo $key; ?>"  name="allowed_roles[]" 
									<?php 
									if(!isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'loggedin' && isset($attr_allowed_roles)){
										if(is_array($attr_allowed_roles) && (in_array($key,$attr_allowed_roles) || in_array('*',$attr_allowed_roles))){
											echo 'checked="checked"';
										}
									}?>
									/> 
									<span class="fm_checkmark"></span> 
								</span> <!--chk_box_span--> 
								<span class="chk_box_txt"> <?php echo $role; ?></span> 
							</span> <!--chk_span_outer-->
						<?php endforeach; ?>
						<p class="mtop10 mbot10 description"><?php _e('It will allow all roles to access file manager on front end. All will apply for all available user roles.','wp-file-manager-pro'); ?></p>
						<p class="err-msg" id="err_msg_roles" style="display:none;"><?php _e('Please choose at least one user role.', 'wp-file-manager-pro'); ?></p>
						</div> <!--frm_grp_inner-->
					</div> <!--frm_grp-->

					<div class="frm_grp">
						<label for="access_folder" class="label_heading"><?php _e('Access Folder', 'wp-file-manager-pro'); ?></label>
						<div class="frm_grp_inner posRelative">
							
							 <div class="input-addon"><?php echo isset($settings['public_path']) && !empty($settings['public_path']) ? $settings['public_path'] : $path; ?></div>
							<input type="text" name="access_folder" id="access_folder" placeholder="<?php _e('Path of the folder to display e.g wp-content/uploads', 'wp-file-manager-pro'); ?>" value="<?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'loggedin' && isset($shortcode_attributes['access_folder']) ? $shortcode_attributes['access_folder'] : '';?>"/>
							<p class="mtop10 mbot10 description"><b><?php _e('Default','wp-file-manager-pro'); ?></b>: <?php _e('Root directory','wp-file-manager-pro'); ?></p>
							<p class="mtop10 mbot10 description"><?php _e('eg. You can put "test" as a name of folder which is located on root directory, or you can give path for sub folders as like "wp-content/plugins". If leave blank or empty it will access all folders on root directory. If you put * then it will auto creates user folders with their username.','wp-file-manager-pro'); ?></p>
						</div> <!--frm_grp_inner-->
					</div> <!--frm_grp-->

					<div class="frm_grp">
						<label for="logged_allowed_operations" class="label_heading"><?php _e('Allowed Operations', 'wp-file-manager-pro'); ?></label>
						<div class="frm_grp_inner posRelative">
							<select name="logged_allowed_operations" id="logged_allowed_operations" multiple="multiple" size="15">
								<?php if (!empty($file_operations) && is_array($file_operations)) {
									foreach ($file_operations as $operation_name => $file_operation) { ?>
											<option value="<?php echo $file_operation; ?>"
											<?php 
											if(!isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'loggedin' && isset($attr_allowed_operations)){
												if(is_array($attr_allowed_operations) && in_array($file_operation,$attr_allowed_operations)){
													echo 'selected="selected"';
												}
											}?>
											><?php echo $operation_name; ?></option>
										<?php
									}
								} ?>   
							</select>
							<p class="mtop10 mbot10 description"><b><?php _e('Default','wp-file-manager-pro'); ?></b>: <?php _e('None','wp-file-manager-pro'); ?></p>
							<p class="mtop10 mbot10 description"><?php _e('For all operations and to allow some operation you can choose operations name from the list.','wp-file-manager-pro'); ?></p>
						</div> <!--frm_grp_inner-->
					</div> <!--frm_grp-->

					<div class="frm_grp">
						<label for="ban_user_ids" class="label_heading"><?php _e('Ban User IDs', 'wp-file-manager-pro'); ?></label>
						<div class="frm_grp_inner posRelative">
							<select name="ban_user_ids" id="ban_user_ids" multiple="multiple" size="15">
								<?php foreach ($allusers as $user):?>
									<option value="<?php echo $user->ID; ?>"
									<?php 
									if(!isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'loggedin' && isset($attr_ban_user_ids)){
										if(is_array($attr_ban_user_ids) && in_array($user->ID,$attr_ban_user_ids)){
											echo 'selected="selected"';
										}
									}?>
									><?php echo $user->user_login." (".__('User ID', 'wp-file-manager-pro').": ".$user->ID.")"; ?></option>
								<?php endforeach; ?>
							</select>
						</div> <!--frm_grp_inner-->
						<p class="mtop10 mbot10 description"><b><?php _e('Default','wp-file-manager-pro'); ?></b>: <?php _e('None','wp-file-manager-pro'); ?></p>
						<p class="mtop10 mbot10 description"><?php _e('To ban specific user from accessing file manager on frontend then you can choose the users from the list.','wp-file-manager-pro'); ?></p>
					</div> <!--frm_grp-->

					<div class="frm_grp">
						<label for="hide_files" class="label_heading"><?php _e('Hide Files', 'wp-file-manager-pro'); ?></label>
						<div class="frm_grp_inner posRelative">
							<input type="text" name="hide_files" id="hide_files" placeholder="<?php _e('Hide files from access folder e.g. wp-content/plugins,wp-config.php etc', 'wp-file-manager-pro'); ?>" value="<?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'loggedin' && isset($shortcode_attributes['hide_files']) ? $shortcode_attributes['hide_files'] : '';?>"/>
							<p class="mtop10 mbot10 description"><b><?php _e('Default','wp-file-manager-pro'); ?></b>: Null</p>
							<p class="mtop10 mbot10 description"><?php _e('It will hide the files and folder mentioned here. <br><strong>Note:</strong> This option is case sensitive and should be comma(,) separated without having white space between them.','wp-file-manager-pro'); ?></p>
						</div> <!--frm_grp_inner-->
					</div> <!--frm_grp-->
					<div class="frm_grp">
						<label for="lock_extensions" class="label_heading"><?php _e('Lock Extensions', 'wp-file-manager-pro'); ?></label>
						<div class="frm_grp_inner posRelative">
							<input type="text" name="lock_extensions" id="lock_extensions" placeholder="<?php _e('Restrict extensions e.g. .php,.css OR php,css', 'wp-file-manager-pro'); ?>" value="<?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'loggedin' && isset($shortcode_attributes['lock_extensions']) ? $shortcode_attributes['lock_extensions'] : '';?>" onkeypress="return AvoidSpace(event)"/>
							<p class="mtop10 mbot10 description"><b><?php _e('Default','wp-file-manager-pro'); ?></b>: Null</p>
							<p class="mtop10 mbot10 description"><?php _e('It will lock files mentioned in commas without white space between them. You can lock more as like ".php,.css,.js" etc.<br><span class="highlighted"><strong>Note:</strong> The extensions in the option are case sensitive and should be comma(,) separated without having white space between them. e.g. php,PHP</span>','wp-file-manager-pro'); ?></p>
						</div> <!--frm_grp_inner-->
					</div> <!--frm_grp-->

					<div class="frm_grp">
						<label for="default_category" class="label_heading"><?php _e('Select Filemanager Language', 'wp-file-manager-pro'); ?></label>
						<div class="frm_grp_inner">
							<select name="lang">
								<?php foreach ($this->fm_languages() as $name => $lang) {
									?>
									<option value="<?php echo $lang; ?>" <?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'loggedin' && isset($shortcode_attributes['lang']) ? $shortcode_attributes['lang'] == $lang ? 'selected="selected"' : '' : ''; ?>><?php echo $name; ?> (<?php echo $lang; ?>)</option>
									<?php
								} ?>
							</select>

							<p class="mtop10 mbot0 description"> <strong><?php _e('Default:', 'wp-file-manager-pro'); ?> </strong> <?php _e('English', 'wp-file-manager-pro'); ?> </p>
							<p class="mtop10 mbot10 description"><?php _e('You can select any language for filemanager.', 'wp-file-manager-pro'); ?></p>
						</div> <!--frm_grp_inner-->
					</div> <!--frm_grp-->

					<div class="frm_grp">
						<label for="default_category" class="label_heading"><?php _e('Select Filemanager Theme', 'wp-file-manager-pro'); ?></label>
						<div class="frm_grp_inner">
							<select name="theme" id="fm_theme">
								<option value="default" <?php echo (isset($opt['theme']) && $opt['theme'] == 'default') ? 'selected="selected"' : ''; ?>><?php  _e('Default', 'wp-file-manager-pro'); ?></option>
								<?php foreach ($this->get_themes() as $theme) {
									?>
								<option value="<?php echo $theme; ?>" <?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'loggedin' && isset($shortcode_attributes['theme']) ? $shortcode_attributes['theme'] == $theme ? 'selected="selected"' : '' : ''; ?>><?php echo ucfirst($theme); ?></option>
								<?php
								} ?>
							</select>

							<p class="mtop10 mbot0 description"><strong><?php _e('Default:', 'wp-file-manager-pro'); ?> </strong> <?php _e('Default', 'wp-file-manager-pro'); ?></p>
							<p class="mtop10 mbot10 description"><?php _e('You can select any theme for filemanager.', 'wp-file-manager-pro'); ?></p>
						</div> <!--frm_grp_inner-->
					</div> <!--frm_grp-->

					<?php /* File Manager Default View */ ?>
					<div class="frm_grp">
						<label for="default_category" class="label_heading"><?php _e('Files View','wp-file-manager-pro'); ?></label>
						<div class="frm_grp_inner">
							<select name="wp_fm_view" id="wp_fm_view">
								<option value="list" <?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'loggedin' && $shortcode_attributes['view'] == 'list' ? 'selected="selected"' : ''; ?>><?php  _e('List', 'wp-file-manager-pro'); ?></option>
								<option value="grid" <?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'loggedin' && $shortcode_attributes['view'] == 'grid' ? 'selected="selected"' : ''; ?>><?php  _e('Icon', 'wp-file-manager-pro'); ?></option>
							</select>
							<p class="mtop10 mbot0 description"><strong><?php _e('Default:','wp-file-manager-pro'); ?> </strong> <?php _e('List','wp-file-manager-pro'); ?></p>
							<p class="mtop10 mbot10 description"><?php _e('You can select any view for filemanager.','wp-file-manager-pro'); ?></p>
						</div> <!--frm_grp_inner-->
					</div> <!--frm_grp-->
				
					<div class="frm_grp">
						<label for="date_format" class="label_heading"><?php _e('Date Format', 'wp-file-manager-pro'); ?></label>
						<div class="frm_grp_inner posRelative">
							<select name="date_format">
								<option value="d M, Y h:i A" <?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'loggedin' && isset($shortcode_attributes['dateformat']) && $shortcode_attributes['dateformat'] == 'd M, Y h:i A' ? 'selected="selected"' : '';?>>d M, Y h:i A</option>
								<option value="d/m/Y h:i A" <?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'loggedin' && isset($shortcode_attributes['dateformat']) && $shortcode_attributes['dateformat'] == 'd/m/Y h:i A' ? 'selected="selected"' : '';?>>d/m/Y h:i A</option>
								<option value="Y/m/d h:i A" <?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'loggedin' && isset($shortcode_attributes['dateformat']) && $shortcode_attributes['dateformat'] == 'Y/m/d h:i A' ? 'selected="selected"' : '';?>>Y/m/d h:i A</option>
								<option value="d-m-Y h:i A" <?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'loggedin' && isset($shortcode_attributes['dateformat']) && $shortcode_attributes['dateformat'] == 'd-m-Y h:i A' ? 'selected="selected"' : '';?>>d-m-Y h:i A</option>
								<option value="Y-m-d h:i A" <?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'loggedin' && isset($shortcode_attributes['dateformat']) && $shortcode_attributes['dateformat'] == 'Y-m-d h:i A' ? 'selected="selected"' : '';?>>Y-m-d h:i A</option>
								<option value="D, y-M h:i A" <?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'loggedin' && isset($shortcode_attributes['dateformat']) && $shortcode_attributes['dateformat'] == 'D, y-M h:i A' ? 'selected="selected"' : '';?>>D, y-M h:i A</option>
							</select>
						    <p class="mtop10 mbot10 description"><?php _e('You can select any date format for filemanager modified date.','wp-file-manager-pro'); ?></p>
						</div> <!--frm_grp_inner-->
					</div> <!--frm_grp-->

					<div class="frm_grp fm_btn_br">
						<div class="btnDv">
							<input type="submit" value="<?php isset($_REQUEST["id"]) && !isset($_REQUEST["exid"]) ? _e('Update Shortcode', 'wp-file-manager-pro') : _e('Generate Shortcode', 'wp-file-manager-pro'); ?>" class="button button-primary fmCustomBtn" id="submit" name="save_shortcode">
						</div>
					</div>
				</form>
			</div> <!--General-->

			<div class="setting_pro_tab_content" id="nonLoggedIn" <?php if(!isset($error)) { echo (isset($shortcode_data) && $shortcode_data->type == 'non_loggedin' && $selected_tab != 'existing') || (isset($selected_tab) && $selected_tab == 'non_loggedin') ? 'style="display:block;"' : '';}?>>
				<form action="" method="post" id="ffm_non_logged_in">
					<?php  wp_nonce_field('wp_filemanager_nonloggedin_shortcode', 'wp_filemanager_nonloggedin_nonce_field'); ?>
					<input type="hidden" name="shortcode_type" value="non_loggedin"/>
					<input type="hidden" name="shortcode_id" value="<?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'non_loggedin' ? $shortcode_data->id : 0;?>"/>
					<?php
					if(isset($_REQUEST["exid"]) && isset($shortcode_data) && isset($shortcode_data->type) && $shortcode_data->type == 'non_loggedin' && isset($selected_tab) && $selected_tab != 'existing'){
						$shortcode_name = $shortcode_data->type == "loggedin" ? "wp_file_manager" : "wp_file_manager_without_login";
						$shortcode_name = "[".$shortcode_name." id='".$shortcode_data->shotcode_key."' title='".$shortcode_data->shotcode_title."']";
						echo '<div class="notice notice-success shortcode-notice"><p>'.sprintf(__("Your shortcode <span style='background-color:#e5e5e5;'>%s</span> has been generated successfully. Copy this shortcode and paste it in your pages.",'wp-file-manager-pro'),$shortcode_name).'</p></div>';
					} else if(isset($_REQUEST["msg"]) && intval($_REQUEST["msg"]) == 2 && (isset($selected_tab) && $selected_tab == 'non_loggedin')){
						echo '<div class="notice notice-success shortcode-notice"><p>'.__("Your shortcode settings has been updated successfully.",'wp-file-manager-pro').'</p></div>';
					}?>

					<div class="frm_grp">
						<label for="shortcode_title" class="label_heading"><?php _e('Shortcode Title', 'wp-file-manager-pro'); ?></label>
						<div class="frm_grp_inner posRelative">
							<input type="text" maxlength="50" name="shortcode_title" id="shortcode_title" placeholder="<?php _e('Enter shortcode title', 'wp-file-manager-pro'); ?>" value="<?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'non_loggedin' ? $shortcode_data->shotcode_title : '';?>"/>
							<p class="mtop10 mbot10 description"><?php _e('It is required to identify the shortcode you are going to use.', 'wp-file-manager-pro'); ?></p>
							<p class="err-msg" id="err_msg_title" style="display:none;"><?php _e('This field can not be empty.', 'wp-file-manager-pro'); ?></p>
							<p class="err-msg" id="err_msg_existing_title" style="display:none;"><?php _e('Title with this name already exists. Please choose another title.', 'wp-file-manager-pro'); ?></p>
						</div> <!--frm_grp_inner-->
					</div> <!--frm_grp-->
					<div class="frm_grp">
						<label for="access_folder" class="label_heading"><?php _e('Access Folder', 'wp-file-manager-pro'); ?></label>
						<div class="frm_grp_inner posRelative">
							<div class="input-addon"><?php echo isset($settings['public_path']) && !empty($settings['public_path']) ? $settings['public_path'] : $path; ?></div>
							<input type="text" name="access_folder" id="access_folder" placeholder="<?php _e('Path of the folder to display e.g wp-content/uploads', 'wp-file-manager-pro'); ?>" value="<?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'non_loggedin' ? $shortcode_attributes['access_folder'] : '';?>"/>
							<p class="mtop10 mbot10 description"><b><?php _e('Default','wp-file-manager-pro'); ?></b>: <?php _e('Root directory','wp-file-manager-pro'); ?></p>
							<p class="mtop10 mbot10 description"><?php _e('eg. You can put "test" as a name of folder which is located on root directory, or you can give path for sub folders as like "wp-content/plugins". If leave blank or empty it will access all folders on root directory. If you put * then it will auto creates user folders with their username.','wp-file-manager-pro'); ?></p>
						</div> <!--frm_grp_inner-->
					</div> <!--frm_grp-->

					<div class="frm_grp">
						<label for="nonlogged_allowed_operations" class="label_heading"><?php _e('Allowed Operations', 'wp-file-manager-pro'); ?></label>
						<div class="frm_grp_inner posRelative">
							<select name="nonlogged_allowed_operations" id="nonlogged_allowed_operations" multiple="multiple" size="15">
								<?php if (!empty($file_operations) && is_array($file_operations)) {
									foreach ($file_operations as $operation_name => $file_operation) { ?>
											<option value="<?php echo $file_operation; ?>"
											<?php 
											if(!isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'non_loggedin'){
												if(is_array($attr_allowed_operations) && in_array($file_operation,$attr_allowed_operations)){
													echo 'selected="selected"';
												}
											}?>
											><?php echo $operation_name; ?></option>
										<?php
									}
								} ?>   
							</select>
						    <p class="mtop10 mbot10 description"><b><?php _e('Default','wp-file-manager-pro'); ?></b>: <?php _e('None','wp-file-manager-pro'); ?></p>
							<p class="mtop10 mbot10 description"><?php _e('For all operations and to allow some operation you can choose operations name from the list.','wp-file-manager-pro'); ?></p>
						</div> <!--frm_grp_inner-->
					</div> <!--frm_grp-->

					<div class="frm_grp">
						<label for="hide_files" class="label_heading"><?php _e('Hide Files', 'wp-file-manager-pro'); ?></label>
						<div class="frm_grp_inner posRelative">
							<input type="text" name="hide_files" id="hide_files" placeholder="<?php _e('Hide files from access folder e.g. test1,folder/test2 etc', 'wp-file-manager-pro'); ?>" value="<?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'non_loggedin' ? $shortcode_attributes['hide_files'] : '';?>"/>
							<p class="mtop10 mbot10 description"><b><?php _e('Default','wp-file-manager-pro'); ?></b>: Null</p>
							<p class="mtop10 mbot10 description"><?php _e('It will hide the files and folder mentioned here. Note: This option is case sensitive and should be comma(,) separated without having white space between them.','wp-file-manager-pro'); ?></p>
						</div> <!--frm_grp_inner-->
					</div> <!--frm_grp-->

					<div class="frm_grp">
						<label for="lock_extensions" class="label_heading"><?php _e('Lock Extensions', 'wp-file-manager-pro'); ?></label>
						<div class="frm_grp_inner posRelative">
							<input type="text" name="lock_extensions" id="lock_extensions" placeholder="<?php _e('Restrict extensions e.g. .php,.css OR php,css', 'wp-file-manager-pro'); ?>" value="<?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'non_loggedin' ? $shortcode_attributes['lock_extensions'] : '';?>" onkeypress="return AvoidSpace(event)"/>
						    <p class="mtop10 mbot10 description"><b><?php _e('Default','wp-file-manager-pro'); ?></b>: Null</p>
							<p class="mtop10 mbot10 description"><?php _e('It will lock files mentioned in commas without white space between them. You can lock more as like ".php,.css,.js" etc.<br><span class="highlighted"><strong>Note:</strong> The extensions in the option are case sensitive and should be comma(,) separated without having white space between them. e.g. php,PHP</span>','wp-file-manager-pro'); ?></p>
						</div> <!--frm_grp_inner-->
					</div> <!--frm_grp-->

					<div class="frm_grp">
						<label for="default_category" class="label_heading"><?php _e('Select Filemanager Language', 'wp-file-manager-pro'); ?></label>
						<div class="frm_grp_inner">
							<select name="lang">
								<?php foreach ($this->fm_languages() as $name => $lang) {
									?>
									<option value="<?php echo $lang; ?>" <?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'non_loggedin' && $shortcode_attributes['lang'] == $lang ? 'selected="selected"' : ''; ?>><?php echo $name; ?> (<?php echo $lang; ?>)</option>
									<?php
								} ?>
							</select>

							<p class="mtop10 mbot0 description"> <strong><?php _e('Default:', 'wp-file-manager-pro'); ?> </strong> <?php _e('English', 'wp-file-manager-pro'); ?> </p>
							<p class="mtop10 mbot10 description"><?php _e('You can select any language for filemanager.', 'wp-file-manager-pro'); ?></p>
						</div> <!--frm_grp_inner-->
					</div> <!--frm_grp-->

					<div class="frm_grp">
						<label for="default_category" class="label_heading"><?php _e('Select Filemanager Theme', 'wp-file-manager-pro'); ?></label>
							<div class="frm_grp_inner">
								<select name="theme" id="fm_theme">
									<option value="default" <?php echo (isset($opt['theme']) && $opt['theme'] == 'default') ? 'selected="selected"' : ''; ?>><?php  _e('Default', 'wp-file-manager-pro'); ?></option>
									<?php foreach ($this->get_themes() as $theme) {
										?>
									<option value="<?php echo $theme; ?>" <?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'non_loggedin' && isset($shortcode_attributes['theme']) ? $shortcode_attributes['theme'] == $theme ? 'selected="selected"' : '' : ''; ?>><?php echo ucfirst($theme); ?></option>
									<?php
									} ?>
								</select>

								<p class="mtop10 mbot0 description"><strong><?php _e('Default:', 'wp-file-manager-pro'); ?> </strong> <?php _e('Default', 'wp-file-manager-pro'); ?></p>
								<p class="mtop10 mbot10 description"><?php _e('You can select any theme for filemanager.', 'wp-file-manager-pro'); ?></p>
							</div> <!--frm_grp_inner-->
						</div> <!--frm_grp-->

						<?php /* File Manager Default View */ ?>
						<div class="frm_grp">
							<label for="default_category" class="label_heading"><?php _e('Files View','wp-file-manager-pro'); ?></label>
							<div class="frm_grp_inner">
								<select name="wp_fm_view" id="wp_fm_view">
									<option value="list" <?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'non_loggedin' && isset($shortcode_attributes['view']) ? $shortcode_attributes['view'] == 'list' ? 'selected="selected"' : '' : ''; ?>><?php  _e('List', 'wp-file-manager-pro'); ?></option>
									<option value="grid" <?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'non_loggedin' && isset($shortcode_attributes['view']) ? $shortcode_attributes['view'] == 'grid' ? 'selected="selected"' : '' : ''; ?>><?php  _e('Icon', 'wp-file-manager-pro'); ?></option>
								</select>
								<p class="mtop10 mbot0 description"><strong><?php _e('Default:','wp-file-manager-pro'); ?> </strong> <?php _e('List','wp-file-manager-pro'); ?></p>
								<p class="mtop10 mbot10 description"><?php _e('You can select any view for filemanager.','wp-file-manager-pro'); ?></p>
							</div> <!--frm_grp_inner-->
						</div> <!--frm_grp-->
					<div class="frm_grp">
						<label for="date_format" class="label_heading"><?php _e('Date Format', 'wp-file-manager-pro'); ?></label>
						<div class="frm_grp_inner posRelative">
							<select name="date_format">
								<option value="d M, Y h:i A" <?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'non_loggedin' && isset($shortcode_attributes['dateformat']) && $shortcode_attributes['dateformat'] == 'd M, Y h:i A' ? 'selected="selected"' : '';?>>d M, Y h:i A</option>
								<option value="d/m/Y h:i A" <?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'non_loggedin' && isset($shortcode_attributes['dateformat']) && $shortcode_attributes['dateformat'] == 'd/m/Y h:i A' ? 'selected="selected"' : '';?>>d/m/Y h:i A</option>
								<option value="Y/m/d h:i A" <?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'non_loggedin' && isset($shortcode_attributes['dateformat']) && $shortcode_attributes['dateformat'] == 'Y/m/d h:i A' ? 'selected="selected"' : '';?>>Y/m/d h:i A</option>
								<option value="d-m-Y h:i A" <?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'non_loggedin' && isset($shortcode_attributes['dateformat']) && $shortcode_attributes['dateformat'] == 'd-m-Y h:i A' ? 'selected="selected"' : '';?>>d-m-Y h:i A</option>
								<option value="Y-m-d h:i A" <?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'non_loggedin' && isset($shortcode_attributes['dateformat']) && $shortcode_attributes['dateformat'] == 'Y-m-d h:i A' ? 'selected="selected"' : '';?>>Y-m-d h:i A</option>
								<option value="D, y-M h:i A" <?php echo !isset($_REQUEST["exid"]) && isset($shortcode_data) && $shortcode_data->type == 'non_loggedin' && isset($shortcode_attributes['dateformat']) && $shortcode_attributes['dateformat'] == 'D, y-M h:i A' ? 'selected="selected"' : '';?>>D, y-M h:i A</option>
							</select>
						    <p class="mtop10 mbot10 description"><?php _e('You can select any date format for filemanager modified date.', 'wp-file-manager-pro'); ?></p>
						</div> <!--frm_grp_inner-->
					</div> <!--frm_grp-->
				
					<div class="frm_grp">
						<div class="btnDv">
							<input type="submit" value="<?php isset($_REQUEST["id"]) && !isset($_REQUEST["exid"]) ? _e('Update Shortcode', 'wp-file-manager-pro') : _e('Generate Shortcode', 'wp-file-manager-pro'); ?>" class="button button-primary fmCustomBtn" id="submit" name="save_shortcode">
						</div>
					</div>
				</form>
			</div>
			<div class="setting_pro_tab_content" id="existingOne" <?php echo (isset($error) || (isset($selected_tab) && $selected_tab == 'existing')) ? 'style="display:block;"' : '';?>>
				<form method="post" id="ffm_existingOne">
					<?php  wp_nonce_field('wp_filemanager_existing_shortcode', 'wp_filemanager_existing_nonce_field'); ?>
					<input type="hidden" name="shortcode_type" value="existing"/>
					<?php 
					if(isset($error)){
						echo '<div class="update-nag notice notice-warning inline shortcode-notice">'.$error.'</div>';
					}
					else if(isset($_REQUEST["exid"]) && isset($shortcode_data) && isset($shortcode_data->type) && isset($selected_tab) && $selected_tab == 'existing'){
						$shortcode_name = $shortcode_data->type == "loggedin" ? "wp_file_manager" : "wp_file_manager_without_login";
						$shortcode_name = "[".$shortcode_name." id='".$shortcode_data->shotcode_key."' title='".$shortcode_data->shotcode_title."']";
						echo '<div class="notice notice-success shortcode-notice"><p>'.sprintf(__("Your shortcode <span style='background-color:#e5e5e5;'>%s</span> has been generated successfully. Copy this shortcode and paste it in your pages.",'wp-file-manager-pro'),$shortcode_name).'</p></div>';
					}
					?>
					<div class="frm_grp">
						<label for="shortcode_title" class="label_heading"><?php _e('Shortcode Title', 'wp-file-manager-pro'); ?></label>
						<div class="frm_grp_inner posRelative">
							<input type="text" maxlength="50" name="shortcode_title" id="shortcode_title" placeholder="<?php _e('Enter shortcode title', 'wp-file-manager-pro'); ?>" value=""/>
							<p class="mtop10 mbot0 description"><?php _e('It is required to identify the shortcode you are going to use.','wp-file-manager-pro'); ?></p>
							<p class="err-msg" id="err_msg_title" style="display:none;"><?php _e('This field can not be empty.', 'wp-file-manager-pro'); ?></p>
							<p class="err-msg" id="err_msg_existing_title" style="display:none;"><?php _e('Title with this name already exists. Please choose another title.', 'wp-file-manager-pro'); ?></p>
						</div> <!--frm_grp_inner-->
					</div> <!--frm_grp-->
					<div class="frm_grp">
						<label for="existing_shorcode" class="label_heading"><?php _e('Generate New Shortcode from existing one', 'wp-file-manager-pro'); ?></label>
						<div class="chk_list">
							<div class="frm_grp_inner">
								<textarea class="large-text code" rows="3" id="shortcode_added" name="shortcode_added" placeholder="<?php _e('Paste the shortcode which you are using in your page/post', 'wp-file-manager-pro'); ?>"></textarea>
								<p class="mtop10 mbot0 description"><?php _e('This will convert the existing shortcode to the new shortcode format.','wp-file-manager-pro'); ?></p>
								<p class="err-msg" id="err_msg_shortcode" style="display:none;"><?php _e('This field can not be empty.', 'wp-file-manager-pro'); ?></p>
							</div> <!--frm_grp_inner-->
						</div>
					</div>  <!--frm_grp-->
					<div class="frm_grp">
						<div class="btnDv">
							<input type="submit" value="<?php _e('Generate Shortcode', 'wp-file-manager-pro'); ?>" class="button button-primary fmCustomBtn" id="submit" name="save_shortcode">
						</div>
					</div>
				</form>
			</div>
		</div> <!--set_tab_dv-->
	</div> <!--setting_pro_wrap-->
</div>
<script type="text/javascript">
	var is_editing = parseInt(<?php echo isset($_REQUEST['id']) ? intval(trim($_REQUEST['id'])) : 0;?>);
	var existing_title = jQuery.parseJSON('<?php echo json_encode($shortcode_titles);?>');
	function filterJsonArr(arr,val){
		var id = -1;
		arr.filter(function (item) {
			if(item.name == val){
				id = parseInt(item.id);
			}
		});
		return id;
	}
	
	jQuery(document).ready(function(){
		jQuery('#ffm_logged_in,#ffm_non_logged_in,#ffm_existingOne').on('keyup keypress', function(e) {
			var keyCode = e.keyCode || e.which;
			if (keyCode === 13) {
				e.preventDefault();
				return false;
			}
		});
	});

	jQuery(document).on('submit', "#ffm_logged_in", function(){
		var input_val = jQuery.trim(jQuery("#ffm_logged_in #shortcode_title").val());
		var is_valid = true;
		if(input_val == ""){
			jQuery("#ffm_logged_in #shortcode_title").addClass("error").focus();
			jQuery("#ffm_logged_in #err_msg_existing_title").css("display","none");
			jQuery("#ffm_logged_in #err_msg_title").css("display","block");
			is_valid = false;
		} else {
			jQuery("#ffm_logged_in #shortcode_title").removeClass("error");
			jQuery("#ffm_logged_in #err_msg_title").css("display","none");
		}

		if(jQuery('.chk-roles:checked').length == 0){
			jQuery(".chk-roles").focus();
			jQuery("#ffm_logged_in #err_msg_roles").css("display","block");
			is_valid = false;
		} else {
			jQuery("#ffm_logged_in #err_msg_roles").css("display","none");
		}

		var db_key = filterJsonArr(existing_title,input_val);
		if(db_key != -1 && input_val != ""){
			if(db_key != is_editing){
				jQuery("#ffm_logged_in #shortcode_title").addClass("error").focus();
				jQuery("#ffm_logged_in #err_msg_title").css("display","none");
				jQuery("#ffm_logged_in #err_msg_existing_title").css("display","block");
				is_valid = false;
			}
		}

		if(is_valid == true){
			jQuery("#ffm_logged_in #shortcode_title").removeClass("error");
			jQuery("#ffm_logged_in #err_msg_title").css("display","none");
			jQuery("#ffm_logged_in #err_msg_roles").css("display","none");
			jQuery("#ffm_logged_in #err_msg_existing_title").css("display","none");
			return true;
		}
		else{
			return false;
		}
	});

	jQuery(document).on('submit', "#ffm_non_logged_in", function(){
		var input_val = jQuery.trim(jQuery("#ffm_non_logged_in #shortcode_title").val());
		var is_valid = true;
		
		if(input_val == ""){
			jQuery("#ffm_non_logged_in #shortcode_title").addClass("error").focus();
			jQuery("#ffm_non_logged_in #err_msg_existing_title").css("display","none");
			jQuery("#ffm_non_logged_in #err_msg_title").css("display","block");
			is_valid = false;
		} else {
			var db_key = filterJsonArr(existing_title,input_val);
			if(db_key != -1){
				if(db_key != is_editing){
					jQuery("#ffm_non_logged_in #shortcode_title").addClass("error").focus();
					jQuery("#ffm_non_logged_in #err_msg_title").css("display","none");
					jQuery("#ffm_non_logged_in #err_msg_existing_title").css("display","block");
					is_valid = false;
				}
			}
		}

		if(is_valid == true){
			jQuery("#ffm_non_logged_in #shortcode_title").removeClass("error");
			jQuery("#ffm_non_logged_in #err_msg_title").css("display","none");
			jQuery("#ffm_non_logged_in #err_msg_existing_title").css("display","none");
			return true;
		}
		else{
			return false;
		}
	});
	
	jQuery(document).on('submit', '#ffm_existingOne', function(){
		var input_val = jQuery.trim(jQuery("#ffm_existingOne #shortcode_title").val());
		var text_val = jQuery.trim(jQuery("#shortcode_added").val());
		var is_valid = true;
		if(input_val == ""){
			jQuery("#ffm_existingOne #shortcode_title").addClass("error");
			jQuery("#ffm_existingOne #err_msg_existing_title").css("display","none");
			jQuery("#ffm_existingOne #err_msg_title").css("display","block");
			is_valid = false;
		} else {
			var db_key = filterJsonArr(existing_title,input_val);
			if(db_key != -1){
				if(db_key != is_editing){
					jQuery("#ffm_existingOne #shortcode_title").addClass("error").focus();
					jQuery("#ffm_existingOne #err_msg_title").css("display","none");
					jQuery("#ffm_existingOne #err_msg_existing_title").css("display","block");
					is_valid = false;
				}
			}
		}

		if(text_val == ""){
			jQuery("#shortcode_added").addClass("error");
			jQuery("#err_msg_shortcode").css("display","block");
			is_valid = false;
		}

		if(is_valid == true){
			jQuery("#ffm_existingOne #shortcode_title").removeClass("error");
			jQuery("#ffm_existingOne #err_msg_title").css("display","none");
			jQuery("#ffm_existingOne #err_msg_existing_title").css("display","none");
			jQuery("#shortcode_added").removeClass("error");
			jQuery("#err_msg_shortcode").css("display","none");
			return true;
		}
		else{
			return false;
		}	
	})
	jQuery(document).on("click","#chk_all", function(){
		if(jQuery(this).prop("checked") == true){
			jQuery(".chk-roles").each(function(){
				jQuery(this).prop("checked",true);
			});
		} else {
			jQuery(".chk-roles").each(function(){
				jQuery(this).prop("checked",false);
			});
		}
	});
	jQuery(".chk-roles").click(function(){
		var all_checkboxed = jQuery(".chk-roles").length;
		if(jQuery(".chk-roles:checked").length == all_checkboxed){
			jQuery("#chk_all").prop("checked",true);
		} else{
			jQuery("#chk_all").prop("checked",false);
		}
	});
	jQuery(document).ready(function(e) {
		<?php 
			if(isset($_REQUEST["msg"])){
				?>
				var admin_page_url = "<?php echo admin_url('admin.php?page=wp_file_manager_shortcode_generator');?><?php if(isset($_REQUEST['id'])) { echo '&id='.$_REQUEST['id']; }?><?php if(isset($_REQUEST['tb'])) { echo '&tb='.$_REQUEST['tb']; }?>";
				window.history.replaceState({}, document.title, admin_page_url);
				<?php
			}
		?>
		jQuery('.shortcode-notice').delay(10000).fadeOut('slow');
		jQuery(".setting_pro_tab a").click(function(event) {
			event.preventDefault();
			jQuery(this).parent().addClass("current");
			jQuery(this).parent().siblings().removeClass("current");
			var tab = jQuery(this).attr("href");
			jQuery(".setting_pro_tab_content").not(tab).css("display", "none");
			jQuery(tab).fadeIn();
		});
		
		jQuery(document).on('click', '.fmcollapse',  function() { 
			jQuery(this).next().slideToggle('fast');
			jQuery(this).toggleClass('non-active');
		});
	});
	function AvoidSpace(event){
		var k = event ? event.which : window.event.keyCode;
    	if (k == 32) return false;
	}
</script>