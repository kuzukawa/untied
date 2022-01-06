<?php

namespace ACFCustomDatabaseTables\Model\ACFFields;

class PageLinkACFField extends ACFFieldBase {

	const TYPE = 'page_link';

	protected $can_create_join_tables = true;

	public function set_value( $value ) {

		/**
		 * As per ACF core. @see \acf_field_page_link::update_value()
		 */
		if ( ! empty( $value ) ) {

			if ( acf_is_sequential_array( $value ) ) {

				$value = array_map( function ( $v ) {
					return strval( acf_maybe_idval( $v ) );
				}, $value );

			} else {
				$value = acf_maybe_idval( $value );
			}
		}

		parent::set_value( $value );
	}

}