<?php

namespace ACFCustomDatabaseTables\Model\ACFFields;

class GalleryACFField extends ACFFieldBase {

	const TYPE = 'gallery';

	public function set_value( $value ) {
		if ( ! empty( $value ) ) {
			/**
			 * As per ACF core gallery field handler, just with one pass.
			 * @see \acf_field_gallery::update_value()
			 */
			$value = array_map( function ( $v ) {
				return strval( acf_idval( $v ) );
			}, (array) $value );
		}

		parent::set_value( $value );
	}

	public function get_value_for_single_column() {
		return $this->encode_value( $this->value );
	}

}