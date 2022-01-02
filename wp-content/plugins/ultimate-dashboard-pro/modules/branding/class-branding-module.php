<?php
/**
 * Branding module.
 *
 * @package Ultimate_Dashboard
 */

namespace UdbPro\Branding;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use Udb\Base\Base_Module;

/**
 * Class to setup branding module.
 */
class Branding_Module extends Base_Module {

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

		$this->url = ULTIMATE_DASHBOARD_PRO_PLUGIN_URL . '/modules/branding';

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
	 * Setup branding module.
	 */
	public function setup() {

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'udb_instant_preview', array( $this, 'instant_preview' ) );

		add_filter( 'udb_branding_colors', array( self::get_instance(), 'branding_colors' ) );

		add_filter( 'udb_branding_enable_feature_field_path', array( self::get_instance(), 'enable_field' ) );
		add_filter( 'udb_branding_choose_layout_field_path', array( self::get_instance(), 'choose_layout_field' ) );
		add_filter( 'udb_branding_admin_bar_logo_field_path', array( self::get_instance(), 'admin_bar_logo_field' ) );
		add_filter( 'udb_branding_admin_bar_logo_url_field_path', array( self::get_instance(), 'admin_bar_logo_url_field' ) );

		add_filter( 'udb_branding_accent_color_field_path', array( self::get_instance(), 'accent_color_field' ) );
		add_filter( 'udb_branding_admin_bar_bg_color_field_path', array( self::get_instance(), 'admin_bar_color_field' ) );
		add_filter( 'udb_branding_admin_menu_bg_color_field_path', array( self::get_instance(), 'admin_menu_bg_color_field' ) );
		add_filter( 'udb_branding_admin_submenu_bg_color_field_path', array( self::get_instance(), 'admin_submenu_bg_color_field' ) );
		add_filter( 'udb_branding_menu_item_color_field_path', array( self::get_instance(), 'menu_item_color_field' ) );
		add_filter( 'udb_branding_menu_item_active_color_field_path', array( self::get_instance(), 'menu_item_active_color_field' ) );

		// The module output.
		require_once __DIR__ . '/class-branding-output.php';
		Branding_Output::init();

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
	 * Instant preview style tags.
	 *
	 * @param array $colors The parsed branding colors.
	 */
	public function instant_preview( $colors = array() ) {

		if ( ! $this->screen()->is_branding() ) {
			return;
		}

		require __DIR__ . '/templates/instant-preview.php';

	}

	/**
	 * Apply branding colors.
	 *
	 * @param array $colors Existing array of color string.
	 * @return array
	 */
	public function branding_colors( $colors ) {

		$branding = get_option( 'udb_branding', array() );

		if ( ! $branding ) {
			return $colors;
		}

		if ( isset( $branding['menu_item_color'] ) && ! empty( $branding['menu_item_color'] ) ) {
			$colors['menu_item_color'] = $branding['menu_item_color'];
		}

		if ( isset( $branding['accent_color'] ) && ! empty( $branding['accent_color'] ) ) {
			$colors['accent_color'] = $branding['accent_color'];
		}

		if ( isset( $branding['admin_bar_bg_color'] ) && ! empty( $branding['admin_bar_bg_color'] ) ) {
			$colors['admin_bar_bg_color'] = $branding['admin_bar_bg_color'];
		}

		if ( isset( $branding['admin_menu_bg_color'] ) && ! empty( $branding['admin_menu_bg_color'] ) ) {
			$colors['admin_menu_bg_color'] = $branding['admin_menu_bg_color'];
		}

		if ( isset( $branding['admin_submenu_bg_color'] ) && ! empty( $branding['admin_submenu_bg_color'] ) ) {
			$colors['admin_submenu_bg_color'] = $branding['admin_submenu_bg_color'];
		}

		return $colors;

	}

	/**
	 * Enable branding field.
	 *
	 * @param string $template The existing template path.
	 * @return string The template path.
	 */
	public function enable_field( $template ) {

		return __DIR__ . '/templates/fields/enable.php';

	}

	/**
	 * Choose layout field.
	 *
	 * @param string $template The existing template path.
	 * @return string The template path.
	 */
	public function choose_layout_field( $template ) {

		return __DIR__ . '/templates/fields/choose-layout.php';

	}

	/**
	 * Admin bar logo field.
	 *
	 * @param string $template The existing template path.
	 * @return string The template path.
	 */
	public function admin_bar_logo_field( $template ) {

		return __DIR__ . '/templates/fields/admin-bar-logo.php';

	}

	/**
	 * Admin bar logo url field.
	 *
	 * @param string $template The existing template path.
	 * @return string The template path.
	 */
	public function admin_bar_logo_url_field( $template ) {

		return __DIR__ . '/templates/fields/admin-bar-logo-url.php';

	}

	/**
	 * Accent color field.
	 *
	 * @param string $template The existing template path.
	 * @return string The template path.
	 */
	public function accent_color_field( $template ) {

		return __DIR__ . '/templates/fields/accent-color.php';

	}

	/**
	 * Admin bar bg color field.
	 *
	 * @param string $template The existing template path.
	 * @return string The template path.
	 */
	public function admin_bar_color_field( $template ) {

		return __DIR__ . '/templates/fields/admin-bar-bg-color.php';

	}

	/**
	 * Admin menu bg color field.
	 *
	 * @param string $template The existing template path.
	 * @return string The template path.
	 */
	public function admin_menu_bg_color_field( $template ) {

		return __DIR__ . '/templates/fields/admin-menu-bg-color.php';

	}

	/**
	 * Admin submenu bg color field.
	 *
	 * @param string $template The existing template path.
	 * @return string The template path.
	 */
	public function admin_submenu_bg_color_field( $template ) {

		return __DIR__ . '/templates/fields/admin-submenu-bg-color.php';

	}

	/**
	 * Menu item color field.
	 *
	 * @param string $template The existing template path.
	 * @return string The template path.
	 */
	public function menu_item_color_field( $template ) {

		return __DIR__ . '/templates/fields/menu-item-color.php';

	}

	/**
	 * Menu item active color field.
	 *
	 * @param string $template The existing template path.
	 * @return string The template path.
	 */
	public function menu_item_active_color_field( $template ) {

		return __DIR__ . '/templates/fields/menu-item-active-color.php';

	}

}
