<?php

namespace ACFCustomDatabaseTables\Contract;

use ACFCustomDatabaseTables\Container;

interface HasActivationRoutine {

	public function run_activation_routine( Container $c );

}