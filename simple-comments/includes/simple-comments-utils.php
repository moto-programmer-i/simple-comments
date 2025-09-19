<?php
// 直接URLを入力してのアクセスを防ぐ
if(!defined('ABSPATH')) { exit; }


if (!class_exists( 'SimpleComments_Utils' ) ) {
  class SimpleComments_Utils {
    // 許可するHTMLタグ
    // https://developer.wordpress.org/reference/functions/wp_kses/
    const ALLOWED_HTML = array(
        'br' => array()
    );
    const USER_AGENT_MAX_LENGTH = 254;

    static function sanitize(string $str) {
      if (empty($str)) {
        return '';
      }
		
      // 指定したタグ以外は削除
      $str = wp_kses($str, self::ALLOWED_HTML);

      // 4連続以上の改行が無視されてしまうので、4つ目からは&nbsp;も挿入
      return preg_replace_callback(
        "/(\r?\n){3}((\r?\n)+)/",
        function ($matches) {
          return $matches[0] . preg_replace("/(\r?\n)/","&nbsp;$1",$matches[2]);
        },
        $str);
    }

    /**
     * パラメータのないURLを作成
     */
    static function get_referer_url() {
      return preg_replace( '/\?.*$/', '', $_SERVER['HTTP_REFERER']);
    }

    static function current_db_date() {
      // https://developer.wordpress.org/reference/functions/current_time/
      return current_time("mysql", false);
    }

    static function current_db_date_gmt() {
      // https://developer.wordpress.org/reference/functions/current_time/
      return current_time("mysql", true);
    }


    static function get_ip_address() {
      return $_SERVER['REMOTE_ADDR'];
    }

    static function get_user_agent() {
      // DBの長さ制限に合わせて短くする
      return substr( $_SERVER['HTTP_USER_AGENT'], 0, self::USER_AGENT_MAX_LENGTH);
    }
  }
}