<?php
/**
 * JS Enqueue.
 *
 * @package Ultimate_Dashboard
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

return function ( $module ) {

	if ( $module->screen()->is_login_redirect() ) {

		wp_enqueue_script( 'udb-pro-login-redirect', $module->url . '/assets/js/login-redirect.js', array( 'udb-login-redirect' ), ULTIMATE_DASHBOARD_PRO_PLUGIN_VERSION, true );

	}

};
