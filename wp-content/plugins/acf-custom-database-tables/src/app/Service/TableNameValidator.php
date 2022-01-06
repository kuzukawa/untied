<?php

namespace ACFCustomDatabaseTables\Service;

use WP_Error;

class TableNameValidator {

	/**
	 * Permitted characters in SQL identifiers
	 *
	 * @see https://dev.mysql.com/doc/refman/5.7/en/identifiers.html
	 */
	const SUPPORTED_CHARACTER_GROUP = 'A-Za-z0-9$_';

	/**
	 * TableNameValidator constructor.
	 *
	 * @param null $wpdb
	 */
	public function __construct( $wpdb = null ) {
		if ( null !== $wpdb ) {
			_deprecated_argument( __METHOD__, '1.1 (ACF Custom Database Tables)', 'No longer injecting $wpdb due to object cache issues. Change this to NULL. Any related props will be removed in version 1.2' );
		}
	}

	/**
	 * public function is_valid( $table_name ) {
	 * return ! is_wp_error( $this->validate( $table_name ) );
	 * }
	 *
	 *
	 * /**
	 * Validates table name. Expects a non-prefixed table name and handles the prefixing internally.
	 *
	 * @param string $table_name
	 *
	 * @return bool|WP_Error TRUE if valid, WP_Error otherwise
	 */
	public function validate( $table_name ) {

		if ( ! $this->is_valid_type( $table_name ) ) {
			return new WP_Error( 'acfcdt', 'Invalid table name' );
		}

		//if ( $this->table_name_already_exists( $table_name ) ) {
		//	return new WP_Error( 'acfcdt', 'A table already exists with that name' );
		//}

		if ( ! $this->matches_supported_format( $table_name ) ) {
			return new WP_Error( 'acfcdt', 'Table name contains unsupported characters' );
		}

		return true;

	}

	/**
	 * @return bool
	 */
	public function is_valid_type( $table_name ) {
		return ( $table_name and is_string( $table_name ) );
	}

	/**
	 * @return bool
	 */
	public function table_name_already_exists( $table_name ) {
		global $wpdb;

		$SQL = $wpdb->prepare( "SHOW TABLES LIKE %s;", $wpdb->prefix . $table_name );

		return (bool) $wpdb->get_results( $SQL, ARRAY_N );
	}

	/**
	 * @param $table_name
	 *
	 * @return bool
	 */
	public function matches_supported_format( $table_name ) {
		$regex = '/^[' . self::SUPPORTED_CHARACTER_GROUP . ']+$/';

		return (bool) preg_match( $regex, $table_name );
	}

	/**
	 * Replaces unsupported characters in an identifier with un underscore for use as an SQL identifier
	 *
	 * @param $identifier
	 *
	 * @return null|string|string[]
	 */
	public function sanitize( $identifier ) {
		$regex = '/[^' . self::SUPPORTED_CHARACTER_GROUP . ']/';

		return preg_replace( $regex, '_', $identifier );
	}

}