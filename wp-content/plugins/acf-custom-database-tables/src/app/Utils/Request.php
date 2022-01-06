<?php

namespace ACFCustomDatabaseTables\Utils;

/**
 * Class Request
 * @package ACFCustomDatabaseTables\Utils
 *
 * Utility methods for accessing data on the request.
 */
class Request {

	/**
	 * Get a value from the request. Defaults to using $_REQUEST but
	 *
	 * @param $key
	 * @param null $default
	 * @param string $context
	 *
	 * @return mixed|null
	 */
	public static function get( $key, $default = null, $context = 'request' ) {
		if ( $context === 'post' ) {
			return Arr::get( $_POST, $key, $default );

		} elseif ( $context === 'get' ) {
			return Arr::get( $_GET, $key, $default );
		}

		return Arr::get( $_REQUEST, $key, $default );
	}

}