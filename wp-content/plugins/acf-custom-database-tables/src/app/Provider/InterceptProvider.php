<?php

namespace ACFCustomDatabaseTables\Provider;

use ACFCustomDatabaseTables\Cache\DataCache;
use ACFCustomDatabaseTables\Coordinator\InterceptCoordinator;
use ACFCustomDatabaseTables\Coordinator\TableCoordinator;
use ACFCustomDatabaseTables\Intercept\PostDeleteIntercept;
use ACFCustomDatabaseTables\Intercept\UserDeleteIntercept;
use ACFCustomDatabaseTables\Settings;
use ACFCustomDatabaseTables\Vendor\Pimple\Container;
use ACFCustomDatabaseTables\Vendor\Pimple\ServiceProviderInterface;

class InterceptProvider implements ServiceProviderInterface {

	/**
	 * @param Container $c
	 */
	public function register( Container $c ) {
		foreach ( $this->definitions() as $key => $callback ) {
			$c[ $key ] = $callback;
		}
	}

	/**
	 * @param Container $c
	 */
	public function boot( Container $c ) {
		/** @var InterceptCoordinator $coord */
		$coord = $c[ InterceptCoordinator::class ];

		$coord->register_intercept( $c[ PostDeleteIntercept::class ] ); // todo - evaluate and work out whether we can/should move functionality into field specific intercepts
		$coord->register_intercept( $c[ UserDeleteIntercept::class ] ); // todo - evaluate and work out whether we can/should move functionality into field specific intercepts
	}

	/**
	 * Return array of container definitions
	 *
	 * @return array
	 */
	private function definitions() {
		return [
			InterceptCoordinator::class => function ( Container $c ) {
				return new InterceptCoordinator(
					$c[ Settings::class ],
					$c[ TableCoordinator::class ],
					$c[ DataCache::class ] );
			},
			PostDeleteIntercept::class => function ( Container $c ) {
				return new PostDeleteIntercept();
			},
			UserDeleteIntercept::class => function ( Container $c ) {
				return new UserDeleteIntercept();
			},

			// Back compat — remove these in version 1.2
			'coordinator.core_metadata' => function ( Container $c ) {
				_deprecated_function( "'coordinator.core_metadata' container binding ", 1.1, 'none' );

				return null;
			},
			'coordinator.intercept' => function ( Container $c ) {
				_deprecated_function( "'coordinator.intercept' container binding ", 1.1, InterceptCoordinator::class );

				return $c[ InterceptCoordinator::class ];
			},
			'post_delete_intercept' => function ( Container $c ) {
				_deprecated_function( "'post_delete_intercept' container binding ", 1.1, PostDeleteIntercept::class );

				return $c[ PostDeleteIntercept::class ];
			},
			'user_delete_intercept' => function ( Container $c ) {
				_deprecated_function( "'user_delete_intercept' container binding ", 1.1, UserDeleteIntercept::class );

				return $c[ UserDeleteIntercept::class ];
			},
		];
	}

}