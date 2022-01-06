<?php

namespace ACFCustomDatabaseTables\Tools;

use ACFCustomDatabaseTables\AdminPost\RebuildMapSystemAdminPost;
use ACFCustomDatabaseTables\Background\RebuildMapSystemBackgroundTask;
use ACFCustomDatabaseTables\Controller\SettingsPageController;
use ACFCustomDatabaseTables\Facade\Api;
use ACFCustomDatabaseTables\Model\ACFFieldGroup;
use ACFCustomDatabaseTables\Utils\Arr;
use ACFCustomDatabaseTables\Utils\View;

class RebuildMapSystemTool extends ToolBase {

	/** @var RebuildMapSystemBackgroundTask */
	private $task;

	/** @var SettingsPageController */
	private $settings_page;

	/** @var RebuildMapSystemAdminPost */
	private $submit;

	/**
	 * @param RebuildMapSystemBackgroundTask $task
	 * @param SettingsPageController $settings_page
	 * @param RebuildMapSystemAdminPost $submit
	 */
	public function __construct( RebuildMapSystemBackgroundTask $task, SettingsPageController $settings_page, RebuildMapSystemAdminPost $submit ) {
		$this->task = $task;
		$this->settings_page = $settings_page;
		$this->submit = $submit;
	}

	public function slug() {
		return 'rebuild-map-system';
	}

	public function name() {
		return 'Rebuild Map System';
	}

	public function description() {
		$output = sprintf( '<p>Rebuild table definition JSON files and update the internal data/table map. The 
			map is crucial in routing data in and out of custom database tables. This does not modify the database. 
			<br><a href="%s" class="acfcdt-external" target="_blank">Learn more</a></p>', esc_url( $this->doc_url() ) );

		if ( $this->task->is_running() ) {
			$output .= sprintf( '<p><a class="button-primary acfcdt-button-loading" href="%s">Running — View Status</a></p>', esc_url( $this->status_url() ) );
		} else {
			$output .= sprintf( '<p><a class="button-primary" href="%s">Select Field Groups</a></p>', esc_url( $this->url() ) );

			// If we have a last run date, show it here and link to status.
			if ( $this->task->is_done() ) {
				$output .= sprintf(
					'<p class="acfcdt-tools__card-footnote acfcdt-text-small">Last completed at %s &rsaquo; <a href="%s">View status</a></p>',
					$this->task->time_completed(),
					esc_url( $this->status_url() )
				);
			}
		}

		return $output;
	}

	public function render() {
		// Only show the status page if the task is currently active OR if the status was specifically requested and the
		// task has something to show. Ideally, we would redirect where there is no status available to show but headers
		// have already been sent at this time. An early running router would be a nice addition to handle these types
		// of situations.
		if ( $this->task->is_running() or ( $this->task->is_done() and $this->status_requested() ) ) {
			$inner = View::prepare( 'tools/rebuild-map-system/migrate', [
				'percentage' => $this->task->percent_complete(),
				'info' => $this->task->snapshot(),
			] );

		} else {
			$inner = View::prepare( 'tools/rebuild-map-system/config', [
				'action' => $this->submit->url(),
				'nonce_field' => $this->submit->nonce_field(),
				'next' => $this->status_url(),
				'manage_url' => $this->settings_page->section_url( 'manage' ),
				'options' => call_user_func( function () {
					$groups = array_map( function ( $field_group ) {
						// todo - these would make sense as object methods. @see https://app.clickup.com/t/50vjk9
						$table_enabled = Arr::get( $field_group, ACFFieldGroup::MANAGE_TABLE_DEFINITION_KEY, false );
						$has_json = Api::field_group_has_table_json_file( $field_group );
						$json_file = $has_json ? Api::get_field_group_table_json_file_name( $field_group ) : '';
						$json_path = $has_json ? Api::get_field_group_table_json_path( $field_group ) : '';
						$table_name = Arr::get( $field_group, ACFFieldGroup::TABLE_NAME_KEY, '' );
						if ( $table_name ) {
							global $wpdb;
							$table_name = $wpdb->prefix . $table_name;
							$table_exists = $wpdb->get_results( $wpdb->prepare( "SHOW TABLES LIKE %s;", $table_name ) );
						} else {
							$table_exists = false;
						}

						return [
							'key' => $field_group['key'],
							'title' => $field_group['title'],
							'checked' => ( $has_json and $table_enabled and $table_exists ),
							'is_active' => Arr::get( $field_group, 'active', false ),
							'table_json_file' => $json_file,
							'table_json_path' => $json_path,
							'table_name' => $table_name,
							'table_exists' => $table_exists,
						];
					}, Api::get_field_groups_with_table_enabled() );

					usort( $groups, function ( $a, $b ) {
						return $a['checked'] ? - 1 : 1;
					} );

					return $groups;
				} )
			] );
		}

		return View::prepare( 'tools/rebuild-map-system', [
			'back_link' => $this->back_url(),
			'inner' => $inner,
		] );
	}

	public function url() {
		return $this->settings_page->tool_url( $this->slug() );
	}

	public function status_url() {
		return add_query_arg( 'acfcdt-subview', 'status', $this->url() );
	}

	/**
	 * Check whether the browser request is specifically for the status page.
	 *
	 * @return bool
	 */
	private function status_requested() {
		return isset( $_REQUEST['acfcdt-subview'] ) and $_REQUEST['acfcdt-subview'] === 'status';
	}

	/**
	 * Check to see if this tool is the current page.
	 *
	 * @return bool
	 */
	public function is_current_page() {
		$current_uri = pathinfo( $_SERVER['REQUEST_URI'], PATHINFO_BASENAME );

		// Remove the sub view query arg — all subviews are part of the tool.
		$current_uri = remove_query_arg( 'acfcdt-subview', $current_uri );

		return $current_uri === pathinfo( $this->url(), PATHINFO_BASENAME );
	}

	public function doc_url() {
		return 'https://hookturn.io/docs/acf-custom-database-tables/1.1/rebuilding-the-map-system/';
	}

	private function back_url() {
		return $this->settings_page->section_url( 'tools' );
	}
}