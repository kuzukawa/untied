<?php

namespace ACFCustomDatabaseTables\DB;

use ACFCustomDatabaseTables\Utils\Arr;

abstract class DynamicTableBase extends ModelBase {

	const MAX_INDEX_LENGTH = 191;
	const TYPE_META = 'meta';
	const TYPE_JOIN = 'join';
	const TYPE_SUB = 'sub';

	/** @var string */
	protected $schema;

	/** @var string */
	protected $name;

	/** @var array */
	protected $primary_key = [];

	/** @var array */
	protected $keys = [];

	/** @var string[] */
	protected $column_defaults = [];

	/** @var string[] */
	protected $columns = [];

	/** @var string */
	protected $object_relationship_key = '';

	/** @var DynamicColumnBase[] */
	protected $dynamic_columns = [];

	/**
	 * Returns the table type
	 *
	 * @return string
	 */
	abstract function type();

	/**
	 * DynamicTableBase constructor.
	 *
	 * @param string $name
	 */
	public function __construct( $name = '' ) {
		parent::__construct();
		$this->set_table_name( $name );
	}

	/**
	 * Table 'type' checks
	 *
	 * @return bool
	 */
	function is_join_table() {
		return $this->type() === 'join';
	}

	/**
	 * Table 'type' checks
	 *
	 * @return bool
	 */
	function is_sub_table() {
		return $this->type() === 'sub';
	}

	/**
	 * Table 'type' checks
	 *
	 * @return bool
	 */
	function is_meta_table() {
		return $this->type() === 'meta';
	}

	/**
	 * Must return the schema for this table (CREATE TABLE SQL Statement)
	 *
	 * @return mixed
	 */
	function schema() {

		// tables without columns don't generate schema
		if ( ! $this->dynamic_columns ) {
			return '';
		}

		if ( ! $this->schema ) {
			$this->build_schema();
		}

		return $this->schema;
	}

	/**
	 * todo optional timestamps
	 *
	 * Builds the table schema
	 */
	function build_schema() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$n_cols = count( $this->dynamic_columns );
		$i = 0;

		$schema = "CREATE TABLE `{$this->full_table_name()}` (" . PHP_EOL;

		foreach ( $this->dynamic_columns as $column ) {
			$i ++;
			$schema .= $column->schema();
			$schema .= ( $i < $n_cols || $this->primary_key() )
				? ',' . PHP_EOL
				: '';
		}

		$schema .= $this->primary_key_schema();
		$schema .= ( $key_schema = $this->key_schema() )
			? ',' . PHP_EOL . $key_schema
			: '';
		$schema .= PHP_EOL;
		$schema .= ") $charset_collate;";

		$this->schema = $schema;
	}

	/**
	 * Checks whether a column needs a max index length defined in key schema
	 *
	 * @param $column_name
	 *
	 * @return bool
	 */
	function key_needs_max_index_length( $column_name ) {
		return (
			isset( $this->dynamic_columns[ $column_name ] )
			and $this->dynamic_columns[ $column_name ]->format() === '%s'
		);
	}

	/**
	 * Prepares a column name for use in a key by back ticking it and, if it requires it, appending the max index length
	 *
	 * @param $column_name
	 *
	 * @return string
	 */
	function key_column_schema( $column_name ) {

		$max_index_length = self::MAX_INDEX_LENGTH;
		$schema = "`{$column_name}`";

		if ( $this->key_needs_max_index_length( $column_name ) ) {
			$schema .= "({$max_index_length})";
		}

		return $schema;
	}

	/**
	 * Builds the primary key schema
	 *
	 * e.g; PRIMARY KEY (`int_col`,`str_col`(191))
	 *
	 * @return string
	 */
	function primary_key_schema() {

		$schema = '';

		if ( $primary_key = $this->primary_key() ) {
			$primary_key = array_map( [ $this, 'key_column_schema' ], $primary_key );
			$key_string = implode( ',', $primary_key );
			$schema .= "PRIMARY KEY ({$key_string})";
		}

		return $schema;
	}

	/**
	 * Builds the schema for any number of non-primary keys
	 *
	 * e.g;
	 *      KEY `key_name` (`int_column`),
	 *      KEY `key_name` (`str_column`(191)),
	 *      KEY `key_name` (`int_column`,`str_column`(191)),
	 *      UNIQUE KEY `key_name` (`int_column`,`str_column`(191))
	 *
	 * @return string
	 */
	function key_schema() {

		$schema = '';

		if ( $keys = $this->keys() ) {

			$i = 0;
			$n_keys = count( $keys );

			foreach ( $keys as $key ) {

				$i ++;
				$cols = array_map( [ $this, 'key_column_schema' ], (array) $key['columns'] );
				$cols_schema = implode( ',', $cols );

				$schema .= ( isset( $key['unique'] ) and $key['unique'] )
					? 'UNIQUE '
					: '';
				$schema .= "KEY `{$key['name']}` ({$cols_schema})";
				$schema .= ( $i < $n_keys )
					? ',' . PHP_EOL
					: '';
			}
		}

		return $schema;
	}

	/**
	 * Must return the table name without the prefix
	 *
	 * @return string
	 */
	function table_name() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	function full_table_name() {
		global $wpdb;

		return $wpdb->prefix . $this->name;
	}

	/**
	 * Returns an associative array of columns and their formats. e.g; ['col_name' => '%s', 'col2_name' => '%d']
	 *
	 * @return array
	 */
	function columns() {
		$columns = [];
		foreach ( $this->dynamic_columns as $column ) {
			$columns[ $column->name() ] = $column->format();
		}

		return $columns;
	}

	/**
	 * If columns have defaults, the required format here is ['col_name' => 'default_val']
	 *
	 * Note: if using the timestamp_field() method to generate a timestamp field in your model's schema, it's not
	 * necessary to set default values for that particular field.
	 *
	 * @return array
	 */
	function column_defaults() {
		// Defaults are currently not supported
		return [];
	}

	/**
	 * Returns an array containing the primary key. Multiple columns are supported for composite keys, but should be
	 * in order of their composition to ensure optimal query performance.
	 *
	 * @return array
	 */
	function primary_key() {
		return $this->primary_key;
	}

	/**
	 * @return array
	 */
	function keys() {
		return $this->keys;
	}

	/**
	 * Dynamic table name setter. If a table name is handed in already prefixed with the current DB's table prefix,
	 * this method strips the prefix as the parent object already accounts for prefixing based on WPDBs settings.
	 *
	 * @param $table_name
	 */
	function set_table_name( $table_name ) {
		global $wpdb;

		$prefix = $wpdb->prefix;

		if ( strpos( $table_name, $prefix ) === 0 ) {
			$table_name = substr( $table_name, strlen( $prefix ) );
		}

		$this->name = $table_name;
	}

	/**
	 * Sets the primary key on the table. Always requires an array. Can be a composite key – just pass in multiple
	 * column names.
	 *
	 * @param array $key
	 */
	function set_primary_key( array $key ) {
		$this->primary_key = $key;
	}

	/**
	 * @param array $keys
	 */
	function set_keys( array $keys ) {
		$this->keys = $keys;
	}

	/**
	 * @param $key
	 */
	function set_object_relationship_key( $key ) {
		$this->object_relationship_key = $key;
	}

	/**
	 * @return string
	 */
	function object_relationship_key() {
		return $this->object_relationship_key;
	}

	/**
	 * @param DynamicColumnBase $column
	 */
	function add_dynamic_column( DynamicColumnBase $column ) {

		$this->columns[ $column->name() ] = $column->format();

		// Defaults are currently not supported
		//if ( $column->has_default_value() ) {
		//	$this->column_defaults[ $column->name() ] = $column->default_value();
		//}

		$this->dynamic_columns[ $column->name() ] = $column;

	}

	/**
	 * App-specific update method. Use this in intercepts and override this in child classes
	 *
	 * @param string $column_name The column name
	 * @param string $value
	 * @param $object_id
	 * @param array $where Irrelevant to this base functionality but essential for sub table support. Not the ideal way
	 * to place this but this will have to do for now.
	 *
	 * @return bool
	 */
	public function update_value( $column_name, $value, $object_id, array $where = [] ) {

		$obj_key = $this->object_relationship_key();

		$bool = $this->insert_or_update( [
			$obj_key => $object_id,
			$column_name => $value
		] );

		return $bool;
	}

	/**
	 * @param string $column_name
	 * @param string $context
	 * @param int $object_id
	 * @param array $where This is redundant on this base object but useful to polymorphs.
	 *
	 * @return array|bool|null
	 */
	public function find_value( $column_name, $context, $object_id, array $where = [] ) {

		$row = $this->_find_value( $column_name, $context, $object_id );

		if ( count( $row ) === 1 ) {
			$row = $row[0];
		}

		return $row;
	}

	/**
	 * @param $key
	 * @param $context
	 * @param $object_id
	 *
	 * @return array|bool|null
	 */
	protected function _find_value( $key, $context, $object_id ) {
		$obj_key = $this->object_relationship_key();

		$row = $this->find_where( [ $obj_key => $object_id ] );

		if ( $row === false ) {
			return null;
		}

		return $row;
	}

	/**
	 * Our table utility has a slight bug that causes problems here with the return result. As a stop gap,
	 * we're going to access the last_result prop of $wpdb directly until we can get this issue fixed.
	 *
	 * @see https://github.com/mishterk/wp.tools.db/issues/14
	 *
	 * @param array $row
	 *
	 * @return bool
	 */
	public function insert_or_update( array $row ) {
		global $wpdb;

		$row = $this->normalise_row( $row );
		$object_key = $this->object_relationship_key();
		$object_id = $row[ $object_key ];

		$existing = $this->find_where( [
			$object_key => $object_id
		] );

		if ( $existing ) {
			$this->update( $row, [ $object_key => $object_id ] );
		} else {
			$this->insert( $row );
		}

		$bool = ( false !== $wpdb->last_result );

		return $bool;
	}

	/**
	 * Custom handling for ACFCDT so that we can avoid using 'ON DUPLICATE KEY UPDATE', which causes some issues.
	 *
	 * As per @param array $rows
	 *
	 * @param bool $validate This was added by us in order to bypass key validation where it causes problems.
	 *
	 * @return bool|mixed
	 * @see insert_rows(), only this method will update any records that are already stored in the table. Use
	 * this only when necessary, as the insert_rows() method has less to do and will, therefore, be a more efficient
	 * option when you know you are dealing with new data.
	 *
	 */
	public function insert_or_update_rows( array $rows, $validate = false ) {
		global $wpdb;

		$rows = $this->normalise_rows( $rows );
		if ( $validate and ! $this->validate_rows( $rows ) ) {
			return $this->handle_error( '', 'Rows could not be inserted due to validation error' );
		}

		$object_key = $this->object_relationship_key();
		$object_id = $rows[0][ $object_key ];

		$query = $wpdb->prepare( "SELECT `id` FROM `{$this->full_table_name()}` WHERE `{$object_key}` = '%s';", $object_id );
		$existing = $wpdb->get_results( $query );
		$existing_ids = Arr::array_column( $existing, 'id' );

		$rows_to_insert = [];
		$bool = false;

		foreach ( $rows as $row ) {
			if ( in_array( $row['id'], $existing_ids ) ) {
				$row_id = $row['id'];
				$object_id = $row[ $object_key ];
				unset( $row['id'] );
				unset( $row[ $object_key ] );
				$this->update( $row, [ 'id' => $row_id, $object_key => $object_id ] );
				$bool = true; // not ideal, as it isn't catching potential update failures
			} else {
				$rows_to_insert[] = $row;
			}
		}

		if ( $rows_to_insert ) {
			$bool = $this->insert_rows( $rows_to_insert );
		}

		return $bool;
	}

	/**
	 * Custom handling for ACFCDT so that we can avoid using 'ON DUPLICATE KEY UPDATE', which causes some issues.
	 *
	 * Inserts multiple rows based on a consistent multi-dimensional array (an array of rows). The data provided needs
	 * to be structured consistently; that is, each row needs to have the same number of items with the keys in the same
	 * order. Each row also needs to contain the primary key (single or composite) and cannot contain key duplicates.
	 *
	 * @param array $rows
	 *
	 * @param bool $validate This was added by us in order to bypass key validation where it causes problems.
	 *
	 * @return bool
	 */
	public function insert_rows( array $rows, $validate = false ) {
		global $wpdb;

		$rows = $this->normalise_rows( $rows );
		if ( $validate and ! $this->validate_rows( $rows ) ) {
			return $this->handle_error( '', 'Rows could not be inserted due to validation error' );
		}

		$formats = $this->get_ordered_formats( $rows[0] );
		$fields = array_keys( $rows[0] );
		$fields_str = $this->prepare_fields_string( $fields );
		$n_rows = count( $rows );
		$n_fields = count( $rows[0] );
		$formats_str = implode( ',', array_slice( $formats, 0, $n_fields ) );

		$SQL = "INSERT INTO `{$this->full_table_name()}` ($fields_str) VALUES";

		$c = 0;
		foreach ( $rows as $row ) {
			$c ++;
			$SQL .= $wpdb->prepare( " ($formats_str)", $row );
			$SQL .= ( $n_rows === $c ? ';' : ',' );
		}

		$wpdb->query( $SQL );
		$bool = $wpdb->last_result;

		return $bool !== false;
	}

	/**
	 * Overriding this one so that we can add an orderby param
	 *
	 * Finds multiple rows based on provided associative array
	 *
	 * @param array $args
	 * @param int $limit
	 * @param int $offset
	 *
	 * ADDED PARAMS – perhaps build these into core lib and adjust accordingly
	 * @param null $order_by
	 * @param string $order
	 *
	 * @return array|bool
	 */
	public function find_where( array $args, $limit = 0, $offset = 0, $order_by = null, $order = 'ASC' ) {
		global $wpdb;

		if ( ! $this->is_associative_array( $args ) ) {
			return $this->handle_error( '', __METHOD__ . ' did not run due to $args variable not being an associative array' );
		}

		$where = $this->build_where_clause( $args );
		$query = "SELECT * FROM `{$this->full_table_name()}` $where";

		if ( $order_by ) {
			$order_by = esc_sql( $order_by );
			$order = esc_sql( $order );
			$query .= " ORDER BY `{$order_by}` {$order}";
		}

		if ( $limit > 0 ) {
			$query .= $wpdb->prepare( " LIMIT %d", $limit );
		}

		if ( $offset > 0 ) {
			$query .= $wpdb->prepare( " OFFSET %d", $offset );
		}

		$query .= ';';

		return $wpdb->get_results( $query, ARRAY_A ) ?: [];
	}

	/**
	 * @return array
	 */
	public function create_or_update_table() {

		/**
		 * Fires immediately before a table schema is passed to dbDelta(). We are using this internally to provide
		 * support for post table update actions in \ACFCustomDatabaseTables\Module\AfterTableSchemaUpdateModule.
		 *
		 * WARNING: this is an internal action and is subject to change without warning or deprecation. Using this
		 *  could break your application.
		 */
		do_action( 'acfcdt/internal/before_create_or_update_table', $this );

		$db_delta_results = parent::create_table();

		/**
		 * Fires immediately after a table schema is passed to dbDelta(). We are using this internally to provide
		 * support for running custom SQL queries or other processes immediately after a table is created/updated.
		 *
		 * WARNING: this is an internal action and is subject to change without warning or deprecation. Using this
		 *  could break your application.
		 */
		$db_delta_results = apply_filters( 'acfcdt/internal/filter_db_delta_results_after_create_or_update_table', $db_delta_results, $this );

		return $db_delta_results;
	}

	/**
	 * This is just an alias to ensure we don't accidentally call create table on the parent class as doing so would
	 * bypass the 'acfcdt/before_create_or_update_table' filter.
	 *
	 * @see \ACFCustomDatabaseTables\DB\DynamicTableBase::create_or_update_table()
	 */
	public function create_table() {
		return $this->create_or_update_table();
	}

}