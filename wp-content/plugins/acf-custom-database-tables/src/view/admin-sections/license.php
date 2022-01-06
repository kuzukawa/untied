<?php
$data = isset( $data ) ? $data : new stdClass();
$license_form = isset( $data->license_form ) ? $data->license_form : '';
?>
<div class="acf-box">
    <div class="title">
        <h3>License</h3>
    </div>
    <div class="inner">
		<?php echo $license_form ?>
    </div>
</div>