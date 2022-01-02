<?php
/**
 * Login url module.
 *
 * @package Ultimate_Dashboard
 */

namespace UdbPro\LoginRedirect;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use Udb\Base\Base_Module;

/**
 * Class to setup login url module.
 */
class Login_Redirect_Module extends Base_Module {
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

		$this->url = ULTIMATE_DASHBOARD_PRO_PLUGIN_URL . '/modules/login-redirect';

	}

	/**
	 * Setup login url module.
	 */
	public function setup() {

		// Stop if free version hasn't been updated to have the login url module.
		if ( ! class_exists( '\Udb\LoginRedirect\Login_Redirect_Module' ) ) {
			return;
		}

		add_action( 'init', array( $this, 'setup_hooks' ) );

	}

	/**
	 * Setup functions hooking on init.
	 *
	 * Currently this module will run for multisite only, so we need to hook it to `init` action hook.
	 * And we can't use `admin_init` hook here because `udb_login_redirect_title` will run on `admin_init`
	 * on the free version in 'ultimate-dashboard/modules/setting/class-setting-module.php' file.
	 */
	public function setup_hooks() {

		if ( ! apply_filters( 'udb_ms_is_blueprint', false ) ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_filter( 'udb_login_redirect_title', array( $this, 'add_tab_menu' ) );
		add_action( 'admin_init', array( $this, 'add_settings' ), 20 );

	}

	/**
	 * Enqueue admin styles.
	 */
	public function admin_styles() {

		$enqueue = require __DIR__ . '/inc/css-enqueue.php';
		$enqueue( $this );

	}

	/**
	 * Enqueue admin scripts.
	 */
	public function admin_scripts() {

		$enqueue = require __DIR__ . '/inc/js-enqueue.php';
		$enqueue( $this );

	}

	/**
	 * Add settings.
	 */
	public function add_settings() {

		// Login redirect fields.
		add_settings_field( 'subsites-login-redirect-url', __( 'Select Role(s)', 'ultimatedashboard' ), array( $this, 'login_redirect_url_field' ), 'udb-login-redirect-settings', 'udb-login-redirect-section' );

	}

	/**
	 * Add tabs nav to "Redirect After Login" metabox header.
	 *
	 * @param string $title The metabox title.
	 */
	public function add_tab_menu( $title ) {

		$tabs_nav = '
		<span class="udb-login-redirect--tab-menu">
			<span class="udb-login-redirect--tab-menu-item is-active" data-udb-tab="blueprint">
				' . __( 'Blueprint', 'ultimatedashboard' ) . '
			</span>
			<span class="udb-login-redirect--tab-menu-item" data-udb-tab="subsites">
				' . __( 'Subsites', 'ultimatedashboard' ) . '
			</span>
		</span>
		';

		$tabs_nav = $title . $tabs_nav;

		return $tabs_nav;

	}

	/**
	 * Subsites login redirect url field.
	 */
	public function login_redirect_url_field() {

		$field = require ULTIMATE_DASHBOARD_PLUGIN_DIR . '/modules/login-redirect/templates/fields/login-redirect-url.php';
		$field( 'subsites' );

	}

}
