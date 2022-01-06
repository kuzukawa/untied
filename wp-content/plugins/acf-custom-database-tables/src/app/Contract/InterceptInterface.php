<?php

namespace ACFCustomDatabaseTables\Contract;

interface InterceptInterface {

	/**
	 * Hooks anything needed by the intercept in order to intercept data for return to InterceptCoordinator
	 */
	public function init();

}