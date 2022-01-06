<?php

namespace ACFCustomDatabaseTables\Compat\WpAllImport;

use ACFCustomDatabaseTables\Model\ACFSelector;

/**
 * Class ApiHookHotfix
 * @package ACFCustomDatabaseTables\Compat\WpAllImport
 *
 * Ensures ACFs API filters needed by ACF Custom Database Tables are fired.
 */
class ApiHookHotfix {

	/** @var CoreMetaBypass */
	private $core_meta_bypass;

	/**
	 * @param CoreMetaBypass $core_meta_bypass
	 */
	public function __construct( CoreMetaBypass $core_meta_bypass ) {
		$this->core_meta_bypass = $core_meta_bypass;
	}

	public function init() {
		add_action( 'pmxi_before_post_import', [ $this, '_run' ] );
		add_action( 'pmxi_before_xml_import', [ $this, '_run' ] );
	}

	public function _run() {
		if ( ! has_filter( 'acf/update_value', [ $this, '_fire_acf_pre_update_value_hook' ] ) ) {
			add_filter( 'acf/update_value', [ $this, '_fire_acf_pre_update_value_hook' ], 1, 4 );
		}

		if ( ! has_filter( 'acf/update_value', [ $this, '_fire_acf_pre_update_metadata_hook' ] ) ) {
			add_filter( 'acf/update_value', [ $this, '_fire_acf_pre_update_metadata_hook' ], 999999999999999999999, 4 );
		}
	}

	public function _fire_acf_pre_update_value_hook( $value, $id, $field_array, $value2 ) {
		$id = $this->fix_id_format( $id );
		apply_filters( "acf/pre_update_value", null, $value, $id, $field_array );

		return $value;
	}

	public function _fire_acf_pre_update_metadata_hook( $value, $id, $field_array, $value2 ) {
		$id = $this->fix_id_format( $id );
		$check = apply_filters( "acf/pre_update_metadata", null, $id, $field_array['name'], $value, false );

		// If the filter returns a value other than null, it means this particular field is marked for core
		// bypass. Let the relevant object know so we can honour core bypasses on import.
		if ( $check !== null ) {
			$selector = ACFSelector::make( $id );
			if ( in_array( $selector->type, [ 'post', 'user' ] ) ) {
				$this->core_meta_bypass->add_field( $selector->id, $field_array, $selector->type );
			}
		}

		return $value;
	}

	/**
	 * WP All Import is incorrectly passing the wrong ID format to the acf/update_value filter. Instead of passing a
	 * string that can be decoded by ACF to identify the object, it is passing only the numeric ID. To ensure user
	 * imports continue to function, we need to adjust the ID accordingly when importing users. When we add support for
	 * terms, this will likely need to be repeated.
	 *
	 * @param $id
	 *
	 * @return string
	 */
	private function fix_id_format( $id ) {
		if ( $this->current_object_type() === 'user' ) {
			$id = "user_$id";
		}

		return $id;
	}

	private function current_object_type() {
		if ( class_exists( 'PMXI_Plugin' ) and isset( \PMXI_Plugin::$session->options['custom_type'] ) ) {
			$type = \PMXI_Plugin::$session->options['custom_type'];
			if ( $type === 'import_users' ) {
				return 'user';
			}
		}

		// Assume posts, if not importing a user. When we add support for terms, we'll need to adjust this method
		// accordingly.
		return 'post';
	}

}