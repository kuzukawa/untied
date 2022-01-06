<?php

namespace ACFCustomDatabaseTables\Coordinator;

use ACFCustomDatabaseTables\Cache\DataCache;
use ACFCustomDatabaseTables\Facade\Factory;
use ACFCustomDatabaseTables\Facade\Map;
use ACFCustomDatabaseTables\Intercept\InterceptBase;
use ACFCustomDatabaseTables\Model\ACFFields\ACFFieldBase;
use ACFCustomDatabaseTables\Model\ACFSelector;
use ACFCustomDatabaseTables\Settings;
use ACFCustomDatabaseTables\Utils\Arr;
use ACFCustomDatabaseTables\Utils\Error;
use ACFCustomDatabaseTables\Utils\FieldValueEncoder;
use WP_Error;

class InterceptCoordinator {

	/** @var  Settings */
	private $settings;

	/** @var  TableCoordinator */
	private $tables;

	/** @var InterceptBase[] */
	private $intercepts = [];

	/** @var DataCache */
	private $cache;

	/**
	 * InterceptCoordinator constructor.
	 *
	 * @param Settings $settings
	 * @param TableCoordinator $tables
	 * @param DataCache $cache
	 */
	public function __construct( Settings $settings, TableCoordinator $tables, DataCache $cache ) {
		$this->tables = $tables;
		$this->settings = $settings;
		$this->cache = $cache;
	}

	/**
	 * Registers an intercept against this object.
	 *
	 * @param InterceptBase $intercept
	 */
	public function register_intercept( InterceptBase $intercept ) {
		if ( ! ( $intercept instanceof InterceptBase ) ) {
			$type = gettype( $intercept );
			Error::log( "$type does not extend InterceptBase. Intercept was not registered." );
		}
		$intercept->set_intercept_coordinator( $this );
		$this->intercepts[] = $intercept;
	}

	/**
	 * Initialises all intercepts
	 */
	public function init() {
		foreach ( $this->intercepts as $intercept ) {
			$intercept->init();
		}
	}

	/**
	 * @return Settings
	 */
	public function settings() {
		return $this->settings;
	}

	/**
	 * Check if a field has a table
	 *
	 * @param ACFFieldBase $field
	 * @param ACFSelector $selector
	 *
	 * @return bool
	 */
	public function has_table( ACFFieldBase $field, ACFSelector $selector ) {
		return Map::has_table( $field, $selector );
	}

	/**
	 * Check if a field is associated with a join table.
	 *
	 * @param ACFFieldBase $field
	 * @param ACFSelector $selector
	 *
	 * @return bool
	 */
	private function is_join_table( ACFFieldBase $field, ACFSelector $selector ) {
		if ( ! ( $table_name = Map::get_table_name( $field, $selector ) ) ) {
			return false;
		}

		return Map::is_join_table( $table_name );
	}

	/**
	 * Check if a field is associated with a sub table. This matches both the field that 'owns' the sub table as well as
	 * any fields within that sub table.
	 *
	 * @param ACFFieldBase $field
	 * @param ACFSelector $selector
	 *
	 * @return bool
	 */
	private function is_sub_table( ACFFieldBase $field, ACFSelector $selector ) {
		if ( ! ( $table_name = Map::get_table_name( $field, $selector ) ) ) {
			return false;
		}

		return Map::is_sub_table( $table_name );
	}

	/**
	 * Check if a field 'owns' a sub table. This applies to fields such as repeaters many sub fields make up the field
	 * itself and the field is represented by a table of its own.
	 *
	 * @param ACFFieldBase $field
	 * @param ACFSelector $selector
	 *
	 * @return bool
	 */
	public function field_owns_sub_table( ACFFieldBase $field, ACFSelector $selector ) {
		return Map::field_is_sub_table_owner( $field, $selector );
	}

	public function field_owns_column( ACFFieldBase $field, ACFSelector $selector ) {
		return Map::field_is_column_owner( $field, $selector );
	}

	public function field_is_nested_within_a_column( ACFFieldBase $field, ACFSelector $selector ) {
		return Map::field_is_nested_within_a_column( $field, $selector );
	}

	/**
	 * Determine whether to prevent a field's values making their way into core meta tables. If a field is complex (has
	 * sub fields) all sub field keys will be bypassed.
	 *
	 * @param ACFFieldBase $field
	 * @param ACFSelector $selector
	 *
	 * @return bool
	 */
	public function should_bypass_values( ACFFieldBase $field, ACFSelector $selector ) {
		$field_array = $field->to_array();
		$object_id = $selector->id;
		$object_type = $selector->type;

		/**
		 * Allow fine grain control over which fields are stored in core meta tables alongside custom database tables.
		 * If left as NULL, global setting is applied.
		 *
		 * @var array $field_array The ACF field array.
		 * @var int|string $object_id The object ID.
		 * @var string $object_type
		 */
		$store_in_core = apply_filters( 'acfcdt/store_acf_field_values_in_core_meta', null, $field_array, $object_id, $object_type );
		if ( null !== $store_in_core ) {
			return (bool) ! $store_in_core;
		}

		return (bool) ! $this->settings->get( 'store_acf_values_in_core_meta' );
	}

	/**
	 * Determine whether to prevent a field's key reference meta entries from making their way into core meta tables. If
	 * a field is complex (has sub fields) all sub field key references will be bypassed.
	 *
	 * @param ACFFieldBase $field
	 * @param ACFSelector $selector
	 *
	 * @return bool
	 */
	public function should_bypass_key_references( ACFFieldBase $field, ACFSelector $selector ) {
		$field_array = $field->to_array();
		$object_id = $selector->id;
		$object_type = $selector->type;

		/**
		 * Allow fine grain control over which field key references are stored in core meta tables. If left as NULL,
		 * global setting is applied.
		 *
		 * @var array $field_array The ACF field array.
		 * @var int|string $object_id The object ID.
		 * @var string $object_type
		 */
		$store_in_core = apply_filters( 'acfcdt/store_acf_field_key_references_in_core_meta', null, $field_array, $object_id, $object_type );
		if ( null !== $store_in_core ) {
			return (bool) ! $store_in_core;
		}

		return (bool) ! $this->settings->get( 'store_acf_keys_in_core_meta' );
	}

	/**
	 * Clears all records matching an ID from a table for the given field name/context
	 *
	 * @param ACFFieldBase $field
	 * @param ACFSelector $selector
	 */
	private function clear_sub_table_for_context( ACFFieldBase $field, ACFSelector $selector ) {
		$this->clear_secondary_table_for_context( $field, $selector );
	}

	/**
	 * Limit the number of rows in a sub table for a given context.
	 *
	 * @param ACFFieldBase $field
	 * @param ACFSelector $selector
	 * @param int $num_rows
	 *
	 * @return void|null
	 */
	public function truncate_sub_table_for_context( ACFFieldBase $field, ACFSelector $selector, $num_rows ) {
		$table_name = Map::get_table_name( $field, $selector );
		$table = $this->tables->get_table_object( $table_name );

		if ( is_wp_error( $table ) ) {
			return Error::log( "Could not locate the `$table_name` table object." )->void();
		}

		// todo - this will make more sense as a model method. e.g; limit_rows_where().
		global $wpdb;
		$SQL = "DELETE FROM `{$table->full_table_name()}` WHERE `{$table->object_relationship_key()}` = {$selector->id} AND `_sort_order` >= %d";
		$wpdb->query( $wpdb->prepare( $SQL, $num_rows ) );

		$this->cache->delete_record( $table_name, $selector->context(), $selector->id );
	}

	public function truncate_encoded_repeater( ACFFieldBase $field, ACFSelector $selector ) {
		$existing = FieldValueEncoder::decode( $this->find_field_value( $field, $selector ), $field->to_array() );
		if ( is_array( $existing ) ) {
			$n_inbound = (int) $field->get_value_raw();
			if ( $n_inbound < count( $existing ) ) {
				$remaining = array_slice( $existing, 0, $n_inbound );
				$field->set_value( $remaining ?: '' );
				if ( is_wp_error( $e = $this->update_field( $field, $selector ) ) ) {
					return $e;
				}
			}

		}

		return true;
	}

	public function truncate_nested_encoded_repeater( ACFFieldBase $field, ACFSelector $selector ) {
		$table_name = Map::get_table_name( $field, $selector );
		$column_name = Map::get_column_name( $field, $selector );
		$pattern = Map::get_field_name_pattern( $field, $selector );
		$row_numbers = $this->extract_row_numbers( $field->name(), $pattern );
		// Always going to be an int as we are working with repeaters here.
		$value_inbound = (int) $field->get_value_for_single_column();
		$root_field = Factory::make_field_object_from_field_key( Map::get_root_field_key( $field, $selector ) );
		$stored = $this->lookup_field_value( $table_name, $column_name, $selector->context(), $selector->id );
		$root_field->set_value_from_db( $stored );

		/**
		 * 1. Get field names without row numbers.
		 * 2. Remove the top field name as this represents the whole row.
		 */
		$names = explode( '_(\d+)_', $pattern ); // 1.
		array_shift( $names ); // 2.

		$stored_decoded = (array) $root_field->get_value_raw();
		$target = Arr::zip( $row_numbers, $names );
		$nested_array = (array) Arr::get_deep( $stored_decoded, $target, [] );

		if ( $value_inbound < count( $nested_array ) ) {
			Arr::set_deep( $stored_decoded, $target, array_slice( $nested_array, 0, $value_inbound ) );
			$root_field->set_value( $stored_decoded );
			if ( is_wp_error( $e = $this->update_field( $root_field, $selector ) ) ) {
				return $e;
			}
		}

		return true;
	}

	/**
	 * Clears all records matching an ID from a table for the given field name/context
	 *
	 * @param ACFFieldBase $field
	 * @param ACFSelector $selector
	 */
	private function clear_join_table_for_context( ACFFieldBase $field, ACFSelector $selector ) {
		$this->clear_secondary_table_for_context( $field, $selector );
	}

	private function clear_secondary_table_for_context( ACFFieldBase $field, ACFSelector $selector ) {
		$table_name = Map::get_table_name( $field, $selector );
		$table = $this->tables->get_table_object( $table_name );

		if ( is_wp_error( $table ) ) {
			return Error::log( "Could not locate the `$table_name` table object." )->void();
		}

		$table->delete_where( [ $table->object_relationship_key() => $selector->id ] );

		$this->cache->delete_record( $table_name, $selector->context(), $selector->id );
	}

	/**
	 * Update database with field value for the given selector context.
	 *
	 * @param ACFFieldBase $field
	 * @param ACFSelector $selector
	 *
	 * @return ACFFieldBase|WP_Error
	 */
	public function update_field( ACFFieldBase $field, ACFSelector $selector ) {
		$updated = false;
		$table_name = Map::get_table_name( $field, $selector );

		if ( is_wp_error( $table = $this->tables->get_table_object( $table_name ) ) ) {
			return Error::log( "Could not locate the `$table_name` table object." )->return( $field );
		}

		$column_name = Map::get_column_name( $field, $selector );

		if ( $table->is_sub_table() ) {
			$pattern = Map::get_field_name_pattern( $field, $selector );
			$row_numbers = $this->extract_row_numbers( $field->name(), $pattern );
			$row_number = $row_numbers[0];

			if ( Map::field_is_column_owner( $field, $selector ) ) {

				if ( $this->value_is_empty( $field ) ) {
					$value = $this->get_empty_value_for_field( $field, $selector );
				} else {
					$value = $field->get_value_for_single_column();
				}
				$updated = $table->update_value(
					$column_name,
					$value,
					$selector->id,
					[ '_sort_order' => $row_number ]
				);

			} elseif ( Map::field_is_nested_within_a_column( $field, $selector ) ) {
				$value_inbound = $field->get_value_for_single_column();
				$root_field = Factory::make_field_object_from_field_key( Map::get_root_field_key( $field, $selector ) );
				$pkey = $table->object_relationship_key();
				$rows = (array) $table->find_where( [ $pkey => $selector->id, '_sort_order' => $row_number ] );
				$stored = Arr::get_deep( $rows, "0.$column_name", [] );
				$root_field->set_value_from_db( $stored );

				/**
				 * 1. Get field names without row numbers.
				 * 2. Remove the top field name as this represents the whole row.
				 * 3. Remove the next top field name as this represents the column and we're working within it.
				 * 4. Remove the row number as were working within a column.
				 */
				$names = explode( '_(\d+)_', $pattern ); // 1.
				array_shift( $names ); // 2.
				array_shift( $names ); // 3.
				array_shift( $row_numbers ); // 4.

				$stored_decoded = $root_field->get_value_raw();
				Arr::set_deep( $stored_decoded, Arr::zip( $row_numbers, $names ), $value_inbound );
				$root_field->set_value( $stored_decoded );
				$value_encoded = $root_field->get_value_for_single_column();

				$updated = $table->update_value(
					$column_name,
					$value_encoded,
					$selector->id,
					[ '_sort_order' => $row_number ]
				);
			}

		} elseif ( $table->is_join_table() ) {
			$updated = $table->update_value( $column_name, $field->get_value_for_join_table(), $selector->id );

		} elseif ( Map::field_is_nested_within_a_column( $field, $selector ) ) {
			$pattern = Map::get_field_name_pattern( $field, $selector );
			$row_numbers = $this->extract_row_numbers( $field->name(), $pattern );
			$value_inbound = $field->get_value_for_single_column();
			$root_field = Factory::make_field_object_from_field_key( Map::get_root_field_key( $field, $selector ) );
			$stored = $this->lookup_field_value( $table->table_name(), $column_name, $selector->context(), $selector->id );
			$root_field->set_value_from_db( $stored );

			/**
			 * 1. Get field names without row numbers.
			 * 2. Remove the top field name as this represents the whole row.
			 */
			$names = explode( '_(\d+)_', $pattern ); // 1.
			array_shift( $names ); // 2.

			$stored_decoded = (array) $root_field->get_value_raw();
			Arr::set_deep( $stored_decoded, Arr::zip( $row_numbers, $names ), $value_inbound );
			$root_field->set_value( $stored_decoded );
			$value_encoded = $root_field->get_value_for_single_column();

			$updated = $table->update_value( $column_name, $value_encoded, $selector->id );

		} else {
			if ( $this->value_is_empty( $field ) ) {
				$value = $this->get_empty_value_for_field( $field, $selector );
			} else {
				$value = $field->get_value_for_single_column();
			}
			$updated = $table->update_value( $column_name, $value, $selector->id );
		}

		if ( $updated === false ) {
			global $wpdb;

			return new WP_Error( 'acfcdt', "Error saving field `$column_name` to custom table `$table_name`. Error message: " . $wpdb->last_error );
		}

		$this->cache->delete_record( $table_name, $selector->context(), $selector->id );

		return $field;
	}

	/**
	 * Delete a value/values for a given field & context.
	 *
	 * @param ACFFieldBase $field
	 * @param ACFSelector $selector
	 */
	public function delete_field( ACFFieldBase $field, ACFSelector $selector ) {
		if ( $this->field_owns_sub_table( $field, $selector ) ) {
			$this->clear_sub_table_for_context( $field, $selector );

		} else if ( $this->is_join_table( $field, $selector ) ) {
			$this->clear_join_table_for_context( $field, $selector );

		} else {
			$field->set_value( $this->get_empty_value_for_field( $field, $selector ) );
			$this->update_field( $field, $selector );
		}
	}

	public function load_field_value( ACFFieldBase $field, ACFSelector $selector ) {
		// Set null default value on object.
		$field->set_value( null );
		$table_name = Map::get_table_name( $field, $selector );

		if ( is_wp_error( $table = $this->tables->get_table_object( $table_name ) ) ) {
			return Error::log( "Could not locate the `$table_name` table object." )->return( $field );
		}

		$column_name = Map::get_column_name( $field, $selector );

		// Sub tables have special handling. If the table located for the current field/context combination is a sub
		// table, handle it.
		if ( $table->is_sub_table() ) {
			// Returning null here when no rows are found ensures the app has an opportunity to fall back to core meta
			// tables. This allows ACF to fill in the blanks where a repeater field already has data stored in core meta
			// tables but has had a custom table introduced and no data migration process has yet been executed.
			if ( empty( $rows = $this->find_sub_table_rows( $table_name, $selector->context(), $selector->id ) ) ) {
				return $field;
			}

			// If the field itself has been broken out into a sub table, it is considered the table owner and all
			// existing data belongs to it. Set all found rows as the field value.
			if ( Map::field_is_sub_table_owner( $field, $selector ) ) {
				$field->set_value_from_db( $rows );

				return $field;
			}

			// If we are here, it means the field must be within the sub table, either as a column or nested within
			// a column. Fetch the field name pattern and use it to determine the table row number we need to query.
			// Also maintain a variable with all rows numbers as we'll need that if we are dealing with a deeply nested
			// field.
			$pattern = Map::get_field_name_pattern( $field, $selector );
			$row_numbers = $this->extract_row_numbers( $field->name(), $pattern );
			$row_number = $row_numbers[0];

			// If the field is a child field of the sub tabled field, it will have a column within the sub table. Find
			// the value that intersects the row number and the column name and set that on the field object.
			if ( Map::field_is_column_owner( $field, $selector ) ) {
				$field->set_value_from_db( Arr::get_deep( $rows, [ $row_number, $column_name ] ) );

				return $field;
			}

			// If we are here, it means the field must be a deeply nested field within a column of the sub table. To
			// fetch the value, we need to determine which field owns the column the desired field is nested within and
			// locate the data for the desired field relative to the column.
			if ( Map::field_is_nested_within_a_column( $field, $selector ) ) {
				$root_field = Factory::make_field_object_from_field_key( Map::get_root_field_key( $field, $selector ) );
				$root_field->set_value_from_db( Arr::get_deep( $rows, [ $row_number, $column_name ] ) );

				/**
				 * 1. Get field names without row numbers.
				 * 2. Remove the top field name as this represents the whole row.
				 * 3. Remove the next top field name as this represents the column and we're working within it.
				 * 4. Remove the row number as were working within a column.
				 */
				$names = explode( '_(\d+)_', $pattern ); // 1.
				array_shift( $names ); // 2.
				array_shift( $names ); // 3.
				array_shift( $row_numbers ); // 4.

				$value = Arr::get_deep( (array) $root_field->get_value_raw(), Arr::zip( $row_numbers, $names ) );
				$field->set_value( $value );

				return $field;
			}

			// This should never really be hit but we'll return the object here as we don't need to execute any more
			// logic within this method if we reach this point. The field object, at this point, contains a NULL value.
			return $field;
		}

		// If we enter this conditional, we are dealing with a nested field within a column of a top-level meta table.
		// We need to determine the field name pattern and use that to find the value that is being requested.
		if ( Map::field_is_nested_within_a_column( $field, $selector ) ) {
			$stored = $this->lookup_field_value( $table->table_name(), $column_name, $selector->context(), $selector->id );
			$pattern = Map::get_field_name_pattern( $field, $selector );
			$row_numbers = $this->extract_row_numbers( $field->name(), $pattern );
			$root_field = Factory::make_field_object_from_field_key( Map::get_root_field_key( $field, $selector ) );
			$root_field->set_value_from_db( $stored );

			/**
			 * 1. Get field names without row numbers.
			 * 2. Remove the top field name as this represents the whole row.
			 */
			$names = explode( '_(\d+)_', $pattern ); // 1.
			array_shift( $names ); // 2.

			$stored_decoded = $root_field->get_value_raw();
			$value = Arr::get_deep( $stored_decoded, Arr::zip( $row_numbers, $names ) );
			$field->set_value_from_db( $value );

			return $field;
		}

		// IF we get to this point, we are dealing with a field that is not within a sub table and not deeply nested
		// within a top-level meta table.
		$value = $this->lookup_field_value( $table_name, $column_name, $selector->context(), $selector->id );

		if ( is_wp_error( $value ) ) {
			return Error::log( $value )->return( $field );
		}

		$field->set_value_from_db( $value );

		return $field;
	}

	/**
	 * @param ACFFieldBase $field
	 * @param ACFSelector $selector
	 *
	 * @return null|WP_Error|mixed
	 */
	public function find_field_value( ACFFieldBase $field, ACFSelector $selector ) {
		if ( ! $table_name = Map::get_table_name( $field, $selector ) ) {
			return null;
		}

		if ( $this->is_sub_table( $field, $selector ) ) {
			$rows = $this->find_sub_table_rows( $table_name, $selector->context(), $selector->id );
			// Returning null here when no rows are found ensures the app has an opportunity
			// to fall back to core meta tables. This allows ACF to fill in the blanks where
			// a repeater field already has data stored in core meta tables but has had a
			// custom table introduced and no data migration process has yet been executed.
			if ( empty( $rows ) ) {
				return null;
			}

			// If field is represented by a sub table, return all rows here.
			if ( Map::field_is_sub_table_owner( $field, $selector ) ) {
				return $rows;
			}

			// If field is not the owner of the sub table, it must be within the sub table. Find the required value and
			// return it.
			$pattern = Map::get_field_name_pattern( $field, $selector );
			$names = explode( '_(\d+)_', $pattern );
			// Remove the top field name as this represents the whole row set and we only need to search within the set.
			array_shift( $names );
			// Honour column name aliasing.
			$names[0] = Map::get_column_name( $field, $selector );

			$row_numbers = $this->extract_row_numbers( $field->name(), $pattern );

			return Arr::get_deep( $rows, join( '.', Arr::zip( $row_numbers, $names ) ), null );
		}

		$column_name = Map::get_column_name( $field, $selector );

		return $this->lookup_field_value( $table_name, $column_name, $selector->context(), $selector->id );
	}

	/**
	 * @param $post_id
	 * @param $post_type
	 */
	public function delete_all_data_for_post( $post_id, $post_type ) {
		/**
		 * Control deletion of all custom database table data for a given post and/or post type.
		 *
		 * @var bool $bool Whether or not to delete the data
		 * @var int $post_id The post ID
		 * @var string $post_type The post type
		 */
		$bool = apply_filters( 'acfcdt/delete_all_custom_table_data', true, $post_id, $post_type );
		if ( $bool === false ) {
			return;
		}

		$table_names = Map::locate_all_tables_by_post_type( $post_type );

		foreach ( $table_names as $table_name ) {
			/**
			 * Control deletion of a specific custom database table data for a given post and/or post type.
			 *
			 * @var bool $bool Whether or not to delete the data
			 * @var int $post_id The post ID
			 * @var string $post_type The post type
			 * @var string $table_name The name of the DB table, excluding the $wpdb->prefix
			 */
			$bool = apply_filters( 'acfcdt/delete_custom_table_data', true, $post_id, $post_type, $table_name );
			if ( $bool === false ) {
				continue;
			}

			$table = $this->tables->get_table_object( $table_name );
			$table->delete_where( [ $table->object_relationship_key() => $post_id ] );
			$this->cache->delete_record( $table_name, "post:{$post_type}", $post_id );
		}

	}

	/**
	 * @param $user_id
	 */
	public function delete_all_data_for_user( $user_id ) {
		$table_names = Map::locate_all_user_tables();

		foreach ( $table_names as $table_name ) {
			$table = $this->tables->get_table_object( $table_name );
			$table->delete_where( [ $table->object_relationship_key() => $user_id ] );
			$this->cache->delete_record( $table_name, "user", $user_id );
		}
	}

	/**
	 * Determine if the incoming value is equivalent to an empty table field. Just using empty() isn't sufficient as
	 * some field values will need to be stored as 0.
	 *
	 * @param ACFFieldBase $field
	 *
	 * @return bool
	 */
	private function value_is_empty( ACFFieldBase $field ) {
		$value = $field->get_value_raw();
		$field = $field->to_array();

		$is_empty = $value === '' or $value === null;

		/**
		 * Use this to control whether a value is considered empty for a given field. If the value is considered empty,
		 * the custom database table field value will be deleted.
		 *
		 * Note the if passing an empty array to update_field() — e.g; update_field( $field_name, [], $post_id ) — the
		 * empty array is stored in core meta tables as a serialized empty array. In a custom DB table, it is stored as
		 * an encoded empty array which is essentially a string. i.e; '[]'. If you prefer empty arrays to result in
		 * empty column fields, use this filter to check for empty arrays and return TRUE.
		 *
		 * @var bool $is_empty Whether or not the value is considered empty.
		 * @var mixed $value The incoming value for the given field.
		 * @var array $field The ACF field array.
		 */
		return apply_filters( 'acfcdt/value_is_empty', $is_empty, $value, $field );
	}

	/**
	 * Determine the correct value to be used when emptying (deleting) a field. Some field types require NULL to prevent
	 * coercion by MySQL.
	 *
	 * @param ACFFieldBase $field
	 * @param ACFSelector $selector
	 *
	 * @return string|null
	 * @see \ACFCustomDatabaseTables\Coordinator\InterceptCoordinator::get_empty_value_for_data_type()
	 */
	private function get_empty_value_for_field( ACFFieldBase $field, ACFSelector $selector ) {
		$value = $this->get_empty_value_for_data_type( Map::get_field_column_type( $field, $selector ) );
		$field = $field->to_array();

		/**
		 * Use this to control the value used to represent an empty database table field. The given value is the empty
		 * value for the relevant column data type which can be controlled using the `acfcdt/empty_value_for_data_type`
		 * filter if necessary.
		 *
		 * @var mixed $value The value used to 'empty' the relevant database table field.
		 * @var array $field The ACF field array.
		 */
		return apply_filters( 'acfcdt/empty_value_for_field', $value, $field );
	}

	/**
	 * The update value needs to change here depending on the field type. Some types, if handed an empty string, will
	 * coerce the value to an appropriate default for the type and that isn't necessarily the desired result in the
	 * database. To facilitate this, we need to check the field column type that is being removed and determine whether
	 * or not it needs to be NULL.
	 *
	 * Types that require null to prevent coercion of the value:
	 *      INTEGER, INT, SMALLINT, TINYINT, MEDIUMINT, BIGINT, DECIMAL, NUMERIC,
	 *      FLOAT, DOUBLE, BIT DATE, DATETIME, TIMESTAMP, YEAR, TIME
	 *
	 * Typs that require '':
	 *      CHAR, VARCHAR, BINARY, VARBINARY, BLOB, TEXT, ENUM, SET
	 *
	 * @param string $data_type The column data type.
	 *
	 * @return string|null
	 */
	private function get_empty_value_for_data_type( $data_type ) {
		$value = '';

		// Strip away any length modifiers & whitespace; lowercase the type.
		$type = trim( strtolower( explode( '(', $data_type )[0] ) );

		if ( in_array( $type, [
			'integer',
			'int',
			'smallint',
			'tinyint',
			'mediumint',
			'bigint',
			'decimal',
			'numeric',
			'float',
			'double',
			'bit',
			'date',
			'datetime',
			'timestamp',
			'year',
			'time',
		] ) ) {
			$value = null;
		}

		/**
		 * Use this to control the value used to represent an empty database table field of a given type. This is,
		 * effectively, the equivalent of deleting the data for a specific data type. If you need to override the empty
		 * value for a specific ACF field, use `acfcdt/filter_empty_value_for_field` instead.
		 *
		 * @var mixed $value The empty value for the given column data type.
		 * @var string $type The data type.
		 */
		return apply_filters( 'acfcdt/empty_value_for_data_type', $value, $type );
	}

	/**
	 * Returns a single column value from a given table.
	 *
	 * @param string $table_name
	 * @param string $column_name
	 * @param string $context
	 * @param string|int $id
	 *
	 * @return WP_Error|null|mixed
	 */
	private function lookup_field_value( $table_name, $column_name, $context, $id ) {
		$row = $this->cache->get_record( $table_name, $context, $id );

		if ( ! $row ) {
			$table = $this->tables->get_table_object( $table_name );

			if ( is_wp_error( $table ) ) {
				return Error::log( "Could not locate the `$table_name` object." )->return( $table );
			}

			if ( $row = $table->find_value( $column_name, $context, $id ) ) {
				$this->cache->set_record( $table_name, $context, $id, $row );
			}
		}

		return ( $row and isset( $row[ $column_name ] ) )
			? $row[ $column_name ]
			: null;
	}

	/**
	 * Find all rows from within a sub table matching the context and object ID.
	 *
	 * @param string $table_name
	 * @param string $context The context
	 * @param int $id The object ID
	 *
	 * @return array|bool|WP_Error|null
	 */
	private function find_sub_table_rows( $table_name, $context, $id ) {
		$rows = $this->cache->get_record( $table_name, $context, $id );

		if ( ! $rows ) {
			$table = $this->tables->get_table_object( $table_name );

			if ( is_wp_error( $table ) ) {
				return Error::log( "Could not locate the `$table_name` object." )->return( $table );
			}

			$object_id_key = $table->object_relationship_key();

			if ( $rows = $table->find_where( [ $object_id_key => $id ] ) ) {

				// remove table data that the repeater field isn't expecting
				foreach ( $rows as $index => $row ) {
					unset(
						$rows[ $index ]['id'],
						$rows[ $index ][ $object_id_key ],
						$rows[ $index ]['_sort_order']
					);
				}

				$this->cache->set_record( $table_name, $context, $id, $rows );
			}
		}

		return $rows;
	}

	/**
	 * Establish row numbers from a complex field name using a matching pattern.
	 *
	 * @param string $field_name e.g; foo_0_bar_2_baz
	 * @param string $field_name_pattern e.g; foo_(\d+)_bar_(\d+)_baz
	 *
	 * e.g;
	 *      $this->extract_row_numbers('foo_0_bar_2_baz', 'foo_(\d+)_bar_(\d+)_baz') > [0,2]
	 *
	 * @return array
	 */
	private function extract_row_numbers( $field_name, $field_name_pattern ) {
		$row_numbers = [];

		preg_match( "/^$field_name_pattern$/", $field_name, $row_numbers );

		if ( ! empty( $row_numbers ) ) {
			array_shift( $row_numbers ); // Remove the pattern from the first index.
		}

		return $row_numbers;
	}


	// DEPRECATED MEMBERS.

	/**
	 * Creates/updates a single value.
	 *
	 * @param string $key
	 * @param string|array $value
	 * @param string $context ACFs selector context. e.g; post|user
	 * @param int $id
	 *
	 * @return mixed|WP_Error
	 * @deprecated in version 1.1
	 */
	public function update( $key, $value, $context, $id ) {
		_deprecated_function( __METHOD__, '1.1', '\ACFCustomDatabaseTables\Coordinator\InterceptCoordinator::update_field()' );

		return new WP_Error( 'acfcdt', 'Method deprecated and no longer in use.' );
	}

	/**
	 * @param $key
	 * @param string $context
	 * @param $id
	 *
	 * @return null|WP_Error|mixed
	 *
	 * @deprecated in favour of \ACFCustomDatabaseTables\Coordinator\InterceptCoordinator::find_field_value()
	 */
	public function find( $key, $context, $id ) {
		_deprecated_function( __METHOD__, '1.1.0', '\ACFCustomDatabaseTables\Coordinator\InterceptCoordinator::find_field_value()' );

		return null;
	}

	/**
	 * Deletes data for a particular object and clears any cached records for that object
	 *
	 * @param string $context e.g; post:book
	 * @param int $object_id e.g; post.ID or user.ID
	 *
	 * @deprecated in 1.1
	 */
	public function delete_all_data_for_object( $context, $object_id ) {
		_deprecated_function( __METHOD__, '1.1.0', '\ACFCustomDatabaseTables\Coordinator\InterceptCoordinator::delete_all_data_for_post()' );

		$data = explode( ':', $context );

		switch ( $data[0] ) {
			case 'user':
				// todo - when we need it
				break;
			case 'post':
				$this->delete_all_data_for_post( $object_id, $data[1] );
				break;
		}

	}

}