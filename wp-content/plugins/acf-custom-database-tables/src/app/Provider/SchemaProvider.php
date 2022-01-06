<?php

namespace ACFCustomDatabaseTables\Provider;

use ACFCustomDatabaseTables\Cache\DataCache;
use ACFCustomDatabaseTables\Cache\TableObjectCache;
use ACFCustomDatabaseTables\Coordinator\TableCoordinator;
use ACFCustomDatabaseTables\Coordinator\TableCreationCoordinator;
use ACFCustomDatabaseTables\Data\ColumnValidator;
use ACFCustomDatabaseTables\Data\TableMap;
use ACFCustomDatabaseTables\Data\TableValidator;
use ACFCustomDatabaseTables\Factory\DynamicColumnFactory;
use ACFCustomDatabaseTables\Factory\DynamicTableFactory;
use ACFCustomDatabaseTables\FileIO\JSONFileParser;
use ACFCustomDatabaseTables\Service\TableNameValidator;
use ACFCustomDatabaseTables\Settings;
use ACFCustomDatabaseTables\Vendor\Pimple\Container;
use ACFCustomDatabaseTables\Vendor\Pimple\ServiceProviderInterface;

class SchemaProvider implements ServiceProviderInterface {

	/**
	 * @param Container $c
	 */
	public function register( Container $c ) {
		foreach ( $this->definitions() as $key => $callback ) {
			$c[ $key ] = $callback;
		}
	}

	public function init( Container $c ) {
		/** @var TableCoordinator $coord */
		$coord = $c[ TableCoordinator::class ];
		$coord->fetch_map_from_cache();
	}

	private function definitions() {
		return [
			TableNameValidator::class => function ( Container $c ) {
				return new TableNameValidator();
			},
			TableValidator::class => function ( Container $c ) {
				return new TableValidator();
			},
			ColumnValidator::class => function ( Container $c ) {
				return new ColumnValidator();
			},
			TableMap::class => function ( Container $c ) {
				return new TableMap(
					$c[ TableValidator::class ],
					$c[ TableNameValidator::class ]
				);
			},
			TableCreationCoordinator::class => function ( Container $c ) {
				return new TableCreationCoordinator( $c[ TableCoordinator::class ] );
			},
			TableCoordinator::class => function ( Container $c ) {
				return new TableCoordinator(
					$c[ JSONFileParser::class ],
					$c[ Settings::class ],
					$c[ TableMap::class ],
					$c[ DynamicTableFactory::class ],
					$c[ TableObjectCache::class ] );
			},
			DataCache::class => function ( Container $c ) {
				return new DataCache();
			},
			TableObjectCache::class => function ( Container $c ) {
				return new TableObjectCache();
			},
			DynamicColumnFactory::class => function ( Container $c ) {
				return new DynamicColumnFactory(
					null,
					$c[ ColumnValidator::class ]
				);
			},
			DynamicTableFactory::class => function ( Container $c ) {
				return new DynamicTableFactory(
					$c[ DynamicColumnFactory::class ],
					$c[ TableValidator::class ]
				);
			},

			// Back compat — remove these in version 1.2
			'wpdb' => function ( Container $c ) {
				_deprecated_function( "ACF Custom Database Table's 'wpdb' container binding.", '1.1 (ACF Custom Database Tables)', 'NONE – no longer injecting this object as a dependency due to issues with object cache. This container binding will be removed in version 1.2' );

				return null;
			},
			'table_name_validator' => function ( Container $c ) {
				_deprecated_function( "'table_name_validator' container binding ", 1.1, TableNameValidator::class );

				return $c[ TableNameValidator::class ];
			},
			'table_validator' => function ( Container $c ) {
				_deprecated_function( "'table_validator' container binding ", 1.1, TableValidator::class );

				return $c[ TableValidator::class ];
			},
			'column_validator' => function ( Container $c ) {
				_deprecated_function( "'column_validator' container binding ", 1.1, ColumnValidator::class );

				return $c[ ColumnValidator::class ];
			},
			'table_map' => function ( Container $c ) {
				_deprecated_function( "'table_map' container binding ", 1.1, TableMap::class );

				return $c[ TableMap::class ];
			},
			'coordinator.table_creation' => function ( Container $c ) {
				_deprecated_function( "'' container bindincoordinator.table_creationg ", 1.1, TableCreationCoordinator::class );

				return $c[ TableCreationCoordinator::class ];
			},
			'coordinator.table' => function ( Container $c ) {
				_deprecated_function( "'coordinator.table' container binding ", 1.1, TableCoordinator::class );

				return $c[ TableCoordinator::class ];
			},
			'cache.data' => function ( Container $c ) {
				_deprecated_function( "'cache.data' container binding ", 1.1, DataCache::class );

				return $c[ DataCache::class ];
			},
			'cache.table_object' => function ( Container $c ) {
				_deprecated_function( "'cache.table_object' container binding ", 1.1, TableObjectCache::class );

				return $c[ TableObjectCache::class ];
			},
			'factory.dynamic_column' => function ( Container $c ) {
				_deprecated_function( "'factory.dynamic_column' container binding ", 1.1, DynamicColumnFactory::class );

				return $c[ DynamicColumnFactory::class ];
			},
			'factory.dynamic_table' => function ( Container $c ) {
				_deprecated_function( "'factory.dynamic_table' container binding ", 1.1, DynamicTableFactory::class );

				return $c[ DynamicTableFactory::class ];
			},
		];
	}
}