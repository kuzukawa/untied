<?php

namespace ACFCustomDatabaseTables\Tools;

abstract class ToolBase {

	/**
	 * @return string
	 */
	abstract public function slug();

	/**
	 * @return string
	 */
	abstract public function name();

	/**
	 * @return string The HTML markup containing descriptive text and link/s to start/navigate to the tool.
	 */
	abstract public function description();

	/**
	 * @return string The rendered view for displaying this particular tool's individual UI.
	 */
	abstract public function render();

}