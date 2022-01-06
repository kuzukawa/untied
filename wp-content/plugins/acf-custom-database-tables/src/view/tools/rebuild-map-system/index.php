<?php
$data = isset( $data ) ? $data : new stdClass();
$back_link = isset( $data->back_link ) ? $data->back_link : '';
$inner = isset( $data->inner ) ? $data->inner : 'Error: No inner view loaded';
?>
<div class="acf-box">
	<div class="title">
		<h3><a href="<?php echo esc_url( $back_link ) ?>">Tools</a> &rsaquo; Rebuild Table Map System</h3>
	</div>
	<div class="inner">
		<?php
		// This is deliberately left raw as the inner view needs
		// to handle escaping specific to its own context.
		echo $inner
		?>
	</div>
</div>