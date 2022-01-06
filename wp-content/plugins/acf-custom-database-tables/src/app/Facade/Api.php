<?php

namespace ACFCustomDatabaseTables\Facade;

use ACFCustomDatabaseTables\Container;
use ACFCustomDatabaseTables\Coordinator\TableCoordinator;
use ACFCustomDatabaseTables\FileIO\TableJSONFileGenerator;
use ACFCustomDatabaseTables\Model\ACFFieldGroup;
use ACFCustomDatabaseTables\Utils\Arr;
use WP_Error;

/**
 * Static methods which will form the basis of the PHP API functions that we'll start to assemble after version 1.1.
 *
 * @package ACFCustomDatabaseTables\Facade
 * @mixin Container
 */
class Api extends FacadeBase {

	protected static function get_facade_accessor() {
		return 'container';
	}

	/**
	 * Check whether a field group has an existing table JSON definition file on disk.
	 *
	 * todo - write tests for this
	 *
	 * @param array $field_group
	 *
	 * @return bool
	 */
	public static function field_group_has_table_json_file( array $field_group ) {
		$path = self::get_field_group_table_json_path( $field_group );

		return $path && file_exists( $path );
	}

	/**
	 * Get the full path to the table JSON definition file for a field group.
	 *
	 * todo - write tests for this
	 *
	 * @param array $field_group
	 *
	 * @return false|string
	 */
	public static function get_field_group_table_json_path( array $field_group ) {
		if ( empty( $json_file_name = self::get_field_group_table_json_file_name( $field_group ) ) ) {
			return false;
		}

		return trailingslashit( Settings::get( 'json_dir' ) ) . $json_file_name;
	}

	/**
	 * Get the table JSON definition file name from a field group.
	 *
	 * todo - write tests for this
	 *
	 * @param array $field_group ACF field group.
	 *
	 * @return string
	 */
	public static function get_field_group_table_json_file_name( array $field_group ) {
		if ( $name = Arr::get( $field_group, ACFFieldGroup::DEFINITION_FILE_NAME_KEY, '' ) ) {
			$name .= '.json';
		}

		return $name;
	}

	/**
	 * Get an array of ACF field groups that have a table definition JSON file on disk.
	 *
	 * todo - write tests for this
	 *
	 * @return array[]
	 */
	public static function get_field_groups_with_table_json_files() {
		$json_dir = untrailingslashit( Settings::get( 'json_dir' ) );

		return array_filter( self::get_field_groups(), function ( $group ) use ( $json_dir ) {

			// If the field group doesn't have a definition file name associated with it, ignore it.
			if ( empty( $json_file_name = Arr::get( $group, ACFFieldGroup::DEFINITION_FILE_NAME_KEY ) ) ) {
				return false;
			}

			// If the JSON file does not exist on disk, the group mustn't be in play.
			if ( ! file_exists( $json_dir . '/' . $json_file_name . '.json' ) ) {
				return false;
			}

			return true;
		} );
	}

	/**
	 * Get an array of all ACF field groups that have custom database tables enabled and have a table name defined.
	 *
	 * todo - write tests for this
	 *
	 * @return array[]
	 */
	public static function get_field_groups_with_table_enabled() {
		return array_filter( self::get_field_groups(), function ( $group ) {
			// If the field group has custom table disabled, ignore it.
			if ( empty( Arr::get( $group, ACFFieldGroup::MANAGE_TABLE_DEFINITION_KEY ) ) ) {
				return false;
			}

			// If the field group does not have a table name, ignore it.
			if ( empty( Arr::get( $group, ACFFieldGroup::TABLE_NAME_KEY ) ) ) {
				return false;
			}

			return true;
		} );
	}

	/**
	 * Re/generate a field group's table JSON definition file. This stores the file to disk.
	 *
	 * todo - write tests for this
	 *
	 * @param array $field_group An ACF field group array.
	 *
	 * @return array|WP_Error The table definition JSON array on success, WP_Error on failure.
	 */
	public static function generate_field_group_table_json( array $field_group ) {
		$field_group_obj = Factory::make_field_group_object_from_array( $field_group );
		/** @var TableJSONFileGenerator $generator */
		$generator = App::make( TableJSONFileGenerator::class );

		return $generator->generate_from_field_group( $field_group_obj );
	}

	/**
	 * Re/generate the PHP file that houses the complete map. This stores the file to disk.
	 *
	 * todo - write tests for this
	 *
	 * @return bool|true|WP_Error True if file on success, WP_Error on failure.
	 */
	public static function generate_table_map_file() {
		/** @var TableCoordinator $builder */
		$builder = App::make( TableCoordinator::class );

		// Read up JSON files into map object.
		$map = $builder->build_map_from_json();
		if ( is_wp_error( $map ) ) {
			return $map;
		}

		return $builder->update_map_cache();
	}

	/**
	 * Get array of ACF field groups. There does appear to be some edge cases where a 'FALSE' value can end up in the
	 * array. This filters out anything that isn't an array.
	 *
	 * @return array
	 */
	private static function get_field_groups() {
		return array_filter( acf_get_field_groups(), 'is_array' );
	}

}