<?php

namespace ACFCustomDatabaseTables\Utils;

/**
 * Class Redirect
 * @package ACFCustomDatabaseTables\Utils
 *
 * Utilities related to redirecting requests.
 */
class Redirect {

	const REDIRECT_BY = 'WordPress/ACF-Custom-Database-Tables';

	/**
	 * Perform a safe redirect to a specified URL. This will prevent redirects to hosts that are not specifically
	 * allowed. By default, the only allowed host is the current host name.
	 *
	 * @param string $url
	 * @param int $status
	 * @param bool $nocache_headers Whether or not to send nocache headers to prevent browser caching the redirect.
	 * @param string $x_redirect_by
	 */
	public static function safely_to( $url, $status = 302, $nocache_headers = true, $x_redirect_by = self::REDIRECT_BY ) {
		if ( $nocache_headers ) {
			nocache_headers();
		}
		if ( wp_safe_redirect( $url, $status, $x_redirect_by ) ) {
			die();
		}
	}

	/**
	 * Redirect to a specified URL on any host.
	 *
	 * @param $url
	 * @param int $status
	 * @param bool $nocache_headers Whether or not to send nocache headers to prevent browser caching the redirect.
	 * @param string $x_redirect_by
	 */
	public static function to( $url, $status = 302, $nocache_headers = true, $x_redirect_by = self::REDIRECT_BY ) {
		if ( $nocache_headers ) {
			nocache_headers();
		}
		if ( wp_redirect( $url, $status, $x_redirect_by ) ) {
			die();
		}
	}

}