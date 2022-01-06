<?php

namespace ACFCustomDatabaseTables\Model\ACFFields;

class ImageACFField extends ACFFieldBase {

	const TYPE = 'image';

	public function set_value( $value ) {
		/**
		 * As per ACF core. @see \acf_field_image::update_value()
		 */
		if ( ! empty( $value ) ) {
			$value = acf_idval( $value );
		}

		parent::set_value( $value );
	}

}