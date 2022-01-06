<?php
$data = isset( $data ) ? $data : new stdClass();
$system_checks_page_url = isset( $data->system_checks_page_url ) ? $data->system_checks_page_url : '';
?>
<div class="inner" style="padding: 15px 12px;">
    <p>There appears to be some compatibility issues with your system preventing the use of the
        <em>ACF Custom Database Tables</em> system.</p>
    <p>Head over to the <a href="<?php echo $system_checks_page_url ?>"> system check tab</a> on our support page for more
        information.</p>
</div>