<?php
/**
 * Admin menu's form footer template.
 *
 * @package Ultimate_Dashboard_Pro
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

return function () {
	?>

	<div class="heatbox-left-footer">
		<button class="button button-large button-primary udb-admin-bar--button udb-admin-bar--submit-button">
			<i class="dashicons dashicons-yes"></i>
			<?php _e( 'Save Changes', 'ultimate-dashboard' ); ?>
		</button>
	</div>
	<div class="heatbox-right-footer">
		<button type="button" class="button button-large button-danger udb-admin-bar--button udb-admin-bar--reset-button udb-admin-bar--reset-all">
			<?php _e( 'Reset Admin Bar Editor', 'ultimate-dashboard' ); ?>
		</button>
	</div>

	<?php
};
