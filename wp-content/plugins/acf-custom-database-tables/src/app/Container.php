<?php

namespace ACFCustomDatabaseTables;

use ACFCustomDatabaseTables\Contract\HasActivationRoutine;
use \ACFCustomDatabaseTables\Vendor\Pimple;
use ACFCustomDatabaseTables\Vendor\Pimple\ServiceProviderInterface;

/**
 * Class Container
 * @package ACFCustomDatabaseTables
 */
class Container extends Pimple\Container {

	protected $providers = [];

	protected $has_activation_routine = [];

	/**
	 * Wrap the default register method so that we can reference our providers when needed.
	 *
	 * @param ServiceProviderInterface $provider
	 * @param array $values
	 *
	 * @return Pimple\Container
	 */
	public function register( ServiceProviderInterface $provider, array $values = array() ) {
		$this->providers[ get_class( $provider ) ] = $provider;

		return parent::register( $provider, $values );
	}

	/**
	 * Get array of all registered service providers
	 *
	 * @return array
	 */
	public function providers() {
		return $this->providers;
	}

	/**
	 * @param string $class
	 *
	 * @return ServiceProviderInterface|null
	 */
	public function provider( $class ) {
		return isset( $this->providers[ $class ] ) ? $this->providers[ $class ] : null;
	}

	public function make( $binding, $default = null ) {
		return isset( $this[ $binding ] )
			? $this[ $binding ]
			: $default;
	}

	public function offsetSet( $id, $value ) {
		// Flag class bindings with activation routines so we can run them on plugin activation.
		if ( is_a( $id, HasActivationRoutine::class, true ) ) {
			$this->has_activation_routine[] = $id;
		}

		parent::offsetSet( $id, $value );
	}

	/**
	 * Run any activation routines on bound objects.
	 */
	public function run_activation_routine_handlers() {
		foreach ( $this->has_activation_routine as $class ) {
			/** @var HasActivationRoutine $h */
			$h = $this->make( $class );
			$h->run_activation_routine( $this );
		}
	}

	/**
	 * @param $prefix
	 *
	 * @return array
	 * @deprecated
	 *
	 * Returns a subset of all registered container keys that have a given prefix
	 *
	 */
	public function get_keys_with_prefix( $prefix ) {
		_deprecated_function( __METHOD__, '1.1',
			'... Functionality no longer required as we are now registering service providers. This method will be removed in version 1.2' );

		return array_filter( $this->keys(), function ( $key ) use ( $prefix ) {
			return ( 0 === strpos( $key, $prefix ) );
		} );
	}

	/**
	 * @param $prefix
	 * @param $callback
	 *
	 * @deprecated
	 *
	 * Applies a callback to a subset of all registered container dependencies that have keys based on a given key
	 * prefix
	 *
	 */
	public function each( $prefix, $callback ) {
		_deprecated_function( __METHOD__, '1.1',
			'... Functionality no longer required as we are now registering service providers. This method will be removed in version 1.2' );

		$keys = $this->get_keys_with_prefix( $prefix );
		foreach ( $keys as $k ) {
			call_user_func( $callback, $this[ $k ] );
		}
	}

}