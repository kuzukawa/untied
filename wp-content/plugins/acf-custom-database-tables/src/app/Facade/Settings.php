<?php

namespace ACFCustomDatabaseTables\Facade;

use ACFCustomDatabaseTables\Controller\SettingsPageController;

/**
 * @package ACFCustomDatabaseTables\Facade
 * @mixin \ACFCustomDatabaseTables\Settings
 *
 * @method static get( $setting, $default = null ) : mixed
 * @method static set( $setting, $value )
 */
class Settings extends FacadeBase {

	protected static function get_facade_accessor() {
		return \ACFCustomDatabaseTables\Settings::class;
	}

	public static function tools_tab_url() {
		return self::$app->make( SettingsPageController::class )->section_url( 'tools' );
	}

	public static function json_dir() {
		return self::get( 'json_dir' );
	}

}