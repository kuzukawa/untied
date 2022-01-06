<?php

namespace ACFCustomDatabaseTables\Utils;

class FieldValueEncoder {

	/**
	 * Encode a given value. If not encodable or already encoded – i.e; is a string – the value won't be encoded.
	 *
	 * @param mixed $value The value to encode.
	 * @param array $field_array The ACF field array.
	 *
	 * @return string|mixed
	 */
	public static function encode( $value, array $field_array = [] ) {
		/**
		 * Filters the value before it is encoded and saved in the database. This filter makes it possible to:
		 *  1. Convert string-based ints to actual integers for cleaner JSON encoded data, when $value is an
		 *     array. e.g; [1,2,3] instead of ["1","2","3"]
		 *  2. Control how non-scalar values are actually stored in the database by converting them to a string other
		 *     than the default encoded JSON. i.e; Serilization over JSON-encoding.
		 *
		 * If you use this filter and encode the data yourself, you'll also need to decode the data accordingly. See
		 * \ACFCustomDatabaseTables\Traits\EncodeHelpers::decode_value();
		 *
		 * @param mixed $value The value to be saved in the database
		 * @param array $field The ACF field array
		 */
		$value = apply_filters( 'acfcdt/filter_value_before_encode', $value, $field_array );

		// If the value is not encodeable, assume custom encoding has been applied and bypass JSON encoding by returning
		// early.
		if ( ! self::can_encode( $value ) ) {
			return $value;
		}

		/**
		 * Filter the value before it is JSON encoded. This filter makes it possible to escape/sanitize a value before
		 * it is encoded.
		 *
		 * @param mixed $value The value to be encoded.
		 * @param array $field The ACF field array.
		 */
		$value_pre_encoded = apply_filters( 'acfcdt/filter_value_before_json_encode', $value, $field_array );

		// JSON encode the value.
		$value_encoded = wp_json_encode(
			$value_pre_encoded,
			self::options( $field_array ),
			self::depth( $field_array )
		);

		// If encoding was not successful, return the original value passed in.
		return ( false === $value_encoded )
			? $value
			: $value_encoded;
	}

	/**
	 * Check whether a value is eligible for encoding or not.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public static function can_encode( $value ) {
		return is_array( $value ) || is_object( $value );
	}

	/**
	 * @param string $value
	 * @param array $field_array
	 *
	 * @return array|mixed
	 */
	public static function decode( $value, array $field_array = [] ) {
		/**
		 * Filters the value after it is read from the database and before it is decoded and returned. This
		 * filter makes it possible to decode any custom encoding applied via the `acfcdt/filter_value_before_encode`
		 * filter.
		 *
		 * @param string $value The value that was read from the database
		 * @param array $field The ACF field array
		 */
		$value = apply_filters( 'acfcdt/filter_value_before_decode', $value, $field_array );

		// If the value is not a string, assume custom decoding has been applied and bypass JSON decoding by returning
		// early.
		if ( ! is_string( $value ) ) {
			return $value;
		}

		$value_decoded = json_decode( $value, true );
		$decode_was_successful = is_array( $value_decoded ) && ( json_last_error() === JSON_ERROR_NONE );

		if ( $decode_was_successful ) {
			/**
			 * Filter the value after it is successfully decoded from JSON.
			 *
			 * @param mixed $value The value to be encoded.
			 * @param array $field The ACF field array.
			 */
			return apply_filters( 'acfcdt/filter_value_after_json_decode', $value_decoded, $field_array );
		}

		return $value;
	}

	/**
	 * @param array $field_array
	 *
	 * @return int
	 */
	public static function options( $field_array = [] ) {
		/**
		 * Filter the JSON encode options bitmask. If the JSON encode options are causing issues, you can change them
		 * by using this filter. @see https://www.php.net/manual/en/function.json-encode.php
		 */
		return apply_filters( 'acfcdt/filter_json_encode_bitmask', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE, $field_array );
	}

	/**
	 * @param array $field_array
	 *
	 * @return int
	 */
	public static function depth( $field_array = [] ) {
		/**
		 * Filter the JSON encode depth setting. @see https://www.php.net/manual/en/function.json-encode.php
		 */
		return apply_filters( 'acfcdt/filter_json_encode_depth', 512, $field_array );
	}

}