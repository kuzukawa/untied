<?php

namespace ACFCustomDatabaseTables\Utils;

use WP_Error;

/**
 * Class Error
 * @package ACFCustomDatabaseTables\Utils
 */
class Error {

	/**
	 * @alias \ACFCustomDatabaseTables\Utils\Error::trigger
	 * @param WP_Error|string $message
	 * @param int $type
	 * @param string $return
	 *
	 * @return Error|mixed|string
	 */
	public static function log( $message, $type = E_USER_NOTICE, $return = '___instance___' ) {
		return self::trigger( $message, $type, $return );
	}

	/**
	 * @param WP_Error|string $message Either a string or a WP_Error object to handle/log.
	 * @param int $type
	 * @param mixed $return The value to return after triggering the error.
	 *
	 * @return Error|mixed
	 */
	public static function trigger( $message, $type = E_USER_NOTICE, $return = '___instance___' ) {
		if ( is_wp_error( $message ) ) {
			$message = $message->get_error_message();
		}

		// todo - could check is dev environment and potentially throw exceptions here in that situation.
		trigger_error( $message, $type );

		return $return === '___instance___'
			? new self
			: $return;
	}

	/**
	 * Chainable return value handler. Useful for one-line/fluent error handling.
	 *
	 * @param mixed $return The value to return.
	 *
	 * @return mixed
	 */
	public function return( $return ) {
		return $return;
	}

	/**
	 * Chainable return handler for returning nothing. Technically returns null.
	 *
	 * @return void|null
	 */
	public function void() {
		return;
	}

}