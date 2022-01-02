<?php
/**
 * Package: WP File Manager Pro
 * Update Check Class
 * Class: wp_file_manager_pro_updates
 */
if(!class_exists('wp_file_manager_pro_updates')) {

    class wp_file_manager_pro_updates {
        
        /**
         * Server
         */
        static $server = 'http://webdesi9.com/plugin_server_twenty_ninteen/wp_file_manager/';

        /**
         * Install
         */
        public function update($filePath) {
			$opt = get_option('wp_file_manager_pro');
			$orderID = isset($opt['orderid']) ? $opt['orderid'] : '';
			$licencekey = isset($opt['serialkey']) ? $opt['serialkey'] : '';
			if(!empty(trim($licencekey)) && !empty(trim($orderID))) {
				require FILEMANEGERPROPATH.'plugin-update-checker/plugin-update-checker.php';
				Puc_v4_Factory::buildUpdateChecker(
					self::$server.'?orderid='.$orderID.'&licencekey='.$licencekey,
					$filePath,
					'wp-file-manager-pro'
				);
			}
        }
    }
}