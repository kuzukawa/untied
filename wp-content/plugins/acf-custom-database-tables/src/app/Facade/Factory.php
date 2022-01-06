<?php

namespace ACFCustomDatabaseTables\Facade;

use ACFCustomDatabaseTables\Container;
use ACFCustomDatabaseTables\Factory\ACFFieldFactory;
use ACFCustomDatabaseTables\Factory\ACFFieldGroupFactory;
use ACFCustomDatabaseTables\Model\ACFFields\ACFFieldBase;
use ACFCustomDatabaseTables\Utils\Error;

/**
 * @package ACFCustomDatabaseTables\Facade
 * @mixin Container
 */
class Factory extends FacadeBase {

	protected static function get_facade_accessor() {
		return 'container';
	}

	/**
	 * @param $acf_field_array
	 *
	 * @return ACFFieldBase
	 */
	public static function make_field_object_from_array( $acf_field_array ) {
		/** @var ACFFieldFactory $factory */
		$factory = self::$app->make( ACFFieldFactory::class );

		return $factory->make_from_field_array( $acf_field_array );
	}

	public static function make_field_object_from_field_key( $field_key ) {
		$field_array = acf_maybe_get_field( $field_key );

		if ( ! $field_array ) {
			Error::log( "Could not get field array for key `$field_key`. Resulting to generic handler instead.
			If there are issues storing this field in custom database tables, this could be the reason why. Consider
			regenerating the database tables map as a potential fix." );

			$field_array = [
				'key' => $field_key,
				'type' => '',
			];
		}

		return self::make_field_object_from_array( $field_array );
	}

	public static function make_field_group_object_from_array( array $field_group_arr ) {
		/** @var ACFFieldGroupFactory $factory */
		$factory = self::$app->make( ACFFieldGroupFactory::class );

		return $factory->make_from_field_group_array( $field_group_arr );
	}

}