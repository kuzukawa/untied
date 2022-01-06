<?php

namespace ACFCustomDatabaseTables\Module;

use ACFCustomDatabaseTables\Contract\ModuleInterface;

/**
 * Class IntegerTypeCastModule
 * @package ACFCustomDatabaseTables\Module
 *
 * This module attempts to convert eligible strings to integers in order to maintain cleaner encoded data in the custom tables.
 */
class IntegerTypeCastModule implements ModuleInterface {

	/** @return string The module name */
	public function name() {
		return 'integer_type_cast';
	}

	public function init() {
		self::enable();
	}

	public function enable() {
		add_filter( 'acfcdt/filter_value_before_encode', [ $this, 'apply_integer_casting' ], 10 );
	}

	public function disable() {
		remove_filter( 'acfcdt/filter_value_before_encode', [ $this, 'apply_integer_casting' ], 10 );
	}

	public function apply_integer_casting( $value ) {
		return is_array( $value )
			? array_map( [ $this, 'convert_eligible_string_to_integer' ], $value )
			: $this->convert_eligible_string_to_integer( $value );
	}

	/**
	 * Converts non-empty whole-number strings to integers. If the resulting integer isn't identical to the original string,
	 * the original value is returned instead (no conversion takes place).
	 *
	 * @param $string
	 *
	 * @return int
	 */
	private function convert_eligible_string_to_integer( $string ) {

		if ( ! is_string( $string ) or $string === '' ) {
			return $string;
		}

		$integer = intval( $string );

		if ( is_numeric( $string ) and $string === (string) $integer ) {
			return $integer;
		}

		return $string;
	}

}