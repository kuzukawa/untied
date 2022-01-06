<?php

namespace ACFCustomDatabaseTables\Provider;

use ACFCustomDatabaseTables\Service\DiagnosticReporter;
use ACFCustomDatabaseTables\Service\DocumentationProvider;
use ACFCustomDatabaseTables\Service\License;
use ACFCustomDatabaseTables\Settings;
use ACFCustomDatabaseTables\Vendor\ManualPackages\PluginUpdater;
use ACFCustomDatabaseTables\Vendor\Pimple\Container;
use ACFCustomDatabaseTables\Vendor\Pimple\ServiceProviderInterface;

class SupportProvider implements ServiceProviderInterface {

	public function register( Container $c ) {
		foreach ( $this->definitions() as $key => $callback ) {
			$c[ $key ] = $callback;
		}
	}

	public function init( Container $c ) {
		add_action( 'admin_init', function () use ( $c ) {
			$c[ PluginUpdater::class ]->init();
		}, 0 );
	}

	private function definitions() {
		return [
			'store_url' => 'https://hookturn.io/',
			'remote_docs_file_url' => 'https://hookturn.io/wp-content/docs/acf-custom-database-tables.json',
			License::class => function ( Container $c ) {
				return new License();
			},
			PluginUpdater::class => function ( Container $c ) {
				return new PluginUpdater(
					$c['store_url'],
					$c['plugin_file'],
					[
						'item_name' => $c['plugin_name'],
						'version' => $c['plugin_version'],
						'license' => $c[ License::class ]->get(),
						'author' => $c['plugin_author'],
						'beta' => false,
					]
				);
			},
			DocumentationProvider::class => function ( Container $c ) {
				// daily timestamp unless WP_DEBUG is on
				$timestamp = ( defined( 'WP_DEBUG' ) and WP_DEBUG )
					? time()
					: strtotime( '00:00:00' );

				$remote_docs = $c['remote_docs_file_url'] . '?t=' . $timestamp;

				return new DocumentationProvider( $remote_docs );
			},
			DiagnosticReporter::class => function ( Container $c ) {
				return new DiagnosticReporter(
					null,
					$c[ Settings::class ]
				);
			},

			// Back compat — remove these in version 1.2
			'service.license' => function ( Container $c ) {
				_deprecated_function( "'service.license' container binding ", 1.1, License::class );

				return $c[ License::class ];
			},
			'plugin_updater' => function ( Container $c ) {
				_deprecated_function( "'plugin_updater' container binding ", 1.1, PluginUpdater::class );

				return $c[ PluginUpdater::class ];
			},
			'service.documentation_provider' => function ( Container $c ) {
				_deprecated_function( "'service.documentation_provider' container binding ", 1.1, DocumentationProvider::class );

				return $c[ DocumentationProvider::class ];
			},
			'service.diagnostic_reporter' => function ( Container $c ) {
				_deprecated_function( "'service.diagnostic_reporter' container binding ", 1.1, DiagnosticReporter::class );

				return $c[ DiagnosticReporter::class ];
			},
		];
	}
}