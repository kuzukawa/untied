<?php

namespace ACFCustomDatabaseTables\Provider;

use ACFCustomDatabaseTables\Compat\WpAllImport\ApiHookHotfix;
use ACFCustomDatabaseTables\Compat\WpAllImport\CoreMetaBypass;
use ACFCustomDatabaseTables\Compat\WpAllImport\WpAllImport;
use ACFCustomDatabaseTables\Vendor\Pimple\Container;
use ACFCustomDatabaseTables\Vendor\Pimple\ServiceProviderInterface;

class CompatProvider implements ServiceProviderInterface {

	public function register( Container $c ) {
		foreach ( $this->definitions() as $key => $callback ) {
			$c[ $key ] = $callback;
		}
	}

	public function boot( Container $c ) {
		$c[ WpAllImport::class ]->init();
	}

	private function definitions() {
		return [
			WpAllImport::class => function ( Container $c ) {
				return new WpAllImport( $c );
			},
			CoreMetaBypass::class => function ( Container $c ) {
				return new CoreMetaBypass();
			},
			ApiHookHotfix::class => function ( Container $c ) {
				return new ApiHookHotfix( $c[ CoreMetaBypass::class ] );
			},
		];
	}

}