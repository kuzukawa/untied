<?php

namespace ACFCustomDatabaseTables\Service;

use ACFCustomDatabaseTables\Facade\Factory;
use ACFCustomDatabaseTables\Model\ACFFields\ACFFieldBase;

class ACFFieldSupportManager {

	/**
	 * Fields that are supported out of the box
	 *
	 * @var array
	 */
	private $supported_fields = [];

	/**
	 * Fields that we explicitly do not support...yet.
	 *
	 * @var array
	 */
	private $unsupported_fields = [];

	/**
	 * ACF field types that can result in a join table
	 *
	 * @var array
	 */
	private $join_table_fields = [];

	/**
	 * ACF field types that can result in a sub table
	 *
	 * @var array
	 */
	private $sub_table_fields = [];

	/**
	 * @param ACFFieldBase $field
	 */
	public function register_field( ACFFieldBase $field ) {

		if ( $field->is_supported() ) {
			$this->supported_fields[] = $field->type();
		} else {
			$this->unsupported_fields[] = $field->type();
		}

		if ( $field->can_create_join_tables() ) {
			$this->join_table_fields[] = $field->type();
		}

		if ( $field->can_create_sub_tables() ) {
			$this->sub_table_fields[] = $field->type();
		}
	}

	/**
	 * @param $field
	 *
	 * @return bool
	 */
	public function field_eligible_for_sub_table( $field ) {
		return in_array( $field['type'], $this->sub_table_fields );
	}

	/**
	 * @param $field
	 *
	 * @return bool
	 */
	public function field_eligible_for_join_table( $field ) {
		return in_array( $field['type'], $this->join_table_fields );
	}

	/**
	 * Checks whether we currently support the ACF field being passed in.
	 *
	 * @param array $field The ACF field object array
	 *
	 * @return bool
	 */
	public function is_supported( $field ) {
		if ( in_array( $field['type'], $this->supported_fields ) ) {
			return true;
		}

		return Factory::make_field_object_from_array( $field )->is_supported();
	}

	/**
	 * @return array
	 * @deprecated
	 */
	public function unprocessed_fields() {
		_deprecated_function( __METHOD__, '1.1',
			'... Functionality no longer required as we are now flagging support on field type objects. This method will be removed in version 1.2' );

		return [];
	}

	/**
	 * @return array
	 * @deprecated
	 */
	public function processed_fields() {
		_deprecated_function( __METHOD__, '1.1',
			'... Functionality no longer required as we are now flagging support on field type objects. This method will be removed in version 1.2' );

		return [];
	}

	/**
	 * Lists field types of all supported ACF fields
	 *
	 * @return array
	 * @deprecated
	 */
	public function supported_fields() {
		_deprecated_function( __METHOD__, '1.1',
			'No replacement. This method will be removed in version 1.2' );

		return $this->supported_fields;
	}

	/**
	 * Dynamic field filter methods. i.e; if a method exists, run it.
	 *
	 * @param $field
	 * @param $value
	 *
	 * @return mixed
	 * @deprecated
	 */
	public function maybe_process_field_value( $value, $field ) {

		_deprecated_function( __METHOD__, '1.1',
			'... Functionality no longer required as we are now flagging support on field type objects. This method will be removed in version 1.2' );

		$method = [ $this, "process_{$field['type']}_field_value" ];

		if ( method_exists( $method[0], $method[1] ) and is_callable( $method ) ) {
			$value = call_user_func( $method, $value, $field );
		}

		return $value;

	}

	/**
	 * Dynamic field filter methods. i.e; if a method exists, run it.
	 *
	 * @param $field
	 * @param $value
	 *
	 * @return mixed
	 * @deprecated
	 */
	public function maybe_unprocess_field_value( $value, $field ) {

		_deprecated_function( __METHOD__, '1.1',
			'... Functionality no longer required as we are now flagging support on field type objects. This method will be removed in version 1.2' );

		$method = [ $this, "unprocess_{$field['type']}_field_value" ];

		if ( method_exists( $method[0], $method[1] ) and is_callable( $method ) ) {
			$value = call_user_func( $method, $field, $value );
		}

		return $value;

	}

	/**
	 * Processing method for inbound 'relationship' field data
	 *
	 * @param $value
	 * @param $field
	 *
	 * @return mixed
	 * @deprecated
	 */
	private function process_relationship_field_value( $value, $field ) {

		_deprecated_function( __METHOD__, '1.1',
			'... Functionality no longer required as we are now flagging support on field type objects. This method will be removed in version 1.2' );

		if ( isset( $field['max'] ) and $field['max'] == 1 ) {
			if ( is_array( $value ) ) {
				return $value[0];
			}
		}

		return $value;
	}

	/**
	 * Processing method for inbound 'post object' field data
	 *
	 * @param $value
	 * @param $field
	 *
	 * @return mixed
	 * @deprecated
	 */
	private function process_post_object_field_value( $value, $field ) {

		_deprecated_function( __METHOD__, '1.1',
			'... Functionality no longer required as we are now flagging support on field type objects. This method will be removed in version 1.2' );

		if ( ! isset( $field['multiple'] ) or ! $field['multiple'] ) {
			if ( is_array( $value ) ) {
				return $value[0];
			}
		}

		return $value;
	}

}