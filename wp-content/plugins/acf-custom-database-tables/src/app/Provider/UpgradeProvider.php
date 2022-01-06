<?php

namespace ACFCustomDatabaseTables\Provider;

use ACFCustomDatabaseTables\Upgrade\RebuildMapSystemUpgrade;
use ACFCustomDatabaseTables\Upgrade\Upgrader;
use ACFCustomDatabaseTables\Vendor\Pimple\Container;
use ACFCustomDatabaseTables\Vendor\Pimple\ServiceProviderInterface;
use Closure;

class UpgradeProvider implements ServiceProviderInterface {

	public function register( Container $c ) {
		// Bind the upgrade classes.
		foreach ( $this->upgrade_definitions() as $key => $callback ) {
			$c[ $key ] = $callback;
		}

		// Bind the upgrader.
		$c[ Upgrader::class ] = function ( Container $c ) {
			$upgrader = new Upgrader();
			$upgrader->set_bindings( array_keys( $this->upgrade_definitions() ) );

			return $upgrader;
		};
	}

	public function boot( Container $c ) {
		// Currently, we run the upgrade checks on each admin page load. It might be beneficial to move to a database
		// version-based system where we have one single DB version for the whole plugin and when that is out of sync,
		// this class is queued up to run through all its updgrade checks.
		$c[ Upgrader::class ]->init();
	}

	/**
	 * Each class defined within these enclosures MUST implement the \ACFCustomDatabaseTables\Contract\UpgradeInterface
	 * interface. If they don't, the upgrader won't run them and will trigger an error.
	 *
	 * @return Closure[]
	 */
	private function upgrade_definitions() {
		return [
			// This isn't tied to a version so it should probably run either first or last, depending on whether or not
			// we decide to run this in amongst other upgrades. i.e; If run on a version update, it won't need to run
			// individually but if there isn't a version update to run, we may need to flag this as needing to run.
			RebuildMapSystemUpgrade::class => function ( Container $c ) {
				return new RebuildMapSystemUpgrade();
			},

			// Version-specific upgrades should be bound here in the order they need to run (ASC).

		];
	}

}