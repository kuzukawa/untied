<?php

use ACFCustomDatabaseTables\Utils\Arr;
use ACFCustomDatabaseTables\Utils\View;

$data = isset( $data ) ? $data : new stdClass();
$options = isset( $data->options ) ? $data->options : [];
$action = isset( $data->action ) ? $data->action : '';
$nonce_field = isset( $data->nonce_field ) ? $data->nonce_field : '';
$next = isset( $data->next ) ? $data->next : '';
$manage_url = isset( $data->manage_url ) ? $data->manage_url : '';
?>
<form method="post" action="<?php echo esc_url( $action ) ?>">
	<?php echo $nonce_field ?>
	<input type="hidden" name="next" value="<?php echo esc_url( $next ) ?>">

	<p>Select the field groups you would like to rebuild the table map files for and click the <em>Rebuild Files</em>
		button. Only field groups with a custom table enabled and a defined table name are available. Field groups with
		both a table definition JSON file and a table in the database are checked by default.</p>

	<div class="acfcdt-field-groups">
		<table class="acfcdt-field-groups__table">
			<thead>
				<tr>
					<th>Field Group</th>
					<th>
						Database Table
						<?php View::render( 'tooltip', [
							'tip' => sprintf(
								'If a field group is included in the map and no table exists, the table needs to be created.<br><br>Either update the field group individually or run <a href="%s">Manage Tables</a></strong> after updating the map system.',
								esc_url( $manage_url ) ),
							'width' => '22em',
						] ); ?>
					</th>
					<th>
						Table JSON File
						<?php View::render( 'tooltip', [
							'tip' => 'The table JSON file contains the table schema for the field group and is generated on field group save',
							'width' => '18em',
						] ); ?>
					</th>
					<th class="acfcdt-field-groups__check-col">Update</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $options as $option ): ?>
					<tr>
						<td>
							<label for="<?php echo 'field-group-' . esc_attr( $option['key'] ) ?>">
								<?php echo esc_html( $option['title'] ) ?>
								<?php if ( ! $option['is_active'] ): View::render( 'tooltip', [
									'tip' => 'Field group is inactive',
									'class' => 'acfcdt-text-gray',
									'dashicon' => 'hidden',
									'width' => '12em',
								] ); endif; ?>
							</label>
						</td>
						<td>
							<?php if ( $option['table_name'] ):
								echo esc_html( $option['table_name'] );
							endif; ?>
							<?php if ( $option['table_exists'] ): View::render( 'tooltip', [
								'tip' => 'Table is in the database',
								'class' => 'acfcdt-text-green',
								'dashicon' => 'yes',
								'width' => '12em',
							] ); endif; ?>
						</td>
						<td>
							<?php echo Arr::get( $option, 'table_json_file', '' ); ?>
							<?php if ( $option['table_json_path'] ): View::render( 'tooltip', [
								'tip' => '<strong>Full path:</strong> <br>' . $option['table_json_path'],
								'dashicon' => 'media-code',
								'width' => '22em',
							] ); endif; ?>
						</td>
						<td class="acfcdt-field-groups__check-col">
							<input type="checkbox" <?php checked( $option['checked'] ) ?>
								   id="<?php echo 'field-group-' . esc_attr( $option['key'] ) ?>"
								   aria-label="<?php echo esc_attr( $option['title'] ) ?>"
								   name="field_group_keys[]"
								   value="<?php echo esc_attr( $option['key'] ) ?>">
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<p class="acfcdt-submit-btns">
		<input class="button button-primary" type="submit" value="Rebuild Files">
	</p>
</form>