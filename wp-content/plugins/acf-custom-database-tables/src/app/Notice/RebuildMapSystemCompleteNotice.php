<?php

namespace ACFCustomDatabaseTables\Notice;

use ACFCustomDatabaseTables\AdminPost\DismissAdminNoticeAdminPost;
use ACFCustomDatabaseTables\Tools\RebuildMapSystemTool;

class RebuildMapSystemCompleteNotice {

	const OPTION = 'acfcdt-render-rebuild-map-complete-notice';

	/**
	 * @var RebuildMapSystemTool
	 */
	private $tool;

	/**
	 * @var DismissAdminNoticeAdminPost
	 */
	private $admin_post;

	/**
	 * @param RebuildMapSystemTool $tool
	 * @param DismissAdminNoticeAdminPost $admin_post
	 */
	public function __construct( RebuildMapSystemTool $tool, DismissAdminNoticeAdminPost $admin_post ) {
		$this->tool = $tool;
		$this->admin_post = $admin_post;
	}

	public function init() {
		add_action( 'admin_notices', [ $this, '_render' ] );
	}

	public function _render() {
		// Auto dismiss when visiting the status page.
		if ( $this->tool->is_current_page() ) {
			$this->dismiss();
		}

		if ( $this->should_render() ) {
			$this->render();
		}
	}

	/**
	 * Set the DB option to render the message. This ensures the notice doesn't disapper until dismissed.
	 */
	public function enqueue() {
		update_option( self::OPTION, 1, false );
	}

	public function dismiss() {
		delete_option( self::OPTION );
	}

	private function should_render() {
		if ( $this->tool->is_current_page() ) {
			return false;
		}

		return (bool) get_option( self::OPTION, false );
	}

	private function render() {
		$message = '<strong>ACF Custom Database Tables</strong> database table map rebuild is complete.';
		$message .= sprintf( '<br><a href="%s">View results</a>', esc_url( $this->tool->status_url() ) );
		$message .= sprintf( ' | <a class="acfcdt-external" href="%s" target="_blank">Learn more</a>', esc_url( $this->tool->doc_url() ) );
		?>
		<div class="notice notice-success is-dismissible acfcdt-dismissible"
		     data-dismiss-url="<?php echo esc_url( $this->admin_post->url( static::class ) ) ?>">
			<p><?php echo $message ?></p>
		</div>
		<?php
	}

}