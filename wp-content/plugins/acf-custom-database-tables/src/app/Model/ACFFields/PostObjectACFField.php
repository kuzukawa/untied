<?php

namespace ACFCustomDatabaseTables\Model\ACFFields;

use ACFCustomDatabaseTables\Utils\Arr;

class PostObjectACFField extends ACFFieldBase {

	const TYPE = 'post_object';

	protected $can_create_join_tables = true;

	public function set_value( $value ) {
		if ( ! empty( $value ) ) {

			if ( is_array( $value ) and ! Arr::is_associative( $value ) ) {
				/**
				 * As per ACF core user field handler
				 * @see \acf_field_post_object::update_value()
				 */
				$value = array_map( function ( $v ) {
					return strval( acf_idval( $v ) );
				}, (array) $value );

			} else {
				$value = acf_idval( $value );
			}
		}

		parent::set_value( $value );
	}

	public function get_value_for_single_column() {
		$value = $this->value;

		if ( ! isset( $this->field_array['multiple'] ) or ! $this->field_array['multiple'] ) {
			if ( is_array( $this->value ) ) {
				$value = $this->value[0];
			}
		}

		if ( ! is_null( $value ) and ! is_scalar( $value ) ) {
			$value = $this->encode_value( $value );
		}

		return $value;
	}

}