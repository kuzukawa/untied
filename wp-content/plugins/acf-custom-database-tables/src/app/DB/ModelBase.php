<?php

namespace ACFCustomDatabaseTables\DB;

use \ACFCustomDatabaseTables\Vendor;

/**
 * Class ModelBase
 * @package ACFCustomDatabaseTables\DB
 *
 * Overrides base methods to:
 * - Support validation bypass params
 * - Fix issues around incorrect bool return vals (some method return zero on success, causing a falsy response)
 * - Add additional query params
 */
abstract class ModelBase extends Vendor\Mishterk\WP\Tools\DB\ModelBase {

	// todo - pull in the validate_rows method and remove/modify the primary key validation part as the rest is sensible.

	/**
	 * Custom handling for ACFCDT for more accurate return value
	 *
	 * Updates an existing row based on an array of where conditions in the format ['col_id' => '1', 'col_name' => 'example']
	 *
	 * @param array $row
	 * @param array $where
	 *
	 * @return bool
	 */
	public function update( array $row, array $where ) {
		global $wpdb;

		parent::update( $row, $where );
		$bool = $wpdb->last_result;

		return $bool !== false;
	}

}