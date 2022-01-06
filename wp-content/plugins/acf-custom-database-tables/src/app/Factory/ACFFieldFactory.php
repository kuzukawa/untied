<?php

namespace ACFCustomDatabaseTables\Factory;

use ACFCustomDatabaseTables\Model\ACFFields\ACFFieldBase;
use ACFCustomDatabaseTables\Vendor\Pimple\Container;

class ACFFieldFactory {

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @param Container $container
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
	}

	/**
	 * @param $type
	 *
	 * @return ACFFieldBase
	 */
	public function make_from_field_type( $type ) {
		// todo - get a type => class name defs list happening in config and give the people
		//  an opportunity to register custom field type handlers

		return isset( $this->container["acf_field.$type"] )
			? $this->container["acf_field.$type"]
			: $this->container["acf_field._generic"];
	}

	/**
	 * @param array $field
	 *
	 * @return ACFFieldBase
	 */
	public function make_from_field_array( array $field ) {
		isset( $field['type'] ) or $field['type'] = '';

		$field_object = $this->make_from_field_type( $field['type'] );
		$field_object->set_field_array( $field );

		return $field_object;
	}

	/**
	 * Return an array of complex field keys mapped to field objects. e.g;
	 *
	 * [
	 *      'repeater' => RepeaterACFField,
	 *      'repeater_0_text' => TextACFField,
	 *      'repeater_0_text2' => TextACFField,
	 *      'repeater_1_text' => TextACFField,
	 *      'repeater_1_text2' => TextACFField,
	 * ]
	 *
	 * @param ACFFieldBase $field
	 *
	 * @return ACFFieldBase[]
	 */
	public function map_field_handlers_to_field_keys( ACFFieldBase $field ) {
		$base = $field->name();
		$handlers = [ $base => $field ];
		$field_array = $field->to_array();

		// build the sub field handler map
		$fn = function ( $base, $handlers, $raw_value, $field_array ) use ( &$fn ) {
			if ( is_array( $raw_value ) ) {

				// build an easily accessed field_name => field_array map
				$map = [];
				foreach ( $field_array['sub_fields'] as $sub_field_array ) {
					$map[ $sub_field_array['name'] ] = $sub_field_array;
				}

				foreach ( $raw_value as $index => $row ) {

					if ( ! is_countable( $row ) ) {
						continue;
					}

					foreach ( $row as $key => $value ) {

						if ( empty( $map[ $key ] ) ) {
							continue;
						}

						$sub_field = $this->make_from_field_array( $map[ $key ] );

						if ( ! $sub_field->is_supported() ) {
							continue;
						}

						$sub_field->set_value( $value );

						$b = "{$base}_{$index}_{$key}";
						$handlers[ $b ] = $sub_field;

						if ( $sub_field->has_sub_fields_array() ) {
							$handlers = $fn( $b, $handlers, $value, $sub_field->to_array() );
						}
					}
				}
			}

			return $handlers;
		};

		return $field->has_sub_fields_array()
			? $fn( $base, $handlers, $field->get_value_raw(), $field_array )
			: $handlers;
	}

}