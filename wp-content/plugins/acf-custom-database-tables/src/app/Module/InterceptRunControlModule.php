<?php

namespace ACFCustomDatabaseTables\Module;

use ACFCustomDatabaseTables\Contract\ModuleInterface;
use ACFCustomDatabaseTables\Intercept\ACFGetFieldIntercept;
use ACFCustomDatabaseTables\Intercept\ACFUpdateFieldIntercept;
use ACFCustomDatabaseTables\Vendor\Pimple\Container;

/**
 * Class InterceptRunControlModule
 * @package ACFCustomDatabaseTables\Module
 *
 * Provide public action hooks for controlling the run state of data intercepts. This module makes it possible to stop
 * data from being stored in or loaded from a custom database table at any point in the theme lifecycle.
 */
class InterceptRunControlModule implements ModuleInterface {

	private $app;

	/**
	 * @param $app
	 */
	public function __construct( Container $app ) {
		$this->app = $app;
	}

	/** @return string The module name */
	public function name() {
		return 'intercept_run_control';
	}

	public function init() {
		add_action( 'acfcdt/disable_get_field_intercept', [ $this, '_disable_get_field_intercept' ] );
		add_action( 'acfcdt/enable_get_field_intercept', [ $this, '_enable_get_field_intercept' ] );
		add_action( 'acfcdt/disable_update_field_intercept', [ $this, '_disable_update_field_intercept' ] );
		add_action( 'acfcdt/enable_update_field_intercept', [ $this, '_enable_update_field_intercept' ] );
	}

	public function _disable_get_field_intercept() {
		$this->app[ ACFGetFieldIntercept::class ]->disable();
	}

	public function _enable_get_field_intercept() {
		$this->app[ ACFGetFieldIntercept::class ]->enable();
	}

	public function _disable_update_field_intercept() {
		$this->app[ ACFUpdateFieldIntercept::class ]->disable();
	}

	public function _enable_update_field_intercept() {
		$this->app[ ACFUpdateFieldIntercept::class ]->enable();
	}

}