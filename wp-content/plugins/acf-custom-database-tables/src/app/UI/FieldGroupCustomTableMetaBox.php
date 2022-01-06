<?php

namespace ACFCustomDatabaseTables\UI;

use ACFCustomDatabaseTables\Factory\ACFFieldGroupFactory;
use ACFCustomDatabaseTables\Service\DiagnosticReporter;
use ACFCustomDatabaseTables\Service\TableNameValidator;
use ACFCustomDatabaseTables\Utils\View;

class FieldGroupCustomTableMetaBox {

	const ID = 'acfcdt-field-group-database-tables';
	const SCREEN = 'acf-field-group';
	const TITLE = 'ACF Custom Database Table Settings';

	/** @var ACFFieldGroupFactory */
	private $field_group_factory;

	/** @var DiagnosticReporter */
	private $diagnostic;

	/** @var TableNameValidator */
	private $table_name_validator;

	/**
	 * FieldGroupCustomTableMetaBox constructor.
	 *
	 * @param null $wpdb Deprecated — don't pass anything other than null.
	 * @param ACFFieldGroupFactory $field_group_factory
	 * @param DiagnosticReporter $diagnostic_reporter
	 * @param TableNameValidator $table_name_validator
	 */
	public function __construct( $wpdb, ACFFieldGroupFactory $field_group_factory, DiagnosticReporter $diagnostic_reporter, TableNameValidator $table_name_validator ) {
		if ( null !== $wpdb ) {
			_deprecated_argument( __METHOD__, '1.1 (ACF Custom Database Tables)', 'No longer injecting $wpdb due to object cache issues. Change this to NULL. Any related props will be removed in version 1.2' );
		}

		$this->field_group_factory = $field_group_factory;
		$this->diagnostic = $diagnostic_reporter;
		$this->table_name_validator = $table_name_validator;
	}

	/**
	 * Registers the meta box with WordPress
	 */
	public function register() {
		add_filter( 'postbox_classes_' . self::SCREEN . '_' . self::ID, [ $this, 'add_classes' ] );
		add_meta_box( self::ID, self::TITLE, [ $this, 'render' ], self::SCREEN, 'normal' );
	}

	/**
	 * Adds necessary ACF CSS class to meta box for style matching
	 *
	 * @param $classes
	 *
	 * @return array
	 */
	public function add_classes( $classes ) {
		$classes[] = 'acf-postbox';

		return $classes;
	}

	/**
	 * The meta box renderer.
	 *
	 * @param \WP_Post $post
	 */
	public function render( \WP_Post $post ) {

		if ( $this->diagnostic->system_passes() ) {
			$this->render_ui( $post );
		} else {
			$this->render_system_issue_content();
		}

	}

	/**
	 * Meta Box content when there is a system compatibility issue.
	 */
	private function render_system_issue_content() {
		View::render( 'meta-box-issue-content', [
			'system_checks_page_url' => admin_url( 'edit.php?post_type=acf-field-group&page=acf-custom-database-tables&acfcdt-section=system' )
		] );
	}

	/**
	 * The meta box content when all is well.
	 *
	 * @param \WP_Post $post
	 */
	private function render_ui( \WP_Post $post ) {
		global $wpdb;

		$field_group = $this->field_group_factory->make_from_post_object( $post );

		?>
		<div class="acfcdt-acf-fields acf-fields -left">
			<?php
			acf_render_field_wrap(
				[
					'label' => 'Manage Table Definition',
					'instructions' => 'Allow this field group to create/update an <em>ACF Custom Database Table</em> definition file',
					'type' => 'true_false',
					'name' => $field_group::MANAGE_TABLE_DEFINITION_KEY,
					'key' => $field_group::MANAGE_TABLE_DEFINITION_KEY,
					'prefix' => 'acf_field_group',
					'value' => $field_group->should_manage_table_definition() ? 1 : 0,
					'ui' => 1,
					'ui_on_text' => 'Yes',
					'ui_off_text' => 'No',
				],
				'div',
				'field'
			);
			acf_render_field_wrap(
				[
					'label' => 'Table Name',
					'instructions' => "The database table prefix will be added for you automatically. You can only use alpha-numeric characters, dollar signs, and underscores here. e.g; <strong>my_table</strong>, <strong>_my_table</strong></strong>, <strong>my_table_1234</strong></strong>, <strong>MyTable</strong>, <strong>\$my_table</strong></strong>",
					'type' => 'text',
					'name' => $field_group::TABLE_NAME_KEY,
					'key' => $field_group::TABLE_NAME_KEY,
					'prefix' => 'acf_field_group',
					'value' => $field_group->table_name(),
					'required' => 1,
					'prepend' => $wpdb->prefix,
					'readonly' => ( $field_group->table_name() and $field_group->has_unique_table_name() and $field_group->owns_table_name( $field_group->table_name() ) )
						? 1
						: 0,
					'placeholder' => 'e.g; book_meta',
					'conditional_logic' => [
						[
							[
								'field' => $field_group::MANAGE_TABLE_DEFINITION_KEY,
								'operator' => '==',
								'value' => '1'
							]
						]
					]
				],
				'div',
				'field'
			);
			acf_render_field_wrap(
				[
					'label' => 'Definition JSON File Name',
					'instructions' => "This is the generated file name within which the database table JSON will be saved.",
					'type' => 'text',
					'name' => $field_group::DEFINITION_FILE_NAME_KEY,
					'key' => $field_group::DEFINITION_FILE_NAME_KEY,
					'prefix' => 'acf_field_group',
					'value' => $field_group->definition_file_name(),
					'required' => 1,
					'append' => '.json',
					'readonly' => ( $field_group->definition_file_name() and $field_group->has_unique_file_name() )
						? 1
						: 0,
					'placeholder' => 'e.g; 4h3kd75h34fg-book-meta-table',
					'conditional_logic' => [
						[
							[
								'field' => $field_group::MANAGE_TABLE_DEFINITION_KEY,
								'operator' => '==',
								'value' => '1'
							]
						]
					],
					'wrapper' => [
						'class' => 'acfcdt-hidden'
					]
				],
				'div',
				'field'
			);
			?>
		</div>
		<?php
		View::render( 'meta-box-deactivation-note', [
			'help_section_url' => admin_url( 'edit.php?post_type=acf-field-group&page=acf-custom-database-tables&acfcdt-section=help' ),
		] );
	}

}