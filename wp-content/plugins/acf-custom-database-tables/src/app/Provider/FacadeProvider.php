<?php

namespace ACFCustomDatabaseTables\Provider;

use ACFCustomDatabaseTables\Facade\FacadeBase;
use ACFCustomDatabaseTables\Vendor\Pimple\Container;
use ACFCustomDatabaseTables\Vendor\Pimple\ServiceProviderInterface;

class FacadeProvider implements ServiceProviderInterface {

	/**
	 * @param Container $c
	 */
	public function register( Container $c ) {
		FacadeBase::set_facade_app( $c );
	}

}