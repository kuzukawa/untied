<?php

namespace ACFCustomDatabaseTables\Service;

use ACFCustomDatabaseTables\Settings;

class DiagnosticReporter {

	/** @var Settings */
	private $settings;

	/**
	 * DiagnosticReporter constructor.
	 *
	 * @param null $wpdb Deprecated — don't pass anything other than null.
	 * @param Settings $settings
	 */
	public function __construct( $wpdb, Settings $settings ) {
		if ( null !== $wpdb ) {
			_deprecated_argument( __METHOD__, '1.1 (ACF Custom Database Tables)', 'No longer injecting $wpdb due to object cache issues. Change this to NULL. Any related props will be removed in version 1.2' );
		}

		$this->settings = $settings;
	}

	public function site_url() {
		return esc_html( site_url() );
	}

	public function home_url() {
		return esc_html( home_url() );
	}

	public function php_version() {
		return function_exists( 'phpversion' )
			? esc_html( phpversion() )
			: '';
	}

	public function is_multisite() {
		return is_multisite();
	}

	public function webserver() {
		return esc_html( ! empty( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : '' );
	}

	public function wp_memory_limit() {
		return esc_html( WP_MEMORY_LIMIT );
	}

	public function php_time_limit() {
		return function_exists( 'ini_get' )
			? esc_html( ini_get( 'max_execution_time' ) )
			: '';
	}

	public function mysql_version() {
		global $wpdb;

		if ( $wpdb->use_mysqli ) {
			$version = mysqli_get_server_info( $wpdb->dbh );
		} elseif ( function_exists( 'mysql_get_server_info' ) ) {
			$version = mysql_get_server_info(); // @phpstan-ignore-line
		} else {
			$version = '?';
		}

		return esc_html( $version );
	}

	public function is_debug_mode_enabled() {
		return ( defined( 'WP_DEBUG' ) && WP_DEBUG );
	}

	public function is_script_debug_enabled() {
		return ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );
	}

	public function database_name() {
		global $wpdb;

		return esc_html( $wpdb->dbname );
	}

	public function database_table_prefix() {
		global $wpdb;

		return esc_html( $wpdb->prefix );
	}

	public function database_table_list() {
		global $wpdb;

		$tables = [];

		if ( $list = $wpdb->get_results( "SHOW TABLES;", ARRAY_N ) ) {
			$tables = array_map( function ( $t ) {
				return esc_html( $t[0] );
			}, $list );
		}

		return $tables;
	}

	public function wp_version() {
		return get_bloginfo( 'version' );
	}

	public function wp_directory() {
		return esc_html( ABSPATH );
	}

	public function theme_name() {
		$theme = wp_get_theme();

		return esc_html( $theme->Name );
	}

	public function theme_dir() {
		$theme = wp_get_theme();

		return esc_html( $theme->get_stylesheet_directory() );
	}

	public function is_child_theme() {
		$theme = wp_get_theme();

		return (bool) $theme->get( 'Template' );
	}

	public function get_active_plugins_data() {
		$plugins = $this->active_plugins();

		if ( $plugins ) {
			$plugins = array_map(
				function ( $plugin_dir_file ) {
					return get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_dir_file );
				},
				$plugins
			);
		}

		return $plugins;
	}

	/**
	 * Returns array of plugin dir/file strings
	 *
	 * @return array
	 */
	public function active_plugins() {
		return (array) get_option( 'active_plugins', array() );
	}

	public function stringify_active_plugin_data( array $plugin_data ) {
		return $plugin_data['Name'] . ' | ' . $plugin_data['Version'] . ' | ' . $plugin_data['AuthorName'] . ' | ' . $plugin_data['TextDomain'];
	}

	public function map_stringify_active_plugin_data( array $plugin_data_list ) {
		return array_map( array( $this, 'stringify_active_plugin_data' ), $plugin_data_list );
	}

	public function mu_plugins() {
		return wp_get_mu_plugins();
	}

	public function get_mu_plugins_data() {
		$plugins = $this->mu_plugins();

		if ( $plugins ) {
			$plugins = array_map(
				function ( $plugin_path ) {
					return get_plugin_data( $plugin_path );
				},
				$plugins
			);
		}

		return $plugins;
	}

	public function acf_version() {
		if ( function_exists( 'acf_get_setting' ) ) {
			return acf_get_setting( 'version' );
		}

		return '';
	}

	public function is_using_acf_json() {
		if ( function_exists( 'acf_get_setting' ) ) {
			$json = acf_get_setting( 'json' );
			$dir = $this->acf_json_directory();

			return $json and file_exists( $dir ) and is_readable( $dir );
		}

		return false;
	}

	public function acf_json_directory() {
		if ( function_exists( 'acf_get_setting' ) ) {
			return acf_get_setting( 'save_json' );
		}

		return '';
	}

	public function acf_json_directory_exists() {
		if ( $dir = $this->acf_json_directory() ) {
			return file_exists( $dir );
		}

		return false;
	}

	public function acf_json_load_points() {
		if ( function_exists( 'acf_get_setting' ) ) {
			return acf_get_setting( 'load_json' );
		}

		return array();
	}

	public function is_acf_json_directory_writable() {
		if ( $dir = $this->acf_json_directory() ) {
			return is_writable( $dir );
		}

		return false;
	}

	public function acfcdt_json_directory() {
		if ( $dir = $this->settings->get( 'json_dir' ) ) {
			return $dir;
		}

		return [];
	}

	public function acfcdt_json_directory_contents() {
		if ( $dir = $this->settings->get( 'json_dir' ) ) {
			$list = [];

			if ( is_readable( $dir ) ) {
				$list[] = $dir;
				$files = scandir( $dir );
				foreach ( $files as $file ) {
					if ( in_array( $file, [ '.', '..' ] ) ) {
						continue;
					}
					$list[] = "$dir/$file";
				}
			}

			return $list;
		}

		return [];
	}

	public function json_definition_file_list() {
		$list = $this->acfcdt_json_directory_contents();
		$list = array_filter( $list, function ( $file ) {
			return ( pathinfo( $file, PATHINFO_EXTENSION ) === 'json' );
		} );

		return $list ?: [];
	}

	public function json_definition_file_count() {
		return count( $this->json_definition_file_list() );
	}

	public function append_accessibility_info( $file_or_dir ) {
		$exists = file_exists( $file_or_dir ) ? 'exists' : 'non existent';
		$writable = is_writable( $file_or_dir ) ? 'writable' : 'not writable';
		$readable = is_readable( $file_or_dir ) ? 'readable' : 'not readable';

		return "$file_or_dir ($exists, $writable, $readable)";
	}

	public function map_accessibility_info( array $array_of_files_or_dirs ) {
		return array_map( array( $this, 'append_accessibility_info' ), $array_of_files_or_dirs );
	}

	public function acfcdt_table_map_cache_dir() {
		return $this->settings->get( 'table_map_cache_dir' );
	}

	public function acfcdt_table_map_cache_file_exists() {
		$file = $this->settings->get( 'table_map_cache_dir' ) . '/_table_map.php';

		return file_exists( $file );
	}

	public function acfcdt_table_map_cache_dir_contents() {
		if ( $dir = $this->settings->get( 'table_map_cache_dir' ) ) {
			if ( is_readable( $dir ) ) {
				$list[] = $dir;
				$files = scandir( $dir );
				foreach ( $files as $file ) {
					if ( in_array( $file, [ '.', '..' ] ) ) {
						continue;
					}
					$list[] = "$dir/$file";
				}

				return $list;
			}
		}

		return [];
	}

	public function settings() {

		$values = $this->settings->get( 'store_acf_values_in_core_meta' ) ? 'TRUE (default)' : 'FALSE';
		$keys = $this->settings->get( 'store_acf_keys_in_core_meta' ) ? 'TRUE (default)' : 'FALSE';
		$joins = $this->settings->get( 'enable_join_tables_globally' ) ? 'TRUE' : 'FALSE (default)';
		$subs = $this->settings->get( 'enable_sub_tables_globally' ) ? 'TRUE' : 'FALSE (default)';
		$wp_all_import = $this->settings->get( 'enable_wp_all_import_plugin_compat' ) ? 'TRUE' : 'FALSE (default)';
		$modules = $this->settings->get( 'activate_modules' );
		$intcast_module = $modules['integer_type_cast'] ? 'ACTIVE (default)' : 'INACTIVE';
		$serialized_module = $modules['serialized_data'] ? 'ACTIVE' : 'INACTIVE (default)';
		$after_scheme_update_module = $modules['after_table_schema_update'] ? 'ACTIVE' : 'INACTIVE (default)';
		$intercept_run_control = $modules['intercept_run_control'] ? 'ACTIVE (default)' : 'INACTIVE';

		$data = [
			"store_acf_values_in_core_meta : $values",
			"store_acf_keys_in_core_meta : $keys",
			"enable_join_tables_globally : $joins",
			"enable_sub_tables_globally : $subs",
			"enable_wp_all_import_plugin_compat : $wp_all_import",
			"[module] integer_type_cast : $intcast_module",
			"[module] serialized_data : $serialized_module",
			"[module] after_table_schema_update : $after_scheme_update_module",
			"[module] intercept_run_control : $intercept_run_control",
		];

		return $data;
	}

	public function is_using_external_object_cache() {
		return wp_using_ext_object_cache();
	}

	public function system_checks() {
		return [
			[
				'name' => 'PHP Version',
				'minimum' => '5.6',
				'current' => $this->php_version(),
				'test' => version_compare( $this->php_version(), '5.6' ) >= 0,
				'notice' => 'You need to upgrade your PHP version to continue.'
			],
			[
				'name' => 'WP Version',
				'minimum' => '4.9',
				'current' => $this->wp_version(),
				'test' => version_compare( $this->wp_version(), '4.1' ) >= 0,
				'notice' => 'You need to update your version of WordPress to continue.'
			],
			[
				'name' => 'ACF Version',
				'minimum' => '5.6.10',
				'current' => $this->acf_version(),
				'test' => version_compare( $this->acf_version(), '5.6.10' ) >= 0,
				'notice' => 'You need to update your version of ACF to continue.'
			],
//			[
//				'name'    => 'Using ACF JSON',
//				'minimum' => 'Required',
//				'current' => $this->is_using_acf_json() ? 'Yes' : 'No',
//				'test'    => $this->is_using_acf_json(),
//				'notice'  => 'You need to be using ACF JSON to continue.'
//			],
//			[
//				'name'    => 'ACF JSON Directory Exists',
//				'minimum' => 'Required',
//				'current' => $this->acf_json_directory_exists() ? 'Yes' : 'No',
//				'test'    => $this->acf_json_directory_exists(),
//				'notice'  => 'You need to create an <code>/acf-json</code> directory inside your theme directory.'
//			],
//			[
//				'name'    => 'JSON Table Definition Files',
//				'minimum' => '1',
//				'current' => $this->json_definition_file_count(),
//				'test'    => $this->json_definition_file_count() >= 1,
//				'notice'  => 'You don\'t appear to have created any JSON table definition files. You need to create these before you can continue.'
//			],
		];
	}

	/**
	 * Extracts only the failed system checks from the self::system_checks() method.
	 *
	 * @param array $checks_array Array of system checks returned by self::system_checks()
	 *
	 * @return array
	 * @see \ACFCustomDatabaseTables\Service\DiagnosticReporter::system_checks()
	 *
	 */
	public function failed_system_checks( array $checks_array ) {
		return array_filter( $checks_array, function ( $check ) {
			return ! $check['test'];
		} );
	}

	/**
	 * @return bool
	 */
	public function system_passes() {
		return (bool) ! $this->failed_system_checks( $this->system_checks() );
	}

	/**
	 * Any specific combinations of settings that will cause problems can be bundled up into an array here.
	 *
	 * @return array
	 */
	public function red_flags() {
		$flags = [];

		if ( ! $this->is_using_acf_json() and ! $this->settings->get( 'store_acf_keys_in_core_meta' ) ) {
			$flags[] = 'ACF JSON not detected while app is configured to bypass field key storage. get_field() will probably fail on the front end.';
		}

		return $flags ?: [ 'none' ];
	}

}