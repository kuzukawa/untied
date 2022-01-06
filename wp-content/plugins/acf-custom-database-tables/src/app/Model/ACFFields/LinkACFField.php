<?php

namespace ACFCustomDatabaseTables\Model\ACFFields;

use ACFCustomDatabaseTables\Utils\Arr;

class LinkACFField extends ACFFieldBase {

	const TYPE = 'link';

	public function set_value( $value ) {
		/**
		 * As per ACF core. @see \acf_field_link::update_value()
		 */
		if ( empty( $value ) || empty( $value['url'] ) ) {
			$value = "";
		}

		parent::set_value( $value );
	}

	public function get_value_for_single_column() {
		if ( Arr::has_no_values( $this->value ) ) {
			return '';
		}

		return $this->encode_value( $this->value );
	}

}