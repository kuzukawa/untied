<?php
$data = isset( $data ) ? $data : new stdClass();
$license = isset( $data->license ) ? $data->license : '';
$license_input_name = isset( $data->license_input_name ) ? $data->license_input_name : '';
$option_group = isset( $data->option_group ) ? $data->option_group : '';
$license_is_valid = isset( $data->license_is_valid ) ? $data->license_is_valid : false;
$deactivate_input_name = isset( $data->deactivate_input_name ) ? $data->deactivate_input_name : '';
$activate_input_name = isset( $data->activate_input_name ) ? $data->activate_input_name : '';
$nonce = isset( $data->nonce ) ? $data->nonce : '';
?>
<form method="post" action="<?php echo admin_url( 'options.php' ) ?>">
	<?php settings_fields( $option_group ); ?>
    <table class="form-table">
        <tbody>

        <tr valign="top">
            <th scope="row" valign="top">
				<?php _e( 'License Key' ); ?>
            </th>
            <td>
                <input name="<?php echo $license_input_name ?>"
                       type="text"
                       class="acfcdt-license-key"
                       value="<?php esc_attr_e( $license ); ?>"/>
                <small>Enter your license key, click the <em>Save License Key</em> button, then activate your
                    license.
                </small>

				<?php submit_button( 'Save License Key' ); ?>
            </td>
        </tr>

		<?php if ( $license ) : ?>
            <tr valign="top">
                <th scope="row" valign="top">
					<?php _e( 'License Status &amp; Activation' ); ?>
                </th>
                <td valign="middle">
					<?php if ( $license_is_valid ) : ?>

                        <span class="acfcdt-license-status acfcdt-license-status--active ">
                                    <?php _e( 'active' ); ?>
                                </span>
						<?php echo $nonce ?>
                        <input type="submit"
                               class="button-secondary"
                               name="<?php echo $deactivate_input_name ?>"
                               value="<?php _e( 'Deactivate License' ); ?>"/>

					<?php else : ?>

                        <span class="acfcdt-license-status acfcdt-license-status--inactive ">
                            <?php _e( 'inactive' ); ?>
                        </span>
						<?php echo $nonce ?>
                        <input type="submit"
                               class="button-secondary"
                               name="<?php echo $activate_input_name ?>"
                               value="<?php _e( 'Activate License' ); ?>"/>

					<?php endif; ?>
                </td>
            </tr>
		<?php endif; ?>

        </tbody>
    </table>
</form>