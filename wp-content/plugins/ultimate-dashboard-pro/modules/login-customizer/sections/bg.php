<?php
/**
 * Background section of Login Customizer.
 *
 * @var $wp_customize This variable is brought from login-customizer.php file.
 *
 * @package Ultimate_Dashboard_PRO
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

use Udb\Udb_Customize_Control;
use Udb\Udb_Customize_Image_Control;
use Udb\Udb_Customize_Color_Control;
use Udb\Udb_Customize_Color_Picker_Control;
use Udb\Udb_Customize_Toggle_Switch_Control;

$wp_customize->add_setting(
	'udb_login[bg_color]',
	array(
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'default'           => '#f1f1f1',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'sanitize_hex_color',
	)
);

$wp_customize->add_control(
	new Udb_Customize_Color_Control(
		$wp_customize,
		'udb_login[bg_color]',
		array(
			'label'    => __( 'Background Color', 'ultimatedashboard' ),
			'section'  => 'udb_login_customizer_bg_section',
			'settings' => 'udb_login[bg_color]',
		)
	)
);

$content_helper = new \UdbPro\Helpers\Content_Helper();

$wp_customize->add_setting(
	'udb_login[bg_image]',
	array(
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'default'           => '',
		'transport'         => 'postMessage',
		'sanitize_callback' => array( $content_helper, 'sanitize_image' ),
	)
);

$wp_customize->add_control(
	new Udb_Customize_Image_Control(
		$wp_customize,
		'udb_login[bg_image]',
		array(
			'label'    => __( 'Upload Background', 'ultimatedashboard' ),
			'section'  => 'udb_login_customizer_bg_section',
			'settings' => 'udb_login[bg_image]',
		)
	)
);

$wp_customize->add_setting(
	'udb_login[bg_position]',
	array(
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'default'           => 'center center',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

$wp_customize->add_control(
	new Udb_Customize_Control(
		$wp_customize,
		'udb_login[bg_position]',
		array(
			'type'     => 'select',
			'section'  => 'udb_login_customizer_bg_section',
			'settings' => 'udb_login[bg_position]',
			'label'    => __( 'Background Position', 'ultimatedashboard' ),
			'choices'  => array(
				'left top'      => __( 'left top', 'ultimatedashboard' ),
				'left center'   => __( 'left center', 'ultimatedashboard' ),
				'left bottom'   => __( 'left bottom', 'ultimatedashboard' ),
				'center top'    => __( 'center top', 'ultimatedashboard' ),
				'center center' => __( 'center center', 'ultimatedashboard' ),
				'center bottom' => __( 'center bottom', 'ultimatedashboard' ),
				'right top'     => __( 'right top', 'ultimatedashboard' ),
				'right center'  => __( 'right center', 'ultimatedashboard' ),
				'right bottom'  => __( 'right bottom', 'ultimatedashboard' ),
			),
		)
	)
);

$wp_customize->add_setting(
	'udb_login[bg_size]',
	array(
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'default'           => 'cover',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

$wp_customize->add_control(
	new Udb_Customize_Control(
		$wp_customize,
		'udb_login[bg_size]',
		array(
			'type'     => 'select',
			'section'  => 'udb_login_customizer_bg_section',
			'settings' => 'udb_login[bg_size]',
			'label'    => __( 'Background Size', 'ultimatedashboard' ),
			'choices'  => array(
				'auto'    => __( 'auto', 'ultimatedashboard' ),
				'cover'   => __( 'cover', 'ultimatedashboard' ),
				'contain' => __( 'contain', 'ultimatedashboard' ),
			),
		)
	)
);

$wp_customize->add_setting(
	'udb_login[bg_repeat]',
	array(
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'default'           => 'no-repeat',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

$wp_customize->add_control(
	new Udb_Customize_Control(
		$wp_customize,
		'udb_login[bg_repeat]',
		array(
			'type'     => 'select',
			'section'  => 'udb_login_customizer_bg_section',
			'settings' => 'udb_login[bg_repeat]',
			'label'    => __( 'Background Repeat', 'ultimatedashboard' ),
			'choices'  => array(
				'no-repeat' => __( 'no-repeat', 'ultimatedashboard' ),
				'repeat'    => __( 'repeat', 'ultimatedashboard' ),
				'repeat-x'  => __( 'repeat-x', 'ultimatedashboard' ),
				'repeat-y'  => __( 'repeat-y', 'ultimatedashboard' ),
			),
		)
	)
);

if ( class_exists( 'Udb\Udb_Customize_Toggle_Switch_Control' ) ) {
	$wp_customize->add_setting(
		'udb_login[enable_bg_overlay_color]',
		array(
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'default'           => 0,
			'transport'         => 'postMessage',
			'sanitize_callback' => 'absint',
		)
	);

	$wp_customize->add_control(
		new Udb_Customize_Toggle_Switch_Control(
			$wp_customize,
			'udb_login[enable_bg_overlay_color]',
			array(
				'settings' => 'udb_login[enable_bg_overlay_color]',
				'section'  => 'udb_login_customizer_bg_section',
				'label'    => __( 'Enable background overlay', 'ultimatedashboard' ),
			)
		)
	);
}

if ( class_exists( 'Udb\Udb_Customize_Color_Picker_Control' ) ) {
	$setting_args = array(
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'transport'         => 'postMessage',
		// @todo Provide proper sanitize based on WPTT color alpha repo.
		'sanitize_callback' => 'sanitize_text_field', // Because sanitize_hex_color wouldn't work on rgba.
	);

	$opts = get_option( 'udb_login', array() );

	if ( isset( $opts['bg_overlay_color'] ) ) {
		$setting_args['default'] = $opts['bg_overlay_color'];
	}

	$wp_customize->add_setting(
		'udb_login[bg_overlay_color]',
		$setting_args
	);

	$wp_customize->add_control(
		new Udb_Customize_Color_Picker_Control(
			$wp_customize,
			'udb_login[bg_overlay_color]',
			array(
				'settings' => 'udb_login[bg_overlay_color]',
				'section'  => 'udb_login_customizer_bg_section',
				'label'    => __( 'Overlay Color', 'ultimatedashboard' ),
				'alpha'    => true,
			)
		)
	);
}
