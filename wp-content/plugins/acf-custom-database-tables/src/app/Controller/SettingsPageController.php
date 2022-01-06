<?php

namespace ACFCustomDatabaseTables\Controller;

use ACFCustomDatabaseTables\Contract\ControllerInterface;
use ACFCustomDatabaseTables\Facade\App;
use ACFCustomDatabaseTables\Provider\ToolsProvider;
use ACFCustomDatabaseTables\Service\DiagnosticReporter;
use ACFCustomDatabaseTables\Service\DocumentationProvider;
use ACFCustomDatabaseTables\Tools\RebuildMapSystemTool;
use ACFCustomDatabaseTables\Tools\ToolBase;
use ACFCustomDatabaseTables\UI\AdminNoticeHandler;
use ACFCustomDatabaseTables\UI\AssetManager;
use ACFCustomDatabaseTables\UI\PersistentAdminNoticeHandler;
use ACFCustomDatabaseTables\Utils\Arr;
use ACFCustomDatabaseTables\Utils\View;

/**
 * Class SettingsPageController
 * @package ACFCustomDatabaseTables\Controller
 */
class SettingsPageController implements ControllerInterface {

	/**
	 * The ACF main options page slug.
	 * @var string
	 */
	private $acf_parent_slug = 'edit.php?post_type=acf-field-group';

	/** @var  DiagnosticReporter */
	private $diagnostic;

	/** @var DocumentationProvider */
	private $docs;

	/** @var LicenseFormController */
	private $license_form;

	/** @var AssetManager */
	private $asset_manager;

	/** @var AdminNoticeHandler */
	private $notifier;

	/**
	 * SettingsPageController constructor.
	 *
	 * @param DiagnosticReporter $diagnostic_reporter
	 * @param DocumentationProvider $documentation_provider
	 * @param LicenseFormController $license_form_controller
	 * @param AssetManager $asset_manager
	 * @param AdminNoticeHandler $notifier
	 */
	public function __construct( DiagnosticReporter $diagnostic_reporter, DocumentationProvider $documentation_provider, LicenseFormController $license_form_controller, AssetManager $asset_manager, AdminNoticeHandler $notifier ) {
		$this->diagnostic = $diagnostic_reporter;
		$this->docs = $documentation_provider;
		$this->license_form = $license_form_controller;
		$this->asset_manager = $asset_manager;
		$this->notifier = $notifier;
	}

	public function init() {
		add_action( 'admin_menu', [ $this, 'register_settings_page' ] );

		// If URL has a flag to display any persistent notices, run the admin notice handler.
		if ( Arr::get( $_GET, PersistentAdminNoticeHandler::FLAG, false ) ) {
			/** @var PersistentAdminNoticeHandler $notices */
			$notices = App::make( PersistentAdminNoticeHandler::class );
			$notices->set_target_hook( 'admin_notices' );
			$notices->init();
			$notices->restore();
		}
	}

	/**
	 * Context-specific initialisation
	 */
	public function run() {
		$this->notifier->init();
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Add an ACF submenu page to house our own settings pages.
	 */
	public function register_settings_page() {
		// This slug changes depending on the site's language settings due to the way WordPress builds admin page hooks
		// using the menu title. @see \add_menu_page().
		// Using the slug this way facilitates the hook changing when the menu title is translated, as per ACF's
		// approach in \ACF_Admin::admin_menu().
		$slug = add_submenu_page(
			$this->acf_parent_slug,
			'ACF Custom Database Tables',
			'Database Tables',
			'manage_options',
			'acf-custom-database-tables',
			[ $this, 'render' ]
		);
		add_action( 'load-' . $slug, [ $this, 'run' ] );
	}


//	/**
//	 * Checks whether we are on our own settings/admin page
//	 *
//	 * @return bool
//	 */
//	public function is_plugin_admin() {
//		global $plugin_page;
//
//		return $plugin_page === 'acf-custom-database-tables';
//	}

	public function enqueue_assets() {
		$this->asset_manager->enqueue_script( 'acfcdt-admin' );
		$this->asset_manager->enqueue_style( 'acfcdt-admin' );
	}

	/**
	 * @param $section
	 *
	 * @return string
	 */
	public function section_url( $section ) {
		return add_query_arg( [
			'page' => 'acf-custom-database-tables',
			'acfcdt-section' => $section,
		], admin_url( $this->acf_parent_slug ) );
	}

	/**
	 * Get URL to a specific tool view
	 *
	 * @param $tool
	 *
	 * @return string
	 */
	public function tool_url( $tool ) {
		return add_query_arg( 'acfcdt-tool', $tool, $this->section_url( 'tools' ) );
	}

	public function current_section() {
		return isset( $_GET['acfcdt-section'] )
			? $_GET['acfcdt-section']
			: 'manage';
	}

	/**
	 * @param $section
	 *
	 * @return bool
	 */
	public function is_section( $section ) {
		$current = $this->current_section();

		return $current === $section;
	}

	/**
	 *
	 */
	public function render() {

		$this->docs->init();

		$current_section = $this->current_section();

		switch ( $current_section ) {
			case 'help':
				$section_content = $this->render_help_section();
				break;
			case 'system':
				$section_content = $this->render_system_section();
				break;
			case 'license':
				$section_content = $this->render_license_section();
				break;
			case 'settings':
				$section_content = $this->render_settings_section();
				break;
			case 'tools':
				$section_content = $this->render_tools_section();
				break;
			case 'manage':
			default:
				$section_content = $this->render_manage_section();
				break;
		}

		View::render( 'settings-page', [
			'current_section' => $current_section,
			'section_links' => [
				[
					'name' => 'manage',
					'text' => 'Manage Tables',
					'title' => 'Run the table create/update process',
					'href' => $this->section_url( 'manage' )
				],
				[
					'name' => 'system',
					'text' => 'System Check',
					'title' => 'Run a system health check',
					'href' => $this->section_url( 'system' )
				],
				[
					'name' => 'license',
					'text' => 'License',
					'title' => 'Manage your license',
					'href' => $this->section_url( 'license' )
				],
				[
					'name' => 'tools',
					'text' => 'Tools',
					'title' => 'Tools and processes',
					'href' => $this->section_url( 'tools' )
				],
				[
					'name' => 'settings',
					'text' => 'Settings',
					'title' => 'Settings',
					'href' => $this->section_url( 'settings' )
				],
				[
					'name' => 'help',
					'text' => 'Help',
					'title' => 'Get help or contact support',
					'href' => $this->section_url( 'help' )
				],
			],
			'section_content' => $section_content
		] );
	}

	/**
	 *
	 */
	public function render_manage_section() {
		$checks = $this->diagnostic->system_checks();
		$failed_checks = $this->diagnostic->failed_system_checks( $checks );

		return View::prepare( 'admin-sections/manage', [
			'help_section_url' => $this->section_url( 'help' ),
			'system_section_url' => $this->section_url( 'system' ),
			'system_problems_detected' => (bool) $failed_checks,
			'json_definition_file_count' => $this->diagnostic->json_definition_file_count(),
			'json_definition_files' => $this->diagnostic->json_definition_file_list()
		] );
	}

	public function render_system_section() {
		$system_checks = $this->diagnostic->system_checks();

		return View::prepare( 'admin-sections/system', [
			'checks' => $system_checks,
			'failed_checks' => $this->diagnostic->failed_system_checks( $system_checks ),
			'help_section_url' => $this->section_url( 'help' ),
		] );
	}

	public function render_license_section() {
		return View::prepare( 'admin-sections/license', [
			'license_form' => $this->license_form->render()
		] );
	}

	public function render_settings_section() {
		return View::prepare( 'admin-sections/settings', [
			//'license_form' => $this->license_form->render()
		] );
	}

	private function render_tools_section() {
		/** @var ToolsProvider $provider */
		$provider = App::provider( ToolsProvider::class );
		$tools = $provider->get_all_tools();

		// If a specific tool's sub view is requested, render it.
		if ( $view = isset( $_GET['acfcdt-tool'] ) ? $_GET['acfcdt-tool'] : false ) {
			foreach ( $tools as $tool ) {
				if ( $tool->slug() === $view ) {
					return $tool->render();
				}
			}
		}

		// Show all tools.
		return View::prepare( 'admin-sections/tools', [
			'tools' => $tools,
		] );
	}

	/**
	 *
	 */
	public function render_help_section() {

		$d = $this->diagnostic;
		$data = [
			[
				'name' => 'PHP version',
				'value' => $d->php_version() ?: '?',
			],
			[
				'name' => 'MySQL version',
				'value' => $d->mysql_version() ?: '?',
			],
			[
				'name' => 'WP version',
				'value' => $d->wp_version() ?: '?',
			],
			[
				'name' => 'WP directory',
				'value' => $d->wp_directory() ?: '?',
			],
			[
				'name' => 'Database name',
				'value' => $d->database_name() ?: '?',
			],
			[
				'name' => 'Database table prefix',
				'value' => $d->database_table_prefix() ?: '?',
			],
			[
				'name' => 'Database tables',
				'value' => implode( PHP_EOL . "\t", $d->database_table_list() ),
			],
			[
				'name' => 'Site URL',
				'value' => $d->site_url() ?: '?',
			],
			[
				'name' => 'Home URL',
				'value' => $d->home_url() ?: '?',
			],
			[
				'name' => 'Is multisite?',
				'value' => $d->is_multisite() ? 'Yes' : 'No',
			],
			[
				'name' => 'Web server',
				'value' => $d->webserver() ?: '?',
			],
			[
				'name' => 'WP memory limit',
				'value' => $d->wp_memory_limit() ?: '?',
			],
			[
				'name' => 'PHP time limit',
				'value' => $d->php_time_limit() ?: '?',
			],
			[
				'name' => 'Debug mode enabled?',
				'value' => $d->is_debug_mode_enabled() ? 'Yes' : 'No',
			],
			[
				'name' => 'Script debug enabled?',
				'value' => $d->is_script_debug_enabled() ? 'Yes' : 'No',
			],
			[
				'name' => 'Theme name',
				'value' => $d->theme_name() ?: '?',
			],
			[
				'name' => 'Theme directory',
				'value' => $d->theme_dir() ?: '?',
			],
			[
				'name' => 'Is child theme?',
				'value' => $d->is_child_theme() ? 'Yes' : 'No',
			],
			[
				'name' => 'Active plugins',
				'value' => implode( PHP_EOL . "\t", $d->map_stringify_active_plugin_data( $d->get_active_plugins_data() ) ),
			],
			[
				'name' => 'MU plugins',
				'value' => implode( PHP_EOL . "\t", $d->map_stringify_active_plugin_data( $d->get_mu_plugins_data() ) ),
			],
			[
				'name' => 'ACF version',
				'value' => $d->acf_version() ?: '?',
			],
			[
				'name' => 'Is using ACF JSON?',
				'value' => $d->is_using_acf_json() ? 'Yes' : 'No',
			],
			[
				'name' => 'ACF JSON save point',
				'value' => $d->acf_json_directory() ? $d->append_accessibility_info( $d->acf_json_directory() ) : '?',
			],
			[
				'name' => 'ACF JSON load points',
				'value' => implode( PHP_EOL . "\t", $d->map_accessibility_info( $d->acf_json_load_points() ) ),
			],
			[
				'name' => 'ACFCDT JSON directory',
				'value' => $d->acfcdt_json_directory() ? $d->append_accessibility_info( $d->acfcdt_json_directory() ) : '?',
			],
			[
				'name' => 'ACFCDT directory contents',
				'value' => implode( PHP_EOL . "\t", $d->map_accessibility_info( $d->acfcdt_json_directory_contents() ) ),
			],
			[
				'name' => 'ACFCDT table map cache dir',
				'value' => $d->acfcdt_table_map_cache_dir()
					? $d->append_accessibility_info( $d->acfcdt_table_map_cache_dir() )
					: '?',
			],
			[
				'name' => 'ACFCDT table map cache dir contents',
				'value' => implode( PHP_EOL . "\t", $d->map_accessibility_info( $d->acfcdt_table_map_cache_dir_contents() ) ),
			],
			[
				'name' => 'Using external object cache?',
				'value' => $d->is_using_external_object_cache() ? 'Yes' : 'No',
			],
			[
				'name' => 'Settings',
				'value' => implode( PHP_EOL . "\t", $d->settings() ),
			],
		];

		if ( $red_flags = $d->red_flags() ) {
			array_unshift( $data, [
				'name' => 'RED FLAGS',
				'value' => implode( PHP_EOL . "\t", $red_flags )
			] );
		}

		return View::prepare( 'admin-sections/help', [
			'documentation_sections' => $this->docs->documentation(),
			'support_email' => $this->docs->support_email(),
			'support_mailto' => $this->docs->support_email_mailto(),
			'system_checks' => $this->diagnostic->system_checks(),
			'system_check_data' => $data,
		] );
	}

}


