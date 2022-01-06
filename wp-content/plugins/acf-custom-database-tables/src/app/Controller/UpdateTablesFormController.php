<?php

namespace ACFCustomDatabaseTables\Controller;

use ACFCustomDatabaseTables\Contract\ControllerInterface;
use ACFCustomDatabaseTables\Coordinator\TableCreationCoordinator;
use ACFCustomDatabaseTables\UI\AdminNoticeHandler;

/**
 * Class UpdateTablesFormController
 * @package ACFCustomDatabaseTables\Controller
 *
 * Handles the rendering of the form, parses post vars on submission, and enqueues necessary admin notices
 */
class UpdateTablesFormController implements ControllerInterface {

	/** @var  TableCreationCoordinator */
	private $table_creation_coordinator;

	/** @var  AdminNoticeHandler */
	private $notifier;

	const POST_NAMESPACE = 'acf-custom-database-tables';

	const NONCE = 'acf-custom-database-tables-update-tables';

	/**
	 * UpdateTablesFormController constructor.
	 *
	 * @param TableCreationCoordinator $table_creation_coordinator
	 * @param AdminNoticeHandler $notifier
	 */
	public function __construct( TableCreationCoordinator $table_creation_coordinator, AdminNoticeHandler $notifier ) {
		$this->table_creation_coordinator = $table_creation_coordinator;
		$this->notifier = $notifier;
	}

	public function init() {
		add_action( 'admin_init', [ $this, 'handle_form_submission' ] );
		add_action( 'acfcdt/hook/settings_page_content', [ $this, 'render' ] );
	}

	/**
	 * Parses post data
	 */
	public function handle_form_submission() {
		if ( ! isset( $_POST[ self::POST_NAMESPACE ] ) ) {
			return;
		}

		if ( false === wp_verify_nonce( $this->get_post_var( 'nonce' ), self::NONCE ) ) {
			$this->notifier->add_error( 'Nonce invalid. Try refreshing the page, then running the process again.' );

			return;
		}

		if ( $this->get_post_var( 'update-tables' ) and ! $this->get_post_var( 'confirmation' ) ) {
			$this->notifier->add_error( 'You need to confirm you have taken a backup before you can modify your database' );
		} else {
			$update = $this->table_creation_coordinator->update_tables();
			if ( is_wp_error( $update ) ) {
				$errors = $update->get_error_messages();
				foreach ( $errors as $error ) {
					$this->notifier->add_error( $error );
				}
			} elseif ( is_array( $update ) ) {

				foreach ( $update as $notice ) {
					$this->notifier->add_success( $notice );
				}

				$cache = $this->table_creation_coordinator->rebuild_map_cache();
				if ( is_wp_error( $cache ) ) {
					$errors = $cache->get_error_messages();
					foreach ( $errors as $error ) {
						$this->notifier->add_error( $error );
					}
				} else {
					$this->notifier->add_success( "<strong>Additional output:</strong>" );
					$this->notifier->add_success( "Table map cache rebuilt." );
				}
			}
		}

	}

	/**
	 * Renders form
	 */
	public function render() {
		$nonce = wp_create_nonce( self::NONCE );
		?>
		<form method="POST">
			<input type="hidden" name="<?php echo self::POST_NAMESPACE ?>[update-tables]" value="1">
			<input type="hidden" name="<?php echo self::POST_NAMESPACE ?>[nonce]" value="<?php echo $nonce ?>">
			<input type="checkbox" id="confirmation" name="<?php echo self::POST_NAMESPACE ?>[confirmation]">
			<label for="confirmation">I understand that this will modify my database and have taken a full backup in
				case I need to roll back.</label>
			<br>
			<br>
			<input type="submit" value="Create/Update Tables" class="button button-primary button-large">
		</form>
		<?php
	}

	/**
	 * Extracts namespaced post variable if available or returns specified default if not.
	 *
	 * @param $name
	 * @param string $default
	 *
	 * @return string
	 */
	private function get_post_var( $name, $default = '' ) {
		return isset( $_POST[ self::POST_NAMESPACE ][ $name ] )
			? $_POST[ self::POST_NAMESPACE ][ $name ]
			: $default;
	}

}