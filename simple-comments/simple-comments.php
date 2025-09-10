<?php
/*
 * Plugin Name: Simple Comments
 * Description: 返信のみのシンプルなコメントプラグイン
 * Author: 元プログラマi
 * License: WTFPL
 * Version: 1.0.0
 */
if(!defined('ABSPATH')) { exit; } // 直接URLを入力してのアクセスを防ぐ

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once( plugin_dir_path( __FILE__ ) . '/includes/simple-comments-nonce-manager.php');

if (!class_exists( 'SimpleComments_Plugin' ) ) {
  class SimpleComments_Plugin {
    function __construct() {
      
      add_filter(
        // イベントのタイミング
        // https://developer.wordpress.org/apis/hooks/filter-reference/
        'the_content',
        
        function() {
          // Javascriptと連携
          // https://ja.wordpress.org/team/handbook/plugin-development/javascript/summary/
          wp_enqueue_script(
            self::SCRIPT_NAME,
            plugins_url('public/js/simple-comments-main.js', __FILE__ ),
            // An array of registered script handles this script depends on.
            array(),
            // ver 後でキャッシュのために追加
            null,
            array(
              'strategy' => 'defer',
            )
          );

          // CSSと連携
          // https://developer.wordpress.org/reference/functions/wp_enqueue_style/
          wp_enqueue_style(
            self::CSS_NAME,
            plugins_url('public/css/simple-comments.css', __FILE__ ),
          );
          

          // nonceの種はランダムでなければならない
          // https://developer.wordpress.org/reference/functions/wp_create_nonce/
           $nonce = SimpleComments_NonceManager::create_nonce(
            // 仮
            '192'
           );
          
          // PHPではstaticの値は保存されない
          // SimpleComments_NonceManager::$STATE = 192;
          // $simple_comment_nonce_map['192'] = $nonce;

          wp_localize_script(
            self::SCRIPT_NAME,
            'objFromPHP',
            array(
              'postUrl' => plugin_dir_url(__FILE__) . 'includes/simple-comments-post.php',
              'nonce'    => $nonce,

              // ajax通信時はこのURLを使わなければいけないらしい（理由は不明）
              // https://ja.wordpress.org/team/handbook/plugin-development/javascript/ajax/#url
              'getCommentUrl' => admin_url( "admin-ajax.php?action=" . self::COMMENT_ACTION_NAME ),
            )
          );
        }
      );

      $get_comments = function () {
        $post_id = $_GET["post_id"];
        if (is_numeric($post_id)) {
          $post_id = intval($post_id);
        }
        else {
          wp_die( "post_idが不正です。post_id:$post_id", 400 );
        }
        
        global $wpdb;
        $wpdb->show_errors();

        // https://developer.wordpress.org/reference/classes/wpdb/get_results/
        $results = $wpdb->get_results($wpdb->prepare(
          "SELECT id, content, parent FROM simple_comments WHERE 
            post_id = %d",
            // ORDER BYは横着してやらない（通常いらないはず）
          array($post_id)));

        // エラーを出力
        if (!$results ) {
          echo $wpdb->last_error;
          exit;
        }

        echo json_encode($results);


        // all ajax handlers should die when finished
        wp_die();
      };

      // ログイン用と非ログイン用でそれぞれ登録しなければいけない
      // https://developer.wordpress.org/reference/hooks/wp_ajax_action/
      add_action( "wp_ajax_" . self::COMMENT_ACTION_NAME,  $get_comments);
      add_action( "wp_ajax_nopriv_" . self::COMMENT_ACTION_NAME, $get_comments);
    }

    const SCRIPT_NAME = 'simple-comments-main';
    const COMMENT_ACTION_NAME = 'simple_comments';
    const CSS_NAME = 'simple-comments-css';
  }
 
  new SimpleComments_Plugin();
}