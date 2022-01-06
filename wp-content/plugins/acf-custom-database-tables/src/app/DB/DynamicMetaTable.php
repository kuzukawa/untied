<?php

namespace ACFCustomDatabaseTables\DB;

class DynamicMetaTable extends DynamicTableBase {

	/**
	 * Returns the table type
	 *
	 * @return string
	 */
	function type() {
		return self::TYPE_META;
	}

}