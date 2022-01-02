<?php if ( ! defined( 'ABSPATH' ) ) exit;
$this->fm_custom_assets();
global $wpdb; ?>
<div class="wrap wp_file_manager_gopro">
    <div class="fmInnerWrap fm_varify_page">

        <h3 class="mainHeading fm_license_de">
            <span class="headingIcon"><img src="<?php echo plugins_url( 'images/cart.svg', __FILE__ );?>"></span>
            <span
                class="headingText"><?php echo __('File Manager PRO - Please enter your order details below. If Not', 'wp-file-manager-pro').' <a href="https://filemanagerpro.io/product/file-manager/" target="_blank" class="page-title-action buy-link" title="click to buy Licence Key">'.__('Buy Now', 'wp-file-manager-pro').'</a>';?></span>
        </h3>
        <p class="description">
            <?php echo __('To unlock updates, please enter your order id and license key below. If you don\'t have a licence key', 'wp-file-manager-pro').', <a href="https://filemanagerpro.io/product/file-manager/" target="_blank" class="page-title-action buy-link" title="'.__('click to buy Licence Key', 'wp-file-manager-pro').'">'.__('please see details & pricing', 'wp-file-manager-pro').'</a>';?>
        </p>
        <?php
if(isset($_POST['verify_wp_file_manager_plugin']))
{	
   mk_file_folder_manager::verify(intval($_POST['orderid']), sanitize_text_field(htmlentities($_POST['licenceKey'])), sanitize_text_field(htmlentities($_GET['page'])));
}
	?>
        <div class="container">
            <form id="verifyfilemanager" method="post" name="verifyfilemanager" action="">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row"><label
                                    for="orderid"><?php _e('ORDER ID (#) *', 'wp-file-manager-pro');?></label></th>
                            <td><input type="text" class="regular-text" value="" id="orderid" name="orderid"
                                    required="required">
                                <p id="tagline-description" class="description">
                                    <?php _e('Please check your email for Order ID.', 'wp-file-manager-pro');?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label
                                    for="licenceKey"><?php _e('LICENCE KEY *', 'wp-file-manager-pro');?></label></th>
                            <td><input type="text" class="regular-text" value="" id="licenceKey" name="licenceKey"
                                    required="required">
                                <p id="tagline-description" class="description">
                                    <?php _e('Please check your email for Licence Key.', 'wp-file-manager-pro');?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="btnDv margin_bot30">
                    <input type="submit" value="<?php _e('Click here to verify', 'wp-file-manager-pro');?>" class="button button-primary fmCustomBtn"
                        name="verify_wp_file_manager_plugin">
                </div>
            </form>
            <?php self::error(__('Note: If you have already purchased this plugin then please check your email. You must have got an Email with orders details in your email, if you didn\'t get any Email. Please contact us at <a href="https://filemanagerpro.io/contact/">https://filemanagerpro.io/contact/</a>', 'wp-file-manager-pro'));?>
        </div>
    </div>

    <style>
    .fm_varify_page form .regular-text {
        border: 1px solid #ddd;
        box-shadow: none;
        padding: 10px;
        height: inherit;
    }

    .fm_varify_page form .margin_bot30 {
        margin-bottom: 30px;
    }

    .fm_varify_page .page-title-action.buy-link {
        background: #267ddd;
        color: #fff;
        border: none;
        border-radius: 30px;
        padding: 5px 15px;
    }
    </style>