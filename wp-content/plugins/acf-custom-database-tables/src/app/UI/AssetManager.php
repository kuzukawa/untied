<?php

namespace ACFCustomDatabaseTables\UI;

/**
 * Class AssetManager
 * @package ACFCustomDatabaseTables\UI
 *
 * todo - support defer/async attributes
 *
 * Manages the registration and enqueuing of assets (CSS, JS) required by the plugin. This class accepts a definition
 * array on instantiation but asset registration methods could also be used directly provided they are hooked onto the
 * wp_enqueue_scripts|admin_enqueue_scripts hook/s.
 *
 * If registering assets using the definition array approach (recommended), the object automatically hooks into WP
 * and registers (does not enqueue) the assets accordingly.
 *
 * Any and all assets registered via this object need to be enqueued where appropriate. This can, of course, be done
 * via WP's regular wp_enqueue_xxxx functions if necessary, but this object does expose internal enqueue_xxxx methods
 * as well.
 *
 * NOTE:
 * Currently does not support external assets, but protocol/full URL checks will be added at some point to support this
 * functionality.
 *
 * DEFINITION ARRAY EXAMPLE:
 *      [
 *        'scripts' => [
 *            'script-handle' => [
 *                'src'       => 'js/filename-min.js',
 *                'src_debug' => 'js/filename.js',
 *                'deps'      => [ 'jquery' ],
 *                'version'   => false,
 *                'in_footer' => false,
 *            ],
 *            …
 *        ],
 *
 *        'styles'  => [
 *            'style-handle' => [
 *                'src'       => 'css/filename-min.css',
 *                'src_debug' => 'css/file-admin.css',
 *                'deps'      => [],
 *                'version'   => false,
 *                'media'     => 'all',
 *            ],
 *            …
 *        ],
 *      ]
 *
 */
class AssetManager {

	private $base_url;
	private $asset_definitions = [];
	private $scripts_registered = [];
	private $styles_registered = [];

	// Need admin assets? Change this to 'admin_enqueue_scripts' using $this->set_registration_hook()
	private $registration_hook = 'wp_enqueue_scripts';

	/**
	 * AssetManager constructor.
	 *
	 * @param string $base_url
	 */
	public function __construct( $base_url ) {
		$this->base_url = trailingslashit( $base_url );
	}

	/**
	 * Hooks into WordPress where appropriate
	 */
	public function init() {
		add_action( $this->registration_hook, [ $this, 'register_defined_scripts' ] );
		add_action( $this->registration_hook, [ $this, 'register_defined_styles' ] );
	}

	/**
	 * Sets the definitions array for auto-registration
	 *
	 * @param array $asset_definitions See class doc above for example
	 */
	public function set_asset_definitions( array $asset_definitions = [] ) {
		$this->asset_definitions = $asset_definitions;
	}

	/**
	 * Change the hook this instance uses to register assets defined in the asset definition array
	 *
	 * @param string $hook_name
	 */
	public function set_registration_hook( $hook_name ) {
		$this->registration_hook = $hook_name;
	}

	/**
	 * Assembles the correct asset src path based on whether or not we are in debug mode or not
	 *
	 * @param $definition
	 *
	 * @return string
	 */
	private function get_definition_src( $definition ) {
		$path = ( defined( 'SCRIPT_DEBUG' ) and SCRIPT_DEBUG )
			? $definition['src_debug']
			: $definition['src'];

		// todo - eventually support external refs by checking for complete URL here

		return $this->base_url . ltrim( $path, '/' );
	}

	/**
	 * Loops through script definition array (if exists) and registers each
	 */
	public function register_defined_scripts() {
		if ( isset( $this->asset_definitions['scripts'] ) and $this->asset_definitions['scripts'] ) {
			foreach ( $this->asset_definitions['scripts'] as $handle => $atts ) {
				$src = $this->get_definition_src( $atts );
				$deps = isset( $atts['deps'] ) ? $atts['deps'] : [];
				$version = isset( $atts['version'] ) ? $atts['version'] : false;
				$in_footer = isset( $atts['in_footer'] ) ? $atts['in_footer'] : false;

				$this->register_script( $handle, $src, $deps, $version, $in_footer );
			}
		}
	}

	/**
	 * Loops through styles definition array (if exists) and registers each
	 */
	public function register_defined_styles() {
		if ( isset( $this->asset_definitions['styles'] ) and $this->asset_definitions['styles'] ) {
			foreach ( $this->asset_definitions['styles'] as $handle => $atts ) {
				$src = $this->get_definition_src( $atts );
				$deps = isset( $atts['deps'] ) ? $atts['deps'] : [];
				$version = isset( $atts['version'] ) ? $atts['version'] : false;
				$media = isset( $atts['media'] ) ? $atts['media'] : 'all';

				$this->register_style( $handle, $src, $deps, $version, $media );
			}
		}
	}

	/**
	 * Registers a script with WordPress and tracks it's registered state (success or fail)
	 *
	 * @param $handle
	 * @param $src
	 * @param array $deps
	 * @param bool $version
	 * @param bool $in_footer
	 */
	public function register_script( $handle, $src, $deps = [], $version = false, $in_footer = false ) {
		$this->scripts_registered[ $handle ] = wp_register_script( $handle, $src, $deps, $version, $in_footer );
	}

	/**
	 * Registers a style with WordPress and tracks it's registered state (success or fail)
	 *
	 * @param $handle
	 * @param $src
	 * @param array $deps
	 * @param bool $version
	 * @param string $media
	 */
	public function register_style( $handle, $src, $deps = [], $version = false, $media = 'all' ) {
		$this->styles_registered[ $handle ] = wp_register_style( $handle, $src, $deps, $version, $media );
	}

	/**
	 * Enqueues the requested script, if successfully registered
	 *
	 * @param $handle
	 */
	public function enqueue_script( $handle ) {
		if ( isset( $this->scripts_registered[ $handle ] ) and $this->scripts_registered[ $handle ] ) {
			wp_enqueue_script( $handle );
		}
	}

	/**
	 * Enqueues the requested style, if successfully registered
	 *
	 * @param $handle
	 */
	public function enqueue_style( $handle ) {
		if ( isset( $this->styles_registered[ $handle ] ) and $this->styles_registered[ $handle ] ) {
			wp_enqueue_style( $handle );
		}
	}

}