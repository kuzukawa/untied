<?php

namespace ACFCustomDatabaseTables\Utils;

class Arr {

	/**
	 * Extract the value for a particular array key if set or return a default if the key doesn't exist. If/where we
	 * don't need PHP support below 7.0, we could just use the null coalescing operator (??) but this is the
	 * alternative.
	 *
	 * @param array $array
	 * @param $key
	 * @param null $default
	 *
	 * @return mixed|null
	 */
	public static function get( array $array, $key, $default = null ) {
		return isset( $array[ $key ] ) ? $array[ $key ] : $default;
	}

	/**
	 * Resolves the value of a multi-dimensional array using dot notation.
	 *
	 * e.g; static::get(['a' => ['b' => 1]], 'a.b') => 1
	 *
	 * @param array $array
	 * @param string|array $key Dot-notated path to nested array value. If it is an array, items will be dot-notated. Can also just be a non-nested key.
	 * @param null $default
	 *
	 * @return array|mixed|null
	 */
	public static function get_deep( $array, $key, $default = null ) {
		$current = $array;

		if ( is_array( $key ) ) {
			$key = join( '.', $key );
		}

		$p = strtok( $key, '.' );

		while ( $p !== false ) {
			if ( ! isset( $current[ $p ] ) ) {
				return $default;
			}
			$current = $current[ $p ];
			$p = strtok( '.' );
		}

		return $current;
	}

	/**
	 * Sets the value within an array. Supports dot-notated keys.
	 *
	 * @param array $array
	 * @param string|array $key
	 * @param $value
	 *
	 * @return mixed
	 */
	public static function set_deep( &$array, $key, $value ) {
		if ( is_null( $key ) ) {
			return $array = $value;
		}

		$keys = is_array( $key ) ? $key : explode( '.', $key );

		while ( count( $keys ) > 1 ) {
			$key = array_shift( $keys );

			if ( ! isset( $array[ $key ] ) || ! is_array( $array[ $key ] ) ) {
				$array[ $key ] = [];
			}

			$array = &$array[ $key ];
		}

		$array[ array_shift( $keys ) ] = $value;

		return $array;
	}

	/**
	 * Return the values from a single column in the input array, identified by the $column_key.
	 *
	 * Optionally, an $index_key may be provided to index the values in the returned array by the
	 * values from the $index_key column in the input array.
	 *
	 * This supports objects which was added in PHP 7.0. This method can be dropped when support for PHP 5.x is dropped.
	 *
	 * @param array $input A list of arrays or objects from which to pull a column of values.
	 * @param string|int $column_key The column of values to return.
	 * @param string|int|null $index_key The column to use as the index/keys for the returned array.
	 *
	 * @return array
	 */
	public static function array_column( array $input, $column_key, $index_key = null ) {
		if ( PHP_MAJOR_VERSION > 5 ) {
			return array_column( $input, $column_key, $index_key );
		}
		$output = [];
		foreach ( $input as $row ) {
			$key = $value = null;
			$key_set = $value_set = false;
			if ( $index_key !== null ) {
				if ( is_array( $row ) && array_key_exists( $index_key, $row ) ) {
					$key_set = true;
					$key = (string) $row[ $index_key ];
				} elseif ( is_object( $row ) && isset( $row->{$index_key} ) ) {
					$key_set = true;
					$key = (string) $row->{$index_key};
				}
			}
			if ( $column_key === null ) {
				$value_set = true;
				$value = $row;
			} elseif ( is_array( $row ) && array_key_exists( $column_key, $row ) ) {
				$value_set = true;
				$value = $row[ $column_key ];
			} elseif ( is_object( $row ) && isset( $row->{$column_key} ) ) {
				$value_set = true;
				$value = $row->{$column_key};
			}
			if ( $value_set ) {
				if ( $key_set ) {
					$output[ $key ] = $value;
				} else {
					$output[] = $value;
				}
			}
		}

		return $output;
	}

	/**
	 * Check to see if all values in an associative array are empty.
	 *
	 * @param array $array
	 *
	 * @return bool
	 */
	public static function has_no_values( $array ) {
		foreach ( (array) $array as $item ) {
			if ( ! empty( $item ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check to see if a given array is associative
	 *
	 * @param array|mixed $array
	 *
	 * @return bool
	 */
	public static function is_associative( $array ) {
		if ( ! is_array( $array ) || $array === [] ) {
			return false;
		}

		return array_keys( $array ) !== range( 0, count( $array ) - 1 );
	}

	/**
	 * Locate an array index/key by value and remove it from the array.
	 *
	 * @param array $array
	 * @param mixed $value The value to look for within the given array.
	 *
	 * @return array
	 */
	public static function remove_value( array $array, $value ) {
		$key = array_search( $value, $array );

		if ( $key !== false ) {
			unset( $array[ $key ] );
		}

		return $array;
	}

	/**
	 * Interweave the values of two arrays in a zip or finger-join arrangement.
	 * e.g; self::zip([1,3], [2,4]) >>> [1,2,3,4]
	 *
	 * @param array $array_a
	 * @param array $array_b
	 *
	 * @return array
	 */
	public static function zip( array $array_a, array $array_b ) {
		$zipped = [];

		while ( null !== $n = array_shift( $array_a ) ) {
			array_push( $zipped, $n );

			if ( null !== $i = array_shift( $array_b ) ) {
				array_push( $zipped, $i );
			}
		}

		return $zipped;
	}

	/**
	 * Prefix each item within an array with a given string. Supports associative arrays.
	 *
	 * @param array $array
	 * @param string $prefix
	 *
	 * @return string[]
	 */
	public static function prefix_values( array $array, $prefix ) {
		foreach ( $array as &$item ) {
			$item = $prefix . $item;
		}

		return $array;
	}

	/**
	 * Add a value to an array if it doesn't already exist in the array.
	 *
	 * @param array $array
	 * @param mixed $value
	 *
	 * @return array
	 */
	public static function add_unique( array $array, $value ) {
		if ( in_array( $value, $array ) ) {
			return $array;
		}

		$array[] = $value;

		return $array;
	}

	/**
	 * Get a value from the array, and remove it.
	 *
	 * @param array $array
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public static function pull( array &$array, $key, $default = null ) {
		if ( array_key_exists( $key, $array ) ) {
			$value = $array[ $key ];
			unset( $array[ $key ] );
		} else {
			$value = $default;
		}

		return $value;
	}

}