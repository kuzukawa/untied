<?php

namespace ACFCustomDatabaseTables\Facade;

use ACFCustomDatabaseTables\Container;
use RuntimeException;

abstract class FacadeBase {

	/**
	 * @var Container
	 */
	protected static $app;

	public static function set_facade_app( Container $app ) {
		static::$app = $app;
	}

	public static function get_facade_app() {
		return static::$app;
	}

	public static function get_facade_root() {
		return static::resolve_facade_instance( static::get_facade_accessor() );
	}

	protected static function resolve_facade_instance( $name ) {
		if ( is_object( $name ) ) {
			return $name;
		}

		return static::$app[ $name ];
	}

	/**
	 * @return string
	 */
	protected static function get_facade_accessor() {
		throw new RuntimeException( 'Facade does not implement getFacadeAccessor method.' );
	}

	/**
	 * Handle dynamic, static calls to the object.
	 *
	 * @param string $method
	 * @param array $args
	 *
	 * @return mixed
	 *
	 * @throws RuntimeException
	 */
	public static function __callStatic( $method, $args ) {
		$instance = static::get_facade_root();

		if ( ! $instance ) {
			throw new RuntimeException( 'A facade root has not been set.' );
		}

		return $instance->$method( ...$args );
	}

}