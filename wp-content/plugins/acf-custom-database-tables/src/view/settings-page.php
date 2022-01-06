<?php
$data = isset( $data ) ? $data : new stdClass();
$section_links = isset( $data->section_links ) ? $data->section_links : [];
$current_section = isset( $data->current_section ) ? $data->current_section : '';
$section_content = isset( $data->section_content ) ? $data->section_content : '';
?>
<div class="wrap acf-settings-wrap" id="acf-custom-database-tables">

    <h1>Custom Database Tables</h1>

	<?php settings_errors() ?>

    <h2 class="nav-tab-wrapper">
		<?php foreach ( $section_links as $section ): ?>
            <a href="<?php echo $section['href'] ?>"
               title="<?php echo $section['title'] ?>"
               class="nav-tab <?php echo ( $section['name'] === $current_section ) ? 'nav-tab-active' : '' ?>">
				<?php echo $section['text'] ?>
            </a>
		<?php endforeach; ?>
    </h2>

    <div>
		<?php echo $section_content ?>
    </div>

</div>