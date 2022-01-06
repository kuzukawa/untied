<?php

namespace ACFCustomDatabaseTables\Controller;

use ACFCustomDatabaseTables\Contract\ControllerInterface;
use ACFCustomDatabaseTables\Coordinator\InterceptCoordinator;

/**
 * Class DefaultContextController
 * @package ACFCustomDatabaseTables\Controller
 */
class DefaultContextController implements ControllerInterface {

	/** @var InterceptCoordinator */
	private $intercept_coordinator;

	/**
	 * DefaultContextController constructor.
	 *
	 * @param InterceptCoordinator $intercept_coordinator
	 */
	public function __construct( InterceptCoordinator $intercept_coordinator ) {
		$this->intercept_coordinator = $intercept_coordinator;
	}

	public function init() {
		$this->intercept_coordinator->init();
	}

}