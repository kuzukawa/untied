<?php

namespace ACFCustomDatabaseTables\Model\ACFFields;

class SelectACFField extends ACFFieldBase {

	const TYPE = 'select';

	public function set_value( $value ) {
		/**
		 * As per ACF core. @see \acf_field_select::update_value()
		 */
		if ( ! empty( $value ) ) {
			if ( is_array( $value ) ) {
				$value = array_map( 'strval', $value );
			}
		}

		parent::set_value( $value );
	}

	public function get_value_for_single_column() {
		return $this->encode_value( $this->value );
	}

}