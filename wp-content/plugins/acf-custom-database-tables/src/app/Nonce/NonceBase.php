<?php

namespace ACFCustomDatabaseTables\Nonce;

/**
 * Class NonceBase
 * @package ACFCustomDatabaseTables\Nonce
 *
 * An abstract base class for defining concrete nonces for use throughout the application. This encapsulates nonce
 * generation and handling whilst allowing customisation to how the nonce is handled.
 */
abstract class NonceBase {

	/**
	 * @return string|int
	 */
	abstract public function action();

	/**
	 * @return string
	 */
	abstract public function name();

	/**
	 * Generate HTML input field for inclusion in forms.
	 *
	 * @return string
	 */
	public function field() {
		return wp_nonce_field( $this->action(), $this->name(), false, false );
	}

	/**
	 * Generate HTML input fields for both the nonce and the referrer field for inclusion in forms.
	 *
	 * @return string
	 */
	public function field_with_referrer() {
		return wp_nonce_field( $this->action(), $this->name(), true, false );
	}

	/**
	 * Create a nonce token.
	 *
	 * @return false|string
	 */
	public function create() {
		return wp_create_nonce( $this->action() );
	}

	/**
	 * Get the nonce token from the request, if available.
	 *
	 * @return mixed|string
	 */
	public function get_value() {
		return isset( $_REQUEST[ $this->name() ] ) ? $_REQUEST[ $this->name() ] : '';
	}

	/**
	 * Check if nonce is valid.
	 *
	 * @return bool
	 */
	public function is_valid() {
		return false !== $this->verify();
	}

	/**
	 * Verify nonce using WP's core function. Useful if more information is needed.
	 *
	 * @return false|int
	 */
	public function verify() {
		return wp_verify_nonce( $this->get_value(), $this->action() );
	}

	/**
	 * Check that the nonce both exists and is valid. If either of these fail, the corresponding handler will be
	 * invoked.
	 */
	public function validate() {
		if ( empty( $this->get_value() ) ) {
			$this->handle_missing_nonce();
		} else if ( false === $this->verify() ) {
			$this->handle_invalid_nonce();
		}
	}

	/**
	 * Behaviour to run when the nonce is missing from the request.
	 */
	protected function handle_missing_nonce() {
		wp_die( 'You cannot do that — the nonce is missing.', 'Error', [ 'back_link' => true ] );
	}

	/**
	 * Behaviour to run when the nonce is invalid.
	 */
	protected function handle_invalid_nonce() {
		wp_die( 'You cannot do that — the nonce is invalid.' );
	}

}