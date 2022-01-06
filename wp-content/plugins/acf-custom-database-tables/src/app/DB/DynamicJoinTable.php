<?php

namespace ACFCustomDatabaseTables\DB;

use ACFCustomDatabaseTables\Utils\Arr;

class DynamicJoinTable extends DynamicTableBase {

	/**
	 * Returns the table type
	 *
	 * @return string
	 */
	function type() {
		return self::TYPE_JOIN;
	}

	/**
	 * App-specific update method. Use this in intercepts and override this in child classes
	 *
	 * @param $column_name
	 * @param string|array $value
	 * @param $id
	 * @param array $where Irrelevant to this base functionality but essential for sub table support. Not the ideal way
	 * to place this but this will have to do for now.
	 *
	 * @return bool
	 */
	public function update_value( $column_name, $value, $id, array $where = [] ) {
		global $wpdb;

		$value = $this->prepare_incoming_value_array( $value );
		$obj_key = $this->object_relationship_key();
		$bool = true;

		// if no values passed in, delete all existing and stop there – field is empty.
		if ( ! $value ) {
			$this->delete_existing_rows( $obj_key, $id );

			return $bool;
		}

		if ( $existing = $this->find_where( [ $obj_key => $id ] ) ) {

			$ids_to_delete = [];
			$remaining_rows = [];

			foreach ( $existing as $row ) {
				if ( false === in_array( $row[ $column_name ], $value ) ) {
					$ids_to_delete[] = $row['id'];
				} else {
					$row['_sort_order'] = (int) array_search( $row[ $column_name ], $value );
					$remaining_rows[] = $row;
				}
			}

			// delete what we don't need
			if ( $ids_to_delete ) {
				$wpdb->query( "DELETE FROM `{$this->full_table_name()}` WHERE `id` IN (" . implode( ',', $ids_to_delete ) . ");" );
			}

			// update what we're keeping
			if ( $remaining_rows ) {
				$bool = $this->insert_or_update_rows( $remaining_rows, false );
			}

			// insert new rows
			if ( $new_ids = array_diff( $value, Arr::array_column( $remaining_rows, $column_name ) ) ) {
				$new_rows = $this->prepare_row_data_sets_for_insertion( $obj_key, $column_name, $new_ids, $id );
				$next_index = count( $remaining_rows );

				foreach ( $new_rows as $i => $row ) {
					$new_rows[ $i ]['_sort_order'] = $next_index ++;
				}

				$bool = $this->insert_rows( $new_rows, false );
			}

		} else {
			$value_array = $this->prepare_row_data_sets_for_insertion( $obj_key, $column_name, $value, $id );
			$bool = $this->insert_rows( $value_array, false );
		}

		return $bool;
	}

	/**
	 * Handles value retrieval specifically for join tables
	 *
	 * @param string $column_name
	 * @param string $context
	 * @param int $object_id
	 * @param array $where Not currently used here in the join table context. See sub tables.
	 *
	 * @return array|bool|null
	 */
	public function find_value( $column_name, $context, $object_id, array $where = [] ) {

		$obj_key = $this->object_relationship_key();

		$row = $this->find_where( [ $obj_key => $object_id ], 0, 0, '_sort_order' );

		if ( $row === false ) {
			return null;
		}

		if ( count( $row ) === 0 ) {
			return null;
		}

		// we only want the join object data as a single dimensional array of values
		$row = [ $column_name => Arr::array_column( $row, $column_name ) ];

		return $row;
	}

	/**
	 * Makes sure we have an array containing no empty values.
	 *
	 * An empty relational field value, when cast as an array, looks like [0 => ''], which ends up being stored in the
	 * join table as a post_id => 0 relational row. We don't want this, hence, the stripping of empty values.
	 *
	 * Note: this also strips falsy values (0, false, null, etc) so, if we run into issues around that, this will
	 * need a handler added to array_filter() that strips only the unwanted values.
	 *
	 * @param $value
	 *
	 * @return array
	 */
	private function prepare_incoming_value_array( $value ) {
		is_array( $value ) or $value = array_filter( (array) $value );

		return $value;
	}

	/**
	 * Query all existing joins for this ID and delete them all in prep for inserting the updated set.
	 *
	 * @param $obj_key
	 * @param $id
	 */
	private function delete_existing_rows( $obj_key, $id ) {
		$this->delete_where( [ $obj_key => $id ] );
	}

	/**
	 * Takes the value array and prepares it for insertion by formatting it into a multi-dim array of row data sets.
	 *
	 * @param string $obj_key
	 * @param string $column_name
	 * @param array $value
	 * @param int $id
	 *
	 * @return array
	 */
	private function prepare_row_data_sets_for_insertion( $obj_key, $column_name, array $value, $id ) {
		$value_array = [];

		$order_count = 0;
		foreach ( $value as $v ) {
			$value_array[] = [
				$obj_key => $id,
				$column_name => $v,
				'_sort_order' => $order_count,
			];
			$order_count ++;
		}

		return $value_array;
	}

}