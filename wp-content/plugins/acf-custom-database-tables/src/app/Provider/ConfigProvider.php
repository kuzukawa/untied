<?php

namespace ACFCustomDatabaseTables\Provider;

use ACFCustomDatabaseTables\Options;
use ACFCustomDatabaseTables\Settings;
use ACFCustomDatabaseTables\Vendor\Pimple\Container;
use ACFCustomDatabaseTables\Vendor\Pimple\ServiceProviderInterface;

class ConfigProvider implements ServiceProviderInterface {

	/**
	 * @param Container $c
	 */
	public function register( Container $c ) {
		foreach ( $this->definitions() as $key => $callback ) {
			$c[ $key ] = $callback;
		}
	}

	private function definitions() {
		return [
			'config_dir' => function ( Container $c ) {
				return $c['plugin_dir'] . 'src/config';
			},
			Settings::class => function ( Container $c ) {
				/** @var Options $opts */
				$opts = $c[ Options::class ];

				return new Settings( $opts->to_array() );
			},
			Options::class => function ( Container $c ) {
				$opts = new Options( require $c['config_dir'] . '/settings.php' );
				$opts->init();

				return $opts;
			},

			// Back compat — remove these in version 1.2
			'settings' => function ( Container $c ) {
				_deprecated_function( "'settings' container binding ", 1.1, Settings::class );

				return $c[ Settings::class ];
			},
			'options' => function ( Container $c ) {
				_deprecated_function( "'options' container binding ", 1.1, Options::class );

				return $c[ Options::class ];
			},
		];
	}
}