<?php

namespace ACFCustomDatabaseTables\Intercept;

use ACFCustomDatabaseTables\Contract\InterceptInterface;
use ACFCustomDatabaseTables\Coordinator\InterceptCoordinator;

abstract class InterceptBase implements InterceptInterface {

	/** @var  InterceptCoordinator */
	protected $coordinator;

	/**
	 * This dependency needs to be injected via a method at this time as intercepts are registered with the
	 * InterceptCoordinator which injects itself in order to act a little like a mediator.
	 *
	 * @param InterceptCoordinator $coordinator
	 *
	 * @see \ACFCustomDatabaseTables\Coordinator\InterceptCoordinator::register_intercept()
	 *
	 */
	public function set_intercept_coordinator( InterceptCoordinator $coordinator ) {
		$this->coordinator = $coordinator;
	}

}