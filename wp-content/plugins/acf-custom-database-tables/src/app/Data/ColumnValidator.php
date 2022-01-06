<?php

namespace ACFCustomDatabaseTables\Data;

use WP_Error;

class ColumnValidator {

	/**
	 * Ensures we have a normalised args set for working with column objects
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function normalise_args( array $args ) {
		return wp_parse_args( $args, [
			'name' => '',
			'format' => '%s',
			'has_default_value' => false
		] );
	}

	/**
	 * Checks to ensure our args array has the minimum required fields
	 *
	 * @param array $args
	 *
	 * @return bool|WP_Error
	 */
	public function validate_args( array $args ) {
		$required = [
			'name'
		];
		$missing = [];
		foreach ( $required as $r ) {
			if ( ! isset( $args[ $r ] ) ) {
				$missing[] = $r;
			}
		}

		if ( $missing ) {
			return new WP_Error( 'acft', 'ColumnValidator::validate_args missing required args: ' . implode( ', ', $missing ) );
		}

		return true;
	}

}