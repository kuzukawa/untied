<?php

namespace ACFCustomDatabaseTables\Data;

use WP_Error;

class TableValidator {

	/**
	 * Checks to ensure our args array has the minimum required fields
	 *
	 * @param array $args
	 *
	 * @return bool|WP_Error
	 */
	public function validate_args( array $args ) {

		$missing = [];

		// must haves: name, relationship.type, columns
		isset( $args['name'] ) or $missing[] = 'name';
		isset( $args['columns'] ) or $missing[] = 'columns';
		isset( $args['relationship']['type'] ) or $missing[] = 'relationship.type';

		if ( $missing ) {
			return new WP_Error( 'acfcdt', 'TableValidator::validate_args missing required args: ' . implode( ', ', $missing ) );
		}

		return true;
	}

	/**
	 * Ensures we have a normalised args set for working with dynamic table objects.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function normalise_args( array $args ) {

		// todo - need to work through the following:
		// 1. Key expansion
		// 2. Object relationship key set up (e.g; post_id) where it hasn't already been defined on the table
		// 3. Auto-addition of primary key, where a primary key hasn't already been defined
		// Note: only on auto-incrementing column can exist on the table, so columns need to be stripped of this where
		// they aren't allowed to have it. Also, the auto-incr column MUST either be a key or be part of a composite key.
		// If these conditions around auto-incr are breached, DB errors occur.

		$args = $this->maybe_set_type( $args );
		$args = $this->maybe_expand_relationship_shorthand( $args );
		$args = $this->maybe_expand_columns( $args );
		//$args = $this->maybe_expand_keys( $args ); // todo - make this happen when we open up this functionality
		$args = $this->maybe_add_relationship_default( $args );
		$args = $this->maybe_add_object_relationship_key( $args );
		$args = $this->maybe_add_object_relationship_column( $args );
		//$args = $this->maybe_add_primary_key( $args );
		//$args = $this->maybe_add_order_column( $args );

		return $args;
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	public function maybe_set_type( array $args ) {
		isset( $args['type'] ) or $args['type'] = 'meta';

		return $args;
	}

	/**
	 * Expands any simple strings in the args.columns array into arrays our app can understand.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function maybe_expand_columns( array $args ) {

		if ( isset( $args['columns'] ) and is_array( $args['columns'] ) ) {
			foreach ( $args['columns'] as $i => $column ) {

				// expand string shorthand
				if ( is_string( $column ) ) {
					$args['columns'][ $i ] = [
						'name' => $column,
						'format' => '%s',
						'map' => [
							'type' => 'acf_field_name',
							'identifier' => $column
						]
					];
				}

				// temp - flesh out column args
				isset( $args['columns'][ $i ]['map'] ) or $args['columns'][ $i ]['map'] = [];
				isset( $args['columns'][ $i ]['map']['type'] ) or $args['columns'][ $i ]['map']['type'] = 'acf_field_name';
				isset( $args['columns'][ $i ]['map']['identifier'] ) or $args['columns'][ $i ]['map']['identifier'] = $column['name'];
				isset( $args['columns'][ $i ]['format'] ) or $args['columns'][ $i ]['format'] = '%s';

			}
		}

		return $args;
	}

	// todo - make this happen when we open up this functionality
	public function maybe_expand_keys( array $args ) {
		// todo
		// ensure keys[x].columns is an array
		// if no keys[x].name specified, implode all column names
		// if keys[x].column[i] has no defined column on the table object, remove it - note: think about where this sites in the process in $this->normalise_args() and make sure this won't remove cols prematurely. OR, move this to a later method.
		return $args;
	}

	/**
	 * Allows for shorthand to be passed in via the 'relationship' table args property by converting that shorthand
	 * into the expected array form.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function maybe_expand_relationship_shorthand( array $args ) {

		if ( isset( $args['relationship'] ) and is_string( $args['relationship'] ) ) {

			$parts = explode( ':', $args['relationship'] );

			if ( ! in_array( $parts[0], [ 'post', 'user' ] ) ) {
				$parts[1] = $parts[0];
				$parts[0] = 'post';
			}

			$args['relationship'] = [
				'type' => $parts[0]
			];

			if ( $args['relationship']['type'] === 'post' ) {
				$args['relationship']['post_type'] = isset( $parts[1] )
					? $parts[1]
					: 'post';
			}

		}

		return $args;
	}

	/**
	 * Sets the default relationship data if the table definition doesn't have this defined.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function maybe_add_relationship_default( array $args ) {
		isset( $args['relationship'] ) or $args['relationship'] = [];
		isset( $args['relationship']['type'] ) or $args['relationship']['type'] = 'post';

		return $args;
	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	public function get_object_relationship_key_name( array $args ) {
		$type = $args['relationship']['type'];
		$key = "{$type}_id";

		return $key;
	}

	/**
	 * Checks whether a key has been defined for the object this table relates to
	 *
	 * @param array $args
	 *
	 * @return bool
	 */
	private function object_relationship_key_defined( array $args ) {

		$key = $this->get_object_relationship_key_name( $args );

		// todo - work this bit out – is it essential?
		//if ( isset( $args['primary_key'] ) and in_array( $key, $args['primary_key'] ) ) {
		//	return true;
		//}

		if ( ! isset( $args['keys'] ) ) {
			return false;
		}

		return (bool) array_filter( $args['keys'], function ( $key_def ) use ( $key ) {
			return $key_def['name'] === $key;
		} );
	}

	/**
	 * @param array $args
	 *
	 * @return bool
	 */
	private function object_relationship_column_defined( array $args ) {

		if ( ! isset( $args['columns'] ) ) {
			return false;
		}

		$key = $this->get_object_relationship_key_name( $args );

		return (bool) array_filter( $args['columns'], function ( $col_def ) use ( $key ) {
			return $col_def['name'] === $key;
		} );
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	public function maybe_add_object_relationship_column( array $args ) {

		$key = $this->get_object_relationship_key_name( $args );

		isset( $args['columns'] ) or $args['columns'] = [];

		if ( ! $this->object_relationship_column_defined( $args ) ) {
			array_unshift( $args['columns'], [
				'name' => $key,
				'format' => '%d',
				'null' => false,
				'unsigned' => true,
				'default' => 0
			] );
		}

		return $args;
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	public function maybe_add_object_relationship_key( array $args ) {

		$key = $this->get_object_relationship_key_name( $args );

		isset( $args['keys'] ) or $args['keys'] = [];

		if ( ! $this->object_relationship_key_defined( $args ) ) {
			array_unshift( $args['keys'], [
				'name' => $key,
				'unique' => false,
				'columns' => [
					$key
				]
			] );
		}

		return $args;
	}

	/**
	 * Handles the set up of primary_key on table definition and the addition of an object_id column if the table definition
	 * doesn't explicitly already define the primary key.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function maybe_add_primary_key( array $args ) {
		if ( ! isset( $args['primary_key'] ) ) {

			$key = 'id';

			$args['primary_key'] = [ $key ];

			array_unshift( $args['columns'], [
				'name' => $key,
				'format' => '%d',
				'auto_increment' => true,
				'null' => false,
				'unsigned' => true,
			] );

		}

		return $args;
	}

	/**
	 * TODO - this ensures join tables have an '_sort_order' column. Without it, ACF's draggable functionality won't matter much, as the keys control the order
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function maybe_add_order_column( array $args ) {

//		if ( $args['type'] !== 'join' ) {
//			return $args;
//		}
//
//		$is_order_col_defined = (bool) array_filter( $args['columns'], function ( $col ) {
//			return $col['name'] === '_sort_order';
//		} );
//
//		if ( ! $is_order_col_defined ) {
//			$args['columns'][] = [
//				'name'    => '_sort_order',
//				'format'  => '%d',
//				'default' => 0,
//			];
//		}

		return $args;
	}

}