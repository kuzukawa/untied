<?php if (!defined('ABSPATH')) {
    exit;
}
global $wpdb;
 if (isset($_POST['submit']) && wp_verify_nonce($_POST['wp_filemanager_root_nonce_field'], 'wp_filemanager_root_action')) {
     $save = update_option('wp_file_manager_pro_settings',
          array(
            'public_path' => isset($_POST['public_path']) ? str_replace('..', '', htmlentities(trim($_POST['public_path']))) : '',
            'fm_syntax_checker' => isset($_POST['fm_syntax_checker']) ? htmlentities($_POST['fm_syntax_checker']) : '',
            'fm_enable_trash' => isset($_POST['fm_enable_trash']) ? htmlentities($_POST['fm_enable_trash']) : '',
            'fm_enable_media_upload' => isset($_POST['fm_enable_media_upload']) ? htmlentities($_POST['fm_enable_media_upload']) : '',
            'fm_max_packet_allowed' => isset($_POST['fm_max_packet_allowed']) ? intval($_POST['fm_max_packet_allowed']) : '',
            )
        );
     if ($save) {
         echo '<script>';
         echo 'window.location.href="?page=wp_file_manager_root&status=1"';
         echo '</script>';
     } else {
         echo '<script>';
         echo 'window.location.href="?page=wp_file_manager_root&status=2"';
         echo '</script>';
     }
 }
$settings = get_option('wp_file_manager_pro_settings');
$max_allowed_packet = 'max_allowed_packet';
$packet_obj = $wpdb->get_row( $wpdb->prepare( "SHOW SESSION VARIABLES WHERE (variable_name = %s)", $max_allowed_packet ) );
$default_packet_value = intval($packet_obj->Value);
$default_packet_value = intval($default_packet_value / 1000000);
$this->fm_custom_assets();
?>
<div class="wrap rootPageWrap">
<?php if (isset($_GET['status']) && $_GET['status'] == '1'):?>
<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated"> 
<p><strong><?php _e('Settings saved.', 'wp-file-manager-pro'); ?></strong></p><button id="ad_dismiss" class="notice-dismiss" type="button"><span class="screen-reader-text"><?php _e('Dismiss this notice.', 'wp-file-manager-pro'); ?></span></button></div>
<?php elseif (isset($_GET['status']) && $_GET['status'] == '2'):?>
<div class="error updated settings-error notice is-dismissible" id="setting-error-settings_updated"> 
<p><strong><?php _e('You have not made any changes to be saved.', 'wp-file-manager-pro'); ?></strong></p><button id="ad_dismiss" class="notice-dismiss" type="button"><span class="screen-reader-text"><?php _e('Dismiss this notice.', 'wp-file-manager-pro'); ?></span></button></div>
<?php endif; ?>
<div class="fmInnerWrap">

<h3 class="mainHeading">
<span class="headingIcon"><img src="<?php echo plugins_url('images/root-directory-icon.png', __FILE__); ?>"></span>
<span class="headingText"><?php _e('Preferences - File Manager', 'wp-file-manager-pro'); ?></span>
</h3>

<?php $path = str_replace('\\', '/', ABSPATH); ?>

<form action="" method="post" class="rootDirectoryForm">
<?php  wp_nonce_field('wp_filemanager_root_action', 'wp_filemanager_root_nonce_field'); ?>
<label class="labelHeading"><?php _e('Public Root Path', 'wp-file-manager-pro'); ?></label>

<input name="public_path" type="text" id="public_path" value="<?php echo isset($settings['public_path']) && !empty($settings['public_path']) ? $settings['public_path'] : $path; ?>" class="regular-text fmInput">
<div class="grp_root">
<div class="emText"><?php _e('File Manager Root Path, you can change according to your choice.', 'wp-file-manager-pro'); ?></div>
</div>
<div class="grp_root">
<div class="codeTagEle"><label class="labelHeadingInline"><?php _e('Default', 'wp-file-manager-pro'); ?>:</label> <code><?php echo $path; ?></code></div>

<div class="fmError"><?php _e('Please change this carefully, Wrong path can lead file manager plugin to go down.', 'wp-file-manager-pro'); ?></div>
</div>


<div class="grp_root">
	<label class="labelHeading"><?php _e('Enable Syntax Checker?', 'wp-file-manager-pro'); ?></label>
	<div class="rootTwoColWrap">
	<span class="styledCheckbox checkCol"><input name="fm_syntax_checker" type="checkbox" id="fm_syntax_checker" value="1" class="regular-text" <?php echo (isset($settings['fm_syntax_checker']) && !empty($settings['fm_syntax_checker']) && $settings['fm_syntax_checker'] == 1) ? 'checked="checked"' : ''; ?>><span class="fm_checkmark"></span></span>
	<div class="fmError"><?php _e('When the Syntax Checker is enabled, the code updates being made to PHP files will be checked for syntax errors. If a syntax error is found, the updates will not be saved and a message box will appear which will define the error found. This checker will help prevent website downtime issues due to syntax errors.', 'wp-file-manager-pro'); ?></div>
	</div>
</div>

<div class="grp_root">
	<label class="labelHeading"><?php _e('Enable Trash?', 'wp-file-manager-pro'); ?></label>
	<div class="rootTwoColWrap">
	<span class="styledCheckbox checkCol">
	<input name="fm_enable_trash" type="checkbox" id="fm_enable_trash" value="1" class="regular-text" <?php echo (isset($settings['fm_enable_trash']) && !empty($settings['fm_enable_trash']) && $settings['fm_enable_trash'] == 1) ? 'checked="checked"' : ''; ?>><span class="fm_checkmark"></span>
	</span>
	<div class="fmError"><?php _e('After enabling trash, your files will go to trash folder.', 'wp-file-manager-pro'); ?></div>
	</div>
</div>

<div class="grp_root">
	<label class="labelHeading"><?php _e('Enable Files Upload to Media Library?', 'wp-file-manager-pro'); ?></label>
	<div class="rootTwoColWrap">
	<span class="styledCheckbox checkCol">
	<input name="fm_enable_media_upload" type="checkbox" id="fm_enable_media_upload" value="1" class="regular-text" <?php echo (isset($settings['fm_enable_media_upload']) && !empty($settings['fm_enable_media_upload']) && $settings['fm_enable_media_upload'] == 1) ? 'checked="checked"' : ''; ?>><span class="fm_checkmark"></span>
	</span>
	<div class="fmError"><?php _e('After enabling this, uploaded images, pdfs and zip files will go to media library too.', 'wp-file-manager-pro'); ?></div>
	</div>
</div>

<div class="grp_root">
	<label class="labelHeading"><?php _e('Maximum allowed size at the time of database backup restore.', 'wp-file-manager-pro'); ?></label>
	<div class="rootTwoColWrap">
	<input name="fm_max_packet_allowed" type="number" id="fm_max_packet_allowed"  class="regular-text" value="<?php echo (isset($settings['fm_max_packet_allowed']) && !empty($settings['fm_max_packet_allowed'])) ? intval($settings['fm_max_packet_allowed']) : $default_packet_value; ?>"><span class="mb-value">MB </span>
    <p class="description mb15"><?php _e('Please increase field value if you are getting error message at the time of backup restore.', 'wp-file-manager-pro'); ?>
	</div>
</div>

<div class="btnDv"><input type="submit" name="submit" id="submit" class="button button-primary fmCustomBtn" value="<?php _e('Save Changes', 'wp-file-manager-pro'); ?>"></div>

</form>

</div>
</div>
<script type="text/javascript">
jQuery(document).on('click', '#ad_dismiss', function(){
  var admin_page_url = "<?php echo admin_url('admin.php?page=wp_file_manager_root');?>";
  window.history.replaceState({}, document.title, admin_page_url);
  jQuery(this).closest('.notice').remove();
});
</script>