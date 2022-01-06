<?php

namespace ACFCustomDatabaseTables\Cache;

class DataCache extends CacheBase {

	/**
	 * TableObjectCache constructor.
	 *
	 * @param string $group
	 */
	public function __construct( $group = 'acfcdt/data' ) {
		parent::__construct( $group );
	}

	/**
	 * @param $table
	 * @param $context
	 * @param $id
	 *
	 * @return string
	 */
	public function build_key( $table, $context, $id ) {
		return "table=$table/context=$context/id=$id";
	}

	/**
	 * @param $table
	 * @param $context
	 * @param $id
	 * @param $value
	 *
	 * @return bool
	 */
	public function set_record( $table, $context, $id, $value ) {
		$key = $this->build_key( $table, $context, $id );

		return $this->set( $key, $value );
	}

	/**
	 * @param $table
	 * @param $context
	 * @param $id
	 *
	 * @return bool|mixed
	 */
	public function get_record( $table, $context, $id ) {
		$key = $this->build_key( $table, $context, $id );

		return $this->get( $key );
	}

	/**
	 * @param $table
	 * @param $context
	 * @param $id
	 *
	 * @return bool
	 */
	public function delete_record( $table, $context, $id ) {
		$key = $this->build_key( $table, $context, $id );

		return $this->delete( $key );
	}

}