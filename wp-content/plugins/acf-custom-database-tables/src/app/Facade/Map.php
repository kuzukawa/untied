<?php

namespace ACFCustomDatabaseTables\Facade;

use ACFCustomDatabaseTables\Data\TableMap;
use ACFCustomDatabaseTables\Model\ACFFields\ACFFieldBase;
use ACFCustomDatabaseTables\Model\ACFSelector;

/**
 * @package ACFCustomDatabaseTables\Facade
 * @mixin TableMap
 *
 * @method static field_is_sub_table_owner( ACFFieldBase $field, ACFSelector $selector ) : bool
 * @method static get_table_name( ACFFieldBase $field, ACFSelector $selector ) : string|false
 * @method static get_field_column_type( $field_name, $context ) : string
 * @method static get_column_name( ACFFieldBase $field, ACFSelector $selector ) : string
 * @method static get_field_name_pattern( ACFFieldBase $field, ACFSelector $selector ) : string
 * @method static field_is_column_owner( ACFFieldBase $field, ACFSelector $selector ) : bool
 * @method static field_is_nested_within_a_column( ACFFieldBase $field, ACFSelector $selector ) : bool
 * @method static get_root_field_key( ACFFieldBase $field, ACFSelector $selector ) : string|null
 * @method static is_sub_table( $table_name ) : bool
 * @method static is_join_table( $table_name ) : bool
 * @method static has_table( ACFFieldBase $field, ACFSelector $selector ) : bool
 * @method static locate_all_tables_by_post_type( string $post_type ) : array
 * @method static locate_all_user_tables() : array
 *
 * @uses \ACFCustomDatabaseTables\Data\TableMap::field_is_sub_table_owner
 * @uses \ACFCustomDatabaseTables\Data\TableMap::get_table_name
 * @uses \ACFCustomDatabaseTables\Data\TableMap::get_field_column_type
 * @uses \ACFCustomDatabaseTables\Data\TableMap::get_column_name
 * @uses \ACFCustomDatabaseTables\Data\TableMap::get_field_name_pattern
 * @uses \ACFCustomDatabaseTables\Data\TableMap::field_is_column_owner
 * @uses \ACFCustomDatabaseTables\Data\TableMap::field_is_nested_within_a_column
 * @uses \ACFCustomDatabaseTables\Data\TableMap::get_root_field_key
 * @uses \ACFCustomDatabaseTables\Data\TableMap::is_sub_table
 * @uses \ACFCustomDatabaseTables\Data\TableMap::is_join_table
 * @uses \ACFCustomDatabaseTables\Data\TableMap::has_table
 * @uses \ACFCustomDatabaseTables\Data\TableMap::locate_all_tables_by_post_type
 * @uses \ACFCustomDatabaseTables\Data\TableMap::locate_all_user_tables
 */
class Map extends FacadeBase {

	protected static function get_facade_accessor() {
		return TableMap::class;
	}

}