<?php
/**
 * JS Enqueue.
 *
 * @package Ultimate_Dashboard_PRO
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

return function ( $module ) {

	if ( $module->screen()->is_branding() ) {

		// Branding settings.
		wp_enqueue_script( 'udb-pro-branding-instant-preview', ULTIMATE_DASHBOARD_PRO_PLUGIN_URL . '/modules/branding/assets/js/instant-preview.js', array( 'udb-branding-instant-preview' ), ULTIMATE_DASHBOARD_PRO_PLUGIN_VERSION, true );

	}

};
