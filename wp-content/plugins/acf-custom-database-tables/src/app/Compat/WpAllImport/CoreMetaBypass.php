<?php

namespace ACFCustomDatabaseTables\Compat\WpAllImport;

/**
 * Class CoreMetaBypass
 * @package ACFCustomDatabaseTables\Compat\WpAllImport
 *
 * Ensure core meta storage is bypassed if/when the plugin is configured to do so.
 */
class CoreMetaBypass {

	private $fields = [
		// 'user:{$user_id}:{$field_name}' => $field_array,
		// 'post:{$post_id}:{$field_name}' => $field_array,
	];

	/**
	 * @param int $object_id
	 * @param array $field_array
	 * @param string $type
	 */
	public function add_field( $object_id, array $field_array, $type = 'post' ) {
		if ( empty( $field_array['name'] ) ) {
			return;
		}

		$this->fields["$type:$object_id:{$field_array['name']}"] = $field_array;
	}

	public function init() {
		add_action( 'pmxi_before_post_import', [ $this, '_run' ] );
		add_action( 'pmxi_before_xml_import', [ $this, '_run' ] );
	}

	/**
	 * Note: if we need to support terms at any point, we also need a handler for `update_post_metadata`.
	 */
	public function _run() {
		if ( ! has_filter( 'update_user_metadata', [ $this, '_bypass_core_user_meta' ] ) ) {
			add_filter( 'update_user_metadata', [ $this, '_bypass_core_user_meta' ], 10, 5 );
		}

		if ( ! has_filter( 'update_post_metadata', [ $this, '_bypass_core_post_meta' ] ) ) {
			add_filter( 'update_post_metadata', [ $this, '_bypass_core_post_meta' ], 10, 5 );
		}
	}

	public function _bypass_core_user_meta( $null, $user_id, $meta_key, $meta_value, $prev_value ) {
		// If not flagged for bypass, return null and let the core meta system do its work.
		if ( ! isset( $this->fields["user:$user_id:$meta_key"] ) ) {
			return $null;
		}

		// Unset the key — we're handling this now and won't need it again.
		unset( $this->fields["user:$user_id:$meta_key"] );

		// Delete the field key reference as it has already been stored at this point by WP All Import.
		delete_user_meta( $user_id, "_$meta_key" );

		// Returning null allows the core meta data to store in core tables.
		return $null;
	}

	public function _bypass_core_post_meta( $null, $post_id, $meta_key, $meta_value, $prev_value ) {
		// If not flagged for bypass, return null and let the core meta system do its work.
		if ( ! isset( $this->fields["post:$post_id:$meta_key"] ) ) {
			return $null;
		}

		// Unset the key — we're handling this now and won't need it again.
		unset( $this->fields["post:$post_id:$meta_key"] );

		// Delete the field key reference as it has already been stored at this point by WP All Import.
		delete_post_meta( $post_id, "_$meta_key" );

		// Returning anything other than null will bypass the core storage.
		return true;
	}

}