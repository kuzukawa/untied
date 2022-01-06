<?php

namespace ACFCustomDatabaseTables\Contract;

interface UpgradeInterface {

	/** @return bool */
	public function should_upgrade();

	public function run_upgrade();

}