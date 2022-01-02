<?php if ( ! defined( 'ABSPATH' ) ) exit; 
global $wpdb;
$dbtables = $wpdb->get_col("show tables"); 
if(isset($_POST['mk_export_db']) && isset($_POST['mk_export_db_action']) && $_POST['mk_export_db_action'] == 'export_fm_db' && wp_verify_nonce( $_POST['export_db_field'], 'mk_export_db_field' ))
{
  $this->exportDB($wpdb->dbhost, $wpdb->dbuser, $wpdb->dbpassword, $wpdb->dbname, $dbtables); 
} else {
	wp_die(__('You don\'t have permission to access this page.', 'wp-file-manager-pro'));
}
?>
