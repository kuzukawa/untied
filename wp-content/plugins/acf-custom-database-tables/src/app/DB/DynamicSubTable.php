<?php

namespace ACFCustomDatabaseTables\DB;

use ACFCustomDatabaseTables\Utils\Arr;
use ACFCustomDatabaseTables\Utils\Error;

class DynamicSubTable extends DynamicTableBase {

	/**
	 * Returns the table type
	 *
	 * @return string
	 */
	function type() {
		return self::TYPE_SUB;
	}

	public function update_value( $column_name, $value, $object_id, $where = [] ) {
		if ( ! isset( $where['_sort_order'] ) ) {
			return Error::log( "Failed to update column `$column_name` on table `{$this->table_name()}` due to missing `__sort_order`." )->return( false );
		}

		$sort_order = intval( $where['_sort_order'] );
		$obj_key = $this->object_relationship_key();

		$existing = $this->find_where( [
			$obj_key => $object_id,
			'_sort_order' => $sort_order,
		] );

		if ( isset( $existing[0]['id'] ) ) {

			$bool = $this->update( [
				$column_name => $value,
				'_sort_order' => $sort_order,
			], [
				'id' => $existing[0]['id']
			] );

		} else {

			$bool = $this->insert( [
				$obj_key => $object_id,
				$column_name => $value,
				'_sort_order' => $sort_order,
			] );
		}

		return $bool;
	}

	public function find_where( array $args, $limit = 0, $offset = 0, $order_by = '_sort_order', $order = 'ASC' ) {
		return parent::find_where( $args, $limit, $offset, $order_by, $order );
	}

}