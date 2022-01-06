<?php

namespace ACFCustomDatabaseTables\Module;

use ACFCustomDatabaseTables\Contract\ModuleInterface;
use ACFCustomDatabaseTables\DB\DynamicTableBase;

/**
 * Class AfterTableSchemaUpdateModule
 * @package ACFCustomDatabaseTables\Module
 *
 * Provides filters for public use that make it possible to run custom processes after a table is created or updated.
 * This module utilises some internal hooks and filters in order to provide a stable set of filters that are simple to
 * implement.
 */
class AfterTableSchemaUpdateModule implements ModuleInterface {

	/** @var string|null The name of the current table being created/updated */
	private $table_name = null;

	/**
	 * @return string
	 */
	public function name() {
		return 'after_table_schema_update';
	}

	public function init() {
		add_action( 'acfcdt/internal/before_create_or_update_table', [ $this, '_reset_table_name' ], 1 );
		add_action( 'acfcdt/internal/before_create_or_update_table', [ $this, '_set_table_name' ] );
		add_filter( 'acfcdt/internal/filter_db_delta_results_after_create_or_update_table', [
			$this,
			'_filter'
		], 10, 2 );
	}

	/**
	 * Safeguards against having the wrong table name in this module.
	 */
	public function _reset_table_name() {
		$this->table_name = null;
	}

	/**
	 * @param DynamicTableBase $table
	 */
	public function _set_table_name( DynamicTableBase $table ) {
		$this->table_name = $table->table_name();
	}

	/**
	 * Applies filters for each table
	 *
	 * @param array $db_delta_results
	 * @param DynamicTableBase $table
	 *
	 * @return string
	 */
	public function _filter( $db_delta_results, DynamicTableBase $table ) {

		if ( $this->table_name ) {

			/**
			 * General use filter for running custom handlers after each table schema is applied to the database. The
			 * hooked function will receive both the results of dbDelta() as an array and the table name – without the
			 * wpdb prefix – that was just applied.
			 *
			 * Use this filter to create custom indexes, notifications, logs, etc. Just be sure to return the dbDelta()
			 * results array so that it can be output to the admin UI. You can also append your own notices to the
			 * dbDelta() output array and they'll appear in the admin UI when the table update process is run.
			 */
			$db_delta_results = apply_filters( "acfcdt/after_create_or_update_table", $db_delta_results, $this->table_name );

			/**
			 * Specific use filter for running custom handlers after a specific table schema is applied to the database.
			 * See docs above for more information.
			 *
			 * Example usage: add_filter('acfcdt/after_create_or_update_table/my_table', 'my_custom_function')
			 */
			$db_delta_results = apply_filters( "acfcdt/after_create_or_update_table/{$this->table_name}", $db_delta_results );
		}

		return $db_delta_results;
	}
}
