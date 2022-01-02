<?php
/*
DB Manager for WP FILE MANAGER
*/
define('DB_MANAGER_VERSION', '1.0');
if (!class_exists('mk_db_manager')):
class mk_db_manager
{
    /*
    Constructor
    */
    public function __construct()
    {
        add_action('admin_menu', array(&$this, 'db_manager_menu_page'));
        add_action('admin_enqueue_scripts', array(&$this, 'ffm_db_things'));
        add_action('wp_ajax_Update_Tbl_Record', array(&$this, 'UpdateTblRecord'));
        add_action('admin_post_mk_db_manager_export_db', array($this, 'export_db'));
    }

    /*
    * Checking WP File Manager Plugin is Activated or not.
    */
    public static function wp_file_manager_exists($type = '')
    {
        if (!function_exists('is_plugin_active')) {
            include_once ABSPATH.'wp-admin/includes/plugin.php';
        }
        if (!empty($type) && $type == 'pro') {
            $checkPlugin = 'wp-file-manager-pro/file_folder_manager_pro.php';
        } else {
            $checkPlugin = 'wp-file-manager/file_folder_manager.php';
        }
        if (is_plugin_active($checkPlugin)) {
            return true;
        } else {
            return false;
        }
    }

    /*
    Scripts
    */
    public function ffm_db_things()
    {
        $getPage = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
        $allowedPages = array(
                                      'wp_file_manager_db_manager',
                                      );
        if (!empty($getPage) && in_array($getPage, $allowedPages)):
            wp_enqueue_script('jquery-ui-resizable');
                        wp_enqueue_script('jquery-ui-script', plugins_url('js/jquery-ui.js', __FILE__));
        wp_enqueue_style('jquery-ui-style', FILEMANAGERPROURL.'/css/jquery-ui.css');
        wp_enqueue_script('fm_db_manager-script', plugins_url('js/fm_db_manager.js', __FILE__));
        wp_localize_script( 'fm_db_manager-script', 'fm_ajax', array(
            'table_name_validation'     => __( 'Missing Table Name in the form!', 'wp-file-manager-pro' ),
            'table_value_validation'     => __( 'Missing value in the form!', 'wp-file-manager-pro' ),
        ));
        wp_enqueue_style('fm_db_manager-style', plugins_url('css/fm_db_manager.css', __FILE__));
        endif;
    }

    /*
    Menu Page - submenu
    */
    public function db_manager_menu_page()
    {
        $opt = get_option('wp_filemanager_options');
        if(isset($opt['diable_local_db_fm']) && !empty($opt['diable_local_db_fm']) && $opt['diable_local_db_fm'] == 'yes') {
            
        } else {
            $permissions = $this->permissions();
            if (empty($permissions)) {
                $permissions = 'manage_options';
            }
            add_submenu_page('wp_file_manager', __('DB Manager', 'wp-file-manager-pro'), __('DB Manager', 'wp-file-manager-pro'), $permissions, 'wp_file_manager_db_manager', array(&$this, 'wp_file_manager_db_manager'));
        }
    }

    public function permissions()
        {
            $opt = get_option('wp_filemanager_options');
            $allowedroles = isset($opt['fm_user_roles_db']) ? $opt['fm_user_roles_db'] : '';
            if (empty($allowedroles)) {
                $allowedroles = array();
            }            
            $current_user = wp_get_current_user();
            $userLogin = $current_user->user_login;
            $userID = $current_user->ID;
            $user = new WP_User($userID);
            if (!empty($user->roles) && is_array($user->roles)) {
                foreach ($user->roles as $role):
                    $role;
                endforeach;
            }
            $permissions = 'manage_options';
            $mk_count_u_roles = array_intersect($user->roles,$allowedroles);            
            if ($role == 'administrator'):
                $permissions = 'manage_options';
             elseif(count($mk_count_u_roles) > 0):
                $permissions = 'read';
             endif;
            return $permissions;
        }

    /*
    DB table Main File
    */
    public function wp_file_manager_db_manager()
    {
        include 'inc/db_tables.php';
    }

    /*
    List Of Tables
    */
    public function show_tbls($dbtables)
    {
        $confirmBox = __('Are you sure want to empty Table? Table may not be recovered. So please delete carefully.','wp-file-manager-pro');
        $confirm_box = __('Are you sure want to delete Table? Deleted Table may not be recovered. So please delete carefully.','wp-file-manager-pro');
        ?>  
       <table width="100%" class="right_tbls">
        <tr>
        <th><?php _e('Tables', 'wp-file-manager-pro'); ?> (<?php echo count($dbtables); ?>) <a href="?page=wp_file_manager_db_manager&action=create_tbl&key=<?php echo wp_create_nonce( 'fm_crte_db_nonce' );?>" class="button"><?php _e('Create New Table','wp-file-manager-pro'); ?></a></th>
        <th></th>
        </tr>
        <?php if (!empty($dbtables) && is_array($dbtables)):
        foreach ($dbtables as $key => $dbtable): ?>
        <tr>
        <td><a href="?page=wp_file_manager_db_manager&action=browse&tbl=<?php echo $dbtable; ?>&key=<?php echo wp_create_nonce( 'fm_browse_db_nonce' );?>"><?php echo $dbtable; ?></a></td>
        <td><a href="?page=wp_file_manager_db_manager&action=browse&tbl=<?php echo $dbtable; ?>&key=<?php echo wp_create_nonce( 'fm_browse_db_nonce' );?>" title="<?php _e('Browse', 'wp-file-manager-pro'); ?>: <?php echo $dbtable; ?>"><?php _e('Browse', 'wp-file-manager-pro'); ?></a> | <a href="?page=wp_file_manager_db_manager&action=insert&tbl=<?php echo $dbtable; ?>&key=<?php echo wp_create_nonce( 'fm_ins_db_nonce' );?>" title="<?php _e('Insert', 'wp-file-manager-pro'); ?>: <?php echo $dbtable; ?>"><?php _e('Insert', 'wp-file-manager-pro'); ?></a> | <a href="?page=wp_file_manager_db_manager&action=drop&tbl=<?php echo $dbtable; ?>&key=<?php echo wp_create_nonce( 'fm_drop_db_nonce' );?>" title="<?php _e('Delete Table', 'wp-file-manager-pro'); ?>: <?php echo $dbtable; ?>" onclick="return confirm('<?php echo $confirm_box;?>')"><?php _e('Drop', 'wp-file-manager-pro'); ?></a> | <a href="?page=wp_file_manager_db_manager&action=truncate&tbl=<?php echo $dbtable; ?>&key=<?php echo wp_create_nonce( 'fm_trunc_db_nonce' );?>" title="<?php _e('Empty Table', 'wp-file-manager-pro'); ?>: <?php echo $dbtable; ?>" onclick="return confirm('<?php echo $confirmBox; ?>')"><?php _e('Empty', 'wp-file-manager-pro'); ?></a></td>
        </tr>
        <?php endforeach;
        endif; ?>
        </table>
	<?php
    }

    /*
    * Browsing Records
    */
    public function BrowseRecords($table2Edit, $keys = '', $values = '', $offSet = 0, $eat_cols = 20, $fuzzy = '')
    {
        global $wpdb;
        $table2Edit = htmlentities($table2Edit);
        // get the users data
        $keysArray = explode('~', $keys);
        $valsArray = explode('~', $values);
        //Get column information
        $cols = $wpdb->get_results('show columns from '.$table2Edit);
        if(!empty($cols)){
                //build where
                $where = '';
                $vals = array();
                for ($i = 0; $i < count($keysArray); ++$i) {
                    //need to find out if the value is for a numeric field or not
                    $isNumeric = 0;
                    foreach ($cols as $col) {
                        if ($col->Field == $keysArray[$i]) {
                            $isNumeric =
                                strpos($col->Type, 'int') !== false ||
                                strpos($col->Type, 'decimal') !== false ||
                                strpos($col->Type, 'float') !== false ||
                                strpos($col->Type, 'double') !== false ||
                                strpos($col->Type, 'real') !== false ||
                                strpos($col->Type, 'bit') !== false ||
                                strpos($col->Type, 'boolean') !== false ||
                                strpos($col->Type, 'serial') !== false;
                        }
                    }

                    if ($keysArray[$i] != '') {
                        if ($i != 0) {
                            $where = $where.' and ';
                        }

                        if ($isNumeric) {
                            $where = $where.$keysArray[$i].' = %d';
                            $vals[] = sanitize_text_field($valsArray[$i]);
                        } else {
                            if ($fuzzy == 'checked') {
                                $where = $where.$keysArray[$i].' like %s';
                                $vals[] = sanitize_text_field('%'.$valsArray[$i].'%');
                            } else {
                                $where = $where.$keysArray[$i].' = %s';
                                $vals[] = sanitize_text_field($valsArray[$i]);
                            }
                        }
                    }
                } 

                $pageno = $offSet;
            //
                $offSet = ($offSet-1) * $eat_cols;
                $total_rows = $wpdb->get_results('select * from '.$table2Edit);
                $total_pages = ceil(count($total_rows) / $eat_cols);

                //Get the records
                if (count($vals) > 0) {
                    //$sql = $wpdb->prepare('select * from '.$table2Edit.' where '.$where.' LIMIT '.$offSet.', '.$eat_cols);
                    $sql = $wpdb->prepare('select * from '.$table2Edit.' where '.$where.' LIMIT '.$offSet.', '.$eat_cols.'', $vals);
                } else {
                    $sql = 'select * from '.$table2Edit.' LIMIT '.$offSet.', '.$eat_cols.'';
                }

                $records = stripslashes_deep($wpdb->get_results($sql, 'ARRAY_A'));
                // print_r($records);
                $alertBox = __('Are you sure want to delete Table? Deleted Table may not be recovered. So please delete carefully.', 'wp-file-manager-pro');
                $alert_box = __('Are you sure want to empty Table? Table may not be recovered. So please delete carefully.', 'wp-file-manager-pro');
                $numCols = $wpdb->num_rows; ?>
                <div class="db_records">  
                <div class="btn_dv">
                <a href="?page=wp_file_manager_db_manager&amp;action=insert&amp;tbl=<?php echo $table2Edit; ?>&amp;key=<?php echo wp_create_nonce( 'fm_ins_db_nonce' );?>" title="<?php _e('ADD', 'wp-file-manager-pro'); ?>: <?php echo $table2Edit; ?>" class="db_new_add"><?php _e('Add New', 'wp-file-manager-pro'); ?></a> <a href="?page=wp_file_manager_db_manager&action=drop&tbl=<?php echo $table2Edit; ?>&key=<?php echo wp_create_nonce( 'fm_drop_db_nonce' );?>" title="<?php _e('Delete Table', 'wp-file-manager-pro'); ?>: <?php echo $table2Edit; ?>" onclick="return confirm('<?php echo $alertBox;?>')" class="button dropBtn"><?php _e('Drop', 'wp-file-manager-pro'); ?></a> <?php if (count($records) > 0) {
                    ?> <a href="?page=wp_file_manager_db_manager&action=truncate&tbl=<?php echo $table2Edit; ?>&key=<?php echo wp_create_nonce( 'fm_trunc_db_nonce' );?>" title="<?php _e('Empty Table', 'wp-file-manager-pro'); ?>: <?php echo $table2Edit; ?>" onclick="return confirm('<?php echo $alert_box;?>')" class="button button-primary emptyBtn"><?php _e('Empty', 'wp-file-manager-pro'); ?></a> <?php
                } ?>
                </div>
                <?php
                if ($numCols > 0) {
                    $primaryKeyExists = false; ?>
                <table>
                <tr>
                <th></th>
                <?php foreach ($cols as $col) {
                        ?>
                <th><?php echo $col->Field; ?></th>
                <?php
                    } ?>
                <th></th>
                </tr>
                <?php /* values */ ?>
                    <?php 
                    $in = 0;

                    $confrm_box = __('Are you sure want to update? It will redirect you to updation form.','wp-file-manager-pro');
                    $confrmBox = __('Are you sure want to delete? Deleted data may not be recovered. So please delete carefully.', 'wp-file-manager-pro');
                    foreach ($records as $record) {
                        ?>
                    <tr>
                    <td class="action_btn">
                                        <?php
                                        if (current_user_can('manage_options')) {
                                            ?>
                                        <a href="?page=wp_file_manager_db_manager&action=update&tbl=<?php echo $table2Edit; ?>&rid=<?php echo $records[$in][$cols[0]->Field]; ?>&pf=<?php echo $cols[0]->Field; ?>&key=<?php echo wp_create_nonce( 'fm_edit_db_nonce' );?>" onclick="return confirm('<?php echo $confrm_box;?>')" title="<?php _e('Edit', 'wp-file-manager-pro'); ?>"><img src="<?php echo plugins_url('/fm_dbmanager/images/edit-icon-png.png', dirname(__FILE__)); ?>" alt="<?php _e('Edit', 'wp-file-manager-pro'); ?>"/></a>
                                            <a href="?page=wp_file_manager_db_manager&action=delete&tbl=<?php echo $table2Edit; ?>&rid=<?php echo $records[$in][$cols[0]->Field]; ?>&pf=<?php echo $cols[0]->Field; ?>&key=<?php echo wp_create_nonce( 'fm_del_db_nonce' );?>" onclick="return confirm('<?php echo $confrmBox;?>')" title="<?php _e('Delete', 'wp-file-manager-pro'); ?>"><img src="<?php echo plugins_url('/fm_dbmanager/images/delete-icon-png.png', dirname(__FILE__)); ?>" alt="<?php _e('Delete', 'wp-file-manager-pro'); ?>"/></a>
                                            <?php
                                        } ?>
                                    </td>
                    <?php foreach ($cols as $col) {
                                            $row = $records[$in]; ?>
                    <?php if ($col->Key == 'PRI') {
                                                $primaryKeyExists = true; ?>
                                    <td class="primary_number" id="PRIMARY:<?php echo $col->Field; ?>"><?php echo $row[$col->Field]; ?></td>
                                    <?php
                                            } else {
                                                ?>
                                    <td id="<?php echo $col->Field; ?>"><input type="text"  value="<?php echo esc_html($row[$col->Field]); ?>" readonly/></td>
                                    <?php
                                            } ?>
                    <?php
                                        } ?>
                    <td class="action_btn">
                                        <?php
                                        if (current_user_can('manage_options')) {
                                            $edit_box = __('Are you sure want to update? It will redirect you to updation form.','wp-file-manager-pro');
                                            $editBox = __('Are you sure want to delete? Deleted data may not be recovered. So please delete carefully.','wp-file-manager-pro');
                                            ?>
                                        <a href="?page=wp_file_manager_db_manager&action=update&tbl=<?php echo $table2Edit; ?>&rid=<?php echo $records[$in][$cols[0]->Field]; ?>&pf=<?php echo $cols[0]->Field; ?>&key=<?php echo wp_create_nonce( 'fm_edit_db_nonce' );?>" onclick="return confirm('<?php echo $edit_box;?>')" title="<?php _e('Edit', 'wp-file-manager-pro'); ?>"><img src="<?php echo plugins_url('/fm_dbmanager/images/edit-icon-png.png', dirname(__FILE__)); ?>" alt="<?php _e('Edit', 'wp-file-manager-pro'); ?>"/></a>
                                            <a href="?page=wp_file_manager_db_manager&action=delete&tbl=<?php echo $table2Edit; ?>&rid=<?php echo $records[$in][$cols[0]->Field]; ?>&pf=<?php echo $cols[0]->Field; ?>&key=<?php echo wp_create_nonce( 'fm_del_db_nonce' );?>" onclick="return confirm('<?php echo $editBox;?>')" title="<?php _e('Delete', 'wp-file-manager-pro'); ?>"><img src="<?php echo plugins_url('/fm_dbmanager/images/delete-icon-png.png', dirname(__FILE__)); ?>" alt="<?php _e('Delete', 'wp-file-manager-pro'); ?>"/></a>
                                            <?php
                                        } ?>
                                    </td>
                    </tr> 
                    <?php ++$in;
                    } ?>
                </table>
                    <?php
                } else {
                    $this->error(__('No Records Found!','wp-file-manager-pro'));
                } ?>
				</div>

            <?php 
            if (count($records) > 0){?>
                <ul class="pagination">
                    <li><a href="?page=wp_file_manager_db_manager&action=browse&tbl=<?php echo $table2Edit; ?>&key=<?php echo wp_create_nonce( 'fm_browse_db_nonce' );?>&pageno=1"><?php _e('First', 'wp-file-manager-pro'); ?></a></li>
                    <li class="<?php if($pageno <= 1){ echo 'disabled'; } ?>">
                        <a href="<?php if($pageno <= 1){ echo 'javascript:void(0)'; } else { echo "?page=wp_file_manager_db_manager&action=browse&tbl=".$table2Edit."&key=".wp_create_nonce( 'fm_browse_db_nonce' )."&pageno=".($pageno - 1); } ?>"><?php _e('Prev', 'wp-file-manager-pro'); ?></a>
                    </li>
                    <?php
                    $end_size = 1;
                    $lmid_size = 5;
                    $rmid_size = 4;
                    $current = $pageno;
                    $tbl_val = sanitize_text_field($_GET["tbl"]);
                    for ( $n = 1; $n <= $total_pages; $n++ )
                    {
                        if ( $n == $current ) 
                        {
                            ?>
                            <li><a class='page-numbers current' href='?page=wp_file_manager_db_manager&action=browse&tbl=<?php echo $table2Edit; ?>&key=<?php echo wp_create_nonce( 'fm_browse_db_nonce' );?>&pageno=<?php echo $n;?>'> <?php echo $n;?></a></li>
                            <?php
                        }
                            
                        else 
                        {
                            if ( ($current && $n >= $current - $lmid_size && $n <= $current + $rmid_size ) ):
                            ?>
                                <li><a class='page-numbers' href='?page=wp_file_manager_db_manager&action=browse&tbl=<?php echo $table2Edit; ?>&key=<?php echo wp_create_nonce( 'fm_browse_db_nonce' );?>&pageno=<?php echo $n;?>'> <?php echo $n;?></a></li>
                                <?php
                            endif;
                        }
                    }
                ?>
                    <li class="<?php if($pageno >= $total_pages){ echo 'disabled'; } ?>">
                        <a href="<?php if($pageno >= $total_pages){ echo 'javascript:void(0)'; } else { echo "?page=wp_file_manager_db_manager&action=browse&tbl=".$table2Edit."&key=".wp_create_nonce( 'fm_browse_db_nonce' )."&pageno=".($pageno + 1); } ?>"><?php _e('Next', 'wp-file-manager-pro'); ?></a>
                    </li>
                    <li><a href="?page=wp_file_manager_db_manager&action=browse&tbl=<?php echo $table2Edit; ?>&key=<?php echo wp_create_nonce( 'fm_browse_db_nonce' );?>&pageno=<?php echo $total_pages; ?>"><?php _e('Last', 'wp-file-manager-pro'); ?></a></li>
                </ul>
        <?php }?>
        <style>
            ul.pagination {
                display: table;
                width: 100%;
            }
            ul.pagination li {
                float: left;
                margin: 0px;
                margin-right: 5px;
            }
            ul.pagination li a {
                color: #000;
                text-decoration: none;
                padding: 4px 11px;
                background: #fff;
                border: 1px solid #ddd;
                display: block;
                box-shadow: 0px 2px #c1c0c01f;
            }
            a.page-numbers.current {
                background: #e33611;
                color: #fff;
            }
            ul.pagination li.disabled a {
                color: rgba(16, 16, 16, 0.3);
                background-color: rgba(239, 239, 239, 0.3);
                border-color: rgba(118, 118, 118, 0.3);
                cursor: default;
            }
        </style>

		<?php
        } else {
            echo __("You don't have permission to access this area.",'wp-file-manager-pro');
            die();
        }
    }

    /* Table Table */
    public function TableDetails($table2Edit)
    {
        global $wpdb;
        $cols = $wpdb->get_results('show columns from '.$table2Edit); ?>
		<form action="" method="post">
        <?php wp_nonce_field('mk_insert_db_tbl', 'insert_db_tbl'); ?>
		<div class="add_form_dv">
			<table id="tableCols">
				<tr>
					<th><strong><?php _e('Column', 'wp-file-manager-pro'); ?></strong></th>
					<th><strong><?php _e('Value', 'wp-file-manager-pro'); ?></strong></th>
				</tr>
			<?php
                foreach ($cols as $col) {
                    ?>
					<tr>
						<td>
							<?php
                                echo $col->Field.' ('.$col->Type.')';
                    if ($col->Key == 'PRI') {
                        echo ' [PRIMARY]';
                    } ?>
	
						</td>
						<td>
							<input type="text" id="<?php echo sanitize_text_field($col->Field); ?>" name="<?php echo sanitize_text_field($col->Field); ?>" value="" />
						</td>
	
					</tr>
				<?php
                } ?>
			</table>
		</div>
			
		<div class="bot_btn_dv">
				&nbsp;
			<input type="reset" class="reset-button" value="<?php _e('Reset', 'wp-file-manager-pro'); ?>" name="reset" />
			<?php
            // Check that editor has rights to add
            if (current_user_can('manage_options')) {
                ?>
			&nbsp;
			<input type="submit" class="add-button" value="<?php _e('Add', 'wp-file-manager-pro'); ?>" name="save_tbl_data" />
			<?php
            } ?>
		</div>
		
		</form>
		<?php
    }

    /* Create Record */
    public function CreateRecord($table2Edit, $values)
    {
        global $wpdb;
        $continue = false;
        if (!empty($values)) {
            foreach ($values as $key => $val) {
                if (!empty($values[$key])) {
                    $continue = true;
                }
            }
        }
        if ($continue) {
            if ($wpdb->insert($table2Edit, $values)) {
                $res = $this->success(__('New Record Created', 'wp-file-manager-pro'));
            } else {
                $res = $this->error(__('Unable to create new record', 'wp-file-manager-pro'));
                $wpdb->show_errors();
                $wpdb->print_error();
                $wpdb->hide_errors();
            }
        } else {
            $res = $this->error(__('Unable to create new record', 'wp-file-manager-pro'));
        }
        return $res;
    }

    /* Update Table Details */
    public function UpdateTableDetails($table2Edit, $fieldname, $rid)
    {
        if (empty($table2Edit) || empty($fieldname) || empty($rid)) {
            $this->error(__('Oops! Some thing went wrong! Please try again.', 'wp-file-manager-pro'));
        } else {
            global $wpdb;
            $cols = $wpdb->get_results('show columns from '.$table2Edit);

            if(strpos(strtolower($fieldname), "select") !== false || strpos(strtolower($fieldname), "update") !== false || strpos(strtolower($fieldname), "delete") !== false || strpos(strtolower($fieldname), "drop") !== false || strpos(strtolower($fieldname), "truncate") !== false){
                echo __("You don't have permission to access this area.",'wp-file-manager-pro');
                die();
            }
            $sql = 'select * from '.$table2Edit.' where '.$fieldname." = '".$rid."'";
            $record = stripslashes_deep($wpdb->get_row($sql, 'ARRAY_A')); ?>
           <?php 
           if(!empty($record)){
           ?>
            <div class="back_btn_dv">
            <a href="?page=wp_file_manager_db_manager&action=browse&tbl=<?php echo $table2Edit; ?>&key=<?php echo wp_create_nonce( 'fm_browse_db_nonce' );?>"><?php _e('&larr; Back', 'wp-file-manager-pro'); ?></a>
        </div>
            <form method="post" id="update_tbl_form">
            <?php wp_nonce_field('mk_update_db_tbl', 'update_db_tbl'); ?>
            <div style="overflow: auto">
                <table id="tableCols">
                    <tr>
                        <th><strong><?php _e('Column', 'wp-file-manager-pro'); ?></strong></th>
                        <th><strong><?php _e('Value', 'wp-file-manager-pro'); ?></strong></th>
                    </tr>
                <?php
                    foreach ($cols as $col) {
                        ?>
                        <tr>
                            <td>
                                <?php
                                    echo $col->Field.' ('.$col->Type.')';
                        if ($col->Key == 'PRI') {
                            echo ' [PRIMARY]';
                        } ?>
        
                            </td>
                            <td>
                                <input type="text" name="<?php echo sanitize_text_field($col->Field); ?>" id="<?php echo sanitize_text_field($col->Field); ?>" value="<?php echo isset($record[$col->Field]) ? $record[$col->Field] : ''; ?>" />
                            </td>
        
                        </tr>
                    <?php
                    } ?>
                </table>
            </div>
            </form>
            <div class="bot_btn_dv">
                <?php
                // Check that editor has rights to add
                if (current_user_can('manage_options')) {
                    ?>
                &nbsp;
                <input type="submit" class="custom_btn update_tbl_data" value="<?php _e('Update', 'wp-file-manager-pro'); ?>" name="update_tbl_data" />
                <?php
                } ?>
            </div>
		<?php
           } else {
                echo __("You don't have permission to access this area.",'wp-file-manager-pro');
           }
        }
    }

    /* Update Record - Ajax*/
    public function UpdateTblRecord()
    {
        $value = array();
        parse_str($_POST['values'], $value);
        $tbl_data = json_encode($value);
        $decoded_tbl_data = json_decode($tbl_data, true);
        if(wp_verify_nonce( $decoded_tbl_data['update_db_tbl'], 'mk_update_db_tbl' )){
            global $wpdb;
            $table2Edit = sanitize_text_field($_POST['table2Edit']);
            $fieldname = sanitize_text_field($_POST['fieldname']);
            $rid = intval($_POST['rid']);
            unset($decoded_tbl_data['update_db_tbl']);
            unset($decoded_tbl_data['_wp_http_referer']);
            $continue = false;
            if (!empty($decoded_tbl_data[$fieldname])) {
                $continue = true;
            }
            if ($continue) {
                if ($wpdb->update($table2Edit, $decoded_tbl_data, array($fieldname => $rid))) {
                    echo json_encode(array('type' => 'success', 'msg' => __("Record.",'wp-file-manager-pro').' <strong>'.$table2Edit.' : '.$rid.'</strong> '.__("Updated Successfully.",'wp-file-manager-pro')));
                } else {
                    echo json_encode(array('type' => 'error', 'msg' => __('Unable to Update record','wp-file-manager-pro')));
                }
            } else {
                echo json_encode(array('type' => 'error', 'msg' => __('Unable to Update record','wp-file-manager-pro')));
            }
        } else {
            echo json_encode(array('type' => 'error', 'msg' => __('Security token mismatch','wp-file-manager-pro')));
        }
        die;
    }

    /* Delete Record */
    public function deleteRecord($table2Edit, $fieldname, $rid)
    {
        if (empty($table2Edit) || empty($fieldname) || empty($rid)) {
            return $this->error(__('Oops! Some thing went wrong! Please try again.','wp-file-manager-pro'));
        } else {
             
          $res = '<a href="?page=wp_file_manager_db_manager&action=browse&tbl='.$table2Edit.'&key='.wp_create_nonce( "fm_browse_db_nonce" ).'" class="button">'.__("&larr; Back", "wp-file-manager-pro").'</a>';
		 global $wpdb;
            $deleteRow = $wpdb->delete($table2Edit, array($fieldname => $rid));
            if ($deleteRow) {
                $res .= $this->success(__('Record', "wp-file-manager-pro").' <strong>'.$table2Edit.' :: '.$fieldname.' :: '.$rid.' '.__('Deleted successfully', "wp-file-manager-pro").'.</strong>');
            } else {
                $res .= $this->error(__('Error occured while deleting a record.', "wp-file-manager-pro"));
            }
            return $res;
        }
    }

    public function createTable($POST)
    {
        global $wpdb;
        $table_name = sanitize_text_field($_POST['table']);
        unset($POST['table']);
        $charset_collate = $wpdb->get_charset_collate();
        $error = array();
        if (count($POST['field_name']) > 0) {
            $sql = "CREATE TABLE $table_name (";
            foreach ($POST['field_name'] as $key => $val):
            if ($POST['field_type'][$key] == 'INT') {
                $fieldlength = empty($POST['field_length'][$key]) || trim($POST['field_length'][$key]) == "" ? '11' : $POST['field_length'][$key];
            } else {
                $fieldlength = $POST['field_length'][$key];
            }
            $emptyCheck = isset($POST['field_null'][$key]) ? $POST['field_null'][$key] : '';
            if (empty($emptyCheck)) {
                $emptyCheck = 'NOT NULL';
            }
            if (empty($POST['field_extra'][0])) {
                $POST['field_extra'][0] = 'AUTO_INCREMENT';
            }
            if($POST['field_type'][$key] == "TEXT" || $POST['field_type'][$key] == "LONGTEXT" || $POST['field_type'][$key] == "MEDIUMTEXT" || $POST['field_type'][$key] == "TINYTEXT" || $POST['field_type'][$key] == "TINYBLOB" || $POST['field_type'][$key] == "MEDIUMBLOB" || $POST['field_type'][$key] == "BLOB" || $POST['field_type'][$key] == "LONGBLOB" || $POST['field_type'][$key] == "DATE" || $POST['field_type'][$key] == "DATETIME" || $POST['field_type'][$key] == "DATETIME" || $POST['field_type'][$key] == "TIMESTAMP" || $POST['field_type'][$key] == "TIME"){
                $sql .= ''.$val.' '.$POST['field_type'][$key].' '.$emptyCheck;
                if(isset($POST['field_extra'][$key])){
                    $sql .=' '.$POST['field_extra'][$key].',';
                } else{
                    $sql .=',';
                }
            } else {
                $sql .= ''.$val.' '.$POST['field_type'][$key].'('.$fieldlength.') '.$emptyCheck;
                if(isset($POST['field_extra'][$key])){
                    $sql .=' '.$POST['field_extra'][$key].',';
                } else{
                    $sql .=',';
                }
            }
            endforeach;
            $sql .= 'PRIMARY KEY  ('.$POST['field_name'][0].')';
            $sql .= ") $charset_collate;";
        }
        $create_TBL = $wpdb->query($sql);
        if ($create_TBL) {
            return $this->success(__('Table Created Successfully !', "wp-file-manager-pro"));
        } else {
            $wpdb->show_errors();
            $wpdb->print_error();
            $wpdb->hide_errors();
            return $this->error(__('Table Not Created !', "wp-file-manager-pro"));
        }
    }

    /* Create new Table */
    public function create_tbl()
    {
        include 'inc/createTbl.php';
    }

    /* drop tbl */
    public function drop_table($tableName)
    {
        global $wpdb;
        $dropTbl = $wpdb->query('DROP TABLE '.$tableName.'');
        if ($dropTbl) {
            return $this->success(__('Table', "wp-file-manager-pro").' <strong>'.$tableName.'</strong> '.__('Deleted Successfully!', "wp-file-manager-pro"));
        } else {
            return $this->error(__('Table', "wp-file-manager-pro").' <strong>'.$tableName.'</strong> '.__('Not Deleted!', "wp-file-manager-pro"));
        }
    }

    /* Truncate tbl */
    public function truncate_tbl($tableName)
    {
        global $wpdb;
        $truncateTbl = $wpdb->query('TRUNCATE TABLE '.$tableName.'');
        if ($truncateTbl) {
            return $this->success(__('Table records of', "wp-file-manager-pro").' <strong>'.$tableName.'</strong> '.__('Deleted Successfully!', "wp-file-manager-pro"));
        } else {
            return $this->error(__('Table records of', "wp-file-manager-pro").' <strong>'.$tableName.'</strong> '.__('Not Deleted!', "wp-file-manager-pro"));
        }
    }

    /* Export DB page */
    public function export_db()
    {
        include 'inc/exportdb.php';
    }

    /* backup */
    public function exportDB($host, $user, $pass, $name, $tables = false, $backup_name = false)
    {
        $mysqli = new mysqli($host, $user, $pass, $name);
        $mysqli->select_db($name);
        $mysqli->query("SET NAMES 'utf8'");
        $queryTables = $mysqli->query('SHOW TABLES');
        while ($row = $queryTables->fetch_row()) {
            $target_tables[] = $row[0];
        }
        if ($tables !== false) {
            $target_tables = array_intersect($target_tables, $tables);
        }
        foreach ($target_tables as $table) {
            $result = $mysqli->query('SELECT * FROM '.$table);
            $fields_amount = $result->field_count;
            $rows_num = $mysqli->affected_rows;
            $res = $mysqli->query('SHOW CREATE TABLE '.$table);
            $TableMLine = $res->fetch_row();
            $content = (!isset($content) ? '' : $content)."\n\n".$TableMLine[1].";\n\n";
            for ($i = 0, $st_counter = 0; $i < $fields_amount; $i++, $st_counter = 0) {
                while ($row = $result->fetch_row()) {
                    if ($st_counter % 100 == 0 || $st_counter == 0) {
                        $content .= "\nINSERT INTO ".$table.' VALUES';
                    }
                    $content .= "\n(";
                    for ($j = 0; $j < $fields_amount; ++$j) {
                        $row[$j] = str_replace("\n", '\\n', addslashes($row[$j]));
                        if (isset($row[$j])) {
                            $content .= '"'.$row[$j].'"';
                        } else {
                            $content .= '""';
                        }
                        if ($j < ($fields_amount - 1)) {
                            $content .= ',';
                        }
                    }
                    $content .= ')';

                    if ((($st_counter + 1) % 100 == 0 && $st_counter != 0) || $st_counter + 1 == $rows_num) {
                        $content .= ';';
                    } else {
                        $content .= ',';
                    }
                    $st_counter = $st_counter + 1;
                }
            }
            $content .= "\n\n\n";
        }
        $backup_name = $backup_name ? $backup_name : $name.'_('.date('H-i-s').'_'.date('d-m-Y').')__'.rand(1, 11111111).'.sql';
        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: Binary');
        header('Content-disposition: attachment; filename="'.$backup_name.'"');
        echo $content;
        exit;
    }

    /* Error Msg */
    public function error($msg)
    {
        return __('<div id="setting-error-settings_updated" class="error settings-error notice"><p><strong>'.$msg.'</strong></p></div>', 'fm');
    }

    /* Success Msg */
    public function success($msg)
    {
        return __('<div id="setting-error-settings_updated" class="updated settings-error notice"><p><strong>'.$msg.'</strong></p></div>', 'fm');
    }

    /* Alert */
    public function alert($msg)
    {
        $alert = '<script>';
        $alert .= 'alert("'.$msg.'")';
        $alert .= '</script>';

        return $alert;
    }
}
endif;
/* Required to hook with WP File Manager */
add_action('load_filemanager_extensions', 'dbmanager_extension_load');
function dbmanager_extension_load()
{
    new mk_db_manager();
}