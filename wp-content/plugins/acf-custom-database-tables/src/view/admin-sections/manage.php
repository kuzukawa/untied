<?php
$data = isset( $data ) ? $data : new stdClass();
$help_section_url = isset( $data->help_section_url ) ? $data->help_section_url : '';
$system_problems_detected = isset( $data->system_problems_detected ) ? $data->system_problems_detected : false;
$json_definition_file_count = isset( $data->json_definition_file_count ) ? $data->json_definition_file_count : 0;
$system_section_url = isset( $data->system_section_url ) ? $data->system_section_url : '';
$json_definition_files = isset( $data->json_definition_files ) ? $data->json_definition_files : [];
?>
<div class="acf-box">
    <div class="title">
        <h3>Create/Update Tables</h3>
    </div>
    <div class="inner">

        <p>This is where you run the process that creates new custom database tables and modifies existing
            ones.</p>

        <p>The process reads your custom table definitions from JSON files and uses that information to
            create new custom tables, modify existing custom tables, and to store and retrieve data from those
            tables when using the <em>Advanced
                Custom Fields</em> system.</p>

        <p>If this is your first time using this system, refer to the <a
                    href="<?php echo $help_section_url ?>">help section</a> for an introduction,
            guides, and other resources.</p>

		<?php if ( $system_problems_detected ): ?>
            <h3>System problems detected</h3>
            <p>We've detected some issues with the current system that need to be addresses before we can start
                working with custom tables.</p>
            <a class="wp-core-ui button-primary" href="<?php echo $system_section_url ?>">View System Checks</a>
		<?php elseif ( $json_definition_file_count < 1 ): ?>
            <h3>No table definition files found.</h3>
            <p>Before you can modify your database, you need to create at least one table definition. Table
                definitions can be created using the <em>Database Table Definition</em> meta box on an <em>ACF
                    Field Group's</em> admin page.</p>
            <p>Alternatively, you can create definitions by manually
                creating JSON files. See the <a
                        href="<?php echo $help_section_url ?>">help section</a> for guides and
                examples on how to do this.</p>
		<?php else: ?>
            <h3><?php echo $json_definition_file_count ?> table definitions found</h3>
            <p>We've found the following <strong><?php echo $json_definition_file_count ?></strong> table
                definition JSON files. Each of these files will be processed by WordPress' built in database
                update mechanism and
                will result in new custom tables being created, or updates being applied to existing tables
                where the definition has changed.</p>
            <textarea class="acfcdt-file-list"
                      autocomplete="off"
                      readonly=""
                      wrap="off"><?php echo implode( PHP_EOL, $json_definition_files ) ?></textarea>

            <h3>Create/update tables</h3>
            <p>If you haven't already done so, we strongly suggest taking a backup of your database before
                continuing.</p>
			<?php do_action( 'acfcdt/hook/settings_page_content' ); ?>
		<?php endif; ?>

    </div>
</div>