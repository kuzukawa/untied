<?php

namespace ACFCustomDatabaseTables\Provider;

use ACFCustomDatabaseTables\Utils\View;
use ACFCustomDatabaseTables\Vendor\Pimple\Container;
use ACFCustomDatabaseTables\Vendor\Pimple\ServiceProviderInterface;

class ViewProvider implements ServiceProviderInterface {

	/**
	 * @param Container $c
	 */
	public function register( Container $c ) {
		$c['view_dir'] = function ( Container $c ) {
			return $c['plugin_dir'] . 'src/view';
		};
	}

	public function init( Container $c ) {
		View::$view_dir = $c['view_dir'];
	}

}