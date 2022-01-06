<?php

namespace ACFCustomDatabaseTables\FileIO;

use ACFCustomDatabaseTables\Data\TableValidator;
use ACFCustomDatabaseTables\Factory\ACFFieldFactory;
use ACFCustomDatabaseTables\Model\ACFFieldGroup;
use ACFCustomDatabaseTables\Service\ACFFieldSupportManager;
use ACFCustomDatabaseTables\Service\TableNameValidator;
use ACFCustomDatabaseTables\Settings;
use ACFCustomDatabaseTables\Utils\Arr;
use ACFCustomDatabaseTables\Utils\Error;
use DateTime;
use DateTimeZone;
use Exception;
use WP_Error;

/**
 * Class TableJSONFileGenerator
 * @package ACFCustomDatabaseTables\FileIO
 *
 * todo: things grew pretty quick here, so this whole class could use some refactoring at some point
 */
class TableJSONFileGenerator {

	/** @var Settings */
	private $settings;

	/** @var TableNameValidator */
	private $table_name_validator;

	/** @var TableValidator */
	private $table_validator;

	/** @var ACFFieldGroup */
	private $field_group;

	/** @var ACFFieldSupportManager */
	private $field_support_manager;

	/** @var ACFFieldFactory */
	private $field_factory;

	/**
	 * TableJSONFileGenerator constructor.
	 *
	 * @param Settings $settings
	 * @param TableNameValidator $table_name_validator
	 * @param TableValidator $table_validator
	 * @param ACFFieldSupportManager $field_support_manager
	 * @param ACFFieldFactory $field_factory
	 */
	public function __construct(
		Settings $settings,
		TableNameValidator $table_name_validator,
		TableValidator $table_validator,
		ACFFieldSupportManager $field_support_manager,
		ACFFieldFactory $field_factory
	) {
		$this->settings = $settings;
		$this->table_name_validator = $table_name_validator;
		$this->table_validator = $table_validator;
		$this->field_support_manager = $field_support_manager;
		$this->field_factory = $field_factory;
	}

	/**
	 * @param ACFFieldGroup $field_group
	 */
	public function set_field_group( ACFFieldGroup $field_group ) {
		$this->field_group = $field_group;
	}

	/**
	 * @param ACFFieldGroup $field_group
	 *
	 * @return array|WP_Error
	 */
	public function generate_from_field_group( ACFFieldGroup $field_group ) {
		$this->set_field_group( $field_group );

		return $this->generate();
	}

	/**
	 * @return array|WP_Error Returns the table definition array on success, WP_Error on failure
	 */
	public function generate() {

		if ( ! $this->field_group ) {
			return new WP_Error( 'acfcdt', 'ACFFieldGroup object not set.' );
		}

		if ( is_wp_error( $validate = $this->table_name_validator->validate( $this->field_group->table_name() ) ) ) {
			return $validate;
		}

		if ( ! $this->field_group->fields() ) {
			return new WP_Error( 'acfcdt', 'Field group has no fields – no definition file could be created.' );
		}

		if ( ! $this->get_supported_fields( $this->field_group ) ) {
			return new WP_Error(
				'acfcdt',
				sprintf(
					'None of the fields in this field group are supported for custom database table use at this time. Support for some complex fields can be enabled in <strong>Custom Fields > Database Tables > </strong><a href="%s">Settings</a>',
					admin_url( 'edit.php?post_type=acf-field-group&page=acf-custom-database-tables&acfcdt-section=settings' )
				)
			);
		}

		if ( ! $relationship = $this->get_relationship( $this->field_group ) ) {
			return new WP_Error( 'acfcdt', 'Could not establish a relationship for the custom database tables – the field group needs to have either a <strong>post type</strong> or <strong>user</strong> in its location rules.' );
		}

		if ( ! file_exists( $dir = $this->settings->get( 'json_dir' ) ) ) {
			if ( false === wp_mkdir_p( $dir ) ) {
				return new WP_Error( 'acfcdt', 'Custom database tables JSON save directory could not be created. Try creating the directory manually then running the process again. Directory: ' . $dir );
			}
		}

		if ( ! is_writable( $dir = $this->settings->get( 'json_dir' ) ) ) {
			return new WP_Error( 'acfcdt', 'Custom database tables JSON save directory is not writable. Directory: ' . $dir );
		} else {
			$this->harden_table_definition_directory();
		}

		$table_name = $this->field_group->table_name();
		$join_tables = $this->build_join_tables_array( $this->field_group );
		$sub_tables = $this->build_sub_tables_array( $this->field_group );
		$columns = $this->build_columns_array( $this->field_group );

		// todo - keygen doesn't really belong here. Better to move this to the table normalisation process.
		$object_key = $this->table_validator->get_object_relationship_key_name( [ 'relationship' => $relationship ] );
		array_unshift( $columns, [
			'name' => $object_key,
			'format' => '%d',
			'type' => 'bigint(20)',
		] );

		array_unshift( $columns, [
			'name' => 'id',
			'format' => '%d',
			'type' => 'bigint(20)',
			'null' => false,
			'auto_increment' => true,
			'unsigned' => true,
		] );

		$definition = [
			'name' => $table_name,
			'relationship' => $relationship,
			'primary_key' => [
				'id'
			],
			'keys' => [
				[
					'name' => $object_key,
					'columns' => [
						$object_key
					],
					'unique' => true,
				]
			],
			'columns' => $columns,
		];

		if ( $join_tables ) {
			$definition['join_tables'] = $join_tables;
		}

		if ( $sub_tables ) {
			$definition['sub_tables'] = $sub_tables;
		}

		/**
		 * Taking a snapshot of the definition so that we can, if we need to, prevent future edits to a field group
		 * that may have been manually adjusted. This isn't a feature right now, so saving a field group will definitely
		 * overwrite any manual edits to a table definition JSON file, but this gives us something to compare to if/when
		 * we start to do this.
		 */
		$definition['hash'] = md5( json_encode( $definition ) );
		$definition['modified'] = $this->generate_modified_time();

		return $this->write_to_file( $this->get_file_path( $this->field_group ), $definition );
	}

	/**
	 * Method creates two files to help prevent people accessing table definitions:
	 *
	 * 1. empty index.php file
	 * 2. .htaccess file
	 */
	public function harden_table_definition_directory() {

		$dir = $this->settings->get( 'json_dir' );

		if ( ! file_exists( $index_file = $dir . '/index.php' ) ) {
			if ( false === ( fclose( fopen( $index_file, 'w' ) ) ) ) {
				Error::log( 'Could not create empty index.php file. You should create the file manually. File: ' . $index_file );
			} else {
				if ( false === file_put_contents( $index_file, "<?php // silence" ) ) {
					Error::log( 'Could not write to index.php file. File: ' . $index_file );
				}
			}
		}

		if ( ! file_exists( $htaccess_file = $dir . '/.htaccess' ) ) {
			if ( false === ( fclose( fopen( $htaccess_file, 'w' ) ) ) ) {
				Error::log( 'Could not create .htaccess file. You should create the file manually. File: ' . $htaccess_file );
			} else {
				if ( false === file_put_contents( $htaccess_file, "Options -Indexes\r\nDeny from all" ) ) {
					Error::log( 'Could not write to .htaccess file. File: ' . $htaccess_file );
				}
			}
		}

	}

	/**
	 * Writes an array as encoded JSON to a given file. If the file doesn't already exist, this will attempt to create
	 * it.
	 *
	 * @param string $file The path and name of a file to write to
	 * @param array $definition_array
	 *
	 * @return array|WP_Error
	 */
	public function write_to_file( $file, $definition_array ) {

		$file_exists = file_exists( $file );

		if ( ! $file_exists ) {
			if ( false === ( fclose( fopen( $file, 'w' ) ) ) ) {
				return new WP_Error( 'acfcdt', "Could not create table definition JSON file for field group. File: $file" );
			}
		}

		if ( false === file_put_contents( $file, acf_json_encode( $definition_array ), true ) ) {
			return new WP_Error( 'acfcdt', "There was a problem writing to the table definition JSON file. File: $file" );
		}

		return [
			'action' => $file_exists ? 'updated' : 'created',
			'definition' => $definition_array,
			'file' => $file
		];
	}

	/**
	 * @param ACFFieldGroup $field_group
	 *
	 * @return string
	 */
	public function get_file_path( ACFFieldGroup $field_group ) {

		$save_dir = untrailingslashit( $this->settings->get( 'json_dir' ) );
		$file_name = sanitize_file_name( $field_group->definition_file_name() );
		$path_info = pathinfo( $file_name );

		if ( isset( $path_info['extension'] ) and $path_info['extension'] ) {
			$file_name = str_replace( '.' . $path_info['extension'], '', $file_name );
		}

		return "$save_dir/$file_name.json";
	}

	/**
	 * Returns a relationship array for use in the definition file. This currently just finds the first post-type/user
	 * that it can and returns a relationship for that, as we are only currently supporting one object relationship per
	 * table at this time. Support for more objects will come.
	 *
	 * @param ACFFieldGroup $field_group
	 *
	 * @return array
	 */
	public function get_relationship( ACFFieldGroup $field_group ) {
		if ( $location_rules = $field_group->get_setting( 'location' ) ) {

			$flattened_rules = call_user_func_array( 'array_merge', $location_rules );

			$flattened_rules = array_filter( $flattened_rules, function ( $rule ) {
				return $rule['operator'] === '==';
			} );

			// find first post type or user in rule set and match that. later, we'll support more complex relationships to multiple objects
			foreach ( $flattened_rules as $rule ) {
				if ( $rule['param'] === 'post_type' ) {

					return [ 'type' => 'post', 'post_type' => $rule['value'] ];

				} elseif ( in_array(
					$rule['param'], [
						'user_form',
						//'user_role',
						//'current_user',
						//'current_user_role'
					]
				) ) {
					return [ 'type' => 'user' ];
				}
			}
		}

		return [];
	}

	/**
	 * @param ACFFieldGroup $field_group
	 *
	 * @return array
	 */
	public function get_supported_fields( ACFFieldGroup $field_group ) {

		$supported_fields = ( $fields = $field_group->fields() )
			? array_filter( $fields, [ $this->field_support_manager, 'is_supported' ] )
			: [];

		return apply_filters( 'acfcdt/field_group_supported_fields', $supported_fields, $field_group->table_name() );
	}

	/**
	 * @param $fields array of ACF field arrays (field group's fields array)
	 *
	 * @return array
	 */
	public function extract_field_names( $fields ) {
		$names = $tables = array_map( function ( $field ) {
			return $field['name'];
		}, $fields );

		return array_values( $names );
	}

	/**
	 * @param ACFFieldGroup $field_group
	 *
	 * @return array
	 */
	public function extract_join_fields( ACFFieldGroup $field_group ) {
		return array_filter( $field_group->fields(), function ( $field ) {

			if ( ! $this->field_support_manager->field_eligible_for_join_table( $field ) ) {
				return false;
			}

			$table_name = $this->field_group->table_name();
			$create_join_table = $this->settings->get( 'enable_join_tables_globally' );

			/**
			 * Filter this and return TRUE to enable the addition of join table definitions in the generated table
			 * definition JSON. You can do this for certain fields, field types, or even entire tables using the
			 * available args.
			 *
			 * IF you just return TRUE, all eligible fields will create join tables on the next schema update.
			 * IF you just return FALSE on this filter, no join table definitions will be added to any JSON files on
			 * the next schema update.
			 *
			 * IF you already have join tables in place and you disable some using this filter, the schema will need
			 * to be updated before ACF Custom Database Tables will understand the change.
			 *
			 * Note: you can't use this to activate join tables on fields that don't currently support that, as this
			 * filter only runs on eligible fields.
			 */
			return apply_filters( 'acfcdt/field_creates_join_table', $create_join_table, $field, $table_name );
		} );
	}

	/**
	 * @param ACFFieldGroup $field_group
	 *
	 * @return array
	 */
	public function extract_sub_table_fields( ACFFieldGroup $field_group ) {
		return array_filter( $field_group->fields(), function ( $field ) {

			if ( ! $this->field_support_manager->field_eligible_for_sub_table( $field ) ) {
				return false;
			}

			$table_name = $this->field_group->table_name();
			$create_sub_table = $this->settings->get( 'enable_sub_tables_globally' );

			/**
			 * Filter this and return TRUE to enable the addition of sub table definitions in the generated table
			 * definition JSON. You can do this for certain fields, field types, or even entire tables using the
			 * available args.
			 *
			 * IF you just return TRUE, all eligible fields will create sub tables on the next schema update.
			 * IF you just return FALSE on this filter, no sub table definitions will be added to any JSON files on
			 * the next scheme update.
			 *
			 * IF you already have sub tables in place and you disable some using this filter, the schema will need
			 * to be updated before ACF Custom Database Tables will understand the change.
			 *
			 * Note: you can't use this to activate sub tables on fields that don't currently support that, as this
			 * filter only runs on eligible fields.
			 */
			return apply_filters( 'acfcdt/field_creates_sub_table', $create_sub_table, $field, $table_name );
		} );
	}

	/**
	 * @param ACFFieldGroup $field_group
	 *
	 * @return array
	 */
	public function build_columns_array( ACFFieldGroup $field_group ) {

		$columns = [];

		$fields = $this->get_supported_fields( $field_group );
		$excluded_field_names = [];

		if ( $join_table_fields = $this->extract_join_fields( $field_group ) ) {
			$excluded_field_names = $this->extract_field_names( $join_table_fields );
		}

		if ( $sub_table_fields = $this->extract_sub_table_fields( $field_group ) ) {
			$excluded_field_names = array_merge( $excluded_field_names, $this->extract_field_names( $sub_table_fields ) );
		}

		foreach ( $fields as $field_array ) {

			if ( in_array( $field_array['name'], $excluded_field_names ) ) {
				continue;
			}

			$field = $this->field_factory->make_from_field_array( $field_array );
			$sanitized_field_name = $this->table_name_validator->sanitize( $field->name() );
			$table_name = $this->field_group->table_name();

			$column = [
				'name' => $sanitized_field_name,
				'type' => $this->get_data_type( $table_name, $sanitized_field_name, $field_array ),
				'map' => [
					'type' => 'acf_field_name',
					'identifier' => $field->name(),
					'key' => $field->key(),
				],
			];

			if ( $match_patterns = $field->get_meta_field_name_match_patterns() ) {
				$column['map']['sub_field_match_patterns'] = $match_patterns;
			}

			$columns[] = $column;
		}

		return $columns;
	}

	/**
	 * @param ACFFieldGroup $field_group
	 *
	 * @return array
	 */
	public function build_join_tables_array( ACFFieldGroup $field_group ) {

		$join_tables = [];

		if ( ! $eligible_fields = $this->extract_join_fields( $field_group ) ) {
			return $join_tables;
		}

		$parent_table_name = $field_group->table_name();
		$relationship = $this->get_relationship( $field_group );

		/**
		 * todo - this is a bit of a hacky way to get the object key – change the approach when time allows, likely by
		 *  moving all this to the normalisation process. Note: this is also used in $this->generate()
		 */
		$obj_key = $this->table_validator->get_object_relationship_key_name( [ 'relationship' => $relationship ] );

		foreach ( $eligible_fields as $field_array ) {
			$field = $this->field_factory->make_from_field_array( $field_array );
			$sanitized_field_name = $this->table_name_validator->sanitize( $field->name() );
			$field_format = $field->is_a( 'page_link' ) ? '%s' : '%d';
			$join_tables[] = [
				'name' => "{$parent_table_name}__{$sanitized_field_name}",
				'primary_key' => [
					'id'
				],
				'keys' => [
					[
						'name' => $obj_key . '_' . $sanitized_field_name,
						'columns' => [
							$obj_key,
							$sanitized_field_name
						],
						'unique' => true,
					],
					[
						'name' => $sanitized_field_name,
						'columns' => [
							$sanitized_field_name
						],
					]
				],
				'columns' => [
					[
						'name' => 'id',
						'format' => '%d',
						'type' => 'bigint(20)',
						'null' => false,
						'auto_increment' => true,
						'unsigned' => true,
					],
					[
						'name' => $obj_key,
						'format' => '%d',
						'type' => 'bigint(20)',
					],
					[
						'name' => $sanitized_field_name,
						'format' => $field_format,
						'type' => ( $field_format === '%d' ) ? 'bigint(20)' : 'longtext',
						'map' => [
							'type' => 'acf_field_name',
							'identifier' => $field->name()
						],
					],
					[
						'name' => '_sort_order',
						'format' => '%d',
						'type' => 'bigint(20)',
						'default' => 0
					],
				]
			];
		}

		return $join_tables;

	}

	public function build_sub_tables_array( ACFFieldGroup $field_group ) {
		$sub_tables = [];

		if ( ! $eligible_fields = $this->extract_sub_table_fields( $field_group ) ) {
			return $sub_tables;
		}

		$parent_table_name = $field_group->table_name();
		$relationship = $this->get_relationship( $field_group );

		/**
		 * todo - this is a bit of a hacky way to get the object key – change the approach when time allows, likely by
		 *  moving all this to the normalisation process. Note: this is also used in $this->generate()
		 */
		$obj_key = $this->table_validator->get_object_relationship_key_name( [ 'relationship' => $relationship ] );

		foreach ( $eligible_fields as $field_array ) {

			$field = $this->field_factory->make_from_field_array( $field_array );

			if ( ! $field->has_sub_fields_array() ) {
				continue;
			}

			$sanitized_field_name = $this->table_name_validator->sanitize( $field->name() );
			$sub_table_name = "{$parent_table_name}__{$sanitized_field_name}";

			$sub_table = [
				'name' => $sub_table_name,
				'parent' => [
					'table' => $parent_table_name,
					'field' => $field->name(),
					'field_key' => $field->key(),
				],
				'primary_key' => [
					'id'
				],
				'keys' => [],
				'columns' => [
					[
						'name' => 'id',
						'format' => '%d',
						'type' => 'bigint(20)',
						'null' => false,
						'auto_increment' => true,
						'unsigned' => true,
					],
					[
						'name' => $obj_key,
						'format' => '%d',
						'type' => 'bigint(20)',
					],
					[
						'name' => '_sort_order',
						'format' => '%d',
						'type' => 'bigint(20)',
						'default' => 0,
					],
				]
			];

			foreach ( $field->get_sub_fields_array() as $sub_field_array ) {

				$sub_field = $this->field_factory->make_from_field_array( $sub_field_array );

				if ( ! $sub_field->is_supported() ) {
					continue;
				}

				$sanitized_sub_field_name = $this->table_name_validator->sanitize( $sub_field->name() );

				$column = [
					'name' => $sanitized_sub_field_name,
					'type' => $this->get_data_type( $sub_table_name, $sanitized_sub_field_name, $sub_field_array ),
					'format' => '%s',
					'map' => [
						'type' => 'acf_field_name',
						'identifier' => $sub_field->name(),
						'key' => $sub_field->key(),
						'match_pattern' => $field->name() . '_(\d+)_' . $sub_field->name(),
					],
				];

				if ( $match_patterns = $sub_field->get_meta_field_name_match_patterns() ) {
					$column['map']['sub_field_match_patterns'] = Arr::prefix_values( $match_patterns, $field->name() . '_(\d+)_' );
				}

				$sub_table['columns'][] = $column;
			}

			$sub_tables[] = $sub_table;
		}

		return $sub_tables;
	}

	/**
	 * Determine the column data type for a given field by passing it through some control filters.
	 *
	 * @param string $table_name
	 * @param string $sanitized_field_name
	 * @param array $field_array
	 * @param string $default
	 *
	 * @return string
	 */
	private function get_data_type( $table_name, $sanitized_field_name, array $field_array, $default = 'longtext' ) {
		$data_type = $default;

		/**
		 * General use filter for overriding the data type of columns. The hooked callback will receive the current
		 * type, the table name, and the column name.
		 *
		 * @var string $data_type The column data type. Defaults to longtext.
		 * @var string $table_name The name of the table without the WP DB prefix.
		 * @var string $sanitized_field_name The column name.
		 * @var array $field_array The ACF field array.
		 */
		$data_type = apply_filters( "acfcdt/set_column_data_type", $data_type, $table_name, $sanitized_field_name, $field_array );

		/**
		 * Specific use filter for overriding a specific column within a specific table. The hooked callback
		 * receives only the type.
		 *
		 * Example usage: add_filter('acfcdt/set_column_data_type/my_table.my_column', '…')
		 *
		 * @var string $data_type The column data type. Defaults to longtext.
		 * @var string $table_name The name of the table without the WP DB prefix.
		 * @var string $sanitized_field_name The column name.
		 * @var array $field_array The ACF field array.
		 */
		$data_type = apply_filters( "acfcdt/set_column_data_type/{$table_name}.{$sanitized_field_name}", $data_type, $field_array );

		// todo - could be worth adding a check here to ensure the filtered data type is valid.

		return $data_type;
	}

	/**
	 * Generate a new UTC timestamp for use in the 'modified' property.
	 *
	 * @return int
	 */
	private function generate_modified_time() {
		try {
			$time = (int) ( new DateTime( 'now', new DateTimeZone( 'UTC' ) ) )->format( 'U' );
		} catch ( Exception $e ) {
			$time = 0;
			Error::log( sprintf( 'Failed to generate modified time. Message reads: %s', $e->getMessage() ) );
		}

		/**
		 * Override the modified time of the table JSON definition. This is more for internal use than anything as we
		 * need to fix the modified time to '0' during out tests. If we didn't, we would need to commit the generated
		 * JSON files each and every time we ran out tests.
		 */
		return (int) apply_filters( 'acfcdt/table_json_modified_time', $time, $this->field_group->to_array() );
	}

}