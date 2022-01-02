<?php if(!class_exists('fm_shortcode_without_login')):
class fm_shortcode_without_login
{
   public function __construct(){
	  add_shortcode('wp_file_manager_without_login', array(&$this, 'wp_file_manager_without_login_view'));
	  // filter t show fm
	  add_filter('the_content',array($this,'fm_show_auto_filemanager'));
   }
   public function wp_file_manager_without_login_view($atts){
		$filemanagerReturn = '';
		include('inc/shortcode.php');
		return $filemanagerReturn;
   }
   public function fm_show_auto_filemanager($content) {
		   global $post;
		   $opt = get_option('wp_filemanager_options');
		   if(isset($opt['mk_pages_list'])) {
			   $filemanagerReturn = isset($opt['without_login_shortcode']) ? stripslashes($opt['without_login_shortcode']) : '[wp_file_manager_without_login]';
			    if(in_array($post->ID,$opt['mk_pages_list'])) {
				   if(isset($opt['display_fm_on_pages']) && $opt['display_fm_on_pages'] == 'after_content')	 {
					$content = $content.$filemanagerReturn;   
				   } else if(isset($opt['display_fm_on_pages']) && $opt['display_fm_on_pages'] == 'before_content') {
				    $content = $filemanagerReturn.$content;
				   } else{
				    $content = $content.$filemanagerReturn; 
				   }
				}
		   }
		   return $content;		   
   }
}
/* Required to hook with WP File Manager */
add_action('load_filemanager_extensions', 'fm_shortcode_without_login_call');
function fm_shortcode_without_login_call()
{
	new fm_shortcode_without_login;
}
endif; 