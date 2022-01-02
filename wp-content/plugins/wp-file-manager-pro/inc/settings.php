<?php if (!defined('ABSPATH')) {
    exit;
}
$this->fm_custom_assets();
?>
<div class="wrap">
<?php if (isset($_GET['msg']) && $_GET['msg'] == '1'):?>
<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated"> 
<p><strong><?php _e('Settings saved.', 'wp-file-manager-pro'); ?></strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text"><?php _e('Dismiss this notice.', 'wp-file-manager-pro'); ?></span></button></div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] == '2'):?>
<div class="error updated settings-error notice is-dismissible" id="setting-error-settings_updated"> 
<p><strong><?php _e('Settings not saved.', 'wp-file-manager-pro'); ?></strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text"><?php _e('Dismiss this notice.', 'wp-file-manager-pro'); ?></span></button></div>
<?php endif; ?>
<div class="setting_pro_wrap fmInnerWrap">

<h3 class="mainHeading">
<span class="headingIcon"><img src="<?php echo plugins_url('images/fm-setting-icon.png', __FILE__); ?>"></span>
<span class="headingText"><?php _e('Settings - File Manager', 'wp-file-manager-pro'); ?></span>
<div class="notice header-notice" style="float:right;font-size: 13px;font-weight: normal;margin:0;"><?php _e('Settings will apply on', 'wp-file-manager-pro'); ?> <code style="font-size: 13px;">[wp_file_manager_admin]</code> <?php _e('shortcode.', 'wp-file-manager-pro'); ?></div>
</h3>

<?php
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
global $wp_roles;
$roles = $wp_roles->get_names();
$allusers = get_users();
$wp_filemanager_options = array();

if (isset($_POST['save_wp_filemanager_settings']) && wp_verify_nonce($_POST['wp_filemanager_nonce_field'], 'wp_filemanager_action')):
    _e('<strong>Saving Please wait...</strong>', 'wp-file-manager-pro');
    $selected_tab = trim($_REQUEST["selected_tab"]);
    $needToUnset = array('save_wp_filemanager_settings','selected_tab'); //no need to save in Database
    foreach ($needToUnset as $noneed):
      unset($_POST[$noneed]);
    endforeach;
    $unsetArray = array('select_user_roles', 'restrict_folders', 'restrict_files', 'userrole_fileoperations', 'select_users', 'restrict_user_folders', 'restrict_user_files', 'users_fileoperations');
    foreach ($unsetArray as $unsetTHIS):
         unset($_POST[$unsetTHIS][0]);
    endforeach;
    foreach ($_POST as $key => $val):
        $wp_filemanager_options[$key] = $val;
    endforeach;

    $saveSettings = update_option('wp_filemanager_options', $wp_filemanager_options);

    if ($saveSettings) {
        mk_file_folder_manager::redirect('?page=wp_file_manager_settings&msg=1&tb='.base64_encode($selected_tab));
    } else {
        mk_file_folder_manager::redirect('?page=wp_file_manager_settings&msg=2&tb='.base64_encode($selected_tab));
    }
endif;
$opt = get_option('wp_filemanager_options');
$themes = mk_file_folder_manager::getFfmThemes();
$list_pages = $this->listofpages(); 
$sel_tab = isset($_GET['tb']) ? base64_decode($_GET['tb']) : 'General';?>
<form action="" method="post" id="ffm_manager">
<?php  wp_nonce_field('wp_filemanager_action', 'wp_filemanager_nonce_field'); ?>
<input type="hidden" name="selected_tab" id="selected_tab" value="<?php echo $sel_tab;?>"/>


<div class="set_tab_dv">
    <ul class="setting_pro_tab">
        <li <?php echo $sel_tab == 'General' ? 'class="current"' : '';?>>
        <a href="#General">
        <span class="icon"><img src="<?php echo plugins_url('images/general_setting_icon.png', dirname(__FILE__)); ?>"/></span>
        <?php _e('General', 'wp-file-manager-pro'); ?></a>
        </li>
        <li <?php echo $sel_tab == 'Code_Editor' ? 'class="current"' : '';?>>
        <a href="#Code_Editor">
         <span class="icon"><img src="<?php echo plugins_url('images/code-editor-icon.png', dirname(__FILE__)); ?>"/></span>
         <?php _e('Code Editor', 'wp-file-manager-pro'); ?></a>
        </li>
        <li <?php echo $sel_tab == 'User_Restriction' ? 'class="current"' : '';?>>
        <a href="#User_Restriction">
         <span class="icon"><img src="<?php echo plugins_url('images/user-icon.png', dirname(__FILE__)); ?>"/></span>
         <?php _e('User Restrictions', 'wp-file-manager-pro'); ?></a>
        </li>
        <li <?php echo $sel_tab == 'User_Role_Restrictions' ? 'class="current"' : '';?>>
        <a href="#User_Role_Restrictions">
         <span class="icon"><img src="<?php echo plugins_url('images/user-role-icon.png', dirname(__FILE__)); ?>"/></span>
         <?php _e('User Role Restrictions', 'wp-file-manager-pro'); ?>
         </a>
        </li>
        <li <?php echo $sel_tab == 'Display' ? 'class="current"' : '';?>>
        <a href="#Display">
         <span class="icon"><img src="<?php echo plugins_url('images/code-editor-icon.png', dirname(__FILE__)); ?>"/></span>
         <?php _e('Frontend Access', 'wp-file-manager-pro'); ?>
         </a>
        </li>
    </ul>

<div class="setting_pro_tab_content" id="General" <?php echo $sel_tab == 'General' ? 'style="display:block;"' : '';?>>
<div class="frm_grp">
<label for="default_category" class="label_heading"><?php _e('Select User Roles to access WP File Manager', 'wp-file-manager-pro'); ?></label>
<div class="chk_list">
<div class="frm_grp_inner">
<?php foreach ($roles as $key => $role):
    if ($key != 'administrator'):?>
    <span class="chk_span_outer"><span class="chk_box_span styledCheckbox"><input type="checkbox" value="<?php echo $key; ?>"  name="fm_user_roles[]"
     <?php 
  if (!empty($opt['fm_user_roles'])):
   if (in_array($key, $opt['fm_user_roles'])):
    echo ' checked="checked"';
   endif;
  endif; ?>     
    /> <span class="fm_checkmark"></span> </span> <!--chk_box_span--> <span class="chk_box_txt"> <?php echo $role; ?></span> </span> <!--chk_span_outer-->
    <?php endif; endforeach; ?><p class="description" style ="margin:0px;"><?php _e('Allow user roles to access WP File Manager.', 'wp-file-manager-pro'); ?></p>
    </div> <!--frm_grp_inner-->
    </div>
</div>  <!--frm_grp-->

<?php /* Start - DB Manager Role Assingnment */ ?>
<div class="frm_grp">
<label for="default_category" class="label_heading"><?php _e('Select User Roles to access DB Manager', 'wp-file-manager-pro'); ?></label>
<div class="chk_list">
<div class="frm_grp_inner">
<?php foreach ($roles as $key => $role):
    if ($key != 'administrator'):?>
    <span class="chk_span_outer"><span class="chk_box_span styledCheckbox"><input type="checkbox" value="<?php echo $key; ?>"  name="fm_user_roles_db[]"
     <?php 
  if (!empty($opt['fm_user_roles_db'])):
   if (in_array($key, $opt['fm_user_roles_db'])):
    echo ' checked="checked"';
   endif;
  endif; ?>     
    /> <span class="fm_checkmark"></span> </span> <!--chk_box_span--> <span class="chk_box_txt"> <?php echo $role; ?></span> </span> <!--chk_span_outer-->
    <?php endif; endforeach; ?><p class="description" style="margin:0;"><?php _e('Allow user roles to access DB Manager.', 'wp-file-manager-pro'); ?></p>
    </div> <!--frm_grp_inner-->
    </div>
</div>  <!--frm_grp-->
<?php /* End - DB Manager Role Assingnment */ ?>

<div class="frm_grp"><label for="default_category"  class="label_heading"><?php _e('Separate or Private Folder Access', 'wp-file-manager-pro'); ?></label>
<div class="frm_grp_inner">
<textarea class="large-text code" rows="3" placeholder="wp-content/plugins" name="private_folder_access"><?php echo !empty($opt['private_folder_access']) ? $opt['private_folder_access'] : ''; ?></textarea><p class="mtop10 mbot10 description"><?php _e('File Manager will access this folder path. Else, will access root directory. <strong>e.g wp-content/plugins</strong>. <br><strong>Note:</strong> Will be valid for all user roles.', 'wp-file-manager-pro'); ?></p>
</div> <!--frm_grp_inner-->
</div> <!--frm_grp-->

<div class="frm_grp">
<label for="default_category" class="label_heading"><?php _e('Maximum Upload Size', 'wp-file-manager-pro'); ?></label>
<div class="frm_grp_inner posRelative">
<input type="number" min="2" value="<?php echo (isset($opt['fm_max_upload_size']) && !empty($opt['fm_max_upload_size'])) ? $opt['fm_max_upload_size'] : '2'; ?>" name="fm_max_upload_size" /> <span class="absPosRt"><?php _e('MB', 'wp-file-manager-pro'); ?> </span><p class="mtop10 mbot10 description"><?php _e('Allow users to upload file of maximum.', 'wp-file-manager-pro'); ?></p>
</div> <!--frm_grp_inner-->
</div> <!--frm_grp-->
<!--<div class="frm_grp">
<label for="default_category" class="label_heading"><?php _e('Notification Email', 'wp-file-manager-pro'); ?></label>
<div class="frm_grp_inner">
<input type="text" value="<?php echo (isset($opt['fm_notification_email']) && !empty($opt['fm_notification_email'])) ? $opt['fm_notification_email'] : get_option('admin_email'); ?>" name="fm_notification_email" /> <p class="mtop10 mbot10"><?php _e('Sends Notifications to this email. <strong>Note:</strong> This will take admin email by default if kept empty.', 'wp-file-manager-pro'); ?></p>
</div>
</div>--> <!--frm_grp-->
<?php /* Added->Upload Email Sent : 31:March:2017*/ ?>
<div class="frm_grp">
<label for="default_category" class="label_heading"> <?php _e('Send Notifications to admin and save logs on file upload ?', 'wp-file-manager-pro'); ?></label>
<div class="frm_grp_inner"><span class="styledCheckbox mrt10"><input type="checkbox" value="yes" name="allow_upload_notifications" 
<?php echo(isset($opt['allow_upload_notifications']) && $opt['allow_upload_notifications'] == 'yes') ? 'checked="checked"' : ''; ?>
/><span class="fm_checkmark"></span></span>
<span class="chk_box_txt"><?php _e('Check to allow file upload notifications. Mail will be sent to admin. Click <a href="admin.php?page=wpfm-emails" target="_blank">here</a> to set notification email.', 'wp-file-manager-pro'); ?></span><div class="fmError"><?php _e('<strong>Note:</strong> This feature is using wp_mail(), Make sure <a href="https://developer.wordpress.org/reference/functions/wp_mail/" title="Read More" target="_blank">wp_mail</a> is working.', 'wp-file-manager-pro'); ?></div>
</div> <!--frm_grp_inner-->
</div> <!--frm_grp-->

<div class="frm_grp">
<label for="allow_download_notifications" class="label_heading"> <?php _e('Send Notifications to admin and save logs on file download ?', 'wp-file-manager-pro'); ?></label>
<div class="frm_grp_inner"><span class="styledCheckbox mrt10"><input type="checkbox" value="yes" name="allow_download_notifications" 
<?php echo(isset($opt['allow_download_notifications']) && $opt['allow_download_notifications'] == 'yes') ? 'checked="checked"' : ''; ?>
/><span class="fm_checkmark"></span></span>
<span class="chk_box_txt"><?php _e('Check to allow file download notifications. Mail will be sent to admin. Click <a href="admin.php?page=wpfm-emails" target="_blank">here</a> to set notification email.', 'wp-file-manager-pro'); ?></span><div class="fmError"><?php _e('<strong>Note:</strong> This feature is using wp_mail(), Make sure <a href="https://developer.wordpress.org/reference/functions/wp_mail/" title="Read More" target="_blank">wp_mail</a> is working.', 'wp-file-manager-pro'); ?></div>
</div> <!--frm_grp_inner-->
</div> <!--frm_grp-->

<div class="frm_grp">
<label for="allow_edit_notifications" class="label_heading"><?php _e('Send Notifications to admin and save logs on file edit ?', 'wp-file-manager-pro'); ?></label>
<div class="frm_grp_inner"><span class="styledCheckbox mrt10"><input type="checkbox" value="yes" name="allow_edit_notifications" 
<?php echo(isset($opt['allow_edit_notifications']) && $opt['allow_edit_notifications'] == 'yes') ? 'checked="checked"' : ''; ?>
/><span class="fm_checkmark"></span></span>
<span class="chk_box_txt"><?php _e('Check to allow file edit notifications. Mail will be sent to admin.  Click <a href="admin.php?page=wpfm-emails" target="_blank">here</a> to set notification email.', 'wp-file-manager-pro'); ?></span><div class="fmError"><?php _e('<strong>Note:</strong> This feature is using wp_mail(), Make sure <a href="https://developer.wordpress.org/reference/functions/wp_mail/" title="Read More" target="_blank">wp_mail</a> is working.', 'wp-file-manager-pro'); ?></div>
</div> <!--frm_grp_inner-->
</div> <!--frm_grp-->
<?php /* End email sent */ ?>

<div class="frm_grp">
<label for="default_category" class="label_heading"><?php _e('Select File Manager Language', 'wp-file-manager-pro'); ?></label>
<div class="frm_grp_inner">
<select name="lang">
<?php foreach ($this->fm_languages() as $name => $lang) {
      ?>
<option value="<?php echo $lang; ?>" <?php echo (isset($opt['lang']) && $opt['lang'] == $lang) ? 'selected="selected"' : ''; ?>><?php echo $name; ?> (<?php echo $lang; ?>)</option>
<?php
  } ?>
</select>

<p class="mtop10 mbot0"> <strong><?php _e('Default:', 'wp-file-manager-pro'); ?> </strong> <?php _e('English', 'wp-file-manager-pro'); ?> </p>
 <p class="mtop10 mbot10 description"><?php _e('You can select any language for File Manager.', 'wp-file-manager-pro'); ?></p>
</div> <!--frm_grp_inner-->
</div> <!--frm_grp-->


<div class="frm_grp">
<label for="default_category" class="label_heading"><?php _e('Select File Manager Theme', 'wp-file-manager-pro'); ?></label>
<div class="frm_grp_inner">
<select name="theme" id="fm_theme">
<option value="default" <?php echo (isset($opt['theme']) && $opt['theme'] == 'default') ? 'selected="selected"' : ''; ?>><?php  _e('Default', 'wp-file-manager-pro'); ?></option>
<?php foreach ($this->get_themes() as $theme) {
      ?>
<option value="<?php echo $theme; ?>" <?php echo (isset($opt['theme']) && $opt['theme'] == $theme) ? 'selected="selected"' : ''; ?>><?php echo ucfirst($theme); ?></option>
<?php
  } ?>
</select>

 <p class="mtop10 mbot0"><strong><?php _e('Default:', 'wp-file-manager-pro'); ?> </strong> <?php _e('Default', 'wp-file-manager-pro'); ?></p>
<p class="mtop10 mbot10 description"><?php _e('You can select any theme for File Manager.', 'wp-file-manager-pro'); ?></p>
</div> <!--frm_grp_inner-->
</div> <!--frm_grp-->

<?php /* File Manager Default View */ ?>
<div class="frm_grp">
<label for="default_category" class="label_heading"><?php _e('Files View','wp-file-manager-pro'); ?></label>
<div class="frm_grp_inner">
<select name="wp_fm_view" id="wp_fm_view">
<option value="list" <?php echo (isset($opt['wp_fm_view']) && $opt['wp_fm_view'] == 'list') ? 'selected="selected"' : ''; ?>><?php  _e('List', 'wp-file-manager-pro'); ?></option>
<option value="grid" <?php echo (isset($opt['wp_fm_view']) && $opt['wp_fm_view'] == 'grid') ? 'selected="selected"' : ''; ?>><?php  _e('Icon', 'wp-file-manager-pro'); ?></option>
</select>
<p class="mtop10 mbot0"><strong><?php _e('Default:','wp-file-manager-pro'); ?> </strong> <?php _e('List','wp-file-manager-pro'); ?></p>
<p class="mtop10 mbot10 description"><?php _e('You can select any view for File Manager.','wp-file-manager-pro'); ?></p>
</div> <!--frm_grp_inner-->
</div> <!--frm_grp-->
<div class="frm_grp">
<label for="allow_edit_notifications" class="label_heading"><?php _e('Enable "Insert File Manager Shortcode" Button with Content Editor ?', 'wp-file-manager-pro'); ?></label>
<div class="frm_grp_inner"><span class="styledCheckbox mrt10"><input type="checkbox" value="yes" name="allow_shortcode_btn_editor"
<?php echo(isset($opt['allow_shortcode_btn_editor']) && $opt['allow_shortcode_btn_editor'] == 'yes') ? 'checked="checked"' : ''; ?>
/><span class="fm_checkmark"></span></span>
<span class="chk_box_txt"><?php _e('Enable "Insert File Manager Shortcode" button in edit page screen with classic editor.', 'wp-file-manager-pro'); ?></span></p>
</div> <!--frm_grp_inner-->
</div> <!--frm_grp-->
<div class="frm_grp last_frm_grp padbot30">
<label for="diable_local_file_system_fm" class="label_heading"> <?php _e('Disable Local File System ?', 'wp-file-manager-pro'); ?></label>
<div class="frm_grp_inner"><span class="styledCheckbox mrt10"><input type="checkbox" value="yes" name="diable_local_file_system_fm"
<?php echo(isset($opt['diable_local_file_system_fm']) && $opt['diable_local_file_system_fm'] == 'yes') ? 'checked="checked"' : ''; ?>
/><span class="fm_checkmark"></span></span>
<span class="chk_box_txt"><?php _e('Check to disable Local File System in File Manager.', 'wp-file-manager-pro'); ?></span></p>
</div> <!--frm_grp_inner-->
</div> <!--frm_grp-->

<div class="frm_grp last_frm_grp padbot30">
<label for="diable_local_db_fm" class="label_heading"> <?php _e('Disable Database Manager ?', 'wp-file-manager-pro'); ?></label>
<div class="frm_grp_inner"><span class="styledCheckbox mrt10"><input type="checkbox" value="yes" name="diable_local_db_fm"
<?php echo(isset($opt['diable_local_db_fm']) && $opt['diable_local_db_fm'] == 'yes') ? 'checked="checked"' : ''; ?>
/><span class="fm_checkmark"></span></span>
<span class="chk_box_txt"><?php _e('Check to disable Database Manager.', 'wp-file-manager-pro'); ?></span></p>
</div> <!--frm_grp_inner-->
</div> <!--frm_grp-->

<div class="frm_grp last_frm_grp padbot30">
<label for="diable_welcome_msg_fm" class="label_heading"> <?php _e('Disable Welcome Message?', 'wp-file-manager-pro'); ?></label>
<div class="frm_grp_inner"><span class="styledCheckbox mrt10"><input type="checkbox" value="yes" name="diable_welcome_msg_fm"
<?php echo(isset($opt['diable_welcome_msg_fm']) && $opt['diable_welcome_msg_fm'] == 'yes') ? 'checked="checked"' : ''; ?>
/><span class="fm_checkmark"></span></span>
<span class="chk_box_txt"><?php _e('Check to disable welcome message on frontend shortcodes.', 'wp-file-manager-pro'); ?></span></p>
</div> <!--frm_grp_inner-->
</div> <!--frm_grp-->

<div class="frm_grp">
<label for="fm_welcome_mesg" class="label_heading"><?php _e('Welcome Message', 'wp-file-manager-pro'); ?></label>
<div class="frm_grp_inner posRelative">
<textarea rows="3" class="large-text code" name="fm_welcome_mesg" placeholder="<?php _e('Welcome:', 'wp-file-manager-pro'); ?> %Username%"><?php echo (isset($opt['fm_welcome_mesg']) && !empty($opt['fm_welcome_mesg'])) ? $opt['fm_welcome_mesg'] : ''; ?></textarea><p class="mtop10 mbot10 description"><?php _e('Welcome message will appear on frontend shortcode.', 'wp-file-manager-pro'); ?> <code>%Username%</code> <?php _e('will show loggedIn user name on frontend.', 'wp-file-manager-pro'); ?></p>
</div> <!--frm_grp_inner-->
</div> <!--frm_grp-->

</div> <!--General-->

<div class="setting_pro_tab_content" id="Code_Editor" <?php echo $sel_tab == 'Code_Editor' ? 'style="display:block;"' : '';?>>

<div class="frm_grp">
<label for="default_category" class="label_heading"><?php _e('Code editor allow fullscreen?', 'wp-file-manager-pro'); ?></label>
<div class="frm_grp_inner"><span class="styledCheckbox mrt10"><input type="checkbox" value="yes" name="allow_fullscreen" <?php echo (isset($opt['allow_fullscreen']) && ($opt['allow_fullscreen'] == 'yes')) ? 'checked="checked"' : ''; ?>/><span class="fm_checkmark"></span></span> <span class="chk_box_txt"><?php _e('Check to allow fullscreen code editor', 'wp-file-manager-pro'); ?></span></div>
</div> <!--frm_grp-->

<div class="frm_grp padbot30 last_frm_grp">
<label for="default_category" class="label_heading"><?php _e('Select theme for Code Editor.', 'wp-file-manager-pro'); ?></label>
<div class="frm_grp_inner">
<select id="code_editor_theme" name="code_editor_theme">
<option value="default"><?php _e('Default', 'wp-file-manager-pro'); ?></option>
<?php if (!empty($themes) && is_array($themes)):
foreach ($themes as $key => $theme):?>
<option value="<?php echo $key; ?>" <?php echo (isset($opt['code_editor_theme']) && $opt['code_editor_theme'] == $key) ? 'selected = "selected"' : ''; ?>><?php echo ucwords(str_replace('-', ' ', $theme)); ?></option>
<?php endforeach; endif; ?>
</select> <p class="description"><?php _e('Select theme for Code Editor. For more information please visit <a href="http://codemirror.net/demo/theme.html" target="_blank">this link</a>.', 'wp-file-manager-pro'); ?></p>
</div> <!--frm_grp_inner-->
</div> <!--frm_grp-->

</div> <!--Code_Editor-->

<div class="setting_pro_tab_content" id="User_Role_Restrictions" <?php echo $sel_tab == 'User_Role_Restrictions' ? 'style="display:block;"' : '';?>>

<div class="frm_grp">
<label for="default_category" class="label_heading"><?php _e('Disable or Ban WP File Manager Operations to User Roles', 'wp-file-manager-pro'); ?></label>
<div class="frm_grp_inner"><p class="mtop10 mbot10 description"><?php _e('Select user role and disable operation.', 'wp-file-manager-pro'); ?></p></div>
</div> <!--frm_grp-->


<div class="click_rule_dv">
<span class="txt_title"><label for="default_category"> <?php _e('Click to Add Rules', 'wp-file-manager-pro'); ?></label></span>
<button id="add_rule_for_userrole" name="add_rule_for_userrole" class="button button-primary pro_btn"><?php _e('Add rule for User Roles', 'wp-file-manager-pro'); ?></button>
</div>

<div class="form-table item_cliche hide">
<div class="group_box_wrap control-tr">

<div class="group_box_left">
<div class="user_roles">
<span class="lbl_txt"><?php _e('If User role is', 'wp-file-manager-pro'); ?></span>
<select name="select_user_roles[]" class="user-selevct">
    <option value=""><?php _e('Select User Role', 'wp-file-manager-pro'); ?></option>
	<?php foreach ($roles as $key => $role):?>
    <option value="<?php echo $key; ?>"><?php echo $role; ?></option>
    <?php endforeach; ?>
	</select> 
    <span class="lbl_txt_rt"><?php _e('Then', 'wp-file-manager-pro'); ?> </span>
    </div> <!--user_roles-->
    </div>

<a href="javascript:void(0)" class="fmcollapse"></a>
<div class="fm_collapseInner">
<div class="group_box_left">
    
    <div class="frm_grp padbot30">
    <label for="default_post_format" class="label_heading"><?php _e('Disable Operations', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">
    <fieldset>
<?php foreach ($file_operations as $operation_name => $file_operation):?><label class="operationCheck" for="users_can_register">
<span class="chkBox styledCheckbox"> <input type="checkbox" value="<?php echo $file_operation; ?>" class="tmpchkname"/><span class="fm_checkmark"></span></span> 
<span class="chkTxt"><?php echo $operation_name; ?> </span>
</label>
<?php endforeach; ?>
</fieldset>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp-->
    
    <div class="frm_grp padbot30">
    <label for="default_post_format" class="label_heading"><?php _e('Separate or private folder access <strong>e.g wp-content/themes</strong> etc.', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">
    <textarea rows="3" class="large-text code" name="seprate_folder[]" placeholder="wp-content/plugins"></textarea>
    <p class="mtop10 mbot10 description"><strong><?php _e('Note:', 'wp-file-manager-pro');?></strong> <?php _e('It will overide "Private Folder Access" settings', 'wp-file-manager-pro');?>.</p>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp-->
    
<div class="frm_grp padbot30">
  <label for="default_post_format" class="label_heading"><?php _e('Enter Folder or Files Paths That You want to <strong>Hide</strong> e.g wp-content/themes|wp-content/plugins.', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">
<textarea rows="3" class="large-text code" name="restrict_folders[]" placeholder="wp-content/themes|wp-content/plugins"></textarea>
<p class="mtop10 mbot10 description"><strong><?php _e('Note:', 'wp-file-manager-pro');?></strong> <?php _e('Multiple separated by Vertical Bar (|).', 'wp-file-manager-pro');?></p>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp-->
    
<div class="frm_grp padbot30">
 <label for="default_post_format" class="label_heading"><?php _e('Enter file extensions which you want to Lock. <strong>e.g .php|.png|.css</strong> etc', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">
<textarea rows="3" class="large-text code" name="restrict_files[]" placeholder=".php|.png|.css"></textarea>
<p class="mtop10 mbot10 description"><strong><?php _e('Note:', 'wp-file-manager-pro');?></strong> <?php _e('The extensions in the option are case sensitive and should be separated by vertical bar (|) without having white space between them. e.g. php,PHP', 'wp-file-manager-pro');?></p>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp-->
    
  <div class="frm_grp last_frm_grp padbot30">
    <label for="default_post_format" class="label_heading"><?php _e('Disable Toolbar and Context Menu', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">
    <fieldset>
    <span class="chkBox styledCheckbox mrt10">
      <input type="checkbox" value="yes" class="dtchkname"/> 
      <span class="fm_checkmark"></span></span> 
<span class="chkTxt mrt10"><?php _e('Disable Toolbar', 'wp-file-manager-pro'); ?></span>

       <span class="chkBox styledCheckbox mrt10">
      <input type="checkbox" value="yes" class="cmchkname"/>
	  <span class="fm_checkmark"></span></span> 
<span class="chkTxt"><?php _e('Disable Context Menu', 'wp-file-manager-pro'); ?></span>
	  </label>
</fieldset>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp-->   
    
    
     </div> <!--group_box_left-->
     <div class="group_box_right"><div class="group_box_right_tbl"><div class="group_box_right_cel"><input type="button" class="button delete_item del_btn_pro" value="<?php _e('Delete', 'wp-file-manager-pro'); ?>" title="<?php _e('Remove', 'wp-file-manager-pro'); ?>" /></div></div></div> <!--group_box_right-->
</div> <!--group_box_wrap-->
</div>
</div>
<div class="big_social_cont">
<?php $totalRoles = isset($opt['select_user_roles']) ? count($opt['select_user_roles']) : 0;
for ($i = 1; $i <= $totalRoles; ++$i) {
    ?>
<div class="group_box_wrap control-tr">
<div class="group_box_left">
<div class="user_roles">
<span class="lbl_txt"><?php _e('If User role is', 'wp-file-manager-pro'); ?></span>
<select name="select_user_roles[]" class="user-selevct role-restriction">
    <option value=""><?php _e('Select User Role', 'wp-file-manager-pro'); ?></option>
	<?php foreach ($roles as $key => $role):?>
    <option value="<?php echo $key; ?>"
     <?php if ($key == $opt['select_user_roles'][$i]): echo 'selected = "selected"';
    endif; ?>
     ><?php echo $role; ?></option>
    <?php endforeach; ?>
	</select> 
    <span class="lbl_txt_rt"><?php _e('Then', 'wp-file-manager-pro'); ?> </span>
</div> <!--user_roles-->
</div>

<a href="javascript:void(0)" class="fmcollapse"></a>
<div class="fm_collapseInner">
<div class="group_box_left">

    <div class="frm_grp">
  <label for="default_post_format" class="label_heading"><?php _e('Disable Operations', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">
<fieldset>
<?php

 foreach ($file_operations as $operation_name => $file_operation):?><label for="users_can_register" class="operationCheck"><span class="chkBox styledCheckbox"> 
 <input type="checkbox" value="<?php echo $file_operation; ?>" name="userrole_fileoperations_<?php echo $i; ?>[]"
 <?php 
  if (!empty($opt['userrole_fileoperations_'.$i])):
   if (in_array($file_operation, $opt['userrole_fileoperations_'.$i])):
   echo ' checked="checked"';
    endif;
    endif; ?> 
 class="tmpchkname" /> <span class="fm_checkmark"></span></span> <span class="chkTxt"> <?php echo $operation_name; ?> </span></label>
<?php endforeach; ?>
</fieldset>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp-->

    <div class="frm_grp">
   <label class="label_heading" for="default_post_format"><?php _e('Separate or private folder access <strong>e.g wp-content/themes</strong> etc.', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">
<textarea rows="3" class="large-text code" name="seprate_folder[]" placeholder="wp-content/plugins"><?php echo $opt['seprate_folder'][$i]; ?></textarea>
<p class="mtop10 mbot10 description"><strong><?php _e('Note:', 'wp-file-manager-pro');?></strong> <?php _e('It will overide "Private Folder Access" settings', 'wp-file-manager-pro');?>.</p>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp-->
    
        <div class="frm_grp">
<label for="default_post_format" class="label_heading"><?php _e('Enter Folder or Files Paths That You want to <strong>Hide</strong> e.g wp-content/themes|wp-content/plugins.', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">
<textarea rows="3" class="large-text code" name="restrict_folders[]" placeholder="wp-content/themes|wp-content/plugins"><?php echo $opt['restrict_folders'][$i]; ?></textarea>
<p class="mtop10 mbot10 description"><strong><?php _e('Note:', 'wp-file-manager-pro');?></strong> <?php _e('Multiple separated by Vertical Bar (|).', 'wp-file-manager-pro');?></p>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp-->
    
            <div class="frm_grp">
<label for="default_post_format" class="label_heading"><?php _e('Enter file extensions which you want to Lock. <strong>e.g .php|.png|.css</strong> etc', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">
<textarea rows="3" class="large-text code" name="restrict_files[]" placeholder=".php|.png|.css"><?php echo $opt['restrict_files'][$i]; ?></textarea>
<p class="mtop10 mbot10 description"><strong><?php _e('Note:', 'wp-file-manager-pro');?></strong> <?php _e('The extensions in the option are case sensitive and should be separated by vertical bar (|) without having white space between them. e.g. php,PHP', 'wp-file-manager-pro');?></p>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp-->
    
    <div class="frm_grp padbot30 last_frm_grp">
    <label for="default_post_format" class="label_heading"><?php _e('Disable Toolbar and Context Menu', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">   
    <fieldset>
        <span class="chkBox styledCheckbox mrt10">
            <input type="checkbox" value="yes" class="dtchkname" name="user_role_disable_toolbar_<?php echo $i; ?>" <?php echo (isset($opt['user_role_disable_toolbar_'.$i]) && ($opt['user_role_disable_toolbar_'.$i] == 'yes')) ? 'checked="checked"' : ''; ?>/><span class="fm_checkmark"></span>
        </span><span class="chkTxt mrt10"><?php _e('Disable Toolbar', 'wp-file-manager-pro'); ?>  </span> <span class="chkBox styledCheckbox mrt10"><input type="checkbox" value="yes" class="cmchkname" name="user_role_disable_context_<?php echo $i; ?>" <?php echo (isset($opt['user_role_disable_context_'.$i]) && ($opt['user_role_disable_context_'.$i] == 'yes')) ? 'checked="checked"' : ''; ?>/><span class="fm_checkmark"></span></span> <span class="chkTxt"><?php _e('Disable Context Menu', 'wp-file-manager-pro'); ?></span></label>
</fieldset>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp-->  
    
    
    
</div> <!--group_box_left-->

<div class="group_box_right"><div class="group_box_right_tbl"><div class="group_box_right_cel"><input type="button" class="button delete_item del_btn_pro" value="<?php _e('Delete', 'wp-file-manager-pro'); ?>" title="<?php _e('Remove', 'wp-file-manager-pro'); ?>" /> </div></div></div> <!--group_box_right-->
</div>     
</div> <!--group_box_wrap-->

<?php
} ?>
</div>
</div> <!--User_Restriction-->

<div class="setting_pro_tab_content" id="User_Restriction" <?php echo $sel_tab == 'User_Restriction' ? 'style="display:block;"' : '';?>>

<div class="frm_grp">
<label for="default_category" class="label_heading"><?php _e('Disable or Ban WP File Manager Operations to Users', 'wp-file-manager-pro'); ?></label>
<div class="frm_grp_inner"><p class="mtop10 mbot10 description"><?php _e('Select users and disable operation.', 'wp-file-manager-pro'); ?></p></div>
</div> <!--frm_grp-->


<div class="click_rule_dv">
<span class="txt_title"><label for="default_category"><?php _e('Click to Add Rules', 'wp-file-manager-pro'); ?></label></span>
<button id="add_rule_for_user" name="add_rule_for_user" class="button button-primary pro_btn"><?php _e('Add rule for Users', 'wp-file-manager-pro'); ?></button>
</div> <!--click_rule_dv-->

<div class="form-table item_cliche_user hide">

<div class="group_box_wrap control-tr-user">

<div class="group_box_left">
<div class="user_roles">
<span class="lbl_txt"><?php _e('If User is', 'wp-file-manager-pro'); ?></span>
<select name="select_users[]" class="user-selevct">
    <option value=""><?php _e('Select User', 'wp-file-manager-pro'); ?></option>
	<?php foreach ($allusers as $user):?>
    <option value="<?php echo $user->user_login; ?>"><?php echo $user->user_login; ?></option>
    <?php endforeach; ?>
	</select>
    <span class="lbl_txt_rt"><?php _e('Then', 'wp-file-manager-pro'); ?> </span>
</div> <!--user_roles-->
</div>

<a href="javascript:void(0)" class="fmcollapse"></a>
<div class="fm_collapseInner">
<div class="group_box_left">

    <div class="frm_grp padbot30">
    <label for="default_post_format" class="label_heading"><?php _e('Disable Operations', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">
<fieldset>
<?php foreach ($file_operations as $operation_name => $file_operation):?><label class="operationCheck" for="users_can_register">
 <span class="chkBox styledCheckbox"> <input type="checkbox" value="<?php echo $file_operation; ?>" class="tmpchkuname"/> <span class="fm_checkmark"></span></span>  <span class="chkTxt"> <?php echo $operation_name; ?> </span> </label>
<?php endforeach; ?>
</fieldset>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp-->

  <div class="frm_grp padbot30">
  <label class="label_heading" for="default_post_format"><?php _e('Separate or private folder access <strong>e.g wp-content/themes</strong> etc.', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">
 <textarea rows="3" class="large-text code" name="user_seprate_folder[]" placeholder="wp-content/plugins"></textarea>
 <p class="mtop10 mbot10 description"><strong><?php _e('Note:', 'wp-file-manager-pro');?></strong> <?php _e('It will overide "Private Folder Access" settings', 'wp-file-manager-pro');?>.</p>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp-->
 
   <div class="frm_grp padbot30">
 <label for="default_post_format" class="label_heading"><?php _e('Enter Folder or Files Paths That You want to <strong>Hide</strong> e.g wp-content/themes|wp-content/plugins.', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">
<textarea rows="3" class="large-text code" name="restrict_user_folders[]" placeholder="wp-content/themes|wp-content/plugins"></textarea>
<p class="mtop10 mbot10 description"><strong><?php _e('Note:', 'wp-file-manager-pro');?></strong> <?php _e('Multiple separated by Vertical Bar (|).', 'wp-file-manager-pro');?></p>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp-->
  
     <div class="frm_grp padbot30">
<label for="default_post_format" class="label_heading"><?php _e('Enter file extensions which you want to Lock. <strong>e.g .php|.png|.css</strong> etc', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">
<textarea rows="3" class="large-text code" name="restrict_user_files[]" placeholder=".php|.png|.css"></textarea>
<p class="mtop10 mbot10 description"><strong><?php _e('Note:', 'wp-file-manager-pro');?></strong> <?php _e('The extensions in the option are case sensitive and should be separated by vertical bar (|) without having white space between them. e.g. php,PHP', 'wp-file-manager-pro');?></p>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp-->
    
     <div class="frm_grp last_frm_grp padbot30">
    <label for="default_post_format" class="label_heading"><?php _e('Disable Toolbar and Context Menu', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">
    <fieldset>
    <span class="chkBox styledCheckbox mrt10">
      <input type="checkbox" value="yes" class="dtchkuname"/>
      <span class="fm_checkmark"></span></span><span class="chkTxt mrt10"><?php _e('Disable Toolbar', 'wp-file-manager-pro'); ?>  </span>
     <span class="chkBox styledCheckbox mrt10"> <input type="checkbox" value="yes" class="cmchkuname"/><span class="fm_checkmark"></span></span> <span class="chkTxt"><?php _e('Disable Context Menu', 'wp-file-manager-pro'); ?></span></label>
</fieldset>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp--> 
    
    
         
</div> <!--group_box_left-->

 <div class="group_box_right"><div class="group_box_right_tbl"><div class="group_box_right_cel"> <input type="button" class="button delete_item_user del_btn_pro" value="<?php _e('Delete', 'wp-file-manager-pro'); ?>" title="<?php _e('Remove', 'wp-file-manager-pro'); ?>" /> </div></div></div> <!--group_box_right-->
 </div>
</div> <!--group_box_wrap-->
</div>

<?php /* OUT PUT USER */?>
<div class="big_social_cont_user">
<?php $totalRoles = isset($opt['select_users']) ? count($opt['select_users']) : 0;
for ($i = 1; $i <= $totalRoles; ++$i) {
    ?>
<div class="group_box_wrap control-tr-user">

<div class="group_box_left">
<div class="user_roles">
<span class="lbl_txt"><?php _e('If User is', 'wp-file-manager-pro'); ?></span>
<select name="select_users[]" class="user-selevct restriction">
    <option value=""><?php _e('Select User', 'wp-file-manager-pro'); ?></option>
	<?php foreach ($allusers as $user):?>
    <option value="<?php echo $user->user_login; ?>" <?php if ($user->user_login == $opt['select_users'][$i]): echo 'selected = "selected"';
    endif; ?>><?php echo $user->user_login; ?></option>
    <?php endforeach; ?>
	</select> 
    <span class="lbl_txt_rt"><?php _e('Then', 'wp-file-manager-pro'); ?> </span>
</div> <!--user_roles-->
</div>

<a href="javascript:void(0)" class="fmcollapse"></a>
<div class="fm_collapseInner">
<div class="group_box_left">


  <div class="frm_grp">
    <label for="default_post_format" class="label_heading"><?php _e('Disable Operations', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">
     <fieldset>
<?php
 foreach ($file_operations as $operation_name => $file_operation):?>
 <label class="operationCheck" for="users_can_register">
    <span class="chkBox styledCheckbox">
        <input type="checkbox" value="<?php echo $file_operation; ?>" name="users_fileoperations_<?php echo $i; ?>[]"
    <?php 
    if (!empty($opt['users_fileoperations_'.$i])):
    if (in_array($file_operation, $opt['users_fileoperations_'.$i])):
    echo ' checked="checked"';
        endif;
        endif; ?> 
    class="tmpchkuname"/> <span class="fm_checkmark"></span></span>  <span class="chkTxt"> <?php echo $operation_name; ?> </span></label>
    <?php endforeach; ?>
</fieldset>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp-->
    
<div class="frm_grp">
<label class="label_heading" for="default_post_format"><?php _e('Separate or private folder access <strong>e.g wp-content/themes</strong> etc.', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">
<textarea rows="3" class="large-text code" name="user_seprate_folder[]" placeholder="wp-content/plugins"><?php echo $opt['user_seprate_folder'][$i]; ?></textarea>
<p class="mtop10 mbot10 description"><strong><?php _e('Note:', 'wp-file-manager-pro');?></strong> <?php _e('It will overide "Private Folder Access" settings', 'wp-file-manager-pro');?>.</p>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp-->
 
 <div class="frm_grp">
<label class="label_heading" for="default_post_format"><?php _e('Enter Folder or Files Paths That You want to <strong>Hide</strong> e.g wp-content/themes|wp-content/plugins.', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">
<textarea rows="3" class="large-text code" name="restrict_user_folders[]" placeholder="wp-content/themes|wp-content/plugins"><?php echo $opt['restrict_user_folders'][$i]; ?></textarea>
<p class="mtop10 mbot10 description"><strong><?php _e('Note:', 'wp-file-manager-pro');?></strong> <?php _e('Multiple separated by Vertical Bar (|).', 'wp-file-manager-pro');?></p>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp-->
 
  <div class="frm_grp">
<label class="label_heading" for="default_post_format"><?php _e('Enter file extensions which you want to Lock. <strong>e.g .php|.png|.css</strong> etc', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">
<textarea rows="3" class="large-text code" name="restrict_user_files[]" placeholder=".php|.png|.css"><?php echo $opt['restrict_user_files'][$i]; ?></textarea>
<p class="mtop10 mbot10 description"><strong><?php _e('Note:', 'wp-file-manager-pro');?></strong> <?php _e('The extensions in the option are case sensitive and should be separated by vertical bar (|) without having white space between them. e.g. php,PHP', 'wp-file-manager-pro');?></p>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp-->
    
  <div class="frm_grp padbot30 last_frm_grp">
    <label for="default_post_format" class="label_heading"><?php _e('Disable Toolbar and Context Menu', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">
    <fieldset>
    <span class="chkBox styledCheckbox mrt10">
      <input type="checkbox" value="yes" class="dtchkuname" name="user_disable_toolbar_<?php echo $i; ?>" <?php echo (isset($opt['user_disable_toolbar_'.$i]) && ($opt['user_disable_toolbar_'.$i] == 'yes')) ? 'checked="checked"' : ''; ?>/><span class="fm_checkmark"></span></span><span class="chkTxt mrt10"><?php _e('Disable Toolbar', 'wp-file-manager-pro'); ?>  </span> <span class="chkBox styledCheckbox mrt10"><input type="checkbox" value="yes" class="cmchkuname" name="user_disable_context_<?php echo $i; ?>" <?php echo (isset($opt['user_disable_context_'.$i]) && ($opt['user_disable_context_'.$i] == 'yes')) ? 'checked="checked"' : ''; ?>/><span class="fm_checkmark"></span></span><span class="chkTxt mrt10"><?php _e('Disable Context Menu', 'wp-file-manager-pro'); ?></span></label>
</fieldset>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp-->   
    
</div> <!--group_box_left-->
 <div class="group_box_right"><div class="group_box_right_tbl"><div class="group_box_right_cel"> <input type="button" class="button delete_item_user del_btn_pro" value="<?php _e('Delete', 'wp-file-manager-pro'); ?>" title="<?php _e('Remove', 'wp-file-manager-pro'); ?>" /></div></div></div> <!--group_box_right-->
</div> 
</div> <!--group_box_wrap-->
<?php
} ?>
</div>
</div> <!--User_Role_Restrictions-->

<div class="setting_pro_tab_content" id="Display" <?php echo $sel_tab == 'Display' ? 'style="display:block;"' : '';?>>
    <?php $mk_pages_list = isset($opt['mk_pages_list']) ? $opt['mk_pages_list'] : array(); ?>
    <label for="default_category" class="label_heading"><?php _e('Select the pages where you want to show the File Manager on Frontend', 'wp-file-manager-pro'); ?></label><br />
    <div class="frm_grp">
    <label for="default_category" class="label_heading"><?php _e('Select Pages', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">
    <select name="mk_pages_list" id="mk_pages_list" multiple="multiple" size="15">
        <?php if (!empty($list_pages) && is_array($list_pages)) {
            foreach ($list_pages as $list_page) { ?>
                        <option value="<?php echo $list_page->ID; ?>" <?php echo in_array($list_page->ID, $mk_pages_list) ? 'selected="selected"' : ''; ?>><?php echo $list_page->post_title; ?> (<?php echo $list_page->post_type; ?> - ID:<?php echo $list_page->ID; ?>)</option>
                    <?php
            }
      } ?>   
    </select>
    <p class="description"><?php _e('<strong>Note:</strong> It will not work if already using any File Manager shortcode in selected page editor.', 'wp-file-manager-pro'); ?></p>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp-->
    <div class="frm_grp">
    <label for="default_category" class="label_heading"><?php _e('Display File Manager ?', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">
    <select name="display_fm_on_pages">
    <option value="after_content" <?php echo (isset($opt['display_fm_on_pages']) && $opt['display_fm_on_pages'] == 'after_content') ? 'selected="selected"' : ''; ?>><?php _e('After Content', 'wp-file-manager-pro'); ?></option>
    <option value="before_content" <?php echo (isset($opt['display_fm_on_pages']) && $opt['display_fm_on_pages'] == 'before_content') ? 'selected="selected"' : ''; ?>><?php _e('Before Content', 'wp-file-manager-pro'); ?></option>
    </select>

    <strong class="def_af_con"><?php _e('Default: After Content', 'wp-file-manager-pro'); ?> </strong>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp-->
    <div class="frm_grp">
    <label for="default_category" class="label_heading"><?php _e('Shortcode', 'wp-file-manager-pro'); ?></label>
    <div class="frm_grp_inner">
    <textarea name="without_login_shortcode" class="large-text code" rows="3" placeholder="<?php _e('Paste the shortcode here', 'wp-file-manager-pro'); ?>"><?php echo (isset($opt['without_login_shortcode']) && !empty($opt['without_login_shortcode'])) ? stripslashes($opt['without_login_shortcode']) : ''; ?></textarea>
    <p class="description"><?php _e("You can generate new shortcode from the <a href='admin.php?page=wp_file_manager_existing_shortcodes'>Shortcode Generator</a> of File Manager.", 'wp-file-manager-pro'); ?></p>
    <p class="description"><?php _e('Shortcode will work on selected pages. Help is given below to edit parameters:', 'wp-file-manager-pro'); ?></p>
    <strong><?php _e('Shortcode Help:', 'wp-file-manager-pro'); ?> </strong><p class="description"><?php _e("<a href='https://filemanagerpro.io/shortcodes/' target='_blank'>Documentation</a>", 'wp-file-manager-pro'); ?></p>
    </div> <!--frm_grp_inner-->
    </div> <!--frm_grp-->

</div>

</div> <!--set_tab_dv-->
<div class="btnDv"><input type="submit" value="<?php _e('Save Changes', 'wp-file-manager-pro'); ?>" class="button button-primary fmCustomBtn" id="submit" name="save_wp_filemanager_settings"></div></form>

</div> <!--setting_pro_wrap-->

</div>
<script>
jQuery(document).ready(function(e){
<?php $opt = get_option('wp_file_manager_pro');
if (empty($opt['ispro']) && empty($opt['serialkey'])) {
    ?>
setInterval(function(){ alert("Invalid Licence Key"); }, 5000);
<?php
} ?>
	/* User Roles */
	jQuery('#add_rule_for_userrole').click(function(e){
		e.preventDefault();
        var role_selected = 1;
        var totalroleDivs = jQuery('.control-tr').length;
		var totalroles = '<?php echo count($roles); ?>';
        jQuery('.big_social_cont .control-tr').each(function(){
            jQuery(this).find('.user-selevct').removeClass("error");
            if(jQuery(this).find('.user-selevct').val() == ""){
                role_selected = 0;
                jQuery(this).find('.user-selevct').addClass("error").focus();
            }
        });
		if(role_selected == 0)
		{
		    alert('<?php _e("Please select user role from previously added rule before adding new!", "wp-file-manager-pro"); ?>');
		}
		else
		{
            jQuery('.big_social_cont').append( jQuery('.item_cliche').html() );
            jQuery('.control-tr:eq('+totalroleDivs+') .tmpchkname').attr('name','userrole_fileoperations_'+totalroleDivs+'[]');
            jQuery('.control-tr:eq('+totalroleDivs+') .dtchkname').attr('name','user_role_disable_toolbar_'+totalroleDivs);
            jQuery('.control-tr:eq('+totalroleDivs+') .cmchkname').attr('name','user_role_disable_context_'+totalroleDivs);
            jQuery('.big_social_cont .group_box_wrap:last-child').find("select.user-selevct").addClass("role-restriction");
            jQuery('html, body').animate({
                scrollTop: jQuery(".big_social_cont .control-tr:last-child").offset().top
            }, 1000);
        }
	});
    jQuery(document).on('click', '.delete_item',  function() {
		var point = jQuery(this).parents('.control-tr');
		point.fadeOut(500, function(){
			 jQuery(this).replaceWith('');
			var totalroleDivs = jQuery('.control-tr').length;
		for(i=1; i<=totalroleDivs-1; i++){
			jQuery('.control-tr:eq('+i+') .tmpchkname').attr('name','userrole_fileoperations_'+i+'[]');
			jQuery('.control-tr:eq('+i+') .dtchkname').attr('name','user_role_disable_toolbar_'+i);
			jQuery('.control-tr:eq('+i+') .cmchkname').attr('name','user_role_disable_context_'+i);
		}	
		 });		
	});	
	/* Users */
	jQuery('#add_rule_for_user').click(function(e){
		e.preventDefault();
		var totalUserDivs = jQuery('.control-tr-user').length;
		var totalUsers = '<?php echo count($allusers); ?>';
        var user_selected = 1;
        jQuery('.big_social_cont_user .control-tr-user').each(function(){
            jQuery(this).find('.user-selevct').removeClass("error");
            if(jQuery(this).find('.user-selevct').val() === ""){
                user_selected = 0;
                jQuery(this).find('.user-selevct').addClass("error").focus();
            }
        });
		if(user_selected == 0)
		{
		    alert('<?php _e("Please select user from the previously added rule before adding new!", "wp-file-manager-pro"); ?>');
		}
		else
		{
            jQuery('.big_social_cont_user').append( jQuery('.item_cliche_user').html() );
            jQuery('.control-tr-user:eq('+totalUserDivs+') .tmpchkuname').attr('name','users_fileoperations_'+totalUserDivs+'[]');
            jQuery('.control-tr-user:eq('+totalUserDivs+') .dtchkuname').attr('name','user_disable_toolbar_'+totalUserDivs);
            jQuery('.control-tr-user:eq('+totalUserDivs+') .cmchkuname').attr('name','user_disable_context_'+totalUserDivs);
            jQuery('.big_social_cont_user .group_box_wrap:last-child').find("select.user-selevct").addClass("restriction");
            jQuery('html, body').animate({
                scrollTop: jQuery(".big_social_cont_user .control-tr-user:last-child").offset().top
            }, 1000);
        }
	});
    jQuery(document).on('click', '.delete_item_user',  function() {
		var point = jQuery(this).parents('.control-tr-user');
		point.fadeOut(500, function(){	
		jQuery(this).replaceWith('');
		var totalUserDivs = jQuery('.control-tr-user').length;
		for(i=1; i<=totalUserDivs-1; i++){
		  jQuery('.control-tr-user:eq('+i+') .tmpchkuname').attr('name','users_fileoperations_'+i+'[]');
		  jQuery('.control-tr-user:eq('+i+') .dtchkuname').attr('name','user_disable_toolbar_'+i);
		  jQuery('.control-tr-user:eq('+i+') .cmchkuname').attr('name','user_disable_context_'+i);
		}
		 });
	});
});

jQuery(document).ready(function(e) {
      jQuery(".setting_pro_tab a").click(function(event) {
        event.preventDefault();
        jQuery(this).parent().addClass("current");
        jQuery(this).parent().siblings().removeClass("current");
        var tab = jQuery(this).attr("href");
        var tab_val = jQuery.trim(tab.replace('#', ''));
        jQuery("#selected_tab").val(tab_val);
        jQuery(".setting_pro_tab_content").not(tab).css("display", "none");
        jQuery(tab).fadeIn();
    });
	
    jQuery(document).on('click', '.fmcollapse',  function() { 
      jQuery(this).next().slideToggle('fast');
	  jQuery(this).toggleClass('non-active');
    });
});

jQuery("#submit").click(function(e){
    var user_selected = 1;
    var role_selected = 1;
    if(jQuery('.big_social_cont_user .control-tr-user').length > 0){
        jQuery('.big_social_cont_user .control-tr-user').each(function(){
            jQuery(this).find('.user-selevct').removeClass("error");
            if(jQuery(this).find('.user-selevct').val() === ""){
                user_selected = 0;
                jQuery(this).find('.user-selevct').addClass("error").focus();
            }
        });
        if(user_selected == 0)
        {
            alert('<?php _e("Please select user in the added user restriction rule before saving.", "wp-file-manager-pro"); ?>');
        }
    }

    if(jQuery('.big_social_cont .control-tr').length > 0){
        jQuery('.big_social_cont .control-tr').each(function(){
            jQuery(this).find('.user-selevct').removeClass("error");
            if(jQuery(this).find('.user-selevct').val() == ""){
                role_selected = 0;
                jQuery(this).find('.user-selevct').addClass("error").focus();
            }
        });
		if(role_selected == 0)
		{
		    alert('<?php _e("Please select user role in the added user role restriction rule before saving.", "wp-file-manager-pro"); ?>');
		}
    }

    if(user_selected == 0 || role_selected == 0){
        e.preventDefault();
    }
});
</script>