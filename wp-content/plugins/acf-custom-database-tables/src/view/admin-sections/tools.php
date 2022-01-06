<?php

use ACFCustomDatabaseTables\Tools\ToolBase;

$data = isset( $data ) ? $data : new stdClass();

/** @var ToolBase[] $tools */
$tools = isset( $data->tools ) ? $data->tools : [];
?>
<div class="acfcdt-admin-wrap">
	<div class="acfcdt-tools">

		<?php foreach ( $tools as $tool ): ?>
			<div class="acfcdt-tools__card">
				<h3 class="acfcdt-tools__card-title"><?php echo esc_html( $tool->name() ) ?></h3>
				<div class="acfcdt-tools__card-descr">
					<?php echo wp_kses( $tool->description(), 'post' ) ?>
				</div>
			</div>
		<?php endforeach; ?>

	</div>
</div>