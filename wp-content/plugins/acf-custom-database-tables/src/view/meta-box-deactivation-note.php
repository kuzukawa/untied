<?php
$data = isset( $data ) ? $data : new stdClass();
$help_section_url = isset( $data->help_section_url ) ? $data->help_section_url : '';
?>
<div class="acfcdt-meta-box-deactivation-note">
    <p>
        This meta box has been deactivated as database table support is not currently available for the location
        rules on this field group. <br>To use this meta box, adjust the location rules so that only one of the following
        exists: <strong>Post Type</strong> or <strong>User Form</strong>. <br>
        <small style="color:#767983;">(Support for mapping multiple objects to a single table is coming soon.)</small>
    </p>
</div>