<?php
/**
 * Handles Comment Post to WordPress and prevents duplicate comment posting.
 *
 * @package Simple Comment
 */
// エラーログ
// https://www.onamae.com/column/wordpress/4/
ini_set('display_errors', 1);
error_reporting(E_ALL);

// echo "準備中";exit;

if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
	$protocol = $_SERVER['SERVER_PROTOCOL'];
	if ( ! in_array( $protocol, array( 'HTTP/1.1', 'HTTP/2', 'HTTP/2.0', 'HTTP/3' ), true ) ) {
		$protocol = 'HTTP/1.0';
	}

	header( 'Allow: POST' );
	header( "$protocol 405 Method Not Allowed" );
	header( 'Content-Type: text/plain' );
	exit;
}



// Sets up the WordPress Environment.
// https://github.com/WordPress/wordpress-develop/blob/6.8.2/src/wp-comments-post.php
// https://wordpress.stackexchange.com/a/190392
$wp_path = preg_replace( '/wp-content.*$/', '', __DIR__ );
require_once( $wp_path . 'wp-load.php' );

require_once( plugin_dir_path( __FILE__ ) . 'simple-comments-constants.php');
require_once( plugin_dir_path( __FILE__ ) . 'simple-comments-utils.php');
require_once( plugin_dir_path( __FILE__ ) . 'simple-comments-nonce-manager.php');



nocache_headers();

// 仮
// $ip = "192";
$ip = SimpleComments_Utils::get_ip_address();

// POSTされたデータは$_POSTに連想配列で入ってる
// echo json_encode($_POST, JSON_PRETTY_PRINT);

// / が \/ になっているので、バックスラッシュを取り除く
$postdata = wp_unslash( $_POST );

// パラメータのないURLを作成しておく
$location = SimpleComments_Utils::get_referer_url();

// nonceチェック
SimpleComments_NonceManager::delete_expired();
SimpleComments_NonceManager::redirect_if_invalid($ip, $location, "?restore=true");

// echo '受信： ' . $_POST['nonce'];
// staticでも値の受け渡しができない


global $wpdb;
$wpdb->show_errors();




// URL例
// http://www.failure4.shop/success-laugh/contact
$post_id = SimpleComments_Constants::POST_ID_CONTACT;
// http://www.failure4.shop/success-laugh/privacy-policy
if (str_contains($_SERVER['HTTP_REFERER'], "privacy-policy")) {
    $post_id = SimpleComments_Constants::POST_ID_PRIVACY_POLICY;
}
else {
	// http://www.failure4.shop/success-laugh/archives/1
	$post_id = preg_replace( '/.*archives\/(\d+).*/', '$1', $_SERVER['HTTP_REFERER']);
	if (is_numeric($post_id)) {
		$post_id = intval($post_id);
	}
	else {
		echo "post_idが不正です。<br>
		post_id: $post_id
		";
		exit;
	}
}

// parentが送信されなかった場合、空文字扱いになのでNULLに変換が必要
if (empty($postdata[SimpleComments_Constants::PARENT])) {
	$postdata[SimpleComments_Constants::PARENT] = NULL;
}



// https://developer.wordpress.org/reference/classes/wpdb/insert/
$result = $wpdb->insert(
	'simple_comments',
	array(
        SimpleComments_Constants::POST_ID => $post_id,
		SimpleComments_Constants::AUTHOR_IP => $ip,
        SimpleComments_Constants::CONTENT => SimpleComments_Utils::sanitize($postdata[SimpleComments_Constants::CONTENT]),
		SimpleComments_Constants::AGENT => SimpleComments_Utils::get_user_agent(),
		SimpleComments_Constants::PARENT => $postdata[SimpleComments_Constants::PARENT],
    ),
	array( '%d', '%s', '%s', '%d')
);

// エラーを出力
if (!$result ) {
	echo $wpdb->last_error;
	exit;
}

wp_safe_redirect($location);


exit;

/*
$comment = wp_handle_comment_submission( wp_unslash( $_POST ) );
if ( is_wp_error( $comment ) ) {
	$data = (int) $comment->get_error_data();
	if ( ! empty( $data ) ) {
		wp_die(
			'<p>' . $comment->get_error_message() . '</p>',
			__( 'Comment Submission Failure' ),
			array(
				'response'  => $data,
				'back_link' => true,
			)
		);
	} else {
		exit;
	}
}
*/



// wp_safe_redirect( $location );
exit;
