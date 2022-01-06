<?php

namespace ACFCustomDatabaseTables\Provider;

use ACFCustomDatabaseTables\UI\AssetManager;
use ACFCustomDatabaseTables\Vendor\Pimple\Container;
use ACFCustomDatabaseTables\Vendor\Pimple\ServiceProviderInterface;

class AssetProvider implements ServiceProviderInterface {

	/**
	 * @param Container $c
	 */
	public function register( Container $c ) {
		foreach ( $this->definitions() as $key => $callback ) {
			$c[ $key ] = $callback;
		}
	}

	public function boot( Container $c ) {
		// Enqueue global assets in the admin.
		// todo — this would probably make a lot of sense in a global admin controller.
		//  See https://app.clickup.com/t/8dt82b
		add_action( 'admin_enqueue_scripts', function () use ( $c ) {
			/** @var AssetManager $manager */
			$manager = $c[ AssetManager::class ];
			$manager->enqueue_script( 'acfcdt-admin-global' );
		} );
	}

	private function definitions() {
		return [
			'asset_url' => function ( Container $c ) {
				return $c['plugin_url'] . 'src/asset';
			},
			AssetManager::class => function ( Container $c ) {
				$manager = new AssetManager( $c['asset_url'] );
				$plugin_version = $c['plugin_version'];
				// todo - maybe read this from config facade instead of pulling in the file.
				$asset_definitions = require $c['config_dir'] . '/assets.php';

				// Set plugin version where assets have a 'null' version.
				// todo - move this into AssetManager. See https://app.clickup.com/t/8k7rhn
				foreach ( $asset_definitions as $type => $assets ) {
					$asset_definitions[ $type ] = array_map( function ( $asset ) use ( $plugin_version ) {
						isset( $asset['version'] ) and $asset['version'] or $asset['version'] = $plugin_version;

						return $asset;
					}, $assets );
				}

				$manager->set_asset_definitions( $asset_definitions );
				$manager->set_registration_hook( 'admin_enqueue_scripts' );
				$manager->init();

				return $manager;
			},

			// Back compat — remove these in version 1.2
			'asset_manager' => function ( Container $c ) {
				_deprecated_function( "'asset_manager' container binding ", 1.1, AssetManager::class );

				return $c[ AssetManager::class ];
			},
		];
	}
}