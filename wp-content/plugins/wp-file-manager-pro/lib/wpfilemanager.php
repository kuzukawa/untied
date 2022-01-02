<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php $opt = get_option('wp_filemanager_options'); 
$opts = get_option('wp_file_manager_pro');
$preferences = get_option('wp_file_manager_pro_settings');
$fmkey = self::fm_get_key();
$applied_theme_name = isset($opt['theme']) && !empty($opt['theme']) ? str_replace(" ","-",sanitize_text_field($opt['theme'])) : 'default';
?>
<script src="<?php echo plugins_url( 'codemirror/lib/codemirror.js', __FILE__ ); ?>"></script>
<link rel="stylesheet" href="<?php echo plugins_url( 'codemirror/lib/codemirror.css', __FILE__ ); ?>">
<?php if(!empty($opt['code_editor_theme']) && $opt['code_editor_theme'] != 'default'):?>
<link rel="stylesheet" href="<?php echo plugins_url( 'codemirror/theme/'.$opt['code_editor_theme'].'.css', __FILE__ ); ?>">
<?php endif;?>
<script src="<?php echo plugins_url( 'codemirror/mode/htmlmixed/htmlmixed.js', __FILE__ ); ?>"></script>
<script src="<?php echo plugins_url( 'codemirror/mode/xml/xml.js', __FILE__ ); ?>"></script>
<script src="<?php echo plugins_url( 'codemirror/mode/css/css.js', __FILE__ ); ?>"></script>
<script src="<?php echo plugins_url( 'codemirror/mode/javascript/javascript.js', __FILE__ ); ?>"></script>
<script src="<?php echo plugins_url( 'codemirror/mode/clike/clike.js', __FILE__ ); ?>"></script>
<script src="<?php echo plugins_url( 'codemirror/mode/php/php.js', __FILE__ ); ?>"></script>
<?php 
include_once(FILEMANEGERPROPATH.'inc/fm_helper.php');

$css = '';
if(isset($localStorage["file_operations"]) && in_array('info',$localStorage["file_operations"])){
	$css .= addCssStyleForInfo('',$applied_theme_name);
}
if(isset($localStorage["file_operations"]) && in_array('download',$localStorage["file_operations"])){
	$css .= addCssStyleForDownload('',1);
}
if(isset($localStorage["gcloud_file_operations"]) && in_array('info', $localStorage["gcloud_file_operations"])){
	$css .= addCssStyleForInfo('gcloud',$applied_theme_name);
}
if(isset($localStorage["gcloud_file_operations"]) && in_array('download', $localStorage["gcloud_file_operations"])){
	$css .= addCssStyleForDownload('gcloud',0);
}
if(isset($localStorage["digital_ocean_file_operations"]) && in_array('info', $localStorage["digital_ocean_file_operations"])){
	$css .= addCssStyleForInfo('digitalocean',$applied_theme_name);
}
if(isset($localStorage["digital_ocean_file_operations"]) && in_array('download', $localStorage["digital_ocean_file_operations"])){
	$css .= addCssStyleForDownload('digitalocean',0);
}
if(!empty($css)){
	wp_register_style( 'fm-admin-common-css', false );
	wp_enqueue_style( 'fm-admin-common-css' );
	wp_add_inline_style( 'fm-admin-common-css', $css);
}
?>
	<script type="text/javascript" charset="utf-8">
		var filesH = "<?php echo base64_encode($fmkey);?>"; 
	   	var selectedFilesArr = {};

		jQuery(document).ready(function() {
		<?php
		if(empty($opts['ispro']) || empty($opts['serialkey']))
		{ ?>
			setInterval(function(){ alert("<?php  _e('Invalid Licence Key', 'wp-file-manager-pro'); ?>"); }, 5000);
		<?php } ?>		
		var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";

		function checkSyntaxFunction(editor,filename,mime){
			return new Promise(function(resolve, reject) {
				var data = {
					'action': 'mk_check_filemanager_php_syntax',
					'code': editor.getValue(),
					'filename': filename,
					'filemime': mime,
				};
				//syntax checker
				jQuery.post(ajaxurl, data, function(response) {
					if(response == '1') {															
						resolve(true);
					} else {
						reject(response);
					}
				});
			})
			
		}

		function hide_toolbar_btns(toHide){
			if(toHide == 1) {
				jQuery(".elfinder .elfinder-contextmenu .elfinder-contextmenu-item.op-info").attr("style","display:none !important");
			} else {
				jQuery(".elfinder .elfinder-contextmenu .elfinder-contextmenu-item.op-info").attr("style","display:block !important");
			}
		}

		function disable_download(toShow){
			if(toShow == 1){
				jQuery(".elfinder-dialog .ui-dialog-content table.elfinder-info-tb a.share-link").css("pointer-events","auto");
				jQuery(".elfinder-dialog .ui-dialog-content table.elfinder-info-tb a.share-link").css("cursor","pointer");
				jQuery(".elfinder-dialog .ui-dialog-content table.elfinder-info-tb a.share-link").css("text-decoration","underline");
			} else{
				jQuery(".elfinder-dialog .ui-dialog-content table.elfinder-info-tb a.share-link").css("pointer-events","none !important");
				jQuery(".elfinder-dialog .ui-dialog-content table.elfinder-info-tb a.share-link").css("cursor","default");
				jQuery(".elfinder-dialog .ui-dialog-content table.elfinder-info-tb a.share-link").css("text-decoration","none");
			}
		}


		function isAddonLoaded(){
			var adminSelectedDrive = jQuery('.elfinder-path span.elfinder-path-dir').text();
			var isAddon = '';
			if(adminSelectedDrive.indexOf("@gcloud") > -1){
				isAddon = 'gcloud';
			} else if(adminSelectedDrive.indexOf("@ocean") > -1){
				isAddon = 'digitalocean';
			}
			return isAddon;
		}

		var adminSelectedDrive = jQuery('.elfinder-path span.elfinder-path-dir').text();
		var selectedItem = '';
		var uploadNonce = "<?php echo wp_create_nonce( 'wp_file_manager_upload_'.$current_user->ID )?>";
		var downloadNonce = "<?php echo wp_create_nonce( 'wp_file_manager_download_'.$current_user->ID )?>";
		var editNonce = "<?php echo wp_create_nonce( 'wp_file_manager_edit_'.$current_user->ID )?>";
		var operationsArr = {};
		operationsArr["file_operations"] = '<?php echo isset($localStorage["file_operations"]) ? json_encode($localStorage["file_operations"]) : json_encode(array());?>';
		operationsArr["gcloud_file_operations"] = '<?php echo isset($localStorage["gcloud_file_operations"]) ? json_encode($localStorage["gcloud_file_operations"]) : json_encode(array())?>';
		operationsArr["digital_ocean_file_operations"] = '<?php echo isset($localStorage["digital_ocean_file_operations"]) ? json_encode($localStorage["digital_ocean_file_operations"]) : json_encode(array())?>';

		operationsArr["file_operations"] = JSON.parse(operationsArr["file_operations"]);
		operationsArr["gcloud_file_operations"] = JSON.parse(operationsArr["gcloud_file_operations"]);
		operationsArr["digital_ocean_file_operations"] = JSON.parse(operationsArr["digital_ocean_file_operations"]);
				
			var wp_fm_plug_admin = jQuery('#wp_file_manager_admin').elfinder({
				url : ajaxurl+'?action=mk_file_folder_manager',
				<?php if(!empty($opt['fm_max_upload_size'])) {?>
				uploadMaxChunkSize : <?php echo $opt['fm_max_upload_size'] * 10485760 ; ?>,
				<?php } else { ?>
				uploadMaxChunkSize : 1048576000000,
				<?php } ?>
				<?php if(isset($opt['lang']) && !empty($opt['lang'])):
					if($opt['lang'] != 'en') { ?>
					lang: '<?php echo $opt['lang']?>',
				<?php } endif;?>
				<?php if(isset($opt['wp_fm_view']) && !empty($opt['wp_fm_view'])): ?>				
					defaultView: '<?php echo $opt['wp_fm_view']?>',
				<?php endif;?>
					height: 500,
					dateFormat: "j M, Y H:i A",		
				/* Start */
				handlers : {
					select : function(event, elfinderInstance) {
						var selected = event.data.selected;
						
						if(selected.length > 0){
							for (i in selected) {
								var file = elfinderInstance.file(selected[i]);
								selectedItem = file.mime;
								var hasAddon = isAddonLoaded();
								if (hasAddon !== "" && selectedItem == "directory") {
									if(!jQuery(".elfinder.ui-helper-reset").hasClass(hasAddon)){
										jQuery(".elfinder.ui-helper-reset").addClass(hasAddon)
									}
									jQuery(".tool-op-rename").css("display","none");
								} else {
									if(jQuery(".elfinder.ui-helper-reset").hasClass(hasAddon)){
										jQuery(".elfinder.ui-helper-reset").removeClass(hasAddon)
									}

									jQuery(".tool-op-rename").removeAttr("style");

									if(hasAddon !== "" && typeof(file) !== 'undefined') {
										selectedFilesArr[file.name] = elfinderInstance.url(selected[i]);
									}
								}
							}
						}
						},
						<?php 
						if(isset($opt['allow_fullscreen']) && $opt['allow_fullscreen'] == 'yes'){
							?>
							dialogopen : function(e, fm) {
								var dialog = e.data.dialog,
									elfNode;
								if (dialog.hasClass('elfinder-dialog-edit')) {
									elfNode = fm.getUI();
									dialog.css({
										top: 0,
										left: 0,
										width: elfNode.width(),
										height: elfNode.height()
									}).trigger('resize', { init : true });
								}
							},
							<?php
						}
						?>
							dblclick: function(event, elfinderInstance)
							{
								var fileData = elfinderInstance.file(event.data.file);
								var hasAddon = isAddonLoaded();
								if (hasAddon !== "" && selectedItem == "directory") {
									switch(hasAddon) {
										case "gcloud":
											if(operationsArr["gcloud_file_operations"].indexOf('download') > -1) {
												if(fileData.mime != "directory"){
													return false;
												}
											}
										break;
										case "digitalocean":
											if(operationsArr["digital_ocean_file_operations"].indexOf('download') > -1) {
												if(fileData.mime != "directory"){
													return false;
												}
											}
										break;
									}
								} else {
									if(operationsArr["file_operations"].indexOf('download') > -1){
										if(fileData.mime != "directory"){
											return false;
										}
									}
								}
							},
							
							/* Upload */
							upload: function(event, instance) {
								var hasAddon = isAddonLoaded();
								var filepaths = [];
								var fileNames = [];
								var uploadedFiles = event.data.added;
								for (i in uploadedFiles) {
									var file = uploadedFiles[i];
									var fileurl = file.url == 1 ? file.name	: file.url;
									if (hasAddon !== ""){
										filepaths.push(btoa(file.name)+'-m-'+filesH);
									} else {
										filepaths.push(btoa(fileurl)+'-m-'+filesH);
									}
									fileNames.push(file.name);
								}	
								if(filepaths != '') {

									<?php if(isset($opt['allow_upload_notifications']) && $opt['allow_upload_notifications'] == 'yes'):?>
											var data = {
												'action': 'mk_file_folder_manager_uc',
												'uploadedby' : "<?php echo $current_user->ID; ?>",
												'uploadefiles' : filepaths,
												'uploadedFilesNames' : fileNames,
												'uploadNonce' : uploadNonce
											};										
											jQuery.post(ajaxurl, data, function(response) { });
									<?php endif; ?>
									<?php if(isset($preferences['fm_enable_media_upload']) && $preferences['fm_enable_media_upload'] == '1'):?>
										var mdata = {
												'action': 'mk_file_folder_manager_media_upload',		
												'uploadefiles' : filepaths,
												'_wpnonce': '<?php echo wp_create_nonce('wp-file-manager');?>'
											};										
											jQuery.post(ajaxurl, mdata, function(response) {});
									<?php endif; ?>

									}								
							},							



						<?php if(isset($opt['allow_download_notifications']) && $opt['allow_download_notifications'] == 'yes'):?>
								/* Download */
								download: function(event, elfinderInstance) {	
								var downloadFiles = {};
								var downloadfiles = event.data.files;									
								
								for (i in downloadfiles) {
									var filenames = downloadfiles[i];
									var hasAddon = isAddonLoaded();
									if(hasAddon !== ""){
										downloadFiles[filenames.name] = typeof(filenames.name) != 'undefined' ? btoa(filenames.name)+'-m-'+filesH : '-m-'+filesH;
									} else {
										downloadFiles[filenames.name] = selectedFilesArr[filenames.name] ? btoa(selectedFilesArr[filenames.name])+'-m-'+filesH : '-m-'+filesH;
									}
								}	
								if(downloadFiles != '') {
									var data = {
										'action': 'mk_file_folder_manager_dc',
										'downloadedby' : "<?php echo $current_user->ID; ?>",
										'downloadedFiles': JSON.stringify(downloadFiles),
										'downloadNonce' : downloadNonce
									};										
									jQuery.post(ajaxurl, data, function(response) {
									});
								}
							}	
							<?php endif; ?>
						},
					/* END */
				uiOptions: {
					toolbar: <?php echo $localStorage["hide_toolbar"] ? '[]' : '{}';?>,
					toolbarExtra : {
						preferenceInContextmenu: false,
					}
				},
				contextmenu: {
					files: <?php echo $localStorage["hide_context_menu"] ? '[]' : '{}';?>,
					navbar: <?php echo $localStorage["hide_context_menu"] ? '[]' : '{}';?>,
					cwd: <?php echo $localStorage["hide_context_menu"] ? '[]' : '{}';?>,
				},
				commandsOptions: {

					edit : {
					
					mimes : [],
					
					editors : [{
					
					mimes : ['text/plain', 'text/html', 'text/javascript', 'text/css', 'text/x-php', 'application/x-php', 'application/x-php','text/x-c++src','text/x-csrc','text/x-c'],
					
					load : function(textarea) {
					 var mimeType = this.file.mime;
                     var filename = this.file.name;
					    editor = CodeMirror.fromTextArea(textarea, {
							mode: mimeType,
							indentUnit: 4,
							lineNumbers: true,
							<?php if(!empty($opt['code_editor_theme']) && $opt['code_editor_theme'] != 'default'):?>
							theme: "<?php echo $opt['code_editor_theme']; ?>",
							<?php endif; ?>
							viewportMargin: Infinity,
							lineWrapping: true
						});
						<?php if(isset($preferences['fm_syntax_checker']) && !empty($preferences['fm_syntax_checker']) && $preferences['fm_syntax_checker'] == 1) { ?>
								if(mimeType === "text/x-php"){
									jQuery(".ui-dialog-buttonset.elfinder-edit-extras").css("position",'relative').css('bottom','5px');
									jQuery('.elfinder-edit-extras').
											append(
												jQuery('<button title="PHP Syntax Only">Check Syntax</button>')
												.button()
												.removeAttr('class')
												.addClass("ui-button-text check-syntax-cta")
												.on('click', function(){
													jQuery('.fm_msg_popup').fadeIn();
													jQuery('.fm_msg_btn_dv').hide();
													jQuery('.fm_msg_text').html("<span class='check_syntax_loading'>Validating syntax please wait...</span>");
													checkSyntaxFunction(editor,filename,mimeType)
													.then(function(result){
														jQuery('.fm_msg_text').html("<span class='no_syntax_error_found'>No syntax error found. Click save button to save file <strong>"+filename+"</strong></span>");
														jQuery('.fm_msg_btn_dv').show();
													}).catch(function(response){
														jQuery('.fm_msg_text').html(response);
														jQuery('.fm_msg_btn_dv').show();
													});
												})
											);
								}
							<?php } ?>
							return editor;
					},
					
					close : function(textarea, instance) {
						this.myCodeMirror = null;
					},
					
					
					save: function(textarea, editor) {							  
						jQuery(textarea).val(editor.getValue());
						 /* Start */
						<?php if(isset($opt['allow_edit_notifications']) && $opt['allow_edit_notifications'] == 'yes'):?>
							var hasAddon = isAddonLoaded();
							if(hasAddon !== ""){
								var data = {
									'action': 'mk_file_folder_manager_fn',
									'editedby' : "<?php echo $current_user->ID; ?>",
									'file': this.file.name,
									'filePath': btoa(this.file.name)+'-m-'+filesH,
									'editNonce' : editNonce,
								};
							} else {
								var data = {
									'action': 'mk_file_folder_manager_fn',
									'editedby' : "<?php echo $current_user->ID; ?>",
									'file': this.file.name,
									'filePath': selectedFilesArr[this.file.name] ? btoa(selectedFilesArr[this.file.name])+'-m-'+filesH : '-m-'+filesH,
									'editNonce' : editNonce,
								};
							}
							jQuery.post(ajaxurl, data, function(response) {
							});
						<?php endif; ?>					
						/* End */	
							  		  
					}
					
					} ]
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
					
					}
					
					}).elfinder('instance');
				
				wp_fm_plug_admin.bind('contextmenu', function(e){
					var isAddon = isAddonLoaded();
					if (isAddon !== "") {
						switch(isAddon){
							case "gcloud":
								if(operationsArr["gcloud_file_operations"].indexOf('info') > -1){
									setTimeout(function(){
										hide_toolbar_btns(1);
									}, 300);
								} else {
									setTimeout(function(){ hide_toolbar_btns(0); }, 500);
								}
							break;
							case "digitalocean":
								if(operationsArr["digital_ocean_file_operations"].indexOf('info') > -1){
									setTimeout(function(){
										hide_toolbar_btns(1);
									}, 300);
								} else {
									setTimeout(function(){ hide_toolbar_btns(0); }, 500);
								}
							break;
						}
						if(selectedItem == "directory"){
							jQuery(".tool-op-rename").css("display","none");
							setTimeout(function(){
								jQuery(".elfinder-contextmenu .op-rename").css("display","none");
							}, 300);
						}
					} else {
						jQuery(".tool-op-rename").removeAttr("style");
					}
				});

				jQuery(document).on('click','.op-info', function(){
					var isAddon = isAddonLoaded();
					if (isAddon !== "") {
						switch(isAddon){
							case "gcloud":
								if(operationsArr["gcloud_file_operations"].indexOf('download') > -1){
									disable_download(1);
								} else {
									disable_download(0);
								}
							break;
							case "digitalocean":
								if(operationsArr["digital_ocean_file_operations"].indexOf('download') > -1){
									disable_download(1);
								} else {
									disable_download(0);
								}
							break;
						}
					}
				});
			});
		</script>
<div class="wrap">
<h2><img src="<?php echo plugins_url( 'images/wp_file_manager.svg', dirname(__FILE__) ); ?>" style="width: 33px;vertical-align: text-top;"><?php  _e(' WP File Manager PRO - '.mk_file_folder_manager::FILE_MANAGER_VERSION.'', 'wp-file-manager-pro'); ?> <?php if(current_user_can('manage_options')) { $this->orderdetails(); } ?></h2>
<div class="theme-<?php echo $applied_theme_name;?>"><div id="wp_file_manager_admin"></div></div>
</div>
<div class="fm_msg_popup">
    <div class="fm_msg_popup_tbl">
        <div class="fm_msg_popup_cell">
            <div class="fm_msg_popup_inner">
            <div class="fm_msg_text">
            	Saving...
            </div>
            <div class="fm_msg_btn_dv"><a href="javascript:void(0)" class="fm_close_msg button button-primary">OK</a></div>
            </div>
        </div>
    </div>
</div>

<style>
.fm_msg_popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 9999;
            background: rgba(0, 0, 0, 0.7);
        }

        .fm_msg_popup .fm_msg_popup_tbl {
            display: table;
            width: 100%;
            height: 100%;
        }

        .fm_msg_popup .fm_msg_popup_tbl .fm_msg_popup_cell {
            display: table-cell;
            vertical-align: middle;
        }

        .fm_msg_popup .fm_msg_popup_tbl .fm_msg_popup_cell .fm_msg_popup_inner {
            max-width: 400px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            text-align: center;
            border-radius: 5px;
            -webkit-border-radius: 5px;
            box-shadow: 10px 10px 5px rgba(0, 0, 0, 0.4);
        }

        .fm_msg_popup .fm_msg_popup_tbl .fm_msg_popup_cell .fm_msg_popup_inner .fm_msg_text {
            margin-bottom: 25px;
            font-size: 15px;
            color: #ff2400;
        }
       .fm_msg_btn_dv 
       {
           display: none;
       }
        .fm_msg_popup .fm_msg_popup_tbl .fm_msg_popup_cell .fm_msg_popup_inner .fm_msg_btn_dv a {

            padding: 0px 30px;
        }
        .check_syntax_loading {
            color: #000;            
        }
        .no_syntax_error_found {
            color: #076b34;
        }
        button.ui-button-text.check-syntax-cta {
            padding: 4px 4px !important;
            border-radius: 4px;
            outline: 0px;
            background: #3077ac;
            position: relative;
            top: 5px;
            color:#fff;
            border:0;
        }
        button.ui-button-text.check-syntax-cta:hover {
         background: #1f5884;
         border:0px;
        }
        #wp_file_manager .ui-dialog.elfinder-to-editing.elfinder-dialog-edit{
         z-index:10 !important;
        }
</style>

<script>
jQuery(document).ready(function(e) {
    jQuery('.fm_close_msg').click(function(e) {
        jQuery('.fm_msg_popup').fadeOut();
    });
	// mac
	if (navigator.userAgent.indexOf('Mac OS X') != -1) {			
    jQuery("body").addClass("mac");
    } else {			
    jQuery("body").addClass("windows");
   }
});
</script>