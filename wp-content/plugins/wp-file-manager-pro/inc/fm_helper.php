<?php 
if ( ! defined( 'ABSPATH' ) ) exit;
$current_user = wp_get_current_user();
$userLogin = $current_user->user_login;
$userID = $current_user->ID; 
$user = new WP_User( $userID );
if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
    foreach ( $user->roles as $role ):
        $role;
    endforeach;	
}
$file_operations = array();

function checkUserRestriction($selected_user, $curLogin,$opt=array()){
    $operations = array();
    foreach($selected_user as $mkKey => $mkUser) {              
        if($mkUser == $curLogin) {
            $operations = isset($opt['users_fileoperations_'.$mkKey]) ? array_merge($opt['users_fileoperations_'.$mkKey],$operations) : $operations;
        }
    }
    return $operations;
}

function checkUserRoleRestriction($select_user_roles,$role,$opt=array()){
    $operations = array();
    foreach($select_user_roles as $mkRKey => $mkUserRole) {
        if($mkUserRole == $role) {
            $operations = isset($opt['userrole_fileoperations_'.$mkRKey]) ? array_merge($opt['userrole_fileoperations_'.$mkRKey],$operations) : $operations;
        }
    }
    return $operations;
}

function addCssStyleForInfo($class,$theme){
    $hasClass = !empty($class) ? ' .elfinder.'.$class.' ' : ' .elfinder ';
    $css = '.theme-'.$theme.$hasClass.'.elfinder-contextmenu .elfinder-contextmenu-item.op-info {
        display:none !important;
    }';
    return $css;
}

function addCssStyleForDownload($class,$is_local){
    $hasClass = !empty($class) ? ' .elfinder.'.$class.' ' : '';
    $css = $hasClass.'.elfinder-dialog .ui-dialog-content table.elfinder-info-tb a.share-link {
        pointer-events: none;
        cursor: default;
        text-decoration: none;
    }';
    if(!$is_local){
        $css .= $hasClass.'.elfinder-dialog .ui-dialog-content table.elfinder-info-tb a.share-link {
            pointer-events: visible !important;
            cursor: pointer !important;
            text-decoration: underline !important;
        }';
    }
    return $css;
}

$localStorage = array();
$localStorage["hide_toolbar"] = false;
$localStorage["hide_context_menu"] = false;
$localStorage["file_operations"] = array();
$localStorage["gdrive_file_operations"] = array();
$selected_user = (isset($opt['select_users']) && !empty($opt['select_users'])) ? $opt['select_users'] : array();
$select_user_roles = (isset($opt['select_user_roles']) && !empty($opt['select_user_roles'])) ? $opt['select_user_roles'] : array();
if(in_array($userLogin, $selected_user)){
    $key = array_search($userLogin, $selected_user);
    if(!empty($opt['user_disable_toolbar_'.$key]) && $opt['user_disable_toolbar_'.$key] == 'yes'){
        $localStorage["hide_toolbar"] = true;
    }					
    if(!empty($opt['user_disable_context_'.$key]) && $opt['user_disable_context_'.$key] == 'yes'){
        $localStorage["hide_context_menu"] = true;
    }
    $localStorage["file_operations"] = checkUserRestriction($selected_user, $userLogin,$opt);
} else if(in_array($role, $select_user_roles)) {
    $key = array_search($role, $select_user_roles);
    if(!empty($opt['user_role_disable_toolbar_'.$key]) && $opt['user_role_disable_toolbar_'.$key] == 'yes'){
        $localStorage["hide_toolbar"] = true;
    }
    if(!empty($opt['user_role_disable_context_'.$key]) && $opt['user_role_disable_context_'.$key] == 'yes'){
        $localStorage["hide_context_menu"] = true;
    }
    $localStorage["file_operations"] = checkUserRoleRestriction($select_user_roles,$role,$opt);
}

/*** Gcloud Check */
if (class_exists('wp_file_manager_google_cloud')) {
    $gcloud = get_option('wp_file_manager_gcloud');
    $gcloud_selected_user = (isset($gcloud['select_users']) && !empty($gcloud['select_users'])) ? $gcloud['select_users'] : array();
    $gcloud_select_user_roles = (isset($gcloud['select_user_roles']) && !empty($gcloud['select_user_roles'])) ? $gcloud['select_user_roles'] : array();
    
	if(in_array($userLogin, $gcloud_selected_user)){
        $key = array_search($userLogin, $gcloud_selected_user);
        $localStorage["gcloud_file_operations"] = checkUserRestriction($gcloud_selected_user, $userLogin,$gcloud);
    } else if(in_array($role, $gcloud_select_user_roles)) {
        $key = array_search($role, $gcloud_select_user_roles);
        $localStorage["gcloud_file_operations"] = checkUserRoleRestriction($gcloud_select_user_roles,$role,$gcloud);
    }
}

/*** DigitalOcean Check */
if (class_exists('wp_file_manager_digitalOcean')) {
    $digitalOcean = get_option('wp_file_manager_digitalOcean');
    $digitalOcean_selected_user = (isset($digitalOcean['select_users']) && !empty($digitalOcean['select_users'])) ? $digitalOcean['select_users'] : array();
    $digitalOcean_select_user_roles = (isset($digitalOcean['select_user_roles']) && !empty($digitalOcean['select_user_roles'])) ? $digitalOcean['select_user_roles'] : array();
    
	if(in_array($userLogin, $digitalOcean_selected_user)){
        $key = array_search($userLogin, $digitalOcean_selected_user);
        $localStorage["digital_ocean_file_operations"] = checkUserRestriction($digitalOcean_selected_user, $userLogin,$digitalOcean);
    } else if(in_array($role, $digitalOcean_select_user_roles)) {
        $key = array_search($role, $digitalOcean_select_user_roles);
        $localStorage["digital_ocean_file_operations"] = checkUserRoleRestriction($digitalOcean_select_user_roles,$role,$digitalOcean);
    }
}
?>