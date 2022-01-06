<?php

use ACFCustomDatabaseTables\AdminPost\CancelRebuildMapSystemAdminPost;
use ACFCustomDatabaseTables\Ajax\RebuildMapSystemStatusAjax;
use ACFCustomDatabaseTables\Facade\App;
use ACFCustomDatabaseTables\Utils\View;

$data = isset( $data ) ? $data : new stdClass();
$percentage = isset( $data->percentage ) ? $data->percentage : 0;
$info = isset( $data->info ) ? $data->info : 'Checking status…';
$logs = isset( $data->logs ) ? $data->logs : [];

/** @var RebuildMapSystemStatusAjax $ajax */
$ajax = App::make( RebuildMapSystemStatusAjax::class );

/** @var CancelRebuildMapSystemAdminPost $cancel */
$cancel = App::make( CancelRebuildMapSystemAdminPost::class );

View::render( 'components/progress', [
	'progress' => $percentage,
	'info' => $info,
	'ajax_status_url' => $ajax->url(),
	'enable_logs' => true,
	'logs' => $logs,
	'nonce' => [
		'name' => $ajax->nonce()->name(),
		'value' => $ajax->nonce()->create(),
	],
] );

if ( $percentage < 100 ):
	?>
	<p class="acfcdt-migrate-cancel">
		<a href="<?php echo esc_url( $cancel->url() ) ?>" class="button">Cancel Operation</a>
	</p>
<?php
endif;