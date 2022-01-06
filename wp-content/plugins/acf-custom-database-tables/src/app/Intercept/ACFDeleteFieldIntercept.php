<?php

namespace ACFCustomDatabaseTables\Intercept;

use ACFCustomDatabaseTables\Facade\Factory;
use ACFCustomDatabaseTables\Facade\Map;
use ACFCustomDatabaseTables\Model\ACFFields\ACFFieldBase;
use ACFCustomDatabaseTables\Model\ACFSelector;

class ACFDeleteFieldIntercept extends InterceptBase {

	private $field_refs = [];

	/**
	 * Hooks anything needed by the intercept in order to intercept data for return to InterceptCoordinator
	 */
	public function init() {
		$this->enable();
	}

	public function enable() {
		add_action( 'acf/delete_value', [ $this, '_capture_field_refs' ], 10, 3 );
		add_filter( 'acf/pre_delete_metadata', [ $this, '_delete_data' ], 10, 4 );
	}

	public function disable() {
		remove_action( 'acf/delete_value', [ $this, '_capture_field_refs' ], 10 );
		remove_filter( 'acf/pre_delete_metadata', [ $this, '_delete_data' ], 10 );
	}

	function _capture_field_refs( $post_id, $field_name, $field ) {
		$this->field_refs["$post_id:$field_name"] = $field;
		$this->field_refs["$post_id:_$field_name"] = $field;
	}

	public function _delete_data( $null, $post_id, $name, $hidden ) {
		$reference = $hidden ? "$post_id:_$name" : "$post_id:$name";

		if ( empty( $this->field_refs[ $reference ] ) ) {
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

		// If we have a field key reference, return early with either TRUE (if bypassing core refs) or $null.
		if ( $hidden ) {
			return $this->coordinator->should_bypass_key_references( $field, $selector ) ? true : $null;
		}

		$return = $this->coordinator->should_bypass_values( $field, $selector ) ? true : $null;

		// todo - consider some form of error handling. If there is an error, it should be logged and $null returned.
		$this->coordinator->delete_field( $field, $selector );

		return $return;
	}

}