<?php

namespace ACFCustomDatabaseTables\Coordinator;

use WP_Error;

class TableCreationCoordinator {

	/** @var  TableCoordinator */
	private $coordinator;

	/**
	 * TableCreationCoordinator constructor.
	 *
	 * @param TableCoordinator $coordinator
	 */
	public function __construct( TableCoordinator $coordinator ) {
		$this->coordinator = $coordinator;
	}

	/**
	 * Runs the table creation/update sequence.
	 *
	 * @return array|WP_Error Array of messages from dbDelta on success, WP_Error object on failure
	 */
	public function update_tables() {

		// Flush the cache to ensure persistent object caches don't interfere with table modification. Without this, new
		// columns aren't created and data isn't stored-in/retrieved-from the tables as expected.
		wp_cache_flush();

		$map = $this->coordinator->build_map_from_json();

		if ( is_wp_error( $map ) ) {
			return $map;
		}

		$tables = $this->coordinator->get_all_table_objects();

		if ( ! $tables ) {
			return new WP_Error( 'acfcdt', 'Could not get table objects – no tables can be created/updated at this time.' );
		}

		$dbDelta_output = [
			'<strong>Results from WordPress\' <code>dbDelta()</code> function:</strong>'
		];

		foreach ( $tables as $table ) {
			$dbDelta_output = array_merge( $dbDelta_output, array_values( $table->create_or_update_table() ) );
		}

		return apply_filters( 'acfcdt/after_create_or_update_tables', $dbDelta_output );
	}

	/**
	 * Rebuilds the table map php file cache
	 *
	 * @return true|WP_Error
	 */
	public function rebuild_map_cache() {
		$update = $this->coordinator->update_map_cache();

		return is_wp_error( $update ) ? $update : true;
	}

}