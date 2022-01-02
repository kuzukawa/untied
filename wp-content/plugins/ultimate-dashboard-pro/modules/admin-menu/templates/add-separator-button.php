<?php
/**
 * Add separator button template.
 *
 * @package Ultimate_Dashboard_Pro
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

return function () {
	?>

	<button type="button" class="udb-admin-menu--add-item udb-admin-menu--add-new-separator">
		<i class="dashicons dashicons-plus"></i>
		<?php _e( 'Add Menu Separator', 'ultimatedashboard' ); ?>
	</button>

	<?php
};
