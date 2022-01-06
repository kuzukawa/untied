<?php

namespace ACFCustomDatabaseTables\Model\ACFFields;

use ACFCustomDatabaseTables\Utils\Arr;

class UserACFField extends ACFFieldBase {

	const TYPE = 'user';

	protected $can_create_join_tables = true;

	public function set_value( $value ) {
		if ( ! empty( $value ) ) {

			if ( is_array( $value ) and ! Arr::is_associative( $value ) ) {
				/**
				 * As per ACF core user field handler
				 * @see \acf_field_user::update_value()
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
		return $this->encode_value( $this->value );
	}

}