<?php
/**
 * Admin Menu module.
 *
 * @package Ultimate_Dashboard
 */

namespace UdbPro\AdminMenu;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use Udb\Base\Base_Module;
use Udb\Helpers\Screen_Helper;
use UdbPro\Helpers\Multisite_Helper;

/**
 * Class to setup admin menu module.
 */
class Admin_Menu_Module extends Base_Module {

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

		$this->url = ULTIMATE_DASHBOARD_PRO_PLUGIN_URL . '/modules/admin-menu';

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
	 * Setup admin menu module.
	 */
	public function setup() {

		add_action( 'udb_admin_menu_sidebar', array( self::get_instance(), 'non_blueprint_notice' ) );
		add_action( 'udb_admin_menu_sidebar', array( self::get_instance(), 'super_admin_notice' ) );
		// add_action( 'admin_enqueue_scripts', array( self::get_instance(), 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( self::get_instance(), 'admin_scripts' ) );
		add_action( 'udb_admin_menu_form_footer', array( self::get_instance(), 'form_footer' ) );

		require __DIR__ . '/class-admin-menu-output.php';
		Admin_Menu_Output::init();

		$this->setup_ajax();

	}

	/**
	 * Setup ajax.
	 */
	public function setup_ajax() {

		require_once __DIR__ . '/ajax/class-reset-menu.php';
		require_once __DIR__ . '/ajax/class-save-menu.php';

		add_action( 'wp_ajax_udb_admin_menu_reset_menu', array( Ajax\Reset_Menu::get_instance(), 'reset' ) );
		add_action( 'wp_ajax_udb_admin_menu_save_menu', array( Ajax\Save_Menu::get_instance(), 'save' ) );

		add_action( 'udb_admin_menu_add_menu_button', array( self::get_instance(), 'add_menu_button' ) );
		add_action( 'udb_admin_menu_add_submenu_button', array( self::get_instance(), 'add_submenu_button' ) );
		add_action( 'udb_admin_menu_add_separator_button', array( self::get_instance(), 'add_separator_button' ) );

	}

	/**
	 * Admin notice to give a warning about admin menu editor usage on non-blueprint site.
	 */
	public function non_blueprint_notice() {

		$ms_helper     = new Multisite_Helper();
		$screen_helper = new Screen_Helper();

		if ( ! $screen_helper->is_admin_menu() || ! $ms_helper->needs_to_switch_blog() ) {
			return;
		}
		?>

		<div class="heatbox udb-notice-metabox is-warning">
			<h2><?php _e( 'Non-Blueprint Notice', 'welome-email-editor' ); ?></h2>
			<div class="heatbox-content">
				<?php
				$description  = __( '<strong>Caution:</strong> If the Admin Menu Editor is configured on a subsite, the blueprint settings for this feature will no longer be inherited.', 'ultimatedashboard' ) . '<br><br>';
				$description .= __( 'To inherit the blueprint configuration again for the Admin Menu Editor, please <strong>reset all menus</strong> (button below).', 'ultimatedashboard' );
				?>

				<p><?php echo $description; ?></p>
			</div>
		</div>

		<?php

	}

	/**
	 * Admin notice to give an info about super admin not being affected by admin menu editor changes on multisite.
	 */
	public function super_admin_notice() {

		$ms_helper     = new Multisite_Helper();
		$screen_helper = new Screen_Helper();

		if ( ! $screen_helper->is_admin_menu() || ! $ms_helper->multisite_supported() || ! is_super_admin() ) {
			return;
		}
		?>

		<div class="heatbox udb-notice-metabox is-info">
			<h2><?php _e( 'Super Admin Notice', 'welome-email-editor' ); ?></h2>
			<div class="heatbox-content">
				<?php
				$description = '<strong>' . __( 'Info:', 'ultimatedashboard' ) . '</strong>';
				$description = $description . ' ' . __( 'Changes made to the <strong>Admin Menu</strong> will not affect super admins. Super admins will always see the full admin bar for maximum control.', 'ultimatedashboard' );
				?>

				<p><?php echo $description; ?></p>
			</div>
		</div>

		<?php

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
	 * Add output to admin menu's form footer.
	 */
	public function form_footer() {

		$template = require __DIR__ . '/templates/form-footer.php';
		$template();

	}

	/**
	 * Add new menu button under the menu list.
	 */
	public function add_menu_button() {

		$template = require __DIR__ . '/templates/add-menu-button.php';
		$template();

	}

	/**
	 * Add new submenu button under the submenu list.
	 */
	public function add_submenu_button() {

		$template = require __DIR__ . '/templates/add-submenu-button.php';
		$template();

	}

	/**
	 * Add new separator button under menu / submenu list.
	 */
	public function add_separator_button() {

		$template = require __DIR__ . '/templates/add-separator-button.php';
		$template();

	}

}
