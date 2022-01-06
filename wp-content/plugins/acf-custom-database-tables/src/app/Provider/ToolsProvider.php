<?php

namespace ACFCustomDatabaseTables\Provider;

use ACFCustomDatabaseTables\AdminPost\CancelRebuildMapSystemAdminPost;
use ACFCustomDatabaseTables\AdminPost\RebuildMapSystemAdminPost;
use ACFCustomDatabaseTables\Background\RebuildMapSystemBackgroundTask;
use ACFCustomDatabaseTables\Controller\SettingsPageController;
use ACFCustomDatabaseTables\Facade\App;
use ACFCustomDatabaseTables\Nonce\RebuildMapSystemNonce;
use ACFCustomDatabaseTables\Tools\RebuildMapSystemTool;
use ACFCustomDatabaseTables\Tools\ToolBase;
use ACFCustomDatabaseTables\UI\PersistentAdminNoticeHandler;
use ACFCustomDatabaseTables\Vendor\Pimple\Container;
use ACFCustomDatabaseTables\Vendor\Pimple\ServiceProviderInterface;

class ToolsProvider implements ServiceProviderInterface {

	public function register( Container $c ) {
		foreach ( $this->definitions() as $key => $callback ) {
			$c[ $key ] = $callback;
		}
	}

	public function boot( Container $c ) {
		if ( is_admin() ) {
			$c[ RebuildMapSystemAdminPost::class ]->init();
			$c[ CancelRebuildMapSystemAdminPost::class ]->init();
		}
	}

	public function definitions() {
		return [
			RebuildMapSystemTool::class => function ( Container $c ) {
				return new RebuildMapSystemTool(
					$c[ RebuildMapSystemBackgroundTask::class ],
					$c[ SettingsPageController::class ],
					$c[ RebuildMapSystemAdminPost::class ]
				);
			},
			RebuildMapSystemNonce::class => function ( Container $c ) {
				return new RebuildMapSystemNonce();
			},
			RebuildMapSystemAdminPost::class => function ( Container $c ) {
				return new RebuildMapSystemAdminPost(
					$c[ RebuildMapSystemNonce::class ],
					$c[ PersistentAdminNoticeHandler::class ],
					$c[ RebuildMapSystemBackgroundTask::class ]
				);
			},
			CancelRebuildMapSystemAdminPost::class => function ( Container $c ) {
				return new CancelRebuildMapSystemAdminPost();
			},
		];
	}

	/**
	 * Build an array of resolved tool classes. This will only contain tools that extend the ToolBase class.
	 *
	 * @return ToolBase[]
	 */
	public function get_all_tools() {
		$tools = [];
		foreach ( $this->definitions() as $key => $callback ) {
			if ( is_subclass_of( $key, ToolBase::class ) and $i = App::make( $key ) ) {
				$tools[] = $i;
			}
		}

		return $tools;
	}

}