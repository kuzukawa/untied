<?php

namespace ACFCustomDatabaseTables;

use ACFCustomDatabaseTables\Utils\Arr;
use ACFCustomDatabaseTables\Utils\View;

/**
 * Class Options
 * @package ACFCustomDatabaseTables
 *
 * Manages all plugin settings in one options meta entry.
 */
class Options {

	const OPTION_NAME = 'acfcdt_options';
	const OPTION_GROUP = 'acfcdt-settings-tab';
	const PAGE = 'acfcdt-settings-tab';

	/**
	 * @var array The default plugin settings array as encoded in config/settings.php
	 */
	private $defaults = [];

	/**
	 * @var array The plugin settings array as stored in the DB.
	 */
	private $options = [];

	/**
	 * @param array $default_settings
	 */
	public function __construct( array $default_settings ) {
		$this->defaults = $default_settings;
	}

	public function to_array() {
		return $this->options;
	}

	/**
	 * Load up the settings.
	 */
	public function init() {
		$this->options = $this->fetch_options();

		add_action( 'admin_init', [ $this, '_register_setting' ] );
		add_action( 'admin_init', [ $this, '_register_settings_sections' ] );
		add_action( 'admin_init', [ $this, '_register_settings_fields' ] );
	}

	/**
	 * Register the option
	 */
	public function _register_setting() {
		register_setting(
			static::OPTION_GROUP,
			static::OPTION_NAME,
			[ 'sanitize_callback' => [ $this, '_sanitize_and_format_input' ] ]
		);
	}

	public function _register_settings_sections() {
		add_settings_section(
			'acfcdt-complex-field-support',
			'', // intentionally empty as we don't currently need a section title
			'__return_null',
			static::PAGE
		);

		add_settings_section(
			'acfcdt-global-settings-section',
			'', // intentionally empty as we don't currently need a section title
			'__return_null',
			static::PAGE
		);

		add_settings_section(
			'acfcdt-data-storage-section',
			'', // intentionally empty as we don't currently need a section title
			'__return_null',
			static::PAGE
		);

		add_settings_section(
			'acfcdt-compatibility-section',
			'', // intentionally empty as we don't currently need a section title
			'__return_null',
			static::PAGE
		);

		add_settings_section(
			'acfcdt-module-section',
			'', // intentionally empty as we don't currently need a section title
			'__return_null',
			static::PAGE
		);
	}

	public function _register_settings_fields() {
		add_settings_field( 'acfcdt-activate-modules', 'Modules', function ( $args ) {
			$defaults = Arr::get( $this->defaults, 'activate_modules', [] );
			$module_names = [
				'integer_type_cast' => 'Integer type casting',
				//'serialized_data' => 'Serialize data instead of JSON encoding',
				'after_table_schema_update' => 'Enable after table schema update actions',
				'intercept_run_control' => 'Enable intercept run control actions',
			];
			$module_descriptions = [
				'integer_type_cast' => 'Where possible, cast numerical strings as integers for cleaner JSON encoded data.',
				//'serialized_data' => 'Serialize complex data values instead of the default JSON-encoding.',
				'after_table_schema_update' => 'Enable actions to run custom code after table scheme is updated. <a href="https://hookturn.io/docs/acf-custom-database-tables/1.1/running-custom-actions-after-a-table-is-updated-or-created/" target="_blank">Learn more</a>',
				'intercept_run_control' => 'Enable actions to control data storage/retrieval at run time. <a href="https://hookturn.io/docs/acf-custom-database-tables/1.1/disabling-storage-or-retrieval-to-and-from-custom-database-tables/" target="_blank">Learn more</a>',
			];
			// If a module's activation state is being controlled by a settings filter hook, we need to make sure the
			// user is aware and that correct setting is displayed in the panel.
			$filtered = apply_filters( 'acfcdt/settings/activate_modules', [] );
			foreach ( $this->options['activate_modules'] as $module => $enabled ) {

				// Don't show the serialized data module in the settings as changing this after data is already stored
				// will create issues retrieving that data.
				if ( $module === 'serialized_data' ) {
					continue;
				}

				View::render( 'check-panel', [
					'enabled' => Arr::get( $filtered, $module, $enabled ),
					'readonly' => isset( $filtered[ $module ] ),
					'id' => "acfcdt-{$module}",
					'name' => sprintf( '%s[activate_modules][%s]', static::OPTION_NAME, $module ),
					'title' => Arr::get( $module_names, $module, $module ),
					'default' => Arr::get( $defaults, $module, false ),
					'description' => Arr::get( $module_descriptions, $module, '' ),
				] );
			}

		}, static::PAGE, 'acfcdt-module-section' );

		add_settings_field( 'acfcdt-complex-field-support', 'Complex Field Support', function ( $args ) {
			$default = Arr::get( $this->defaults, 'enable_repeater_field_support', false );
			$filtered = apply_filters( 'acfcdt/settings/enable_repeater_field_support', null );
			$readonly = $filtered !== null;
			View::render( 'check-panel', [
				'enabled' => $readonly
					? $filtered
					: Arr::get( $this->options, 'enable_repeater_field_support', $default ),
				'readonly' => $readonly,
				'id' => "acfcdt-repeater-support",
				'name' => sprintf( '%s[enable_repeater_field_support]', static::OPTION_NAME ),
				'title' => 'Enable repeater support',
				'default' => $default,
				'description' => 'Allow repeater fields to store in custom tables. <a href="https://hookturn.io/docs/acf-custom-database-tables/1.1/working-with-repeater-fields/" target="_blank">Learn more</a>',
			] );
		}, static::PAGE, 'acfcdt-complex-field-support' );

		add_settings_field( 'acfcdt-core-meta-storage', 'Core Meta Storage', function ( $args ) {
			$default = Arr::get( $this->defaults, 'store_acf_values_in_core_meta', false );
			$filtered = apply_filters( 'acfcdt/settings/store_acf_values_in_core_meta', null );
			$readonly = $filtered !== null;
			View::render( 'check-panel', [
				'enabled' => $readonly
					? $filtered
					: Arr::get( $this->options, 'store_acf_values_in_core_meta', $default ),
				'readonly' => $readonly,
				'id' => 'acfcdt-store-acf-values-in-core-meta',
				'name' => sprintf( '%s[store_acf_values_in_core_meta]', static::OPTION_NAME ),
				'title' => 'Store ACF values in core meta tables (global)',
				'default' => $default,
				'description' => 'Continue storing meta value in core meta tables alongside custom tables. This is the global default/fallback setting. For granular control over core meta storage, <a href="https://hookturn.io/docs/acf-custom-database-tables/1.1/bypassing-data-storage-in-core-meta-tables/" target="_blank">learn more</a>',
			] );

			$default = Arr::get( $this->defaults, 'store_acf_keys_in_core_meta', false );
			$filtered = apply_filters( 'acfcdt/settings/store_acf_keys_in_core_meta', null );
			$readonly = $filtered !== null;
			View::render( 'check-panel', [
				'enabled' => $readonly
					? $filtered
					: Arr::get( $this->options, 'store_acf_keys_in_core_meta', $default ),
				'readonly' => $readonly,
				'id' => 'acfcdt-store-acf-keys-in-core-meta',
				'name' => sprintf( '%s[store_acf_keys_in_core_meta]', static::OPTION_NAME ),
				'title' => 'Store ACF keys in core meta tables (global)',
				'default' => $default,
				'description' => 'Continue storing field key references in core meta tables. This is the global default/fallback setting. For granular control over core meta storage, <a href="https://hookturn.io/docs/acf-custom-database-tables/1.1/bypassing-data-storage-in-core-meta-tables/" target="_blank">learn more</a>',
			] );
		}, static::PAGE, 'acfcdt-data-storage-section' );

		add_settings_field( 'acfcdt-global-table-types', 'Global Settings', function ( $args ) {
			$default = Arr::get( $this->defaults, 'enable_sub_tables_globally', false );
			$filtered = apply_filters( 'acfcdt/settings/enable_sub_tables_globally', null );
			$readonly = $filtered !== null;
			View::render( 'check-panel', [
				'enabled' => $readonly
					? $filtered
					: Arr::get( $this->options, 'enable_sub_tables_globally', $default ),
				'readonly' => $readonly,
				'id' => 'acfcdt-enable-sub-tables-globally',
				'name' => sprintf( '%s[enable_sub_tables_globally]', static::OPTION_NAME ),
				'title' => 'Enable sub tables globally',
				'default' => $default,
				'description' => 'Create sub tables for all eligible fields by default. <a href="https://hookturn.io/docs/acf-custom-database-tables/1.1/globally-activate-sub-tables/" target="_blank">Learn more</a>',
			] );

			$default = Arr::get( $this->defaults, 'enable_join_tables_globally', false );
			$filtered = apply_filters( 'acfcdt/settings/enable_join_tables_globally', null );
			$readonly = $filtered !== null;
			View::render( 'check-panel', [
				'enabled' => $readonly
					? $filtered
					: Arr::get( $this->options, 'enable_join_tables_globally', $default ),
				'readonly' => $readonly,
				'id' => 'acfcdt-enable-join-tables-globally',
				'name' => sprintf( '%s[enable_join_tables_globally]', static::OPTION_NAME ),
				'title' => 'Enable join tables globally',
				'default' => $default,
				'description' => 'Create join tables for all eligible fields by default. <a href="https://hookturn.io/docs/acf-custom-database-tables/1.1/activating-join-tables-on-eligible-fields/" target="_blank">Learn more</a>',
			] );
		}, static::PAGE, 'acfcdt-global-settings-section' );

		add_settings_field( 'acfcdt-compatibility', 'Compatibility', function ( $args ) {
			$default = Arr::get( $this->defaults, 'enable_wp_all_import_plugin_compat', false );
			$filtered = apply_filters( 'acfcdt/settings/enable_wp_all_import_plugin_compat', null );
			$readonly = $filtered !== null;
			View::render( 'check-panel', [
				'enabled' => $readonly
					? $filtered
					: Arr::get( $this->options, 'enable_wp_all_import_plugin_compat', $default ),
				'readonly' => $readonly,
				'id' => 'acfcdt-enable-wp-all-import-plugin-compat',
				'name' => sprintf( '%s[enable_wp_all_import_plugin_compat]', static::OPTION_NAME ),
				'title' => 'Enable <em>WP All Import</em> plugin compatibility <strong class="acfcdt-beta-tag">Experimental</strong>',
				'default' => $default,
				'description' => 'Enable a compatibility layer to route imported field data into custom database tables. <a href="https://hookturn.io/docs/acf-custom-database-tables/1.1/wp-all-import-compatibility/" target="_blank">Learn more</a>',
			] );
		}, static::PAGE, 'acfcdt-compatibility-section' );
	}

	/**
	 * Mutates form data on its way into the database.
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function _sanitize_and_format_input( $value ) {
		$stored = $this->fetch_options();

		// Loop through current active module settings. If inbound value contains active setting for the module, set it
		// as true. If the module is not present in the inbound payload, set as false. This works around the default
		// HTML value being 'on' and only checked items appearing in the array. This way we get all settings storing as
		// they are configured in the admin.
		foreach ( (array) $stored['activate_modules'] as $module => $state ) {
			$value['activate_modules'][ $module ] = isset( $value['activate_modules'][ $module ] );
		}

		$value['store_acf_values_in_core_meta'] = isset( $value['store_acf_values_in_core_meta'] );
		$value['store_acf_keys_in_core_meta'] = isset( $value['store_acf_keys_in_core_meta'] );
		$value['enable_repeater_field_support'] = isset( $value['enable_repeater_field_support'] );
		$value['enable_sub_tables_globally'] = isset( $value['enable_sub_tables_globally'] );
		$value['enable_join_tables_globally'] = isset( $value['enable_join_tables_globally'] );
		$value['enable_wp_all_import_plugin_compat'] = isset( $value['enable_wp_all_import_plugin_compat'] );

		return $this->options = array_replace_recursive( $stored, $value );
	}

	/**
	 * Retrieve the options from the DB. If nothing is stored yet, store the defaults.
	 *
	 * @return array
	 */
	private function fetch_options() {
		$stored = get_option( static::OPTION_NAME, false );

		// If the `column_data_type_override` module is still in the settings array, remove it. This module is no longer
		// in use and has no relevance. Unsetting this guards against it appearing in the plugin settings.
		// Note: We can remove this in version 1.2.
		if ( isset( $stored['activate_modules']['column_data_type_override'] ) ) {
			unset( $stored['activate_modules']['column_data_type_override'] );
		}

		if ( $stored === false ) {
			update_option( static::OPTION_NAME, $this->get_filtered_defaults(), true );
			$stored = get_option( static::OPTION_NAME, [] );
		}

		// Ensure the filtered JSON directory is maintained.
		// The directory configurations are picked up and filtered before being passed to this object. This worked fine
		// when our settings/config system was all hard-coded and file-based but since introducing stored options in
		// version 1.1, code-based control over the JSON directory was rendered useless after the options had been
		// stored to the database as the filtered values were being overridden by the previously stored values. These
		// lines of code ensure the filtered values are always in play.
		$stored['immutable']['json_dir'] = $this->defaults['immutable']['json_dir'];
		$stored['immutable']['table_map_cache_dir'] = $this->defaults['immutable']['table_map_cache_dir'];

		return (array) $stored;
	}

	/**
	 * Get the default settings array with user filters applied. This is only really needed whilst we transition from
	 * code-only config to UI-supported config to ensure the initial array of settings stored in the options table are
	 * a correct representation of the current installations config. We'll be able to remove this method in
	 * version 1.1.1.
	 *
	 * todo - remove this in version 1.1.1 and just access the defaults property directly in
	 *  \ACFCustomDatabaseTables\Options::fetch_options()
	 */
	private function get_filtered_defaults() {
		$d = [
			'enable_repeater_field_support' => apply_filters( 'acfcdt/settings/enable_repeater_field_support', Arr::get( $this->defaults, 'enable_repeater_field_support', false ) ),
			'store_acf_values_in_core_meta' => apply_filters( 'acfcdt/settings/store_acf_values_in_core_meta', Arr::get( $this->defaults, 'store_acf_values_in_core_meta', false ) ),
			'store_acf_keys_in_core_meta' => apply_filters( 'acfcdt/settings/store_acf_keys_in_core_meta', Arr::get( $this->defaults, 'store_acf_keys_in_core_meta', false ) ),
			'enable_join_tables_globally' => apply_filters( 'acfcdt/settings/enable_join_tables_globally', Arr::get( $this->defaults, 'enable_join_tables_globally', false ) ),
			'enable_sub_tables_globally' => apply_filters( 'acfcdt/settings/enable_sub_tables_globally', Arr::get( $this->defaults, 'enable_sub_tables_globally', false ) ),
			'enable_wp_all_import_plugin_compat' => apply_filters( 'acfcdt/settings/enable_wp_all_import_plugin_compat', Arr::get( $this->defaults, 'enable_wp_all_import_plugin_compat', false ) ),
			'activate_modules' => apply_filters( 'acfcdt/settings/activate_modules', Arr::get( $this->defaults, 'activate_modules', [] ) ),
		];

		return array_replace_recursive( $this->defaults, $d );
	}

	private function show_error( $message ) {
		add_settings_error( static::OPTION_NAME, '', $message, 'error' );
	}

	private function show_success( $message ) {
		add_settings_error( static::OPTION_NAME, '', $message, 'success' );
	}

}