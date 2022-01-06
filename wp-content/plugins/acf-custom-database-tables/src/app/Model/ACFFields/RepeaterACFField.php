<?php

namespace ACFCustomDatabaseTables\Model\ACFFields;

use ACFCustomDatabaseTables\Facade\Settings;

class RepeaterACFField extends ACFFieldBase {

	const TYPE = 'repeater';

	protected $can_create_sub_tables = true;

	/**
	 * @var array Array of field names mapped to their field keys
	 */
	private $field_key_name_map = [];

	/**
	 * Override method so we can allow our settings system to set the value here.
	 *
	 * @return bool
	 */
	public function is_supported() {
		$this->is_supported = Settings::get( 'enable_repeater_field_support', false );

		return parent::is_supported();
	}

	public function set_field_array( array $field ) {
		parent::set_field_array( $field );
		$this->field_key_name_map = $this->build_field_key_name_map( $this->field_array );
	}

	public function set_value( $value ) {
		$this->set_value_from_db( $value );
	}

	public function set_value_from_db( $encoded_value ) {
		$value = $this->decode_value( $encoded_value );

		if ( is_array( $value ) ) {
			// Nested repeaters will be encoded. We need to ensure we decode these as well.
			foreach ( $value as $row_num => $data ) {
				foreach ( $data as $key => $datum ) {
					$value[ $row_num ][ $key ] = $this->decode_value( $datum );
				}
			}
		}

		$this->value = $value;
	}

	/**
	 * Return a row count when requesting the repeater field. Returning NULL here when there are no rows to count
	 * ensures the core meta data fallbacks are still possible.
	 *
	 * @return int|null
	 */
	public function get_value() {
		return is_countable( $this->value )
			? count( $this->value )
			: null;
	}

	public function get_value_for_single_column() {
		$formatted_data = $this->format_data_for_storage( $this->value, $this->field_key_name_map );

		return $this->encode_value( $formatted_data );
	}

	public function get_value_for_sub_table() {
		return $this->format_data_for_storage( $this->value, $this->field_key_name_map );
	}

	/**
	 * Build an array of meta field names for this object. This will loop through the values array creating an array of
	 * every single key we need to work with for this field. We use these keys to intercept ACFs calls to the core
	 * meta tables and instead return data from the custom DB tables where data can be found. e.g;
	 *
	 * [
	 *      'repeater',
	 *      'repeater_0_field',
	 *      'repeater_0_field2',
	 *      'repeater_0_field2_0_subfield',
	 *      'repeater_0_field2_0_subfield2',
	 *      'repeater_1_field',
	 *      'repeater_1_field2',
	 *      'repeater_1_field2_0_subfield',
	 *      'repeater_1_field2_0_subfield2',
	 * ]
	 *
	 * @return array
	 */
	public function get_meta_field_names() {
		$fn = function ( $base, $names, $raw_value ) use ( &$fn ) {
			if ( is_array( $raw_value ) ) {
				foreach ( $raw_value as $index => $row ) {
					if ( ! is_array( $row ) ) {
						continue;
					}

					foreach ( $row as $key => $value ) {
						$b = $names[] = "{$base}_{$index}_{$key}";
						$names = $fn( $b, $names, $value );
					}
				}
			}

			return $names;
		};

		$value = $this->get_value_raw();
		$value = $this->format_data_for_storage( $value, $this->field_key_name_map );

		return $fn( $this->name(), parent::get_meta_field_names(), $value );
	}

	/**
	 * Return a flat array of all descendent field name patterns. e.g;
	 *
	 * [
	 *      'repeater_(\d+)_repeater_text',
	 *      'repeater_(\d+)_repeater_text_area',
	 *      'repeater_(\d+)_nested_repeater',
	 *      'repeater_(\d+)_nested_repeater_(\d+)_first_sub_field',
	 *      'repeater_(\d+)_nested_repeater_(\d+)_second_sub_field',
	 * ]
	 */
	public function get_meta_field_name_match_patterns() {
		$fn = function ( $patterns, $field, $base = '' ) use ( &$fn ) {

			if ( $base ) {
				$patterns[ $field['key'] ] = $base .= '_(\d+)_' . $field['name'];
			} else {
				$base = $field['name'];
			}

			if ( isset( $field['sub_fields'] ) and is_array( $field['sub_fields'] ) ) {
				foreach ( $field['sub_fields'] as $sub_field ) {
					$patterns = $fn( $patterns, $sub_field, $base );
				}
			}

			return $patterns;
		};

		return $fn( [], $this->field_array );
	}

	/**
	 * Format repeater data in preparation for storage. The value passed through by ACF is for internal use by ACF and
	 * provides field values mapped to field keys instead of names. The value array also contains string based row
	 * numbers in the format 'row-1' which is something we don't need. This method reformats the data so that we end up
	 * with a nice clean array of rows, each containing values mapped to field names.
	 *
	 * If this is called on the already-formatted value array – which happens when we set the value to data that has
	 * been pulled from the database – it won't break as it doesn't replace field key names.
	 *
	 * @param array|mixed $data
	 * @param array $key_name_map
	 *
	 * @return array
	 */
	private function format_data_for_storage( $data, $key_name_map ) {
		if ( ! is_array( $data ) ) {
			return $data;
		}

		$return = [];

		$i = - 1;
		foreach ( $data as $key => $value ) {
			$i ++;

			// if already a field key name, use that. Otherwise, either replace the field key reference with the field
			// name or use a row number.
			if ( in_array( $key, $key_name_map ) ) {
				$index = $key;

			} else {
				$index = isset( $key_name_map[ $key ] )
					? $key_name_map[ $key ]
					: $i;
			}

			$return[ $index ] = $this->format_data_for_storage( $value, $key_name_map );
		}

		return $return;
	}

	/**
	 * @param array $field The ACF field array
	 *
	 * @return array
	 */
	private function build_field_key_name_map( $field ) {
		$map = [];

		$map[ $field['key'] ] = $field['name'];

		if ( isset( $field['sub_fields'] ) and is_array( $field['sub_fields'] ) ) {
			foreach ( $field['sub_fields'] as $field ) {
				$map = array_merge( $map, $this->build_field_key_name_map( $field ) );
			}
		}

		return $map;
	}

}