<?php

namespace ACFCustomDatabaseTables\AdminPost;

use ACFCustomDatabaseTables\Facade\App;
use ACFCustomDatabaseTables\Utils\Error;
use ACFCustomDatabaseTables\Utils\Redirect;
use ACFCustomDatabaseTables\Utils\Request;

/**
 * Set an admin post endpoint for dismissing admin notices.
 *
 * @package ACFCustomDatabaseTables\AdminPost
 */
class DismissAdminNoticeAdminPost {

	const ACTION = 'acfcdt-dismiss-admin-notice';
	const FQN_PARAM = 'acfcdt-fqn';

	public function init() {
		add_action( 'admin_post_' . self::ACTION, [ $this, '_handle' ] );
	}

	public function url( $notice_class ) {
		return add_query_arg( [
			'action' => self::ACTION,
			self::FQN_PARAM => $this->encode_class_fqn( $notice_class ),
		], admin_url( 'admin-post.php' ) );
	}

	public function _handle() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have permission to do that.' );
		}

		$this->dismiss_notice();

		// Redirect back to previous screen
		Redirect::safely_to( wp_get_referer() );
	}

	/**
	 * @return void
	 */
	private function dismiss_notice() {
		// Get the assocated notice handler class name from the request.
		$fqn = Request::get( self::FQN_PARAM, '' );
		if ( empty( $fqn ) ) {
			Error::log( 'Failed to dismiss notice — `acfcdt_fqn` parameter not specified.' );
			return;
		}

		// Give it back its namespace separators.
		$fqn = $this->decode_class_fqn( $fqn );

		// Resolve the object from the container.
		$notice = App::make( $fqn );
		if ( ! is_object( $notice ) ) {
			Error::log( sprintf( 'Failed to dismiss notice with FQN %s — could not resolve notice object.', $fqn ) );
			return;
		}

		// Validate the object has the expected method.
		if ( ! method_exists( $notice, 'dismiss' ) ) {
			Error::log( sprintf( 'Failed to dismiss notice with FQN %s — notice object does not have dismiss() method.', $fqn ) );
			return;
		}

		$notice->dismiss();
	}

	/**
	 * Replaces namespace separator with dots so the FQN isn't mutilated by esc_attr().
	 *
	 * @param string $fqn
	 *
	 * @return string
	 */
	private function encode_class_fqn( $fqn ) {
		return (string) str_replace( '\\', '.', $fqn );
	}

	private function decode_class_fqn( $fqn ) {
		return (string) str_replace( '.', '\\', $fqn );
	}

}