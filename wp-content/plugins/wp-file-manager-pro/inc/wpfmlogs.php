<?php if ( ! defined( 'ABSPATH' ) ) exit;
$this->fm_custom_assets();
global $wpdb; 
$tbl = $wpdb->prefix.'fm_file_action_log';
$action = (isset($_GET['action']) && !empty($_GET['action'])) ? sanitize_text_field($_GET['action']) : '';
$tab_type = isset($_GET['tab_type']) && !empty($_GET['tab_type']) ? sanitize_text_field($_GET['tab_type']) : 'edit_file';
if(!empty($action) && $action == 'delete' && wp_verify_nonce( $_GET['key'], 'log_del_nonce') && current_user_can('administrator')) {
  $id = intval($_GET['id']);
  $removeLog = $wpdb->delete($tbl, array('id' => $id));
  if($removeLog) {
    $this->redirect('?page=wpfm-logs&tab_type='.$tab_type.'&msg=1');
  }
}
if (isset($_GET['pageno'])) {
  $pageno = $_GET['pageno'];
} else {
  $pageno = 1;
}


/*******Pagination************/

//case common
$no_of_records_per_page = 10;
$offset = ($pageno-1) * $no_of_records_per_page;
$showing_page = ($pageno * $no_of_records_per_page)-($no_of_records_per_page-1);
$sno = $showing_page-1;

//case 1
$e_total_editFiles = $wpdb->get_results("select * from ".$tbl." where action='edit' order by id DESC");
$e_total_pages = ceil(count($e_total_editFiles) / $no_of_records_per_page);
$e_editedFiles = $wpdb->get_results("select * from ".$tbl." where action='edit' order by id DESC LIMIT ".$offset.", ".$no_of_records_per_page."");

//case 2
$d_total_downloadedFiles = $wpdb->get_results("select * from ".$tbl." where action='download' order by id DESC");
$d_total_pages = ceil(count($d_total_downloadedFiles) / $no_of_records_per_page);
$d_downloadFiles = $wpdb->get_results("select * from ".$tbl." where action='download' order by id DESC LIMIT ".$offset.", ".$no_of_records_per_page."");

//case 3
$u_total_uploadedFiles = $wpdb->get_results("select * from ".$tbl." where action='upload' order by id DESC");
$u_total_pages = ceil(count($u_total_uploadedFiles) / $no_of_records_per_page);
$u_uploadedFiles = $wpdb->get_results("select * from ".$tbl." where action='upload' order by id DESC LIMIT ".$offset.", ".$no_of_records_per_page."");
$confirmBox = __('Are you sure want to delete?','wp-file-manager-pro');
?>
<div class="wrap rootPageWrap">
  <div class="fmInnerWrap logs_page_wrap">
    <h3 class="mainHeading">
    <span class="headingIcon"><img src="<?php echo plugins_url('images/root-directory-icon.png', __FILE__); ?>"></span>
    <span class="headingText"><?php _e('Logs', 'wp-file-manager-pro'); ?></span>
    </h3>
   <p class="description"><?php _e('To enable logs, go to <a href="admin.php?page=wp_file_manager_settings">General Settings Tab</a> -> Check "Send Notifications to admin on file upload " , "Send Notifications to admin on file download" and "Send Notifications to admin on file edit" options.', 'wp-file-manager-pro'); ?></p>
    <ul class="logs_tabs">
      <li class="logs_tab <?php if($tab_type  == 'edit_file'){ echo 'log_active';}?>" data-attr="edit_file"><a><span><img src="<?php echo plugins_url('images/logs_edit.png', __FILE__); ?>"></span><?php _e('Edited Files','wp-file-manager-pro');?></a></li>
      <li class="logs_tab <?php if($tab_type  == 'download_file'){ echo 'log_active';}?>" data-attr="download_file"><a><span><img src="<?php echo plugins_url('images/logs_download.png', __FILE__); ?>"></span><?php _e('Downloaded Files','wp-file-manager-pro');?></a></li>
      <li class="logs_tab <?php if($tab_type  == 'upload_file'){ echo 'log_active';}?>" data-attr="upload_file"><a><span><img src="<?php echo plugins_url('images/logs_upload.png', __FILE__); ?>"></span><?php _e('Uploaded Files','wp-file-manager-pro');?></a></li>
    </ul>

    <div class="logs_tab_container">
      <!--case 1-->
      <div class="edit_file logs_tab_block <?php if($tab_type  == 'edit_file'){ echo 'log_active'; }?>">
        <h3><?php _e('Edited Files','wp-file-manager-pro');?></h3>        
        <?php if(!empty($e_editedFiles) && $tab_type  == 'edit_file') { ?>
          <select class="bulk_selection_1">
          <option value=""><?php _e('Bulk Actions','wp-file-manager-pro');?></option>
          <option value="delete"><?php _e('Delete','wp-file-manager-pro');?></option>
        </select>
        <button class="bulkApply bulk_delete_1 button"><?php _e('Apply','wp-file-manager-pro');?></button>

          <table class="form-table">
                <tr>
                  <th><span class="styledCheckbox">
                      <input type="checkbox" id="checkedAll" class="select_all_upload"> <span class="fm_checkmark"></span>
                    </span>
                  </th>
                  <th><?php _e('Sr No.','wp-file-manager-pro');?></th>
                  <th><?php _e('User ID','wp-file-manager-pro');?></th>
                  <th><?php _e('User Name','wp-file-manager-pro');?></th>
                  <th><?php _e('Files','wp-file-manager-pro');?></th>
                  <th><?php _e('Date','wp-file-manager-pro');?></th>
                  <th><?php _e('Action','wp-file-manager-pro');?></th>
                </tr>
            <?php foreach($e_editedFiles as $editedFile) {
                $sno++;?>
                <tr>
                <td><span class="styledCheckbox">
                      <input type="checkbox" class="single_check log_ids_1" name="log_ids_1[]" value=<?php echo $editedFile->id;?>> <span class="fm_checkmark"></span>
                    </span></td>
                  <td><?php echo $sno; ?></td>                  
                  <td><a href="user-edit.php?user_id=<?php echo $editedFile->uid; ?>"><?php echo $editedFile->uid; ?></a></td>
                  <td><?php echo $editedFile->uname; ?></td>
                  <td><?php echo $editedFile->files; ?></td>
                  <td><?php echo date('j M, Y H:i A', strtotime($editedFile->log_date)); ?></td>
                  <td><a title="<?php _e('Delete', 'wp-file-manager-pro'); ?>" class="del_log" href="?page=wpfm-logs&tab_type=edit_file&action=delete&id=<?php echo $editedFile->id.'&key='.wp_create_nonce( 'log_del_nonce' );?>" onclick="return confirm ('<?php echo $confirmBox;?>')"><img src="<?php echo plugins_url('images/logs_trash.png', __FILE__); ?>"></a></td>
                </tr>
            <?php } ?>
          </table>
        <?php } else { ?>
          <div class="error"><p><?php _e('No log(s) found!','wp-file-manager-pro');?></p></div>
        <?php } ?>
        <div class="pagination_container">
          <?php
              $e_lmid_size = 5;
              $e_rmid_size = 4;
              $e_current = $pageno;
              $e_content_tab = "&tab_type=".$tab_type;
              if($e_total_pages>1){
            ?>
            <div class="logs_total_showing"><?php echo __("Showing ", 'wp-file-manager-pro').$showing_page." ".__("to", 'wp-file-manager-pro')." ".($sno)." ".__("of", 'wp-file-manager-pro')." ".count($e_total_editFiles)." ".__("entries", 'wp-file-manager-pro');?></div>
            <ul class="pagination">
              <!--<li><a href="?page=wpfm-logs&pageno=1<?php //echo $e_content_tab?>">First</a></li>-->
              <li class="<?php if($pageno <= 1){ echo 'disabled'; } ?>">
                  <a href="<?php if($pageno <= 1){ echo '#'; } else { echo "?page=wpfm-logs&pageno=".($pageno - 1).$e_content_tab; } ?>">Prev</a>
              </li>
            <?php
            for ( $n = 1; $n <= $e_total_pages; $n++ )
            {
              if ( $n == $e_current ) 
              {
                ?>
                <li><a class='page-numbers current' href='?page=wpfm-logs&pageno=<?php echo $n.$e_content_tab;?>'> <?php echo $n;?></a></li>
                <?php
              }
              else 
              {
                if ( ($e_current && $n >= $e_current - $e_lmid_size && $n <= $e_current + $e_rmid_size ) ):
                ?>
                  <li><a class='page-numbers' href='?page=wpfm-logs&pageno=<?php echo $n.$e_content_tab ;?>'> <?php echo $n;?></a></li>
                  <?php
                endif;
              }
            }
          ?>
              <li class="<?php if($pageno >= $e_total_pages){ echo 'disabled'; } ?>">
                  <a href="<?php if($pageno >= $e_total_pages){ echo '#'; } else { echo "?page=wpfm-logs&pageno=".($pageno + 1).$e_content_tab; } ?>"><?php _e('Next','wp-file-manager-pro');?></a>
              </li>
              <!--<li><a href="?page=wpfm-logs&pageno=<?php //echo $e_total_pages.$e_content_tab; ?>"><?php //_e('Last','wp-file-manager-pro');?></a></li>-->
            </ul>
            <?php }?>
        </div>
      </div>
      <!--edit_file-->

      <!--case 2-->
      <div class="download_file logs_tab_block <?php if($tab_type  == 'download_file'){ echo 'log_active';}?>">
        <h3><?php _e('Downloaded Files','wp-file-manager-pro');?></h3>        
        <?php if(!empty($d_downloadFiles) && $tab_type  == 'download_file') { ?>
          <select class="bulk_selection_2">
          <option value=""><?php _e('Bulk Actions','wp-file-manager-pro');?></option>
          <option value="delete"><?php _e('Delete','wp-file-manager-pro');?></option>
        </select>
        <button class="bulkApply bulk_delete_2 button"><?php _e('Apply','wp-file-manager-pro');?></button>
          <table class="form-table">
                <tr>
                  <th><span class="styledCheckbox">
                      <input type="checkbox" id="checkedAll" class="select_all_upload"> <span class="fm_checkmark"></span>
                    </span></th>
                  <th><?php _e('Sr No.','wp-file-manager-pro');?></th>
                  <th><?php _e('User ID','wp-file-manager-pro');?></th>
                  <th><?php _e('User Name','wp-file-manager-pro');?></th>
                  <th><?php _e('Files','wp-file-manager-pro');?></th>
                  <th><?php _e('Date','wp-file-manager-pro');?></th>
                  <th><?php _e('Action','wp-file-manager-pro');?></th>
                </tr>
            <?php foreach($d_downloadFiles as $downloadedFile) {
              $sno++;?>
                <tr>
                <td><span class="styledCheckbox">
                      <input type="checkbox" class="single_check log_ids_2" name="log_ids_2[]" value=<?php echo $downloadedFile->id;?>> <span class="fm_checkmark"></span>
                    </span></td>
                  <td><?php echo $sno; ?></td>
                  <td><a href="user-edit.php?user_id=<?php echo $downloadedFile->uid; ?>"><?php echo $downloadedFile->uid; ?></a></td>
                  <td><?php echo $downloadedFile->uname; ?></td>
                  <td><?php echo $downloadedFile->files; ?></td>
                  <td><?php echo date('j M, Y H:i A', strtotime($downloadedFile->log_date)); ?></td>
                  <td><a title="<?php _e('Delete','wp-file-manager-pro');?>" class="del_log" href="?page=wpfm-logs&action=delete&tab_type=download_file&id=<?php echo $downloadedFile->id.'&key='.wp_create_nonce( 'log_del_nonce' );?>" onclick="return confirm ('<?php echo $confirmBox;?>')"><img src="<?php echo plugins_url('images/logs_trash.png', __FILE__); ?>"></a></td>
                </tr>
            <?php } ?>
          </table>
        <?php } else { ?>
          <div class="error"><p><?php _e('No log(s) found!','wp-file-manager-pro');?></p></div>
        <?php } ?>
        <div class="pagination_container">
            <?php
              $d_lmid_size = 5;
              $d_rmid_size = 4;
              $d_current = $pageno;
              $d_content_tab = "&tab_type=".$tab_type;
              if($d_total_pages>1){
            ?>
            <div class="logs_total_showing"><?php echo __("Showing ", 'wp-file-manager-pro').$showing_page." ".__("to", 'wp-file-manager-pro')." ".($sno)." ".__("of", 'wp-file-manager-pro')." ".count($d_total_downloadedFiles)." ".__("entries", 'wp-file-manager-pro');?></div>
            <ul class="pagination">
              <!--<li><a href="?page=wpfm-logs&pageno=1<?php //echo $d_content_tab?>">First</a></li>-->
              <li class="<?php if($pageno <= 1){ echo 'disabled'; } ?>">
                  <a href="<?php if($pageno <= 1){ echo '#'; } else { echo "?page=wpfm-logs&pageno=".($pageno - 1).$d_content_tab; } ?>">Prev</a>
              </li>
            <?php
            for ( $n = 1; $n <= $d_total_pages; $n++ )
            {
              if ( $n == $d_current ) 
              {
                ?>
                <li><a class='page-numbers current' href='?page=wpfm-logs&pageno=<?php echo $n.$d_content_tab;?>'> <?php echo $n;?></a></li>
                <?php
              }
                
              else 
              {
                if ( ($d_current && $n >= $d_current - $d_lmid_size && $n <= $d_current + $d_rmid_size ) ):
                ?>
                  <li><a class='page-numbers' href='?page=wpfm-logs&pageno=<?php echo $n.$d_content_tab ;?>'> <?php echo $n;?></a></li>
                  <?php
                endif;
              }
            }
          ?>
              <li class="<?php if($pageno >= $d_total_pages){ echo 'disabled'; } ?>">
                  <a href="<?php if($pageno >= $d_total_pages){ echo '#'; } else { echo "?page=wpfm-logs&pageno=".($pageno + 1).$d_content_tab; } ?>"><?php _e('Next','wp-file-manager-pro');?></a>
              </li>
              <!--<li><a href="?page=wpfm-logs&pageno=<?php //echo $d_total_pages.$d_content_tab; ?>"><?php //_e('Last','wp-file-manager-pro');?></a></li>-->
            </ul>
          <?php }?>
        </div>
      </div>
      <!--download_file-->

      <!--case 3-->
      <div class="upload_file logs_tab_block <?php if($tab_type  == 'upload_file'){ echo 'log_active';}?>">
        <h3><?php _e('Uploaded File','wp-file-manager-pro');?></h3>       
        <?php if(!empty($u_uploadedFiles) && $tab_type  == 'upload_file') { ?>
          <select class="bulk_selection_3">
          <option value=""><?php _e('Bulk Actions','wp-file-manager-pro');?></option>
          <option value="delete"><?php _e('Delete','wp-file-manager-pro');?></option>
        </select>
        <button class="bulkApply bulk_delete_3 button"><?php _e('Apply','wp-file-manager-pro');?></button>
          <table class="form-table">
                <tr>
                  <th><span class="styledCheckbox">
                      <input type="checkbox" id="checkedAll" class="select_all_upload"> <span class="fm_checkmark"></span>
                    </span></th>
                  <th><?php _e('Sr No.','wp-file-manager-pro');?></th>
                  <th><?php _e('User ID','wp-file-manager-pro');?></th>
                  <th><?php _e('User Name','wp-file-manager-pro');?></th>
                  <th><?php _e('Files','wp-file-manager-pro');?></th>
                  <th><?php _e('Date','wp-file-manager-pro');?></th>
                  <th><?php _e('Action','wp-file-manager-pro');?></th>
                </tr>
            <?php foreach($u_uploadedFiles as $uploadedFile) {
                $sno++;?>
                <tr>
                  <td><span class="styledCheckbox">
                      <input type="checkbox" class="single_check log_ids_3" name="log_ids_3[]" value=<?php echo $uploadedFile->id;?>> <span class="fm_checkmark"></span>
                    </span></td>                 
                  <td><?php echo $sno; ?></td>
                  <td><a href="<?php echo admin_url();?>user-edit.php?user_id=<?php echo $uploadedFile->uid; ?>"><?php echo $uploadedFile->uid; ?></a></td>
                  <td><?php echo $uploadedFile->uname; ?></td>
                  <td><?php echo $uploadedFile->files; ?></td>
                  <td><?php echo date('j M, Y H:i A', strtotime($uploadedFile->log_date)); ?></td>
                  <td><a class="del_log" title="<?php _e('Delete','wp-file-manager-pro');?>" href="<?php echo admin_url('admin.php');?>?page=wpfm-logs&tab_type=upload_file&action=delete&id=<?php echo $uploadedFile->id.'&key='.wp_create_nonce( 'log_del_nonce' );?>" onclick="return confirm ('<?php echo $confirmBox;?>')"><img src="<?php echo plugins_url('images/logs_trash.png', __FILE__); ?>"></a></td>
                </tr>
            <?php } ?>
            <tr>
            </tr>
          </table>
        <?php } else { ?>
        <div class="error"><p><?php _e('No log(s) found!','wp-file-manager-pro');?></p></div>
        <?php } ?>
          <div class="pagination_container">
          <?php
              $u_lmid_size = 5;
              $u_rmid_size = 4;
              $u_current = $pageno;
              $u_content_tab = "&tab_type=".$tab_type;
              if($u_total_pages>1){
            ?>
            <div class="logs_total_showing"><?php echo __("Showing ", 'wp-file-manager-pro').$showing_page." ".__("to", 'wp-file-manager-pro')." ".($sno)." ".__("of", 'wp-file-manager-pro')." ".count($u_total_uploadedFiles)." ".__("entries", 'wp-file-manager-pro');?></div>
            <ul class="pagination">
              <!--<li><a href="<?php //echo admin_url('admin.php');?>?page=wpfm-logs&pageno=1<?php //echo $u_content_tab?>">First</a></li>-->
              <li class="<?php if($pageno <= 1){ echo 'disabled'; } ?>">
                  <a href="<?php if($pageno <= 1){ echo 'javascript:void(0)'; } else { echo "?page=wpfm-logs&pageno=".($pageno - 1).$u_content_tab; } ?>">Prev</a>
              </li>
            <?php
            for ( $n = 1; $n <= $u_total_pages; $n++ )
            {
              if ( $n == $u_current )
              {
                ?>
                <li><a class='page-numbers current' href='<?php echo admin_url('admin.php');?>?page=wpfm-logs&pageno=<?php echo $n.$u_content_tab;?>'> <?php echo $n;?></a></li>
                <?php
              }
              else 
              {
                if ( ($u_current && $n >= $u_current - $u_lmid_size && $n <= $u_current + $u_rmid_size ) ):
                ?>
                  <li><a class='page-numbers' href='<?php echo admin_url('admin.php');?>?page=wpfm-logs&pageno=<?php echo $n.$u_content_tab ;?>'> <?php echo $n;?></a></li>
                  <?php
                endif;
              }
            }
          ?>
              <li class="<?php if($pageno >= $u_total_pages){ echo 'disabled'; } ?>">
                  <a href="<?php if($pageno >= $u_total_pages){ echo 'javascript:void(0)'; } else { echo admin_url('admin.php')."?page=wpfm-logs&pageno=".($pageno + 1).$u_content_tab; } ?>">Next</a>
              </li>
              <!--<li><a href="?page=wpfm-logs&pageno=<?php //echo $u_total_pages.$u_content_tab; ?>">Last</a></li>-->
            </ul>
          <?php }?>
        </div>
      </div>
      <!--upload_file-->
    </div>
  </div>
</div>
<script>
jQuery(document).ready(function(){
  var ajax_url = "<?php echo admin_url('admin-ajax.php')?>";
  jQuery(".logs_tab").on("click", function(){
    var class_val = jQuery(this).attr("data-attr");
    window.location.href = "<?php echo admin_url('admin.php');?>?page=wpfm-logs&tab_type="+class_val;
  });
  //end tab switch redirect
  var multivalue = [];
  jQuery( "body" ).on( "click", "#checkedAll",function() {
      if (this.checked) {
        jQuery(".single_check").each(function() {
            jQuery(this).prop('checked',true);
        });
      }
      else{
        jQuery(".single_check").each(function() {
          jQuery(this).prop('checked', false);
        });
      }
    });
//end all check

    jQuery( "body" ).on( "click", ".single_check",function() {
      var count = 0;
      jQuery(".single_check").each(function() {
          if(!this.checked){  //true on uncheck
            count = 1;
          }
        });
        if(count == 1){
            jQuery("#checkedAll").prop('checked',false);
          }
          else{
            jQuery("#checkedAll").prop('checked',true);
          }
    });
    //end check Single

jQuery(".bulk_delete_1").click(function () {
  bulk_delete(1,ajax_url);
}); //click
jQuery(".bulk_delete_2").click(function () {
  bulk_delete(2,ajax_url);
}); //click
jQuery(".bulk_delete_3").click(function () {
  bulk_delete(3,ajax_url);
}); //click

});
function bulk_delete(tab,ajax_url) {
  var bulk_action_selection = jQuery('.bulk_selection_'+tab+' :selected').val();
  if(bulk_action_selection == 'delete') {
    var delarr = new Array();
    jQuery(".log_ids_"+tab).each(function () {
        if(jQuery(this).is(':checked')) {
         delarr.push(jQuery(this).val());
        }
    });
    if(delarr == '') {
      alert("<?php _e('Please select log(s) to delete!','wp-file-manager-pro');?>");
    } else {
        var r = confirm("<?php _e('Are you sure want to remove selected log(s)?','wp-file-manager-pro');?>")
        if (r == true) {
            jQuery.ajax({
                type: "POST",
                url: ajax_url,
                data: {
                        action : 'mk_file_manager_pro_logs_remove',
                        delarr: delarr,
                        key: '<?php echo wp_create_nonce( 'del_alllog_nonce' );?>'        
                    },
                cache: false,

            success: function(response) {   
                alert(response);
                //location.reload();
                window.location.href = 'admin.php?page=wpfm-logs&tab_type=<?php echo $tab_type;?>';
            }
            });//ajax
        }
 }
} else {
  alert("<?php _e('Please select delete action!','wp-file-manager-pro');?>");
}
}
</script>
<style>
a:focus, button:focus {
    box-shadow: none !important;
}
.logs_page_wrap{
  background:none !important;
  border:none !important;
}
.logs_page_wrap .logs_tab_block{
  display:none;
}
.logs_page_wrap .log_active{
  display:block;
}
.logs_page_wrap ul.logs_tabs {
    display: table;
    width: 100%;
    margin: 0;
    background: #0073aa;
}
.logs_page_wrap ul.logs_tabs li.logs_tab{
    float: left;
    width: 33.3333%;
    color: #0073aa;
    margin: 0px;
}
.logs_page_wrap ul.logs_tabs li.logs_tab.log_active {
  background: #155a8e;
}
.logs_page_wrap ul.logs_tabs li.logs_tab a{
    color: #fff;
    padding: 20px 10px;
    display: block;
    text-decoration: none;
    border-right: 1px solid #1785c0;
    font-weight: 700;
    text-align: center;
    cursor:pointer;
}
.logs_page_wrap ul.logs_tabs li.logs_tab a:hover{
  background: #155a8e;
}
.logs_page_wrap ul.logs_tabs li.logs_tab a span{
  display:block;
  margin-bottom: 5px;
}
.logs_page_wrap ul.logs_tabs li.logs_tab a span img{
  width:48px;
  height:48px;
}
.logs_page_wrap .logs_tab_block h3{
  margin:0px;
  padding:18px 0px;
}
.logs_page_wrap .logs_tab_container{
    background: #fff;
    padding: 20px;
}
.logs_page_wrap .logs_tab_container table tr td, .logs_page_wrap .logs_tab_container table tr th {
    padding-left: 0px;
    display: table-cell;
    padding: 12px 8px 8px;
    text-align: center;
    border-right: 1px solid #fff;
}
.logs_page_wrap .logs_tab_container table tr th{
  color: #fff;
}
.logs_page_wrap .logs_tab_container table tr:first-child{
  background:#2173aa!important;
}
.logs_page_wrap .logs_tab_container table tr:nth-child(2n){
  background:#fff;
}
.logs_page_wrap .logs_tab_container table tr:nth-child(2n+1){
  background:#ddd;
}
.logs_page_wrap .logs_tab_container table tr td a img{
  width: 30px;
  height: 30px;
}
.logs_page_wrap ul.pagination li a{
  padding: 5px 10px;
    text-decoration: none;
    border: 1px solid#ddd;
    cursor: pointer;
    display: block;
}
.logs_page_wrap ul.pagination li a:hover, .logs_page_wrap ul.pagination li a.current{
  background: #165a8e;
  color: #fff;
}
.logs_page_wrap .pagination_container {
    display: table;
    width: 100%;
    padding: 20px 0px;
}
.logs_page_wrap .pagination_container ul.pagination {
    float: right;
    margin: 0px;
}
.logs_page_wrap .pagination_container ul.pagination li {
  float: left;
    margin-left: 4px;
}
.logs_page_wrap .pagination_container .logs_total_showing{
  float:left;
}
li.disabled a {
    color: #c5c5c5 !important;
    pointer-events: none;
    cursor: auto;
    background-color: #fff;
    border-color: #dee2e6;
}
.del_log:focus{
  outline: none;
  box-shadow: none;
}
</style>