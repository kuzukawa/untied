<?php
/**
 * Accent color field.
 *
 * @package Ultimate_Dashboard_PRO
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

return function () {

	$branding     = get_option( 'udb_branding' );
	$default      = '#0073AA';
	$accent_color = isset( $branding['accent_color'] ) ? $branding['accent_color'] : $default;
	?>

	<input type="text" name="udb_branding[accent_color]" value="<?php echo esc_attr( $accent_color ); ?>" class="udb-color-field udb-branding-color-field udb-instant-preview-trigger" data-default="<?php echo esc_attr( $default ); ?>" data-udb-trigger-name="accent-color" />

	<?php

};
