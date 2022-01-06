<?php

namespace ACFCustomDatabaseTables\Notice;

use ACFCustomDatabaseTables\Tools\RebuildMapSystemTool;
use ACFCustomDatabaseTables\UI\AdminNoticeHandler;

class RebuildMapSystemRunningNotice {

	/** @var AdminNoticeHandler */
	private $notifier;

	/** @var RebuildMapSystemTool */
	private $tool;

	/**
	 * @param AdminNoticeHandler $notifier
	 * @param RebuildMapSystemTool $tool
	 */
	public function __construct( AdminNoticeHandler $notifier, RebuildMapSystemTool $tool ) {
		$this->notifier = $notifier;
		$this->tool = $tool;
	}

	public function init() {
		if ( $this->should_render() ) {
			$message = '<strong>ACF Custom Database Tables</strong> is currently rebuilding files used to map data in and out of custom DB tables. This should only take a moment.';
			$message .= sprintf( '<br><a href="%s">Check status</a>', esc_url( $this->tool->status_url() ) );
			$message .= sprintf( ' | <a class="acfcdt-external" href="%s" target="_blank">Learn more</a>', esc_url( $this->tool->doc_url() ) );
			$this->notifier->add_info( $message );
		}
	}

	/**
	 * Display the notification everywhere except on the tool's management page.
	 *
	 * @return bool
	 */
	private function should_render() {
		return ! $this->tool->is_current_page();
	}

}