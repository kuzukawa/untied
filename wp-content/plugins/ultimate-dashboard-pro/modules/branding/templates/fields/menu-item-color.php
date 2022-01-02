<?php
/**
 * Menu item color field.
 *
 * @package Ultimate_Dashboard_PRO
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

return function () {

	$branding = get_option( 'udb_branding' );
	$default  = '#ffffff';
	$color    = isset( $branding['menu_item_color'] ) ? $branding['menu_item_color'] : $default;
	?>

	<input type="text" name="udb_branding[menu_item_color]" value="<?php echo esc_attr( $color ); ?>" class="udb-color-field udb-branding-color-field udb-instant-preview-trigger" data-default="<?php echo esc_attr( $default ); ?>" data-udb-trigger-name="menu-item-color" />

	<?php

};
