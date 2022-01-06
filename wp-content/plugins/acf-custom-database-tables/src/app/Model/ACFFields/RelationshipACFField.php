<?php

namespace ACFCustomDatabaseTables\Model\ACFFields;

use ACFCustomDatabaseTables\Utils\Arr;

class RelationshipACFField extends ACFFieldBase {

	const TYPE = 'relationship';

	protected $can_create_join_tables = true;

	public function set_value( $value ) {
		if ( ! empty( $value ) ) {

			if ( is_array( $value ) and ! Arr::is_associative( $value ) ) {
				/**
				 * As per ACF core relationship field handler
				 * @see \acf_field_relationship::update_value()
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

		if ( isset( $this->field_array['max'] ) and $this->field_array['max'] == 1 ) {
			if ( is_array( $this->value ) ) {
				$value = $this->value[0];
			}
		}

		return $this->encode_value( $value );
	}

}