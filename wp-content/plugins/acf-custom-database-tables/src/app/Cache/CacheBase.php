<?php

namespace ACFCustomDatabaseTables\Cache;

abstract class CacheBase {

	protected $group = '';

	/**
	 * CacheBase constructor.
	 *
	 * @param string $group
	 */
	public function __construct( $group ) {
		$this->group = $group;
	}

	/**
	 * @return string
	 */
	public function group() {
		return $this->group;
	}

	/**
	 * @param $key
	 *
	 * @return bool|mixed
	 */
	public function get( $key ) {
		return wp_cache_get( $key, $this->group() );
	}

	/**
	 * @param $key
	 * @param $value
	 *
	 * @return bool
	 */
	public function set( $key, $value ) {
		return wp_cache_set( $key, $value, $this->group() );
	}

	/**
	 * @param $key
	 *
	 * @return bool
	 */
	public function delete( $key ) {
		return wp_cache_delete( $key, $this->group() );
	}

}