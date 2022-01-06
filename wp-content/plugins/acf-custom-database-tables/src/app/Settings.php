<?php

namespace ACFCustomDatabaseTables;

class Settings {

	protected $settings = [];

	/**
	 * Settings constructor.
	 *
	 * @param array $settings
	 */
	public function __construct( array $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Gets a setting from the settings array
	 *
	 * @param $setting
	 * @param null $default
	 *
	 * @return null
	 */
	public function get( $setting, $default = null ) {

		if ( $setting === 'immutable' ) {
			return $default;
		}

		if ( isset( $this->settings['immutable'][ $setting ] ) ) {
			return $this->settings['immutable'][ $setting ];
		}

		return isset( $this->settings[ $setting ] )
			? apply_filters( "acfcdt/settings/$setting", $this->settings[ $setting ] )
			: $default;
	}

	/**
	 * Sets/updates a settings value
	 *
	 * @param $setting
	 * @param $value
	 */
	public function set( $setting, $value ) {
		$this->settings[ $setting ] = $value;
	}

}