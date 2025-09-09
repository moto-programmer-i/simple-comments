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
    static function sanitize(string $str) {
        // 指定したタグ以外は削除
        return wp_kses($str, self::ALLOWED_HTML);
    }
  }
}