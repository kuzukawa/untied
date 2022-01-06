<?php

namespace ACFCustomDatabaseTables\Ajax;

use ACFCustomDatabaseTables\Background\RebuildMapSystemBackgroundTask;
use ACFCustomDatabaseTables\Facade\App;
use ACFCustomDatabaseTables\Nonce\NonceBase;

class RebuildMapSystemStatusAjax {

	const ACTION = 'acfcdt-rebuild-map-system';

	/** @var NonceBase */
	private $nonce;

	/**
	 * @param NonceBase $nonce
	 */
	public function __construct( NonceBase $nonce ) {
		$this->nonce = $nonce;
	}

	public function init() {
		add_action( 'wp_ajax_' . self::ACTION, [ $this, '_handle' ] );
	}

	public function url() {
		return add_query_arg( 'action', self::ACTION, admin_url( 'admin-ajax.php' ) );
	}

	/**
	 * @return NonceBase
	 */
	public function nonce() {
		return $this->nonce;
	}

	public function _handle() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'You do not have permission to do that.' ], 403 );
		}

		if ( ! $this->nonce->is_valid() ) {
			wp_send_json_error( [ 'message' => 'Nonce was invalid — try reloading the page or starting the process again.' ], 403 );
		}

		/** @var RebuildMapSystemBackgroundTask $task */
		$task = App::make( RebuildMapSystemBackgroundTask::class );

		echo wp_json_encode( [
			'percentage' => $task->percent_complete(),
			'complete' => $task->is_done(),
			'info' => $task->snapshot(),
			'logs' => $task->logs(),
		] );

		die();
	}

}