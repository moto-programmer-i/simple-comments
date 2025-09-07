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
          // Javascriptと連携。参考
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
              'post_url' => plugin_dir_url(__FILE__) . 'includes/simple-comments-post.php',
              'nonce'    => $nonce,
            )
          );
        }
      );
    }

    const SCRIPT_NAME = 'simple-comments-main';
  }
 
  new SimpleComments_Plugin();
}