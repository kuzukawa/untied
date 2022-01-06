<?php

namespace ACFCustomDatabaseTables\Model\ACFFields;

class NumberACFField extends ACFFieldBase {

	const TYPE = 'number';

	public function set_value( $value ) {
		/**
		 * As per ACF core. @see \acf_field_number::update_value()
		 */
		if ( ! empty( $value ) ) {
			// remove ','
			if ( acf_str_exists( ',', $value ) ) {
				$value = str_replace( ',', '', $value );
			}
		}

		parent::set_value( $value );
	}

}