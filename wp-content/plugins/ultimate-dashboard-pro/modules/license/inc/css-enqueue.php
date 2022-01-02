<?php
/**
 * CSS Enqueue.
 *
 * @package Ultimate_Dashboard_PRO
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

return function () {

	$current_screen = get_current_screen();

	if ( 'udb_widgets_page_udb-license' !== $current_screen->id ) {
		return;
	}

	// Heatbox.
	wp_enqueue_style( 'heatbox', ULTIMATE_DASHBOARD_PLUGIN_URL . '/assets/css/heatbox.css', array(), ULTIMATE_DASHBOARD_PLUGIN_VERSION );

};
