<?php

namespace ACFCustomDatabaseTables\Model;

use ACFCustomDatabaseTables\Utils\Arr;
use WP_Post;

/**
 * Class FieldGroup
 *
 * Internal field group object. This is not necessarily a full parity representation of an ACF field group but, instead,
 * has all data/methods required by this plugin.
 *
 * todo - methods that act as both setters and getter should be split. Maybe we deprecate method args in these cases.
 *
 * @see \ACFCustomDatabaseTables\Factory\ACFFieldGroupFactory for object creation.
 *
 */
class ACFFieldGroup {

	const MANAGE_TABLE_DEFINITION_KEY = 'acfcdt_manage_table_definition';
	const TABLE_NAME_KEY = 'acfcdt_table_name';
	const DEFINITION_FILE_NAME_KEY = 'acfcdt_table_definition_file_name';

	/** @var WP_Post $post */
	private $post;

	/** @var array Unserialized post content from ACF field group post type */
	private $settings = [];

	/** @var array Registered fields on the field group (post meta) */
	private $fields = [];

	/** @var bool */
	private $has_unique_table_name;

	/** @var bool */
	private $has_unique_file_name;

	/** @var string */
	private $unique_file_name;

	/**
	 * This is the complete field group array which provides some degree of support for using local field groups. Local
	 * field group support needs work so this is considered incomplete for now. First added to facilitate support for
	 * map system rebuilds.
	 *
	 * @var array
	 */
	private $field_group = [];

	/**
	 * @param WP_Post|array $field_group An instance of a field group post, or a field group array.
	 */
	public function __construct( $field_group = null ) {
		if ( $field_group instanceof WP_Post ) {
			$this->post = $field_group;
			$this->settings = (array) maybe_unserialize( $field_group->post_content );
		} elseif ( is_array( $field_group ) ) {
			$this->field_group = $field_group;
		}
	}

	/**
	 * Gets a setting from ACF field group
	 *
	 * @param $setting_name
	 * @param string $default
	 *
	 * @return mixed|string
	 */
	public function get_setting( $setting_name, $default = '' ) {
		// Try the settings array.
		$setting = Arr::get( $this->settings, $setting_name, $default );

		// If not found in the settings array, try the field group array.
		if ( $setting === $default ) {
			$setting = Arr::get( $this->field_group, $setting_name, $default );
		}

		return $setting;
	}

	/**
	 * Set the value of a given setting.
	 *
	 * @param string $settings_name
	 * @param mixed $value
	 */
	public function set_setting( $settings_name, $value ) {
		$this->settings[ $settings_name ] = $value;
	}

	/**
	 * Conditional check to see if table is active for this field group. Functions as setter if bool provided as arg.
	 *
	 * @param null $bool
	 *
	 * @return bool
	 */
	public function should_manage_table_definition( $bool = null ) {
		// If value provided, set it.
		if ( is_bool( $bool ) ) {
			if ( $this->post_id() ) {
				update_post_meta( $this->post_id(), self::MANAGE_TABLE_DEFINITION_KEY, $bool );
			}

			$this->set_setting( self::MANAGE_TABLE_DEFINITION_KEY, $bool );

			return $bool;
		}

		// Try fetching value from post meta.
		if ( $this->post_id() ) {
			$bool = (bool) get_post_meta( $this->post_id(), self::MANAGE_TABLE_DEFINITION_KEY, true );
		}

		// Try local setting from field group, if post meta unavailable.
		if ( ! $bool ) {
			$bool = (bool) $this->get_setting( self::MANAGE_TABLE_DEFINITION_KEY );
		}

		return $bool;
	}

	/**
	 * Internal meta handler for table name. This only acts on post meta.
	 *
	 * @param null|string $name If null, acts as a getter. If string provided, acts as a setter.
	 *
	 * @return mixed
	 */
	private function stored_table_name( $name = null ) {
		// If value is provided and we have a post object, update the meta.
		if ( is_string( $name ) and $this->post_id() ) {
			update_post_meta( $this->post_id(), self::TABLE_NAME_KEY, $name );
		}

		// Try fetching from post meta.
		if ( $this->post_id() ) {
			return get_post_meta( $this->post_id(), self::TABLE_NAME_KEY, true );
		}

		return $name;
	}

	/**
	 * Sets/gets custom table name, minus the $wpdb->prefix
	 *
	 * @param null|string $name
	 *
	 * @return string
	 */
	public function table_name( $name = null ) {
		if ( is_string( $name ) ) {
			$this->stored_table_name( $name );
			$this->set_setting( self::TABLE_NAME_KEY, $name );
		}

		// Try fetching value from post meta.
		if ( $this->post_id() ) {
			$name = get_post_meta( $this->post_id(), self::TABLE_NAME_KEY, true );
		}

		// Try local setting from field group, if post meta unavailable.
		if ( ! $name ) {
			$name = $this->get_setting( self::TABLE_NAME_KEY );
		}

		return $name;
	}

	/**
	 * Get the fields array.
	 *
	 * @return array
	 */
	public function fields() {
		if ( empty( $this->fields ) ) {
			$this->fields = acf_get_fields( $this->get_field_group_array() );
		}

		return $this->fields ?: [];
	}

	/**
	 * @return array
	 */
	private function get_field_group_array() {
		if ( $this->post_id() and is_array( $field_group = acf_get_field_group( $this->post_id() ) ) ) {
			return $field_group;
		}

		return (array) $this->field_group;
	}

	/**
	 * @return array
	 */
	public function to_array() {
		return $this->get_field_group_array();
	}

	public function reset_post_meta() {
		if ( $this->post_id() ) {
			delete_post_meta( $this->post_id(), self::MANAGE_TABLE_DEFINITION_KEY );
			delete_post_meta( $this->post_id(), self::TABLE_NAME_KEY );
			delete_post_meta( $this->post_id(), self::DEFINITION_FILE_NAME_KEY );
		}
	}

	/**
	 * todo - consider moving this into a separate object
	 *
	 * @param string $file_name
	 *
	 * @return string
	 */
	public function sanitize_definition_file_name( $file_name ) {
		$file_name = sanitize_file_name( $file_name );
		$extension = pathinfo( $file_name, PATHINFO_EXTENSION );

		// Strip the extension.
		if ( $extension ) {
			/** @var string $file_name */
			$file_name = str_replace( '.' . $extension, '', $file_name );
		}

		return $file_name;
	}

	/**
	 * @param null|string $file_name
	 *
	 * @return mixed
	 */
	public function definition_file_name( $file_name = null ) {
		// If provided, set the definition file name.
		if ( is_string( $file_name ) ) {
			if ( $this->post_id() ) {
				update_post_meta( $this->post_id(), self::DEFINITION_FILE_NAME_KEY, $file_name );
			}
			$this->set_setting( self::DEFINITION_FILE_NAME_KEY, $file_name );
		}

		// Try retrieving from local prop.
		if ( $this->unique_file_name ) {
			return $this->unique_file_name;
		}

		// Try retrieving from post meta.
		if ( $this->post_id() and $name = get_post_meta( $this->post_id(), self::DEFINITION_FILE_NAME_KEY, true ) ) {
			return $name;
		}

		// Try retrieving from local setting.
		if ( $name = $this->get_setting( self::DEFINITION_FILE_NAME_KEY ) ) {
			return $name;
		}

		// Generate a new one, if need be.
		return $this->generate_file_name();
	}

	/**
	 * @return int
	 */
	public function post_modified_time() {
		if ( $this->post_id() and $time = (int) get_post_modified_time( 'U', true, $this->post_id(), true ) ) {
			return $time;
		}

		return 0;
	}

	/**
	 * Generates a file name for this field group
	 *
	 * @return string
	 */
	private function generate_file_name() {
		if ( ! $this->unique_file_name ) {
			$this->unique_file_name = uniqid( "table_{$this->post_id()}x" );
		}

		return $this->unique_file_name;
	}

	/**
	 * @param array $field_group_array An ACF field group array.
	 */
	public function update_post_meta_from_field_group_array( $field_group_array ) {
		if ( isset( $field_group_array[ self::MANAGE_TABLE_DEFINITION_KEY ] ) ) {
			$this->should_manage_table_definition( (bool) $field_group_array[ self::MANAGE_TABLE_DEFINITION_KEY ] );
		}

		if ( isset( $field_group_array[ self::TABLE_NAME_KEY ] ) ) {
			$this->table_name( $field_group_array[ self::TABLE_NAME_KEY ] );
		}

		if ( isset( $field_group_array[ self::TABLE_NAME_KEY ] ) ) {
			$this->definition_file_name( $field_group_array[ self::DEFINITION_FILE_NAME_KEY ] );
		}
	}

	/**
	 * Looks to the field group settings and saves those in post meta for the field group.
	 */
	public function update_post_meta_from_internal_field_group_settings() {
		if ( $setting = $this->get_setting( self::MANAGE_TABLE_DEFINITION_KEY ) ) {
			$this->should_manage_table_definition( (bool) $setting );
		}

		if ( $setting = $this->get_setting( self::TABLE_NAME_KEY ) ) {
			$this->table_name( $setting );
		}

		if ( $setting = $this->get_setting( self::DEFINITION_FILE_NAME_KEY ) ) {
			$this->definition_file_name( $setting );
		}
	}

	/**
	 * If any other field group posts are found with the same table name, returns false
	 *
	 * Only checks field group data at this time, so it is still possible for a dev to manually create tables with the
	 * same name by creating definition files manually. This would result in data conflicts, so we should work on that
	 * for a future version.
	 *
	 * @return bool
	 */
	public function has_unique_table_name() {
		if ( ! $this->has_unique_table_name ) {
			$this->has_unique_table_name = true;

			// very rough here...
			global $wpdb;
			$table_name = $this->table_name();
			if ( ! $this->owns_table_name( $table_name ) ) {
				$table_exists = $wpdb->get_results( $wpdb->prepare( "SHOW TABLES LIKE %s;", $wpdb->prefix . $table_name ) );
				if ( $table_exists or $this->another_field_group_owns_table_name() ) {
					$this->has_unique_table_name = false;
				}
			}
		}

		return $this->has_unique_table_name;
	}

	/**
	 * Note: this doesn't check local field groups so that's a limitation to consider for a future release when working
	 * on local field group (PHP or JSON) support.
	 *
	 * @return bool
	 */
	public function another_field_group_owns_table_name() {
		return (bool) get_posts( [
			'post_type' => 'acf-field-group',
			'post__not_in' => [ $this->post_id() ],
			'no_found_rows' => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields' => 'ids',
			'meta_query' => [
				[
					'key' => self::TABLE_NAME_KEY,
					'value' => $this->table_name(),
				]
			]
		] );
	}

	/**
	 * If any other field group posts are found with the same file name, returns false
	 *
	 * Only checks field group data at this time, so it is still possible for a dev to manually create tables with the
	 * same name by creating definition files manually. This would result in data conflicts, so we should work on that
	 * for a future version.
	 *
	 * @return bool
	 */
	public function has_unique_file_name() {
		if ( ! $this->has_unique_file_name ) {
			$this->has_unique_file_name = ! get_posts( [
				'post_type' => 'acf-field-group',
				'post__not_in' => [ $this->post_id() ],
				'no_found_rows' => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'fields' => 'ids',
				'meta_query' => [
					[
						'key' => self::DEFINITION_FILE_NAME_KEY,
						'value' => $this->definition_file_name(),
					]
				]
			] );
		}

		return $this->has_unique_file_name;
	}

	/**
	 * Checks if this field group already owns the table name. i.e; has the table saved in the DB.
	 *
	 * @param string $table_name Unprefixed table name
	 *
	 * @return bool
	 */
	public function owns_table_name( $table_name ) {
		if ( $stored = $this->stored_table_name() ) {
			return $stored === $table_name;
		}

		return false;
	}

	/**
	 * If there is a post object available, return its title.
	 *
	 * @return string
	 */
	public function title() {
		return $this->post() ? $this->post()->post_title : '';
	}

	/**
	 * If there is a post object available, return it.
	 *
	 * @return WP_Post|null
	 */
	private function post() {
		return $this->post instanceof WP_Post ? $this->post : null;
	}

	/**
	 * @return int
	 */
	private function post_id() {
		return $this->post() ? $this->post()->ID : 0;
	}

}