<?php

namespace ACFCustomDatabaseTables\Factory;

use ACFCustomDatabaseTables\Data\ColumnValidator;
use ACFCustomDatabaseTables\DB\DynamicColumn;
use ACFCustomDatabaseTables\DB\DynamicColumnBase;
use ACFCustomDatabaseTables\DB\DynamicColumnBigint;
use WP_Error;
use wpdb;

class DynamicColumnFactory {

	/**
	 * @var wpdb
	 * @deprecated No longer injection WPDB due to Redis-related issues. This will be removed in version 1.2
	 */
	protected $wpdb;

	/** @var  ColumnValidator */
	protected $validator;

	/**
	 * DynamicColumnFactory constructor.
	 *
	 * @param null $wpdb Deprecated — don't pass anything other than null.
	 * @param ColumnValidator $column_validator
	 */
	public function __construct( $wpdb, ColumnValidator $column_validator ) {
		if ( null !== $wpdb ) {
			_deprecated_argument( __METHOD__, '1.1 (ACF Custom Database Tables)', 'No longer injecting $wpdb due to object cache issues. Change this to NULL. Any related props will be removed in version 1.2' );
		}

		$this->validator = $column_validator;
	}

	/**
	 * Makes a DynamicColumn object from an args array
	 *
	 * @param array $args
	 *
	 * @return DynamicColumnBase|WP_Error
	 */
	public function make( array $args ) {

		$validation = $this->validator->validate_args( $args );

		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		$args = $this->validator->normalise_args( $args );

		switch ( $args['format'] ) {
			case '%d':
				$column = new DynamicColumnBigint( null, $args['name'], $args['format'] );
				break;
			case '%s':
			default:
				$column = new DynamicColumn( null, $args['name'], $args['format'] );
		}

		! isset( $args['type'] ) or $column->set_type( $args['type'] );
		! isset( $args['unique'] ) or $column->set_unique( $args['unique'] );
		! isset( $args['auto_increment'] ) or $column->set_auto_increment( $args['auto_increment'] );
		! isset( $args['null'] ) or $column->set_null( $args['null'] );
		! isset( $args['unsigned'] ) or $column->set_unsigned( $args['unsigned'] );
		! isset( $args['default'] ) or $column->set_default_value( $args['default'] );

		return $column;
	}

}