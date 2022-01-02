<?php if ( ! defined( 'ABSPATH' ) ) exit; 
if(isset($_REQUEST["fm_up_submit"]) && wp_verify_nonce($_POST['wp_filemanager_popup_nonce_field'], 'wp_filemanager_popup_action')){
	update_option('wp_filemanager_sc_update',mk_file_folder_manager::FILE_MANAGER_VERSION);
}
$opt_doc_popu = get_option('wp_filemanager_sc_update');
if((!$opt_doc_popu && empty($opt_doc_popu)) || (!empty($opt_doc_popu) && mk_file_folder_manager::FILE_MANAGER_VERSION > $opt_doc_popu)){
	?>
	<style>        
		.fm_updater_outer,
		.fm_updater_outer *{ box-sizing: border-box; }
		.fm_updater_outer{ background: rgba(0, 0, 0, 0.7); width: 100%; height: 100%; position: fixed; top: 0; left: 0; z-index: 999;}
		.fm_updater_outer .fm_updater{font-family:Arial, sans-serif; max-width: 485px;background: #fff;position: absolute;left: 50%;top: 50%;transform: translate(-50%, -50%);}
		.fm_updater_outer .fm_updater .fm_updater_header{ background: #203564; text-align:center; padding:25px 20px 32px; position: relative;  }
		.fm_updater_outer .fm_updater .fm_updater_header img{ max-width: 64px; margin-bottom: 20px; }
		.fm_updater_outer .fm_updater .fm_updater_header h2{ color: #fff; font-size:22px; margin-top: 0px;  margin-bottom: 0px; }
		.fm_updater_outer .fm_updater .fm_updater_wrapper{ padding: 30px 40px; text-align: center; }
		.fm_updater_outer .fm_updater .fm_updater_wrapper p{ font-size: 14px; font-weight: 400; line-height: 24px; color: rgba(0, 0, 0, 0.7); margin-bottom: 25px; margin-top:0px; }
		.fm_updater_outer .fm_updater .fm_updater_wrapper a.fm_documentation{ display: inline-block; color: #fff; background: #203564; text-decoration: none; text-transform: uppercase; font-size: 13px; font-weight: normal; letter-spacing: 0.1px; padding: 15px 20px; border-radius: 5px; border: none; }
		.fm_updater_outer .fm_updater .fm_updater_wrapper a.fm_documentation:hover{ background: #1c305d; }
		.fm_updater_outer .fm_updater_close{ position: absolute; right: 10px; top: 10px; margin: 0px; cursor: pointer;background: inherit;border: none;}
		.fm_updater_outer .fm_updater .fm_updater_header .fm_updater_close img {margin: 0px;}
		.fm_updater_outer .fm_updater_close:hover{ opacity: 0.8; }
		.fm_updater_outer .fm_quote {display:block;}
		.fm_updater_outer .fm_updater .fm_updater_wrapper p.no-gap{ margin-bottom: 10px; }
	</style>
	<div class="fm_updater_outer">
		<div class="fm_updater">
			<form id="fm_update_popup" name="fm_update_popup" action="" method="post">
				<?php  wp_nonce_field('wp_filemanager_popup_action', 'wp_filemanager_popup_nonce_field'); ?>
				<div class="fm_updater_header">
					<img src="<?php echo fm_plugin_url;?>/images/digitalocean.svg" alt="<?php _e('File Manager Pro Update','wp-file-manager-pro');?> <?php echo mk_file_folder_manager::FILE_MANAGER_VERSION;?>" />
					<h2><?php _e('File Manager Pro','wp-file-manager-pro');?> <?php echo mk_file_folder_manager::FILE_MANAGER_VERSION;?></h2>
					<button name="fm_up_submit" type="submit" class="fm_updater_close"><img src="<?php echo fm_plugin_url;?>/images/fm_updator_close.svg" /></button>
				</div>
				<div class="fm_updater_wrapper">
					<p class="no-gap"><strong><?php _e('FileManager','wp-file-manager-pro');?> <?php echo mk_file_folder_manager::FILE_MANAGER_VERSION;?></strong>, <?php _e('is now compatible with Digital Ocean Add-on.','wp-file-manager-pro');?></p><p><?php _e('Users can now display Digital Ocean files and folders in FileManager in just 1-Click!','wp-file-manager-pro');?>.</p>
					<a class="fm_documentation" href="https://filemanagerpro.io/product/digital-ocean-add-on/" target="_blank" rel="noopener noreferrer"><?php _e('Click here to purchase now','wp-file-manager-pro');?></a>
				</div>
			</form>
		</div>
	</div>
<?php 
}
?>