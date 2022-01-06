<?php

namespace ACFCustomDatabaseTables\UI;

/**
 * Class PersistentAdminNoticeHandler
 * @package ACFCustomDatabaseTables\UI
 *
 * An admin notice handler that collects notices on one page cycle, stores them, then displays them on the following
 * page load.
 */
class PersistentAdminNoticeHandler extends AdminNoticeHandler {

	const FLAG = 'acfcdt_notices';

	private $transient_key = 'acfcdt-persistent-admin-notices';

	/** @var array These object props will be stored if they contain data */
	private $persistent_props = [
		'error_messages',
		'success_messages',
		'warning_messages',
		'info_messages'
	];

	/**
	 * Override the transient key, if necessary
	 *
	 * @param $key
	 */
	public function set_transient_key( $key ) {
		$this->transient_key = $key;
	}

	/**
	 * Stores the message data in a WP transient for retrieval on a subsequent page load
	 */
	public function store() {
		if ( $data = $this->get_persistent_data() ) {
			$expiry = ini_get( 'max_execution_time' ) ?: MINUTE_IN_SECONDS;
			set_transient( $this->transient_key, $data, $expiry );
		}
	}

	/**
	 * Restores message data on the object so that it is ready for display
	 */
	public function restore() {
		if ( $data = get_transient( $this->transient_key ) ) {

			foreach ( $data as $prop => $messages ) {
				$this->$prop = array_merge( $this->$prop, $messages );
			}

			delete_transient( $this->transient_key );
		}
	}

	/**
	 * Gets all object props marked as persistent
	 *
	 * @return array
	 */
	private function get_persistent_data() {
		$data = array_intersect_key( get_object_vars( $this ), array_flip( $this->persistent_props ) );

		return array_filter( $data );
	}

}