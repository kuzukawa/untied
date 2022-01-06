<?php

namespace ACFCustomDatabaseTables\Intercept;

use ACFCustomDatabaseTables\Facade\Factory;
use ACFCustomDatabaseTables\Facade\Map;
use ACFCustomDatabaseTables\Model\ACFFields\ACFFieldBase;
use ACFCustomDatabaseTables\Model\ACFSelector;
use ACFCustomDatabaseTables\Service\ACFLocalReferenceFallback;
use ACFCustomDatabaseTables\Utils\Error;
use WP_Error;

/**
 * Class ACFGetFieldIntercept
 * @package ACFCustomDatabaseTables\Intercept
 */
class ACFGetFieldIntercept extends InterceptBase {

	private $field_refs = [];

	public function init() {
		$this->enable();
	}

	/**
	 * Hook anything needed by the intercept in order to return data from custom db tables.
	 */
	public function enable() {
		add_filter( 'acf/pre_load_value', [ $this, '_capture_field_refs' ], 10, 3 );
		add_filter( 'acf/pre_load_metadata', [ $this, '_load_data' ], 10, 4 );
	}

	/**
	 * Stop intercepting calls and allow all data to be retrieved from core meta tables only.
	 */
	public function disable() {
		remove_filter( 'acf/pre_load_value', [ $this, '_capture_field_refs' ], 10 );
		remove_filter( 'acf/pre_load_metadata', [ $this, '_load_data' ], 10 );
	}

	/**
	 * Capture incoming field array and map it field name and field key reference.
	 *
	 * @param null|mixed $null
	 * @param string|int $post_id
	 * @param array $field
	 *
	 * @return mixed
	 */
	function _capture_field_refs( $null, $post_id, $field ) {
		$this->field_refs["$post_id:{$field['name']}"] = $field;

		return $null;
	}

	/**
	 * @param null|mixed $null
	 * @param string|int $post_id
	 * @param string $name
	 * @param bool $hidden
	 *
	 * @return mixed|null
	 */
	public function _load_data( $null, $post_id, $name, $hidden ) {
		$reference = "$post_id:$name";

		if ( $hidden or empty( $this->field_refs[ $reference ] ) ) {
			return $null;
		}

		// Prepare required objects.
		$selector = ACFSelector::make( $post_id );
		$field = Factory::make_field_object_from_array( $this->field_refs[ $reference ] );

		// Unset cached values.
		unset( $this->field_refs[ $reference ] );

		// Bail if field is not supported or does not have a table.
		if ( ! $field->is_supported() or ! Map::has_table( $field, $selector ) ) {
			return $null;
		}

		$field = $this->coordinator->load_field_value( $field, $selector );

		if ( is_wp_error( $field ) ) {
			return Error::log( 'Failed to retrieve data from table. Error: ' . $field->get_error_message() )->return( $null );
		}

		return $field->get_value();
	}

	/**
	 * @param $field_key
	 * @param $field_name
	 * @param $post_id
	 *
	 * @return mixed
	 * @deprecated This was the original handler but we want to adopt our internal naming convention of prefixing hooke
	 * methods with an underscore.
	 *
	 */
	public function maybe_get_local_field_reference( $field_key, $field_name, $post_id ) {
		_deprecated_function( __METHOD__, '1.1',
			'\ACFCustomDatabaseTables\Service\ACFLocalReferenceFallback::_fall_back_to_local_references(). This method will be removed in version 1.2' );

		$compat = new ACFLocalReferenceFallback();

		return $compat->_fall_back_to_local_references( $field_key, $field_name, $post_id );
	}

	/**
	 * @param $null
	 * @param $selector
	 * @param $field
	 *
	 * @return array|mixed|object|WP_Error|null
	 * @deprecated This was the original handler but we want to adopt our internal naming convention of prefixing hooke
	 * methods with an underscore.
	 *
	 */
	public function fetch_value( $null, $selector, $field ) {
		_deprecated_function( __METHOD__, '1.1',
			'No replacement. This method will be removed in version 1.2' );
	}

}