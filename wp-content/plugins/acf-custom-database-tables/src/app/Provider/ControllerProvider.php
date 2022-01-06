<?php

namespace ACFCustomDatabaseTables\Provider;

use ACFCustomDatabaseTables\Contract\ControllerInterface;
use ACFCustomDatabaseTables\Controller\ACFFieldGroupAdminController;
use ACFCustomDatabaseTables\Controller\DefaultContextController;
use ACFCustomDatabaseTables\Controller\LicenseFormController;
use ACFCustomDatabaseTables\Controller\SettingsPageController;
use ACFCustomDatabaseTables\Controller\UpdateTablesFormController;
use ACFCustomDatabaseTables\Coordinator\InterceptCoordinator;
use ACFCustomDatabaseTables\Coordinator\TableCreationCoordinator;
use ACFCustomDatabaseTables\Factory\ACFFieldGroupFactory;
use ACFCustomDatabaseTables\FileIO\TableJSONFileGenerator;
use ACFCustomDatabaseTables\Service\DiagnosticReporter;
use ACFCustomDatabaseTables\Service\DocumentationProvider;
use ACFCustomDatabaseTables\Service\License;
use ACFCustomDatabaseTables\UI\AdminNoticeHandler;
use ACFCustomDatabaseTables\UI\AssetManager;
use ACFCustomDatabaseTables\UI\FieldGroupCustomTableMetaBox;
use ACFCustomDatabaseTables\UI\PersistentAdminNoticeHandler;
use ACFCustomDatabaseTables\Vendor\Pimple\Container;
use ACFCustomDatabaseTables\Vendor\Pimple\ServiceProviderInterface;

class ControllerProvider implements ServiceProviderInterface {

	/**
	 * @param Container $c
	 */
	public function register( Container $c ) {
		foreach ( $this->definitions() as $key => $callback ) {
			$c[ $key ] = $callback;
		}
	}

	public function init( Container $c ) {
		foreach ( $this->definitions() as $key => $callback ) {

			// Skip deprecated bindings. Remove this in version 1.2.
			if ( in_array( $key, [
				'controller.settings_page',
				'controller.acf_field_group_admin',
				'controller.update_tables_form',
				'controller.default_context',
				'controller.license_form',
			] ) ) {
				continue;
			}

			$controller = $c[ $key ];

			if ( $controller instanceof ControllerInterface ) {
				$controller->init();
			}
		}
	}

	private function definitions() {
		return [
			SettingsPageController::class => function ( Container $c ) {
				return new SettingsPageController(
					$c[ DiagnosticReporter::class ],
					$c[ DocumentationProvider::class ],
					$c[ LicenseFormController::class ],
					$c[ AssetManager::class ],
					$c[ AdminNoticeHandler::class ]
				);
			},
			ACFFieldGroupAdminController::class => function ( Container $c ) {
				return new ACFFieldGroupAdminController(
					$c[ FieldGroupCustomTableMetaBox::class ],
					$c[ ACFFieldGroupFactory::class ],
					$c[ PersistentAdminNoticeHandler::class ],
					$c[ TableJSONFileGenerator::class ],
					$c[ AssetManager::class ]
				);
			},
			UpdateTablesFormController::class => function ( Container $c ) {
				return new UpdateTablesFormController(
					$c[ TableCreationCoordinator::class ],
					$c[ AdminNoticeHandler::class ]
				);
			},
			DefaultContextController::class => function ( Container $c ) {
				return new DefaultContextController( $c[ InterceptCoordinator::class ] );
			},
			LicenseFormController::class => function ( Container $c ) {
				return new LicenseFormController( $c[ License::class ] );
			},

			// Back compat — remove these in version 1.2
			'controller.settings_page' => function ( Container $c ) {
				_deprecated_function( "'controller.settings_page' container binding ", 1.1, SettingsPageController::class );

				return $c[ SettingsPageController::class ];
			},
			'controller.acf_field_group_admin' => function ( Container $c ) {
				_deprecated_function( "'controller.acf_field_group_admin' container binding ", 1.1, ACFFieldGroupAdminController::class );

				return $c[ ACFFieldGroupAdminController::class ];
			},
			'controller.update_tables_form' => function ( Container $c ) {
				_deprecated_function( "'controller.update_tables_form' container binding ", 1.1, UpdateTablesFormController::class );

				return $c[ UpdateTablesFormController::class ];
			},
			'controller.default_context' => function ( Container $c ) {
				_deprecated_function( "'controller.default_context' container binding ", 1.1, DefaultContextController::class );

				return $c[ DefaultContextController::class ];
			},
			'controller.license_form' => function ( Container $c ) {
				_deprecated_function( "'controller.license_form' container binding ", 1.1, LicenseFormController::class );

				return $c[ LicenseFormController::class ];
			},
		];

	}
}