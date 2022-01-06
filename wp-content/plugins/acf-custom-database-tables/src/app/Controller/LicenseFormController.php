<?php

namespace ACFCustomDatabaseTables\Controller;

use ACFCustomDatabaseTables\Contract\ControllerInterface;
use ACFCustomDatabaseTables\Service\License;
use ACFCustomDatabaseTables\Utils\View;

/**
 * Class LicenseFormController
 * @package ACFCustomDatabaseTables\Controller
 */
class LicenseFormController implements ControllerInterface {

	const DEACTIVATE_KEY = 'acfcdt_license_deactivate';
	const ACTIVATE_KEY = 'acfcdt_license_activate';

	/** @var License */
	private $license;

	/**
	 * LicenseFormController constructor.
	 *
	 * @param License $license
	 */
	public function __construct( License $license ) {
		$this->license = $license;
	}

	public function init() {
		$this->license->init();
		add_action( 'admin_menu', [ $this, 'watch' ] );
		add_action( 'admin_notices', [ $this, 'temp_admin_messages' ] );
	}

	/**
	 * Keeps an eye out for triggered de/activation processes, checks were allowed to proceed, then actions requests.
	 */
	public function watch() {
		if ( $this->activation_triggered() and $this->license->verify_nonce() ) {
			$this->license->activate();
		}

		if ( $this->deactivation_triggered() and $this->license->verify_nonce() ) {
			$this->license->deactivate();
		}
	}

	/**
	 * @return bool
	 */
	private function activation_triggered() {
		return isset( $_POST[ self::ACTIVATE_KEY ] );
	}

	/**
	 * @return bool
	 */
	private function deactivation_triggered() {
		return isset( $_POST[ self::DEACTIVATE_KEY ] );
	}

	public function render() {
		return View::prepare( 'license-form', [
			'license' => $this->license->get(),
			'license_input_name' => $this->license->license_key(),
			'option_group' => $this->license->group(),
			'license_is_valid' => $this->license->is_valid(),
			'deactivate_input_name' => self::DEACTIVATE_KEY,
			'activate_input_name' => self::ACTIVATE_KEY,
			'nonce' => $this->license->get_nonce(),
		] );
	}

	/**
	 * Temporary admin notices handling until we properly set up our admin notifier object as a dep
	 */
	public function temp_admin_messages() {
		if ( isset( $_GET['acfcdt_activation'] ) && ! empty( $_GET['message'] ) ) {
			switch ( $_GET['acfcdt_activation'] ) {
				case 'false':
					$message = urldecode( $_GET['message'] );
					?>
					<div class="error">
						<p><?php echo $message; ?></p>
					</div>
					<?php
					break;

				case 'true':
				default:
					// nothing here
					break;
			}
		}
	}

}