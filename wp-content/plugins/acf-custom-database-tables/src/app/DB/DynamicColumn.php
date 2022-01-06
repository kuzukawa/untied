<?php

namespace ACFCustomDatabaseTables\DB;

class DynamicColumn extends DynamicColumnBase {

	/**
	 * Outputs column schema
	 *
	 * Any specific type of column that extends this object needs to define a schema method that returns SQL that makes
	 * up the `CREATE TABLE …` syntax
	 *
	 * @return string
	 */
	function schema() {
		$name = $this->name();
		$type = $this->type();
		$schema = $this->maybe_append_default( "`$name` $type" );

		return $schema;
	}

}