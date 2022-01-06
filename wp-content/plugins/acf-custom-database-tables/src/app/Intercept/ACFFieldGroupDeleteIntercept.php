<?php

namespace ACFCustomDatabaseTables\Intercept;

use ACFCustomDatabaseTables\Factory\ACFFieldGroupFactory;
use ACFCustomDatabaseTables\FileIO\TableJSONFileGenerator;

class ACFFieldGroupDeleteIntercept extends InterceptBase {

	/** @var ACFFieldGroupFactory */
	private $field_group_factory;

	/** @var TableJSONFileGenerator */
	private $table_JSON_file_generator;

	/** @var string The file name to attempt to delete */
	private $file_to_delete;

	/**
	 * ACFFieldGroupDeleteIntercept constructor.
	 *
	 * @param ACFFieldGroupFactory $field_group_factory
	 * @param TableJSONFileGenerator $table_JSON_file_generator
	 */
	public function __construct( ACFFieldGroupFactory $field_group_factory, TableJSONFileGenerator $table_JSON_file_generator ) {
		$this->field_group_factory = $field_group_factory;
		$this->table_JSON_file_generator = $table_JSON_file_generator;
	}

	public function init() {
		add_action( 'before_delete_post', [ $this, 'queue_file_for_deletion' ] );
		add_action( 'deleted_post', [ $this, 'delete_file' ] );
	}

	/**
	 * Gets the info we need before the post is deleted
	 *
	 * @param int $post_id
	 */
	public function queue_file_for_deletion( $post_id ) {

		if ( get_post_type( $post_id ) !== 'acf-field-group' ) {
			return;
		}

		$field_group = $this->field_group_factory->make_from_post_id( $post_id );
		$full_path = $this->table_JSON_file_generator->get_file_path( $field_group );

		if ( $full_path ) {
			$this->file_to_delete = $full_path;
		}
	}

	/**
	 * Deletes the files AFTER the post is deleted
	 */
	public function delete_file() {

		if ( $this->file_to_delete and file_exists( $this->file_to_delete ) and is_writable( $this->file_to_delete ) ) {
			unlink( $this->file_to_delete );
		}

	}

}