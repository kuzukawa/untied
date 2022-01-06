<?php

namespace ACFCustomDatabaseTables;

class Core {

	/** @var Container */
	protected $container;

	/** @var bool */
	private $booted = false;

	/** @var bool */
	private $initialised = false;

	/**
	 * Core constructor.
	 *
	 * @param Container $container
	 */
	public function __construct( Container $container ) {
		$this->container = $container['container'] = $container;
	}

	/**
	 * Run all boot routines on providers
	 */
	public function boot() {
		if ( ! $this->booted ) {
			$this->boot_providers();
			$this->booted = true;
		}
	}

	/**
	 * Runs all init routines
	 */
	public function init() {
		if ( ! $this->initialised ) {
			$this->init_providers();
			$this->initialised = true;
		}
	}

	/**
	 * Loop through registered service providers and initilise them
	 */
	private function boot_providers() {
		foreach ( $this->container->providers() as $class_name => $provider ) {
			if ( method_exists( $provider, 'boot' ) ) {
				$provider->boot( $this->container );
			}
		}
	}

	/**
	 * Loop through registered service providers and initilise them
	 */
	private function init_providers() {
		foreach ( $this->container->providers() as $class_name => $provider ) {
			if ( method_exists( $provider, 'init' ) ) {
				$provider->init( $this->container );
			}
		}
	}

	/**
	 * Get the container instance.
	 *
	 * WARNING: This is for internal use by our testing systems and should not be considered a point of
	 *  extension/modification for developers.
	 *
	 * Do not use this method unless you:
	 *  1. Know what you are doing.
	 *  2. Test plugin updates thoroughly in a staging environment before deploying to a live site.
	 *  3. Are prepared to change any custom implementations to facilitate major changes to the core plugin.
	 *
	 * @return Container
	 * @internal
	 * @access private
	 *
	 */
	public function __get_container() {
		return $this->container;
	}

	/**
	 * @return Container
	 * @deprecated in favour of \ACFCustomDatabaseTables\Core::__container() as the double underscore prefix is less
	 * likely to appear in IDE code-completion suggestions and gives the method an air of internal use. We still need
	 * the container to be accessible externally, however, as the facilitates our automated testing.
	 *
	 */
	public function container() {
		_deprecated_function( __METHOD__, '1.1',
			'\ACFCustomDatabaseTables\Core::__container if you must but read the method docblock to understand the consequences. This method will be removed in version 1.2' );

		return $this->__get_container();
	}

	/** @deprecated */
	public function init_table_map() {
		_deprecated_function( __METHOD__, '1.1',
			'... Functionality has been moved to a service provider. This method will be removed in version 1.2' );
	}

	/** @deprecated */
	public function init_modules() {
		_deprecated_function( __METHOD__, '1.1',
			'... Functionality has been moved to a service provider. This method will be removed in version 1.2' );
	}

	/** @deprecated */
	public function init_controllers() {
		_deprecated_function( __METHOD__, '1.1',
			'... Functionality has been moved to a service provider. This method will be removed in version 1.2' );
	}

	/** @deprecated */
	public function init_updater() {
		_deprecated_function( __METHOD__, '1.1',
			'... Functionality has been moved to a service provider. This method will be removed in version 1.2' );
	}

}