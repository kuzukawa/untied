<?php

namespace ACFCustomDatabaseTables\Compat\WpAllImport;

use ACFCustomDatabaseTables\Facade\Settings;
use ACFCustomDatabaseTables\Vendor\Pimple\Container;

class WpAllImport {

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @param Container $container
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
	}

	public function init() {
		if ( $this->is_installed() and $this->should_run() ) {
			$this->run();
		}
	}

	private function is_installed() {
		return defined( 'PMXI_VERSION' );
	}

	private function should_run() {
		return Settings::get( 'enable_wp_all_import_plugin_compat', false );
	}

	private function run() {
		// This ensures core meta bypasses are honoured.
		$this->container[ CoreMetaBypass::class ]->init();

		// This ensures the API filters we need are fired and data is stored in custom tables where appropriate.
		$this->container[ ApiHookHotfix::class ]->init();
	}

}