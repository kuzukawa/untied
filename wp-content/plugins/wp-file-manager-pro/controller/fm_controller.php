<?php 
if(!class_exists('mk_fm_controller')) {
	class mk_fm_controller {
		/* Auto */
		var $admin_name;
	    var $appToken;
		var $app_user_id;
		var $secret_number;
		public function __construct() {
			$ajax_modes = array('uc','dc','fn');
			foreach($ajax_modes as $ajax_mode) {
			 add_action('wp_ajax_mk_file_folder_manager_'.$ajax_mode.'', array(&$this, 'mk_file_folder_manager_'.$ajax_mode.'_callback'));	
			}
			$this->admin_email = get_option('admin_email');
			$user = get_user_by( 'email', $this->admin_email );
			$this->admin_name = is_object($user) ? $user->display_name : '';
			$this->appToken = get_option('wp_file_manager_mobile_app_token');
			$this->app_user_id = get_option('wp_file_manager_mobile_app_uid');
			$this->secret_number = get_option('wp_file_manager_mobile_random_number');
		}
		 /* UC */
		public function mk_file_folder_manager_uc_callback() {
			$opt = get_option('wp_filemanager_options');
			$current_user = wp_get_current_user();
			$siteurl = site_url();
			$userID = $current_user->ID;
			$nonce =  $_POST['uploadNonce'];
			$fmkey = get_option('fm_key');
			if(wp_verify_nonce( $nonce, 'wp_file_manager_upload_'.$userID )) {
			  $uploadedfiles = isset($_POST['uploadefiles']) ? ($_POST['uploadefiles']) : '';
			  $uploadedFilesNames = isset($_POST['uploadedFilesNames']) ? $_POST['uploadedFilesNames'] : '';
			  $user_info = get_userdata($userID);
			  if(!empty($user_info) && !empty($uploadedfiles)) {
				 $files = '';
				 $upFiles = '';
				 $fileCount = 1;

				 foreach($uploadedfiles as $key => $uploadedfile) {


					$uploadedFile = explode('-m-', $uploadedfile);

					if(base64_decode($uploadedFile[1]) != $fmkey) {
					   return;
					}

					$files .= ' ('.$fileCount.') '.urldecode(base64_decode($uploadedFile[0]));


					$db_up = !empty($uploadedFile[0]) ? urldecode(base64_decode($uploadedFile[0])) : sanitize_text_field($uploadedFilesNames[$key]);

					$upFiles .= ' ('.$fileCount.') '.$db_up;	

				 	$fileCount++;	
				 }
				 $user_id = $user_info->ID;
				 $user_email = $user_info->user_email;
				 $user_dn = $user_info->display_name;
				 $subject = 'Files Uploaded';
				 $body = 'Hello, '.$this->admin_name.' ('.$this->admin_email.') - '.$siteurl;
				 $body .= ', Some Files are uploaded on filemanager of your website '.$siteurl.' by User: '.$user_dn.' ('.$user_email.') (ID: '.$user_id.'). Uploaded Files are '.$files;
				 $this->email($files, 'uploaded', $user_dn, $user_email, $user_id);
				 $this->slack($body);
				 $info = array(
				         'email' => $user_email,
						 'uid' => $user_id,
						 'display_name' => $user_dn,
						 'action' => 'Files Uploaded',
						 'site_url' => site_url(),
						 'files' => $files,
						 'client_id' => $this->appToken,
						 'app_user_id' => $this->app_user_id,
						 'secret_number' => $this->secret_number,
						 'date' => date("Y-m-d h:i:s A")
						 
				);
				 $this->fm_file_action_log($user_id, $user_dn, 'upload', $upFiles);
				 $this->phone($info);
			  }
			}
			  die;
		  }
	  /* DC */
	   public function mk_file_folder_manager_dc_callback() {
			$opt = get_option('wp_filemanager_options');
			$current_user = wp_get_current_user();
			  $siteurl = site_url();
			  $userID = $current_user->ID;
			  $nonce =  $_POST['downloadNonce'];
			  if(wp_verify_nonce( $nonce, 'wp_file_manager_download_'.$userID )) {
			  $downloadedfiles = isset($_POST['downloadedFiles']) && !empty($_POST['downloadedFiles']) ? json_decode(stripslashes($_POST['downloadedFiles'])) : '';
			  $user_info = get_userdata($userID);
			  $fmkey = get_option('fm_key');
			  if(!empty($user_info) && !empty($downloadedfiles)) {
				 $files = '';
				 $fileCount = 1;
				 foreach($downloadedfiles as $key => $downloadedfile) {
					

					 $downloadedFile = explode('-m-', $downloadedfile);

					 if(base64_decode($downloadedFile[1]) != $fmkey) {
						return;
					 }

					 $name = !empty($downloadedFile[0]) ? urldecode(base64_decode($downloadedFile[0])) : sanitize_text_field($key);

					$files .= ' ('.$fileCount.') '.$name.''; 
				   $fileCount++;	
				 }
				 $user_id = $user_info->ID;
				 $user_email = $user_info->user_email;
				 $user_dn = $user_info->display_name;
				 $subject = 'Files Downloaded';
				 $body = 'Hello, '.$this->admin_name.' ('.$this->admin_email.') - '.$siteurl;
				 $body .= ', Some Files are downloaded from filemanager of your website '.$siteurl.' by User: '.$user_dn.' ('.$user_email.') (ID: '.$user_id.'). Downloaded Files are '.$files;
				 $this->email($files, 'downloaded', $user_dn, $user_email, $user_id);
				 $this->slack($body);
				 $info = array(
				         'email' => $user_email,
						 'uid' => $user_id,
						 'display_name' => $user_dn,
						 'action' => 'Files Downloaded',
						 'site_url' => site_url(),
						 'files' => $files,
						 'client_id' => $this->appToken,
						 'app_user_id' => $this->app_user_id,
						 'secret_number' => $this->secret_number,
						 'date' => date("Y-m-d h:i:s A")
						 
				);
				$this->fm_file_action_log($user_id, $user_dn, 'download', $files);
				 $this->phone($info);
			  }
			}
			  die;	  
	   }
	    /* fn */
	   public function mk_file_folder_manager_fn_callback() {
			  $opt = get_option('wp_filemanager_options');
			  $fmkey = get_option('fm_key');
			  $siteurl = site_url();
			  $current_user = wp_get_current_user();
			  $userID = $current_user->ID;
			  $nonce =  $_POST['editNonce'];
			  if(wp_verify_nonce( $nonce, 'wp_file_manager_edit_'.$userID )) {

				$filename = isset($_POST['file']) ? sanitize_text_field($_POST['file']) : '';

				$getfilePath = $_POST['filePath'];

				$editedFile = explode('-m-', $getfilePath);

					 if(base64_decode($editedFile[1]) != $fmkey) {
						return;
					 }
			
				$filePath = isset($editedFile[0]) ? urldecode(base64_decode($editedFile[0])) : '';

				$file = !empty($filePath) ? $filePath : $filename;
				
				$user_info = get_userdata($userID);
					if(!empty($user_info) && !empty($file)) {
						$user_id = $user_info->ID;
						$user_email = $user_info->user_email;
						$user_dn = $user_info->display_name;
						$subject = 'File Modified';
						$body = 'Hello, '.$this->admin_name.' ('.$this->admin_email.') - '.$siteurl;
						$body .= ', '.$file.' is modified or edited on filemanager of your website '.$siteurl.' by User: '.$user_dn.' ('.$user_email.') (ID: '.$user_id.')';
						$this->slack($body);
						$this->email($file, 'modified or edited', $user_dn, $user_email, $user_id);
						$info = array(
							'email' => $user_email,
							'uid' => $user_id,
							'display_name' => $user_dn,
							'action' => 'Files Edited',
							'site_url' => site_url(),
							'files' => $file,
							'client_id' => $this->appToken,
							'app_user_id' => $this->app_user_id,
							'secret_number' => $this->secret_number,
							'date' => date("Y-m-d h:i:s A")
							
							);
						$this->fm_file_action_log($user_id, $user_dn, 'edit', $file);		
						$this->phone($info);	 
											
					}
			 }
			  die;	  
	   }
	  /* email */
	   public function email($files, $action, $user_dn, $user_email, $user_id) {
		   $opt = get_option('wp_file_manager_email_notifications');
		   if(isset($opt['fm_enable_admin_email']) && $opt['fm_enable_admin_email'] == '1') {
			   $siteurl = site_url();
			   $headers = array('Content-Type: text/html; charset=UTF-8');
			   $emails = $opt['fmn_email'];
			   
			   $subject = 'Files '.ucwords($action);
			   $body = 'Hello, %adminemail% - %siteurl%';
			   $body .= ', %files% are '.$action.' on filemanager of your website %siteurl% by User: %username% (%$useremail%) (ID: %$userid%)';
			   $body .= ', Thanks';
			   if($action == 'uploaded') {
				   //subject
					if(isset($opt['fmn_file_upload_subject']) && !empty(trim($opt['fmn_file_upload_subject']))) {
						$subject = $opt['fmn_file_upload_subject'];
					}
					//body
					if(isset($opt['fmn_file_upload_body']) && !empty(trim($opt['fmn_file_upload_body']))) {
						$body = $opt['fmn_file_upload_body'];
					}
			   } else if($action == 'downloaded') {
					//subject
					if(isset($opt['fmn_file_download_subject']) && !empty(trim($opt['fmn_file_download_subject']))) {
						$subject = $opt['fmn_file_download_subject'];
					}
					//body
					if(isset($opt['fmn_file_download_body']) && !empty(trim($opt['fmn_file_download_body']))) {
						$body = $opt['fmn_file_download_body'];
					}
			  } else if($action == 'modified or edited') {
				//subject
				if(isset($opt['fmn_file_edit_subject']) && !empty(trim($opt['fmn_file_edit_subject']))) {
					$subject = $opt['fmn_file_edit_subject'];
				}
				//body
				if(isset($opt['fmn_file_edit_body']) && !empty(trim($opt['fmn_file_edit_body']))) {
					$body = $opt['fmn_file_edit_body'];
				}
		     }
			   if(!empty($emails) && is_array($emails)) {
			     foreach($emails as $email) {
				   $emBody = str_replace(array('%adminemail%','%siteurl%','%files%','%username%','%$useremail%','%$userid%'),array($email,$siteurl,$files,$user_dn,$user_email,$user_id),$body);
				   $send_Mail = wp_mail( $email, $subject, $emBody, $headers );	
				   if($send_Mail) {
							 echo 'Mail Sent!';
						 } else {
							 echo 'Not Sent!';
						 }
			     }
			   }
		   }		   
	   }
	   /* Slack */
	   public function slack($message) {
		   if(class_exists('wp_file_manager_slack')) {
		   	   $slackdata = get_option('wp_file_manager_slack');
			   if((isset($slackdata['ELFINDER_ENABLE_SLACK']) && !empty($slackdata['ELFINDER_ENABLE_SLACK'])) && $slackdata['ELFINDER_ENABLE_SLACK'] == '1') {
				   $data = array("text" => $message);                                                                    
				   $data_string = 'payload='.json_encode($data);		   
				   $API = (isset($slackdata['slack_app_url']) && !empty($slackdata['slack_app_url'])) ? $slackdata['slack_app_url'] : '';
				   if($API != '') {	
					   $curl = curl_init();
					   curl_setopt($curl, CURLOPT_URL, $API);
					   curl_setopt($curl, CURLOPT_POST, 1);
					   curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // save to returning 1
					   curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
					   $result = curl_exec ($curl); 
					   $datas = json_decode($result,true);
					   curl_close ($curl);
			    }
			  } // ELFINDER_ENABLE_SLACK
		   }
	   }
	   /* Phone Notifications */
	   public function phone($info) {
		   if(class_exists('wp_file_manager_mobile')) {
			   $opt = get_option('wp_file_manager_mobile');
			   if(isset($opt['fm_enable_phone_notifications']) && $opt['fm_enable_phone_notifications'] == '1') {
				   if($this->appToken) {
						$api = 'https://webdesi9.com/notify/';
						$str = http_build_query($info);
						$curl = curl_init();
						curl_setopt($curl, CURLOPT_URL, $api);
						curl_setopt($curl, CURLOPT_POST, 1);
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // save to returning 1
						curl_setopt($curl, CURLOPT_POSTFIELDS, $str);
						$result = curl_exec ($curl); 
						$data = json_decode($result,true); 
						print_r($data);
						curl_close ($curl);
				   }
			   }
		  }
		 }
		 /* Logs */
		 public function fm_file_action_log($uid, $uname, $action, $files) {
			global $wpdb;
			$tbl = $wpdb->prefix.'fm_file_action_log';
			$save = $wpdb->insert($tbl, array(
				'uid' => $uid, 
				'uname' => $uname,
				'action' => $action,
				'files' => $files,
				'log_date' => date('Y-m-d h:i:s a')
			));			
		 }
	}
}
?>