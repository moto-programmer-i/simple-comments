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
        // 指定したタグ以外は削除
        return wp_kses($str, self::ALLOWED_HTML);
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