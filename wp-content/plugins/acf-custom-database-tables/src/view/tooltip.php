<?php
$data = isset( $data ) ? $data : new stdClass();
$class = isset( $data->class ) ? $data->class : '';
$tip = isset( $data->tip ) ? $data->tip : '';
$dashicon = isset( $data->dashicon ) ? $data->dashicon : 'editor-help';
$width = isset( $data->width ) ? $data->width : '18em';
$allowed_html = isset( $data->allowed_html ) ? $data->allowed_html : [
	'a' => [
		'href' => true,
		'target' => true,
		'title' => true,
	],
	'br' => true,
	'strong' => true,
];
?>
<span class="acfcdt-tooltip dashicons dashicons-<?php echo esc_attr( $dashicon ) ?> <?php echo esc_attr( $class ) ?>">
	<span class="acfcdt-tooltip__tip"
	      style="min-width:<?php echo esc_attr( $width ) ?>"><?php echo wp_kses( $tip, $allowed_html ) ?></span>
</span>