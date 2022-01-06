<?php

namespace ACFCustomDatabaseTables\Factory;

use ACFCustomDatabaseTables\Data\TableValidator;
use ACFCustomDatabaseTables\DB\DynamicColumnBase;
use ACFCustomDatabaseTables\DB\DynamicTableBase;
use ACFCustomDatabaseTables\DB\DynamicJoinTable;
use ACFCustomDatabaseTables\DB\DynamicSubTable;
use ACFCustomDatabaseTables\DB\DynamicMetaTable;
use WP_Error;

class DynamicTableFactory {

	/** @var DynamicColumnFactory */
	protected $dynamic_column_factory;

	/** @var  TableValidator */
	protected $validator;

	/**
	 * DynamicTableFactory constructor.
	 *
	 * @param DynamicColumnFactory $dynamic_column_factory
	 * @param TableValidator $table_validator
	 */
	public function __construct( DynamicColumnFactory $dynamic_column_factory, TableValidator $table_validator ) {
		$this->dynamic_column_factory = $dynamic_column_factory;
		$this->validator = $table_validator;
	}

	/**
	 * Makes a DynamicTableBase object from an args array
	 *
	 * @param array $args
	 *
	 * @return DynamicTableBase|WP_Error
	 */
	public function make( array $args ) {

		$valid = $this->validator->validate_args( $args );

		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		$args = $this->validator->normalise_args( $args );

		switch ( $args['type'] ) {
			case 'join':
				$table = new DynamicJoinTable( $args['name'] );
				break;
			case 'sub':
				$table = new DynamicSubTable( $args['name'] );
				break;
			case 'meta':
			default:
				$table = new DynamicMetaTable( $args['name'] );
		}

		$table->set_object_relationship_key( $this->validator->get_object_relationship_key_name( $args ) );

		! isset( $args['primary_key'] ) or $table->set_primary_key( $args['primary_key'] );
		! isset( $args['keys'] ) or $table->set_keys( $args['keys'] );

		foreach ( $args['columns'] as $column_args ) {
			$column = $this->dynamic_column_factory->make( $column_args );
			if ( $column instanceof DynamicColumnBase ) {
				$table->add_dynamic_column( $column );
			}
		}

		return $table;
	}

}