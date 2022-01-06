<?php

namespace ACFCustomDatabaseTables\AdminPost;

use ACFCustomDatabaseTables\Background\RebuildMapSystemBackgroundTask;
use ACFCustomDatabaseTables\Nonce\NonceBase;
use ACFCustomDatabaseTables\UI\PersistentAdminNoticeHandler;
use ACFCustomDatabaseTables\Utils\Redirect;
use ACFCustomDatabaseTables\Utils\Request;

/**
 * Class RebuildMapSystemAdminPost
 * @package ACFCustomDatabaseTables\AdminPost
 *
 * Handles the config submission when rebuilding the map system. This ensures the request is valid, checks the payload
 * has what it needs, then kicks off the background process.
 */
class RebuildMapSystemAdminPost {

	const ACTION = 'acfcdt-rebuild-map-system';

	/** @var PersistentAdminNoticeHandler */
	private $notice;

	/** @var NonceBase */
	private $nonce;

	/** @var RebuildMapSystemBackgroundTask */
	private $task;

	/**
	 * @param NonceBase $nonce
	 * @param PersistentAdminNoticeHandler $notice
	 * @param RebuildMapSystemBackgroundTask $task
	 */
	public function __construct( NonceBase $nonce, PersistentAdminNoticeHandler $notice, RebuildMapSystemBackgroundTask $task ) {
		$this->nonce = $nonce;
		$this->notice = $notice;
		$this->task = $task;
	}

	public function init() {
		add_action( 'admin_post_' . self::ACTION, [ $this, '_handle' ] );
	}

	public function url() {
		return add_query_arg( 'action', self::ACTION, admin_url( 'admin-post.php' ) );
	}

	public function nonce_field() {
		return $this->nonce->field_with_referrer();
	}

	public function _handle() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have permission to do that.' );
		}

		$this->nonce->validate();
		$this->validate_payload();
		$this->start_rebuild();
	}

	/**
	 * Check the request contains the expected data and, if not, redirect back to the previous screen with a notice.
	 */
	public function validate_payload() {
		if ( ! Request::get( 'field_group_keys' ) ) {
			$this->notice->add_warning( 'No field groups were selected. Choose which field groups you wish to rebuild then try again.' );
			$this->notice->store();
			Redirect::safely_to( add_query_arg( PersistentAdminNoticeHandler::FLAG, 1, wp_get_referer() ) );
		}
	}

	public function start_rebuild() {
		// Start the background task
		$this->task->run( [
			'batch_size' => 10,
			'field_group_keys' => Request::get( 'field_group_keys', [] ),
		] );

		Redirect::safely_to( Request::get( 'next', wp_get_referer() ) );
	}

}