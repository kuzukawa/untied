<?php

namespace ACFCustomDatabaseTables\Model\ACFFields;

class TaxonomyACFField extends ACFFieldBase {

	const TYPE = 'taxonomy';

	protected $can_create_join_tables = true;

	public function set_value( $value ) {
		/**
		 * As per ACF core. @see \acf_field_taxonomy::update_value
		 */
		if ( is_array( $value ) ) {
			$value = array_filter( $value );

			// Not in ACF core – we're adding this for consistency. Without this,
			// disabling the integer_type_cast module would have no effect on this
			// field data.
			$value = array_map( 'strval', $value );
		}

		parent::set_value( $value );
	}

	public function get_value_for_single_column() {
		return $this->encode_value( $this->value );
	}

}