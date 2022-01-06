<?php

namespace ACFCustomDatabaseTables\Service;

use ACFCustomDatabaseTables\Utils\Error;

class DocumentationProvider {

	private $remote_json_docs_url = '';

	/**
	 * Decoded JSON documentation
	 *
	 * @var array
	 */
	private $documentation = [];

	/**
	 * @param string $remote_json_docs_url
	 */
	public function __construct( $remote_json_docs_url ) {
		$this->remote_json_docs_url = $remote_json_docs_url;
	}

	/**
	 * Coordinates the remote call and decodes the response
	 */
	public function init() {
		if ( $json_string = $this->fetch_docs_json() ) {
			$json_string = $this->string_replacements( $json_string );
			$decoded = json_decode( $json_string, true );
			if ( null !== $decoded ) {
				$this->documentation = $decoded;
			}
		}
	}

	/**
	 * Performs string replacements on documentation for a better UX
	 *
	 * @param $documentation
	 *
	 * @return mixed
	 */
	public function string_replacements( $documentation ) {

		$acf_save_json_dir = acf_get_setting( 'save_json' );
		$content_dirname = basename( content_url() );
		$acf_save_json_url = content_url( substr( $acf_save_json_dir, strpos( $acf_save_json_dir, $content_dirname ) + strlen( $content_dirname ) ) );

		$url = $acf_save_json_url . '/database-tables';
		$replacement = sprintf( '<a href=\"%s\" target=\"_blank\" rel=\"noopener noreferrer\">%s</a>', $url, $url );

		return str_replace(
			'e.g; https://example.com/wp-content/themes/my-theme/acf-json/database-tables',
			$replacement,
			$documentation
		);
	}

	/**
	 * Fetches JSON docs from transient cache or, if not available, makes a remote call to fetch docs from remote
	 * server and caches them as a transient when successful.
	 *
	 * @return string
	 */
	public function fetch_docs_json() {

		$transient_key = 'acfcdt-remote-documentation';

		if ( false === ( $docs = get_transient( $transient_key ) ) ) {
			if ( $docs = $this->fetch_remote_docs_json() ) {
				set_transient( $transient_key, $docs, DAY_IN_SECONDS );
			}
		}

		return $docs ?: '';
	}

	/**
	 * Fetches the remote documentation JSON
	 *
	 * @return string
	 */
	public function fetch_remote_docs_json() {

		$remote = wp_remote_get( $this->remote_json_docs_url );

		if ( is_wp_error( $remote ) ) {
			return Error::log( 'ACF Custom Database Tables external documentation request failed. Error Message: ' . $remote->get_error_message() )->return( '' );
		}

		if ( $remote['response']['code'] !== 200 ) {
			return Error::log( 'ACF Custom Database Tables external documentation request failed with response code: ' . $remote['response']['code'] )->return( '' );
		}

		return $remote['body'];
	}

	/**
	 * Gets a property from the decoded JSON
	 *
	 * @param $prop
	 *
	 * @return mixed|string
	 */
	public function get_prop( $prop ) {
		if ( $this->documentation and isset( $this->documentation[ $prop ] ) ) {
			return $this->documentation[ $prop ];
		}

		return '';
	}

	public function support_email() {
		if ( $d = $this->get_prop( 'support' ) ) {
			return $d['email'];
		}

		return '';
	}

	public function support_default_subject() {
		if ( $d = $this->get_prop( 'support' ) ) {
			return $d['default_subject'];
		}

		return '';
	}

	public function support_email_mailto() {
		return 'mailto:' . $this->support_email() . '?subject=' . rawurlencode( $this->support_default_subject() );
	}

	public function documentation() {
		return $this->get_prop( 'documentation' );
	}

}