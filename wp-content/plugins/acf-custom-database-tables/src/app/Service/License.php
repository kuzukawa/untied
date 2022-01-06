<?php

namespace ACFCustomDatabaseTables\Service;

use ACFCustomDatabaseTables\Utils\Error;

class License {

	const LICENSE_PAGE = 'edit.php?post_type=acf-field-group&page=acf-custom-database-tables&acfcdt-section=license'; // todo - this should be handled differently
	const STORE_URL = 'https://hookturn.io/';
	const ITEM_NAME = 'ACF Custom Database Tables'; // todo - might need to double check this
	const GROUP = 'acfcdt_license';
	const LICENSE_KEY = 'acfcdt_license';
	const STATUS_KEY = 'acfcdt_license_status';
	const NONCE = 'acfcdt_license_nonce';

	public function init() {
		add_action( 'admin_menu', [ $this, 'register_options' ] );
	}

	/**
	 * note - accessing these constants on an object property – e.g; $this->license::GROUP – was causing issues for some
	 * people. Hence, the accessor method.
	 *
	 * @return string
	 */
	public function group() {
		return self::GROUP;
	}

	/**
	 * note - accessing these constants on an object property – e.g; $this->license::LICENSE_KEY ¬ was causing issues
	 * for some people. Hence, the accessor method.
	 *
	 * @return string
	 */
	public function license_key() {
		return self::LICENSE_KEY;
	}

	public function register_options() {
		register_setting( self::GROUP, self::LICENSE_KEY, [ $this, 'sanitize_key' ] );
	}

	/**
	 * @return string
	 */
	public function get() {
		return trim( get_option( self::LICENSE_KEY ) );
	}

	/**
	 * @return mixed
	 */
	public function get_status() {
		return get_option( self::STATUS_KEY );
	}

	/**
	 * @return bool
	 */
	public function delete_status() {
		return delete_option( self::STATUS_KEY );
	}

	/**
	 * @return bool
	 */
	public function queue_for_reactivation() {
		return $this->delete_status();
	}

	/**
	 * @return bool
	 */
	public function is_valid() {
		return $this->get_status() === 'valid';
	}

	/**
	 * @return bool
	 */
	public function remote_check_if_valid() {

		$response = wp_remote_post( self::STORE_URL, [
			'timeout' => 15,
			'sslverify' => false,
			'body' => [
				'edd_action' => 'check_license',
				'license' => $this->get(),
				'item_name' => urlencode( self::ITEM_NAME ),
				'url' => home_url()
			]
		] );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		return ( $license_data->license === 'valid' );
	}

	public function activate() {

		// Call the custom API.
		$response = wp_remote_post( self::STORE_URL, [
			'timeout' => 15,
			'sslverify' => false,
			'body' => [
				'edd_action' => 'activate_license',
				'license' => $this->get(),
				'item_name' => urlencode( self::ITEM_NAME ),
				'url' => home_url()
			]
		] );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.' );
			}
		} else {

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( false === $license_data->success ) {
				switch ( $license_data->error ) {
					case 'expired' :
						$message = sprintf(
							__( 'Your license key expired on %s.' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
						);
						break;
					case 'revoked' :
						$message = __( 'Your license key has been disabled.' );
						break;
					case 'missing' :
						$message = __( 'Invalid license.' );
						break;
					case 'invalid' :
					case 'site_inactive' :
						$message = __( 'Your license is not active for this URL.' );
						break;
					case 'item_name_mismatch' :
						$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), self::ITEM_NAME );
						break;
					case 'no_activations_left':
						$message = __( 'Your license key has reached its activation limit.' );
						break;
					default :
						$message = __( 'An error occurred, please try again.' );
						break;
				}
			}
		}

		// failed if a message exists
		if ( ! empty( $message ) ) {

			$base_url = admin_url( self::LICENSE_PAGE );
			$redirect = add_query_arg( array(
				'acfcdt_activation' => 'false',
				'message' => urlencode( $message )
			), $base_url );
			wp_redirect( $redirect );
			exit();

		} else if ( isset( $license_data ) and is_object( $license_data ) ) {

			update_option( self::STATUS_KEY, $license_data->license );
			wp_redirect( admin_url( self::LICENSE_PAGE ) );
			exit();

		} else {

			Error::log( 'ACFCDT Plugin\'s license activation failed without any message or license data.' );

		}
	}

	public function deactivate() {

		$response = wp_remote_post( self::STORE_URL, [
			'timeout' => 15,
			'sslverify' => false,
			'body' => [
				'edd_action' => 'deactivate_license',
				'license' => $this->get(),
				'item_name' => urlencode( self::ITEM_NAME ), // the name of our product in EDD
				'url' => home_url()
			]
		] );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.' );
			}

			$base_url = admin_url( self::LICENSE_PAGE );
			$redirect = add_query_arg( array(
				'acfcdt_activation' => 'false',
				'message' => urlencode( $message )
			), $base_url );

			wp_redirect( $redirect );
			exit();
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( $license_data->license === 'deactivated' ) {
			$this->delete_status();
		}

		wp_redirect( admin_url( self::LICENSE_PAGE ) );
		exit();
	}

	/**
	 * @return string
	 */
	public function get_nonce() {
		return wp_nonce_field( self::NONCE, self::NONCE, true, false );
	}

	/**
	 * @return false|int
	 */
	public function verify_nonce() {
		return check_admin_referer( self::NONCE, self::NONCE );
	}

	/**
	 * @param $new_license
	 *
	 * @return mixed
	 */
	public function sanitize_key( $new_license ) {

		$old = $this->get();

		if ( $old && $old != $new_license ) {
			$this->queue_for_reactivation();
		}

		return $new_license;
	}

}