<?php
// 直接URLを入力してのアクセスを防ぐ
if(!defined('ABSPATH')) { exit; }

// 定数クラス。横着してここに全部書く。
if (!class_exists( 'SimpleComments_Constants' ) ) {
  class SimpleComments_Constants {
    // simple_commentsテーブル
    const ID = 'id';
    const POST_ID = 'post_id';
    const AUTHOR_IP = 'author_ip';
    const DATE_UTC = 'date_utc';
    const CONTENT = 'content';
    const AGENT = 'agent';
    const PARENT = 'parent';


    // nonceテーブル
    // const AUTHOR_IP = 'author_ip';
    const NONCE = 'nonce';
    // const CONTENT = 'content';

    // 記事以外の場合のコメントを考えていなかった。
    // 0: お問い合わせ
    // 1: プライバシーポリシー
    // 10以上: 記事
    // で横着な対応をする。（よい子は真似しないこと）
    const POST_ID_CONTACT = 0;
    const POST_ID_PRIVACY_POLICY = 1;

    // wp_postテーブル（Wordpress既存）
    const POST_DATE = 'post_date';
    const POST_DATE_GMT = 'post_date_gmt';
    const POST_CONTENT = 'post_content';
    const POST_TITLE = 'post_title';
  }
}