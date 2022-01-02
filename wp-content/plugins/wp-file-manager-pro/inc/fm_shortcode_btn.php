<?php if (!defined('ABSPATH'))
    exit;
add_thickbox();
global $wpdb;
$manage_shortcodes = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'wpfm_shortcodes ORDER BY id DESC');
?>
<a onclick="showPopup()" class='button button-primary thickbox' title='WP File Manager PRO' href='#TB_inline?width=100%&height=300&inlineId=fm_pro_shortcode'>
  <?php _e('Insert File Manager Shortcode', 'wp-file-manager-pro'); ?>
</a>
<div id="fm_pro_shortcode" style="display:none;">
  <table class="form-table fm_pro_form">
    <tbody>
      <tr>
        <th scope="row"><label for="blogname"><?php _e('Select Shortcode', 'wp-file-manager-pro'); ?></label></th>
        <td>
          <select id="fm_shortcode">
            <option value=""><?php _e('Choose Shortcode', 'wp-file-manager-pro'); ?></option>
            <?php if(count($manage_shortcodes) > 0){
              foreach($manage_shortcodes as $shortcode){
                $shortcode_name = $shortcode->type == "loggedin" ? "wp_file_manager" : "wp_file_manager_without_login";
                $shortcode_name = $shortcode_name." id='".$shortcode->shotcode_key."' title='".$shortcode->shotcode_title."'";
                ?>
                <option value="<?php echo '['.$shortcode_name.']'?>"><?php echo $shortcode->shotcode_title;?></option>
                <?php
              }
            }?>
          </select>
        </td>
      </tr>
      <tr>
        <th scope="row"></th>
        <td>
          <p class="submit">
            <input name="insert_fm_shortcode" id="insert_fm_shortcode" class="button button-primary" value="Insert Shortcode" type="submit">
          </p>
        </td>
      </tr>
    </tbody>
  </table> 
</div>