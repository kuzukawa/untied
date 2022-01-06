<?php
$data = isset( $data ) ? $data : new stdClass();
$enabled = isset( $data->enabled ) ? $data->enabled : false;
$readonly = isset( $data->readonly ) ? $data->readonly : false;
$id = isset( $data->id ) ? $data->id : uniqid( 'acfcdt_' );
$name = isset( $data->name ) ? $data->name : "";
$title = isset( $data->title ) ? $data->title : "";
$default = isset( $data->default ) ? $data->default : false;
$description = isset( $data->description ) ? $data->description : "";
?>
<div class="acfcdt-check-panel">

    <input <?php checked( $enabled ) ?> <?php readonly( $readonly ) ?>
            class="acfcdt-check-panel__check"
            type="checkbox"
            data-default="<?php echo $default ? 'checked' : 'unchecked' ?>"
            id="<?php echo esc_attr( $id ) ?>"
            name="<?php echo $name ?>">

    <div class="acfcdt-check-panel__bg"></div>

    <label class="acfcdt-check-panel__label" for="<?php echo esc_attr( $id ) ?>">

        <strong><?php echo $title ?></strong>
	    <?php if ( $readonly ): ?>
            <span class="acfcdt-check-panel__readonly">Read-only</span>
	    <?php endif; ?>

		<?php if ( $description ): ?>
            <p class="acfcdt-check-panel__descr"><?php echo $description ?></p>
		<?php endif; ?>

    </label>

    <span class="acfcdt-check-panel__right">
		<code class="acfcdt-check-panel__default">Default: <?php echo $default ? 'Enabled' : 'Disabled' ?></code>
	</span>

	<?php if ( $readonly ): ?>
        <div class="acfcdt-check-panel__overlay">
            <span>This has been configured via PHP and cannot be modified here.</span>
        </div>
	<?php endif; ?>

</div>