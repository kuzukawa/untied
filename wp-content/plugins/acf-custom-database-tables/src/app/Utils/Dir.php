<?php

namespace ACFCustomDatabaseTables\Utils;

class Dir {

	/**
	 * @return string
	 */
	public static function get_json_dir() {

		if ( defined( 'ACFCDT_JSON_DIR' ) ) {
			return wp_normalize_path( ACFCDT_JSON_DIR );
		}

		if ( $acf_dir = self::get_acf_save_json_dir() ) {
			$dir = "{$acf_dir}/database-tables";

		} else if ( $uploads_dir = self::get_wp_uploads_basedir() ) {
			$dir = "{$uploads_dir}/acf-custom-database-tables";

		} else {
			$dir = '';

		}

		/**
		 * If you need to modify the base dir for all ACF Custom Database Tables JSON and cache files, you can do so by
		 * filtering this value.
		 *
		 * This needs to be modified before the 'plugins_loaded' hook, so you'll need to make this modification via a
		 * plugin. The reason for this is that the table map needs to be available as early as possible for custom data
		 * mapping to work.
		 *
		 * Note: If preferable, the directory can also be set using the ACFCDT_JSON_DIR constant in your wp-config.php
		 * file. That value will not be filterable.
		 *
		 */
		$dir = apply_filters( 'acfcdt/json_dir', wp_normalize_path( $dir ) );

		if ( ! $dir ) {
			Error::log( 'Could not establish a directory for ACF Custom Database Tables JSON. You should consider defining the path with the ACFCDT_JSON_DIR constant.' );
		}

		return $dir;
	}

	/**
	 * Gets the ACF JSON save dir, if it exists and is writable.
	 *
	 * @return string
	 */
	private static function get_acf_save_json_dir() {

		$dir = function_exists( 'acf_get_setting' )
			? acf_get_setting( 'save_json' )
			: '';

		return ( $dir and self::is_writable( $dir ) )
			? $dir
			: '';
	}

	/**
	 * Gets the WP Uploads base dir, if it exists and is writable.
	 *
	 * @return string
	 */
	private static function get_wp_uploads_basedir() {
		$uploads = wp_get_upload_dir();

		return ( isset( $uploads['basedir'] ) and self::is_writable( $uploads['basedir'] ) )
			? $uploads['basedir']
			: '';
	}

	/**
	 * Checks if a dir exists and is writable.
	 *
	 * @param $dir
	 *
	 * @return bool
	 */
	private static function is_writable( $dir ) {
		return ( file_exists( $dir ) and is_writable( $dir ) );
	}

}