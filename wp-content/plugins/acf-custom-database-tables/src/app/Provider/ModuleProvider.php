<?php

namespace ACFCustomDatabaseTables\Provider;

use ACFCustomDatabaseTables\Contract\ModuleInterface;
use ACFCustomDatabaseTables\Module\AfterTableSchemaUpdateModule;
use ACFCustomDatabaseTables\Module\IntegerTypeCastModule;
use ACFCustomDatabaseTables\Module\InterceptRunControlModule;
use ACFCustomDatabaseTables\Module\SerializedDataModule;
use ACFCustomDatabaseTables\Settings;
use ACFCustomDatabaseTables\Vendor\Pimple\Container;
use ACFCustomDatabaseTables\Vendor\Pimple\ServiceProviderInterface;

class ModuleProvider implements ServiceProviderInterface {

	/**
	 * @param Container $c
	 */
	public function register( Container $c ) {
		foreach ( $this->definitions() as $key => $callback ) {
			$c[ $key ] = $callback;
		}
	}

	public function init( Container $c ) {
		/** @var Settings $settings */
		$settings = $c[ Settings::class ];

		$active_modules = array_filter( $settings->get( 'activate_modules' ) );

		if ( ! $active_modules ) {
			return;
		}

		foreach ( $this->definitions() as $key => $callback ) {

			// Skip deprecated bindings. Remove this in version 1.2.
			if ( in_array( $key, [
				'module.integer_type_cast',
				'module.serialized_data',
				'module.after_table_schema_update',
				'module.intercept_run_control',
			] ) ) {
				continue;
			}

			$module = $c[ $key ];

			if ( $module instanceof ModuleInterface and isset( $active_modules[ $module->name() ] ) ) {
				$module->init();
			}
		}
	}

	private function definitions() {
		return [
			IntegerTypeCastModule::class => function ( Container $c ) {
				return new IntegerTypeCastModule();
			},
			SerializedDataModule::class => function ( Container $c ) {
				return new SerializedDataModule();
			},
			AfterTableSchemaUpdateModule::class => function ( Container $c ) {
				return new AfterTableSchemaUpdateModule();
			},
			InterceptRunControlModule::class => function ( Container $c ) {
				return new InterceptRunControlModule( $c );
			},

			// Back compat — remove these in version 1.2
			'module.integer_type_cast' => function ( Container $c ) {
				_deprecated_function( "'module.integer_type_cast' container binding ", 1.1, IntegerTypeCastModule::class );

				return $c[ IntegerTypeCastModule::class ];
			},
			'module.serialized_data' => function ( Container $c ) {
				_deprecated_function( "'module.serialized_data' container binding ", 1.1, SerializedDataModule::class );

				return $c[ SerializedDataModule::class ];
			},
			'module.after_table_schema_update' => function ( Container $c ) {
				_deprecated_function( "'module.after_table_schema_update' container binding ", 1.1, AfterTableSchemaUpdateModule::class );

				return $c[ AfterTableSchemaUpdateModule::class ];
			},
			'module.intercept_run_control' => function ( Container $c ) {
				_deprecated_function( "'module.intercept_run_control' container binding ", 1.1, InterceptRunControlModule::class );

				return $c[ InterceptRunControlModule::class ];
			},
		];

	}
}