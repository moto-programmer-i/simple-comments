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

// SimpleComments_NonceManagerの読み込み
require_once( plugin_dir_path( __FILE__ ) . 'simple-comments-nonce-manager.php');


nocache_headers();

// 仮
$ip = "192";

// nonceチェック
SimpleComments_NonceManager::delete_expired();
if (is_null(SimpleComments_NonceManager::get_nonce($ip))) {
	// 403 Forbidden
	$waitSeconds = 1;

	// パラメータを付け直す
	$location = preg_replace( '/\?.*$/', '', $_SERVER['HTTP_REFERER']);
	header("Location: " . $location, true, 403);
	echo "申し訳ありませんが、ページの有効期限が切れました。もう1度お試しください。
		<br>
		${waitSeconds}秒後に自動的にリダイレクトされます。";

	echo "
		<script>
			setTimeout(() => {
				window.location = '". $location . "?comment=" . $_POST['content']. "';
			}, ${waitSeconds}000);
			
		</script>
		";
	exit;
}

echo "ok";
exit;


echo '受信： ' . $_POST['nonce'];
// staticでも値の受け渡しができない


exit;


// POSTされたデータは$_POSTに連想配列で入ってる
// echo json_encode($_POST, JSON_PRETTY_PRINT);


// / が \/ になっているので、バックスラッシュを取り除く
$postdata = wp_unslash( $_POST );

global $wpdb;

$wpdb->show_errors();

// https://developer.wordpress.org/reference/classes/wpdb/insert/
$result = $wpdb->insert(
	'simple_comments',
	array(
        'post_id' => 0,
		// サニタイズが必要
        'content' => 'サニタイズが必要',// $postdata['content'],
		// author_ip
		'parent_id' => $postdata['parent_id'],
    ),
	array( '%d', '%s', '%d')
);

// エラーを出力
if (!$result ) {
	echo $wpdb->last_error;
	exit;
}

// 新しいID
echo $wpdb->insert_id;

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


// $user            = wp_get_current_user();
// $cookies_consent = ( isset( $_POST['wp-comment-cookies-consent'] ) );

/**
 * Fires after comment cookies are set.
 *
 * @since 3.4.0
 * @since 4.9.6 The `$cookies_consent` parameter was added.
 *
 * @param WP_Comment $comment         Comment object.
 * @param WP_User    $user            Comment author's user object. The user may not exist.
 * @param bool       $cookies_consent Comment author's consent to store cookies.
 */
// do_action( 'set_comment_cookies', $comment, $user, $cookies_consent );

// $location = empty( $_POST['redirect_to'] ) ? get_comment_link( $comment ) : $_POST['redirect_to'] . '#comment-' . $comment->comment_ID;

/*
// If user didn't consent to cookies, add specific query arguments to display the awaiting moderation message.
if ( ! $cookies_consent && 'unapproved' === wp_get_comment_status( $comment ) && ! empty( $comment->comment_author_email ) ) {
	$location = add_query_arg(
		array(
			'unapproved'      => $comment->comment_ID,
			'moderation-hash' => wp_hash( $comment->comment_date_gmt ),
		),
		$location
	);
}
*/

/**
 * Filters the location URI to send the commenter after posting.
 *
 * @since 2.0.5
 *
 * @param string     $location The 'redirect_to' URI sent via $_POST.
 * @param WP_Comment $comment  Comment object.
 */
// $location = apply_filters( 'comment_post_redirect', $location, $comment );

// wp_safe_redirect( $location );
exit;
