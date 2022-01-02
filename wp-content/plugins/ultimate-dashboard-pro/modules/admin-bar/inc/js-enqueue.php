<?php
/**
 * JS Enqueue.
 *
 * @package Ultimate_Dashboard_PRO
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

return function ( $module ) {

	if ( $module->screen()->is_admin_bar() ) {

		wp_dequeue_script( 'udb-admin-bar' );
		wp_deregister_script( 'udb-admin-bar' );

		// Admin menu.
		wp_enqueue_script( 'udb-admin-bar', $module->url . '/assets/js/admin-bar.js', array( 'jquery', 'dashicons-picker', 'jquery-ui-sortable' ), ULTIMATE_DASHBOARD_PRO_PLUGIN_VERSION, true );

		/**
		 * These codes are not being used currently.
		 * But leave it here because in the future, if requested, it would be used for
		 * "hide menu item for specific role(s) / user(s)" functionality (inside dropdowns).
		 */
		// $wp_roles   = wp_roles();
		// $role_names = $wp_roles->role_names;
		// $roles      = array();

		// foreach ( $role_names as $role_key => $role_name ) {
		// 	array_push(
		// 		$roles,
		// 		array(
		// 			'id'   => $role_key,
		// 			'text' => $role_name,
		// 		)
		// 	);
		// }

		$admin_bar_data = array(
			'nonces'    => array(
				// 'getUsers'  => wp_create_nonce( 'udb_admin_bar_get_users' ),
				'resetMenu' => wp_create_nonce( 'udb_admin_bar_reset_menu' ),
				'saveMenu'  => wp_create_nonce( 'udb_admin_bar_save_menu' ),
			),
			'warningMessages' => array(
				'resetMenu' => __( 'Caution! Are you sure you want to reset the Admin Bar Editor?', 'ultimatedashboard' ),
			),
			// 'roles'     => $roles,
			'templates' => array(
				'menuList'    => require ULTIMATE_DASHBOARD_PLUGIN_DIR . '/modules/admin-bar/templates/menu-list.php',
				'submenuList' => require ULTIMATE_DASHBOARD_PLUGIN_DIR . '/modules/admin-bar/templates/submenu-list.php',
			),
		);

		$admin_bar_data = apply_filters( 'udb_admin_bar_js_object', $admin_bar_data );

		wp_localize_script(
			'udb-admin-bar',
			'udbAdminBar',
			$admin_bar_data
		);

	}

};
