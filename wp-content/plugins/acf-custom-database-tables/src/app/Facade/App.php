<?php

namespace ACFCustomDatabaseTables\Facade;

use ACFCustomDatabaseTables\Container;

/**
 * @package ACFCustomDatabaseTables\Facade
 * @mixin Container
 * @method static make( $binding, $default = null )
 * @method static run_activation_routine_handlers()
 * @method static provider( $binding )
 */
class App extends FacadeBase {

	protected static function get_facade_accessor() {
		return 'container';
	}

	public static function version() {
		return self::make( 'plugin_version', 0 );
	}

}