<?php
/**
 * License module.
 *
 * @package Ultimate_Dashboard
 */

namespace UdbPro\License;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use Udb\Base\Base_Module;

/**
 * Class to setup branding module.
 */
class License_Module extends Base_Module {

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

		$this->url = ULTIMATE_DASHBOARD_PRO_PLUGIN_URL . '/modules/license';

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
	 * Setup License module.
	 */
	public function setup() {

		add_action( 'admin_menu', array( self::get_instance(), 'submenu_page' ), 20 );
		add_action( 'admin_notices', array( $this, 'license_notice' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );

		require_once __DIR__ . '/edd/license.php';

	}

	/**
	 * Add submenu page.
	 */
	public function submenu_page() {

		// Stop here - no matter what - if we are on a Multisite installation and not on the main site.
		if ( ! is_main_site() ) {
			return;
		}

		add_submenu_page( 'edit.php?post_type=udb_widgets', 'License', 'License', apply_filters( 'udb_license_capability', 'manage_options' ), 'udb-license', array( $this, 'submenu_page_content' ) );

	}

	/**
	 * Submenu page content.
	 */
	public function submenu_page_content() {

		$template = require __DIR__ . '/templates/license-template.php';
		$template();

	}

	/**
	 * Enqueue admin styles.
	 */
	public function admin_styles() {

		$enqueue = require __DIR__ . '/inc/css-enqueue.php';
		$enqueue( $this );

	}

	/**
	 * Admin notices about plugin's license.
	 */
	public function license_notice() {

		// Stop here if we are on a Multisite installation and not on the main site.
		if ( ! is_main_site() ) {
			return;
		}

		// Stop here if current user cannot manage options.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$status           = get_option( 'ultimate_dashboard_license_status' );
		$license_page_url = get_admin_url() . 'edit.php?post_type=' . ULTIMATE_DASHBOARD_PRO_LICENSE_PAGE;
		$docs_url         = 'https://ultimatedashboard.io/docs/installation-license-activation/';

		if ( 'expired' === $status ) {

			$class       = 'notice notice-error';
			$license_key = trim( get_option( 'ultimate_dashboard_license_key' ) );
			$renew_url   = 'https://ultimatedashboard.io/checkout/?edd_license_key=' . $license_key . '&download_id=' . ULTIMATE_DASHBOARD_PRO_ITEM_ID;
			// translators: %1%s: Plugin name, %2$s: Renewal URL.
			$message = sprintf( __( 'Your License for <strong>%1$s</strong> has expired. <a href="%2$s" target="_blank">Renew your License</a> to keep getting Feature Updates.', 'ultimatedashboard' ), ULTIMATE_DASHBOARD_PRO_PRODUCT_NAME, $renew_url );

			printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );

		} elseif ( 'valid' !== $status ) {

			$class   = 'notice notice-warning';
			$message = sprintf(
				// translators: 1: License page url, 2: Product name, 3: Documentation URL.
				__( 'Please <a href="%1$s">activate your license key</a> to receive updates for <strong>%2$s</strong>. <a href="%3$s" target="_blank">Help</a>', 'ultimatedashboard' ),
				$license_page_url,
				ULTIMATE_DASHBOARD_PRO_PRODUCT_NAME,
				$docs_url
			);

			printf( '<div class="%1s"><p>%2s</p></div>', $class, $message );

		} elseif ( $this->license_key_mismatch() ) {

			$class = 'notice notice-error';
			// translators: %1$s: License page url, %2$s: Plugin name, %3$s: URL to the docs.
			$message  = '<strong>' . __( 'License key mismatch!', 'ultimatedashboard' ) . '</strong>';
			$message .= '<br>';
			$message .= sprintf( __( 'Please <a href="%1$s">revalidate your license key</a> for <strong>%2$s</strong>.', 'ultimatedashboard' ), $license_page_url, ULTIMATE_DASHBOARD_PRO_PRODUCT_NAME );

			printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );

		}

	}

	/**
	 * Check for license key mismatch.
	 *
	 * @return bool
	 */
	public function license_key_mismatch() {

		$status           = get_option( 'ultimate_dashboard_license_status' );
		$current_site_url = get_option( 'udb_pro_site_url' );

		// Stop if $current_site_url is not set.
		if ( ! $current_site_url ) {
			return false;
		}

		// Stop if there's no valid license key.
		if ( 'valid' !== $status ) {
			return false;
		}

		// Stop if domain hasn't changed.
		if ( $current_site_url === $_SERVER['SERVER_NAME'] ) {
			return false;
		}

		return true;

	}

}
