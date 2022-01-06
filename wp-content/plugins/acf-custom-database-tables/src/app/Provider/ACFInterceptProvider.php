<?php

namespace ACFCustomDatabaseTables\Provider;

use ACFCustomDatabaseTables\Coordinator\InterceptCoordinator;
use ACFCustomDatabaseTables\Factory\ACFFieldFactory;
use ACFCustomDatabaseTables\Factory\ACFFieldGroupFactory;
use ACFCustomDatabaseTables\FileIO\TableJSONFileGenerator;
use ACFCustomDatabaseTables\Intercept\ACFDeleteFieldIntercept;
use ACFCustomDatabaseTables\Intercept\ACFFieldGroupDeleteIntercept;
use ACFCustomDatabaseTables\Intercept\ACFGetFieldIntercept;
use ACFCustomDatabaseTables\Intercept\ACFUpdateFieldIntercept;
use ACFCustomDatabaseTables\Service\ACFFieldSupportManager;
use ACFCustomDatabaseTables\Vendor\Pimple\Container;
use ACFCustomDatabaseTables\Vendor\Pimple\ServiceProviderInterface;

class ACFInterceptProvider implements ServiceProviderInterface {

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

		// todo - when we have all other TODOs in here resolved, consider a loop on defs here
		// ACF API intercepts
		$coord->register_intercept( $c[ ACFUpdateFieldIntercept::class ] );
		$coord->register_intercept( $c[ ACFGetFieldIntercept::class ] ); // todo - move functionality into field-specific intercepts
		$coord->register_intercept( $c[ ACFFieldGroupDeleteIntercept::class ] ); // todo - evaluate and work out whether we can/should move functionality into field specific intercepts
		$coord->register_intercept( $c[ ACFDeleteFieldIntercept::class ] ); // todo - evaluate and work out whether we can/should move functionality into field specific intercepts
	}

	/**
	 * Return array of container definitions
	 *
	 * @return array
	 */
	private function definitions() {
		return [
			ACFFieldGroupDeleteIntercept::class => function ( Container $c ) {
				return new ACFFieldGroupDeleteIntercept(
					$c[ ACFFieldGroupFactory::class ],
					$c[ TableJSONFileGenerator::class ] );
			},
			ACFUpdateFieldIntercept::class => function ( Container $c ) {
				return new ACFUpdateFieldIntercept();
			},
			ACFDeleteFieldIntercept::class => function ( Container $c ) {
				return new ACFDeleteFieldIntercept();
			},
			ACFGetFieldIntercept::class => function ( Container $c ) {
				return new ACFGetFieldIntercept();
			},

			// Back compat — remove these in version 1.2
			'acf_field_group_delete_intercept' => function ( Container $c ) {
				_deprecated_function( "'acf_field_group_delete_intercept' container binding ", 1.1, ACFFieldGroupDeleteIntercept::class );

				return $c[ ACFFieldGroupDeleteIntercept::class ];
			},
			'acf_update_field_intercept' => function ( Container $c ) {
				_deprecated_function( "'acf_update_field_intercept' container binding ", 1.1, ACFUpdateFieldIntercept::class );

				return $c[ ACFUpdateFieldIntercept::class ];
			},
			'acf_delete_field_intercept' => function ( Container $c ) {
				_deprecated_function( "'acf_delete_field_intercept' container binding ", 1.1, ACFDeleteFieldIntercept::class );

				return $c[ ACFDeleteFieldIntercept::class ];
			},
			'acf_get_field_intercept' => function ( Container $c ) {
				_deprecated_function( "'acf_get_field_intercept' container binding ", 1.1, ACFGetFieldIntercept::class );

				return $c[ ACFGetFieldIntercept::class ];
			},
		];
	}

}