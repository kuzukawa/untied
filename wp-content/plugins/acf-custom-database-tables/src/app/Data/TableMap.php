<?php

namespace ACFCustomDatabaseTables\Data;

use ACFCustomDatabaseTables\Model\ACFFields\ACFFieldBase;
use ACFCustomDatabaseTables\Model\ACFSelector;
use ACFCustomDatabaseTables\Service\TableNameValidator;
use ACFCustomDatabaseTables\Utils\Arr;
use WP_Error;

class TableMap {

	/**
	 * @var TableValidator
	 */
	protected $table_validator;

	/**
	 * @var TableNameValidator
	 */
	protected $table_name_validator;

	/**
	 * @var array Table definitions
	 */
	protected $tables = [];

	/**
	 * @var array Table name list e.g;
	 *  [
	 *      0 => 'table_name_1',
	 *      1 => 'table_name_2',
	 *      2 => 'table_name_3',
	 *      4 => 'table_name_4',
	 *  ]
	 */
	protected $table_names = [];

	/**
	 * @var array objects and their associated tables. e.g;
	 *  [
	 *      'user' => [ 0, 1, 2 ],
	 *      'post' => [ 3 ],
	 *  ]
	 */
	protected $types = [];

	/**
	 * @var array Post types and their associated tables. e.g;
	 *  [
	 *      'post'        => [ 0, 2 ],
	 *      'page'        => [ 1, 4 ],
	 *      'custom_type' => [ 3 ]
	 *  ]
	 */
	protected $post_types = [];

	/**
	 * @var array ACF field names and their associated tables. This map needs a little more complexity to allow for the
	 *            same ACF field names to be used in multiple places. e.g;
	 *  [
	 *      'user' => [
	 *          'field_name1' => [ 2 ],
	 *          'field_name2' => [ 3, 4 ]
	 *      ],
	 *      'post:post' => [
	 *          'field_name1' => [ 2 ],
	 *          'field_name2' => [ 3, 4 ]
	 *      ],
	 *      'post:page' => [
	 *          'field_name1' => [ 2 ],
	 *          'field_name2' => [ 3, 4 ]
	 *      ],
	 *      'post:post_type' => [
	 *          'field_name1' => [ 2 ],
	 *          'field_name2' => [ 3, 4 ]
	 *      ]
	 *  ]
	 */
	protected $acf_field_names = [];

	/**
	 * @var array ACF field names and their associated field keys. This map needs a little more complexity to allow for
	 *            the same ACF field names to be used in multiple places. e.g;
	 *  [
	 *      'user' => [
	 *          'field_3jd8fj42k' => 'field_name1',
	 *          'field_3jd8fj42r' => 'field_name2',
	 *      ],
	 *      'post:post' => [
	 *          'field_3jd8fj42t' => 'field_name1',
	 *          'field_3jd8fj42a' => 'field_name2',
	 *      ],
	 *      'post:page' => [
	 *          'field_3jd8fj42u' => 'field_name1',
	 *          'field_3jd8fj42i' => 'field_name2',
	 *      ],
	 *      'post:post_type' => [
	 *          'field_3jd8fj42m' => 'field_name1',
	 *          'field_3jd8fj42l' => 'field_name2',
	 *      ]
	 *  ]
	 */
	protected $acf_field_keys = [];

	/**
	 * @var array
	 *  [
	 *      'user' => [
	 *          'field_3jd8fj42k' => 'field_name1_(\d+)_sub_field',
	 *          'field_3jd8fj42r' => 'field_name2_(\d+)_sub_field',
	 *      ],
	 *      'post:post' => [
	 *          'field_3jd8fj42t' => 'field_name1_(\d+)_sub_field',
	 *          'field_3jd8fj42a' => 'field_name2_(\d+)_sub_field',
	 *      ],
	 *      'post:page' => [
	 *          'field_3jd8fj42u' => 'field_name1_(\d+)_sub_field',
	 *          'field_3jd8fj42i' => 'field_name2_(\d+)_sub_field',
	 *      ],
	 *      'post:post_type' => [
	 *          'field_3jd8fj42m' => 'field_name1_(\d+)_sub_field',
	 *          'field_3jd8fj42l' => 'field_name2_(\d+)_sub_field',
	 *      ]
	 *  ]
	 */
	protected $acf_field_key_name_patterns = [];

	/**
	 * @var array Regex patterns for matching complex ACF field names and their associated tables. Similar to
	 *            \ACFCustomDatabaseTables\Data\TableMap::$acf_field_names.
	 *
	 *  [
	 *      'user' => [
	 *          'repeater_(\d+)_subfield' => [ 2 ],
	 *          'repeater_(\d+)_subfield_(\d+)_nested_subfield' => [ 3, 4 ]
	 *      ],
	 *      'post:post' => [
	 *          'repeater_(\d+)_subfield' => [ 2 ],
	 *          'repeater_(\d+)_subfield_(\d+)_nested_subfield' => [ 3, 4 ]
	 *      ],
	 *      'post:page' => [
	 *          'repeater_(\d+)_subfield' => [ 2 ],
	 *          'repeater_(\d+)_subfield_(\d+)_nested_subfield' => [ 3, 4 ]
	 *      ],
	 *      'post:post_type' => [
	 *          'repeater_(\d+)_subfield' => [ 2 ],
	 *          'repeater_(\d+)_subfield_(\d+)_nested_subfield' => [ 3, 4 ]
	 *      ]
	 *  ]
	 *
	 *
	 * @var array
	 */
	protected $acf_field_name_patterns = [];

	/**
	 * @var array column data types e.g;
	 *  [
	 *      'table1_name' => [
	 *          'field1_name' => 'longtext',
	 *          'field2_name' => 'varchar',
	 *      ]
	 *      'table2_name' => [
	 *          'field1_name' => 'int(3)'
	 *      ],
	 *      'book_meta' => [
	 *          'author' => 'char(40)'
	 *      ],
	 *  ]
	 */
	protected $acf_field_column_types = [];

	/**
	 * todo - maybe change this to column_name_aliases as this is really only in place for that feature
	 *
	 * @var array ACF Field Names e.g;
	 *  [
	 *      'table1_name.field1_name' => 'column1_name',
	 *      'table1_name.field2_name' => 'column2_name',
	 *      'table2_name.field1_name' => 'column1_name',
	 *      'book_meta.author' => 'author_name',
	 *  ]
	 */
	protected $acf_field_column_names = [];

	/**
	 * @var array Column names mapped to table_name.field_name patterns. Used for determining which
	 *            column in a given table is mapped to a complex field name (e.g; repeater_0_field_4_subfield). e.g;
	 *  [
	 *      'table1_name.field1_(\d+)_field' => 'column1_name',
	 *      'table1_name.field1_(\d+)_field2_(\d+)_subfield' => 'column2_name',
	 *  ]
	 */
	protected $acf_field_column_name_patterns = [];

	/**
	 * todo - rename to something like root_column_keys
	 * @var array Nested field keys pointing to their root column field key.
	 */
	protected $nested_field_key_parents = [];

	/**
	 * todo - rename to something like table_column_owners
	 * @var array ACF field keys that own columns, pointing to their {table_index}.{column_name}. e.g; [
	 *      'user' => [
	 *          'field_key_1' => 1.column_1,
	 *          'field_key_2' => 1.column_2,
	 *          'field_key_3' => 2.column_1,
	 *      ],
	 *      'post:post' => [
	 *          'field_key_1' => 1.column_1,
	 *          'field_key_2' => 1.column_2,
	 *          'field_key_3' => 2.column_1,
	 *      ],
	 *      'post:page' => [
	 *          'field_key_1' => 1.column_1,
	 *          'field_key_2' => 1.column_2,
	 *          'field_key_3' => 2.column_1,
	 *      ],
	 *      'post:post_type' => [
	 *          'field_key_1' => 1.column_1,
	 *          'field_key_2' => 1.column_2,
	 *          'field_key_3' => 2.column_1,
	 *      ],
	 * ]
	 */
	protected $column_owners = [];

	/**
	 * @var array ACF field names that own a sub table mapped to table indexes. e.g;
	 *  [
	 *      'user' => [
	 *          'field_name1' => [ 2 ],
	 *          'field_name2' => [ 3, 4 ]
	 *      ],
	 *      'post:post' => [
	 *          'field_name1' => [ 2 ],
	 *          'field_name2' => [ 3, 4 ]
	 *      ],
	 *      'post:page' => [
	 *          'field_name1' => [ 2 ],
	 *          'field_name2' => [ 3, 4 ]
	 *      ],
	 *      'post:post_type' => [
	 *          'field_name1' => [ 2 ],
	 *          'field_name2' => [ 3, 4 ]
	 *      ]
	 *  ]
	 */
	protected $acf_sub_table_owners = [];

	/**
	 * @var array join table indexes and their meta tables. e.g;
	 *  [
	 *      1 => [ 0 ],
	 *      2 => [ 0 ],
	 *  ]
	 */
	protected $join_tables = [];

	/**
	 * @var array sub table indexes and their parent tables. e.g;
	 *  [
	 *      1 => [ 0 ],
	 *      2 => [ 0 ],
	 *  ]
	 */
	protected $sub_tables = [];

	/**
	 * Note: meta tables are the main tables we create – sub and join are related to meta tables.
	 *
	 * @var array meta table indexes and their child tables. e.g;
	 *  [
	 *      0 => [ 1, 2 ],
	 *      3 => [ 4 ],
	 *  ]
	 */
	protected $meta_tables = [];

	/**
	 * TableMap constructor.
	 *
	 * @param TableValidator $table_validator
	 * @param TableNameValidator $table_name_validator
	 */
	public function __construct( TableValidator $table_validator, TableNameValidator $table_name_validator ) {
		$this->table_validator = $table_validator;
		$this->table_name_validator = $table_name_validator;
	}

	/**
	 * Adds/updates table to the map. This also validates and normalises table args first.
	 *
	 * @param array $args
	 *
	 * @return bool|WP_Error
	 */
	public function add_table( array $args ) {
		$args = $this->table_validator->normalise_args( $args );

		$validation = $this->table_validator->validate_args( $args );
		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		$this->parse_table_args( $args );

		return true;
	}

	/**
	 * Returns the index of the required table name for reference in other data points
	 *
	 * @param $name
	 *
	 * @return int|WP_Error
	 */
	public function get_table_name_index( $name ) {
		$flipped = array_flip( $this->table_names );
		if ( isset( $flipped[ $name ] ) ) {
			return $flipped[ $name ];
		} else {
			return new WP_Error( 'acfcdt', "Missing TableMap::table_names entry for table $name" );
		}
	}

	/**
	 * Picks through the table args and assigns relevant data to properties for easy lookup
	 *
	 * Note: if the table def already exists and is being registered again, some issues may arise where the table name
	 * index appears where it should no longer be. e.g; if a table array is registered with one post type and then the
	 * array is changed and passed through here again, the original post_type array will still contain the table name
	 * index. This should really be a problem for us, but if it comes up, we'll need a mechanism to rebuild all reference
	 * properties on this object. Easiest way would be to simply take a copy of the tables prop, clear all props, then
	 * run all tables through this method again. Could be room for a refactor here, but not essential at this stage.
	 *
	 * @param array $args
	 */
	public function parse_table_args( array $args ) {
		$this->register_table_config( $args );
		$this->register_table_name( $args );
		$this->register_objects( $args );
		$this->register_post_types( $args );
		$this->register_columns( $args );
		$this->register_join_tables( $args );
		$this->register_sub_tables( $args );
	}

	/**
	 * Loops through the 'join_tables' param of a table object, registers the join tables within, and sets up join table
	 * related mappings.
	 *
	 * @param array $args
	 */
	public function register_join_tables( array $args ) {
		if ( isset( $args['join_tables'] ) ) {
			foreach ( $args['join_tables'] as $join_table_args ) {

				$join_table_args['relationship'] = $args['relationship'];

				// todo - consider abstracting out into $this->add_join_table();
				$join_table_args['type'] = 'join'; // maybe move this to table normalisation instead?
				$this->add_table( $join_table_args );

				// register the relationship between tables
				$parent_index = $this->get_table_name_index( $args['name'] );
				$child_index = $this->get_table_name_index( $join_table_args['name'] );

				// map child to parent
				isset( $this->join_tables[ $child_index ] ) or $this->join_tables[ $child_index ] = [];
				$this->add_unique( $this->join_tables[ $child_index ], $parent_index );

				// map parent to child
				isset( $this->meta_tables[ $parent_index ] ) or $this->meta_tables[ $parent_index ] = [];
				$this->add_unique( $this->meta_tables[ $parent_index ], $child_index );
			}
		}
	}

	public function register_sub_tables( array $args ) {
		if ( isset( $args['sub_tables'] ) ) {
			foreach ( $args['sub_tables'] as $sub_table_args ) {

				$sub_table_args['relationship'] = $args['relationship'];

				// todo - consider abstracting out into $this->add_sub_table();
				$sub_table_args['type'] = 'sub'; // maybe move this to table normalisation instead?
				$this->add_table( $sub_table_args );

				// register the relationship between tables
				$parent_index = $this->get_table_name_index( $args['name'] );
				$child_index = $this->get_table_name_index( $sub_table_args['name'] );

				// map child to parent
				isset( $this->sub_tables[ $child_index ] ) or $this->sub_tables[ $child_index ] = [];
				$this->add_unique( $this->sub_tables[ $child_index ], $parent_index );

				// map parent to child
				isset( $this->meta_tables[ $parent_index ] ) or $this->meta_tables[ $parent_index ] = [];
				$this->add_unique( $this->meta_tables[ $parent_index ], $child_index );
			}
		}
	}

	/**
	 * Returns entire table map
	 *
	 * @param null|string $property If provided and if property exists, returns just the value of the property instead
	 *                              of the whole map.
	 *
	 * @return array
	 */
	public function get_map( $property = null ) {
		if ( $property ) {
			return property_exists( $this, $property ) ? $this->$property : [];
		}

		return [
			'tables' => $this->tables,
			'table_names' => $this->table_names,
			'types' => $this->types,
			'post_types' => $this->post_types,
			'join_tables' => $this->join_tables,
			'sub_tables' => $this->sub_tables,
			'meta_tables' => $this->meta_tables,
			'acf_field_names' => $this->acf_field_names,
			'acf_field_keys' => $this->acf_field_keys,
			'acf_field_key_name_patterns' => $this->acf_field_key_name_patterns,
			'acf_sub_table_owners' => $this->acf_sub_table_owners,
			'acf_field_name_patterns' => $this->acf_field_name_patterns,
			'acf_field_column_types' => $this->acf_field_column_types,
			'acf_field_column_names' => $this->acf_field_column_names,
			'acf_field_column_name_patterns' => $this->acf_field_column_name_patterns,
			'nested_field_key_parents' => $this->nested_field_key_parents,
			'column_owners' => $this->column_owners,
		];
	}

	/**
	 * Accepts a multi-dimensional array containing all map keys and data. This is for use when we want to bypass
	 * parsing data. e.g; when picking up a cached map array.
	 *
	 * @param array $map
	 *
	 * @return $this
	 */
	public function set_map( array $map ) {
		$this->tables = Arr::get( $map, 'tables', [] );
		$this->table_names = Arr::get( $map, 'table_names', [] );
		$this->types = Arr::get( $map, 'types', [] );
		$this->post_types = Arr::get( $map, 'post_types', [] );
		$this->join_tables = Arr::get( $map, 'join_tables', [] );
		$this->sub_tables = Arr::get( $map, 'sub_tables', [] );
		$this->meta_tables = Arr::get( $map, 'meta_tables', [] );
		$this->acf_field_names = Arr::get( $map, 'acf_field_names', [] );
		$this->acf_field_keys = Arr::get( $map, 'acf_field_keys', [] );
		$this->acf_field_key_name_patterns = Arr::get( $map, 'acf_field_key_name_patterns', [] );
		$this->acf_sub_table_owners = Arr::get( $map, 'acf_sub_table_owners', [] );
		$this->acf_field_name_patterns = Arr::get( $map, 'acf_field_name_patterns', [] );
		$this->acf_field_column_types = Arr::get( $map, 'acf_field_column_types', [] );
		$this->acf_field_column_names = Arr::get( $map, 'acf_field_column_names', [] );
		$this->acf_field_column_name_patterns = Arr::get( $map, 'acf_field_column_name_patterns', [] );
		$this->nested_field_key_parents = Arr::get( $map, 'nested_field_key_parents', [] );
		$this->column_owners = Arr::get( $map, 'column_owners', [] );

		return $this;
	}

	/**
	 * Resets the map for rebuild
	 */
	public function reset() {
		$this->tables = [];
		$this->table_names = [];
		$this->types = [];
		$this->post_types = [];
		$this->join_tables = [];
		$this->sub_tables = [];
		$this->meta_tables = [];
		$this->acf_field_names = [];
		$this->acf_field_keys = [];
		$this->acf_field_key_name_patterns = [];
		$this->acf_sub_table_owners = [];
		$this->acf_field_name_patterns = [];
		$this->acf_field_column_types = [];
		$this->acf_field_column_names = [];
		$this->acf_field_column_name_patterns = [];
		$this->nested_field_key_parents = [];
		$this->column_owners = [];
	}

	/**
	 * @param array $args
	 *
	 * @return bool
	 */
	public function is_already_registered( array $args ) {
		return isset( $this->tables[ $args['name'] ] );
	}

	/**
	 * Adds table config args to the tables prop
	 *
	 * @param array $args
	 */
	public function register_table_config( array $args ) {
		$name = $args['name'];
		$this->tables[ $name ] = $this->is_already_registered( $args )
			? wp_parse_args( $args, $this->tables[ $name ] )
			: $args;
	}

	/**
	 * Adds the table name to the table_names prop if not already there
	 *
	 * @param array $args
	 *
	 * @return int The index of the table name
	 */
	public function register_table_name( array $args ) {
		$this->add_unique( $this->table_names, $args['name'] );

		return $this->get_table_name_index( $args['name'] );
	}

	/**
	 * Adds date to the objects prop
	 *
	 * @param array $args
	 */
	public function register_objects( array $args ) {
		$object = $args['relationship']['type'];
		isset( $this->types[ $object ] ) or $this->types[ $object ] = [];
		$table_index = $this->get_table_name_index( $args['name'] );

		$this->add_unique( $this->types[ $object ], $table_index );
	}

	/**
	 * Adds data to the post_types prop
	 *
	 * @param array $args
	 */
	public function register_post_types( array $args ) {
		$object = $args['relationship']['type'];
		$post_type = isset( $args['relationship']['post_type'] )
			? $args['relationship']['post_type']
			: '';

		if ( $object === 'post' and $post_type ) {
			isset( $this->post_types[ $post_type ] ) or $this->post_types[ $post_type ] = [];
			$this->add_unique( $this->post_types[ $post_type ], $this->get_table_name_index( $args['name'] ) );
		}
	}

	/**
	 * @param array $args
	 *
	 * @deprecated 1.1. Will be removed in version 1.2.
	 */
	public function register_acf_field_names( array $args ) {
		_deprecated_function( __METHOD__, '', '\ACFCustomDatabaseTables\Data\TableMap::register_columns' );
		$this->register_columns( $args );
	}

	/**
	 * Registers column data as needed across various class properties.
	 *
	 * @param array $args
	 */
	public function register_columns( array $args ) {
		$object = $args['relationship']['type'];

		if ( $object === 'post' ) {
			$post_type = ( isset( $args['relationship']['post_type'] ) and $args['relationship']['post_type'] )
				? $args['relationship']['post_type']
				: 'post';

			$object = "post:$post_type";
		}

		isset( $this->acf_field_names[ $object ] ) or $this->acf_field_names[ $object ] = [];

		$table_name = $args['name'];
		$table_index = $this->get_table_name_index( $table_name );

		// If this is a sub table, there will be a field in the parent table that 'owns' it. We need to make sure that
		// field name is mapped to the sub table.
		if ( isset( $args['parent']['table'], $args['parent']['field'] ) ) {
			$this->add_unique( $this->acf_field_names[ $object ][ $args['parent']['field'] ], $table_index );
			// todo - consider just mapping directly to field key here
			$this->add_unique( $this->acf_sub_table_owners[ $object ][ $args['parent']['field'] ], $table_index );
			if ( isset( $args['parent']['field_key'] ) ) {
				$this->acf_field_keys[ $object ][ $args['parent']['field_key'] ] = $args['parent']['field'];
			}
		}

		foreach ( $args['columns'] as $column_args ) {
			if ( ! isset( $column_args['map']['type'] ) ) {
				continue;
			}

			if ( $column_args['map']['type'] === 'acf_field_name' ) {
				$column_name = $column_args['name'];
				$acf_field_name = $column_args['map']['identifier'];

				$match_pattern = isset( $column_args['map']['match_pattern'] )
					? $column_args['map']['match_pattern']
					: '';

				if ( $match_pattern ) {
					$this->add_unique( $this->acf_field_name_patterns[ $object ][ $match_pattern ], $table_index );
					$this->alias_column_name_pattern( $column_name, $match_pattern, $table_name );
				} else {
					$this->add_unique( $this->acf_field_names[ $object ][ $acf_field_name ], $table_index );
				}

				$sub_field_match_patterns = isset( $column_args['map']['sub_field_match_patterns'] )
					? $column_args['map']['sub_field_match_patterns']
					: [];

				if ( $sub_field_match_patterns ) {
					foreach ( $sub_field_match_patterns as $field_key => $pattern ) {
						$this->add_unique( $this->acf_field_name_patterns[ $object ][ $pattern ], $table_index );
						$this->acf_field_key_name_patterns[ $object ][ $field_key ] = $pattern;
						// Map the sub field key against the field key of the column it is nested within.
						if ( isset( $column_args['map']['key'] ) ) {
							$this->nested_field_key_parents[ $field_key ] = $column_args['map']['key'];
						}
					}
				}

				// if col.name doesn't match col.map.identifier, map the identifier
				if ( $column_name !== $acf_field_name ) {
					$this->alias_column_name( $column_name, $acf_field_name, $table_name );
				}

				// Also track field keys against field names, if they are in the table JSON.
				if ( isset( $column_args['map']['key'] ) ) {
					if ( isset( $column_args['map']['match_pattern'] ) ) {
						$this->acf_field_key_name_patterns[ $object ][ $column_args['map']['key'] ] = $column_args['map']['match_pattern'];
					} else {
						$this->acf_field_keys[ $object ][ $column_args['map']['key'] ] = $acf_field_name;
					}
					$this->column_owners[ $object ][ $column_args['map']['key'] ] = "$table_index.$column_name";
				}

				// Track the field column data types.
				$this->acf_field_column_types[ $table_name ][ $column_name ] = isset( $column_args['type'] )
					? $column_args['type']
					: 'longtext';
			}
		}
	}

	/**
	 * Define an alternative column name for a field name. This will allow us to start aliasing column names when we
	 * open up the at functionality. At present, this is used to map sub-table table_name.complex_0_field_3_names to
	 * actual column names on the sub-table.
	 *
	 * @param $acf_field_name
	 * @param $table_name
	 * @param $column_name
	 */
	private function alias_column_name( $column_name, $acf_field_name, $table_name ) {
		$key = $this->build_acf_field_column_name_key( $table_name, $acf_field_name );

		$this->acf_field_column_names[ $key ] = $column_name;
	}

	private function alias_column_name_pattern( $column_name, $acf_field_name, $table_name ) {
		$key = $this->build_acf_field_column_name_key( $table_name, $acf_field_name );

		$this->acf_field_column_name_patterns[ $key ] = $column_name;
	}

	/**
	 * @param array $indexes
	 *
	 * @return array
	 */
	private function get_all_table_names_for_index_array( array $indexes ) {
		$table_names = [];

		foreach ( $indexes as $index ) {
			if ( isset( $this->table_names[ $index ] ) ) {
				$table_names[] = $this->table_names[ $index ];
			}
		}

		return $table_names;
	}

	/**
	 * @param string $post_type
	 *
	 * @return array table names Table names without the $wpdb->prefix
	 */
	public function locate_all_tables_by_post_type( $post_type ) {
		return isset( $this->post_types[ $post_type ] )
			? $this->get_all_table_names_for_index_array( $this->post_types[ $post_type ] )
			: [];
	}

	/**
	 * @return array
	 */
	public function locate_all_user_tables() {
		return isset( $this->types['user'] )
			? $this->get_all_table_names_for_index_array( $this->types['user'] )
			: [];

	}

	/**
	 * Get the array of table indexes registered against a field name.
	 *
	 * @param string $field_name
	 * @param string $context Must be normalised or it won't match
	 *
	 * @return array
	 */
	private function get_table_indexes_for_field_name( $field_name, $context ) {
		return isset( $this->acf_field_names[ $context ][ $field_name ] )
			? $this->acf_field_names[ $context ][ $field_name ]
			: [];
	}

	/**
	 * Finds the ACF field key for the given field name and context.
	 *
	 * @param string $acf_field_name
	 * @param string $context This should be in the form of `post:{post_type}` or `user`.
	 *
	 * @return string Either a field key (if found) or an empty string.
	 *
	 * @see \ACFCustomDatabaseTables\Service\ACFLocalReferenceFallback::_pre_load_field_key_reference_from_map()
	 */
	public function locate_acf_field_key_by_acf_field_name( $acf_field_name, $context ) {
		$context = $this->normalise_context( $context );

		if ( isset( $this->acf_field_keys[ $context ] ) ) {
			if ( false !== $key = array_search( $acf_field_name, $this->acf_field_keys[ $context ] ) ) {
				return $key;
			}
		}

		if ( isset( $this->acf_field_key_name_patterns[ $context ] ) ) {
			foreach ( $this->acf_field_key_name_patterns[ $context ] as $key => $pattern ) {
				if ( preg_match( "/^$pattern$/", $acf_field_name ) ) {
					return $key;
				}
			}
		}

		return '';
	}

	/**
	 * @param ACFFieldBase $field
	 * @param ACFSelector $selector
	 *
	 * @return string
	 */
	public function get_field_name_pattern( ACFFieldBase $field, ACFSelector $selector ) {
		return Arr::get_deep(
			$this->acf_field_key_name_patterns,
			$selector->context() . '.' . $field->key(),
			''
		);
	}

	private function get_schema_data( ACFFieldBase $field, ACFSelector $selector ) {
		// Get the root field key to look up (root field keys have columns).
		$field_key = $this->get_root_field_key( $field, $selector );
		$table_col_string = Arr::get_deep( $this->column_owners, [ $selector->context(), $field_key ], '' );
		/*
		 * 0: table index
		 * 1: column name
		 */
		$parts = explode( '.', $table_col_string );
		$index = Arr::get( $parts, 0, '' );

		return [
			'table_index' => $index,
			'table_name' => Arr::get( $this->table_names, $index, '' ),
			'column_name' => Arr::get( $parts, 1, '' ),
		];
	}

	/**
	 * Get the column name for a given field in a given context.
	 *
	 * @param ACFFieldBase $field
	 * @param ACFSelector $selector
	 *
	 * @return string
	 */
	public function get_column_name( ACFFieldBase $field, ACFSelector $selector ) {
		$column_name = Arr::get( $this->get_schema_data( $field, $selector ), 'column_name', '' );
		if ( $column_name ) {
			return $column_name;
		}

		if ( $table_name = $this->get_table_name( $field, $selector ) ) {
			return $this->locate_column_name_by_acf_field_name( $table_name, $field, $selector );
		}

		return '';
	}

	/**
	 * Finds the column name for an ACF field name in a given table, if an alternative column name has been mapped. If
	 * no mapping can be found, the field name is returned as that will be the name of the column.
	 *
	 * @param $table_name
	 * @param ACFFieldBase $field
	 * @param ACFSelector $selector
	 *
	 * @return mixed
	 * @deprecated in version 1.1. Moving to object-oriented lookups in here.
	 */
	private function locate_column_name_by_acf_field_name( $table_name, ACFFieldBase $field, ACFSelector $selector ) {
		$key = $this->build_acf_field_column_name_key( $table_name, $field->name() );

		// Note: this is infrastructure to support column name aliasing. Not in use yet but it will be.
		if ( isset( $this->acf_field_column_names[ $key ] ) ) {
			return $this->acf_field_column_names[ $key ];
		}

		if ( $pattern = $this->get_field_name_pattern( $field, $selector ) ) {
			$key = $this->build_acf_field_column_name_key( $table_name, $pattern );
			if ( $column_name = Arr::get( $this->acf_field_column_name_patterns, $key ) ) {
				return $column_name;
			}
		}

		// no mapping? Just return the field name
		return $field->name();
	}

	/**
	 * Get the table name for a given field in a given context.
	 *
	 * @param ACFFieldBase $field
	 * @param ACFSelector $selector
	 *
	 * @return string|false
	 */
	public function get_table_name( ACFFieldBase $field, ACFSelector $selector ) {
		$table_name = Arr::get( $this->get_schema_data( $field, $selector ), 'table_name', '' );
		if ( $table_name ) {
			return $table_name;
		}

		// first attempt to locate using get_table_indexes_for_field_name()
		$indexes = $this->get_table_indexes_for_field_name( $field->name(), $selector->context() );

		// If no index available for the field name, try looking up a pattern for the field key and checking for indexes
		// against the pattern.
		if ( empty( $indexes ) ) {
			$pattern = $this->get_field_name_pattern( $field, $selector );
			if ( $pattern ) {
				$indexes = Arr::get_deep( $this->acf_field_name_patterns, [ $selector->context(), $pattern ], [] );
			}
		}

		if ( ! isset( $indexes[0] ) ) {
			return false;
		}

		return Arr::get( $this->table_names, $indexes[0], false );
	}

	public function has_table( ACFFieldBase $field, ACFSelector $selector ) {
		return (bool) $this->get_table_name( $field, $selector );
	}

	/**
	 * Determine the table name for the given field and context and return the data type of the column that stores the
	 * field data.
	 *
	 * @param ACFFieldBase $field
	 * @param ACFSelector $selector
	 *
	 * @return mixed|string
	 */
	public function get_field_column_type( ACFFieldBase $field, ACFSelector $selector ) {
		$default = 'longtext';

		if (
			! $table_name = $this->get_table_name( $field, $selector ) or
			! $column_name = $this->get_column_name( $field, $selector )
		) {
			return $default;
		}

		return Arr::get_deep( $this->acf_field_column_types, [ $table_name, $column_name ], $default );
	}

	private function build_acf_field_column_name_key( $table_name, $acf_field_name ) {
		return "{$table_name}.{$acf_field_name}";
	}

	/**
	 * Check if a table (by name) is a join table.
	 *
	 * @param $table_name
	 *
	 * @return bool
	 */
	public function is_join_table( $table_name ) {
		if ( is_wp_error( $index = $this->get_table_name_index( $table_name ) ) ) {
			return false;
		}

		return isset( $this->join_tables[ $index ] );
	}

	/**
	 * Check if a table (by name) is a sub table.
	 *
	 * @param $table_name
	 *
	 * @return bool
	 */
	public function is_sub_table( $table_name ) {
		if ( is_wp_error( $index = $this->get_table_name_index( $table_name ) ) ) {
			return false;
		}

		return isset( $this->sub_tables[ $index ] );
	}

	/**
	 * Check whether a field name owns a sub table. This would be the case where a complex field is not represented by
	 * a column in a meta table but instead creates a table of its own for storing sub fields.
	 *
	 * @param ACFFieldBase $field
	 * @param ACFSelector $selector
	 *
	 * @return bool
	 */
	public function field_is_sub_table_owner( ACFFieldBase $field, ACFSelector $selector ) {
		// This may actually come in as a key ref or a field name so if it is a key, get the field name as that is what
		// is currently being used to determine sub table owners.
		$field_name = isset( $this->acf_field_keys[ $selector->context() ][ $field->key() ] )
			? $this->acf_field_keys[ $selector->context() ][ $field->key() ]
			: $field->name();

		return isset( $this->acf_sub_table_owners[ $selector->context() ][ $field_name ] );
	}

	/**
	 * Check if the field has its own column. Use this to differentiate between fields that have a column and fields
	 * that are nested within a column.
	 *
	 * @param ACFFieldBase $field
	 * @param ACFSelector $selector
	 *
	 * @return bool
	 */
	public function field_is_column_owner( ACFFieldBase $field, ACFSelector $selector ) {
		return isset( $this->column_owners[ $selector->context() ][ $field->key() ] );
	}

	public function field_is_nested_within_a_column( ACFFieldBase $field, ACFSelector $selector ) {
		return isset( $this->nested_field_key_parents[ $field->key() ] );
	}

	/**
	 * If a field is nested within a column, get the root column field key. If not, return the given field's key.
	 *
	 * @param ACFFieldBase $field
	 * @param ACFSelector $selector
	 *
	 * @return string|null
	 */
	public function get_root_field_key( ACFFieldBase $field, ACFSelector $selector ) {
		return Arr::get( $this->nested_field_key_parents, $field->key(), $field->key() );
	}

	/**
	 * Normalise the object context. This facilitates the use of short-hand context for the core 'post' and 'page' object
	 * types.
	 *
	 * @param $context
	 *
	 * @return string
	 */
	private function normalise_context( $context ) {
		if ( $context === 'post' ) {
			$context = 'post:post';
		} elseif ( $context === 'page' ) {
			$context = 'post:page';
		}

		return $context;
	}

	private function add_unique( &$array, $value ) {
		$array = Arr::add_unique( (array) $array, $value );
	}

}