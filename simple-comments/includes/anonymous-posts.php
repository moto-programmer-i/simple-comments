<?php
// 確認用URL
// http://www.failure4.shop/success-laugh/wp-content/plugins/simple-comments/includes/anonymous-posts.php

// 本当はこの投稿機能はプラグインを分けるべきだが、今回は面倒なのでそのままいく

// フォームの要素の名前
const CONTENT = "content";
const LEARN = "learn";


// Sets up the WordPress Environment.
// https://github.com/WordPress/wordpress-develop/blob/6.8.2/src/wp-comments-post.php
// https://wordpress.stackexchange.com/a/190392
$wp_path = preg_replace( '/wp-content.*$/', '', __DIR__ );
require_once( $wp_path . 'wp-load.php' );

require_once( plugin_dir_path( __FILE__ ) . 'simple-comments-constants.php');
require_once( plugin_dir_path( __FILE__ ) . 'simple-comments-utils.php');
require_once( plugin_dir_path( __FILE__ ) . 'simple-comments-nonce-manager.php');




// 送信時処理
if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
    // echo "準備中"; exit;

    // / が \/ になっているので、バックスラッシュを取り除く
    $postdata = wp_unslash( $_POST );
    
    // 仮
    $ip = "192";

    // パラメータのないURLを作成しておく
    $location = SimpleComments_Utils::get_referer_url();

    // nonceチェック
    SimpleComments_NonceManager::delete_expired();
    SimpleComments_NonceManager::redirect_if_invalid($ip, $location,
        "?restore=true"
    );



    global $wpdb;
    $wpdb->show_errors();

    // 既存カラムpost_contentに合うように整形
    // 例
    /*
<!-- wp:paragraph -->
<p>ポイントカードの期限</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">学びerror</h2>
<!-- /wp:heading -->
*/
    $post_content = "
    <p>". SimpleComments_Utils::sanitize($postdata[CONTENT]) . "</p>
    <h2 class=\"wp-block-heading\">この失敗からの学び</h2>
    <p>". SimpleComments_Utils::sanitize($postdata[LEARN]) . "</p>"
    ;

    // https://developer.wordpress.org/reference/classes/wpdb/insert/
    $result = $wpdb->insert(
        'wp_posts',
        array(
            // https://developer.wordpress.org/reference/functions/current_time/
            // もう面倒なのでUtilは作らない
            SimpleComments_Constants::POST_DATE => current_time("mysql", false),
            SimpleComments_Constants::POST_DATE_GMT => current_time("mysql", true),
            SimpleComments_Constants::POST_CONTENT => $post_content,
            SimpleComments_Constants::POST_TITLE => SimpleComments_Utils::sanitize($postdata[SimpleComments_Constants::POST_TITLE]),
            SimpleComments_Constants::AUTHOR_IP => SimpleComments_Utils::sanitize($ip)
            // agent
        ),
        array('%s', '%s', '%s', '%s', '%s')
    );

    // エラーを出力
    if (!$result ) {
        echo $wpdb->last_error;
        exit;
    }

    // 登録したidを取得
    $id = $wpdb->insert_id;

    wp_safe_redirect("/success-laugh/archives/$id");
    exit;
}

// GETの時はHTMLを表示
?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<!-- Wordpressの機能を使ってCSSを適用するのが推奨らしいが、面倒なのでこれで -->
    <link rel="stylesheet" id="cocoon-style-css" href="/success-laugh/wp-content/themes/cocoon-master/style.css?ver=6.8.2&amp;fver=20250901031631" media="all">
	<title>失敗を投稿</title>
	<style type="text/css">
		* {
			font-family: "Hiragino Kaku Gothic ProN", "Hiragino Sans", Meiryo, sans-serif;
		}
		body {
			background-color: var(--cocoon-white-color);
		}
		form {
			display: flex;
			flex-direction: column;
			row-gap: 20px;
			justify-content: center;
			align-items: center;
		}
		input {
			width: 100%;
			font-size: xx-large;
		}
		textarea {
			font-size: xx-large;
			width: 100%;
		}
		.title {
			font-size: xxx-large;
			font-weight: bold;
		}
		.content {
			height: 70vh;
		}
		.submit-area {
			width: 90%;
		}
	</style>
	<link rel="icon" href="/success-laugh/wp-content/uploads/2025/09/%E5%A4%B1%E6%95%97-150x150.jpg" sizes="32x32">
	<link rel="icon" href="/success-laugh/wp-content/uploads/2025/09/%E5%A4%B1%E6%95%97-300x300.jpg" sizes="192x192">
	<meta name="msapplication-TileImage" content="/success-laugh/wp-content/uploads/2025/09/失敗-300x300.jpg">

</head>
	<body>
		<form method="POST" action="" onSubmit="return onSubmit();">
            <input id="post_title" name="post_title" class="title" required placeholder="タイトル">
			<textarea id="content" name="content" class="content" required placeholder="失敗した話"></textarea>
			<textarea id="learn" name="learn" rows="3" required placeholder="・学び（箇条書き推奨）"></textarea>
            <input type="hidden" name="nonce" value="<?php echo SimpleComments_NonceManager::create_nonce('192');?>">
			<div class="submit-area">
				<input type="submit" class="submit" value="失敗を投稿">
			</div>
        </form>
	</body>
	<script>
		// インラインだとdeferが効かないらしい
		// もう面倒なのでここに書く
		
		// 2回書くのが面倒だが、対応策不明
		const POST_TITLE = "post_title";
		const CONTENT = "content";
		const LEARN = "learn";
		
		// nonceエラー時の復元
		const params = new URLSearchParams(window.location.search);
		if(params.get("restore")) {
			document.getElementById(POST_TITLE).value = sessionStorage.getItem(POST_TITLE);	
			document.getElementById(CONTENT).value = sessionStorage.getItem(CONTENT);
			document.getElementById(LEARN).value = sessionStorage.getItem(LEARN);	
		}
		
		
        function onSubmit() {
			// nonceエラーに備えて保存
			sessionStorage.setItem(POST_TITLE, document.getElementById(POST_TITLE).value);
			sessionStorage.setItem(CONTENT, document.getElementById(CONTENT).value);
			sessionStorage.setItem(LEARN, document.getElementById(LEARN).value);
			
            return confirm('投稿後の変更はできません。よろしいですか？');
        }
    </script>
</html>