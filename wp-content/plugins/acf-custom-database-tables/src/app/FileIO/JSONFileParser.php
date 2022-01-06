<?php

namespace ACFCustomDatabaseTables\FileIO;

class JSONFileParser {

	/**
	 * Multidimensional array containing the contents of all read files
	 *
	 * @var array
	 */
	protected $decoded_file_contents = [];

	/**
	 * Loops through all .json files in specified directory, passing each to the JSONFileParser::read_file() method
	 *
	 * @param $dir_path
	 *
	 * @return bool
	 */
	function read_files_in_dir( $dir_path ) {

		$dir_path = untrailingslashit( $dir_path );

		if ( ! is_dir( $dir_path ) ) {
			return false;
		}

		$resource = opendir( $dir_path );

		while ( ( $file = readdir( $resource ) ) !== false ) {
			$this->read_file( "$dir_path/$file" );
		}

		closedir( $resource );

		return true;
	}

	/**
	 * Gets the content of a json file,
	 *
	 * @param $file_path
	 *
	 * @return bool
	 */
	function read_file( $file_path ) {

		if ( ! file_exists( $file_path ) or pathinfo( $file_path, PATHINFO_EXTENSION ) !== 'json' ) {
			return false;
		}

		$json = file_get_contents( $file_path );

		if ( empty( $json ) ) {
			return false;
		}

		$decoded = json_decode( $json, true );

		if ( $decoded !== null ) {
			$this->decoded_file_contents[] = $decoded;

			return true;
		}

		return false;
	}

	/**
	 * Returns the read_files array property
	 *
	 * @return array
	 */
	function decoded_file_contents() {
		return $this->decoded_file_contents;
	}

	/**
	 * Clears out data props
	 */
	function clear() {
		$this->decoded_file_contents = [];
	}

}