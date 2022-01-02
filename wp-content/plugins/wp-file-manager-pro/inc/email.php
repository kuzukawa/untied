<?php if ( ! defined( 'ABSPATH' ) ) exit;
$this->fm_custom_assets();
$opt = get_option('wp_file_manager_email_notifications');
$msg = isset($_GET['msg']) ? sanitize_text_field($_GET['msg']) : '';
if(isset($_POST['submit']) && wp_verify_nonce( $_POST['mk_fm_nonce_field'], 'mk_fm_action' )):
echo __('Saving please wait...', 'wp-file-manager-pro');
$unset = array('mk_fm_nonce_field','_wp_http_referer', 'submit');
foreach($unset as $nu) {
	unset($_POST[$nu]);
}

$saveSettings = update_option('wp_file_manager_email_notifications', $_POST );

	if($saveSettings){
		 $this->redirect('?page=wpfm-emails&msg=1');	
		} else {
		 $this->redirect('?page=wpfm-emails&msg=2');	
		}
endif; ?>
<div class="wrap">
<div class="fmInnerWrap">

<h3 class="mainHeading">
<span class="headingIcon"><img src="<?php echo plugins_url( 'images/fm_email_icon.png', __FILE__ );?>"></span>
<span class="headingText"><?php _e('Email Notifications', 'wp-file-manager-pro');?></span>
</h3>


<?php
if(isset($_GET['msg']) && $_GET['msg'] == '1'):?>
<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated"> 
<p><strong><?php _e('Settings saved.','wp-file-manager-pro'); ?></strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text"><?php _e('Dismiss this notice.','wp-file-manager-pro'); ?></span></button></div>
<?php elseif(isset($_GET['msg']) && $_GET['msg'] == '2'):?>
<div class="error updated settings-error notice is-dismissible" id="setting-error-settings_updated"> 
<p><strong><?php _e('Settings not saved.','wp-file-manager-pro'); ?></strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text"><?php _e('Dismiss this notice.','wp-file-manager-pro'); ?></span></button></div>
<?php endif; ?>
<div id="email" class="tabcontent" style="display:block">
<form id="fm_frm_email" name="fm_frm_email" action="" method="post">
<?php wp_nonce_field( 'mk_fm_action', 'mk_fm_nonce_field' ); ?>
 <table class="form-table emailNotiTable">
<tbody>
<tr class="fir-en">
<th scope="row">
<label for="fm_enable_admin_email"><?php _e('Enable?', 'wp-file-manager-pro');?> </label></th>

<td style="padding-bottom: 20px;">
<span class="styledCheckbox mrt10">
<input type="checkbox" name="fm_enable_admin_email" value="1" <?php echo (isset($opt['fm_enable_admin_email']) && $opt['fm_enable_admin_email'] == 1) ? 'checked="checked"' : '';?>/> <span class="fm_checkmark"></span>
</span>
</td>

</tr>
<tr>
<th scope="row"><label class="lab_sub" for="admin_email"><?php _e('Email Addresses', 'wp-file-manager-pro');?> </label></th>
<td>
<?php if(!empty($opt['fmn_email']) && is_array($opt['fmn_email'])) {
foreach($opt['fmn_email'] as $key => $emails) {	
if($key == 0) { ?>
<div><input name="fmn_email[]" type="email" aria-describedby="admin-email-description" value="<?php echo $emails;?>" class="regular-text ltr ten_email" onfocusout="validateEmail(this)" onkeyup="validateEmail(this)"></div>
<?php } else { ?>
<div class="clone_inp" style="padding:5px 0;">
<input name="fmn_email[]" type="email" aria-describedby="admin-email-description" value="<?php echo $emails;?>" class="regular-text ltr ten_email" onfocusout="validateEmail(this)" onkeyup="validateEmail(this)">
<a href="#" class="delete_ten_email" title="<?php _e('Delete', 'wp-file-manager-pro');?>"><img src="<?php echo plugins_url( 'images/delete-mail-text-icon.png', __FILE__ );?>"/> </a>
</div>
<?php } } } else { ?>
<input name="fmn_email[]" type="email" aria-describedby="admin-email-description" value="<?php echo get_option('admin_email');?>" class="regular-text ltr ten_email">
<?php } ?>

<div class="more_ten_email"></div>
<div class="fm_addMoreBtnDv">
<button class="button add_more_ten_email"><?php _e('Add More <strong>+</strong>', 'wp-file-manager-pro');?></button>
</div>
<p class="description" id="admin-email-description"><?php _e('This address is used for notification purposes, like theme/plugin notification.', 'wp-file-manager-pro');?></p></td>
</tr>

<?php /* Start - Email Notification Text */ ?>
<tr>
<th scope="row"></th>
<td><h3><?php _e('Files Upload Notifications', 'wp-file-manager-pro');?></h3></td>
</tr>
<tr>
<th scope="row"><label class="lab_sub"><?php _e('Subject', 'wp-file-manager-pro');?></label></th>
<td>
<input name="fmn_file_upload_subject" type="text" value="<?php echo (isset($opt['fmn_file_upload_subject']) && 
!empty(trim($opt['fmn_file_upload_subject']))) ? $opt['fmn_file_upload_subject'] : __('Files Uploaded', 'wp-file-manager-pro')?>" class="regular-text">
</td>
</tr>
<tr>
<?php $fmn_file_upload_body = 'Hello, %adminemail% - %siteurl%';
 $fmn_file_upload_body .= ', %files% are uploaded on filemanager of your website %siteurl% by User: %username% (%$useremail%) (ID: %$userid%)';
 $fmn_file_upload_body .= ', Thanks.';
?>
<th scope="row"><?php _e('Body', 'wp-file-manager-pro');?></th>
<td>
<textarea name="fmn_file_upload_body" rows="10" cols="50" id="fmn_file_upload_body" class="large-text code"><?php echo (isset($opt['fmn_file_upload_body']) && !empty(trim($opt['fmn_file_upload_body']))) ? $opt['fmn_file_upload_body'] : $fmn_file_upload_body;?></textarea>
<p class="description"><strong><?php _e('Example:', 'wp-file-manager-pro')?></strong> <?php echo $fmn_file_upload_body;?></p>
</td>
</tr>


<tr>
<th scope="row"></th>
<td><h3><?php _e('Files Download Notifications', 'wp-file-manager-pro');?></h3></td>
</tr>
<tr>
<th scope="row"><label class="lab_sub"><?php _e('Subject', 'wp-file-manager-pro');?></label></th>
<td>
<input name="fmn_file_download_subject" type="text" value="<?php echo (isset($opt['fmn_file_download_subject']) && 
!empty(trim($opt['fmn_file_download_subject']))) ? $opt['fmn_file_download_subject'] : __('Files Downloaded', 'wp-file-manager-pro')?>" class="regular-text">
</td>
</tr>
<tr>
<?php $fmn_file_download_body = 'Hello, %adminemail% - %siteurl%';
 $fmn_file_download_body .= ', %files% are downloaded on filemanager of your website %siteurl% by User: %username% (%$useremail%) (ID: %$userid%)';
 $fmn_file_download_body .= ', Thanks.';
?>
<th scope="row"><?php _e('Body', 'wp-file-manager-pro');?></th>
<td>
<textarea name="fmn_file_download_body" rows="10" cols="50" id="fmn_file_download_body" class="large-text code"><?php echo (isset($opt['fmn_file_download_body']) && !empty(trim($opt['fmn_file_download_body']))) ? $opt['fmn_file_download_body'] : $fmn_file_download_body;?></textarea>
<p class="description"><strong><?php _e('Example:', 'wp-file-manager-pro')?></strong> <?php echo $fmn_file_download_body;?></p>
</td>
</tr>

<tr>
<th scope="row"></th>
<td><h3><?php _e('Files Edit Notifications', 'wp-file-manager-pro');?></h3></td>
</tr>
<tr>
<th scope="row"><label class="lab_sub"><?php _e('Subject', 'wp-file-manager-pro');?></label></th>
<td>
<input name="fmn_file_edit_subject" type="text" value="<?php echo (isset($opt['fmn_file_edit_subject']) && 
!empty(trim($opt['fmn_file_edit_subject']))) ? $opt['fmn_file_edit_subject'] : __('Files Modified or Edited', 'wp-file-manager-pro')?>" class="regular-text">
</td>
</tr>
<tr>
<?php $fmn_file_edit_body = 'Hello, %adminemail% - %siteurl%';
 $fmn_file_edit_body .= ', %files% are modified or edited on filemanager of your website %siteurl% by User: %username% (%$useremail%) (ID: %$userid%)';
 $fmn_file_edit_body .= ', Thanks.';
?>
<th scope="row"><?php _e('Body', 'wp-file-manager-pro');?></th>
<td>
<textarea name="fmn_file_edit_body" rows="10" cols="50" id="fmn_file_edit_body" class="large-text code"><?php echo (isset($opt['fmn_file_edit_body']) && !empty(trim($opt['fmn_file_edit_body']))) ? $opt['fmn_file_edit_body'] : $fmn_file_edit_body;?></textarea>
<p class="description"><strong><?php _e('Example:', 'wp-file-manager-pro')?></strong> <?php echo $fmn_file_edit_body;?></p>
</td>
</tr>




<?php /* End - Email Notification Text */?>

</tbody></table>
<div class="btnDv"><input type="submit" name="submit" id="submit" class="button button-primary fmCustomBtn" value="<?php _e('Save Changes', 'wp-file-manager-pro');?>"></div>
</form>
</div>
<script type="text/javascript">
jQuery(document).ready(function(e) {
    jQuery('.add_more_ten_email').click(function(e) {
        e.preventDefault();
		var input_ele = '<div class="clone_inp" style="padding:5px 0;">';
		input_ele += '<input name="fmn_email[]" type="email" aria-describedby="admin-email-description" value="" class="regular-text ltr ten_email" onfocusout="validateEmail(this)">';
		input_ele +=' <a href="#" class="delete_ten_email" title="<?php _e('Delete', 'wp-file-manager-pro');?>"><img src="<?php echo plugins_url( 'images/delete-mail-text-icon.png', __FILE__ );?>"/> </a>';
		input_ele +='</div>';
		jQuery('.more_ten_email').append(input_ele);
    });
	jQuery(document).on('click', '.delete_ten_email', function(e){
		e.preventDefault();
		var point = jQuery(this).parents('.clone_inp');
		point.fadeOut(100, function(){ 		
		   jQuery(this).replaceWith('');
		 });
	});
});
function validateEmail(control){
	jQuery(control).removeClass("v-error");
	var email = jQuery(control).val();
	var emailReg = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	if( !emailReg.test(email)) {
		jQuery(control).addClass("v-error");
	}
}
jQuery("#fm_frm_email").on('submit',function(){
	if(jQuery(".v-error").length > 0){
		alert('<?php _e('All added email fields needs to filled and should have valid email address.', 'wp-file-manager-pro');?>');
		return false;
	}
	else{
		var isValid = true;
		var emailReg = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		jQuery('input.ten_email').each(function() {
			var email = jQuery(this).val();
			if( jQuery(this).val() === '' ){
				isValid = false;
				jQuery(this).addClass('v-error');
			} else if (!emailReg.test(email)){
				isValid = false;
				jQuery(this).addClass('v-error');
			}
			else {
				jQuery(this).removeClass('v-error');
			}
		});
		if(isValid === true){
			return true;
		}
		else{
			alert('<?php _e('All added email fields needs to filled and should have valid email address.', 'wp-file-manager-pro');?>');
			return false;
		}
	}
});
</script>
</div>
</div>