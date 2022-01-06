<?php

namespace ACFCustomDatabaseTables\Service;

use ACFCustomDatabaseTables\Data\TableMap;
use ACFCustomDatabaseTables\Model\ACFSelector;

/**
 * Class ACFLocalReferenceFallback
 * @package ACFCustomDatabaseTables\Service
 *
 * On get_field(), ACF doesn't go on to check local JSON files in order to get a field_key. This class adds a fallback
 * layer that handles that by explicitly checking local JSON files where a field_key hasn't already been established
 * from the DB.
 */
class ACFLocalReferenceFallback {

	public function init() {
		// Pre load field key from our map if we have it.
		add_filter( 'acf/pre_load_reference', [ $this, '_pre_load_field_key_reference_from_map' ], 10, 3 );
		// Allow fallback to local field references.
		add_filter( 'acf/load_reference', [ $this, '_fall_back_to_local_references' ], 15, 3 );
	}

	/**
	 * This is a fix for a bug in ACF where multiple fields of the same name aren't correctly aliased in ACFs 'fields'
	 * and 'local-fields' stores. This causes ACF to return the wrong field key when the same field name is used across
	 * multiple post types. Elliot is aware of this and this may well be fixed in 5.9 but for now, we are implementing
	 * our own internal handling for this to ensure fields stored in custom DB tables always have the correct field key
	 * and subsequently the correct field object.
	 *
	 * @param string|null $null The field key, if found. NULL if not.
	 * @param string $field_name
	 * @param string|int $post_id
	 *
	 * @return string
	 */
	public function _pre_load_field_key_reference_from_map( $null, $field_name, $post_id ) {

		if ( ! $context = ACFSelector::make( $post_id )->context() ) {
			return $null;
		}

		/** @var TableMap $map */
		$c = acf_custom_database_tables()->__get_container();
		$map = $c[ TableMap::class ];

		if ( $key = $map->locate_acf_field_key_by_acf_field_name( $field_name, $context ) ) {
			return $key;
		}

		return $null;
	}

	/**
	 * @param $field_key
	 * @param $field_name
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public function _fall_back_to_local_references( $field_key, $field_name, $post_id ) {
		// If we already have the field key, don't do anything here.
		if ( $field_key ) {
			return $field_key;
		}

		if ( acf_is_local_field( $field_name ) and $field = acf_get_local_field( $field_name ) ) {
			return $field['key'];
		}

		return $field_key;
	}

}