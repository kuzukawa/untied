<?php
/**
 * Setup Ultimate Dashboard PRO plugin.
 *
 * @package Ultimate_Dashboard_Pro
 */

namespace UdbPro;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use UdbPro\Helpers\Content_Helper;
use UdbPro\Helpers\Multisite_Helper;

/**
 * Class to setup Ultimate Dashboard PRO plugin.
 */
class Setup {

	/**
	 * The class instanace
	 *
	 * @var object
	 */
	public static $instance = null;

	/**
	 * Get the class instance.
	 *
	 * @return object
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

		add_action( 'plugins_loaded', array( self::get_instance(), 'setup' ) );

	}

	/**
	 * Setup the class.
	 */
	public function setup() {

		$blueprint = get_site_option( 'udb_multisite_blueprint' );

		// Declare glolbal variables.
		$GLOBALS['blueprint'] = $blueprint ? (int) $blueprint : 0;

		// Enable multisite support.
		add_filter( 'udb_pro_ms_support', '__return_true' );

		require __DIR__ . '/helpers/class-multisite-helper.php';
		register_deactivation_hook( ULTIMATE_DASHBOARD_PRO_PLUGIN_FILE, array( $this, 'deactivation' ) );

		// Check whether Ultimate Dashboard free active status & version.
		if ( ! defined( 'ULTIMATE_DASHBOARD_PLUGIN_VERSION' ) || version_compare( ULTIMATE_DASHBOARD_PLUGIN_VERSION, '3.0', '<' ) ) {

			require __DIR__ . '/modules/instant-install/class-instant-install-module.php';
			InstantInstall\Instant_Install_Module::init();

			// Stop if Ultimate Dashboard free is not active or it's version is lower than 3.0.
			return;

		}

		$this->load_helpers();
		Backwards_Compatibility::init();

		add_action( 'init', array( $this, 'load_textdomain' ) );

		$prefix = is_network_admin() ? 'network_admin_' : '';
		add_filter( $prefix . 'plugin_action_links_' . ULTIMATE_DASHBOARD_PRO_PLUGIN_FILE, array( $this, 'action_links' ) );

		add_action( 'init', array( self::get_instance(), 'check_activation_meta' ) );
		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ), 30 );

		// Pro version filters.
		add_filter( 'udb_saved_modules', array( $this, 'saved_modules' ) );
		add_filter( 'udb_modules', array( $this, 'load_modules' ) );
		add_filter( 'udb_content_editor', array( $this, 'get_content_editor' ), 10, 2 );

	}

	/**
	 * Load Ultimate Dashboard Pro helper classes.
	 *
	 * Note: the multisite helper has been loaded in the setup method above
	 * because it will be used as part of plugin de-activation process.
	 */
	public function load_helpers() {

		require __DIR__ . '/helpers/class-video-helper.php';
		require __DIR__ . '/helpers/class-content-helper.php';
		require __DIR__ . '/helpers/class-widget-helper.php';
		require __DIR__ . '/helpers/class-branding-helper.php';

	}

	/**
	 * Load textdomain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'ultimatedashboard', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Add action links displayed in plugins page.
	 *
	 * @param array $links The action links array.
	 * @return array The modified action links array.
	 */
	public function action_links( $links ) {

		$multisite_settings = array();
		$settings           = array( '<a href="' . admin_url( 'edit.php?post_type=udb_widgets&page=udb_settings' ) . '">' . __( 'Settings', 'ultimatedashboard' ) . '</a>' );

		if ( apply_filters( 'udb_pro_ms_support', false ) ) {
			$multisite_settings = is_multisite() ? array( '<a href="' . network_admin_url( 'settings.php?page=ultimate-dashboard-multisite' ) . '">' . __( 'Network Settings', 'ultimatedashboard' ) . '</a>' ) : array();
		}

		return array_merge( $links, $settings, $multisite_settings );

	}

	/**
	 * Plugin deactivation.
	 */
	public function deactivation() {

		$ms_helper = new Multisite_Helper();

		if ( $ms_helper->multisite_supported() ) {
			global $blueprint;

			$site_ids = get_sites(
				array(
					'fields' => 'ids',
				)
			);

			if ( $blueprint ) {
				// When blueprint is set, we get the data removal option from the blueprint site only.
				$settings    = get_blog_option( $blueprint, 'udb_settings', array() );
				$remove_data = isset( $settings['remove-on-uninstall'] ) ? true : false;

				if ( $remove_data ) {
					foreach ( $site_ids as $site_id ) {
						$this->delete_udb_pro_data( $site_id );
					}
				}
			} else {
				// When blueprint is not set, we check the data removal option per-site id.
				foreach ( $site_ids as $site_id ) {
					$settings    = get_blog_option( $site_id, 'udb_settings', array() );
					$remove_data = isset( $settings['remove-on-uninstall'] ) ? true : false;

					if ( $remove_data ) {
						$this->delete_udb_pro_data( $site_id );
					}
				}
			}
		} else {
			$settings    = get_option( 'udb_settings' );
			$remove_data = isset( $settings['remove-on-uninstall'] ) ? true : false;

			if ( $remove_data ) {
				$this->delete_udb_pro_data();
			}
		}

	}

	/**
	 * Delete pro-related options on plugin deactivation.
	 *
	 * But `udb_multisite_blueprint` won't be deleted on both free & pro versions deactivation.
	 * So that it wouldn't be a problem if user deactivate free version first or pro version first.
	 * Both versions will be able to get the blueprint value.
	 *
	 * So yea, `udb_multisite_blueprint` will stays in the database.
	 *
	 * @param int|null $site_id The site id or null.
	 */
	public function delete_udb_pro_data( $site_id = null ) {

		if ( $site_id ) {
			delete_blog_option( $site_id, 'udb_admin_menu' );
			delete_blog_option( $site_id, 'udb_admin_bar' );

			delete_blog_option( $site_id, 'udb_compat_branding_meta' );

			delete_blog_option( $site_id, 'udb_pro_widget_order' );
			delete_blog_option( $site_id, 'udb_multisite_exclude' );
			delete_blog_option( $site_id, 'udb_multisite_widget_order' );
			delete_blog_option( $site_id, 'udb_multisite_capability' );

			delete_blog_option( $site_id, 'udb_pro_site_url' );
			delete_blog_option( $site_id, 'udb_pro_plugin_activated' );

			// In case free version was deactivated first, we need to delete the restored settings option.
			if ( ! defined( 'ULTIMATE_DASHBOARD_PLUGIN_VERSION' ) ) {
				delete_blog_option( $site_id, 'udb_settings' );
			}
		} else {
			delete_option( 'udb_admin_menu' );
			delete_option( 'udb_admin_bar' );

			delete_option( 'udb_compat_branding_meta' );

			delete_option( 'udb_pro_widget_order' );
			delete_option( 'udb_multisite_exclude' );
			delete_option( 'udb_multisite_widget_order' );
			delete_option( 'udb_multisite_capability' );

			delete_option( 'udb_pro_site_url' );
			delete_option( 'udb_pro_plugin_activated' );

			// In case free version was deactivated first, we need to delete the restored settings option.
			if ( ! defined( 'ULTIMATE_DASHBOARD_PLUGIN_VERSION' ) ) {
				delete_option( 'udb_settings' );
			}
		}

	}

	/**
	 * Check plugin activation meta.
	 */
	public function check_activation_meta() {

		if ( ! current_user_can( 'activate_plugins' ) || get_option( 'udb_pro_plugin_activated' ) ) {
			return;
		}

		update_option( 'udb_pro_site_url', $_SERVER['SERVER_NAME'] );
		update_option( 'udb_pro_plugin_activated', 1 );

	}

	/**
	 * Admin body class.
	 *
	 * @param string $classes The class names.
	 */
	public function admin_body_class( $classes ) {

		$ms_helper = new Multisite_Helper();

		if ( ! $ms_helper->multisite_supported() ) {
			return $classes;
		}

		if ( is_network_admin() ) {
			$classes .= ' udb-is-network-admin';
		}

		if ( is_main_site() ) {
			$classes .= ' udb-is-main-site';
		} else {
			$classes .= ' udb-is-subsite';
		}

		$classes .= ' udb-site-' . get_current_blog_id();

		return $classes;

	}

	/**
	 * Filter the "get_content_editor" value of Content_Helper class in the free version.
	 *
	 * @param string $editor The editor name from free version.
	 * @param int    $post_id ID of the post being checked.
	 *
	 * @return string The content editor name from pro version.
	 */
	public function get_content_editor( $editor, $post_id ) {

		$content_helper = new Content_Helper();
		return $content_helper->get_content_editor( $post_id );

	}

	/**
	 * Get saved/default modules.
	 *
	 * Helper function, similar to what we have in the free version but with multisite support in mind.
	 * Also used to filter "udb_saved_modules" in the free version.
	 *
	 * @return array The saved/default modules.
	 */
	public function saved_modules() {

		$defaults = array(
			'white_label'       => 'true',
			'login_customizer'  => 'true',
			'login_redirect'    => 'true',
			'admin_pages'       => 'true',
			'admin_menu_editor' => 'true',
			'admin_bar_editor'  => 'true',
		);

		$saved_modules = get_option( 'udb_modules', $defaults );

		$ms_helper = new Helpers\Multisite_Helper();

		if ( $ms_helper->needs_to_switch_blog() ) {
			global $blueprint;

			// If we need to switch blog, let's grab udb_modules from the blueprint.
			$saved_modules = get_blog_option( $blueprint, 'udb_modules', $defaults );
		}

		return $saved_modules;

	}

	/**
	 * Load Ultimate Dashboard Pro modules.
	 *
	 * @param array $modules The modules being loaded.
	 * @return array $modules The modules being loaded.
	 */
	public function load_modules( $modules ) {

		$modules['UdbPro\\Widget\\Widget_Module']   = __DIR__ . '/modules/widget/class-widget-module.php';
		$modules['UdbPro\\Setting\\Setting_Module'] = __DIR__ . '/modules/setting/class-setting-module.php';

		$saved_modules = $this->saved_modules();

		if ( isset( $saved_modules['white_label'] ) && 'true' === $saved_modules['white_label'] ) {
			$modules['UdbPro\\Branding\\Branding_Module'] = __DIR__ . '/modules/branding/class-branding-module.php';
		}

		if ( isset( $saved_modules['login_customizer'] ) && 'true' === $saved_modules['login_customizer'] ) {
			$modules['UdbPro\\LoginCustomizer\\Login_Customizer_Module'] = __DIR__ . '/modules/login-customizer/class-login-customizer-module.php';
		}

		if ( isset( $saved_modules['login_redirect'] ) && 'true' === $saved_modules['login_redirect'] ) {
			$modules['UdbPro\\LoginRedirect\\Login_Redirect_Module'] = __DIR__ . '/modules/login-redirect/class-login-redirect-module.php';
		}

		if ( isset( $saved_modules['admin_pages'] ) && 'true' === $saved_modules['admin_pages'] ) {
			$modules['UdbPro\\AdminPage\\Admin_Page_Module'] = __DIR__ . '/modules/admin-page/class-admin-page-module.php';
		}

		if ( isset( $saved_modules['admin_menu_editor'] ) && 'true' === $saved_modules['admin_menu_editor'] ) {
			$modules['UdbPro\\AdminMenu\\Admin_Menu_Module'] = __DIR__ . '/modules/admin-menu/class-admin-menu-module.php';
		}

		if ( version_compare( ULTIMATE_DASHBOARD_PLUGIN_VERSION, '3.2.1', '>' ) ) {
			if ( 'true' === $saved_modules['admin_bar_editor'] ) {
				$modules['UdbPro\\AdminBar\\Admin_Bar_Module'] = __DIR__ . '/modules/admin-bar/class-admin-bar-module.php';
			}
		}

		$modules['UdbPro\\Tool\\Tool_Module']       = __DIR__ . '/modules/tool/class-tool-module.php';
		$modules['UdbPro\\License\\License_Module'] = __DIR__ . '/modules/license/class-license-module.php';

		$ms_helper = new Helpers\Multisite_Helper();

		if ( $ms_helper->multisite_supported() ) {
			$modules['UdbPro\\Multisite\\Multisite_Module'] = __DIR__ . '/modules/multisite/class-multisite-module.php';
		}

		return $modules;

	}

}
