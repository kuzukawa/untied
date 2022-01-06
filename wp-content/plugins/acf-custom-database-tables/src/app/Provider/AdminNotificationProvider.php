<?php

namespace ACFCustomDatabaseTables\Provider;

use ACFCustomDatabaseTables\AdminPost\DismissAdminNoticeAdminPost;
use ACFCustomDatabaseTables\Notice\RebuildMapSystemCompleteNotice;
use ACFCustomDatabaseTables\Notice\RebuildMapSystemRunningNotice;
use ACFCustomDatabaseTables\Tools\RebuildMapSystemTool;
use ACFCustomDatabaseTables\UI\AdminNoticeHandler;
use ACFCustomDatabaseTables\UI\PersistentAdminNoticeHandler;
use ACFCustomDatabaseTables\Vendor\Pimple\Container;
use ACFCustomDatabaseTables\Vendor\Pimple\ServiceProviderInterface;

class AdminNotificationProvider implements ServiceProviderInterface {

	public function register( Container $c ) {
		foreach ( $this->definitions() as $key => $callback ) {
			$c[ $key ] = $callback;
		}
	}

	public function boot( Container $c ) {
		if ( is_admin() ) {
			$c['global_admin_notices']->init();
			$c[ RebuildMapSystemCompleteNotice::class ]->init();
			$c[ DismissAdminNoticeAdminPost::class ]->init();
		}
	}

	private function definitions() {
		return [
			AdminNoticeHandler::class => function ( Container $c ) {
				return new AdminNoticeHandler();
			},
			PersistentAdminNoticeHandler::class => function ( Container $c ) {
				return new PersistentAdminNoticeHandler();
			},
			// We need a global admin notice handling instance that displays notifications on any page in the admin.
			// Hence, the string based key in order to reuse the object.
			'global_admin_notices' => function ( Container $c ) {
				return new AdminNoticeHandler();
			},
			RebuildMapSystemRunningNotice::class => function ( Container $c ) {
				return new RebuildMapSystemRunningNotice(
					$c['global_admin_notices'],
					$c[ RebuildMapSystemTool::class ] );
			},
			RebuildMapSystemCompleteNotice::class => function ( Container $c ) {
				return new RebuildMapSystemCompleteNotice(
					$c[ RebuildMapSystemTool::class ],
					$c[ DismissAdminNoticeAdminPost::class ] );
			},
			DismissAdminNoticeAdminPost::class => function ( Container $c ) {
				return new DismissAdminNoticeAdminPost();
			},

			// Back compat — remove these in version 1.2
			'admin_notice_handler' => function ( Container $c ) {
				_deprecated_function( "'admin_notice_handler' container binding ", 1.1, AdminNoticeHandler::class );

				return $c[ AdminNoticeHandler::class ];
			},
			'persistent_admin_notice_handler' => function ( Container $c ) {
				_deprecated_function( "'persistent_admin_notice_handler' container binding ", 1.1, PersistentAdminNoticeHandler::class );

				return $c[ PersistentAdminNoticeHandler::class ];
			},
		];
	}

}