<?php 
if (!defined('ABSPATH')) {
    exit;
}
global $wpdb;

$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'default';
$fm_show_default = isset($_GET['action']) ? 0 : 1;
$tbl = isset($_GET['tbl']) ? sanitize_text_field($_GET['tbl']) : '';

/* Add Record */
if (isset($_POST['save_tbl_data']) && wp_verify_nonce( $_POST['insert_db_tbl'], 'mk_insert_db_tbl' )) {
    unset($_POST['save_tbl_data']);
    unset($_POST['reset']);
    unset($_POST['insert_db_tbl']);
    unset($_POST['_wp_http_referer']);
    $data_result = $this->CreateRecord(sanitize_text_field($_GET['tbl']), $_POST);
} else if(isset($_POST['CreateTbl']) && wp_verify_nonce( $_POST['create_db_tbl'], 'mk_create_db_tbl' )) { 
    unset($_POST['CreateTbl']);
    unset($_POST['create_db_tbl']);
    unset($_POST['_wp_http_referer']);
    $data_result = $this->createTable($_POST);
}

if ($action == 'delete' && isset($_REQUEST["key"]) && wp_verify_nonce($_REQUEST["key"], 'fm_del_db_nonce')) {
    $data_result = $this->deleteRecord(sanitize_text_field($_GET['tbl']), sanitize_text_field($_GET['pf']), intval($_GET['rid']));
} elseif ($action == 'drop' && isset($_REQUEST["key"]) && wp_verify_nonce($_REQUEST["key"], 'fm_drop_db_nonce')) {
    $data_result = $this->drop_table(sanitize_text_field($_GET['tbl']));
} elseif ($action == 'truncate' && isset($_REQUEST["key"]) && wp_verify_nonce($_REQUEST["key"], 'fm_trunc_db_nonce')) {
    $data_result = $this->truncate_tbl(sanitize_text_field($_GET['tbl']));
}

$dbtables = $wpdb->get_col('show tables');
?>
<div class="wrap dbmanager">
<div class="mk_export_db">
<form action="admin-post.php?action=mk_db_manager_export_db" method="post">
<?php wp_nonce_field('mk_export_db_field', 'export_db_field'); ?>
<input type="hidden" name="mk_export_db_action" value="export_fm_db"/>
<input type="submit" name="mk_export_db" value="<?php _e('Export Database', 'wp-file-manager-pro'); ?>" class="button button-primary" />
</form>
</div>
<div class="clear"></div>
<div class="db_top">
  <span class="dbDetail dbDetailHost">
  <span class="icon"><img src="<?php echo plugins_url('images/host.png', dirname(__FILE__)); ?>" width="16" /></span> <strong> <?php _e('Host:', 'wp-file-manager-pro'); ?> </strong> <?php echo $wpdb->dbhost; ?> &raquo; 
  </span>

  <span class="dbDetail dbDetailDatabase">
  <span class="icon"><img src="<?php echo plugins_url('images/database.png', dirname(__FILE__)); ?>" width="16"/></span> <strong><?php _e('Database:', 'wp-file-manager-pro'); ?> </strong><a href="?page=wp_file_manager_db_manager&action=default&key=<?php echo wp_create_nonce( 'fm_default_db_nonce' );?>"><?php echo $wpdb->dbname; ?></a><?php if (!empty($tbl)) {
    ?> &raquo;  </span>
      <span class="dbDetail dbDetailTable">
      <span class="icon"> <img src="<?php echo plugins_url('images/table.png', dirname(__FILE__)); ?>" width="16"/></span> <strong><?php _e('Table:', 'wp-file-manager-pro'); ?> </strong> <?php echo htmlspecialchars(htmlentities($tbl)); ?> <?php
} ?> 
  </span>
  
  <a href="?page=wp_file_manager_db_manager&amp;action=create_tbl&amp;key=<?php echo wp_create_nonce( 'fm_crte_db_nonce' );?>" class="button button-primary"><?php _e('Create New Table', 'wp-file-manager-pro'); ?></a>
</div>
<div class="db_main" id="db_main">
<div class="db_main_left">
<div id="db_left_resize">
<ul>
<li><span class="db_name"><a href="?page=wp_file_manager_db_manager&action=default&key=<?php echo wp_create_nonce( 'fm_default_db_nonce' );?>"><?php echo $wpdb->dbname; ?> (<?php echo count($dbtables); ?>) </a></span>
<ul>
<?php 
$dropTableName = isset($_GET['action']) && trim($_GET['action']) == 'drop' ? sanitize_text_field($_GET['tbl']) : '';
?>
<?php if (!empty($dbtables) && is_array($dbtables)):
foreach ($dbtables as $dbtable): ?>
<?php if($dropTableName != $dbtable): ?>
<li <?php echo($dbtable == $tbl) ? 'class="active"' : ''; ?>><a href="?page=wp_file_manager_db_manager&action=browse&tbl=<?php echo $dbtable; ?>&key=<?php echo wp_create_nonce( 'fm_browse_db_nonce' );?>"><?php echo $dbtable; ?></a></li>
<?php endif; endforeach; endif; ?>
</ul>
</li>
</ul>
</div> <!--db_left_resize-->
</div>

<div class="db_main_right">
<div id="db_main_right_content">
<div id="msg_bar"></div>
<?php 
if(isset($data_result)){
    echo $data_result;
}

if (($fm_show_default == 1 && $action == 'default') || ($action == 'default' && isset($_REQUEST["key"]) && wp_verify_nonce($_REQUEST["key"], 'fm_default_db_nonce'))) {
    $this->show_tbls($dbtables);
} elseif ($action == 'create_tbl' && isset($_REQUEST["key"]) && wp_verify_nonce($_REQUEST["key"], 'fm_crte_db_nonce')) {
    $this->create_tbl();
} elseif ($action == 'browse' && isset($_REQUEST["key"]) && wp_verify_nonce($_REQUEST["key"], 'fm_browse_db_nonce')) {
    $offSet = 1;
    if (isset($_GET['pageno'])) {
        $offSet = sanitize_text_field($_GET['pageno']);
    } else {
        $offSet = 1;
    }
    $this->BrowseRecords(sanitize_text_field($_GET['tbl']), $keys = '', $values = '', $offSet , $eat_cols = 200, $fuzzy = '');
} elseif ($action == 'insert' && isset($_REQUEST["key"]) && wp_verify_nonce($_REQUEST["key"], 'fm_ins_db_nonce')) {
    $this->TableDetails(sanitize_text_field($_GET['tbl']));
} elseif ($action == 'update' && isset($_REQUEST["key"]) && wp_verify_nonce($_REQUEST["key"], 'fm_edit_db_nonce')) {
    $this->UpdateTableDetails(sanitize_text_field($_GET['tbl']), sanitize_text_field($_GET['pf']), intval($_GET['rid'])); ?>
<script>
 jQuery(document).ready( function() {
   jQuery(".update_tbl_data").click( function(e) {
	   e.preventDefault();
	   var update_tbl_form = jQuery('#update_tbl_form').serialize();
      jQuery.ajax({
         type : "post",
         url : "<?php echo admin_url('admin-ajax.php'); ?>",
         data : {
			      action: "Update_Tbl_Record",
				  table2Edit : "<?php echo sanitize_text_field($_GET['tbl']); ?>",
				  fieldname:  "<?php echo sanitize_text_field($_GET['pf']); ?>",
				  rid:  "<?php echo sanitize_text_field($_GET['rid']); ?>",
				  values: update_tbl_form
				},
         success: function(response) {
			 var data = jQuery.parseJSON(response);
            if(data.type == "success") {
               jQuery("#msg_bar").html('<span class="success"><?php _e('Success', 'wp-file-manager-pro'); ?>:: '+data.msg+'</span>')
            }
            else {
               jQuery("#msg_bar").html('<span class="error"><?php _e('Error', 'wp-file-manager-pro'); ?>:: '+data.msg+'</span>')
            }
         }
      })   

   })

})
</script> 
<?php
}
else{
    if(!isset($data_result)){
        echo __("You don't have permission to access this area.",'wp-file-manager-pro');
    }
}

?>
</div>  <!--db_main_right_content-->
</div>
</div>
</div>