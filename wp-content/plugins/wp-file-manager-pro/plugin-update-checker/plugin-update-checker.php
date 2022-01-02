<?php
require dirname(__FILE__) . '/Puc/v4p5/Factory.php';
require dirname(__FILE__) . '/Puc/v4/Factory.php';
require dirname(__FILE__) . '/Puc/v4p5/Autoloader.php';
new Puc_v4p5_Autoloader();
Puc_v4_Factory::addVersion('Plugin_UpdateChecker', 'Puc_v4p5_Plugin_UpdateChecker', '4.5');