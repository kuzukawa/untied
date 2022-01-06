<?php

namespace ACFCustomDatabaseTables\Upgrade;

use ACFCustomDatabaseTables\Contract\UpgradeInterface;
use ACFCustomDatabaseTables\Facade\App;
use ACFCustomDatabaseTables\Utils\Error;

/**
 * Coordinate upgrades. This class loops through a list of container bindings, checking each one to see if an upgrade
 * is required.
 *
 * @package ACFCustomDatabaseTables\Upgrade
 */
class Upgrader {

	private $bindings = [];

	/**
	 * @param array $bindings
	 */
	public function set_bindings( array $bindings ) {
		$this->bindings = $bindings;
	}

	public function init() {
		// The upgrader needs to run on init as this ensures we have access to ACFs APIs and those APIs function
		// correctly. e.g; acf_get_field_groups() doesn't return all field groups unless we hook our upgrader further
		// along the lifecycle than our boot() method.
		add_action( 'admin_init', [ $this, '_run_upgrades' ] );
	}

	public function _run_upgrades() {
		foreach ( $this->bindings as $class ) {

			/** @var UpgradeInterface $upgrade */
			$upgrade = App::make( $class );
			if ( ! $upgrade instanceof UpgradeInterface ) {
				Error::log( sprintf( 'Can not run upgrade [%s] — does not implement [%s]', get_class( $upgrade ), UpgradeInterface::class ) );
				continue;
			}

			if ( $upgrade->should_upgrade() ) {
				$upgrade->run_upgrade();

				// If we only prompt one at a time, break the loop. If we come up against this need, we should add a
				// method to our upgrade interface that checks whether the upgrade should prevent others from running.
				// Or, maybe we just have a check in place that only breaks this loop if an upgrade requires user
				// permission to run (via an admin banner prompt).
				//break;
			}
		}
	}

}