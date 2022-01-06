<?php

namespace ACFCustomDatabaseTables\Provider;

use ACFCustomDatabaseTables\Data\TableValidator;
use ACFCustomDatabaseTables\Factory\ACFFieldFactory;
use ACFCustomDatabaseTables\FileIO\JSONFileParser;
use ACFCustomDatabaseTables\FileIO\TableJSONFileGenerator;
use ACFCustomDatabaseTables\Service\ACFFieldSupportManager;
use ACFCustomDatabaseTables\Service\TableNameValidator;
use ACFCustomDatabaseTables\Settings;
use ACFCustomDatabaseTables\Vendor\Pimple\Container;
use ACFCustomDatabaseTables\Vendor\Pimple\ServiceProviderInterface;

class FileIoProvider implements ServiceProviderInterface {

	/**
	 * @param Container $c
	 */
	public function register( Container $c ) {
		foreach ( $this->definitions() as $key => $callback ) {
			$c[ $key ] = $callback;
		}
	}

	/**
	 * Return array of container definitions
	 *
	 * @return array
	 */
	private function definitions() {
		return [
			JSONFileParser::class => function ( Container $c ) {
				return new JSONFileParser();
			},
			TableJSONFileGenerator::class => function ( Container $c ) {
				return new TableJSONFileGenerator(
					$c[ Settings::class ],
					$c[ TableNameValidator::class ],
					$c[ TableValidator::class ],
					$c[ ACFFieldSupportManager::class ],
					$c[ ACFFieldFactory::class ]
				);
			},

			// Back compat — remove these in version 1.2
			'json_file_parser' => function ( Container $c ) {
				_deprecated_function( "'json_file_parser' container binding ", 1.1, JSONFileParser::class );

				return $c[ JSONFileParser::class ];
			},
			'table_json_file_generator' => function ( Container $c ) {
				_deprecated_function( "'table_json_file_generator' container binding ", 1.1, TableJSONFileGenerator::class );

				return $c[ TableJSONFileGenerator::class ];
			},
		];
	}

}