<?php
/**
 * Reset menu.
 *
 * @package Ultimate_Dashboard_PRO
 */

namespace UdbPro\AdminBar\Ajax;

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Class to handle ajax request to reset admin menu.
 */
class Reset_Menu {

	/**
	 * The class instance.
	 *
	 * @var object
	 */
	public static $instance;

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
	 * Reset menu.
	 */
	public function reset() {
		$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';

		if ( ! wp_verify_nonce( $nonce, 'udb_admin_bar_reset_menu' ) ) {
			wp_send_json_error( __( 'Invalid token', 'ultimatedashboard' ) );
		}

		delete_option( 'udb_admin_bar' );

		wp_send_json_success( __( 'Menu reset successfully', 'ultimatedashboard' ) );
	}

}
