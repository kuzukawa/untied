<?php

namespace ACFCustomDatabaseTables\Model\ACFFields;

use ACFCustomDatabaseTables\Utils\Arr;

class GoogleMapACFField extends ACFFieldBase {

	const TYPE = 'google_map';

	/**
	 * Ensuring the value is decoded as it is set on the object.
	 *
	 * This closely mimics ACF's \acf_field_google_map::update_value() object method. We considered using that method
	 * directly but some of ACF's update_value methods produce side-effects that will already be triggered after our
	 * intercept code in \ACFCustomDatabaseTables\Intercept\ACFUpdateFieldIntercept::_update_value(). Doubleing up on
	 * ACF's side-effects could be problematic at the worst or just plain inefficient at best so we are opting to
	 * implement the functionality here instead.
	 *
	 *
	 * @param $value
	 */
	public function set_value( $value ) {
		if ( is_string( $value ) ) {
			$value = (array) json_decode( wp_unslash( $value ), true );
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