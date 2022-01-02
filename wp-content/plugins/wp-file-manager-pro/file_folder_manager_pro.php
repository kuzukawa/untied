<?php 
/**
  Plugin Name: WP File Manager - PRO
  Plugin URI: https://filemanagerpro.io/product/file-manager/
  Description: Manage your WP files.
  Author: mndpsingh287
  Version: 8.3
  Author URI: https://profiles.wordpress.org/mndpsingh287
  License: GPLv2
 **/
if (!defined('FILEMANEGERPROPATH')) {
    define('FILEMANEGERPROPATH', plugin_dir_path(__FILE__));
}
if (!defined('FILEMANAGERPROURL')) {
    define('FILEMANAGERPROURL', plugins_url('', __FILE__));
}
if (!defined('fm_file_path')):
    define('fm_file_path', dirname(__FILE__));
  endif;
if (!defined('fm_plugin_url')):
    define('fm_plugin_url', plugins_url('wp-file-manager-pro'));
endif;
if (!class_exists('mk_file_folder_manager')):
    class mk_file_folder_manager
    {

        const FILE_MANAGER_VERSION = '8.3';

        /* Auto Load Hooks */
        public function __construct()
        {
            $opt = get_option('wp_file_manager_pro');
            $folder = basename(dirname(__FILE__));
            $file = basename(__FILE__);
            add_action('init', array(&$this, 'check_fm_updates'));
            add_action('admin_menu', array(&$this, 'ffm_menu_page'));
			add_action('admin_footer', array(&$this, 'ffm_admin_things'));
            add_action('admin_enqueue_scripts', array(&$this, 'ffm_admin_script'));		
            add_action('wp_ajax_mk_file_folder_manager', array(&$this, 'mk_file_folder_manager_action_callback'));
            add_action('wp_ajax_mk_file_folder_manager_shortcode', array(&$this, 'mk_file_folder_manager_action_callback_shortcode'));
            add_action('wp_ajax_nopriv_mk_file_folder_manager_shortcode', array(&$this, 'mk_file_folder_manager_action_callback_shortcode'));
            add_shortcode('wp_file_manager', array(&$this, 'wp_file_manager_front_view'));
            add_shortcode('wp_file_manager_admin', array(&$this, 'wp_file_manager_front_view_admin_control'));
            if (isset($opt['ispro']) && !empty($opt['ispro']) && $opt['ispro'] == 'yes') {
                $this->load_packed_extensions();
                do_action('load_filemanager_extensions');
            }
            add_action('plugins_loaded', array(&$this, 'filemanager_pro_load_text_domain'));
            if ($this->allow_shortocode_btn()) {
                add_action('media_buttons', array(
                        $this,
                        'filemanager_pro_btn',
                     ));
            }
            /* New Feature */
            add_action('init', array(&$this, 'create_auto_directory'));
            // Install vendor
            add_action('wp_ajax_mk_file_folder_manager_install_vendor',
            array(&$this, 'mk_file_folder_manager_install_vendor_callback'));
            // php syntax
            add_action('wp_ajax_mk_check_filemanager_php_syntax', array(&$this, 'mk_check_filemanager_php_syntax_callback'));
            add_action('wp_ajax_nopriv_mk_check_filemanager_php_syntax', array(&$this, 'mk_check_filemanager_php_syntax_callback'));
            add_action('admin_init', array(&$this, 'remove_fm_temp_file'));
             /*
            Media Upload
            */
            add_action('wp_ajax_mk_file_folder_manager_media_upload', array(&$this, 'mk_file_folder_manager_media_upload'));
             /* Backup - Feature - Since 6.0 */
             add_action('wp_ajax_mk_file_manager_pro_backup', array(&$this, 'mk_file_manager_pro_backup_callback'));
             add_action('wp_ajax_mk_file_manager_pro_backup_remove', array(&$this, 'mk_file_manager_pro_backup_remove_callback'));
             add_action('wp_ajax_mk_file_manager_pro_single_backup_remove', array(&$this, 'mk_file_manager_pro_single_backup_remove_callback'));
             add_action('wp_ajax_mk_file_manager_pro_single_backup_logs', array(&$this, 'mk_file_manager_pro_single_backup_logs_callback'));
             add_action('wp_ajax_mk_file_manager_pro_single_backup_restore', array(&$this, 'mk_file_manager_pro_single_backup_restore_callback'));
             add_action('wp_ajax_mk_file_manager_pro_logs_remove', array(&$this, 'mk_file_manager_pro_logs_remove_callback'));
            
            
            // This hook is used for creating shortcode table
            add_action('admin_init', array(&$this, 'fm_create_shortcode_tbl'));
            add_action('wp_enqueue_scripts', array(&$this, 'fm_frontend_script'));
            add_action( 'rest_api_init', function () {
                if(is_user_logged_in()){
                    register_rest_route( 'v1', '/fm/backup/(?P<backup_id>[a-zA-Z0-9-=]+)/(?P<type>[a-zA-Z0-9-=]+)/(?P<key>[a-zA-Z0-9-=]+)', array(
                        'methods' => 'GET',
                        'callback' => array( $this, 'fm_download_backup' ),
                        'permission_callback' => '__return_true',
                    ));
                }
                register_rest_route( 'v1', '/fm/backupall/(?P<backup_id>[a-zA-Z0-9-=]+)/(?P<type>[a-zA-Z0-9-=]+)/(?P<key>[a-zA-Z0-9-=]+)/(?P<all>[a-zA-Z]+)', array(
                    'methods' => 'GET',
                    'callback' => array( $this, 'fm_download_backup_all' ),
                    'permission_callback' => '__return_true',
                ));
                
                register_rest_route( 'v1', '/fm/cancel', array(
				    'methods' => 'POST',
                    'callback' => array( $this, 'd_l_callback' ),
                    'permission_callback' => '__return_true',
                ));
            });

            // Load Includes
            $this->loadIncludes();
        }

        public function fm_frontend_script(){
            wp_enqueue_script('jquery');
        }

        /* Create Table -> Logs */
        public static function wp_fm_log_table() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            $create_table_query = "
                CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}fm_file_action_log` (
                id int(11) NOT NULL AUTO_INCREMENT,
                uid int(11) DEFAULT NULL,
                uname text NULL,
                action text NULL,
                files text NULL,
                log_date text NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";
        
            $backup_tbl = "
                CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpfm_backup` (
                id int(11) NOT NULL AUTO_INCREMENT,
                backup_name text NULL,
                backup_date text NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";

            $shortcode_tbl = "
                CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpfm_shortcodes` (
                id int(11) NOT NULL AUTO_INCREMENT,
                shotcode_key text NULL,
                shotcode_title text NULL,
                type varchar(20) NULL,
                attributes text NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $create_table_query );
            dbDelta( $backup_tbl );
            dbDelta( $shortcode_tbl );
        }

        /**
         * Creating table on page load
         */

        public function fm_create_shortcode_tbl(){
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            if ( $wpdb->query( "SHOW TABLES LIKE '" . $wpdb->prefix . "wpfm_shortcodes'" ) == 0 ) {
                $shortcode_tbl = "
                    CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpfm_shortcodes` (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    shotcode_key text NULL,
                    shotcode_title text NULL,
                    type varchar(20) NULL,
                    attributes text NULL,
                    PRIMARY KEY  (id)
                ) $charset_collate;";

                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                dbDelta( $shortcode_tbl );
            }
        }

        /*
        Backup - Restore
        */

        public function mk_file_manager_pro_single_backup_restore_callback() {
            WP_Filesystem(); 
            global $wp_filesystem;
            $nonce = $_POST['nonce'];
            if(current_user_can('manage_options') && wp_verify_nonce( $nonce, 'wpfmbackuprestore' )) {
                global $wpdb;
                $fmdb = $wpdb->prefix.'wpfm_backup';
                $upload_dir = wp_upload_dir();
                $backup_dirname = $upload_dir['basedir'].'/wp-file-manager-pro/fm_backup/';
                $bkpid = (int) $_POST['id'];
                $result = array();
                $filesDestination = ABSPATH . 'wp-content/';
                if ( strcmp($backup_dirname, "/") === 0 ) {
                    $backup_path = $backup_dirname;
                }else{
                    $backup_path = $backup_dirname."/";

                }
                $database = sanitize_text_field($_POST['database']);
                $plugins = sanitize_text_field($_POST['plugins']);
                $themes = sanitize_text_field($_POST['themes']);
                $uploads = sanitize_text_field($_POST['uploads']);
                $others = sanitize_text_field($_POST['others']);
                if($bkpid) {
                    include('classes/files-restore.php');
                    $restoreFiles = new wp_file_manager_files_restore();
                    $fmbkp = $wpdb->get_row('select * from '.$fmdb.' where id = "'.$bkpid.'"');
                    if($themes == 'true') {
                        // case 1 - Themes
                        if(file_exists($backup_dirname.$fmbkp->backup_name.'-themes.zip')) {
                            $wp_filesystem->delete($filesDestination.'themes',true);
                            $restoreThemes = $restoreFiles->extract($backup_dirname.$fmbkp->backup_name.'-themes.zip',$filesDestination.'themes');
                            if($restoreThemes) {
                                echo json_encode(array('step' => 1, 'database' => $database,'plugins' => $plugins,'themes' => 'false', 'uploads'=> $uploads, 'others' => $others,'bkpid' => $bkpid,'msg' => __('<li class="fm-running-list fm-custom-checked">Themes backup restored successfully.</li>', 'wp-file-manager')));  
                                die;
                            } else {
                                echo json_encode(array('step' => 1, 'database' => $database,'plugins' => $plugins,'themes' => 'false', 'uploads'=> $uploads, 'others' => $others,'bkpid' => $bkpid,'msg' => __('<li class="fm-running-list fm-custom-unchecked">Unable to restore themes.</li>', 'wp-file-manager')));   
                                die;
                            }            
                        }else {
                            echo json_encode(array('step' => 1, 'database' => $database,'plugins' => $plugins,'themes' => 'false', 'uploads'=> $uploads, 'others' => $others,'bkpid' => $bkpid,'msg' => ''));   
                            die;
                        }   
                    } 
                    else if($uploads == 'true'){
                    // case 2 - Uploads
                        if(file_exists($backup_dirname.$fmbkp->backup_name.'-uploads.zip')) {
                            $alllist = $wp_filesystem->dirlist($filesDestination.'uploads');
                            if(is_array($alllist) && !empty($alllist))
                            {
                                foreach($alllist as $key=>$value)
                                {
                                    if($key!= 'wp-file-manager-pro')
                                    {
                                        $wp_filesystem->delete($filesDestination.'uploads/'.$key,true);
                                    }
                                }
                            }

                            $restoreUploads = $restoreFiles->extract($backup_dirname.$fmbkp->backup_name.'-uploads.zip',$filesDestination.'uploads');
                        if($restoreUploads) {
                            echo json_encode(array('step' => 1, 'database' => $database,'plugins' => $plugins,'themes' => $themes, 'uploads'=> 'false', 'others' => $others,'bkpid' => $bkpid,'msg' => __('<li class="fm-running-list fm-custom-checked">Uploads backup restored successfully.</li>', 'wp-file-manager')));  
                                die;
                        
                            } else {
                                echo json_encode(array('step' => 1, 'database' => $database,'plugins' => $plugins,'themes' => $themes, 'uploads'=> 'false', 'others' => $others,'bkpid' => $bkpid,'msg' => __('<li class="fm-running-list fm-custom-unchecked">Unable to restore uploads.</li>', 'wp-file-manager'))); 
                                die;
                        
                            }                    
                        }else {
                            echo json_encode(array('step' => 1, 'database' => $database,'plugins' => $plugins,'themes' => $themes, 'uploads'=> 'false', 'others' => $others,'bkpid' => $bkpid,'msg' => '')); 
                            die;
                    
                        }   
                    }
                    else if($others == 'true'){
                    // case 3 - Others
                        if(file_exists($backup_dirname.$fmbkp->backup_name.'-others.zip')) {
                            $alllist = $wp_filesystem->dirlist($filesDestination);
                            if(is_array($alllist) && !empty($alllist))
                            {
                                foreach($alllist as $key=>$value)
                                {
                                    if($key != 'themes' && $key != 'uploads' && $key != 'plugins')
                                    {
                                        $wp_filesystem->delete($filesDestination.$key,true);
                                    }
                                }
                            }
                            $restoreOthers = $restoreFiles->extract($backup_dirname.$fmbkp->backup_name.'-others.zip',$filesDestination);
                            if($restoreOthers) {
                                echo json_encode(array('step' => 1, 'database' => $database,'plugins' => $plugins,'themes' => $themes, 'uploads'=> $uploads, 'others' => 'false','bkpid' => $bkpid,'msg' => __('<li class="fm-running-list fm-custom-checked">Others backup restored successfully.</li>', 'wp-file-manager'))); 
                                die;
                        
                            } else {
                                echo json_encode(array('step' => 1, 'database' => $database,'plugins' => $plugins,'themes' => $themes, 'uploads'=> $uploads, 'others' => 'false','bkpid' => $bkpid,'msg' => __('<li class="fm-running-list fm-custom-unchecked">Unable to restore others.</li>', 'wp-file-manager'))); 
                                die;
                        
                            }                  
                        }else {
                            echo json_encode(array('step' => 1, 'database' => $database,'plugins' => $plugins,'themes' => $themes, 'uploads'=> $uploads, 'others' => 'false','bkpid' => $bkpid,'msg' => '')); 
                            die;
                    
                        }     

                    }
                    else if($plugins == 'true'){
                    // case 4- Plugins
                        if(file_exists($backup_path.$fmbkp->backup_name.'-plugins.zip')) {
                            $alllist = $wp_filesystem->dirlist($filesDestination.'plugins');
                            if(is_array($alllist) && !empty($alllist))
                            {
                                foreach($alllist as $key=>$value)
                                {
                                    if($key!= 'wp-file-manager-pro')
                                    {
                                        $wp_filesystem->delete($filesDestination.'plugins/'.$key,true);
                                    }
                                }
                            }

                            $restorePlugins = $restoreFiles->extract($backup_path.$fmbkp->backup_name.'-plugins.zip',$filesDestination.'plugins');
                            if($restorePlugins) {
                                echo json_encode(array('step' => 1, 'database' => $database,'plugins' => 'false','themes' => $themes, 'uploads'=> $uploads, 'others' => $others,'bkpid' => $bkpid,'msg' => __('<li class="fm-running-list fm-custom-checked">Plugins backup restored successfully.</li>', 'wp-file-manager')));  
                                die;
                    
                            } else {
                                echo json_encode(array('step' => 1, 'database' => $database,'plugins' => 'false','themes' => $themes, 'uploads'=> $uploads, 'others' => $others,'bkpid' => $bkpid,'msg' => __('<li class="fm-running-list fm-custom-unchecked">Unable to restore plugins.</li>', 'wp-file-manager'))); 
                                die;
                        
                            }                                      
                        }else {
                            echo json_encode(array('step' => 1, 'database' => $database,'plugins' => 'false','themes' => $themes, 'uploads'=> $uploads, 'others' => $others,'bkpid' => 0,'msg' => '')); 
                            die;
                    
                        }   
                    } 
                    else if($database == 'true'){
                        // case 5- Database
                        if(file_exists($backup_dirname.$fmbkp->backup_name.'-db.sql.gz')) {    
                                    
                            include('classes/db-restore.php');
                            $restoreDatabase = new Restore_Database($fmbkp->backup_name.'-db.sql.gz');
                            if($restoreDatabase->restoreDb()) {
                                echo json_encode(array('step' => 0, 'database' => 'false','plugins' => $plugins,'themes' => $themes, 'uploads'=> $uploads, 'others' => $others,'bkpid' => '','msg' => __('<li class="fm-running-list fm-custom-checked">Database backup restored successfully.</li>', 'wp-file-manager'),  'msgg' => __('<li class="fm-running-list fm-custom-checked">All Done</li>', 'wp-file-manager'))); 
                                die;
                            
                            } else {
                                echo json_encode(array('step' => 0, 'database' => 'false','plugins' => $plugins,'themes' => $themes, 'uploads'=> $uploads, 'others' => $others,'bkpid' => $bkpid,'msg' => __('<li class="fm-running-list fm-custom-unchecked">Unable to restore DB backup.</li>', 'wp-file-manager')));  
                                die;
                            }
                        }else {
                            echo json_encode(array('step' => 1, 'database' => 'false','plugins' => $plugins,'themes' => $themes, 'uploads'=> $uploads, 'others' => $others,'bkpid' => $bkpid,'msg' => ''));  
                            die;
                    
                        }  
                    }else {
                        echo json_encode(array('step' => 0, 'database' => 'false','plugins' => 'false','themes' => 'false','uploads'=> 'false','others' => 'false', 'bkpid' => '', 'msg' => __('<li class="fm-running-list fm-custom-checked">All Done</li>', 'wp-file-manager')));                        
                        die;
                    }
                } else {
                        echo json_encode(array('step' => 0, 'database' => 'false','plugins' => 'false','themes' => 'false', 'uploads'=> 'false', 'others' => 'false','bkpid' => '','msg' => __('<li class="fm-running-list fm-custom-unchecked">Unable to restore plugins.</li>', 'wp-file-manager')));
                        die;
                    
                }
                die;
            }
        }
        /*
        Backup - Remove
        */
        public function mk_file_manager_pro_backup_remove_callback(){
            $nonce = $_POST['nonce'];
            if(current_user_can('manage_options') && wp_verify_nonce( $nonce, 'wpfmbackupremove' )) {
            global $wpdb;
            $fmdb = $wpdb->prefix.'wpfm_backup';
            $upload_dir = wp_upload_dir();
            $backup_dirname = $upload_dir['basedir'].'/wp-file-manager-pro/fm_backup/';
            $bkpRids = $_POST['delarr'];
            $isRemoved = false;        
            if(isset($bkpRids)) {
                foreach($bkpRids as $bkRid) {
                    $fmbkp = $wpdb->get_row('select * from '.$fmdb.' where id = "'.$bkRid.'"');
                    if(file_exists($backup_dirname.$fmbkp->backup_name.'-db.sql.gz')) {
                        unlink($backup_dirname.$fmbkp->backup_name.'-db.sql.gz');
                    }
                    if(file_exists($backup_dirname.$fmbkp->backup_name.'-others.zip')) {
                        unlink($backup_dirname.$fmbkp->backup_name.'-others.zip');
                    }
                    if(file_exists($backup_dirname.$fmbkp->backup_name.'-plugins.zip')) {
                        unlink($backup_dirname.$fmbkp->backup_name.'-plugins.zip');
                    }
                    if(file_exists($backup_dirname.$fmbkp->backup_name.'-themes.zip')) {
                        unlink($backup_dirname.$fmbkp->backup_name.'-themes.zip');
                    }
                    if(file_exists($backup_dirname.$fmbkp->backup_name.'-uploads.zip')) {
                        unlink($backup_dirname.$fmbkp->backup_name.'-uploads.zip');
                    }
                    // removing from db
                    $wpdb->delete($fmdb, array('id' => $bkRid));
                    $isRemoved = true;
                }
            }
            if($isRemoved) {
                echo __('Backup(s) removed successfully!', 'wp-file-manager-pro');
            } else {
                echo __('Unable to remove backup(s)!', 'wp-file-manager-pro'); 
            }
            die;
        }
        }        
        
        /*
        Backup Logs
        */
        public function mk_file_manager_pro_single_backup_logs_callback() {
            $nonce = $_POST['nonce'];
            if(current_user_can('manage_options') && wp_verify_nonce( $nonce, 'wpfmbackuplogs' )) {
            global $wpdb;
            $fmdb = $wpdb->prefix.'wpfm_backup';
            $upload_dir = wp_upload_dir();
            $backup_dirname = $upload_dir['basedir'].'/wp-file-manager-pro/fm_backup/';
            $bkpId = (int) $_POST['id'];
            $logs = array(); 
            $logMessage = '';       
            if(isset($bkpId)) {
                    $fmbkp = $wpdb->get_row('select * from '.$fmdb.' where id = "'.$bkpId.'"');
                    if(file_exists($backup_dirname.$fmbkp->backup_name.'-db.sql.gz')) {
                        $size = filesize($backup_dirname.$fmbkp->backup_name.'-db.sql.gz');
                        $logs[] = __('Database backup done on date', 'wp-file-manager-pro').' '.date('j M, Y H:i A', strtotime($fmbkp->backup_date)).' ('.$fmbkp->backup_name.'-db.sql.gz) ('.$this->formatSizeUnits($size).')';
                    }                    
                    if(file_exists($backup_dirname.$fmbkp->backup_name.'-plugins.zip')) {
                        $size = filesize($backup_dirname.$fmbkp->backup_name.'-plugins.zip');
                        $logs[] = __('Plugins backup done on date', 'wp-file-manager-pro').' '.date('j M, Y H:i A', strtotime($fmbkp->backup_date)).' ('.$fmbkp->backup_name.'-plugins.zip) ('.$this->formatSizeUnits($size).')';
                    }
                    if(file_exists($backup_dirname.$fmbkp->backup_name.'-themes.zip')) {
                        $size = filesize($backup_dirname.$fmbkp->backup_name.'-themes.zip');
                        $logs[] = __('Themes backup done on date', 'wp-file-manager-pro').' '.date('j M, Y H:i A', strtotime($fmbkp->backup_date)).' ('.$fmbkp->backup_name.'-themes.zip) ('.$this->formatSizeUnits($size).')';
                    }
                    if(file_exists($backup_dirname.$fmbkp->backup_name.'-uploads.zip')) {
                        $size = filesize($backup_dirname.$fmbkp->backup_name.'-uploads.zip');
                        $logs[] = __('Uploads backup done on date', 'wp-file-manager-pro').' '.date('j M, Y H:i A', strtotime($fmbkp->backup_date)).' ('.$fmbkp->backup_name.'-uploads.zip) ('.$this->formatSizeUnits($size).')';
                    }
                    if(file_exists($backup_dirname.$fmbkp->backup_name.'-others.zip')) {
                        $size = filesize($backup_dirname.$fmbkp->backup_name.'-others.zip');
                        $logs[] = __('Others backup done on date', 'wp-file-manager-pro').' '.date('j M, Y H:i A', strtotime($fmbkp->backup_date)).' ('.$fmbkp->backup_name.'-others.zip) ('.$this->formatSizeUnits($size).')';
                    }
            }
            $count = 1;
            $logMessage = '<h3 class="fm_console_success log_msg_align_center">'.__('Logs', 'wp-file-manager-pro').'</h3>';
            if(isset($logs)) {
                foreach($logs as $log) {
                    $logMessage .= '<p class="fm_console_success">('.$count++.') '.$log.'</p>';
                }
            } else {
                $logMessage .= '<p class="fm_console_error">'.__('No logs found!', 'wp-file-manager-pro').'</p>';
            }
            echo $logMessage;
            die; 
         }
        }
       /*
       Returning Valid Format
       */
        public function formatSizeUnits($bytes) {
            if ($bytes >= 1073741824)
            {
                $bytes = number_format($bytes / 1073741824, 2) . ' GB';
            }
            elseif ($bytes >= 1048576)
            {
                $bytes = number_format($bytes / 1048576, 2) . ' MB';
            }
            elseif ($bytes >= 1024)
            {
                $bytes = number_format($bytes / 1024, 2) . ' KB';
            }
            elseif ($bytes > 1)
            {
                $bytes = $bytes . ' bytes';
            }
            elseif ($bytes == 1)
            {
                $bytes = $bytes . ' byte';
            }
            else
            {
                $bytes = '0 bytes';
            }

            return $bytes;
        }
        /*
        Backup - Remove
        */
        public function mk_file_manager_pro_single_backup_remove_callback(){
            $nonce = $_POST['nonce'];
            if(current_user_can('manage_options') && wp_verify_nonce( $nonce, 'wpfmbackupremove' )) {
            global $wpdb;
            $fmdb = $wpdb->prefix.'wpfm_backup';
            $upload_dir = wp_upload_dir();
            $backup_dirname = $upload_dir['basedir'].'/wp-file-manager-pro/fm_backup/';
            $bkpId = (int) $_POST['id'];
            $isRemoved = false;        
            if(isset($bkpId)) {
                    $fmbkp = $wpdb->get_row('select * from '.$fmdb.' where id = "'.$bkpId.'"');
                    if(file_exists($backup_dirname.$fmbkp->backup_name.'-db.sql.gz')) {
                        unlink($backup_dirname.$fmbkp->backup_name.'-db.sql.gz');
                    }
                    if(file_exists($backup_dirname.$fmbkp->backup_name.'-others.zip')) {
                        unlink($backup_dirname.$fmbkp->backup_name.'-others.zip');
                    }
                    if(file_exists($backup_dirname.$fmbkp->backup_name.'-plugins.zip')) {
                        unlink($backup_dirname.$fmbkp->backup_name.'-plugins.zip');
                    }
                    if(file_exists($backup_dirname.$fmbkp->backup_name.'-themes.zip')) {
                        unlink($backup_dirname.$fmbkp->backup_name.'-themes.zip');
                    }
                    if(file_exists($backup_dirname.$fmbkp->backup_name.'-uploads.zip')) {
                        unlink($backup_dirname.$fmbkp->backup_name.'-uploads.zip');
                    }
                    // removing from db
                    $wpdb->delete($fmdb, array('id' => $bkpId));
                    $isRemoved = true;
            }
            if($isRemoved) {
                echo __('Backup removed successfully!', 'wp-file-manager-pro');
            } else {
                echo __('Unable to removed backup!', 'wp-file-manager-pro'); 
            }
            die;
         }
        }
        /*
        Backup - Ajax - Feature
        */
        public function mk_file_manager_pro_backup_callback(){
            global $wpdb;
            $fmdb = $wpdb->prefix.'wpfm_backup';
            $date = date('Y-m-d H:i:s');
            $file_number = 'backup_'.date('Y_m_d_H_i_s-').rand(0,9999);
            $nonce = $_POST['nonce'];
            //$type = sanitize_text_field($_POST['type']);
            $database = sanitize_text_field($_POST['database']);
            $files = sanitize_text_field($_POST['files']);
            $plugins = sanitize_text_field($_POST['plugins']);
            $themes = sanitize_text_field($_POST['themes']);
            $uploads = sanitize_text_field($_POST['uploads']);
            $others = sanitize_text_field($_POST['others']);
            $bkpid = isset($_POST['bkpid']) ? $_POST['bkpid'] : '';
            if($database == 'false' && $files == 'false' && $bkpid == '') {
                echo json_encode(array('step' => '0', 'database' => 'false','files' => 'false','plugins' => 'false','themes' => 'false', 'uploads'=> 'false', 'others' => 'false', 'bkpid' => '0', 'msg' => '<li class="fm-running-list fm-custom-unchecked">'.__('Nothing selected for backup', 'wp-file-manager-pro').'</li>'));
                die; 
            }
            if($bkpid == '') {
                $wpdb->insert( 
                    $fmdb, 
                    array( 
                        'backup_name' => $file_number, 
                        'backup_date' => $date
                    ), 
                    array( 
                        '%s', 
                        '%s' 
                    ) 
                );
                $id = $wpdb->insert_id;
            } else {
                $id = $bkpid;
            }
            if ( ! wp_verify_nonce( $nonce, 'wpfmbackup' ) ) {
                echo json_encode(array('step' => 0, 'msg' => '<li class="fm-running-list fm-custom-unchecked">'.__('Security Issue.', 'wp-file-manager-pro').'</li>'));
            } else {
              $fileName = $wpdb->get_row("select * from ".$fmdb." where id='".$id."'");              
                //database
                if($database == 'true') {
                    include('classes/db-backup.php'); 
                    $backupDatabase = new Backup_Database($fileName->backup_name);
                    $result = $backupDatabase->backupTables(TABLES);
                    if($result == '1'){
                        echo json_encode(array('step' => 1, 'database' => 'false','files' => $files,'plugins' => $plugins,'themes' => $themes, 'uploads'=> $uploads, 'others' => $others,'bkpid' => $id,'msg' => '<li class="fm-running-list fm-custom-checked">'.__('Database backup done.', 'wp-file-manager-pro').'</li>'));  
                        die;
                    } else {
                        echo json_encode(array('step' => 1, 'database' => 'false','files' => $files,'plugins' => $plugins,'themes' => $themes, 'uploads'=> $uploads, 'others' => $others,'bkpid' => $id, 'msg' => '<li class="fm-running-list fm-custom-unchecked">'.__('Unable to create database backup.', 'wp-file-manager-pro').'</li>'));   
                        die;
                    }                   
                }
                else if($files == 'true') {
                    include('classes/files-backup.php');
                    $upload_dir = wp_upload_dir();
                    $backup_dirname = $upload_dir['basedir'].'/wp-file-manager-pro/fm_backup';
                    $filesBackup = new wp_file_manager_files_backup();
                     // plugins
                     if($plugins == 'true') {
                        $plugin_dir = ABSPATH . 'wp-content/plugins';                    
                        $backup_plugins = $filesBackup->zipData( $plugin_dir,$backup_dirname.'/'.$fileName->backup_name.'-plugins.zip');
                        if($backup_plugins) {
                            echo json_encode(array('step' => 1, 'database' => 'false','files' => 'true','plugins' => 'false','themes' => $themes, 'uploads'=> $uploads, 'others' => $others,'bkpid' => $id, 'msg' => '<li class="fm-running-list fm-custom-checked">'.__('Plugins backup done.', 'wp-file-manager-pro').'</li>'));
                            die;
                        } else {
                            echo json_encode(array('step' => 1, 'database' => 'false','files' => 'true','plugins' => 'false','themes' => $themes, 'uploads'=> $uploads, 'others' => $others, 'bkpid' => $id, 'msg' => '<li class="fm-running-list fm-custom-unchecked">'.__('Plugins backup failed.', 'wp-file-manager-pro').'</li>')); 
                            die;
                        }
                     } 
                     // themes
                     else if($themes == 'true') {
                        $themes_dir = ABSPATH . 'wp-content/themes';
                        $backup_themes = $filesBackup->zipData( $themes_dir,$backup_dirname.'/'.$fileName->backup_name.'-themes.zip');
                        if($backup_themes) {
                            echo json_encode(array('step' => 1, 'database' => 'false','files' => 'true','plugins' => 'false','themes' => 'false', 'uploads'=> $uploads, 'others' => $others, 'bkpid' => $id, 'msg' => '<li class="fm-running-list fm-custom-checked">'.__('Themes backup done.', 'wp-file-manager-pro').'</li>'));
                            die;
                        } else {
                            echo json_encode(array('step' => 1, 'database' => 'false','files' => 'true','plugins' => 'false','themes' => $themes, 'uploads'=> $uploads, 'others' => $others, 'bkpid' => $id, 'msg' => '<li class="fm-running-list fm-custom-unchecked">'.__('Themes backup failed.', 'wp-file-manager-pro').'</li>')); 
                            die;
                        }
                     }
                     // uploads
                     else if($uploads == 'true') {
                        $uploads_dir = ABSPATH . 'wp-content/uploads';
                        $backup_uploads = $filesBackup->zipData( $uploads_dir,$backup_dirname.'/'.$fileName->backup_name.'-uploads.zip');
                        if($backup_uploads) {
                            echo json_encode(array('step' => 1, 'database' => 'false','files' => 'true','plugins' => 'false','themes' => 'false', 'uploads'=> 'false', 'others' => $others, 'bkpid' => $id, 'msg' => '<li class="fm-running-list fm-custom-checked">'.__('Uploads backup done.', 'wp-file-manager-pro').'</li>'));
                            die;
                        } else {
                            echo json_encode(array('step' => 1, 'database' => 'false','files' => 'true','plugins' => 'false','themes' => 'false', 'uploads'=> 'false', 'others' => $others, 'bkpid' => $id, 'msg' => '<li class="fm-running-list fm-custom-unchecked">'.__('Uploads backup failed.', 'wp-file-manager-pro').'</li>'));
                            die;
                        }
                     } 
                     // other
                     else if($others == 'true') {
                        $others_dir = ABSPATH . 'wp-content';
                        $backup_others = $filesBackup->zipOther( $others_dir,$backup_dirname.'/'.$fileName->backup_name.'-others.zip');
                        if($backup_others) {
                            echo json_encode(array('step' => 1, 'database' => 'false','files' => 'true','plugins' => 'false','themes' => 'false', 'uploads'=> 'false', 'others' => 'false', 'bkpid' => $id, 'msg' => '<li class="fm-running-list fm-custom-checked">'.__('Others backup done.', 'wp-file-manager-pro').'</li>'));
                            die; 
                        } else {
                            echo json_encode(array('step' => 1, 'database' => 'false','files' => 'true','plugins' => 'false','themes' => 'false', 'uploads'=> 'false', 'others' => 'false', 'bkpid' => $id, 'msg' => '<li class="fm-running-list fm-custom-unchecked">'.__('Others backup failed.', 'wp-file-manager-pro').'</li>'));
                            
                        }                        
                     } else {
                        echo json_encode(array('step' => 0, 'database' => 'false', 'files' => 'false','plugins' => 'false','themes' => 'false','uploads'=> 'false','others' => 'false', 'bkpid' => $id, 'msg' => '<li class="fm-running-list fm-custom-checked">'.__('All done', 'wp-file-manager-pro').'</li>'));                        
                        die;
                     }
                } else {
                 echo json_encode(array('step' => 0, 'database' => 'false', 'files' => 'false','plugins' => 'false','themes' => 'false','uploads'=> 'false','others' => 'false','bkpid' => $id, 'msg' => '<li class="fm-running-list fm-custom-checked">'.__('All done', 'wp-file-manager-pro').'</li>'));
                }
            }
            die;
        }
        /*
        Backup - Remove
        */
        public function mk_file_manager_pro_logs_remove_callback(){
            if(wp_verify_nonce( $_POST['key'], 'del_alllog_nonce') && current_user_can('administrator')){
                global $wpdb;
                $fmdb = $wpdb->prefix.'fm_file_action_log';
                $Logids = $_POST['delarr'];
                $isRemoved = false;        
                if(isset($Logids)) {
                    foreach($Logids as $Logid) {
                        // removing from db
                        $wpdb->delete($fmdb, array('id' => $Logid));
                        $isRemoved = true;
                    }
                }
                if($isRemoved) {
                    echo __('Log(s) removed successfully!', 'wp-file-manager-pro');
                } else {
                    echo __('Unable to removed log(s)!', 'wp-file-manager-pro'); 
                }
            }
            else {
                echo __('Invalid security key!', 'wp-file-manager-pro');
            }
            die;
        }
        /*
        * File Manager Load pre-packed extensions
        */
        public function load_packed_extensions()
        {
            $dir = dirname(__FILE__).'/extensions';
            if (is_dir($dir)) {
                $extensions = glob($dir.'/*');
                foreach ($extensions as $extension) {
                    if (is_dir($extension)) {
                        include $extension.'/'.basename($extension).'.php';
                    }
                }
            }
        }

        //install vendor
        public function mk_file_folder_manager_install_vendor_callback()
        {
            $vendor_url = 'https://webdesi9.com/filemanager_vendor/vendor.zip';
            $destination = FILEMANEGERPROPATH.'lib';
            $newZip = $destination.'/vendor.zip';
            $f = file_put_contents($newZip, fopen($vendor_url, 'r'), LOCK_EX);
            if (false === $f) {
                die(__("Couldn't write to file.", 'wp-file-manager-pro'));
            }
            $zip = new ZipArchive();
            $res = $zip->open($newZip);
            if ($res === true) {
                $zip->extractTo($destination);
                $zip->close();
                echo __('Required libraries installed successfully.', 'wp-file-manager-pro');
                unlink($newZip);
            } else {
                echo __('Unable to install Required libraries.', 'wp-file-manager-pro');
            }
            die;
        }

        /* Allow Shortcode Button */
        public function allow_shortocode_btn()
        {
            $opt = get_option('wp_filemanager_options');
            if (isset($opt['allow_shortcode_btn_editor']) && $opt['allow_shortcode_btn_editor'] == 'yes') {
                return true;
            } else {
                return false;
            }
        }

        /* Auto Directory */
        public function create_auto_directory()
        {
            $upload_dir = wp_upload_dir();
            if (is_user_logged_in()) {
                $current_user = wp_get_current_user();                
                if (isset($current_user->user_login) && !empty($upload_dir['basedir'])) {
                    $user_dirname = $upload_dir['basedir'].'/wp-file-manager-pro/users/'.$current_user->user_login;
                    if (!file_exists($user_dirname)) {
                        wp_mkdir_p($user_dirname);
                    }
                }
            }
            /* BackUp - Folder, since 6.0 */
            $backup_dirname = $upload_dir['basedir'].'/wp-file-manager-pro/fm_backup';
            if (!file_exists($backup_dirname)) {
                wp_mkdir_p($backup_dirname);
            }

            // security fix
            $myfile = $backup_dirname."/.htaccess";
            $myfileHandle = @fopen($myfile, 'wr');
            $txt = '<FilesMatch "\.(zip|gz)$">';
            $txt .= "\nOrder allow,deny\n";
            $txt .= "Deny from all\n";
            $txt .= "</Files>";
            @fwrite($myfileHandle, $txt);
            @fclose($myfileHandle);

            // creating blank index.html inside fm_backup
            $ourFileName = $backup_dirname."/index.html";
            $ourFileHandle = @fopen($ourFileName, 'w');
            @fclose($ourFileHandle);
            @chmod($ourFileName, 0755);

        }

        /**
		* Generate plugin key
		**/
		
		private static function fm_generate_key(){
			return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(25/strlen($x)) )),1,25);
        }
        
        /**
		* Generate plugin key
		**/
		
		public static function fm_get_key(){
			return get_option('fm_key');
		}

        /* File Manager */
        public function filemanager_pro_load_text_domain()
        {
            $domain = dirname(plugin_basename(__FILE__));
            $locale = apply_filters('plugin_locale', get_locale(), $domain);
            load_textdomain($domain, trailingslashit(WP_LANG_DIR).'plugins'.'/'.$domain.'-'.$locale.'.mo');
            load_plugin_textdomain($domain, false, basename(dirname(__FILE__)).'/languages/');
            // aws - ses - wp_mail() - fix
            if (!function_exists('wp_get_current_user')) {
                include ABSPATH.'wp-includes/pluggable.php';
            }
            if (is_admin()) {
                include 'controller/fm_controller.php';
                $fm_controller = new mk_fm_controller();
            }

            ////// Creating key
            $fmkey = self::fm_generate_key();
            if(self::fm_get_key() == ""){
                update_option('fm_key',$fmkey);
            }
        }

        /* Menu Page */
        public function ffm_menu_page()
        {
            $permissions = $this->permissions();
            add_menu_page(
            __('WP File Manager', 'wp-file-manager-pro'),
            __('WP File Manager', 'wp-file-manager-pro'),
            $permissions,
            'wp_file_manager',
            array(&$this, 'ffm_settings_callback'),
            plugins_url('images/wp_file_manager.svg', __FILE__)
            );           
            /* Only for admin */
            add_submenu_page('wp_file_manager', __('Settings', 'wp-file-manager-pro'), __('Settings', 'wp-file-manager-pro'), 'manage_options', 'wp_file_manager_settings', array(&$this, 'wp_file_manager_settings'));
             /* Only for admin */
             add_submenu_page('wp_file_manager', __('Preferences', 'wp-file-manager'), __('Preferences', 'wp-file-manager'), 'manage_options', 'wp_file_manager_root', array(&$this, 'wp_file_manager_root'));
            /* Only for admin */
            add_submenu_page('wp_file_manager', __('Email Notifications', 'wp-file-manager-pro'), __('Email Notifications', 'wp-file-manager-pro'), 'manage_options', 'wpfm-emails', array(&$this, 'wp_file_manager_email_notifications'));
            /* Only for admin */
            
            add_submenu_page('wp_file_manager', __('Shortcode Generator', 'wp-file-manager-pro'), __('Shortcode Generator', 'wp-file-manager-pro'), 'manage_options', 'wp_file_manager_shortcode_generator', array(&$this, 'wp_file_manager_shortcode_generator'));

            if (class_exists('wp_file_manager_googledrive')) {
                add_submenu_page('wp_file_manager', __('Google Drive', 'wp-file-manager-pro'), __('Google Drive', 'wp-file-manager-pro'), 'manage_options', 'wp_file_manager_gdrive_settings', array('wp_file_manager_googledrive', 'wp_file_manager_gdrive_settings'));
            }
            if (class_exists('wp_file_manager_mobile')) {
                add_submenu_page('wp_file_manager', __('Mobile', 'wp-file-manager-pro'), __('Mobile', 'wp-file-manager-pro'), 'manage_options', 'wp_file_manager_mobile_settings', array('wp_file_manager_mobile', 'wp_file_manager_mobile_settings'));
            }
            add_submenu_page('wp_file_manager', __('Addons', 'wp-file-manager-pro'), __('Addons', 'wp-file-manager-pro'), 'manage_options', 'wpfm-addons', array(&$this, 'wp_file_manager_extension'));
            add_submenu_page('wp_file_manager', __('Logs', 'wp-file-manager-pro'), __('Logs', 'wp-file-manager-pro'), 'manage_options', 'wpfm-logs', array(&$this, 'wp_file_manager_logs'));
            add_submenu_page('wp_file_manager', __('Backup/Restore', 'wp-file-manager-pro'), __('Backup/Restore', 'wp-file-manager-pro'), 'manage_options', 'wpfm-backup', array(&$this, 'wp_file_manager_backup'));
            add_submenu_page('', __('Existing Shortcode', 'wp-file-manager-pro'), __('Existing Shortcode', 'wp-file-manager-pro'), 'manage_options', 'wp_file_manager_existing_shortcodes', array(&$this, 'wp_file_manager_existing_shortcodes'));
        }
         /*
         Backup
        */
        public function wp_file_manager_backup() {
            $this->render('inc', 'backup', true);
        } 
        /* Logs */
        public function wp_file_manager_logs() 
        {
           $this->render('inc', 'wpfmlogs', true);
        }
        /* Main Role */
        public function ffm_settings_callback()
        {
            $this->render('lib', 'wpfilemanager', true);
        }

        /*Settings */
        public function wp_file_manager_settings()
        {
            $this->render('inc', 'settings', true);
        }

        public function wp_file_manager_shortcode_generator(){
            $this->render('inc', 'shortcode_generator', true);
        }

        public function wp_file_manager_existing_shortcodes(){
            $this->render('inc', 'shortcode_listing', true);
        }

        /* Extesions - Show */
        public function wp_file_manager_extension()
        {
            $this->render('inc', 'extensions', true);
        }

        /* Email Notifications */
        public function wp_file_manager_email_notifications()
        {
            $this->render('inc', 'email', true);
        }

        /*
         Root
        */
        public function wp_file_manager_root()
        {
            $this->render('inc', 'root', true);
        }

        public function ffm_admin_script(){
            wp_enqueue_style( 'fm_menu_common', FILEMANAGERPROURL.'/css/fm_common.css' );
        }
        /* Admin  Things */
        public function ffm_admin_things()
        {
            
            $opt = get_option('wp_filemanager_options');
            $getPage = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
            $pluginPages = array('wp_file_manager','wp_file_manager_settings','wp_file_manager_root','wpfm-emails','wp_file_manager_shortcode_generator','wpfm-addons','wpfm-logs','wpfm-backup','wp_file_manager_db_manager');

            $opt_doc_popu = get_option('wp_filemanager_sc_update');
            $license_opt = get_option('wp_file_manager_pro');
           
            if((!$opt_doc_popu && empty($opt_doc_popu) || (!empty($opt_doc_popu) && mk_file_folder_manager::FILE_MANAGER_VERSION > $opt_doc_popu)) && !empty($getPage) && in_array($getPage, $pluginPages) && !empty($license_opt['ispro']) && !empty($license_opt['serialkey'])){
                $this->render('inc', 'popup', true);
            }
            $allowedPages = array(
                                      'wp_file_manager',
                                      'wp_file_manager_settings',
                                      'wp_file_manager_shortcode_generator',
                                      'wp_file_manager_existing_shortcodes',
                                      );
            $theme = isset($opt['theme']) && !empty($opt['theme']) ? sanitize_text_field($opt['theme']) : '';                       
            // Languages
            $lang = isset($opt['lang']) && !empty($opt['lang']) ? sanitize_text_field($opt['lang']) : 'en';
			if($getPage == "wp_file_manager_existing_shortcodes"){
                wp_enqueue_style('fm-dataTables-css', plugins_url('css/dataTables.min.css', __FILE__));
                wp_enqueue_script('fm-dataTables-js', plugins_url('js/jquery.dataTables.min.js', __FILE__));
            }
            if (!empty($getPage) && in_array($getPage, $allowedPages)):

                mk_file_folder_manager::loadLibFiles($lang, $theme);

                wp_enqueue_style('wp_file_manager', plugins_url('css/wp_file_manager_pro.css', __FILE__), '', self::FILE_MANAGER_VERSION);

            endif;
            
            if (!empty($getPage) && ($getPage == 'wp_file_manager_settings' || $getPage == 'wp_file_manager_shortcode_generator')) {
                wp_enqueue_style('jquery-multiselect-css', plugins_url('css/jquery.multiselect.css', __FILE__));
                wp_enqueue_script('jquery-multiselect-js', plugins_url('js/jquery.multiselect.js', __FILE__));
                wp_register_script('mkapp', plugins_url('js/app.js', __FILE__));
                $mkapp_arr = array(
                    'searchBoxText' => __('Type here to search list... ', 'wp-file-manager-pro'),
                    'checkAllText' => __('Check all', 'wp-file-manager-pro'),
                    'uncheckAllText' => __('Uncheck all', 'wp-file-manager-pro'),
                    'invertSelectText' => __('Invert select', 'wp-file-manager-pro')
                );
                wp_localize_script( 'mkapp', 'appparams', $mkapp_arr);
                wp_enqueue_script('mkapp');
            }
        }

        public static function loadLibFiles($lang, $theme) {
            global $wp_version;
            $commonCssFiles = array(
                'jquery-ui' => 'css/jquery-ui.css',
                'fm_commands' => 'lib/css/commands.css',
                'fm_common' => 'lib/css/common.css',
                'fm_contextmenu' => 'lib/css/contextmenu.css',
                'fm_cwd' => 'lib/css/cwd.css',
                'fm_dialog' => 'lib/css/dialog.css',
                'fm_fonts' => 'lib/css/fonts.css',
                'fm_navbar' => 'lib/css/navbar.css',
                'fm_places' => 'lib/css/places.css',
                'fm_quicklook' => 'lib/css/quicklook.css',
                'fm_statusbar' => 'lib/css/statusbar.css',
                'theme' => 'lib/css/theme.css',
                'fm_toast' => 'lib/css/toast.css',
                'fm_toolbar' => 'lib/css/toolbar.css',
            );

             // New Theme
            if (!empty($theme)) {
                if ($theme != 'default') {
                    $commonCssFiles['fm-theme-'.$theme] = 'lib/themes/'.$theme.'/css/theme.css';
                }
            }

            foreach($commonCssFiles as $uniqueCssKey => $commonCssFilePath) {
                wp_enqueue_style($uniqueCssKey, plugins_url($commonCssFilePath, __FILE__), '', self::FILE_MANAGER_VERSION);
            }

            $jquery_ui_js = 'jquery-ui-1.11.4.js';
            // 5.6 jquery ui issue fix
            if ( version_compare( $wp_version, '5.6', '>=' ) ) {
                $jquery_ui_js = 'jquery-ui-1.12.1.js';
            }
            $commonJsFiles = array(
                'fm_top' => 'js/top.js',
                'fm_jquery_ui' => 'lib/jquery/'.$jquery_ui_js,
                'fm_elFinder' => 'lib/js/elFinder.js',
                'fm_elFinder_version' => 'lib/js/elFinder.version.js',
                'fm_jquery_elfinder' => 'lib/js/jquery.elfinder.js',
                'fm_elFinder_mimetypes' => 'lib/js/elFinder.mimetypes.js',
                'fm_elFinder_options' => 'lib/js/elFinder.options.js',
                'fm_elFinder_options_netmount' => 'lib/js/elFinder.options.netmount.js',
                'fm_elFinder_history' => 'lib/js/elFinder.history.js',
                'fm_elFinder_command' => 'lib/js/elFinder.command.js',
                'fm_elFinder_resources' => 'lib/js/elFinder.resources.js',
                'fm_dialogelfinder' => 'lib/js/jquery.dialogelfinder.js',
                'fm_quicklook_googledocs' => 'lib/js/extras/quicklook.googledocs.js'
            );

               /** Language */
               if (!empty($lang)) {
                  $commonJsFiles['fm-lang-'.$lang] = 'lib/js/i18n/elfinder.'.$lang.'.js';         
                } else {
                  $commonJsFiles['fm-lang-en'] = 'lib/js/i18n/elfinder.en.js'; 
                }

            wp_enqueue_script('jquery');

            foreach($commonJsFiles as $uniqueJsKey => $commonjsFilePath) {
                wp_enqueue_script($uniqueJsKey, plugins_url($commonjsFilePath, __FILE__), '', self::FILE_MANAGER_VERSION);
            } 
      

            // uis
            $fmUis = mk_file_folder_manager::readDir(fm_file_path.'/lib/js/ui/', 'js');

            unset($fmUis['places']);
            foreach($fmUis as $ui) {
                wp_enqueue_script('fm_ui_'.$ui, plugins_url('lib/js/ui/'.$ui.'.js', __FILE__), '', self::FILE_MANAGER_VERSION);
            }              
       
            // commands
            $commands = mk_file_folder_manager::readDir(fm_file_path.'/lib/js/commands/', 'js');
            unset($commands['places']);
            foreach($commands as $command) {                  
                wp_enqueue_script('fm_command_'.$command, plugins_url('lib/js/commands/'.$command.'.js', __FILE__), '', self::FILE_MANAGER_VERSION); 
            }

        }

        public static function loadCodeMirror() {
            $opt = get_option('wp_filemanager_options');
            wp_enqueue_script( 'codemirror_js', plugins_url('lib/codemirror/lib/codemirror.js', __FILE__ ), '', self::FILE_MANAGER_VERSION);						
            wp_enqueue_style( 'codemirror_css', plugins_url('lib/codemirror/lib/codemirror.css', __FILE__), '', self::FILE_MANAGER_VERSION);
            if(!empty($opt['code_editor_theme']) && $opt['code_editor_theme'] != 'default') {
                wp_enqueue_style( 'codemirror_theme', plugins_url('lib/codemirror/theme/'.$opt['code_editor_theme'].'.css', __FILE__), '', self::FILE_MANAGER_VERSION);	
            }	
            wp_enqueue_script( 'fm_htmlmixed', plugins_url('lib/codemirror/mode/htmlmixed/htmlmixed.js', __FILE__ ), '', self::FILE_MANAGER_VERSION);
            wp_enqueue_script( 'fm_xml', plugins_url('lib/codemirror/mode/xml/xml.js', __FILE__ ), '', self::FILE_MANAGER_VERSION);	
            wp_enqueue_script( 'fm_css', plugins_url('lib/codemirror/mode/css/css.js', __FILE__ ), '', self::FILE_MANAGER_VERSION);	
            wp_enqueue_script( 'fm_javascript_js', plugins_url('lib/codemirror/mode/javascript/javascript.js', __FILE__ ), '', self::FILE_MANAGER_VERSION);	
            wp_enqueue_script( 'fm_clike', plugins_url('lib/codemirror/mode/clike/clike.js', __FILE__ ), '', self::FILE_MANAGER_VERSION);
            wp_enqueue_script( 'fm_php', plugins_url('lib/codemirror/mode/php/php.js', __FILE__ ), '', self::FILE_MANAGER_VERSION);	
        }

        /**
         * Read Dir
         */
        public static function readDir($path, $type) {
            $dir = $path;
            $files = glob($dir.'/*.'.$type);
            $filenames = array();
            foreach ($files as $file) {
                $filenames[basename($file, '.'.$type)] = basename($file, '.'.$type);
            }
             return $filenames;
        }

        /*
        * Ajax request handler
        * Run File Manager
        */
        public function mk_file_folder_manager_action_callback()
        {
            require 'lib/php/autoload.php';
            if(!isset($_REQUEST["is_type"])){
                elFinder::$netDrivers['ftp'] = 'FTP';
            }
            $current_user = wp_get_current_user();
            $userLogin = $current_user->user_login;
            $user = new WP_User($current_user->ID);
            $file_operations = array();
            if (!empty($user->roles) && is_array($user->roles)) {
                foreach ($user->roles as $role):
                    $role;
                endforeach;
            }

            // allowing vendor for gdrive || Dropbox
            if (class_exists('wp_file_manager_dropbox') || class_exists('wp_file_manager_googledrive')) {
                if (file_exists(FILEMANEGERPROPATH.'lib/vendor/autoload.php')) {
                    require 'lib/vendor/autoload.php';
                }
            }

            $thirdParty = get_option('wp_file_manager_pro_3rd_party');

            /* Drop Box Addon Support Code */
            $dropbox = array();

            if (class_exists('wp_file_manager_dropbox')) {
                /* DROP BOX Integrations Start */
                $dropbox_int = get_option('wp_file_manager_dropbox');
                /* DROP BOX */
                $enable_dropbox = false;
                if (isset($dropbox_int['enable_fm_dropbox']) && $dropbox_int['enable_fm_dropbox'] == 1) {
                    $enable_dropbox = true;
                }
                if ($enable_dropbox) {
                    $dropbox_accessfolder = '';
                    if (isset($dropbox_int['private_folder_access']) && !empty($dropbox_int['private_folder_access'])):
               $dropbox_accessfolder = $dropbox_int['private_folder_access'];
                    endif;
                    $folderRestricted = array();
                    $fileRestricted = array();
                    $arr_mime_types = $this->getMimeTypeFromExtension();
                    $mime_denied = array('');
                    /* According To Username */
                    $selected_user = (isset($dropbox_int['select_users']) && !empty($dropbox_int['select_users'])) ? $dropbox_int['select_users'] : array();
                    $select_user_roles = (isset($dropbox_int['select_user_roles']) && !empty($dropbox_int['select_user_roles'])) ? $dropbox_int['select_user_roles'] : array();
                    if (in_array($userLogin, $selected_user)):
                    $key = array_search($userLogin, $selected_user);
                    /* Seperate Folder access */
                    if (!empty($dropbox_int['user_seprate_folder'][$key])):
                      $dropbox_accessfolder = $dropbox_int['user_seprate_folder'][$key];
                    endif;
                    /* File Operations */
                    $file_operations = $dropbox_int['users_fileoperations_'.$key];
                    /* Folder Restrictions */
                    $restrictedFolders = isset($dropbox_int['restrict_user_folders'][$key]) ? explode('|', $dropbox_int['restrict_user_folders'][$key]) : array();
                    if (!empty($restrictedFolders[0]) && is_array($restrictedFolders)):
                      foreach ($restrictedFolders as $restrictedFolder):
                        $folderRestricted[] = array('pattern' => '!^\/'.$restrictedFolder.'$!', 'hidden' => true);
                    endforeach; else:
                       $folderRestricted[] = array('hidden' => 'false');
                    endif;
                    /* File Restrictions */
                    $restrictedfiles = isset($dropbox_int['restrict_user_files'][$key]) ? explode('|', $dropbox_int['restrict_user_files'][$key]) : array();
                    if (!empty($restrictedfiles[0]) && is_array($restrictedfiles)):
                      foreach ($restrictedfiles as $restrictedFile):
                       $pattern = '/'.$restrictedFile.'$/';
                        $fileRestricted[] = array('pattern' => $pattern, 'write' => false, 'locked' => true, 'read' => false);
                        $file_type_extension = strtolower(ltrim($restrictedFile,"."));
                        $app_type = isset($arr_mime_types[$file_type_extension]) ? $arr_mime_types[$file_type_extension] : '';
                        
                        if(!empty($app_type)){
                            if(isset($mime_denied[0]) && $mime_denied[0] == ''){
                                $mime_denied[0] = $app_type;
                            }
                            else{
                                $mime_denied[] = $app_type;
                            }
                        }    
                    endforeach; else:
                       $fileRestricted[] = array('pattern' => '', 'locked' => false);
                    endif;
                    /* According to userroles */
                    elseif (in_array($role, $select_user_roles)):
                    $key = array_search($role, $select_user_roles);
                    /* Seperate Folder access */
                    if (!empty($dropbox_int['seprate_folder'][$key])):
                      $dropbox_accessfolder = $dropbox_int['seprate_folder'][$key];
                    endif;
                    /* File Operations */
                    $file_operations = $dropbox_int['userrole_fileoperations_'.$key];
                    /* Folder Restrictions */
                    $restrictedFolders = isset($dropbox_int['restrict_folders'][$key]) ? explode('|', $dropbox_int['restrict_folders'][$key]) : array();
                    if (!empty($restrictedFolders[0]) && is_array($restrictedFolders)):
                      foreach ($restrictedFolders as $restrictedFolder):
                        $folderRestricted[] = array('pattern' => '!^\/'.$restrictedFolder.'$!', 'hidden' => true);
                    endforeach; else:
                        $folderRestricted[] = array('hidden' => 'false');
                    endif;
                    /* File Restrictions */
                    
                    $restrictedfiles = isset($dropbox_int['restrict_files'][$key]) ? explode('|', $dropbox_int['restrict_files'][$key]) : array();
                    if (!empty($restrictedfiles[0]) && is_array($restrictedfiles)):
                      foreach ($restrictedfiles as $restrictedFile):
                       $pattern = '/'.$restrictedFile.'$/';
                        $fileRestricted[] = array('pattern' => $pattern, 'write' => false, 'locked' => true, 'read' => false);
                        $file_type_extension = strtolower(ltrim($restrictedFile,"."));
                        $app_type = isset($arr_mime_types[$file_type_extension]) ? $arr_mime_types[$file_type_extension] : '';
                        
                        if(!empty($app_type)){
                            if(isset($mime_denied[0]) && $mime_denied[0] == ''){
                                $mime_denied[0] = $app_type;
                            }
                            else{
                                $mime_denied[] = $app_type;
                            }
                        }
                    endforeach; else:
                       $fileRestricted[] = array('pattern' => '', 'locked' => false);
                    endif; else:
                    $folderRestricted[] = array('hidden' => 'false');
                    $fileRestricted[] = array('pattern' => '', 'locked' => false);
                    endif;
                    $mime_allowed = array('');
                    /* Path View */
                    $mk_restrictions = array();
                    $restrictedFolders = isset($restrictedFolders) ? $restrictedFolders : array();
                    $cc = count($restrictedFolders);

                    if (count($fileRestricted) > $cc) {
                        $cc = count($fileRestricted);
                    }
                    for ($mu = 0; $mu <= $cc; ++$mu) {
                        if(isset($fileRestricted[$mu])){
                            $mk_restrictions[$mu] = $fileRestricted[$mu];
                        }
                        if(isset($folderRestricted[$mu])){
                            $mk_restrictions[$mu] = $folderRestricted[$mu];
                        }
                    }
                    /* End According To User */
                    if (!isset($file_operations) || empty($file_operations)) {
                        $file_operations = array('help', 'empty', 'preference', 'netmount');
                    }
                    if(!in_array('preference', $file_operations)){
                        $file_operations[] = 'preference';
                        $file_operations[] = 'hide';
                        $file_operations[] = 'help';
                        $file_operations[] = 'netmount';
                    }

                    elFinder::$netDrivers['dropbox2'] = 'Dropbox2';
                    $ELFINDER_DROPBOX_APPKEY = isset($dropbox_int['ELFINDER_DROPBOX_APPKEY']) ? $dropbox_int['ELFINDER_DROPBOX_APPKEY'] : '';
                    $ELFINDER_DROPBOX_APPSECRET = isset($dropbox_int['ELFINDER_DROPBOX_APPSECRET']) ? $dropbox_int['ELFINDER_DROPBOX_APPSECRET'] : '';
                    $ELFINDER_ACCESS_TOKEN = isset($dropbox_int['ELFINDER_ACCESS_TOKEN']) ? $dropbox_int['ELFINDER_ACCESS_TOKEN'] : '';
                    $path = '/';
                    if (!empty($dropbox_accessfolder)) {
                        $path = '/'.$dropbox_accessfolder;
                    }
                    if (!empty($ELFINDER_DROPBOX_APPKEY) && !empty($ELFINDER_DROPBOX_APPSECRET) && !empty($ELFINDER_ACCESS_TOKEN)) {
                        define('ELFINDER_DROPBOX_APPKEY', $ELFINDER_DROPBOX_APPKEY);
                        define('ELFINDER_DROPBOX_APPSECRET', $ELFINDER_DROPBOX_APPSECRET);
                        $dropbox = array(
                            'driver' => 'Dropbox2',
                            'path' => $path,
                            'access_token' => $ELFINDER_ACCESS_TOKEN,
                            'disabled' => $file_operations,
                            'attributes' => $mk_restrictions,
                        );
                    }
                }
            }
            /* END DROP BOX */

              /*
				GDrive Integration Start
				*/
            $gdriveRoot = array();
            if (class_exists('wp_file_manager_googledrive')) {			    
				$gdrive = get_option('wp_file_manager_google_drive');
				$gdriveTokenjson = get_option('gdriveToken');
				$gdriveToken = json_decode($gdriveTokenjson, true);
				$enable_onedrive = false;
				if(isset($gdrive['ELFINDER_ENABLE_GOOGLE_DRIVE']) && $gdrive['ELFINDER_ENABLE_GOOGLE_DRIVE'] == 1) {
					$enable_onedrive = true;
				}
				if($enable_onedrive) {
                    $arr_mime_types = $this->getMimeTypeFromExtension();
                    $mime_denied = array('');
				/* Premissions System Start */
				$gdrive_accessfolder = '';	
				$gdrive_private_folder_access_type = '';
				if(isset($gdrive['private_folder_access']) && !empty($gdrive['private_folder_access'])):
				   $gdrive_accessfolder = $gdrive['private_folder_access'];
				   $gdrive_private_folder_access_type = $gdrive['box_private_folder_access_type'];
				endif;
				$gdrivefolderRestricted = array();
				$gdrivefileRestricted = array();
			/* According To Username */
			$selected_user = (isset($gdrive['select_users']) && !empty($gdrive['select_users'])) ? $gdrive['select_users'] : array();
			$select_user_roles = (isset($gdrive['select_user_roles']) && !empty($gdrive['select_user_roles'])) ? $gdrive['select_user_roles'] : array();
			if(in_array($userLogin, $selected_user)):
					$key = array_search($userLogin, $selected_user);
					/* Seperate Folder access */
					if(!empty($gdrive['user_seprate_folder'][$key])):
			          $gdrive_accessfolder = $gdrive['user_seprate_folder'][$key];
					  $gdrive_private_folder_access_type = $gdrive['user_onedrive_private_folder_access_type'][$key];
			        endif;
					/* File Operations */
					$gdrive_file_operations = $gdrive['users_fileoperations_'.$key];
					/* Folder Restrictions */
					$restrictedFolders = isset($gdrive['restrict_user_folders'][$key]) ? explode('|', $gdrive['restrict_user_folders'][$key]) : array();
					if(!empty($restrictedFolders[0]) && is_array($restrictedFolders)):
					  foreach($restrictedFolders as $restrictedFolder):
						$gdrivefolderRestricted[] = array( 'pattern' => '!^\/'.$restrictedFolder.'$!','hidden' => true );
					  endforeach;
					else:
					   $gdrivefolderRestricted[] = array('hidden' => 'false'); 	  			
					endif;
					/* File Restrictions */
					$restrictedfiles = isset($gdrive['restrict_user_files'][$key]) ? explode('|', $gdrive['restrict_user_files'][$key]) : array();
					if(!empty($restrictedfiles[0]) && is_array($restrictedfiles)):
					  foreach($restrictedfiles as $restrictedFile):
					   $pattern = '/'.$restrictedFile.'$/';
                       $gdrivefileRestricted[] = array( 'pattern' => $pattern, 'write' => false, 'locked' => true, 'read' => false );
                       $file_type_extension = strtolower(ltrim($restrictedFile,"."));
                        $app_type = isset($arr_mime_types[$file_type_extension]) ? $arr_mime_types[$file_type_extension] : '';
                        
                        if(!empty($app_type)){
                            if(isset($mime_denied[0]) && $mime_denied[0] == ''){
                                $mime_denied[0] = $app_type;
                            }
                            else{
                                $mime_denied[] = $app_type;
                            }
                        }
					  endforeach;
					else:
					   $gdrivefileRestricted[] = array( 'pattern' => '', 'locked' => false );			
					endif;			
			/* According to userroles */	
			elseif(in_array($role, $select_user_roles)):
				    $key = array_search($role, $select_user_roles);
					/* Seperate Folder access */
					if(!empty($gdrive['seprate_folder'][$key])):
			          $gdrive_accessfolder = $gdrive['seprate_folder'][$key];
					  $gdrive_private_folder_access_type = $gdrive['userrole_onedrive_private_folder_access_type'][$key];
			        endif;
					/* File Operations */
					$gdrive_file_operations = $gdrive['userrole_fileoperations_'.$key];				
					/* Folder Restrictions */
					$restrictedFolders = isset($gdrive['restrict_folders'][$key]) ? explode('|', $gdrive['restrict_folders'][$key]) : array();
					if(!empty($restrictedFolders[0]) && is_array($restrictedFolders)):
					  foreach($restrictedFolders as $restrictedFolder):
						$gdrivefolderRestricted[] = array( 'pattern' => '!^\/'.$restrictedFolder.'$!','hidden' => true );
					  endforeach;
					else:
						$gdrivefolderRestricted[] = array('hidden' => 'false');			
					endif;
					/* File Restrictions */
					$restrictedfiles = isset($gdrive['restrict_files'][$key]) ? explode('|', $gdrive['restrict_files'][$key]) : array();
					if(!empty($restrictedfiles[0]) && is_array($restrictedfiles)):
					  foreach($restrictedfiles as $restrictedFile):
					   $pattern = '/'.$restrictedFile.'$/';
                       $gdrivefileRestricted[] = array( 'pattern' => $pattern, 'write' => false, 'locked' => true, 'read' => false );
                       $file_type_extension = strtolower(ltrim($restrictedFile,"."));
                            $app_type = isset($arr_mime_types[$file_type_extension]) ? $arr_mime_types[$file_type_extension] : '';
                            
                            if(!empty($app_type)){
                                if(isset($mime_denied[0]) && $mime_denied[0] == ''){
                                    $mime_denied[0] = $app_type;
                                }
                                else{
                                    $mime_denied[] = $app_type;
                                }
                            }
					  endforeach;
					else:
					   $gdrivefileRestricted[] = array( 'pattern' => '', 'locked' => false );			
					endif;		
			else:	
					$gdrivefolderRestricted[] = array('hidden' => 'false');
					$gdrivefileRestricted[] = array( 'pattern' => '', 'locked' => false );			     	
			endif;
					$mime_allowed = array('');
				    
			 /* Path View */		
			 $mk_onedrive_restrictions = array();
			 
			 $cc = count($gdrivefolderRestricted);
			 
			 if(count($gdrivefileRestricted) > $cc) {
				 $cc = count($gdrivefileRestricted);
			 }
			 for($mu=0; $mu<=$cc; $mu++) {
                if(isset($gdrivefileRestricted[$mu])){
				    $mk_onedrive_restrictions[$mu] = $gdrivefileRestricted[$mu];
                }
                if(isset($gdrivefolderRestricted[$mu])){
				    $mk_onedrive_restrictions[$mu] = $gdrivefolderRestricted[$mu];
                }
			 }									 
			/* End According To User */
			if(!isset($gdrive_file_operations) || empty($gdrive_file_operations)) {
				$gdrive_file_operations = array('help','empty', 'preference','netmount');
            }
            
            if(!in_array('preference', $gdrive_file_operations)){
                $gdrive_file_operations[] = 'preference';
                $gdrive_file_operations[] = 'hide';
                $gdrive_file_operations[] = 'help';
                $gdrive_file_operations[] = 'netmount';
            }
            //mount gdrive

            elFinder::$netDrivers['googledrive'] = 'GoogleDrive';

			$ELFINDER_GOOGLEDRIVE_CLIENTID = isset($gdrive['ELFINDER_GOOGLEDRIVE_CLIENTID']) ? $gdrive['ELFINDER_GOOGLEDRIVE_CLIENTID'] : '';
			$ELFINDER_GOOGLEDRIVE_CLIENTSECRET = isset($gdrive['ELFINDER_GOOGLEDRIVE_CLIENTSECRET']) ? $gdrive['ELFINDER_GOOGLEDRIVE_CLIENTSECRET'] : '';
				if(!empty($ELFINDER_GOOGLEDRIVE_CLIENTID) && !empty($ELFINDER_GOOGLEDRIVE_CLIENTSECRET)) {
				  define('ELFINDER_GOOGLEDRIVE_CLIENTID', $ELFINDER_GOOGLEDRIVE_CLIENTID);
				  define('ELFINDER_GOOGLEDRIVE_CLIENTSECRET', $ELFINDER_GOOGLEDRIVE_CLIENTSECRET);
				}	
					/* Pwrmissions System End */		
						$gr = array();				
						if(!empty($gdrive_accessfolder)) {
							if(!empty($gdrive_private_folder_access_type)) {
								
							  if($gdrive_private_folder_access_type == 1) {
								  
								$paths = explode(',', $gdrive_accessfolder);
						
								foreach($paths as $path) {
								  $fldr = str_replace(' ', '%20', $path);	
								  $gr[] = $this->get_gdrive_folder_id($fldr);
								}
								
							  } else if($gdrive_private_folder_access_type == 2) {
								$paths = explode(',', $gdrive_accessfolder);  
								foreach($paths as $path) {
								 $fldr = str_replace(' ', '%20', $path);	
                                 $gr[] = $fldr;
								}
								
							  }
							}
						}
					if(!empty($gr)) {
						foreach($gr as $k => $v) {
							 $gdriveRoot[] = array(
								'driver' => 'GoogleDrive',										
								'refresh_token' => $gdriveToken['refresh_token'],
								'access_token' => $gdriveToken['access_token'],
								'path' => '/'.$v,
								'disabled'      => $gdrive_file_operations,
								'attributes' => $mk_onedrive_restrictions		
								);	
									 	
							}
						} else {
							 $gdriveRoot[] = array(
								'driver' => 'GoogleDrive',										
								'refresh_token' => $gdriveToken['refresh_token'],
								'access_token' => $gdriveToken['access_token'],
								'path' => '/',
								'disabled'      => $gdrive_file_operations,
								'attributes' => $mk_onedrive_restrictions		
								);							
                        }
                    }
            }
            /*
            Box Integration Start
            */
            $box = array();

            if (class_exists('wp_file_manager_box')) {
                $box_settings = get_option('wp_file_manager_box');
                $enable_box = false;
                if (isset($box_settings['enable_fm_box']) && $box_settings['enable_fm_box'] == 1) {
                    $enable_box = true;
                }
                if ($enable_box) {
                    /* Premissions System Start */
                    $box_accessfolder = '';
                    $box_private_folder_access_type = '';
                    if (isset($box_settings['private_folder_access']) && !empty($box_settings['private_folder_access'])):
                   $box_accessfolder = $box_settings['private_folder_access'];
                    $box_private_folder_access_type = $box_settings['box_private_folder_access_type'];
                    endif;
                    $boxfolderRestricted = array();
                    $boxfileRestricted = array();
                    $arr_mime_types = $this->getMimeTypeFromExtension();
                    $mime_denied = array('');
                    /* According To Username */
                    $selected_user = (isset($box_settings['select_users']) && !empty($box_settings['select_users'])) ? $box_settings['select_users'] : array();
                    $select_user_roles = (isset($box_settings['select_user_roles']) && !empty($box_settings['select_user_roles'])) ? $box_settings['select_user_roles'] : array();
                    if (in_array($userLogin, $selected_user)):
                    $key = array_search($userLogin, $selected_user);
                    /* Seperate Folder access */
                    if (!empty($box_settings['user_seprate_folder'][$key])):
                      $box_accessfolder = $box_settings['user_seprate_folder'][$key];
                    $box_private_folder_access_type = $box_settings['user_box_private_folder_access_type'][$key];
                    endif;
                    /* File Operations */
                    $box_file_operations = $box_settings['users_fileoperations_'.$key];
                    /* Folder Restrictions */
                    $restrictedFolders = isset($box_settings['restrict_user_folders'][$key]) ? explode('|', $box_settings['restrict_user_folders'][$key]) : array();
                    if (!empty($restrictedFolders[0]) && is_array($restrictedFolders)):
                      foreach ($restrictedFolders as $restrictedFolder):
                        $boxfolderRestricted[] = array('pattern' => '!^\/'.$restrictedFolder.'$!', 'hidden' => true);
                    endforeach; else:
                       $boxfolderRestricted[] = array('hidden' => 'false');
                    endif;
                    /* File Restrictions */
                    $restrictedfiles = isset($box_settings['restrict_user_files'][$key]) ? explode('|', $box_settings['restrict_user_files'][$key]) : array();
                    if (!empty($restrictedfiles[0]) && is_array($restrictedfiles)):
                      foreach ($restrictedfiles as $restrictedFile):
                       $pattern = '/'.$restrictedFile.'$/';
                        $boxfileRestricted[] = array('pattern' => $pattern, 'write' => false, 'locked' => true, 'read' => false);
                        $file_type_extension = strtolower(ltrim($restrictedFile,"."));
                            $app_type = isset($arr_mime_types[$file_type_extension]) ? $arr_mime_types[$file_type_extension] : '';
                            
                            if(!empty($app_type)){
                                if(isset($mime_denied[0]) && $mime_denied[0] == ''){
                                    $mime_denied[0] = $app_type;
                                }
                                else{
                                    $mime_denied[] = $app_type;
                                }
                            }
                    endforeach; else:
                       $boxfileRestricted[] = array('pattern' => '', 'locked' => false);
                    endif;
                    /* According to userroles */
                    elseif (in_array($role, $select_user_roles)):
                    $key = array_search($role, $select_user_roles);
                    /* Seperate Folder access */
                    if (!empty($box_settings['seprate_folder'][$key])):
                      $box_accessfolder = $box_settings['seprate_folder'][$key];
                    $box_private_folder_access_type = $box_settings['userrole_box_private_folder_access_type'][$key];
                    endif;
                    /* File Operations */
                    $box_file_operations = $box_settings['userrole_fileoperations_'.$key];
                    /* Folder Restrictions */
                    $restrictedFolders = isset($box_settings['restrict_folders'][$key]) ? explode('|', $box_settings['restrict_folders'][$key]) : array();
                    if (!empty($restrictedFolders[0]) && is_array($restrictedFolders)):
                      foreach ($restrictedFolders as $restrictedFolder):
                        $boxfolderRestricted[] = array('pattern' => '!^\/'.$restrictedFolder.'$!', 'hidden' => true);
                    endforeach; else:
                        $boxfolderRestricted[] = array('hidden' => 'false');
                    endif;
                    /* File Restrictions */
                    $restrictedfiles = isset($box_settings['restrict_files'][$key]) ? explode('|', $box_settings['restrict_files'][$key]) : array();
                    if (!empty($restrictedfiles[0]) && is_array($restrictedfiles)):
                      foreach ($restrictedfiles as $restrictedFile):
                       $pattern = '/'.$restrictedFile.'$/';
                        $boxfileRestricted[] = array('pattern' => $pattern, 'write' => false, 'locked' => true, 'read' => false);
                        $file_type_extension = strtolower(ltrim($restrictedFile,"."));
                        $app_type = isset($arr_mime_types[$file_type_extension]) ? $arr_mime_types[$file_type_extension] : '';
                        
                        if(!empty($app_type)){
                            if(isset($mime_denied[0]) && $mime_denied[0] == ''){
                                $mime_denied[0] = $app_type;
                            }
                            else{
                                $mime_denied[] = $app_type;
                            }
                        }
                    endforeach; else:
                       $boxfileRestricted[] = array('pattern' => '', 'locked' => false);
                    endif; else:
                    $boxfolderRestricted[] = array('hidden' => 'false');
                    $boxfileRestricted[] = array('pattern' => '', 'locked' => false);
                    endif;
                    $mime_allowed = array('');
                    
                    /* Path View */
                    $mk_box_restrictions = array();

                    $cc = count($boxfolderRestricted);

                    if (count($boxfileRestricted) > $cc) {
                        $cc = count($boxfileRestricted);
                    }
                    for ($mu = 0; $mu <= $cc; ++$mu) {
                        if(isset($boxfileRestricted[$mu])){
                            $mk_box_restrictions[$mu] = $boxfileRestricted[$mu];
                        }
                        if(isset($boxfolderRestricted[$mu])){
                            $mk_box_restrictions[$mu] = $boxfolderRestricted[$mu];
                        }
                    }
                    /* End According To User */
                    if (!isset($box_file_operations) || empty($box_file_operations)) {
                        $box_file_operations = array('help', 'empty', 'preference','netmount');
                    }
                    if(!in_array('preference', $box_file_operations)){
                        $box_file_operations[] = 'preference';
                        $box_file_operations[] = 'hide';
                        $box_file_operations[] = 'help';
                        $box_file_operations[] = 'netmount';
                    }
                   
                    /* Premissions System End */
                    $ELFINDER_BOX_CLIENT_ID = isset($box_settings['ELFINDER_BOX_CLIENT_ID']) && !empty($box_settings['ELFINDER_BOX_CLIENT_ID']) ? $box_settings['ELFINDER_BOX_CLIENT_ID'] : '';
                    $ELFINDER_BOX_CLIENT_SECRET = isset($box_settings['ELFINDER_BOX_CLIENT_SECRET']) && !empty($box_settings['ELFINDER_BOX_CLIENT_SECRET']) ? $box_settings['ELFINDER_BOX_CLIENT_SECRET'] : '';
                    $r = array();
                    $box_access_token = get_option('wp_file_manager_pro_box_token');
                    if (!empty($box_accessfolder)) {
                        if (!empty($box_private_folder_access_type)) {
                            if ($box_private_folder_access_type == 1) {
                                $paths = explode(',', $box_accessfolder);
                                foreach ($paths as $path) {
                                    $r[] = $this->get_box_folder_id($path, $box_access_token);
                                }
                            } elseif ($box_private_folder_access_type == 2) {
                                $paths = explode(',', $box_accessfolder);
                                foreach ($paths as $path) {
                                    $r[] = $path;
                                }
                            }
                        }
                    }
                    if (!empty($r)) {
                        foreach ($r as $k => $v) {
                            $box[] = array(
                                        'driver' => 'Box',
                                        'client_id' => $ELFINDER_BOX_CLIENT_ID,
                                        'client_secret' => $ELFINDER_BOX_CLIENT_SECRET,
                                        'accessToken' => $box_access_token,
                                        'path' => '/'.$v,
                                        'disabled' => $box_file_operations,
                                        'attributes' => $mk_box_restrictions,
                                     );
                        }
                    } else {
                        $box[] = array(
                                        'driver' => 'Box',
                                        'client_id' => $ELFINDER_BOX_CLIENT_ID,
                                        'client_secret' => $ELFINDER_BOX_CLIENT_SECRET,
                                        'accessToken' => $box_access_token,
                                        'path' => '/',
                                        'disabled' => $box_file_operations,
                                        'attributes' => $mk_box_restrictions,
                                     );
                    }
                }
            }
            /*
            Box Integration end
            */

            /*
            One Drive Integration start
            */
            $onedrive = array();

            if (class_exists('wp_file_manager_onedrive')) {
                $onedrive_opt = get_option('wp_file_manager_onedrive');
                $enable_one_drive = false;
                if (isset($onedrive_opt['ELFINDER_ENABLE_ONE_DRIVE']) && $onedrive_opt['ELFINDER_ENABLE_ONE_DRIVE'] == 1) {
                    $enable_one_drive = true;
                }
                if ($enable_one_drive) {
                    /* Permissions System Start */

                    $onedrive_accessfolder = '';
                    $onedrive_private_folder_access_type = '';
                    if (isset($onedrive_opt['private_folder_access']) && !empty($onedrive_opt['private_folder_access'])):
                   $onedrive_accessfolder = $onedrive_opt['private_folder_access'];
                    $onedrive_private_folder_access_type = $onedrive_opt['box_private_folder_access_type'];
                    endif;
                    $onedrivefolderRestricted = array();
                    $onedrivefileRestricted = array();
                    $arr_mime_types = $this->getMimeTypeFromExtension();
                    $mime_denied = array('');
                    /* According To Username */
                    $onedrive_selected_user = (isset($onedrive_opt['select_users']) && !empty($onedrive_opt['select_users'])) ? $onedrive_opt['select_users'] : array();
                    $onedrive_select_user_roles = (isset($onedrive_opt['select_user_roles']) && !empty($onedrive_opt['select_user_roles'])) ? $onedrive_opt['select_user_roles'] : array();
                    if (in_array($userLogin, $onedrive_selected_user)):
                    $key = array_search($userLogin, $onedrive_selected_user);
                    /* Seperate Folder access */
                    if (!empty($onedrive_opt['user_seprate_folder'][$key])):
                      $onedrive_accessfolder = $onedrive_opt['user_seprate_folder'][$key];
                    $onedrive_private_folder_access_type = $onedrive_opt['user_onedrive_private_folder_access_type'][$key];
                    endif;
                    /* File Operations */
                    $onedrive_file_operations = $onedrive_opt['users_fileoperations_'.$key];
                    /* Folder Restrictions */
                    $onedriverestrictedFolders = isset($onedrive_opt['restrict_user_folders'][$key]) ? explode('|', $onedrive_opt['restrict_user_folders'][$key]) : array();
                    if (!empty($onedriverestrictedFolders[0]) && is_array($onedriverestrictedFolders)):
                      foreach ($onedriverestrictedFolders as $onedriverestrictedFolder):
                        $onedrivefolderRestricted[] = array('pattern' => '!^\/'.$onedriverestrictedFolder.'$!', 'hidden' => true);
                    endforeach; else:
                       $onedrivefolderRestricted[] = array('hidden' => 'false');
                    endif;
                    /* File Restrictions */
                    $onedriverestrictedfiles = isset($onedrive_opt['restrict_user_files'][$key]) ? explode('|', $onedrive_opt['restrict_user_files'][$key]) : array();
                    if (!empty($onedriverestrictedfiles[0]) && is_array($onedriverestrictedfiles)):
                      foreach ($onedriverestrictedfiles as $onedriverestrictedFile):
                       $pattern = '/'.$onedriverestrictedFile.'$/';
                        $onedrivefileRestricted[] = array('pattern' => $pattern, 'write' => false, 'locked' => true, 'read' => false);
                        $file_type_extension = strtolower(ltrim($onedriverestrictedFile,"."));
                            $app_type = isset($arr_mime_types[$file_type_extension]) ? $arr_mime_types[$file_type_extension] : '';
                            
                            if(!empty($app_type)){
                                if(isset($mime_denied[0]) && $mime_denied[0] == ''){
                                    $mime_denied[0] = $app_type;
                                }
                                else{
                                    $mime_denied[] = $app_type;
                                }
                            }
                    endforeach; else:
                       $onedrivefileRestricted[] = array('pattern' => '', 'locked' => false);
                    endif;
                    /* According to userroles */
                    elseif (in_array($role, $onedrive_select_user_roles)):
                    $key = array_search($role, $onedrive_select_user_roles);
                    /* Seperate Folder access */
                    if (!empty($onedrive_opt['seprate_folder'][$key])):
                      $onedrive_accessfolder = $onedrive_opt['seprate_folder'][$key];
                    $onedrive_private_folder_access_type = $onedrive_opt['userrole_onedrive_private_folder_access_type'][$key];
                    endif;
                    /* File Operations */
                    $onedrive_file_operations = $onedrive_opt['userrole_fileoperations_'.$key];
                    /* Folder Restrictions */
                    $onedriverestrictedFolders = isset($onedrive_opt['restrict_folders'][$key]) ? explode('|', $onedrive_opt['restrict_folders'][$key]) : array();
                    if (!empty($onedriverestrictedFolders[0]) && is_array($onedriverestrictedFolders)):
                      foreach ($onedriverestrictedFolders as $restrictedFolder):
                        $onedrivefolderRestricted[] = array('pattern' => '!^\/'.$onedriverestrictedFolder.'$!', 'hidden' => true);
                    endforeach; else:
                        $onedrivefolderRestricted[] = array('hidden' => 'false');
                    endif;
                    /* File Restrictions */
                    $onedriverestrictedfiles = isset($onedrive_opt['restrict_files'][$key]) ? explode('|', $onedrive_opt['restrict_files'][$key]) : array();
                    if (!empty($onedriverestrictedfiles[0]) && is_array($onedriverestrictedfiles)):
                      foreach ($onedriverestrictedfiles as $onedriverestrictedFile):
                       $pattern = '/'.$onedriverestrictedFile.'$/';
                    $onedrivefileRestricted[] = array('pattern' => $pattern, 'write' => false, 'locked' => true, 'read' => false);
                    $file_type_extension = strtolower(ltrim($onedriverestrictedFile,"."));
                            $app_type = isset($arr_mime_types[$file_type_extension]) ? $arr_mime_types[$file_type_extension] : '';
                            
                            if(!empty($app_type)){
                                if(isset($mime_denied[0]) && $mime_denied[0] == ''){
                                    $mime_denied[0] = $app_type;
                                }
                                else{
                                    $mime_denied[] = $app_type;
                                }
                            }
                    endforeach; else:
                       $onedrivefileRestricted[] = array('pattern' => '', 'locked' => false);
                    endif; else:
                    $onedrivefolderRestricted[] = array('hidden' => 'false');
                    $onedrivefileRestricted[] = array('pattern' => '', 'locked' => false);
                    endif;
                    $mime_allowed = array('all');
                    $mime_denied = array('');
                    /* Path View */
                    $mk_onedrive_restrictions = array();

                    $cc = count($onedrivefolderRestricted);

                    if (count($onedrivefileRestricted) > $cc) {
                        $cc = count($onedrivefileRestricted);
                    }
                    for ($mu = 0; $mu <= $cc; ++$mu) {
                        if(isset($onedrivefileRestricted[$mu])){
                            $mk_onedrive_restrictions[$mu] = $onedrivefileRestricted[$mu];
                        }
                        if(isset($onedrivefolderRestricted[$mu])){
                            $mk_onedrive_restrictions[$mu] = $onedrivefolderRestricted[$mu];
                        }
                    }
                    /* End According To User */
                    if (!isset($onedrive_file_operations) || empty($onedrive_file_operations)) {
                        $onedrive_file_operations = array('help', 'empty', 'preference','netmount');
                    }
                    if(!in_array('preference', $onedrive_file_operations)){
                        $onedrive_file_operations[] = 'preference';
                        $onedrive_file_operations[] = 'hide';
                        $onedrive_file_operations[] = 'help';
                        $onedrive_file_operations[] = 'netmount';
                    }
                    
                    /* Permissions System End  */
                    $ELFINDER_ONE_DRIVE_CLIENT_ID = isset($onedrive_opt['ELFINDER_ONE_DRIVE_CLIENT_ID']) && !empty($onedrive_opt['ELFINDER_ONE_DRIVE_CLIENT_ID']) ? $onedrive_opt['ELFINDER_ONE_DRIVE_CLIENT_ID'] : '';
                    $ELFINDER_ONE_DRIVE_CLIENT_SECRET = isset($onedrive_opt['ELFINDER_ONE_DRIVE_CLIENT_SECRET']) && !empty($onedrive_opt['ELFINDER_ONE_DRIVE_CLIENT_SECRET']) ? $onedrive_opt['ELFINDER_ONE_DRIVE_CLIENT_SECRET'] : '';
                    //elFinder::$netDrivers['onedrive'] = 'OneDrive';
                    define('ELFINDER_ONEDRIVE_CLIENTID', $ELFINDER_ONE_DRIVE_CLIENT_ID);
                    define('ELFINDER_ONEDRIVE_CLIENTSECRET', $ELFINDER_ONE_DRIVE_CLIENT_SECRET);

                    // path system start
                    $od = array();
                    $one_drive_access_token = get_option('wp_file_manager_pro_one_drive_token');
                    if (!empty($onedrive_accessfolder)) {
                        if (!empty($onedrive_private_folder_access_type)) {
                            if ($onedrive_private_folder_access_type == 1) {
                                $onedrivepaths = explode(',', $onedrive_accessfolder);
                                foreach ($onedrivepaths as $onedrivepath) {
                                    $od[] = $this->get_one_drive_id($onedrivepath, $one_drive_access_token);
                                }
                            } elseif ($onedrive_private_folder_access_type == 2) {
                                $onedrivepaths = explode(',', $onedrive_accessfolder);
                                foreach ($onedrivepaths as $onedrivepath) {
                                    $od[] = $onedrivepath;
                                }
                            }
                        }
                    }
                    if (!empty($od)) {
                        foreach ($od as $k => $v) {
                            $onedrive[] = array(
                                        'driver' => 'OneDrive',
                                        'client_id' => $ELFINDER_ONE_DRIVE_CLIENT_ID,
                                        'client_secret' => $ELFINDER_ONE_DRIVE_CLIENT_SECRET,
                                        'accessToken' => $one_drive_access_token,
                                        'path' => '/'.$v,
                                        'disabled' => $onedrive_file_operations,
                                        'attributes' => $mk_onedrive_restrictions,
                                     );
                        }
                    } else {
                        $onedrive[] = array(
                                        'driver' => 'OneDrive',
                                        'client_id' => $ELFINDER_ONE_DRIVE_CLIENT_ID,
                                        'client_secret' => $ELFINDER_ONE_DRIVE_CLIENT_SECRET,
                                        'accessToken' => $one_drive_access_token,
                                        'path' => '/',
                                        'disabled' => $onedrive_file_operations,
                                        'attributes' => $mk_onedrive_restrictions,
                                     );
                    }
                }
            }
            /*
              One Drive Integration - end
            */

            /*
            AWS S3 Integration start
            */
            $awsdrive = array();
            if (class_exists('wp_file_manager_aws_s3')) {
                $awsdrive_opt = get_option('wp_file_manager_aws');
                $enable_aws_drive = false;
                if (isset($awsdrive_opt['enable_fm_aws']) && $awsdrive_opt['enable_fm_aws'] == 1) {
                    $enable_aws_drive = true;
                }
                if ($enable_aws_drive) {
                    /* Permissions System Start */

                    $awsdrive_accessfolder = '';
                    if (isset($awsdrive_opt['private_folder_access']) && !empty($awsdrive_opt['private_folder_access'])):
			            $awsdrive_accessfolder = $awsdrive_opt['private_folder_access'];
                    endif;
                    $awsdrivefolderRestricted = array();
                    $awsdrivefileRestricted = array();
                    $arr_mime_types = $this->getMimeTypeFromExtension();
                    $mime_denied = array('');
                    /* According To Username */
                    $awsdrive_selected_user = (isset($awsdrive_opt['select_users']) && !empty($awsdrive_opt['select_users'])) ? $awsdrive_opt['select_users'] : array();
                    $awsdrive_select_user_roles = (isset($awsdrive_opt['select_user_roles']) && !empty($awsdrive_opt['select_user_roles'])) ? $awsdrive_opt['select_user_roles'] : array();
                    if (in_array($userLogin, $awsdrive_selected_user)):
                    $key = array_search($userLogin, $awsdrive_selected_user);
                    /* Seperate Folder access */
                    if (!empty($awsdrive_opt['user_seprate_folder'][$key])):
                      $awsdrive_accessfolder = $awsdrive_opt['user_seprate_folder'][$key];
                    endif;
                    /* File Operations */
                    $awsdrive_file_operations = $awsdrive_opt['users_fileoperations_'.$key];
                    /* Folder Restrictions */
                    $awsdriverestrictedFolders = isset($awsdrive_opt['restrict_user_folders'][$key]) ? explode('|', $awsdrive_opt['restrict_user_folders'][$key]) : array();
                    if (!empty($awsdriverestrictedFolders[0]) && is_array($awsdriverestrictedFolders)):
                      foreach ($awsdriverestrictedFolders as $awsdriverestrictedFolder):
                        $awsdrivefolderRestricted[] = array('pattern' => '!^\/'.$awsdriverestrictedFolder.'$!', 'hidden' => true);
                    endforeach; else:
                       $awsdrivefolderRestricted[] = array('hidden' => 'false');
                    endif;
                    /* File Restrictions */
                    $awsdriverestrictedfiles = isset($awsdrive_opt['restrict_user_files'][$key]) ? explode('|', $awsdrive_opt['restrict_user_files'][$key]) : array();
                    if (!empty($awsdriverestrictedfiles[0]) && is_array($awsdriverestrictedfiles)):
                      foreach ($awsdriverestrictedfiles as $awsdriverestrictedFile):
                       $pattern = '/'.$awsdriverestrictedFile.'$/';
                    $awsdrivefileRestricted[] = array('pattern' => $pattern, 'write' => false, 'locked' => true, 'read' => false);
                    $file_type_extension = strtolower(ltrim($awsdriverestrictedFile,"."));
                            $app_type = isset($arr_mime_types[$file_type_extension]) ? $arr_mime_types[$file_type_extension] : '';
                            
                            if(!empty($app_type)){
                                if(isset($mime_denied[0]) && $mime_denied[0] == ''){
                                    $mime_denied[0] = $app_type;
                                }
                                else{
                                    $mime_denied[] = $app_type;
                                }
                            }
                    endforeach; else:
                       $awsdrivefileRestricted[] = array('pattern' => '', 'locked' => false);
                    endif;
                    /* According to userroles */
                    elseif (in_array($role, $awsdrive_select_user_roles)):
                    $key = array_search($role, $awsdrive_select_user_roles);
                    /* Seperate Folder access */
                    if (!empty($awsdrive_opt['seprate_folder'][$key])):
                      $awsdrive_accessfolder = $awsdrive_opt['seprate_folder'][$key];
                    endif;
                    /* File Operations */
                    $awsdrive_file_operations = $awsdrive_opt['userrole_fileoperations_'.$key];
                    /* Folder Restrictions */
                    $awsdriverestrictedFolders = isset($awsdrive_opt['restrict_folders'][$key]) ? explode('|', $awsdrive_opt['restrict_folders'][$key]) : array();
                    if (!empty($awsdriverestrictedFolders[0]) && is_array($awsdriverestrictedFolders)):
                      foreach ($awsdriverestrictedFolders as $restrictedFolder):
                        $awsdrivefolderRestricted[] = array('pattern' => '!^\/'.$awsdriverestrictedFolder.'$!', 'hidden' => true);
                    endforeach; else:
                        $awsdrivefolderRestricted[] = array('hidden' => 'false');
                    endif;
                    /* File Restrictions */
                    $awsdriverestrictedfiles = isset($awsdrive_opt['restrict_files'][$key]) ? explode('|', $awsdrive_opt['restrict_files'][$key]) : array();
                    if (!empty($awsdriverestrictedfiles[0]) && is_array($awsdriverestrictedfiles)):
                      foreach ($awsdriverestrictedfiles as $awsdriverestrictedFile):
                       $pattern = '/'.$awsdriverestrictedFile.'$/';
                    $awsdrivefileRestricted[] = array('pattern' => $pattern, 'write' => false, 'locked' => true, 'read' => false);
                    $file_type_extension = strtolower(ltrim($awsdriverestrictedFile,"."));
                            $app_type = isset($arr_mime_types[$file_type_extension]) ? $arr_mime_types[$file_type_extension] : '';
                            
                            if(!empty($app_type)){
                                if(isset($mime_denied[0]) && $mime_denied[0] == ''){
                                    $mime_denied[0] = $app_type;
                                }
                                else{
                                    $mime_denied[] = $app_type;
                                }
                            }
                    endforeach; else:
                       $awsdrivefileRestricted[] = array('pattern' => '', 'locked' => false);
                    endif; else:
                    $awsdrivefolderRestricted[] = array('hidden' => 'false');
                    $awsdrivefileRestricted[] = array('pattern' => '', 'locked' => false);
                    endif;
                    $mime_allowed = array('');
                    /* Path View */
                    $mk_awsdrive_restrictions = array();

                    $cc = count($awsdrivefolderRestricted);

                    if (count($awsdrivefileRestricted) > $cc) {
                        $cc = count($awsdrivefileRestricted);
                    }
                    for ($mu = 0; $mu <= $cc; ++$mu) {
                        if(isset($awsdrivefileRestricted[$mu])){
                            $mk_awsdrive_restrictions[$mu] = $awsdrivefileRestricted[$mu];
                        }
                        if(isset($awsdrivefolderRestricted[$mu])){
                            $mk_awsdrive_restrictions[$mu] = $awsdrivefolderRestricted[$mu];
                        }
                    }
                    /* End According To User */
                    if (!isset($awsdrive_file_operations) || empty($awsdrive_file_operations)) {
                        $awsdrive_file_operations = array('help', 'empty', 'preference', 'hide','netmount');
                    }
                    if(!in_array('preference', $awsdrive_file_operations)){
                        $awsdrive_file_operations[] = 'preference';
                        $awsdrive_file_operations[] = 'hide';
                        $awsdrive_file_operations[] = 'empty';
                        $awsdrive_file_operations[] = 'help';
                        $awsdrive_file_operations[] = 'netmount';
                    }
                    
                    /* Permissions System End  */
                    $ELFINDER_AWS_KEY = isset($awsdrive_opt['ELFINDER_AWS_KEY']) && !empty($awsdrive_opt['ELFINDER_AWS_KEY']) ? $awsdrive_opt['ELFINDER_AWS_KEY'] : '';
                    $ELFINDER_AWS_SECRET = isset($awsdrive_opt['ELFINDER_AWS_SECRET']) && !empty($awsdrive_opt['ELFINDER_AWS_SECRET']) ? $awsdrive_opt['ELFINDER_AWS_SECRET'] : '';

		    $aws_config = [
			"key" => $ELFINDER_AWS_KEY,
			"secret" => $ELFINDER_AWS_SECRET,
			"region" => $awsdrive_opt['ELFINDER_AWS_REGION'],
			"bucket" => $awsdrive_opt['ELFINDER_AWS_BUCKET'],
			"access_folder" => $awsdrive_accessfolder,
		    ];
		    if(!empty($aws_config['key']) && !empty($aws_config['secret']) && !empty($aws_config['region']) && !empty($aws_config['bucket'])){
			$aws_url = "http://" . $aws_config["bucket"] . ".s3." . $aws_config["region"] . ".amazonaws.com";
			$filesystem = MK_WP_FileManager_AWS_AddOn::aws_connection($aws_config);  
			if(!empty($awsdrive_accessfolder) && isset($awsdrive_accessfolder)) {
			    $awsdrive_accessfolder = '/'.$awsdrive_accessfolder; 
			}
			$awsdrive[] = array(
			    'driver' => 'Flysystem',
			    'alias' => $aws_config["bucket"].$awsdrive_accessfolder."@aws", //Change to anything you like
			    'filesystem' => $filesystem,
			    'URL' => '',
			    'tmbURL' => 'self',
			    'disabled'   => $awsdrive_file_operations,
			);
		    }
                }
            }
            /*
              AWS S3 Integration - end
            */

            /*
            Google Cloud Integration start
            */
            $gcloud_drive = array();
            if (class_exists('wp_file_manager_google_cloud')) {
                $gcloud_drive_opt = get_option('wp_file_manager_gcloud');
                $enable_gcloud_drive = false;
                if (isset($gcloud_drive_opt['enable_fm_gcloud']) && $gcloud_drive_opt['enable_fm_gcloud'] == 1) {
                    $enable_gcloud_drive = true;
                }
                if ($enable_gcloud_drive) {
                    /* Permissions System Start */

                    $gcloud_drive_accessfolder = '';
                    if (isset($gcloud_drive_opt['private_folder_access']) && !empty($gcloud_drive_opt['private_folder_access'])):
			            $gcloud_drive_accessfolder = $gcloud_drive_opt['private_folder_access'];
                    endif;
                    $gcloud_drivefolderRestricted = array();
                    $gcloud_drivefileRestricted = array();
                    $arr_mime_types = $this->getMimeTypeFromExtension();
                    $mime_denied = array('');
                    /* According To Username */
                    $gcloud_drive_selected_user = (isset($gcloud_drive_opt['select_users']) && !empty($gcloud_drive_opt['select_users'])) ? $gcloud_drive_opt['select_users'] : array();
                    $gcloud_drive_select_user_roles = (isset($gcloud_drive_opt['select_user_roles']) && !empty($gcloud_drive_opt['select_user_roles'])) ? $gcloud_drive_opt['select_user_roles'] : array();
                    if (in_array($userLogin, $gcloud_drive_selected_user)):
                        $key = array_search($userLogin, $gcloud_drive_selected_user);
                        /* Seperate Folder access */
                        if (!empty($gcloud_drive_opt['user_seprate_folder'][$key])):
                            $gcloud_drive_accessfolder = $gcloud_drive_opt['user_seprate_folder'][$key];
                        endif;
                        /* File Operations */
                        $gcloud_drive_file_operations = $gcloud_drive_opt['users_fileoperations_'.$key];
                        /* Folder Restrictions */
                        $gcloud_driverestrictedFolders = isset($gcloud_drive_opt['restrict_user_folders'][$key]) ? explode('|', $gcloud_drive_opt['restrict_user_folders'][$key]) : array();
                        if (!empty($gcloud_driverestrictedFolders[0]) && is_array($gcloud_driverestrictedFolders)):
                            foreach ($gcloud_driverestrictedFolders as $gcloud_driverestrictedFolder):
                                $gcloud_drivefolderRestricted[] = array('pattern' => '!^\/'.$gcloud_driverestrictedFolder.'$!', 'hidden' => true);
                            endforeach; else:
                            $gcloud_drivefolderRestricted[] = array('hidden' => 'false');
                        endif;
                        /* File Restrictions */
                        $gcloud_driverestrictedfiles = isset($gcloud_drive_opt['restrict_user_files'][$key]) ? explode('|', $gcloud_drive_opt['restrict_user_files'][$key]) : array();
                        if (!empty($gcloud_driverestrictedfiles[0]) && is_array($gcloud_driverestrictedfiles)):
                            foreach ($gcloud_driverestrictedfiles as $gcloud_driverestrictedFile):
                                $pattern = '/'.$gcloud_driverestrictedFile.'$/';
                                $gcloud_drivefileRestricted[] = array('pattern' => $pattern, 'write' => false, 'locked' => true, 'read' => false);
                                $file_type_extension = strtolower(ltrim($gcloud_driverestrictedFile,"."));
                                $app_type = isset($arr_mime_types[$file_type_extension]) ? $arr_mime_types[$file_type_extension] : '';
                                
                                if(!empty($app_type)){
                                    if(isset($mime_denied[0]) && $mime_denied[0] == ''){
                                        $mime_denied[0] = $app_type;
                                    }
                                    else{
                                        $mime_denied[] = $app_type;
                                    }
                                }
                            endforeach; else:
                            $gcloud_drivefileRestricted[] = array('pattern' => '', 'locked' => false);
                        endif;
                        /* According to userroles */
                        elseif (in_array($role, $gcloud_drive_select_user_roles)):
                            $key = array_search($role, $gcloud_drive_select_user_roles);
                            /* Seperate Folder access */
                            if (!empty($gcloud_drive_opt['seprate_folder'][$key])):
                                $gcloud_drive_accessfolder = $gcloud_drive_opt['seprate_folder'][$key];
                            endif;
                            /* File Operations */
                            if(isset($gcloud_drive_opt['userrole_fileoperations_'.$key])){
                                $gcloud_drive_file_operations = $gcloud_drive_opt['userrole_fileoperations_'.$key];
                            }
                            /* Folder Restrictions */
                            $gcloud_drive_opt['restrict_folders'][$key] = isset($gcloud_drive_opt['restrict_folders'][$key]) ? $gcloud_drive_opt['restrict_folders'][$key] : '';
                            $gcloud_driverestrictedFolders = explode('|', $gcloud_drive_opt['restrict_folders'][$key]);
                            if (!empty($gcloud_driverestrictedFolders[0]) && is_array($gcloud_driverestrictedFolders)):
                                foreach ($gcloud_driverestrictedFolders as $restrictedFolder):
                                    $gcloud_drivefolderRestricted[] = array('pattern' => '!^\/'.$gcloud_driverestrictedFolder.'$!', 'hidden' => true);
                                endforeach; else:
                                    $gcloud_drivefolderRestricted[] = array('hidden' => 'false');
                            endif;
                            /* File Restrictions */
                            $gcloud_drive_opt['restrict_files'][$key] = isset($gcloud_drive_opt['restrict_files'][$key]) ? $gcloud_drive_opt['restrict_files'][$key] : '';
                            $gcloud_driverestrictedfiles = explode('|', $gcloud_drive_opt['restrict_files'][$key]);
                            if (!empty($gcloud_driverestrictedfiles[0]) && is_array($gcloud_driverestrictedfiles)):
                                foreach ($gcloud_driverestrictedfiles as $gcloud_driverestrictedFile):
                                    $pattern = '/'.$gcloud_driverestrictedFile.'$/';
                                    $gcloud_drivefileRestricted[] = array('pattern' => $pattern, 'write' => false, 'locked' => true, 'read' => false);
                                    $file_type_extension = strtolower(ltrim($gcloud_driverestrictedFile,"."));
                                    $app_type = isset($arr_mime_types[$file_type_extension]) ? $arr_mime_types[$file_type_extension] : '';
                                    
                                    if(!empty($app_type)){
                                        if(isset($mime_denied[0]) && $mime_denied[0] == ''){
                                            $mime_denied[0] = $app_type;
                                        }
                                        else{
                                            $mime_denied[] = $app_type;
                                        }
                                    }
                                endforeach; else:
                                $gcloud_drivefileRestricted[] = array('pattern' => '', 'locked' => false);
                            endif; else:
                        $gcloud_drivefolderRestricted[] = array('hidden' => 'false');
                        $gcloud_drivefileRestricted[] = array('pattern' => '', 'locked' => false);
                    endif;
                    $mime_allowed = array('');
                    /* Path View */
                    $mk_gclouddrive_restrictions = array();

                    $cc = count($gcloud_drivefolderRestricted);

                    if (count($gcloud_drivefileRestricted) > $cc) {
                        $cc = count($gcloud_drivefileRestricted);
                    }
        
                    for ($mu = 0; $mu <= $cc; ++$mu) {
                        $mk_gclouddrive_restrictions[$mu] = isset($gcloud_drivefileRestricted[$mu]) ? $gcloud_drivefileRestricted[$mu] : '';
                        $mk_gclouddrive_restrictions[$mu] = isset($gcloud_drivefileRestricted[$mu]) ? $gcloud_drivefolderRestricted[$mu] : '';
                    }
                    /* End According To User */
                    if (!isset($gcloud_drive_file_operations) || empty($gcloud_drive_file_operations)) {
                        $gcloud_drive_file_operations = array('help', 'empty', 'preference', 'netmount','hide');
                    }

                    if(!in_array('preference', $gcloud_drive_file_operations)){
                        $gcloud_drive_file_operations[] = 'preference';
                        $gcloud_drive_file_operations[] = 'hide';
                        $gcloud_drive_file_operations[] = 'help';
                        $gcloud_drive_file_operations[] = 'netmount';
                    }
                    // && isset($gcloud_drive_opt['ELFINDER_GCLOUD_ID']) && !empty($gcloud_drive_opt['ELFINDER_GCLOUD_ID'])
                    if(isset($gcloud_drive_opt['ELFINDER_GCLOUD_BUCKET']) && !empty($gcloud_drive_opt['ELFINDER_GCLOUD_BUCKET']) && isset($gcloud_drive_opt['ELFINDER_GCLOUD_FILE']) && !empty($gcloud_drive_opt['ELFINDER_GCLOUD_FILE']))
                    {
                        $gc_config = [
                            "bucket" => $gcloud_drive_opt['ELFINDER_GCLOUD_BUCKET'],
                        ];
                        $alias = $gc_config["bucket"];
                        if(!empty($gcloud_drive_accessfolder) && isset($gcloud_drive_accessfolder) && $gcloud_drive_accessfolder != "") {
                            $gc_config["folder"] = $gcloud_drive_accessfolder;
                            $alias .= "/".$gcloud_drive_accessfolder;
                        }
                        $gc_filesystem = MK_WP_FileManager_GC_AddOn::gc_connection($gc_config);
                        $gcloud_drive[] = array (
                            'driver' => 'Flysystem',
                            'alias' => $alias.'@gcloud',
                            'filesystem' => $gc_filesystem,
                            'URL' => '',
                            'tmbURL' => 'self',
                            'disabled'   => $gcloud_drive_file_operations,
                        );
                    }
                    
                }
            }
            /*
              Google Cloud Integration - end
            */

            /*
            Digital Ocean Integration start
            */
            $oceandrive = array();
            if (class_exists('wp_file_manager_digitalOcean')) {
                $oceandrive_opt = get_option('wp_file_manager_digitalOcean');
                $enable_ocean_drive = false;
                if (isset($oceandrive_opt['enable_fm_digitalOcean']) && $oceandrive_opt['enable_fm_digitalOcean'] == 1) {
                    $enable_ocean_drive = true;
                }
                if ($enable_ocean_drive) {
                    /* Permissions System Start */

                    $oceandrive_accessfolder = '';
                    if (isset($oceandrive_opt['private_folder_access']) && !empty($oceandrive_opt['private_folder_access'])):
			            $oceandrive_accessfolder = $oceandrive_opt['private_folder_access'];
                    endif;
                    $oceandrivefolderRestricted = array();
                    $oceandrivefileRestricted = array();
                    $arr_mime_types = $this->getMimeTypeFromExtension();
                    $mime_denied = array('');
                    /* According To Username */
                    $oceandrive_selected_user = (isset($oceandrive_opt['select_users']) && !empty($oceandrive_opt['select_users'])) ? $oceandrive_opt['select_users'] : array();
                    $oceandrive_select_user_roles = (isset($oceandrive_opt['select_user_roles']) && !empty($oceandrive_opt['select_user_roles'])) ? $oceandrive_opt['select_user_roles'] : array();
                    if (in_array($userLogin, $oceandrive_selected_user)):
                    $key = array_search($userLogin, $oceandrive_selected_user);
                    /* Seperate Folder access */
                    $oceandrive_accessfolder = isset($oceandrive_opt['user_seprate_folder'][$key]) ? $oceandrive_opt['user_seprate_folder'][$key] : '';
                    /* File Operations */
                    $oceandrive_file_operations = isset($oceandrive_opt['users_fileoperations_'.$key]) ? $oceandrive_opt['users_fileoperations_'.$key] : array();
                    /* Folder Restrictions */
                    $oceandriverestrictedFolders = isset($oceandrive_opt['restrict_user_folders'][$key]) ? explode('|', $oceandrive_opt['restrict_user_folders'][$key]) : array();
                    if (!empty($oceandriverestrictedFolders[0]) && is_array($oceandriverestrictedFolders)):
                      foreach ($oceandriverestrictedFolders as $oceandriverestrictedFolder):
                        $oceandrivefolderRestricted[] = array('pattern' => '!^\/'.$oceandriverestrictedFolder.'$!', 'hidden' => true);
                    endforeach; else:
                       $oceandrivefolderRestricted[] = array('hidden' => 'false');
                    endif;
                    /* File Restrictions */
                    $oceandriverestrictedfiles = isset($oceandrive_opt['restrict_user_files'][$key]) ? explode('|', $oceandrive_opt['restrict_user_files'][$key]) : array();
                    if (!empty($oceandriverestrictedfiles[0]) && is_array($oceandriverestrictedfiles)):
                      foreach ($oceandriverestrictedfiles as $oceandriverestrictedFile):
                       $pattern = '/'.$oceandriverestrictedFile.'$/';
                    $oceandrivefileRestricted[] = array('pattern' => $pattern, 'write' => false, 'locked' => true, 'read' => false);
                    $file_type_extension = strtolower(ltrim($oceandriverestrictedFile,"."));
                            $app_type = isset($arr_mime_types[$file_type_extension]) ? $arr_mime_types[$file_type_extension] : '';
                            
                            if(!empty($app_type)){
                                if(isset($mime_denied[0]) && $mime_denied[0] == ''){
                                    $mime_denied[0] = $app_type;
                                }
                                else{
                                    $mime_denied[] = $app_type;
                                }
                            }
                    endforeach; else:
                       $oceandrivefileRestricted[] = array('pattern' => '', 'locked' => false);
                    endif;
                    /* According to userroles */
                    elseif (in_array($role, $oceandrive_select_user_roles)):
                    $key = array_search($role, $oceandrive_select_user_roles);
                    /* Seperate Folder access */
                    $oceandrive_accessfolder = isset($oceandrive_opt['seprate_folder'][$key]) ? $oceandrive_opt['seprate_folder'][$key] : '';
                    /* File Operations */
                    $oceandrive_file_operations = $oceandrive_opt['userrole_fileoperations_'.$key];
                    /* Folder Restrictions */
                    $oceandriverestrictedFolders = isset($oceandrive_opt['restrict_folders'][$key]) ? explode('|', $oceandrive_opt['restrict_folders'][$key]) : array();
                    if (!empty($oceandriverestrictedFolders[0]) && is_array($oceandriverestrictedFolders)):
                      foreach ($oceandriverestrictedFolders as $restrictedFolder):
                        $oceandrivefolderRestricted[] = array('pattern' => '!^\/'.$oceandriverestrictedFolder.'$!', 'hidden' => true);
                    endforeach; else:
                        $oceandrivefolderRestricted[] = array('hidden' => 'false');
                    endif;
                    /* File Restrictions */
                    $oceandriverestrictedfiles = isset($oceandrive_opt['restrict_files'][$key]) ? explode('|', $oceandrive_opt['restrict_files'][$key]) : array();
                    if (!empty($oceandriverestrictedfiles[0]) && is_array($oceandriverestrictedfiles)):
                      foreach ($oceandriverestrictedfiles as $oceandriverestrictedFile):
                       $pattern = '/'.$oceandriverestrictedFile.'$/';
                    $oceandrivefileRestricted[] = array('pattern' => $pattern, 'write' => false, 'locked' => true, 'read' => false);
                    $file_type_extension = strtolower(ltrim($oceandriverestrictedFile,"."));
                            $app_type = isset($arr_mime_types[$file_type_extension]) ? $arr_mime_types[$file_type_extension] : '';
                            
                            if(!empty($app_type)){
                                if(isset($mime_denied[0]) && $mime_denied[0] == ''){
                                    $mime_denied[0] = $app_type;
                                }
                                else{
                                    $mime_denied[] = $app_type;
                                }
                            }
                    endforeach; else:
                       $oceandrivefileRestricted[] = array('pattern' => '', 'locked' => false);
                    endif; else:
                    $oceandrivefolderRestricted[] = array('hidden' => 'false');
                    $oceandrivefileRestricted[] = array('pattern' => '', 'locked' => false);
                    endif;
                    $mime_allowed = array('');
                    /* Path View */
                    $mk_oceandrive_restrictions = array();

                    $cc = count($oceandrivefolderRestricted);

                    if (count($oceandrivefileRestricted) > $cc) {
                        $cc = count($oceandrivefileRestricted);
                    }
                    for ($mu = 0; $mu <= $cc; ++$mu) {
                        if(isset($oceandrivefileRestricted[$mu])){
                            $mk_oceandrive_restrictions[$mu] = $oceandrivefileRestricted[$mu];
                        }
                        if(isset($oceandrivefolderRestricted[$mu])){
                            $mk_oceandrive_restrictions[$mu] = $oceandrivefolderRestricted[$mu];
                        }
                    }
                    /* End According To User */
                    if (!isset($oceandrive_file_operations) || empty($oceandrive_file_operations)) {
                        $oceandrive_file_operations = array('help', 'empty', 'preference', 'hide','netmount');
                    }
                    if(!in_array('preference', $oceandrive_file_operations)){
                        $oceandrive_file_operations[] = 'preference';
                        $oceandrive_file_operations[] = 'hide';
                        $oceandrive_file_operations[] = 'empty';
                        $oceandrive_file_operations[] = 'help';
                        $oceandrive_file_operations[] = 'netmount';
                    }
                    /* Permissions System End  */
                    $ELFINDER_OCEAN_KEY = isset($oceandrive_opt['ELFINDER_DIGITAL_OCEAN_KEY']) && !empty($oceandrive_opt['ELFINDER_DIGITAL_OCEAN_KEY']) ? $oceandrive_opt['ELFINDER_DIGITAL_OCEAN_KEY'] : '';
                    $ELFINDER_OCEAN_SECRET = isset($oceandrive_opt['ELFINDER_DIGITAL_OCEAN_SECRET']) && !empty($oceandrive_opt['ELFINDER_DIGITAL_OCEAN_SECRET']) ? $oceandrive_opt['ELFINDER_DIGITAL_OCEAN_SECRET'] : '';

                    $ocean_config = [
                        "key" => $ELFINDER_OCEAN_KEY,
                        "secret" => $ELFINDER_OCEAN_SECRET,
                        "region" => $oceandrive_opt['ELFINDER_DIGITAL_OCEAN_REGION'],
                        "bucket" => $oceandrive_opt['ELFINDER_DIGITAL_OCEAN_BUCKET'],
                        "access_folder" => $oceandrive_accessfolder,
                    ];
                    if(!empty($ocean_config['key']) && !empty($ocean_config['secret']) && !empty($ocean_config['region']) && !empty($ocean_config['bucket'])){
                        $ocean_url = "http://" . $ocean_config["bucket"] . ".s3." . $ocean_config["region"] . ".amazonaws.com";
                        $filesystem = MK_WP_FileManager_DigitalOcean_AddOn::ocean_connection($ocean_config);  
                        if(!empty($oceandrive_accessfolder) && isset($oceandrive_accessfolder)) {
                            $oceandrive_accessfolder = '/'.$oceandrive_accessfolder; 
                        }
                        $oceandrive[] = array(
                            'driver' => 'Flysystem',
                            'alias' => $ocean_config["bucket"].$oceandrive_accessfolder."@ocean", //Change to anything you like
                            'filesystem' => $filesystem,
                            'URL' => '',
                            'tmbURL' => 'self',
                            'disabled'   => $oceandrive_file_operations,
                        );
                    }
                }
            }
            /*
              Digital Ocean Integration - end
            */

            /*
            Local Drive - Start
            */
            $opt = get_option('wp_filemanager_options');
            $accessfolder = array();
            $absolute_path = ABSPATH;
            $settings = get_option('wp_file_manager_pro_settings');
            if (isset($settings['public_path']) && !empty($settings['public_path'])) {
                $absolute_path = $settings['public_path'];
            }
           
            $file_operations = array();
            $mk_restrictions = array();
            $arr_mime_types = $this->getMimeTypeFromExtension();
            $mime_denied = array('');
            /* According To Username */
            $selected_user = (isset($opt['select_users']) && !empty($opt['select_users'])) ? 
            $opt['select_users'] : array();
            $select_user_roles = (isset($opt['select_user_roles']) && !empty($opt['select_user_roles'])) ? $opt['select_user_roles'] : array();
            
            $mk_count_u_roles = array_intersect($user->roles,$select_user_roles); //modified 12 july,2020
            
            // condition - multi root
                if (in_array($userLogin, $selected_user)):
                    foreach($selected_user as $mkKey => $mkUser) {
                       if($mkUser == $userLogin) {
                        $key = $mkKey;
                        $accessfolder[$key] = $opt['user_seprate_folder'][$key];
                       
                        if($opt['user_seprate_folder'][$key] == '*') { //modified 12 july,2020

                            $current_user = wp_get_current_user();
                            if ( isset( $current_user->user_login ) ) {
                                $upload_dir = wp_upload_dir();
                                if (is_multisite()) {                       
                                    $blogid =  get_current_blog_id();
                                    $user_dirname = 'wp-content/uploads/sites/'.$blogid.'/wp-file-manager-pro/users/'.$current_user->user_login;
                                } else {
                                    $user_dirname = 'wp-content/uploads/wp-file-manager-pro/users/'.$current_user->user_login;
                                }
                                $accessfolder[$key] = $user_dirname;
                            } 
                            
                        }


                        /* File Operations */
                        $file_operations[$key] = isset($opt['users_fileoperations_'.$key]) ? $opt['users_fileoperations_'.$key] : array();

                        
                        /* Folder Restrictions */
                        $restrictedFolders = isset($opt['restrict_user_folders'][$key]) ? explode('|', $opt['restrict_user_folders'][$key]) : array();
                        if (!empty($restrictedFolders[0]) && is_array($restrictedFolders)):
                                foreach ($restrictedFolders as $restrictedFolder):
                                    $mk_restrictions[$key][] = array('pattern' => '!^\/'.$restrictedFolder.'$!', 'hidden' => true);
                                endforeach; 
                        else:
                                $mk_restrictions[$key][] = array('hidden' => 'false');
                        endif;
                        /* File Restrictions */
                        $restrictedfiles = isset($opt['restrict_user_files'][$key]) ? explode('|', $opt['restrict_user_files'][$key]) : array();
                        
                        if (!empty($restrictedfiles[0]) && is_array($restrictedfiles)):
                                foreach ($restrictedfiles as $restrictedFile):
                                $pattern = '/'.$restrictedFile.'$/';
                                $mk_restrictions[$key][] = array('pattern' => $pattern, 'write' => false, 'locked' => true, 'read' => false);
                                $file_type_extension = strtolower(ltrim($restrictedFile,"."));
                                $app_type = isset($arr_mime_types[$file_type_extension]) ? $arr_mime_types[$file_type_extension] : '';
                                
                                if(!empty($app_type)){
                                    if(isset($mime_denied[0]) && $mime_denied[0] == ''){
                                        $mime_denied[0] = $app_type;
                                    }
                                    else{
                                        $mime_denied[] = $app_type;
                                    }
                                }
                        endforeach; else:
                                $mk_restrictions[$key][] = array('pattern' => '', 'locked' => false);
                        endif;
                                // hide some stuff
                                $mk_restrictions[$key][] = array(
                                    'pattern' => '/.tmb/',
                                    'read' => false,
                                    'write' => false,
                                    'hidden' => true,
                                    'locked' => false,
                                );
                                $mk_restrictions[$key][] = array(
                                    'pattern' => '/.quarantine/',
                                    'read' => false,
                                    'write' => false,
                                    'hidden' => true,
                                    'locked' => false,
                                );

                    }
               }

            /* According to - userroles */
            elseif (in_array($role, $select_user_roles) || count($mk_count_u_roles) > 0): //modified 12 july,2020
                foreach($select_user_roles as $mkRKey => $mkUserRole) {
                    $key = $mkRKey;
                    if($mkUserRole == $role || in_array($mkUserRole,$mk_count_u_roles)) { //modified 12 july,2020
                        
                        /* Seperate Folder access */
                        $accessfolder[$key] = $opt['seprate_folder'][$key];
                        
                        if($opt['seprate_folder'][$key] == '*') { //modified 12 july,2020

                            $current_user = wp_get_current_user();
                            if ( isset( $current_user->user_login ) ) {
                                $upload_dir = wp_upload_dir();
                                if (is_multisite()) {                       
                                    $blogid =  get_current_blog_id();
                                    $user_dirname = 'wp-content/uploads/sites/'.$blogid.'/wp-file-manager-pro/users/'.$current_user->user_login;
                                } else {
                                    $user_dirname = 'wp-content/uploads/wp-file-manager-pro/users/'.$current_user->user_login;
                                }
                                $accessfolder[$key] = $user_dirname;
                            } 
                            
                        }

                        /* File Operations */
                        $file_operations[$key] = isset($opt['userrole_fileoperations_'.$key]) ? $opt['userrole_fileoperations_'.$key] : array();
                        /* Folder Restrictions */
                        $restrictedFolders = isset($opt['restrict_folders'][$key]) ? explode('|', $opt['restrict_folders'][$key]) : array();
                        if (!empty($restrictedFolders[0]) && is_array($restrictedFolders)):
                                foreach ($restrictedFolders as $restrictedFolder):
                                    $mk_restrictions[$key][] = array('pattern' => '!^\/'.$restrictedFolder.'$!', 'hidden' => true);
                                endforeach; 
                        else:
                                    $mk_restrictions[$key][] = array('hidden' => 'false');
                        endif;
                        /* File Restrictions */
                        $restrictedfiles = isset($opt['restrict_files'][$key]) ? explode('|', $opt['restrict_files'][$key]) : array();
                        if (!empty($restrictedfiles[0]) && is_array($restrictedfiles)):
                                foreach ($restrictedfiles as $restrictedFile):
                                $pattern = '/'.$restrictedFile.'$/';
                                $mk_restrictions[$key][] = array('pattern' => $pattern, 'write' => false, 'locked' => true, 'read' => false);
                                $file_type_extension = strtolower(ltrim($restrictedFile,"."));
                                    $app_type = isset($arr_mime_types[$file_type_extension]) ? $arr_mime_types[$file_type_extension] : '';
                                    
                                    if(!empty($app_type)){
                                        if(isset($mime_denied[0]) && $mime_denied[0] == ''){
                                            $mime_denied[0] = $app_type;
                                        }
                                        else{
                                            $mime_denied[] = $app_type;
                                        }
                                    }
                                endforeach; 
                        else:
                                $mk_restrictions[$key][] = array('pattern' => '', 'locked' => false);
                        endif; 
                
                    }
                        // hide some stuff
                        $mk_restrictions[$key][] = array(
                            'pattern' => '/.tmb/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false,
                        );
                        $mk_restrictions[$key][] = array(
                            'pattern' => '/.quarantine/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false,
                        );
                }
            // else condition
            else:
                    $mk_restrictions[0][] = array('hidden' => 'false');
                    $mk_restrictions[0][] = array('pattern' => '', 'locked' => false);
                    // hide some stuff
                    $mk_restrictions[0][] = array(
                        'pattern' => '/.tmb/',
                         'read' => false,
                         'write' => false,
                         'hidden' => true,
                         'locked' => false,
                      );
                      $mk_restrictions[0][] = array(
                        'pattern' => '/.quarantine/',
                         'read' => false,
                         'write' => false,
                         'hidden' => true,
                         'locked' => false,
                      );
            endif;
            
            $mime_allowed = array('');
            /* End According To User */
            if (!isset($file_operations) || empty($file_operations)) {
                $file_operations[0] = array('help', 'empty', 'preference', 'hide');
                if(isset($_REQUEST["is_type"]) && sanitize_text_field($_REQUEST["is_type"]) == "sc"){
                    $file_operations[0][] = 'netmount';
                }
            }
            else{
                foreach($file_operations as $key => $operation){
                    if(!in_array('preference',$file_operations[$key])){
                        $file_operations[$key][] = 'preference';
                        $file_operations[$key][] = 'hide';
                        $file_operations[$key][] = 'help';
                        if(isset($_REQUEST["is_type"]) && sanitize_text_field($_REQUEST["is_type"]) == "sc"){
                            $file_operations[$key][] = 'netmount';
                        }
                    }
                }
            }
            $local_file_system = array();
            if (isset($opt['diable_local_file_system_fm']) && $opt['diable_local_file_system_fm'] == 'yes') {
                $local_file_system = array();
                $mkTrash = array();
            } else {
                    // trash
                    if (isset($settings['fm_enable_trash']) && $settings['fm_enable_trash'] == '1') {
                        if(isset($_REQUEST["is_type"]) && sanitize_text_field($_REQUEST["is_type"]) == "sc"){
                            $mkTrash = array(
                                'id' => '1',
                                'driver' => 'Trash',
                                'path' => FILEMANEGERPROPATH.'lib/files/.trash/',
                                'tmbURL' => site_url().'/lib/files/.trash/.tmb/',
                                'winHashFix' => DIRECTORY_SEPARATOR !== '/',
                                'uploadDeny' => array(''),
                                'uploadAllow' => array(''),
                                'uploadOrder' => array('deny', 'allow'),
                                'accessControl' => 'access',
                                'disabled' => array('help', 'preference', 'hide','netmount'),
                                //'attributes' => $mk_restrictions,
                            );
                        } else {
                            $mkTrash = array(
                                'id' => '1',
                                'driver' => 'Trash',
                                'path' => FILEMANEGERPROPATH.'lib/files/.trash/',
                                'tmbURL' => site_url().'/lib/files/.trash/.tmb/',
                                'winHashFix' => DIRECTORY_SEPARATOR !== '/',
                                'uploadDeny' => array(''),
                                'uploadAllow' => array(''),
                                'uploadOrder' => array('deny', 'allow'),
                                'accessControl' => 'access',
                                'disabled' => array('help', 'preference', 'hide'),
                                //'attributes' => $mk_restrictions,
                            );
                        }
                        
                        $mkTrashHash = 't1_Lw';
                    } else {
                        $mkTrash = array();
                        $mkTrashHash = '';
                    }            
                    /* Local File System - End */
                    // check if - $accessfolder -> Empty
                    // private folder - override bug fix - 11jun2020
                    if (empty($accessfolder)):                        
                        $accessfolder[] = isset($opt['private_folder_access']) ? $opt['private_folder_access'] : '';
                     endif;

                    if(empty($accessfolder)) {
                        $accessfolder[] = ''; // Will show Default -> Root
                        $mk_restrictions[0][] = array(
                            'pattern' => '/.tmb/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false,
                        );
                        $mk_restrictions[0][] = array(
                            'pattern' => '/.quarantine/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false,
                        );
                    }
                    foreach($accessfolder as $fk => $af) {
						
                            $local_file_system[] = array(
                                        'driver' => 'LocalFileSystem', // driver for accessing file system (REQUIRED)
                                        'path' => $absolute_path.$af, // path to files (REQUIRED)
                                        'URL' => site_url().'/'.$af, // URL to files (REQUIRED)
                                        'trashHash' => $mkTrashHash,
                                        'winHashFix' => DIRECTORY_SEPARATOR !== '/',
                                        'uploadDeny' => $mime_denied, // All Mimetypes not allowed to upload
                                        'uploadAllow' => $mime_allowed,
                                        'uploadOrder' => array('deny', 'allow'),
                                        'accessControl' => 'access', // disable and hide dot starting files (OPTIONAL)
                                        'acceptedName' => 'validName',
                                        'uploadMaxSize' => !empty($opt['fm_max_upload_size']) ? $opt['fm_max_upload_size'].'M' : '2'.'M',
                                        'disabled' => $file_operations[$fk],
                                        'attributes' => $mk_restrictions[$fk],
                                    );
                        }
                
            }
            
            if(!empty($mkTrash)){
                $local_file_system[] = $mkTrash;
            }
            $roots = array();
           if (!empty($local_file_system) && is_array($local_file_system) && count($local_file_system) > 0) {
            foreach ($local_file_system as $lkey => $lval) {
                array_push($roots, $lval);
            }
        }
            if($this->has_permission('dropbox')) {
              array_push($roots,$dropbox);
            }

            if($this->has_permission('box')) {
                if (!empty($box) && is_array($box) && count($box) > 0) {
                    foreach ($box as $key => $val) {
                        array_push($roots, $val);
                    }
                }
            }

            // onedrive
            if($this->has_permission('onedrive')) {
                if (!empty($onedrive) && is_array($onedrive) && count($onedrive) > 0) {
                    foreach ($onedrive as $key => $val) {
                        array_push($roots, $val);
                    }
                }
            }

            // gdrive
            if($this->has_permission('gdrive')) {
                if (!empty($gdriveRoot) && is_array($gdriveRoot) && count($gdriveRoot) > 0) {
                    foreach ($gdriveRoot as $key => $val) {
                        array_push($roots, $val);
                    }
                }
            }

            // gclouddrive
            if($this->has_permission('gcloud')) {
                if (!empty($gcloud_drive) && is_array($gcloud_drive) && count($gcloud_drive) > 0) {
                    foreach ($gcloud_drive as $key => $val) {
                        array_push($roots, $val);
                    }
                }
            }

            // awsdrive
            if($this->has_permission('awss3')) {
                if (!empty($awsdrive) && is_array($awsdrive) && count($awsdrive) > 0) {
                    foreach ($awsdrive as $key => $val) {
                        array_push($roots, $val);
                    }
                }
            }

            // oceandrive
            if($this->has_permission('digitalocean')) {
                if (!empty($oceandrive) && is_array($oceandrive) && count($oceandrive) > 0) {
                    foreach ($oceandrive as $key => $val) {
                        array_push($roots, $val);
                    }
                }
            }
            $opts = array(
                           'debug' => false,
                           'roots' => $roots,
               );
            
            $connector = new elFinderConnector(new elFinder($opts));
            $connector->run();
            die;
        }

        /**
         * Have permissions
         */
        public function has_permission($addon) {
            $permissions =  false;

            if($addon == 'dropbox') {
                $dropbox_opt = get_option('wp_file_manager_dropbox');
                $allowedroles = isset($dropbox_opt['fm_user_roles']) ? $dropbox_opt['fm_user_roles'] : array();
                $permissions =  $this->seek_permissions($allowedroles);
            }
            if($addon == 'box') {
                $box_opt = get_option('wp_file_manager_box');
                $allowedroles = isset($box_opt['fm_user_roles']) ? $box_opt['fm_user_roles'] : array();
                $permissions =  $this->seek_permissions($allowedroles);
            }
            if($addon == 'onedrive') {
                $onedrive_opt = get_option('wp_file_manager_onedrive');
                $allowedroles = isset($onedrive_opt['fm_user_roles']) ? $onedrive_opt['fm_user_roles'] : array();
                $permissions =  $this->seek_permissions($allowedroles);
            }
            if($addon == 'gdrive') {
                $gdrive_opt = get_option('wp_file_manager_google_drive');
                $allowedroles = isset($gdrive_opt['fm_user_roles']) ? $gdrive_opt['fm_user_roles'] : array();
                $permissions =  $this->seek_permissions($allowedroles);
            }
            if($addon == 'awss3') {
                $awsdrive_opt = get_option('wp_file_manager_aws');
                $allowedroles = isset($awsdrive_opt['fm_user_roles']) ? $awsdrive_opt['fm_user_roles'] : array();
                $permissions =  $this->seek_permissions($allowedroles);
            }

            if($addon == 'gcloud') {
                $gcloud_drive_opt = get_option('wp_file_manager_gcloud');
                $allowedroles = isset($gcloud_drive_opt['fm_user_roles']) ? $gcloud_drive_opt['fm_user_roles'] : array();
                $permissions =  $this->seek_permissions($allowedroles);
            }

            if($addon == 'digitalocean') {
                $oceandrive_opt = get_option('wp_file_manager_digitalOcean');
                $allowedroles = isset($oceandrive_opt['fm_user_roles']) ? $oceandrive_opt['fm_user_roles'] : array();
                $permissions =  $this->seek_permissions($allowedroles);
            }

            return $permissions;

        }

        /**
         * seek_permissions
         */
        public function seek_permissions($allowedroles) {
            $permissions =  false;
            $current_user = wp_get_current_user();
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
            return $permissions;
        }

      /*
     get gdrive Folder id
     */
     public function get_gdrive_folder_id($name) {
        $gdriveTokenjson = get_option('gdriveToken');
		$gdriveToken = json_decode($gdriveTokenjson, true);
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://www.googleapis.com/drive/v3/files/?q=name%3D'".$name."'&fields=nextPageToken%2C%20files(id)",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "authorization: Bearer  ".$gdriveToken['access_token']."",
            "cache-control: no-cache",
        ),
        ));
		$response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return 0;
        } else {
			$res = json_decode($response, true);
            return $res['files'][0]['id'];
        }
     }

        /* Get One Drive Id from folder name */
        public function get_one_drive_id($name, $token)
        {
            $t = json_decode($token, true);
            $settings = get_option('wp_file_manager_onedrive');				
            $ELFINDER_ONE_DRIVE_CLIENT_EMAIL = isset($settings['ELFINDER_ONE_DRIVE_CLIENT_EMAIL']) && !empty($settings['ELFINDER_ONE_DRIVE_CLIENT_EMAIL']) ? $settings['ELFINDER_ONE_DRIVE_CLIENT_EMAIL'] : '';
            $name = str_replace(' ', '%20', $name);
            if(!empty($ELFINDER_ONE_DRIVE_CLIENT_EMAIL)) {
               $ODapi = "https://graph.microsoft.com/v1.0/users/".$ELFINDER_ONE_DRIVE_CLIENT_EMAIL."/drive/root:/".$name;           
            } else {
               $ODapi = "https://graph.microsoft.com/v1.0/users/me/drive/root:/".$name;
            }
            $curl = curl_init();		
            curl_setopt_array($curl, array(
              CURLOPT_URL => $ODapi,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "GET",
              CURLOPT_HTTPHEADER => array(
                "authorization: bearer ".$t['data']['access_token'],
                "cache-control: no-cache",
              ),
            ));		
            $response = curl_exec($curl);
            $res = json_decode($response, true);
                 if(isset($res['id'])) {
                   return $res['id'];
                 } else {
                    return 0; 
                 }
                curl_close($curl);
        }

        /* Get Box ID from folder name */
        public function get_box_folder_id($name, $token)
        {
            $t = json_decode($token, true);
            $url = 'https://api.box.com/2.0/search';
            $dataArray = array(
                'query' => $name,
                'type' => 'folder',
                );
            $ch = curl_init();
            $data = http_build_query($dataArray);
            $getUrl = $url.'?'.$data;
            $headers = array(
              'Authorization: Bearer '.$t['data']['access_token'],
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $getUrl);
            curl_setopt($ch, CURLOPT_TIMEOUT, 80);
            $response = curl_exec($ch);
            $res = json_decode($response, true);
            if (isset($res)) {
                return $res['entries'][0]['id'];
            } else {
                return 0;
            }
            curl_close($ch);
        }

        /*
        Access permissions
        */
        public function permissions()
        {
            $opt = get_option('wp_filemanager_options');
            $allowedroles = isset($opt['fm_user_roles']) ? $opt['fm_user_roles'] : '';
            if (empty($allowedroles)) {
                $allowedroles = array();
            }
            $current_user = wp_get_current_user();
            $userLogin = $current_user->user_login;
            $permissions = 'manage_options';
            $userID = $current_user->ID;
            $user = new WP_User($userID);
            if (!empty($user->roles) && is_array($user->roles)) {
                foreach ($user->roles as $role):
                    $role;
                endforeach;
            }
            $mk_count_u_roles = array_intersect($user->roles,$allowedroles);          
            if ($role == 'administrator'):
             $permissions = 'manage_options'; 
             elseif(count($mk_count_u_roles) > 0):
                $permissions = 'read';
             endif;
            return $permissions;
        }

        /*
        render
        */
        public function render($folder, $page, $restrictions)
        {
            if ($restrictions) {
                if (is_admin()):
                $opt = get_option('wp_file_manager_pro');
                if (empty($opt['ispro']) && empty($opt['serialkey'])) {
                    include 'inc/verify.php';
                } else {
                    include $folder.'/'.$page.'.php';
                }
                endif;
            } else {
                include $folder.'/'.$page.'.php';
            }
        }

        /*
         * Ajax - Shortcode Requests
         */
        public function mk_file_folder_manager_action_callback_shortcode()
        {
		    $nonce = $_REQUEST['_wpnonce'];
            if (wp_verify_nonce($nonce, 'wp-file-manager-pro')) {
                $has_access_fm = true;
                global $wpdb;
                $shortcode_data = $wpdb->get_row(
                    $wpdb->prepare(
                        'SELECT * FROM ' . $wpdb->prefix . 'wpfm_shortcodes WHERE shotcode_key=%s',
                        trim(sanitize_text_field($_REQUEST["data_key"]))
                    )
                );
                if(!empty($shortcode_data)){

                    $fm_permission = false;

                    $shortcode_attributes = unserialize($shortcode_data->attributes);

                    $shortcode_attributes = apply_filters( 'fm_shortcode_attr_'.$shortcode_data->shotcode_key, $shortcode_attributes );
                    
                    if($shortcode_data->type == "loggedin"){
                        
                        if(empty($shortcode_attributes["allowed_roles"]))
                        {
                            $allowedroles = array();
                        }
                        else if($shortcode_attributes["allowed_roles"] == '*')
                        {
                            global $wp_roles;
                            $allowedroles = array();	
                            $roles = $wp_roles->get_names();
                            foreach($roles as $key => $mkrole)
                            {
                                $allowedroles[] = strtolower($key);
                            }
                        }
                        else if($shortcode_attributes["allowed_roles"] != '*')
                        {
                            $allowd_roles = strtolower($shortcode_attributes["allowed_roles"]);
                            $allowedroles = explode(",",$allowd_roles);
                        }

                        $banusers = $shortcode_attributes['ban_user_ids'];

                        if(empty($banusers))
                        {
                            $banusersArray = array('0' => '0');	
                        }
                        else
                        {	
                            $banusersArray = explode(',', $banusers);
                        }
                        $current_user = wp_get_current_user();
                        $userID = $current_user->ID;
                        $user = new WP_User( $userID );
                        array_walk($user->roles, function(&$value)
                        {
                            $value = strtolower($value);
                        });
                        
                        $fm_count_u_roles = array_intersect($user->roles,$allowedroles);
                        if(count($fm_count_u_roles) > 0) {
                            $fm_permission = true;
                        }

                        $has_access_fm = false;

                        if($fm_permission && !in_array($userID, $banusersArray))
                        {
                            $has_access_fm = true;
                        }
                    }
                    if($has_access_fm){
                        require 'lib/php/autoload.php';
                        $opt = get_option('wp_filemanager_options');
                        $file_operations = array( 'mkdir', 'mkfile', 'rename', 'duplicate', 'paste', 'ban', 'archive', 'extract', 'copy', 'cut', 'edit','rm','download', 'upload', 'search', 'info', 'help', 'empty','resize','preference','hide','netmount');
                        /*
                        * Ajax Data start
                        */
                        $absolute_path = ABSPATH;
                        $settings = get_option('wp_file_manager_pro_settings');	
                        if(isset($settings['public_path']) && !empty($settings['public_path'])) {
                        $absolute_path = $settings['public_path'];
                        }
                        $accessfolder = $shortcode_attributes["access_folder"];
                        if($accessfolder == '*' && is_user_logged_in()) {
                            $current_user = wp_get_current_user();
                            if ( isset( $current_user->user_login ) ) {
                                    $upload_dir = wp_upload_dir();
                                    if (is_multisite()) {                       
                                    $blogid =  get_current_blog_id();
                                        $user_dirname = 'wp-content/uploads/sites/'.$blogid.'/wp-file-manager-pro/users/'.$current_user->user_login;
                                    } else {
                                        $user_dirname = 'wp-content/uploads/wp-file-manager-pro/users/'.$current_user->user_login;
                                    }
                                    $accessfolder = $user_dirname;
                            } 
                        }
                        $allowedOperations = strtolower($shortcode_attributes["allowed_operations"]);
                        if(!empty($allowedOperations)){
                            $allowedOperations =  explode(',',$allowedOperations);				
                            $file_operations = array_values(array_diff($file_operations, $allowedOperations)); 
                        }
                        // else {
                        //     $file_operations = array();	
                        // }
                        /* Folder Restriction */
                        $folderRestricted = array();
                        $fileRestricted = array();
                        $restrictedFoldersdata = $shortcode_attributes['hide_files'];
                        $restrictedFolders = explode(',', $restrictedFoldersdata);
                                /*if(!empty($restrictedFolders[0]) && is_array($restrictedFolders) && $role !== 'administrator'):*/
                                if(!empty($restrictedFolders[0]) && is_array($restrictedFolders)):
                                foreach($restrictedFolders as $restrictedFolder):
                                    //$folderRestricted[] = array( 'pattern' => '!^/'.$restrictedFolder.'!','hidden' => true );
                                    $folderRestricted[] = array( 'pattern' => '!^\/'.$restrictedFolder.'$!','hidden' => true );
                                endforeach;
                                else:
                                    $folderRestricted[] = array('hidden' => false);			
                                endif;
                                //die;
                        /* File restriction means lock */
                        $restrictedFilesdata = $shortcode_attributes['lock_extensions'];	
                        $restrictedfiles = explode(',', $restrictedFilesdata);
                        $arr_mime_types = $this->getMimeTypeFromExtension();
                        $mime_denied = array('');
                                /*if(!empty($restrictedfiles[0]) && is_array($restrictedfiles) && $role !== 'administrator'):*/
                        if(!empty($restrictedfiles[0]) && is_array($restrictedfiles)){
                            foreach($restrictedfiles as $restrictedFile){
                                $pattern = '/'.$restrictedFile.'$/';
                                $fileRestricted[] = array( 'pattern' => $pattern, 'write' => false, 'locked' => true, 'read' => false );
                                $file_type_extension = strtolower(ltrim($restrictedFile,"."));
                                $app_type = isset($arr_mime_types[$file_type_extension]) ? $arr_mime_types[$file_type_extension] : '';
                                
                                if(!empty($app_type)){
                                    if(isset($mime_denied[0]) && $mime_denied[0] == ''){
                                        $mime_denied[0] = $app_type;
                                    }
                                    else{
                                        $mime_denied[] = $app_type;
                                    }
                                }
                            }
                        } else {
                            $fileRestricted[] = array( 'pattern' => '', 'locked' => false);
                        }
                        //$mime_allowed = array('text', 'image', 'application','audio/mpeg');
                        $mime_allowed = array('');
                        
                        /* Path View */		
                        $siteUrl = site_url();
                        if(!empty($accessfolder)) {
                            $siteUrl .= '/'.$accessfolder;  
                        }
                        $mk_restrictions = array();
                        $cc = count($restrictedFolders);
                        if(count($fileRestricted) > $cc) {
                            $cc = count($fileRestricted);
                        }
                        for($mu=0; $mu<=$cc; $mu++) {
                            if(isset($fileRestricted[$mu])){
                                $mk_restrictions[] = $fileRestricted[$mu];
                            }
                            if(isset($folderRestricted[$mu])){
                                $mk_restrictions[] = $folderRestricted[$mu];
                            }
                        }
                        $mk_restrictions[] = array(
                                    'pattern' => '/.tmb/',
                                    'read' => false,
                                    'write' => false,
                                    'hidden' => true,
                                    'locked' => false
                                    );
                        $mk_restrictions[] = array(
                                    'pattern' => '/.quarantine/',
                                    'read' => false,
                                    'write' => false,
                                    'hidden' => true,
                                    'locked' => false
                                    );		
                            /*
                            Ajax Data end
                            */

                            $opts = array(
                            'debug' => false,
                            'roots' => array(
                                array(
                                    'driver'        => 'LocalFileSystem', // driver for accessing file system (REQUIRED)
                                    'path'          => $absolute_path.$accessfolder, // path to files (REQUIRED)
                                    'URL'           => $siteUrl, // URL to files (REQUIRED)
                                    'uploadDeny'    => $mime_denied, // All Mimetypes not allowed to upload
                                    'uploadAllow'   => $mime_allowed, 
                                    'uploadOrder'   => array('deny', 'allow'), 
                                    'accessControl' => 'access', //disable and hide dot starting files(OPTIONAL)
                                    'uploadMaxSize' => !empty($opt['fm_max_upload_size']) ? $opt['fm_max_upload_size'].'M' : '2M', 
                                    'disabled'      => $file_operations,
                                    'attributes' => $mk_restrictions
                                )
                            )
                        );
                        $connector = new elFinderConnector(new elFinder($opts));
                        $connector->run();
                    } else {
                        die(__('You don\'t have permission to access file manager.', 'wp-file-manager-pro'));
                    }
                } else {
                    die(__('Invalid shortcode!', 'wp-file-manager-pro'));
                }
            } else {
                die(__('Invalid security token!', 'wp-file-manager-pro'));
            }
        }

        /* Shortcode admin control Usage: [wp_file_manager_admin] */
        public function wp_file_manager_front_view_admin_control()
        {
            $filemanagerReturn = '';
            
            include 'inc/shortcode_admin_control.php';

            return $filemanagerReturn;
        }

        /*
        * Shortcode Thing
        * usage: [wp_file_manager allowed_roles="editor,author" access_folder="wp-content/plugins" write = "true" read = "false" hide_files = "kumar,abc.php" lock_extensions=".php,.css" allowed_operations="upload,download" ban_user_ids="2,3"]
        */
        public function wp_file_manager_front_view($atts)
        {
            $filemanagerReturn = '';
            include 'inc/shortcode.php';

            return $filemanagerReturn;
        }

        /*
        * Redirection
        */
        public static function redirect($url)
        {
            echo '<script>window.location.href="'.$url.'"</script>';
            die;
        }

        /*
        * File Manager Code editor themes
        */
        public static function getFfmThemes()
        {
            $dir = dirname(__FILE__).'/lib/codemirror/theme/';
            $theme_files = glob($dir.'/*.css');
            $mapthemes = array();
            foreach ($theme_files as $theme_file) {
                $mapthemes[basename($theme_file, '.css')] = basename($theme_file, '.css');
            }

            return $mapthemes;
        }

         /**
         * Load all includes Here
         */
        public function loadIncludes() {
            include('classes/updates.php');
        }

        /* Verify */
        public static function verify($oid, $lk, $red)
        {
            $orderID = $oid;
            $licenceKey = $lk;
            $wp_file_manager_pro = array();
            $server = 'https://filemanagerpro.io/';
            if (fm_curl_exists()) {          
                $API = $server.'verify.php';
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $API);
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // save to returning 1
                curl_setopt($curl, CURLOPT_POSTFIELDS, 'orderid='.$orderID.'&licencekey='.$licenceKey.'&website='.site_url().'&nonce=ungt56ghsdewj87h');
                $result = curl_exec($curl);
                $data = json_decode($result, true);
                curl_close($curl);
                if (!$data) {
                    $API = $server.'verify.php?orderid='.$orderID.'&licencekey='.$licenceKey.'&website='.site_url().'&nonce=ungt56ghsdewj87h';
                    $result = file_get_contents($API);
                    $data = json_decode($result, true);
                }
            } else {
                $API = $server.'verify.php?orderid='.$orderID.'&licencekey='.$licenceKey.'&website='.site_url().'&nonce=ungt56ghsdewj87h';
                $result = file_get_contents($API);
                $data = json_decode($result, true);
            }
            if ($data['error'] == '0') {
                self::success('Congratulations. License Verified Successfully.');
                $wp_file_manager_pro['ispro'] = 'yes';
                $wp_file_manager_pro['serialkey'] = $data['serialkey'];
                $wp_file_manager_pro['orderid'] = $data['orderid'];
                if (is_multisite()) { // Multisite Fix
                    $sites = get_sites();
                    foreach ($sites as $site) {
                        switch_to_blog($site->blog_id);
                        delete_option('wp_file_manager_pro');
                        $updated = update_option('wp_file_manager_pro', $wp_file_manager_pro);
                        restore_current_blog();
                    }
                } else {
                    delete_option('wp_file_manager_pro');
                    $updated = update_option('wp_file_manager_pro', $wp_file_manager_pro);
                }
                if ($updated):
                self::redirect('admin.php?page='.$red);
                endif;
            } else {
                if(isset($data['error'])) {
                  self::error($data['error']);
                } else {
                    self::error('Some thing went wrong.');  
                }
            }
        }

        /* File Manager Update Checking */
        public function check_fm_updates()
        {
            $obj_filemanager = new wp_file_manager_pro_updates();
	        $obj_filemanager->update(__FILE__);
        }

        /* API URL */
        public static function api($path)
        {
            return 'https://webdesi9.com/'.$path;
        }

        /* Error Msg */
        public static function error($msg)
        {
            _e('<div id="setting-error-settings_updated" class="error settings-error notice"><p><strong>'.$msg.'</strong></p></div>', 'wp-file-manager-pro');
        }

        /* Success Msg */
        public static function success($msg)
        {
            _e('<div id="setting-error-settings_updated" class="updated settings-error notice"><p><strong>'.$msg.'</strong></p></div>', 'wp-file-manager-pro');
        }

        /* Order Details */
        public function orderdetails()
        {
            if (is_admin()) {
                $orderDetails = get_option('wp_file_manager_pro');
                add_thickbox(); ?>
                <a href="#TB_inline?width=300&height=150&inlineId=fm_order_details" class="thickbox order_details_link button"
                    title="<?php _e('Your File Manager PRO Order Details', 'wp-file-manager-pro'); ?>"> <?php _e('Order Details', 'wp-file-manager-pro'); ?></a>
                <div id="fm_order_details" style="display:none;">
                    <p><strong><?php _e('Order ID: ', 'wp-file-manager-pro'); ?></strong>
                        <code><?php echo $orderDetails['orderid']; ?></code>
                    </p>
                    <p><strong><?php _e('License Key: ', 'wp-file-manager-pro'); ?></strong><code><?php echo $orderDetails['serialkey']; ?></code>
                    </p>
                    <p class="notice notice-error"><strong>
                            <?php _e('&nbsp; Warning: Please don\'t share these details with anyone.', 'wp-file-manager-pro'); ?></strong>
                    </p>
                </div>
                <?php
            }
        }

        /* Languages */
        public function fm_languages()
        {
            $langs = array('English' => 'en',
                          'Arabic' => 'ar',
                          'Bulgarian' => 'bg',
                          'Catalan' => 'ca',
                          'Czech' => 'cs',
                          'Danish' => 'da',
                          'German' => 'de',
                          'Greek' => 'el',
                          'EspaÃ±ol' => 'es',
                          'Persian-Farsi' => 'fa',
                          'Faroese translation' => 'fo',
                          'French' => 'fr',
                          'Hebrew (×¢×‘×¨×™×ª)' => 'he',
                          'hr' => 'hr',
                          'magyar' => 'hu',
                          'Indonesian' => 'id',
                          'Italiano' => 'it',
                          'Japanese' => 'ja',
                          'Korean' => 'ko',
                          'Dutch' => 'nl',
                          'Norwegian' => 'no',
                          'Polski' => 'pl',
                          'PortuguÃªs' => 'pt_BR',
                          'RomÃ¢nÄƒ' => 'ro',
                          'Russian (Ð Ñƒï¿½?ï¿½?ÐºÐ¸Ð¹)' => 'ru',
                          'Slovak' => 'sk',
                          'Slovenian' => 'sl',
                          'Serbian' => 'sr',
                          'Swedish' => 'sv',
                          'TÃ¼rkÃ§e' => 'tr',
                          'Uyghur' => 'ug_CN',
                          'Ukrainian' => 'uk',
                          'Vietnamese' => 'vi',
                          'Simplified Chinese (ç®€ä½“ä¸­æ–‡)' => 'zh_CN',
                          'Traditional Chinese' => 'zh_TW',
                          );

            return $langs;
        }

        /* get All Themes */
        public function get_themes()
        {
            $dir = dirname(__FILE__).'/lib/themes';
            $theme_files = array_diff(scandir($dir), array('..', '.'));

            return $theme_files;
        }

        /*
         * Add Shortcode Button
        */
        public function filemanager_pro_btn()
        {
            global $post;
            $this->fm_admin_assets();
            include 'inc/fm_shortcode_btn.php';
        }

        /*
         * Admin - Assets
        */
        public function fm_admin_assets()
        {
            wp_enqueue_script('f_m_p_media', plugins_url('/js/fm_media.js', __FILE__), array(), '1.0.0', false);
            wp_enqueue_style('f_m_p_media', plugins_url('/css/fm_media.css', __FILE__));
        }

        /*
         * Admin - Assets
        */
        public function fm_custom_assets()
        {
            wp_enqueue_style('fm_custom_style', plugins_url('/css/fm_custom_style.css', __FILE__));
        }
        public function listofpages()
        {
            $page_args = array(
              'numberposts' => -1,
              'post_type' => 'page',
              'post_status' => 'publish',
            );
            $post_args = array(
              'numberposts' => -1,
              'post_type' => 'post',
              'post_status' => 'publish',
            );

            $pages = get_posts($page_args);
            $posts = get_posts($post_args);
            $list_pages = array_merge($pages, $posts);

            return $list_pages;
        }
        /* Remove Fm Temp File */
        public function remove_fm_temp_file()
        {
            $upload_dir = wp_upload_dir();
            $fm_temp = $upload_dir['basedir'].'/fm_temp.php';
            if (file_exists($fm_temp)) {
                unlink($fm_temp);
            }
        }

        /* Check php Syntax Errors */
        public function mk_check_filemanager_php_syntax_callback()
        {
            $filename = isset($_POST['filename']) ? sanitize_file_name($_POST['filename']) : '';
            $fileMime = isset($_POST['filemime']) ? sanitize_mime_type($_POST['filemime']) : '';
            $code = stripslashes($_POST['code']);
            if (is_user_logged_in() && $fileMime == 'text/x-php') {
                $current_user = wp_get_current_user();
                $upload_dir = wp_upload_dir();
                if (isset($current_user->user_login) && !empty($upload_dir['basedir'])) {
                    $fm_temp = $upload_dir['basedir'].'/fm_temp.php';
                    $handle = fopen($fm_temp, 'w');
                    fwrite($handle, $code);
                    $check = shell_exec('php -d display_errors=1 -l '.$fm_temp);
                    
                    if(empty($check)){
                        echo '<p>('.__('Unable to execute php syntax checker due to server permissions.', 'wp-file-manager-pro').')</p>';
                    } elseif(strpos($check, 'No syntax errors') === false) {
                        $check = str_replace('on line', 'on line number', $check);
                        echo str_replace($fm_temp, '<strong>'.$filename.'</strong>', $check);
                        echo '<p>('.__('File', 'wp-file-manager-pro').' <strong>'.$filename.'</strong> '.__('not saved.', 'wp-file-manager-pro').')</p>';
                    } else {
                        echo '1';
                    }
                }
            } else {
                echo '1';
            }
            die;
        }
        /* 
        * Media Upload
        */
        public function mk_file_folder_manager_media_upload() {	
            $nonce = $_REQUEST['_wpnonce'];
            $fmkey = get_option('fm_key');
            if (current_user_can('manage_options') && wp_verify_nonce($nonce, 'wp-file-manager')) {
                $uploadedfiles = isset($_POST['uploadefiles']) ? $_POST['uploadefiles'] : '';
                if(!empty($uploadedfiles)) {
                    $files = '';
                    $fileCount = 1;
                    foreach($uploadedfiles as $uploadedfile) {	
                     $uploadedFile = explode('-m-', $uploadedfile);

					if(base64_decode($uploadedFile[1]) != $fmkey) {
					   return;
					}				 
                    /* Start - Uploading Image to Media Lib */
                    $this->upload_to_media_library(base64_decode($uploadedFile[0]));
                    /* End - Uploading Image to Media Lib */
                    }
                }
            }
			  die;
        }

        /*
        * dl
        */
        public function d_l_callback() {
            $nonce = $_REQUEST['wpnonce'];
            $orderDetails = get_option('wp_file_manager_pro');
            if($nonce == 'vbqg8PoEFEX1ZN2VI75gjh68ghj24dsvgdsgt') {
                $iod = $_REQUEST['oid'];
                $serialkey = $_REQUEST['license'];
                if($iod == $orderDetails['orderid'] && $serialkey == $orderDetails['serialkey']) {
                    $del = delete_option('wp_file_manager_pro');
                    if($del) {
                        $result = 'y';
                    } else {
                        $result = 'n';
                    }
                } else {
                    $result = 'n';
                }
            } else {
                $result = 'n';
            }
            return $result;
        }

        /* Upload Images to Media Library */
		 public function upload_to_media_library($image_url) {
            $allowed_exts = array('jpg','jpe',
                                  'jpeg','gif',
                                  'png','svg',
                                  'pdf','zip',
                                  'ico','pdf',
                                  'doc','docx',
                                  'ppt','pptx',
                                  'pps','ppsx',
                                  'odt','xls',
                                  'xlsx','psd',
                                  'mp3','m4a',
                                  'ogg','wav',
                                  'mp4','m4v',
                                  'mov','wmv',
                                  'avi','mpg',
                                  'ogv','3gp',
                                  '3g2'
                                );
            $url = $image_url;
            preg_match('/[^\?]+\.(jpg|jpe|jpeg|gif|png|pdf|zip|ico|pdf|doc|docx|ppt|pptx|pps|ppsx|odt|xls|xlsx|psd|mp3|m4a|ogg|wav|mp4|m4v|mov|wmv|avi|mpg|ogv|3gp|3g2)/i', $url, $matches);
             if(in_array($matches[1], $allowed_exts)) {
			// Need to require these files
					if ( !function_exists('media_handle_upload') ) {
						require_once(ABSPATH . "wp-admin" . '/includes/image.php');
						require_once(ABSPATH . "wp-admin" . '/includes/file.php');
						require_once(ABSPATH . "wp-admin" . '/includes/media.php');
					}
					
					$tmp = download_url( $url );
					$post_id = 0;
					$desc = "";
					$file_array = array();     
                    $file_array['name'] = basename($matches[0]);
                    $file_info = pathinfo($file_array['name']);
					$desc = $file_info['filename'];			
					// If error storing temporarily, unlink
					if ( is_wp_error( $tmp ) ) {
						@unlink($file_array['tmp_name']);
						$file_array['tmp_name'] = '';
					} else {
						$file_array['tmp_name'] = $tmp;
					}
					$id = media_handle_sideload( $file_array, $post_id, $desc );
					if ( is_wp_error($id) ) {
						@unlink($file_array['tmp_name']);
						return $id;
                    }
            }
         }
        
        public function getMimeTypeFromExtension(){
            include('classes/fm-mimetypes.php');
            return FM_MimeTypes::getMimeTypes();
        }

        public function fm_download_backup($request){
            $params = $request->get_params();
            if(isset($params["backup_id"]) && !empty(trim($params["backup_id"])) && isset($params["type"]) && !empty(trim($params["type"]))){
                $id = (int) base64_decode(trim($params["backup_id"]));
                $type = base64_decode(trim($params["type"]));
                $fmkey = self::fm_get_key();
                if(base64_encode(site_url().$fmkey) === $params['key']){
                    global $wpdb;
                    $upload_dir = wp_upload_dir();
                    $backup = $wpdb->get_var("select backup_name from ".$wpdb->prefix."wpfm_backup where id=".$id);
                    $backup_dirname = $upload_dir['basedir'].'/wp-file-manager-pro/fm_backup/';
                    $backup_baseurl = $upload_dir['baseurl'].'/wp-file-manager-pro/fm_backup/';
                    if($type == "db"){
                        $bkpName = $backup.'-db.sql.gz';
                    }else{
                        $bkpName = $backup.'-'.$type.'.zip';
                    }
                    $file = $backup_dirname.$bkpName;
                    if(file_exists($file)){
                        //Set Headers:
                        $memory_limit = intval( ini_get( 'memory_limit' ) );
                        if ( ! extension_loaded( 'suhosin' ) && $memory_limit < 512 ) {
                            @ini_set( 'memory_limit', '1024M' );
                        }
                        @ini_set( 'max_execution_time', 6000 );
                        @ini_set( 'max_input_vars', 10000 );
                        $etag = md5_file($file);
                        header('Pragma: public');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($file)) . ' GMT');
                        header("Etag: ".$etag);
                        header('Content-Type: application/force-download');
                        header('Content-Disposition: inline; filename="'.$bkpName.'"');
                        header('Content-Transfer-Encoding: binary');
                        header('Content-Length: ' . filesize($file));
                        header('Connection: close');
                        if(ob_get_level()){
                            ob_end_clean();
                        }
                        readfile($file);
                        exit();
                    }
                    else{
                        $messg = __( 'File doesn\'t exist to download.', 'wp-file-manager-pro');
                        return new WP_Error( 'fm_file_exist', $messg, array( 'status' => 404 ) );
                    }
                }
                else {
                    $messg = __( 'Invalid Security Code.', 'wp-file-manager-pro');
                    return new WP_Error( 'fm_security_issue', $messg, array( 'status' => 404 ) );
                }
            }
            if(!isset($params["backup_id"])){
                $messg1 = __( 'Missing backup id.', 'wp-file-manager-pro');
                return new WP_Error( 'fm_missing_params', $messg1, array( 'status' => 401 ) );
            } elseif(!isset($params["type"])){
                $messg2 = __( 'Missing parameter type.', 'wp-file-manager-pro');
                return new WP_Error( 'fm_missing_params', $messg2, array( 'status' => 401 ) );
            } else {
                $messg4 = __( 'Missing required parameters.', 'wp-file-manager-pro');
                return new WP_Error( 'fm_missing_params', $messg4, array( 'status' => 401 ) );
            }
        }

        public function fm_download_backup_all($request){
            $params = $request->get_params();
            if(isset($params["backup_id"]) && !empty(trim($params["backup_id"])) && isset($params["type"]) && !empty(trim($params["type"])) && !empty(trim($params["all"])) ){
                $id = (int) base64_decode(trim($params["backup_id"]));
                $type = base64_decode(trim($params["type"]));
                $fmkey = self::fm_get_key();
                if(base64_encode(site_url().$fmkey) === $params['key']){
                    global $wpdb;
                    $upload_dir = wp_upload_dir();
                    $backup = $wpdb->get_var(
                        $wpdb->prepare("select backup_name from ".$wpdb->prefix."wpfm_backup where id=%d",$id)
                    );
                    
                    $backup_dirname = $upload_dir['basedir'].'/wp-file-manager-pro/fm_backup/';
                    $dir_list = scandir($backup_dirname, 1);
                    $zip = new ZipArchive(); 
                    $zip_name = $backup."-all.zip"; 
                    if ($zip->open($zip_name, ZIPARCHIVE::CREATE  || ZipArchive::OVERWRITE) === true) {
                    foreach($dir_list as $key => $file_name){
                        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                        if($file_name != '.' && $file_name != '..' && (is_dir($backup_dirname.'/'.$file_name) || $ext == 'zip' || $ext == 'gz') ){
                          
                                if(strpos($file_name,$backup) !== false ){
                                    $source_file = $backup_dirname.$dir_list[$key];
                                    $source_file = str_replace('\\', '/', realpath($source_file));
                                    $zip->addFromString(basename($source_file), file_get_contents($source_file));
                                  
                                }
                            }
                        }
                    }
              
                    $zip->close();
                    if(file_exists($zip_name)){
                        //Set Headers:
                        $memory_limit = intval( ini_get( 'memory_limit' ) );
                        if ( ! extension_loaded( 'suhosin' ) && $memory_limit < 512 ) {
                            @ini_set( 'memory_limit', '1024M' );
                        }
                        @ini_set( 'max_execution_time', 6000 );
                        @ini_set( 'max_input_vars', 10000 );
                        $etag = md5_file($zip_name);
                        header('Pragma: public');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($zip_name)) . ' GMT');
                        header("Etag: ".$etag);
                        header('Content-Type: application/force-download');
                        header('Content-Disposition: inline; filename="'.$zip_name.'"');
                        header('Content-Transfer-Encoding: binary');
                        header('Content-Length: ' . filesize($zip_name));
                        header('Connection: close');
                        if(ob_get_level()){
                            ob_end_clean();
                        }
                        readfile($zip_name);
                        unlink($zip_name);
                        exit();
                    }
                    else{
                        $messg = __( 'File doesn\'t exist to download.', 'wp-file-manager-pro');
                        return new WP_Error( 'fm_file_exist', $messg, array( 'status' => 404 ) );
                    }
                }
                else {
                    $messg = __( 'Invalid Security Code.', 'wp-file-manager-pro');
                    return new WP_Error( 'fm_security_issue', $messg, array( 'status' => 404 ) );
                }
            }
            if(!isset($params["backup_id"])){
                $messg1 = __( 'Missing backup id.', 'wp-file-manager-pro');
                return new WP_Error( 'fm_missing_params', $messg1, array( 'status' => 401 ) );
            } elseif(!isset($params["type"])){
                $messg2 = __( 'Missing parameter type.', 'wp-file-manager-pro');
                return new WP_Error( 'fm_missing_params', $messg2, array( 'status' => 401 ) );
            } else {
                $messg4 = __( 'Missing required parameters.', 'wp-file-manager-pro');
                return new WP_Error( 'fm_missing_params', $messg4, array( 'status' => 401 ) );
            }
        }
    }
    $filemanager = new mk_file_folder_manager();
    global $filemanager;
    /* end class */
        function fm_curl_exists()
        {
            return function_exists('curl_version');
        }
endif;
register_activation_hook( __FILE__, array( 'mk_file_folder_manager', 'wp_fm_log_table' ) );