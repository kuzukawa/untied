<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<style>
.wrap-two .by-box .add-plug .txt-box .button:hover {
    background: #0071a1;
    border-color: #0071a1;
    color: #fff;
}
.mk_green { color:#fff; background:#0C0; border-radius: 50% }

.wrap-two{
	width:96%;
	float:left;
	background:#fff;
	border:1px #dddddd solid;
	margin-top: 15px;
	padding:20px;
}
.wrap-two h2{
	margin-top: 0px;
    border-bottom: 1px #dddd solid;
    padding-bottom: 15px;   
}
.wrap-two h2 img{
	float:left;
	margin-right:15px;
}
.wrap-two h2 p{
	font-size: 22px;
	margin-top: 0px;
    margin-bottom: 0px;
	font-weight: bold;
}
.wrap-two .by-box{
	width:100%;
	float:left;
	max-width:500px;
}
.wrap-two .by-box .add-plug{
	border-bottom:1px #dddddd solid;
	padding:15px 0px;
	width:100%;
	float:left;
}
.wrap-two .by-box .add-plug .img-box{
	width:50%;
	float:left;
}
.wrap-two .by-box .add-plug .img-box p{
	font-size: 18px;
    font-weight: bold;
    color: #000;
    margin: 0px;
    margin-top: 8px;
}
.wrap-two .by-box .add-plug .img-box img{
		float:left;
	    margin-right: 15px;
		margin-top: 4px;
}
.wrap-two .by-box .add-plug .txt-box{
	width:50%;
	float:left;
	text-align:right;
}
.wrap-two .by-box .add-plug .txt-box p{
	margin: 0px;
    background: #52be22;
    color: #fff;
    width: 120px;
    float: right;
    text-align: left;
    min-height: 40px;
    line-height: 37px;
    border-radius: 5px;
    font-size: 16px;
    font-weight: bold;
	cursor:pointer;
}
.wrap-two .by-box .add-plug .txt-box p .mk_green{
	background: none;
    margin-top: 10px;
    float: left;
    padding-left: 9px;
    margin-right: 5px;;
}
.wrap-two .by-box .add-plug .txt-box .button{
	background: #267ddd;
    color: #fff;
    font-size: 16px;
    font-weight: bold;
    border: 0px;
    border-radius: 5px;
    width: 120px;
    text-align: center;
    min-height: 40px;
    line-height: 37px;
}

</style>
<?php $dir = ABSPATH.'wp-content/plugins/';
function mkgeneratePluginActivationLinkUrl($plugin, $action = 'activate')
{
   	if ( strpos( $plugin, '/' ) ) {
		$plugin = str_replace( '\/', '%2F', $plugin );
	}
	$url = sprintf( admin_url( 'plugins.php?action=' . $action . '&plugin=%s&plugin_status=all&paged=1&s' ), $plugin );
	$_REQUEST['plugin'] = $plugin;
	$url = wp_nonce_url( $url, $action . '-plugin_' . $plugin );
	return $url;
}
?>
<div class="wrap">
<div class="wrap-two">

<h2> <img src="<?php echo plugins_url( 'images/add.png', dirname(__FILE__) ); ?>"/> 
	<p><?php _e('Add-ons', 'wp-file-manager-pro');?></p></h2>
	<div class="by-box">
		<div class="add-plug">
			<div class="img-box">
				<img src="<?php echo plugins_url( 'images/gc-filemanager.svg', dirname(__FILE__) ); ?>" style="width:36px"/> 
				<p><?php _e('Google Cloud', 'wp-file-manager-pro');?> </p>
			</div>
			<div class="txt-box">
				<p>
					<?php if(class_exists('wp_file_manager_google_cloud')) {
						echo __('Installed', 'wp-file-manager-pro').' <span class="dashicons dashicons-yes mk_green"></span>';
					} else if(file_exists($dir.'wp-file-manager-google-cloud')) { 
						echo '<a href="'.mkgeneratePluginActivationLinkUrl('wp-file-manager-google-cloud/wp_file_manager_google_cloud.php').'" class="button">'.__('Activate', 'wp-file-manager-pro').'</a>'; 
					} else {
						echo '<a href="https://filemanagerpro.io/product/file-manager-google-cloud" target="_blank" class="button">'.__('Buy Now', 'wp-file-manager-pro').'</a>';                 
					}
					?>
                </p>
			</div>
		</div>
		<div class="add-plug">
			<div class="img-box">
				<img src="<?php echo plugins_url( 'images/box.png', dirname(__FILE__) ); ?>"/>
				<p><?php _e('Box', 'wp-file-manager-pro');?> </p>
			</div>
			<div class="txt-box">
				<p>
				<?php if(class_exists('wp_file_manager_box')) {
                  echo __('Installed', 'wp-file-manager-pro').' <span class="dashicons dashicons-yes mk_green"></span>';
                 } else if(file_exists($dir.'wp-file-manager-box')) { 
				  echo '<a href="'.mkgeneratePluginActivationLinkUrl('wp-file-manager-box/box.php').'" class="button">'.__('Activate', 'wp-file-manager-pro').'</a>'; 
				 } else {
                 echo '<a href="https://filemanagerpro.io/product/file-manager-box" target="_blank" class="button">'.__('Buy Now', 'wp-file-manager-pro').'</a>';                 
				 }
				?>                
                </p>
			</div>
		</div>
		
		
		<div class="add-plug">
			<div class="img-box">
				<img src="<?php echo plugins_url( 'images/drop.png', dirname(__FILE__) ); ?>"/>
				<p><?php _e('DropBox', 'wp-file-manager-pro');?> </p>
			</div>
			<div class="txt-box">
                <p><?php if(class_exists('wp_file_manager_dropbox')) {
                  echo __('Installed', 'wp-file-manager-pro').' <span class="dashicons dashicons-yes mk_green"></span>';
                 } else if(file_exists($dir.'wp-file-manager-dropbox')) { 
				  echo '<a href="'.mkgeneratePluginActivationLinkUrl('wp-file-manager-dropbox/dropbox.php').'" class="button">'.__('Activate', 'wp-file-manager-pro').'</a>'; 
				 } else {
                 echo '<a href="https://filemanagerpro.io/product/file-manager-dropbox" target="_blank" class="button">'.__('Buy Now', 'wp-file-manager-pro').'</a>';                 
				 }
				?> 
                </p>
			</div>
		</div>
		
		
		<div class="add-plug">
			<div class="img-box">
				<img src="<?php echo plugins_url( 'images/drive.png', dirname(__FILE__) ); ?>"/> 
				<p><?php _e('Google Drive', 'wp-file-manager-pro');?> </p>
			</div>
			<div class="txt-box">
				<p>
				<?php if(class_exists('wp_file_manager_googledrive')) {
                  echo __('Installed', 'wp-file-manager-pro').' <span class="dashicons dashicons-yes mk_green"></span>';
                 } else if(file_exists($dir.'wp-file-manager-googledrive')) { 
				  echo '<a href="'.mkgeneratePluginActivationLinkUrl('wp-file-manager-googledrive/wp_file_manager_googledrive.php').'" class="button">'.__('Activate', 'wp-file-manager-pro').'</a>'; 
				 } else {
                 echo '<a href="https://filemanagerpro.io/product/file-manager-google-drive" target="_blank" class="button">'.__('Buy Now', 'wp-file-manager-pro').'</a>';                 
				 }
				?> 				
				</p>
			</div>
		</div>

				<div class="add-plug">
			<div class="img-box">
				<img src="<?php echo plugins_url( 'images/one.png', dirname(__FILE__) ); ?>"/> 
				<p><?php _e('OneDrive', 'wp-file-manager-pro');?> </p>
			</div>
			<div class="txt-box">
               <p>
				<?php if(class_exists('wp_file_manager_onedrive')) {
                  echo __('Installed', 'wp-file-manager-pro').' <span class="dashicons dashicons-yes mk_green"></span>';
                 } else if(file_exists($dir.'wp-file-manager-onedrive')) { 
				  echo '<a href="'.mkgeneratePluginActivationLinkUrl('wp-file-manager-onedrive/wp_file_manager_onedrive.php').'" class="button">'.__('Activate', 'wp-file-manager-pro').'</a>'; 
				 } else {
                 echo '<a href="https://filemanagerpro.io/product/file-manager-one-drive" target="_blank" class="button">'.__('Buy Now', 'wp-file-manager-pro').'</a>';                 
				 }
				?>
                </p>
			</div>
		</div>
		
		<div class="add-plug">
			<div class="img-box">
				<img src="<?php echo plugins_url( 'images/aws.png', dirname(__FILE__) ); ?>"/>
				<p><?php _e('AWS S3', 'wp-file-manager-pro');?> </p>
			</div>
			<div class="txt-box">
                <p><?php if(class_exists('wp_file_manager_aws_s3')) {
                  echo __('Installed', 'wp-file-manager-pro').' <span class="dashicons dashicons-yes mk_green"></span>';
                 } else if(file_exists($dir.'wp-file-manager-aws-s3')) { 
				  echo '<a href="'.mkgeneratePluginActivationLinkUrl('wp-file-manager-aws-s3/wp_file_manager_aws_s3.php').'" class="button">'.__('Activate', 'wp-file-manager-pro').'</a>'; 
				 } else {
                 echo '<a href="https://filemanagerpro.io/product/file-manager-aws-s3/" target="_blank" class="button">'.__('Buy Now', 'wp-file-manager-pro').'</a>';                 
				 }
				?> 
                </p>
			</div>
		</div>

		<div class="add-plug">
			<div class="img-box">
				<img src="<?php echo plugins_url( 'images/digitalocean.svg', dirname(__FILE__) ); ?>" style="width: 36px;"/>
				<p><?php _e('Digital Ocean', 'wp-file-manager-pro');?> </p>
			</div>
			<div class="txt-box">
                <p><?php if(class_exists('wp_file_manager_digitalOcean')) {
                  echo __('Installed', 'wp-file-manager-pro').' <span class="dashicons dashicons-yes mk_green"></span>';
                 } else if(file_exists($dir.'file-manager-digital-ocean')) { 
				  echo '<a href="'.mkgeneratePluginActivationLinkUrl('file-manager-digital-ocean/wp_file_manager_digital_ocean.php').'" class="button">'.__('Activate', 'wp-file-manager-pro').'</a>'; 
				 } else {
                 echo '<a href="https://filemanagerpro.io/product/digital-ocean-add-on/" target="_blank" class="button">'.__('Buy Now', 'wp-file-manager-pro').'</a>';                 
				 }
				?> 
                </p>
			</div>
		</div>
		
		<div class="add-plug">
			<div class="img-box">
				<img src="<?php echo plugins_url( 'images/git.png', dirname(__FILE__) ); ?>"/> 
				<p><?php _e('Git Hub', 'wp-file-manager-pro');?> </p>
			</div>
			<div class="txt-box">
				<p>
					<?php if(class_exists('wp_file_manager_git')) {
						echo __('Installed', 'wp-file-manager-pro').' <span class="dashicons dashicons-yes mk_green"></span>';
					} else if(file_exists($dir.'wp-file-manager-git')) { 
						echo '<a href="'.mkgeneratePluginActivationLinkUrl('wp-file-manager-git/wp_file_manager_git.php').'" class="button">'.__('Activate', 'wp-file-manager-pro').'</a>'; 
					} else {
						echo '<a href="https://filemanagerpro.io/product/file-manager-git" target="_blank" class="button">'.__('Buy Now', 'wp-file-manager-pro').'</a>';                 
					}
					?>
                </p>
			</div>
		</div>
	</div>
</div>

</div>