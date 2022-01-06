<?php

namespace ACFCustomDatabaseTables\Provider;

use ACFCustomDatabaseTables\Ajax\RebuildMapSystemStatusAjax;
use ACFCustomDatabaseTables\Nonce\RebuildMapSystemNonce;
use ACFCustomDatabaseTables\Vendor\Pimple\Container;
use ACFCustomDatabaseTables\Vendor\Pimple\ServiceProviderInterface;

class AjaxProvider implements ServiceProviderInterface {

	public function register( Container $c ) {
		foreach ( $this->definitions() as $key => $callback ) {
			$c[ $key ] = $callback;
		}
	}

	public function boot( Container $c ) {
		if ( is_admin() ) {
			$c[ RebuildMapSystemStatusAjax::class ]->init();
		}
	}

	public function definitions() {
		return [
			RebuildMapSystemStatusAjax::class => function ( Container $c ) {
				return new RebuildMapSystemStatusAjax( $c[ RebuildMapSystemNonce::class ] );
			},
		];
	}

}