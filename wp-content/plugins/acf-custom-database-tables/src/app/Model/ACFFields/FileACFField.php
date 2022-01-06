<?php

namespace ACFCustomDatabaseTables\Model\ACFFields;

class FileACFField extends ACFFieldBase {

	const TYPE = 'file';

	public function set_value( $value ) {
		/**
		 * As per ACF core. @see \acf_field_file::update_value()
		 */
		if ( ! empty( $value ) ) {
			$value = acf_idval( $value );
		}

		parent::set_value( $value );
	}

}