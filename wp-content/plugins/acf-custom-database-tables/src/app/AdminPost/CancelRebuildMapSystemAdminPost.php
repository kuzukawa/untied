<?php

namespace ACFCustomDatabaseTables\AdminPost;

use ACFCustomDatabaseTables\Background\RebuildMapSystemBackgroundTask;
use ACFCustomDatabaseTables\Facade\App;
use ACFCustomDatabaseTables\Tools\RebuildMapSystemTool;
use ACFCustomDatabaseTables\Utils\Redirect;

class CancelRebuildMapSystemAdminPost {

	const ACTION = 'acfcdt-cancel-rebuild-map-system';

	public function init() {
		add_action( 'admin_post_' . self::ACTION, [ $this, '_handle' ] );
	}

	public function url() {
		return add_query_arg( [ 'action' => self::ACTION ], admin_url( 'admin-post.php' ) );
	}

	public function _handle() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have permission to do that.' );
		}

		$this->cancel_processing();
		$this->redirect_to_tool_config();
	}

	private function cancel_processing() {
		/** @var RebuildMapSystemBackgroundTask $task */
		$task = App::make( RebuildMapSystemBackgroundTask::class );
		$task->cancel();
	}

	private function redirect_to_tool_config() {
		/** @var RebuildMapSystemTool $tool */
		$tool = App::make( RebuildMapSystemTool::class );
		Redirect::safely_to( $tool->url() );
	}

}