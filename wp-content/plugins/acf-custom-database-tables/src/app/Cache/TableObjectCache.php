<?php

namespace ACFCustomDatabaseTables\Cache;

use ACFCustomDatabaseTables\DB\DynamicTableBase;

class TableObjectCache extends CacheBase {

	/**
	 * TableObjectCache constructor.
	 *
	 * @param string $group
	 */
	public function __construct( $group = 'acfcdt/table_objects' ) {
		parent::__construct( $group );
	}

	/**
	 * @param string $key
	 *
	 * @return bool|DynamicTableBase
	 */
	public function get( $key ) {
		return parent::get( $key );
	}

	/**
	 * @param string $key
	 * @param DynamicTableBase $value
	 *
	 * @return bool
	 */
	public function set( $key, $value ) {
		return parent::set( $key, $value );
	}

}