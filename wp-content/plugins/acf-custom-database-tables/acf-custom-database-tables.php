<?php
/**
 * Plugin Name: Advanced Custom Fields: Custom Database Tables
 * Plugin URI: https://www.hookturn.io/downloads/acf-custom-database-tables
 * Description: Store ACF data in custom database tables
 * Version: 1.1.0
 * Author: Phil Kurth (Hookturn)
 * Author URI: http://hookturn.io
 * License: GPLv3 or later
 */

use ACFCustomDatabaseTables\Activator;
use ACFCustomDatabaseTables\Container;
use ACFCustomDatabaseTables\Core;
use ACFCustomDatabaseTables\Provider;
use ACFCustomDatabaseTables\Psr4Autoloader;

// If this file is called directly, abort.
defined( 'WPINC' ) or die();

define( 'ACFCDT_VERSION', '1.1.0' );

include plugin_dir_path( __FILE__ ) . 'src/app/Psr4Autoloader.php';
include plugin_dir_path( __FILE__ ) . 'src/app/Activator.php';

/**
 * Main instance function.
 *
 * Need to interact with the ACF Custom Tables main instance? Call this function and go wild...just not too wild.
 * Seriously, though – if you are needing to access this, do so with caution as this plugin's internals are likely to
 * change significantly in its early stages of life.
 *
 * @return Core|null
 */
function acf_custom_database_tables() {

	static $instance;

	if ( Activator::is_acf_installed() and ! $instance ) {

		$dir = plugin_dir_path( __FILE__ );

		$loader = new Psr4Autoloader();
		$loader->register();
		$loader->addNamespace( 'ACFCustomDatabaseTables', $dir . 'src/app' );

		$container = new Container( [
			'plugin_file' => __FILE__,
			'plugin_dir' => $dir,
			'plugin_url' => plugin_dir_url( __FILE__ ),
			'plugin_name' => 'ACF Custom Database Tables',
			'plugin_version' => ACFCDT_VERSION,
			'plugin_author' => 'Phil Kurth (Hookturn)',
		] );

		$container->register( new Provider\FacadeProvider() );
		$container->register( new Provider\ACFInterceptProvider() );
		$container->register( new Provider\ACFProvider() );
		$container->register( new Provider\ViewProvider() );
		$container->register( new Provider\ModuleProvider() );
		$container->register( new Provider\ControllerProvider() );
		$container->register( new Provider\SchemaProvider() );
		$container->register( new Provider\AdminNotificationProvider() );
		$container->register( new Provider\AssetProvider() );
		$container->register( new Provider\ConfigProvider() );
		$container->register( new Provider\InterceptProvider() );
		$container->register( new Provider\SupportProvider() );
		$container->register( new Provider\FileIoProvider() );
		$container->register( new Provider\BackgroundProcessProvider() );
		$container->register( new Provider\ToolsProvider() );
		$container->register( new Provider\UpgradeProvider() );
		$container->register( new Provider\CompatProvider() );
		$container->register( new Provider\AjaxProvider() );

		$instance = new Core( $container );
	}

	return $instance;
}

add_action( 'plugins_loaded', function () {
	if ( $instance = acf_custom_database_tables() ) {
		$instance->boot();
		$instance->init();
	}
} );

register_activation_hook( __FILE__, function () {
	Activator::check_activation_constraints();
	Activator::set_activation_states();
} );