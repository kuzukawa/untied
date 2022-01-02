<?php
/**
 * Branding output.
 *
 * @package Ultimate_Dashboard_Pro
 */

namespace UdbPro\Branding;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use Udb\Base\Base_Output;
use UdbPro\Helpers\Branding_Helper;

/**
 * Class to setup branding output.
 */
class Branding_Output extends Base_Output {

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
	 * Init the class setup.
	 */
	public static function init() {

		$class = new self();
		$class->setup();

	}

	/**
	 * Setup branding output.
	 */
	public function setup() {

		add_action( 'admin_enqueue_scripts', array( self::get_instance(), 'dashboard_styles' ), 100 );
		add_action( 'wp_enqueue_scripts', array( self::get_instance(), 'frontend_styles' ), 100 );

		add_action( 'admin_head', array( self::get_instance(), 'admin_styles' ), 100 );
		add_action( 'admin_head', array( self::get_instance(), 'admin_styles_preview' ), 120 );

		add_filter( 'udb_branding_dashboard_styles', array( self::get_instance(), 'minify_css' ), 20 );
		add_filter( 'udb_branding_admin_styles', array( self::get_instance(), 'minify_css' ), 20 );
		add_filter( 'udb_branding_frontend_styles', array( self::get_instance(), 'minify_css' ), 20 );
		add_filter( 'udb_branding_login_styles', array( self::get_instance(), 'minify_css' ), 20 );

		add_action( 'admin_bar_menu', array( self::get_instance(), 'replace_admin_bar_logo' ), 11 );
		add_filter( 'udb_admin_bar_logo_url', array( self::get_instance(), 'change_admin_bar_logo_url' ) );
		add_action( 'admin_bar_menu', array( self::get_instance(), 'remove_admin_bar_logo' ), 99 );

		add_action( 'adminmenu', array( self::get_instance(), 'modern_admin_bar_logo' ) );
		add_action( 'adminmenu', array( self::get_instance(), 'modern_admin_bar_logo_preview' ), 30 );

	}

	/**
	 * Enqueue dashboard styles.
	 */
	public function dashboard_styles() {

		$udb_dashboard_styles = $this->get_dashboard_styles();
		wp_add_inline_style( 'udb-dashboard', $udb_dashboard_styles );

	}

	/**
	 * Get dashboard styles.
	 *
	 * @return string The dashboard CSS.
	 */
	public function get_dashboard_styles() {

		$css = '';

		ob_start();
		include_once __DIR__ . '/inc/widget-styles.css.php';
		$css = ob_get_clean();

		return apply_filters( 'udb_branding_dashboard_styles', $css );

	}

	/**
	 * Print admin styles.
	 *
	 * @param bool $inherit_blueprint Whether the admin styles source is inherited from blueprint.
	 */
	public function admin_styles( $inherit_blueprint = false ) {

		$branding         = get_option( 'udb_branding', array() );
		$branding_enabled = isset( $branding['enabled'] );
		$active_layout    = isset( $branding['layout'] ) && 'modern' === $branding['layout'] ? 'modern' : 'default';

		if ( ! $inherit_blueprint && $this->screen()->is_branding() ) {
			return;
		}

		if ( ! $branding_enabled ) {
			return;
		}

		echo '<style class="udb-admin-colors-output udb-' . $active_layout . '-admin-colors-output ' . ( $inherit_blueprint ? 'udb-inherited-from-blueprint' : '' ) . '">' . $this->get_admin_styles( $active_layout ) . '</style>';

	}

	/**
	 * Print admin styles for preview purpose.
	 */
	public function admin_styles_preview() {

		$branding         = get_option( 'udb_branding', array() );
		$branding_enabled = isset( $branding['enabled'] );
		$active_layout    = isset( $branding['layout'] ) && 'modern' === $branding['layout'] ? 'modern' : 'default';

		if ( ! $this->screen()->is_branding() ) {
			return;
		}

		echo '<style' . ( ! $branding_enabled || 'default' !== $active_layout ? ' type="text/udb"' : '' ) . ' class="udb-admin-colors-preview udb-admin-colors-output udb-default-admin-colors-output">' . $this->get_admin_styles( 'default' ) . '</style>
		';

		echo '<style' . ( ! $branding_enabled || 'modern' !== $active_layout ? ' type="text/udb"' : '' ) . ' class="udb-admin-colors-preview udb-admin-colors-output udb-modern-admin-colors-output">' . $this->get_admin_styles( 'modern' ) . '</style>';

	}

	/**
	 * Get admin styles.
	 *
	 * @return string The admin CSS.
	 */
	public function get_admin_styles( $layout = 'default' ) {

		$css = '';

		ob_start();

		require __DIR__ . '/inc/admin-styles-' . $layout . '.css.php';

		$css = ob_get_clean();

		return apply_filters( 'udb_branding_admin_styles', $css );

	}

	/**
	 * Enqueue frontend styles.
	 */
	public function frontend_styles() {

		$branding_helper = new Branding_Helper();

		if ( ! $branding_helper->is_enabled() ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			return;
		}

		$udb_frontend_styles = $this->get_frontend_styles();
		wp_add_inline_style( 'admin-bar', $udb_frontend_styles );

	}

	/**
	 * Get frontend styles.
	 *
	 * @return string The frontend CSS.
	 */
	public function get_frontend_styles() {

		$css = '';

		ob_start();
		include_once __DIR__ . '/inc/frontend-styles.css.php';
		$css = ob_get_clean();

		return apply_filters( 'udb_branding_frontend_styles', $css );

	}

	/**
	 * Minify CSS
	 *
	 * @param string $css The css.
	 *
	 * @return string the minified CSS.
	 */
	public function minify_css( $css ) {

		// Remove comments.
		$css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );

		// Remove spaces.
		$css = str_replace( ': ', ':', $css );
		$css = str_replace( ' {', '{', $css );
		$css = str_replace( ', ', ',', $css );
		$css = str_replace( array( "\r\n", "\r", "\n", "\t", '  ', '    ', '    ' ), '', $css );

		return $css;

	}

	/**
	 * Replace admin bar logo.
	 *
	 * We do this to add a filter to the logo URL.
	 *
	 * @param object $wp_admin_bar The wp admin bar.
	 */
	public function replace_admin_bar_logo( $wp_admin_bar ) {

		$wp_admin_bar->remove_menu( 'wp-logo' );

		$args = array(
			'id'    => 'wp-logo',
			'title' => '<span class="ab-icon"></span>',
			'href'  => apply_filters( 'udb_admin_bar_logo_url', network_site_url() ),
			'meta'  => array(
				'class' => 'udb-wp-logo',
			),
		);

		if ( is_admin() && $this->screen()->is_branding() ) {
			$branding  = get_option( 'udb_branding' );
			$classname = 'udb-wp-logo';

			if ( isset( $branding['enabled'] ) ) {
				if ( isset( $branding['remove_admin_bar_logo'] ) ) {
					$classname = 'udb-wp-logo udb-is-hidden';
				} else {
					if ( 'modern' === $branding['layout'] ) {
						$classname = 'udb-wp-logo udb-is-hidden';
					}
				}
			}

			$args['meta'] = array(
				'class' => $classname,
			);
		}

		$wp_admin_bar->add_menu( $args );

	}

	/**
	 * Change admin bar logo URL.
	 *
	 * Doesn't require separate multisite support!
	 *
	 * @param string $admin_bar_logo_url The admin bar logo URL.
	 *
	 * @return string The updated admin bar logo URL.
	 */
	public function change_admin_bar_logo_url( $admin_bar_logo_url ) {

		$branding = get_option( 'udb_branding' );

		if ( ! isset( $branding['enabled'] ) ) {
			return $admin_bar_logo_url;
		}

		if ( isset( $branding['remove_admin_bar_logo'] ) ) {
			return $admin_bar_logo_url;
		}

		if ( ! empty( $branding['admin_bar_logo_url'] ) ) {
			$admin_bar_logo_url = $branding['admin_bar_logo_url'];
		}

		return $admin_bar_logo_url;

	}

	/**
	 * Remove admin bar logo.
	 *
	 * @param object $wp_admin_bar The wp admin bar.
	 */
	public function remove_admin_bar_logo( $wp_admin_bar ) {

		$branding = get_option( 'udb_branding' );

		if ( ! is_admin() || ! $this->screen()->is_branding() ) {
			if ( isset( $branding['remove_admin_bar_logo'] ) ) {
				$wp_admin_bar->remove_node( 'wp-logo' );
			}
		}

	}

	/**
	 * Modern layout: custom admin bar logo.
	 *
	 * @param bool $inherit_blueprint Whether the admin styles source is inherited from blueprint.
	 */
	public function modern_admin_bar_logo( $inherit_blueprint = false ) {

		$branding_helper     = new Branding_Helper();
		$is_branding_enabled = $branding_helper->is_enabled();
		$branding            = get_option( 'udb_branding' );

		// Stop here if branding is not enabled.
		if ( ! $is_branding_enabled ) {
			return;
		}

		// Stop here if modern layout is not selected.
		if ( ! isset( $branding['layout'] ) || 'modern' !== $branding['layout'] ) {
			return;
		}

		// If no logo is selected, use default.
		if ( ! empty( $branding['admin_bar_logo_image'] ) ) {
			$logo = $branding['admin_bar_logo_image'];
		} else {
			$logo = $this->url . '/assets/images/ultimate-dashboard-logo.png';
		}

		// If no logo url was set, use default.
		if ( ! empty( $branding['admin_bar_logo_url'] ) ) {
			$url = $branding['admin_bar_logo_url'];
		} else {
			$url = network_site_url();
		}

		// Let's add a filter, in case someone wants to dynamically change the logo.
		$logo = apply_filters( 'udb_admin_bar_logo_image', $logo );
		?>

		<li class="udb-admin-logo-wrapper udb-admin-logo-wrapper-output <?php echo ( $inherit_blueprint ? 'udb-inherited-from-blueprint' : '' ); ?>">
			<a href="<?php echo esc_url( $url ); ?>">
				<img class="udb-admin-logo" src="<?php echo esc_url( $logo ); ?>" />
			</a>
		</li>

		<?php

	}

	/**
	 * Modern layout: custom admin bar logo for preview purpose.
	 */
	public function modern_admin_bar_logo_preview() {

		// Only for branding's settings page.
		if ( ! $this->screen()->is_branding() ) {
			return;
		}

		$branding = get_option( 'udb_branding' );

		// If the saved layout is modern, then we already have the markup.
		if ( isset( $branding['layout'] ) && 'modern' === $branding['layout'] ) {
			return;
		}

		// If no logo is selected, use default.
		if ( ! empty( $branding['admin_bar_logo_image'] ) ) {
			$logo = $branding['admin_bar_logo_image'];
		} else {
			$logo = $this->url . '/assets/images/ultimate-dashboard-logo.png';
		}

		// If no logo url was set, use default.
		if ( ! empty( $branding['admin_bar_logo_url'] ) ) {
			$url = $branding['admin_bar_logo_url'];
		} else {
			$url = network_site_url();
		}

		// Let's add a filter, in case someone wants to dynamically change the logo.
		$logo = apply_filters( 'udb_admin_bar_logo_image', $logo );
		?>

		<li class="udb-admin-logo-wrapper udb-admin-logo-wrapper-preview udb-is-hidden">
			<a href="<?php echo esc_url( $url ); ?>">
				<img class="udb-admin-logo" src="<?php echo esc_url( $logo ); ?>" />
			</a>
		</li>

		<?php

	}

	/**
	 * Print color in rgba format from hex color.
	 *
	 * @param string     $hex_color Color in hex format.
	 * @param int|string $opacity The alpha opacity part of an rgba color.
	 */
	public function print_rgba_from_hex( $hex_color, $opacity ) {

		if ( ! class_exists( '\Udb\Helpers\Color_Helper' ) ) {
			echo esc_attr( $hex_color );
			return;
		}

		$color_helper = new \Udb\Helpers\Color_Helper();

		$rgb = $color_helper->hex_to_rgb( $hex_color );

		$rgba_string = 'rgba(' . $rgb[0] . ', ' . $rgb[1] . ', ' . $rgb[2] . ', ' . $opacity . ')';

		echo esc_attr( $rgba_string );

	}

}
