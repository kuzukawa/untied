<?php

namespace ACFCustomDatabaseTables\Controller;

use ACFCustomDatabaseTables\Contract\ControllerInterface;
use ACFCustomDatabaseTables\Factory\ACFFieldGroupFactory;
use ACFCustomDatabaseTables\FileIO\TableJSONFileGenerator;
use ACFCustomDatabaseTables\Model\ACFFieldGroup;
use ACFCustomDatabaseTables\UI\AssetManager;
use ACFCustomDatabaseTables\UI\FieldGroupCustomTableMetaBox;
use ACFCustomDatabaseTables\UI\PersistentAdminNoticeHandler;
use ACFCustomDatabaseTables\Utils\View;

class ACFFieldGroupAdminController implements ControllerInterface {

	/** @var FieldGroupCustomTableMetaBox */
	private $custom_table_meta_box;

	/** @var ACFFieldGroupFactory */
	private $field_group_factory;

	/** @var ACFFieldGroup */
	private $field_group;

	/** @var PersistentAdminNoticeHandler */
	private $persistent_notifier;

	/** @var TableJSONFileGenerator */
	private $table_json_file_generator;

	/** @var AssetManager */
	private $asset_manager;

	/**
	 * ACFFieldGroupAdminController constructor.
	 *
	 * @param FieldGroupCustomTableMetaBox $custom_table_meta_box
	 * @param ACFFieldGroupFactory $field_group_factory
	 * @param PersistentAdminNoticeHandler $persistent_notifier
	 * @param TableJSONFileGenerator $table_json_file_generator
	 * @param AssetManager $asset_manager
	 */
	public function __construct( FieldGroupCustomTableMetaBox $custom_table_meta_box, ACFFieldGroupFactory $field_group_factory, PersistentAdminNoticeHandler $persistent_notifier, TableJSONFileGenerator $table_json_file_generator, AssetManager $asset_manager ) {
		$this->custom_table_meta_box = $custom_table_meta_box;
		$this->field_group_factory = $field_group_factory;
		$this->persistent_notifier = $persistent_notifier;
		$this->table_json_file_generator = $table_json_file_generator;
		$this->asset_manager = $asset_manager;
	}

	public function init() {
		add_action( 'load-post.php', [ $this, 'run' ] );
		add_action( 'load-post-new.php', [ $this, 'run' ] );
		add_filter( 'acf/validate_field_group', [ $this, 'sanitize_field_group_input_data' ] );
		add_action( 'current_screen', [ $this, 'update_post_meta_on_local_import' ] );
		add_action( 'acf/duplicate_field_group', [ $this, 'modify_duplicate_field_group' ] );
		add_filter( 'acf/update_field_group', [ $this, 'update_post_meta_on_acf_json_file_import' ] );
	}

	/**
	 * Context-specific initialisation
	 */
	public function run() {
		if ( $this->should_run() ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
			add_action( 'acf/update_field_group', [ $this, 'create_table_definition' ] );
			add_action( 'add_meta_boxes', [ $this->custom_table_meta_box, 'register' ] );
			add_action( 'save_post', [ $this, 'enforce_persistent_field_group_data' ], 9, 2 );
			$this->persistent_notifier->set_target_hook( 'edit_form_top' );
			$this->persistent_notifier->init();
			$this->persistent_notifier->restore();
		}
	}

	/**
	 * todo - move to ACFFieldGroupListController when controller available
	 *
	 * Handles data reset/removal on field group duplication – we don't want same table data carrying over, nor do we
	 * want to assume that a duplicate field group will have a table.
	 *
	 * @param $field_group_array
	 */
	public function modify_duplicate_field_group( $field_group_array ) {
		$field_group = $this->field_group_factory->make_from_field_group_array( $field_group_array );
		$field_group->reset_post_meta();
		$field_group_array[ $field_group::MANAGE_TABLE_DEFINITION_KEY ] = 0;
		$field_group_array[ $field_group::TABLE_NAME_KEY ] = '';
		$field_group_array[ $field_group::DEFINITION_FILE_NAME_KEY ] = '';
		acf_update_field_group( $field_group_array );
	}

	/**
	 * todo - move to ACFFieldGroupListController when controller available
	 *
	 * Ensures any ACFCDT data on the field group array is saved when the field group is imported from local JSON.
	 *
	 * @param \WP_Screen $screen
	 */
	public function update_post_meta_on_local_import( \WP_Screen $screen ) {

		if ( $screen->id !== 'edit-acf-field-group' ) {
			return;
		}

		if ( ! isset( $_GET['acfsynccomplete'] ) or ! $_GET['acfsynccomplete'] ) {
			return;
		}

		$group_ids = explode( ',', $_GET['acfsynccomplete'] );

		foreach ( $group_ids as $post_id ) {
			$field_group = $this->field_group_factory->make_from_post_id( $post_id );
			$field_group->update_post_meta_from_internal_field_group_settings();
		}
	}

	/**
	 * Enqueue scripts and stylesheets for this context
	 */
	public function enqueue_assets() {
		$this->asset_manager->enqueue_script( 'acfcdt-field-group' );
		$this->asset_manager->enqueue_style( 'acfcdt-admin' );
	}

	/**
	 * todo - can we possibly run this on the acf/validate_field_group hook instead?
	 *
	 * Hooks into field group save process before the post is inserted/updated in order to ensure our custom data points
	 * are stored inside the field group's content. This makes sure that, when a field group is restored from JSON, the
	 * newly created post object isn't missing the settings we store in post meta.
	 *
	 * @param $post_id
	 * @param \WP_Post $post
	 *
	 * @return mixed
	 */
	public function enforce_persistent_field_group_data( $post_id, \WP_Post $post ) {

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			 or $post->post_type !== 'acf-field-group'
			 or wp_is_post_revision( $post_id )
		) {
			return $post_id;
		}

		if ( isset( $_POST['acf_field_group']['acfcdt_manage_table_definition'] ) ) {

			$field_group = $this->field_group_factory->make_from_post_id( $post_id );
			$name = $field_group->table_name();
			$file_name = $field_group->definition_file_name();

			if ( ! isset( $_POST['acf_field_group']['acfcdt_table_name'] ) and $name ) {
				$_POST['acf_field_group']['acfcdt_table_name'] = $name;
			}

			if ( ! isset( $_POST['acf_field_group']['acfcdt_table_definition_file_name'] ) and $file_name ) {
				$_POST['acf_field_group']['acfcdt_table_definition_file_name'] = $file_name;
			}

		}

		return $post_id;
	}

	/**
	 * TODO - not entirely sure this should go here. Might need to figure out a better home for it.
	 *
	 * Sanitizes input data before it is saved to the DB.
	 *
	 * @param array $field_group ACF field group array
	 *
	 * @return array
	 */
	public function sanitize_field_group_input_data( $field_group ) {

		if ( $this->should_run() ) {

			/**
			 * We only need to get this to use the sanitization method, so this further enforces a move away from having
			 * sanitization inside the object. See \ACFCustomDatabaseTables\Model\ACFFieldGroup::sanitize_definition_file_name()
			 *
			 * If we use this for comparison, we're actually using the previous existing version or a draft, as a our factory
			 * makes this via a call to get_post(). This actually means we are sanitizing old data instead of the data
			 * that is incoming (passed into this method).
			 *
			 * Need to do some refactoring on this.
			 */
			$object = $this->get_field_group_object( $field_group );

			if ( $object ) {
				$a = $object::MANAGE_TABLE_DEFINITION_KEY;
				if ( isset( $field_group[ $a ] ) and $field_group[ $a ] ) {

					$b = $object::DEFINITION_FILE_NAME_KEY;
					if ( isset( $field_group[ $b ] ) ) {
						$field_group[ $b ] = $object->sanitize_definition_file_name( $field_group[ $b ] );
					}

				}
			}
		}

		return $field_group;
	}

	/**
	 * Generates the table definition JSON file for the field group. This runs after the field group post object has
	 * been updated in the DB and before the meta box is registered.
	 *
	 * @param array $acf_field_group_array The ACF field group array
	 */
	public function create_table_definition( $acf_field_group_array ) {

		$field_group_object = $this->get_field_group_object( $acf_field_group_array, true );

		if (
			! isset( $acf_field_group_array[ $field_group_object::MANAGE_TABLE_DEFINITION_KEY ] )
			or ! $acf_field_group_array[ $field_group_object::MANAGE_TABLE_DEFINITION_KEY ]
		) {
			$field_group_object->should_manage_table_definition( false );

			return;
		}

		if ( ! $field_group_object->has_unique_table_name() ) {
			$results = new \WP_Error( 'acfcdt', 'The table name has already been used and can not be modified by this field group.' );
		} elseif ( ! $field_group_object->has_unique_file_name() ) {
			$results = new \WP_Error( 'acfcdt', 'The table definition file name has already been used on another field group.' );
		} else {
			$results = $this->table_json_file_generator->generate_from_field_group( $field_group_object );
		}

		if ( is_wp_error( $results ) ) {
			$this->persistent_notifier->add_error( 'Database table definition JSON file could not be created. <br><strong>Error message:</strong> <em>' . $results->get_error_message() . '</em>' );
		} else {

			$field_group_object->update_post_meta_from_field_group_array( $acf_field_group_array );

			$fields = [
				[
					'title' => 'File:',
					'content' => $results['file'],
				],
				[
					'title' => 'Table Name:',
					'content' => $results['definition']['name'],
				],
				[
					'title' => 'Relationship:',
					'content' => ( $results['definition']['relationship']['type'] === 'user' )
						? 'Links to users'
						: "Links to posts with post type <em>{$results['definition']['relationship']['type']}</em>",
				],
				[
					'title' => 'Columns:',
					'content' => implode( '<br>', array_map( function ( $column ) {
						$type = sprintf( '&nbsp;&nbsp;<small style="color:#666;">[<strong>%s</strong>]</small>', isset( $column['type'] ) ? $column['type'] : 'longtext' );
						if ( is_string( $column ) ) {
							return $column . $type;
						} elseif ( ! isset( $column['map']['identifier'] ) ) {
							return $column['name'] . $type;
						} elseif ( $column['name'] === $column['map']['identifier'] ) {
							return $column['name'] . $type;
						} else {
							return sprintf( '%s&nbsp;&nbsp;%s&nbsp;&nbsp;<small style="color:#666;">(Field name: <strong>%s</strong>)</small>', $column['name'], $type, $column['map']['identifier'] );
						}
					}, $results['definition']['columns'] ) ),
				]
			];

			if ( isset( $results['definition']['join_tables'] ) and $results['definition']['join_tables'] ) {
				$fields[] = [
					'title' => 'Join Tables:',
					'content' => implode( '<br>', array_map( function ( $join_table ) {
						return $join_table['name'];
					}, $results['definition']['join_tables'] ) ),
				];
			}

			if ( isset( $results['definition']['sub_tables'] ) and $results['definition']['sub_tables'] ) {
				$fields[] = [
					'title' => 'Sub Tables:',
					'content' => implode( '<br>', array_map( function ( $sub_table ) {
						return $sub_table['name'];
					}, $results['definition']['sub_tables'] ) ),
				];
			}

			$message_content = View::prepare( 'table-definition-creation-notice-content', [
				'action' => $results['action'],
				'fields' => $fields,
				'button_url' => admin_url( 'edit.php?post_type=acf-field-group&page=acf-custom-database-tables' ),
			] );

			$this->persistent_notifier->add_success( $message_content );
		}

		$this->persistent_notifier->store();
	}

	/**
	 * Runs on import of ACF JSON file (via admin tools) after the field group post has been inserted/updated. This
	 * makes sure the field group has the table definition data in post meta as long as another field group does not
	 * exist with the same table name.
	 *
	 * @param $acf_field_group_array
	 */
	public function update_post_meta_on_acf_json_file_import( $acf_field_group_array ) {

		if ( ! $this->is_acf_json_import_running() ) {
			return;
		}

		$field_group = $this->field_group_factory->make_from_field_group_array( $acf_field_group_array );

		if ( ! $field_group->another_field_group_owns_table_name() ) {
			$field_group->update_post_meta_from_internal_field_group_settings();
		}
	}

	/**
	 * Context check to see if we are on the field group admin screen
	 *
	 * @return bool
	 */
	private function should_run() {
		global $typenow;

		return $typenow === 'acf-field-group'
			   and ! $this->is_acf_json_import_running();
	}

	/**
	 * Gets the field group from internal property or calls property update method if not yet available
	 *
	 * @param $acf_field_group_array
	 *
	 * @param bool $force_update Bypasses internal cache
	 *
	 * @return ACFFieldGroup
	 */
	private function get_field_group_object( $acf_field_group_array, $force_update = false ) {
		if ( $force_update or ! $this->field_group ) {
			$this->update_field_group_prop( $acf_field_group_array );
		}

		return $this->field_group;
	}

	/**
	 * This method always updates the internal field_group prop with the latest field group data
	 *
	 * @param $acf_field_group_array
	 */
	private function update_field_group_prop( $acf_field_group_array ) {
		$post = get_post( $acf_field_group_array['ID'] );

		if ( $post instanceof \WP_Post ) {
			$this->field_group = $this->field_group_factory->make_from_post_object( $post );
		}
	}

	private function is_acf_json_import_running() {
		return isset( $_FILES['acf_import_file'] );
	}

}