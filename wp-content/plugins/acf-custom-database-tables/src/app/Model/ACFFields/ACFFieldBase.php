<?php

namespace ACFCustomDatabaseTables\Model\ACFFields;

use ACFCustomDatabaseTables\Utils\FieldValueEncoder;

abstract class ACFFieldBase {

	const TYPE = '';

	/**
	 * Default values are duplicated from ACF core. @see \acf_validate_field().
	 *
	 * @var array
	 */
	private static $defaults = [
		'ID' => 0,
		'key' => '',
		'label' => '',
		'name' => '',
		'prefix' => '',
		'type' => 'text',
		'value' => null,
		'menu_order' => 0,
		'instructions' => '',
		'required' => false,
		'id' => '',
		'class' => '',
		'conditional_logic' => false,
		'parent' => 0,
		'wrapper' => []
	];

	/** @var array The ACF field array. */
	protected $field_array = [];

	/** @var mixed The field value. */
	protected $value = null;

	/** @var bool Whether this field type is supported. */
	protected $is_supported = true;

	/**
	 * @var bool Whether this field type's supported status can be modified via a filter. This is used to lock down
	 * some core fields until internal support lands in the plugin.
	 */
	protected $is_supported__filterable = true;

	/**
	 * Whether this field type's value should be processed by the field value encoder/decoder. This should basically
	 * always be true as it ensures any value type can be passed to update_field() without issue. If set to false,
	 * both $this->encode_value() and $this->decode_value() methods will be by short-circuited by default. Each have
	 * internal filters that allow finer control, however.
	 *
	 * @var bool
	 */
	protected $can_encode_value = true;

	/** @var bool Whether join tables can be used to store this field's value. */
	protected $can_create_join_tables = false;

	/** @var bool Whether sub tables can be used to store this field's value. */
	protected $can_create_sub_tables = false;

	public function __construct() {
		$this->field_array = self::$defaults;
		$this->field_array['type'] = static::TYPE;
	}

	/**
	 * Set an existing field array.
	 */
	public function set_field_array( array $field ) {
		$this->field_array = $field;
	}

	public function to_array() {
		return $this->field_array;
	}

	public function set_value( $value ) {
		$this->value = $value;
	}

	/**
	 * Set the raw value taken from the DB. Given we store JSON-encoded values and have no way of knowing what
	 * third-party field types might be operating with, this presumes a value could be encoded and runs it through the
	 * decode method where it is decoded, if possible.
	 *
	 * @param string|int $encoded_value
	 */
	public function set_value_from_db( $encoded_value ) {
		$this->set_value( $this->decode_value( $encoded_value ) );
	}

	/**
	 * @return bool
	 */
	public function is_supported() {
		// If a field's support status is specifically marked as unfilterable, return early so this can't be overridden
		// via the filter below. This is important as we have support coming for some fields and want to limit the
		// possibility of conflicts with any user-implemented workarounds.
		if ( $this->is_supported__filterable === false ) {
			return $this->is_supported;
		}

		/**
		 * Enables custom field types to register as supported. Enables the possibility of removing support for core
		 * field types.
		 *
		 * Note: the first variation of this filter is the legacy format — new filters within this object are prefixed
		 * with acfcdt/field/. todo - deprecate the legacy format. See https://app.clickup.com/t/8kc21t.
		 *
		 * @param bool $supported Whether or not the provided field is supported.
		 * @param array $field_array The ACF field array
		 */
		$supported = (bool) apply_filters( 'acfcdt/is_supported_field', $this->is_supported, $this->field_array );
		return (bool) apply_filters( 'acfcdt/field/is_supported', $supported, $this->field_array );
	}

	public function name() {
		return $this->get_field_array_value( 'name' );
	}

	public function type() {
		return $this->get_field_array_value( 'type', static::TYPE );
	}

	public function key() {
		return $this->get_field_array_value( 'key' );
	}

	public function is_a( $field_type ) {
		return $this->type() === $field_type;
	}

	public function has_sub_fields_array() {
		return isset( $this->field_array['sub_fields'] ) and $this->field_array['sub_fields'];
	}

	public function get_sub_fields_array() {
		if ( $this->has_sub_fields_array() ) {
			return $this->field_array['sub_fields'];
		}

		return [];
	}

	public function can_create_join_tables() {
		return $this->can_create_join_tables;
	}

	public function can_create_sub_tables() {
		return $this->can_create_sub_tables;
	}

	/**
	 * Always returns the raw value property.
	 *
	 * @return null|mixed
	 */
	public function get_value_raw() {
		return $this->value;
	}

	public function get_value() {
		return $this->value;
	}

	public function get_value_for_single_column() {
		return $this->is_encodable_value( $this->value )
			? $this->encode_value( $this->value )
			: $this->value;
	}

	public function get_value_for_join_table() {
		return $this->value;
	}

	/**
	 * Return an array of all field names that this field and sub-fields contain data for. For simple fields, this will
	 * only be one meta field name. For more complex fields – e.g; repeaters – this will be different.
	 *
	 * @return array
	 */
	public function get_meta_field_names() {
		return [ $this->name() ];
	}

	/**
	 * Return a flat array of all descendent field name patterns.
	 *
	 * @return array
	 */
	public function get_meta_field_name_match_patterns() {
		return [];
	}

	/**
	 * Access data property on the ACF field array
	 *
	 * @param $key
	 * @param string $default
	 *
	 * @return mixed|string
	 */
	protected function get_field_array_value( $key, $default = '' ) {
		return isset( $this->field_array[ $key ] )
			? $this->field_array[ $key ]
			: $default;
	}

	/**
	 * @return bool
	 */
	protected function is_encodable_value( $value ) {
		return FieldValueEncoder::can_encode( $value );
	}

	protected function encode_value( $value ) {
		return apply_filters( 'acfcdt/field/should_encode_value', $this->can_encode_value, $this->field_array, $value )
			? FieldValueEncoder::encode( $value, $this->field_array )
			: $value;
	}

	protected function decode_value( $value ) {
		return apply_filters( 'acfcdt/field/should_decode_value', $this->can_encode_value, $this->field_array, $value )
			? FieldValueEncoder::decode( $value, $this->field_array )
			: $value;
	}

}