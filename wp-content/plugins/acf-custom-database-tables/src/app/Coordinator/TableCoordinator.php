<?php

namespace ACFCustomDatabaseTables\Coordinator;

use ACFCustomDatabaseTables\Cache\TableObjectCache;
use ACFCustomDatabaseTables\Data\TableMap;
use ACFCustomDatabaseTables\DB\DynamicTableBase;
use ACFCustomDatabaseTables\Factory\DynamicTableFactory;
use ACFCustomDatabaseTables\FileIO\JSONFileParser;
use ACFCustomDatabaseTables\Settings;
use WP_Error;

/**
 * Class TableCoordinator
 * @package ACFCustomDatabaseTables\Coordinator
 *
 * Handles the generation and caching of the table map;
 */
class TableCoordinator {

	/** @var  JSONFileParser */
	private $parser;

	/** @var  Settings */
	private $settings;

	/** @var TableMap */
	private $map;

	/** @var  DynamicTableFactory */
	private $factory;

	/** @var TableObjectCache */
	private $table_cache;

	/**
	 * TableCoordinator constructor.
	 *
	 * @param JSONFileParser $parser
	 * @param Settings $settings
	 * @param TableMap $map
	 * @param DynamicTableFactory $factory
	 * @param TableObjectCache $table_cache
	 */
	public function __construct( JSONFileParser $parser, Settings $settings, TableMap $map, DynamicTableFactory $factory, TableObjectCache $table_cache ) {
		$this->parser = $parser;
		$this->settings = $settings;
		$this->map = $map;
		$this->factory = $factory;
		$this->table_cache = $table_cache;
	}

	/**
	 * Reads all table definition JSON files from the json dir and loads table definitions into TableMap object.
	 *
	 * @return TableMap|WP_Error
	 */
	public function build_map_from_json() {

		$dir = $this->settings->get( 'json_dir' );
		$error = new WP_Error();
		$success = $this->parser->read_files_in_dir( $dir );

		if ( ! $success ) {
			$error->add( 'acfcdt', "Failed to read JSON files in dir: $dir. Tables will not be created/updated from those definitions." );
		}

		$this->map->reset();

		foreach ( $this->parser->decoded_file_contents() as $table_definition ) {
			$added = $this->map->add_table( $table_definition );
			if ( is_wp_error( $added ) ) {
				$error->add( 'acfcdt', $added->get_error_message() );
			}
		}

		return $error->errors
			? $error
			: $this->map;
	}

	/**
	 * todo - one day, refactor disk writing into an object under FileIO
	 *
	 * Caches the TableMap objects map to a php file for faster loading. When called, this method rewrites the cache
	 * file.
	 *
	 * @return true|WP_Error
	 */
	public function update_map_cache() {
		if ( ! $this->map ) {
			$object = $this->build_map_from_json();
			if ( is_wp_error( $object ) ) {
				$object->add( 'acfcdt', "Failed to update map cache due to inability to build map from JSON." );

				return $object;
			}
		}

		$cache_dir = $this->cache_dir();

		if ( ! is_dir( $cache_dir ) and ! mkdir( $cache_dir, 0750 ) ) {
			return new WP_Error( 'acfcdt', "Cache dir does not exist and could not be created. Please create the directory manually and try again. Dir: $cache_dir" );
		}

		if ( ! is_writable( $cache_dir ) ) {
			return new WP_Error( 'acfcdt', "Cache dir is not writable. Dir: $cache_dir" );
		}

		$cache_file = $this->cache_file();

		if ( false === ( fclose( fopen( $cache_file, 'w' ) ) ) ) {
			return new WP_Error( 'acfcdt', "Could not create cache file. Please create the file manually and try again. File: $cache_file" );
		}

		if ( false === file_put_contents( $cache_file, "<?php defined( 'WPINC' ) or die('Nothing to see here.'); return " . var_export( $this->map->get_map(), true ) . ";" ) ) {
			return new WP_Error( 'acfcdt', "There was a problem writing to the cache file. File: $cache_file" );
		}

		return true;
	}

	/**
	 * Gets the array from the table map cache file, inserts that data into the table map object, then returns the
	 * table map object.
	 *
	 * @return TableMap|WP_Error
	 */
	public function fetch_map_from_cache() {
		$file = $this->cache_file();
		if ( ! is_readable( $file ) ) {
			return new WP_Error( 'acfcdt', "Cache file is not readable. File: $file" );
		}

		$map = include $file;
		$object = $this->map->set_map( (array) $map );

		if ( is_wp_error( $object ) ) {
			$object->add( 'acfcdt', 'Failed to build map object from cache.' );

			return $object;
		}

		return $object;
	}

	/**
	 * Returns the requested DynamicTableBase. If the object hasn't already been created, this creates it based on the map
	 * definition and caches it in $this->tables[]
	 *
	 * @param string $table_name
	 *
	 * @return DynamicTableBase|WP_Error
	 */
	public function get_table_object( $table_name ) {

		if ( $table = $this->table_cache->get( $table_name ) ) {
			return $table;
		}

		$table_defs = $this->map->get_map( 'tables' );

		if ( ! isset( $table_defs[ $table_name ] ) ) {
			return new WP_Error( 'acfcdt', "Table '$table_name' does not have a table definition." );
		}

		$object = $this->factory->make( $table_defs[ $table_name ] );
		if ( is_wp_error( $object ) ) {
			$object->add( 'acfcdt', "Failed to create the requested DynamicTableBase object: $table_name" );

			return $object;
		}

		$this->table_cache->set( $table_name, $object );

		return $object;
	}

	/**
	 * Returns an array of all DynamicTableBase objects. This first cross references the array of objects with all registered
	 * table definitions and, if a table object hasn't yet been instantiated, this calls on self::table() to create and
	 * cache the object in $this->tables[]
	 *
	 * @return DynamicTableBase[]
	 */
	public function get_all_table_objects() {

		$tables = [];
		$table_names = $this->map->get_map( 'table_names' );

		foreach ( $table_names as $name ) {
			$tables[] = $this->get_table_object( $name );
		}

		return $tables;
	}

	/**
	 * Accessor for the TableMap object
	 *
	 * @return TableMap
	 */
	public function map() {
		return $this->map;
	}

	/**
	 * Returns the cache directory path as specified in the settings object.
	 *
	 * @return null
	 */
	private function cache_dir() {
		return $this->settings->get( 'table_map_cache_dir', false );
	}

	/**
	 * Returns the full path to the table map cache file
	 *
	 * @return string
	 */
	private function cache_file() {
		return rtrim( $this->cache_dir(), DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . '_table_map.php';
	}

}