<?php
$data = isset( $data ) ? $data : new stdClass();
$action = isset( $data->action ) ? $data->action : '';
$button_url = isset( $data->button_url ) ? $data->button_url : '';
$fields = isset( $data->fields ) ? $data->fields : [];
?>
<p>Custom database table definition JSON file was <?php echo $action ?>:</p>
<?php if ( $fields ): ?>
    <table class="acfcdt-notice-table">
		<?php foreach ( $fields as $field ): ?>
            <tr>
                <th><?php echo $field['title'] ?></th>
                <td><?php echo $field['content'] ?></td>
            </tr>
		<?php endforeach; ?>
    </table>
<?php endif; ?>
<p>
    To apply these updates to your database, you need to run the update process on the
    <em>Manage Tables</em> admin screen.
</p>
<p>
    <a class="button button-primary" href="<?php echo $button_url ?>">Manage Database Tables</a>
</p>