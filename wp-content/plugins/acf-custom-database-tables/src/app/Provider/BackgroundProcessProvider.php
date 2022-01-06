<?php

namespace ACFCustomDatabaseTables\Provider;

use ACFCustomDatabaseTables\Background\RebuildMapSystemBackgroundTask;
use ACFCustomDatabaseTables\Vendor\Pimple\Container;
use ACFCustomDatabaseTables\Vendor\Pimple\ServiceProviderInterface;

class BackgroundProcessProvider implements ServiceProviderInterface {

	public function register( Container $c ) {
		foreach ( $this->definitions() as $key => $callback ) {
			$c[ $key ] = $callback;
		}
	}

	public function boot( Container $c ) {
		// Make sure any background processes are listening.
		$c[ RebuildMapSystemBackgroundTask::class ]->listen();
	}

	private function definitions() {
		return [
			RebuildMapSystemBackgroundTask::class => function ( Container $c ) {
				return new RebuildMapSystemBackgroundTask;
			},
		];
	}
}