<?php
/**
 * Export processing.
 *
 * @package Ultimate_Dashboard
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

return function () {

	$admin_menu = array();
	$admin_bar  = array();

	$multisite_settings = array(
		'udb_multisite_blueprint'    => 0,
		'udb_multisite_exclude'      => '',
		'udb_multisite_widget_order' => 0,
		'udb_multisite_capability'   => 'manage_network',
	);

	if ( isset( $_POST['udb_export_settings'] ) && $_POST['udb_export_settings'] ) {
		$admin_menu = get_option( 'udb_admin_menu', array() );
		$admin_bar  = get_option( 'udb_admin_bar', array() );

		foreach ( $multisite_settings as $key => $value ) {
			$multisite_settings[ $key ] = get_site_option( $key );
		}
	}

	return array(
		'admin_menu'         => $admin_menu,
		'admin_bar'          => $admin_bar,
		'multisite_settings' => $multisite_settings,
	);

};
