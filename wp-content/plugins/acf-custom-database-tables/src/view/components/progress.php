<?php
$data = isset( $data ) ? $data : new stdClass();
$progress = isset( $data->progress ) ? $data->progress : 0;
$info = isset( $data->info ) ? $data->info : 'Checking status…';
$ajax_status_url = isset( $data->ajax_status_url ) ? $data->ajax_status_url : '';
$enable_logs = isset( $data->enable_logs ) ? $data->enable_logs : true;
$logs = isset( $data->logs ) ? $data->logs : [];
$nonce = isset( $data->nonce ) ? $data->nonce : [ 'name' => '_wpnonce', 'value' => wp_create_nonce() ];

$classes = [];
if ( $enable_logs ) {
	$classes[] = 'acfcdt-progress--enable-logs';
}
if ( $progress == 100 ) {
	$classes[] = 'acfcdt-progress--done';
}

?>
<div class="acfcdt-progress <?php echo implode( ' ', $classes ) ?>"
     data-done-class="acfcdt-progress--done"
     data-nonce="<?php echo esc_attr( wp_json_encode( $nonce ) ) ?>"
     data-progress="<?php echo esc_attr( $progress ) ?>"
     data-ajax-status-url="<?php echo esc_attr( $ajax_status_url ) ?>">
	<div class="acfcdt-progress__error error inline">
		<p>
			<strong>There was an error retrieving progress data:</strong> <span class="acfcdt-progress__error-message">Error unknown</span>
		</p>
	</div>
	<div class="acfcdt-progress__bar">
		<div class="acfcdt-progress__background"></div>
		<div class="acfcdt-progress__progress" style="width:<?php echo esc_attr( $progress ) ?>%;"></div>
		<div class="acfcdt-progress__stripes"></div>
		<div class="acfcdt-progress__info"><?php echo esc_html( $info ) ?></div>
	</div>
	<div class="acfcdt-progress__logs">
		<textarea class="acfcdt-progress__log-output"
		          aria-label="Log Output"
		          rows="6"
		          readonly><?php echo join( PHP_EOL, $logs ) ?></textarea>
	</div>
</div>