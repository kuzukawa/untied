<?php

return [

	/**
	 * Scripts required for the plugin
	 */
	'scripts' => [

		'acfcdt-admin-global' => [
			'src' => 'js/acfcdt-admin-global.min.js',
			'src_debug' => 'js/acfcdt-admin-global.js',
			'deps' => [ 'jquery' ],
			'version' => false,
			'in_footer' => false,
		],

		'acfcdt-admin' => [
			'src' => 'js/acfcdt-admin.min.js',
			'src_debug' => 'js/acfcdt-admin.js',
			'deps' => [ 'jquery' ],
			'version' => false,
			'in_footer' => false,
		],

		'acfcdt-field-group' => [
			'src' => 'js/acfcdt-field-group.min.js',
			'src_debug' => 'js/acfcdt-field-group.js',
			'deps' => [ 'jquery' ],
			'version' => false,
			'in_footer' => false,
		],

	],

	/**
	 * Stylesheets required for the plugin
	 */
	'styles' => [

		'acfcdt-admin' => [
			'src' => 'css/acfcdt-admin.min.css',
			'src_debug' => 'css/acfcdt-admin.css',
			'deps' => [],
			'version' => false,
			'media' => 'all',
		]

	],

];