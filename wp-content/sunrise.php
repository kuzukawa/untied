<?php
// wp-includes/ms-load.php:214
function my_pre_get_site_by_path($pre, $domain, $path, $segments, $paths) {
    $args = array(
        'number'                 => 1,
        'update_site_meta_cache' => false,
    );

    // 元ソースからドメインでのフィルタ部分を削除してパスだけでサイトを検索する

    if ( count( $paths ) > 1 ) {
        $args['path__in']               = $paths;
        $args['orderby']['path_length'] = 'DESC';
    } else {
        $args['path'] = array_shift( $paths );
    }

    $result = get_sites( $args );
    $site   = array_shift( $result );

    if ( $site ) {
        return $site;
    }
    return $pre;
}
add_filter('pre_get_site_by_path', 'my_pre_get_site_by_path', 10, 5);

// wp-includes/canonical.php:605
function my_redirect_canonical($redirect_url, $requested_url) { 
    // redirect_url のホスト名が home_url() 基準に変更されてしまうため
    // 元URL を プライマリドメイン基準に変更して差が無ければリダイレクト中止
    $_sanitized = str_replace($_SERVER['HTTP_HOST'], 'www.example.com', $requested_url);
    if ($_sanitized == $redirect_url) {
        return NULL;
    }
    return $redirect_url;
}
add_filter('redirect_canonical', 'my_redirect_canonical', 10, 2);