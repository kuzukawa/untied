<?php

namespace ACFCustomDatabaseTables\Contract;

interface ModuleInterface {

	/** @return string The module name */
	public function name();

	public function init();

}