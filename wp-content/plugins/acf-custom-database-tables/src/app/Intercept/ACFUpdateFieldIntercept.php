<?php

namespace ACFCustomDatabaseTables\Intercept;

use ACFCustomDatabaseTables\Facade\Factory;
use ACFCustomDatabaseTables\Facade\Map;
use ACFCustomDatabaseTables\Model\ACFSelector;
use ACFCustomDatabaseTables\Utils\Arr;
use ACFCustomDatabaseTables\Utils\Error;

/**
 * Class ACFUpdateFieldIntercept
 * @package ACFCustomDatabaseTables\Intercept
 *
 * Hooks into ACFs acf/update_value filter to intercept the meta data.
 */
class ACFUpdateFieldIntercept extends InterceptBase {

	private $field_refs = [];

	public function init() {
		$this->enable();
	}

	/**
	 * Hook anything needed by the intercept in order to store data in custom db tables.
	 */
	public function enable() {
		add_filter( 'acf/pre_update_value', [ $this, '_capture_field_refs' ], 10, 4 );
		add_filter( 'acf/pre_update_metadata', [ $this, '_update_tables' ], 10, 5 );
	}

	/**
	 * Stop intercepting calls and allow all data to be stored in core meta tables only.
	 */
	public function disable() {
		remove_filter( 'acf/pre_update_value', [ $this, '_capture_field_refs' ], 10 );
		remove_filter( 'acf/pre_update_metadata', [ $this, '_update_tables' ], 10 );
	}

	/**
	 * Capture incoming field array and map it field name and field key reference.
	 *
	 * @param null|mixed $null If anything other than null, ACF won't save date to core meta tables.
	 * @param mixed $value
	 * @param string|int $post_id
	 * @param array $field
	 *
	 * @return mixed
	 */
	function _capture_field_refs( $null, $value, $post_id, $field ) {
		$this->field_refs["$post_id:{$field['name']}"] = $field;
		$this->field_refs["$post_id:_{$field['name']}"] = $field;

		return $null;
	}

	/**
	 * Hooked method that intercepts the data being processed by ACF and saves it into a custom table if a custom table
	 * has been defined for the data. If no table found, the data is passed through and left to be handled by ACF as per
	 * usual.
	 *
	 * todo https://app.clickup.com/t/b9abmt
	 *
	 * @param null|mixed $null
	 * @param string|int $post_id
	 * @param string $name
	 * @param mixed $value
	 * @param bool $hidden
	 *
	 * @return bool|mixed
	 */
	function _update_tables( $null, $post_id, $name, $value, $hidden ) {
		$reference = $hidden ? "$post_id:_$name" : "$post_id:$name";

		$field_array = Arr::pull( $this->field_refs, $reference );
		if ( empty( $field_array ) ) {
			return $null;
		}

		// Prepare required objects.
		$selector = ACFSelector::make( $post_id );
		$field = Factory::make_field_object_from_array( $field_array );

		// Bail if field is not supported or does not have a table.
		if ( ! $field->is_supported() or ! Map::has_table( $field, $selector ) ) {
			return $null;
		}

		// If we have a field key reference, determine whether or not to short circuit the storage of the key reference
		// in core meta tables.
		if ( $hidden ) {
			return $this->coordinator->should_bypass_key_references( $field, $selector ) ? true : $null;
		}

		// At this point, we're dealing with a value and need to determine whether or not to short circuit the storage
		// of the value in core meta tables.
		$return = $this->coordinator->should_bypass_values( $field, $selector ) ? true : $null;

		/**
		 * Filter a field value before it is stored in a custom database table.
		 *
		 * @param mixed $value
		 * @param string|int|mixed $post_id
		 * @param array $field_array
		 */
		$value = apply_filters( 'acfcdt/filter_value_before_update', wp_unslash( $value ), $post_id, $field_array );

		$field->set_value( $value );

		// If the field owns a sub table, remove extra data from table for this object ID. The values for each subfield
		// will have already been added to the sub table by this point so we just need to remove any extra rows.
		if ( $this->coordinator->field_owns_sub_table( $field, $selector ) ) {
			if ( is_array( $existing = $this->coordinator->find_field_value( $field, $selector ) ) ) {
				$n_inbound = (int) $field->get_value_raw();
				if ( $n_inbound < count( $existing ) ) {
					$this->coordinator->truncate_sub_table_for_context( $field, $selector, $n_inbound );
				}
			}

			return $return;
		}

		// Repeater field specific handling. At this stage, each individual sub field has been updated atomically as
		// part of the repeater update loop. This part here is all about limiting data rows and involves querying,
		// modifying, and resaving the repeater's data array after all sub fields have already been updated.
		if ( $field->is_a( 'repeater' ) ) {

			// If repeater field is nested within another column and the currently stored value has more rows than the
			// inbound value, remove excess columns.
			if ( $this->coordinator->field_is_nested_within_a_column( $field, $selector ) ) {
				return is_wp_error( $e = $this->coordinator->truncate_nested_encoded_repeater( $field, $selector ) )
					? Error::log( $e->get_error_message() )->return( $null )
					: $return;
			}

			// If field is repeater, is column owner (encoded), find number of existing rows, remove any extraneous
			// compared to field value, the save the payload.
			if ( $this->coordinator->field_owns_column( $field, $selector ) ) {
				return is_wp_error( $e = $this->coordinator->truncate_encoded_repeater( $field, $selector ) )
					? Error::log( $e->get_error_message() )->return( $null )
					: $return;
			}

			// If the value coming in is not an empty string, we want to stop handling here. All fields within the
			// repeater have been atomically updated at this point and ACF is now attempting to save the number of rows
			// as an integer in a separate meta field. If we allow the system to save a value at this point, it will
			// overwrite any encoded data — hence, the return. If the value IS an empty string, the repeater is being
			// cleared so we allow it to flow on through to update and empty the column.
			if ( $field->get_value_raw() !== '' ) {
				return $return;
			}
		}

		// Attempt to update the table. If it fails, log it and return the original value.
		if ( is_wp_error( $updated = $this->coordinator->update_field( $field, $selector ) ) ) {
			return Error::log( $updated->get_error_message() )->return( $null );
		}

		return $return;
	}

	/**
	 * @param $null
	 * @param $value
	 * @param $selector
	 * @param $field_array
	 *
	 * @return null|mixed
	 * @deprecated This was the original handler but we want to adopt our internal naming convention of prefixing hooked
	 * methods with an underscore.
	 *
	 */
	public function update_value( $null, $value, $selector, $field_array ) {
		_deprecated_function( __METHOD__, '1.1', '\ACFCustomDatabaseTables\Intercept\ACFUpdateFieldIntercept::_update_tables(). This method will be removed in version 1.2' );

		return $this->_capture_field_refs( $null, $value, $selector, $field_array );
	}

}