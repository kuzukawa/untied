<?php
$data = isset( $data ) ? $data : new stdClass();
$system_checks = isset( $data->checks ) ? $data->checks : [];
$failed_checks = isset( $data->failed_checks ) ? $data->failed_checks : [];
$help_section_url = isset( $data->help_section_url ) ? $data->help_section_url : '';
?>
<div class="acf-box">
    <div class="title">
        <h3>System Checks</h3>
    </div>
    <div class="inner">

		<?php if ( $failed_checks ): ?>
            <p>We appear to have found some issues during our system compatibility check that will need to be
                addressed before we can continue:</p>
		<?php else: ?>
            <p>Everything looks good. We haven't detected any immediate system issues preventing the use of the
                plugin.</p>
		<?php endif; ?>

        <div class="acfcdt-table-wrap">
            <table class="acfcdt-table">
                <tr>
                    <th>Requirement</th>
                    <th>Minimum</th>
                    <th>Current</th>
                    <th>Status</th>
                    <th class="-no-border"></th>
                </tr>
				<?php foreach ( $system_checks as $check ): ?>
                    <tr>
                        <th><?php echo $check['name'] ?></th>
                        <td><?php echo $check['minimum'] ?></td>
                        <td><?php echo $check['current'] ?></td>
                        <td><?php echo $check['test']
								? '<span style="color:#46b450">Pass</span>'
								: '<span style="color:#c00">Fail</span>' ?>
                        </td>
                        <td class="-no-border"><?php echo $check['test'] ? '' : "<em>{$check['notice']}</em>" ?></td>
                    </tr>
				<?php endforeach; ?>
            </table>
        </div>

		<?php if ( $failed_checks ): ?>
            <h3>Not sure what to do?</h3>

            <p>If you aren't sure what to do with this information, head on over to the
                <a href="<?php echo $help_section_url; ?>">help tab</a> and send us a support request.</p>
		<?php endif; ?>

    </div>
</div>