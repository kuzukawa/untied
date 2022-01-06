<?php

namespace ACFCustomDatabaseTables\Upgrade;

use ACFCustomDatabaseTables\Background\RebuildMapSystemBackgroundTask;
use ACFCustomDatabaseTables\Container;
use ACFCustomDatabaseTables\Contract\HasActivationRoutine;
use ACFCustomDatabaseTables\Contract\UpgradeInterface;
use ACFCustomDatabaseTables\Facade\Api;
use ACFCustomDatabaseTables\Facade\App;
use ACFCustomDatabaseTables\Facade\Settings;
use ACFCustomDatabaseTables\Model\ACFFieldGroup;
use ACFCustomDatabaseTables\Utils\Arr;
use ACFCustomDatabaseTables\Utils\Request;

/**
 * Rebuild the map system automatically based on the version set in the class.
 *
 * @package ACFCustomDatabaseTables\Upgrade
 */
class RebuildMapSystemUpgrade implements UpgradeInterface, HasActivationRoutine {

	const VERSION = 1628121600; // strtotime('5th August 2021')
	const VERSION_KEY = 'acfcdt-rebuild-map-system-upgrade-version';

	public function should_upgrade() {
		if ( ! is_admin() ) {
			return false;
		}

		// Don't run when a plugin is being deactivated.
		if ( Request::get( 'action' ) === 'deactivate' ) {
			return false;
		}

		return $this->db_version() < self::VERSION;
	}

	public function run_upgrade() {
		$this->start_background_task( $this->get_eligible_field_group_keys() );
		$this->update_db_version();
	}

	public function run_activation_routine( Container $c ) {
		if ( $this->is_first_activation() ) {
			$this->set_initial_version();
		}
	}

	private function is_first_activation() {
		if ( $this->db_version() > 0 ) {
			return false;
		}

		// If there is a table JSON directory in place, we can assume this plugin has been active previously and should
		// not set the initial version for this upgrade.
		if ( $this->table_json_directory_exists() ) {
			return false;
		}

		return true;
	}

	private function set_initial_version() {
		$this->update_db_version();
	}

	private function db_version() {
		return (int) get_option( self::VERSION_KEY, 0 );
	}

	private function update_db_version() {
		update_option( self::VERSION_KEY, self::VERSION );
	}

	private function table_json_directory_exists() {
		return is_dir( Settings::json_dir() );
	}

	/**
	 * Get an array of field group keys. A field group is eligible if it has a table JSON file on disk, a database
	 * table in the database, and the field group's custom table is enabled.
	 *
	 * @return array An array of field group keys.
	 */
	private function get_eligible_field_group_keys() {
		$keys = [];
		$groups = Api::get_field_groups_with_table_enabled();

		// todo - the checks in here would make sense as object methods. @see https://app.clickup.com/t/50vjk9
		foreach ( $groups as $group ) {
			// If field group does not have a table JSON file, skip.
			if ( ! Api::field_group_has_table_json_file( $group ) ) {
				continue;
			}

			// If a table name cannot be determined, skip.
			if ( ! $table_name = Arr::get( $group, ACFFieldGroup::TABLE_NAME_KEY, '' ) ) {
				continue;
			}

			// If the table does not exist in the database, skip.
			global $wpdb;
			$table_name = $wpdb->prefix . $table_name;
			$table_exists = $wpdb->get_results( $wpdb->prepare( "SHOW TABLES LIKE %s;", $table_name ) );
			if ( ! $table_exists ) {
				continue;
			}

			// If a field group is missing a key, for some reason, skip it.
			if ( empty( $group['key'] ) ) {
				continue;
			}

			$keys[] = $group['key'];
		}

		return $keys;
	}

	private function start_background_task( array $keys ) {
		/** @var RebuildMapSystemBackgroundTask $task */
		$task = App::make( RebuildMapSystemBackgroundTask::class );
		$task->run( [
			'batch_size' => 10,
			'field_group_keys' => $keys,
		] );
	}

}