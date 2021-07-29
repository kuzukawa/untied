<?php
/**
 * WordPress の基本設定
 *
 * このファイルは、インストール時に wp-config.php 作成ウィザードが利用します。
 * ウィザードを介さずにこのファイルを "wp-config.php" という名前でコピーして
 * 直接編集して値を入力してもかまいません。
 *
 * このファイルは、以下の設定を含みます。
 *
 * * MySQL 設定
 * * 秘密鍵
 * * データベーステーブル接頭辞
 * * ABSPATH
 *
 * @link https://ja.wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// 注意:
// Windows の "メモ帳" でこのファイルを編集しないでください !
// 問題なく使えるテキストエディタ
// (http://wpdocs.osdn.jp/%E7%94%A8%E8%AA%9E%E9%9B%86#.E3.83.86.E3.82.AD.E3.82.B9.E3.83.88.E3.82.A8.E3.83.87.E3.82.A3.E3.82.BF 参照)
// を使用し、必ず UTF-8 の BOM なし (UTF-8N) で保存してください。

// ** MySQL 設定 - この情報はホスティング先から入手してください。 ** //
/** WordPress のためのデータベース名 */
define( 'DB_NAME', 'wordpress' );

/** MySQL データベースのユーザー名 */
define( 'DB_USER', 'wordpress' );

/** MySQL データベースのパスワード */
define( 'DB_PASSWORD', 'password' );

/** MySQL のホスト名 */
define( 'DB_HOST', 'wordpress-web.cd3nqb3uj58c.ap-northeast-1.rds.amazonaws.com' );

/** データベースのテーブルを作成する際のデータベースの文字セット */
define( 'DB_CHARSET', 'utf8mb4' );

/** データベースの照合順序 (ほとんどの場合変更する必要はありません) */
define( 'DB_COLLATE', '' );

/**#@+
 * 認証用ユニークキー
 *
 * それぞれを異なるユニーク (一意) な文字列に変更してください。
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org の秘密鍵サービス} で自動生成することもできます。
 * 後でいつでも変更して、既存のすべての cookie を無効にできます。これにより、すべてのユーザーを強制的に再ログインさせることになります。
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'kecBY}35d)8v_1b#1pfU[?__1KT.)3;5DSWXhy1JWSf#,u`S?+gqK8Tfey}[)`YK' );
define( 'SECURE_AUTH_KEY',  'holG!eI7xDo^,9o($>nl,#Mj jETBv5,-I=p/6cuy`%4?~k2cUF5^tM=R>m!VvD*' );
define( 'LOGGED_IN_KEY',    'x(@]m1QYuym7}E{v2yR9&m`|qk@8e^-a^O#9o8t.p@)xy%==xQSJCmoxr6G3t`NW' );
define( 'NONCE_KEY',        '.721r^yU^jnKg ?VwJ2R?3r`SSG3EQj0d/IWUw,dwIboP6qaE<15}TB*]^7SOJcU' );
define( 'AUTH_SALT',        '~d9kA&msG+2e3uh_<~yX*(`&KWgf(!6^<[NI{-Hp_]qr_M [qY|,FXHJ#Pr-ADFg' );
define( 'SECURE_AUTH_SALT', '*T~LI@J60XaFZ57$vnX$9%faEw?H:ebfS.mjc-*}),&j|{8U;F|eG?.,`2#a(LlQ' );
define( 'LOGGED_IN_SALT',   'z9l%t3Z+{(kci!{X:z8ut{`IliZpx@({/ v@J{^Gym+])m&}v|Jq]2 jg?9SQy)B' );
define( 'NONCE_SALT',       ']hS:e(gj+mDF.Q9>1`8iBf<dWLjy5<58J0qO+g$[X@?=={jJYkkKe;M;>/F[BMCO' );

/**#@-*/

/**
 * WordPress データベーステーブルの接頭辞
 *
 * それぞれにユニーク (一意) な接頭辞を与えることで一つのデータベースに複数の WordPress を
 * インストールすることができます。半角英数字と下線のみを使用してください。
 */
$table_prefix = 'wp_';

/**
 * 開発者へ: WordPress デバッグモード
 *
 * この値を true にすると、開発中に注意 (notice) を表示します。
 * テーマおよびプラグインの開発者には、その開発環境においてこの WP_DEBUG を使用することを強く推奨します。
 *
 * その他のデバッグに利用できる定数についてはドキュメンテーションをご覧ください。
 *
 * @link https://ja.wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* カスタム値は、この行と「編集が必要なのはここまでです」の行の間に追加してください。 */

define( 'AS3CF_SETTINGS', serialize( array(
    'provider' => 'aws',
    'access-key-id' => 'AKIAVSMWDZNLNBWWAQHB',
    'secret-access-key' => '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',
) ) );

/* 編集が必要なのはここまでです ! WordPress でのパブリッシングをお楽しみください。 */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';