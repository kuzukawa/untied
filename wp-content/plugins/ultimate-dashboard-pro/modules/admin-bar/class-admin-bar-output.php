<?php
/**
 * Admin bar output.
 *
 * @package Ultimate_Dashboard_Pro
 */

namespace UdbPro\AdminBar;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use Udb\Vars;
use Udb\Base\Base_Output;
use Udb\Helpers\Array_Helper;
use Udb\AdminBar\Admin_Bar_Module as Free_Admin_Bar_Module;
use UdbPro\Helpers\Multisite_Helper;
use WP_User;

/**
 * Class to setup admin bar output.
 */
class Admin_Bar_Output extends Base_Output {

	/**
	 * The class instance.
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * The current module url.
	 *
	 * @var string
	 */
	public $url;

	/**
	 * Module constructor.
	 */
	public function __construct() {

		$this->url = ULTIMATE_DASHBOARD_PRO_PLUGIN_URL . '/modules/admin-bar';

	}

	/**
	 * Get instance of the class.
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;

	}

	/**
	 * Init the class setup.
	 */
	public static function init() {

		$class = new self();
		$class->setup();

	}

	/**
	 * Setup Admin Menu output.
	 */
	public function setup() {

		add_action( 'wp_before_admin_bar_render', array( self::get_instance(), 'menu_output' ), 1000000 );

	}

	/**
	 * Prepare admin bar nodes.
	 */
	public function menu_output() {

		global $wp_admin_bar;

		$parsed_menu   = $this->get_parsed_menu();
		$existing_menu = $wp_admin_bar->get_nodes();

		// Remove all nodes.
		foreach ( $existing_menu as $node_id => $node ) {
			$wp_admin_bar->remove_node( $node_id );
		}

		// Convert $parsed_menu to array of nodes.
		$this->generate_nodes( $parsed_menu );

	}

	/**
	 * Process the admin bar output.
	 *
	 * @param boolean $from_multisite Whether or not the function is called from multisite function.
	 *
	 * @return array The parsed menu in flat array format.
	 */
	public function get_parsed_menu( $from_multisite = false ) {

		global $wp_admin_bar;

		$ms_helper   = new Multisite_Helper();
		$switch_blog = $from_multisite && $ms_helper->needs_to_switch_blog() ? true : false;

		if ( $switch_blog ) {
			global $blueprint;
			switch_to_blog( $blueprint );
		}

		$module = new Free_Admin_Bar_Module();

		// This $existing_menu doesn't have effect with blog switching.
		$existing_menu = $wp_admin_bar->get_nodes();
		$existing_menu = $module->nodes_to_array( $existing_menu );

		$saved_menu  = get_option( 'udb_admin_bar', array() );
		$parsed_menu = ! $saved_menu ? $existing_menu : $module->parse_menu( $saved_menu, $existing_menu, 'output' );

		if ( $switch_blog ) {
			restore_current_blog();
		}

		return $parsed_menu;

	}

	/**
	 * Generate WP_Admin_Bar nodes based on parsed menu.
	 *
	 * @param array $parsed_menu The parsed menu in flat array format.
	 */
	public function generate_nodes( $parsed_menu ) {

		global $wp_admin_bar;

		$nodes = array();

		$user  = wp_get_current_user();
		$roles = $user->roles;

		foreach ( $parsed_menu as $menu_id => $menu ) {
			$args = array();

			$role_allowed = true;
			$user_allowed = true;

			/**
			 * These codes are not being used currently.
			 * But leave it here because in the future, if requested, it would be used for
			 * "hide menu item for specific role(s) / user(s)" functionality.
			 */
			// foreach ( $roles as $role ) {
			// 	if ( in_array( $role, $menu['disallowed_roles'], true ) ) {
			// 		$role_allowed = false;
			// 		break;
			// 	}
			// }

			// if ( in_array( $user->ID, $menu['disallowed_users'], true ) ) {
			// 	$user_allowed = false;
			// }

			if ( $role_allowed && $user_allowed && ! $menu['is_hidden'] ) {
				foreach ( $menu as $arg_key => $arg_value ) {
					if (
						false === stripos( $arg_key, '_default' )
						&& 'was_added' !== $arg_key
						&& 'icon' !== $arg_key
						/**
						 * These conditions are not being used currently.
						 * But leave it here because in the future, if requested, it would be used for
						 * "hide menu item for specific role(s) / user(s)" functionality.
						 */
						// && 'disallowed_roles' !== $arg_key
						// && 'disallowed_users' !== $arg_key
					) {
						$value = $arg_value;

						if ( '' === $value ) {
							$value = $menu[ $arg_key . '_default' ];
						}

						$args[ $arg_key ] = $value;
					}
				}

				if ( $menu['was_added'] ) {
					if ( isset( $menu['icon'] ) && ! empty( $menu['icon'] ) ) {
						$args['title'] = '
							<div class="ab-item udb-admin-bar-output--menu-link">
								<span class="ab-icon dashicons ' . $menu['icon'] . '"></span>
								<span class="ab-label">
									' . $args['title'] . '
								</span>
							</div>
						';
					}
				}
			}

			$wp_admin_bar->add_node( $args );
		}
	}

}
