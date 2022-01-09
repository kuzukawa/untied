<?php
/* テーマの機能追加 */
add_theme_support('post-thumbnails');

/* 【管理画面】カスタム投稿の追加 */
function custom_post_type(){
    $labels = array(
        //管理画面に表示する名前
        'name' => 'ニュース管理',
        //メニューに表示する名前
        'menu_name' => 'ニュース管理',
        //メニューに表示する名前(一覧)
        'all_items' => 'ニュース一覧',
        //メニューに表示する名前(新規追加)
        'add_new' => '新規追加',
        //新規追加ページのタイトル
        'add_new_item' => '新規ニュース追加',
        //編集ページのタイトル
        'edit_item' => 'ニュースを編集',
        //一覧ページの「新規追加」ボタンのラベル
        'new_item' => '新規ニュース',
        //編集ページの「投稿を表示」ボタンラベル
        'view_item' => 'ニュースを表示',
        //一覧ページの検索ボタンのラベル
        'search_items' => 'ニュースを検索',
        //一覧ページに投稿が見つからなかったときに表示
        'not_found' => '投稿されたイベントはありません',
        //ゴミ箱に何も入っていないときに表示
        'not_found_in_trash' => 'ゴミ箱にニュースはありません',
    );

    $supports = array(
        'title', 'editor','thumbnail',
    );

    $args=array(
        'label' => __('news'),
        'labels' => $labels,
        'public' => true,
        'menu_position' => 4,
        'supports' => $supports,
        'menu_icon' => 'dashicons-text-page',
        'has_archive' => 'event',
    );
    register_post_type('news', $args);

    //カスタムタクソノミー：newsカテゴリー
    $labels = array(
        'name' => _x('カテゴリー', 'taxonomy general name' ),
        'singular_name' => _x('カテゴリー', 'taxonomy singular name'),
        'add_new_item' => __('新規カテゴリーを追加'),
        'edit_item' => __('カテゴリーの編集'),
        'update_item' => __('カテゴリーを更新'),
        'search_items' => __('カテゴリーを検索'),
    );
    $args = array(
        'labels' => $labels,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'rewrite' =>array('slug' => 'news', 'with_front' => false),
        'hierarchical' => true,
        'update_count_callback' => '_update_post_term_count',
    );
    register_taxonomy('news_category', 'news', $args);

}
add_action('init', custom_post_type);

/* 投稿一覧の表示項目のカスタマイズ */
function add_posts_columns( $columns ) {
    $columns['category'] = 'カテゴリー';
    $columns['eyecatch'] = 'アイキャッチ';
    return $columns;
}
function custom_posts_column( $column_name, $post_id ) {
    if ( $column_name == 'category' ) {
        $cf_category = get_post_meta( $post_id, 'category', true );
        echo ( $cf_category ) ? $cf_category : '－';
    }
    else if ( $column_name == 'eyecatch' ) {
        $cf_eyecatch = get_post_meta( $post_id, 'eyecatch', true );
        $img = get_the_post_thumbnail($cf_eyecatch, 'small', array( 'style'=>'width:100px;height:auto;' ));
        if( ! empty($img)){
            echo $img;
        }
        else {
            echo __('None');
        }
    }

}
add_filter( 'manage_news_posts_columns', 'add_posts_columns' );
add_action( 'manage_news_posts_custom_column', 'custom_posts_column', 10, 2 );
  