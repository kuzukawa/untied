<?php

namespace ACFCustomDatabaseTables;

use ACFCustomDatabaseTables\Facade\App;

/**
 * Class Activator
 * @package ACFCustomDatabaseTables
 *
 * Activation utils
 */
class Activator {

	/**
	 * Static
	 *
	 * @return bool
	 */
	public static function is_acf_installed() {
		return class_exists( 'ACF' );
	}

	/**
	 * Checks requirements for activation and, if not available, dies with an informative message. This is for use on
	 * plugin activation.
	 */
	public static function check_activation_constraints() {

		if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {
			ob_start();
			?>
			<h1>Incompatible PHP Version</h1>
			<p>The <em>ACF Custom Database Tables</em> plugin requires PHP version 5.6 or greater to work.</p>
			<p>Your current version: <?php echo PHP_VERSION ?>.</p>
			<?php
			wp_die( ob_get_clean(), 'System Compatibility Issue', array( 'back_link' => true ) );
		}

		if ( ! self::is_acf_installed() ) {
			ob_start();
			?>
			<h1>Oops! Missing ACF…</h1>
			<p>In order to use the <em>ACF Custom Database Tables</em> plugin, you need to also have the <em>Advanced
					Custom Fields</em> plugin (version 5.6.10 or higher) installed and activated.
			</p>
			<p><a href="https://www.advancedcustomfields.com/" class="button button-primary button-large"
			      target="_blank"
			      rel="noopener noreferrer">Get <em>Advanced Custom Fields</em></a></p>
			<?php
			wp_die( ob_get_clean(), 'Missing Dependency', array( 'back_link' => true ) );
		}

	}

	public static function set_activation_states() {
		// Make container bindings available.
		acf_custom_database_tables();
		App::run_activation_routine_handlers();
	}

}