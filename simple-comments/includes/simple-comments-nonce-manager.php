<?php
// 直接URLを入力してのアクセスを防ぐ
if(!defined('ABSPATH')) { exit; }

if (!class_exists( 'SimpleComments_NonceManager' ) ) {
  class SimpleComments_NonceManager {
    // nonceの有効期限。wordpressのものと合わせた
    // https://developer.wordpress.org/apis/security/nonces/
    const LIFETIME_HOURS = 12;
    const DATETIME_FORMAT = 'Y-m-d H:i:s';
    
    static public function create_expires_datetime() {
      return (new DateTime('now', new DateTimeZone('UTC')))->sub(DateInterval::createFromDateString(self::LIFETIME_HOURS . ' hours'));
    }
    // DBの文字列形式で期限切れ日時を返す
    // https://mariadb.com/docs/server/reference/data-types/date-and-time-data-types/datetime
    static public function create_expires_datetime_str() {
      return self::create_expires_datetime()->format(self::DATETIME_FORMAT);
    }

    /**
     * @return 有効期限内のnonce、なければNULL
     */
    static public function get_nonce(string $ip) {
      // https://developer.wordpress.org/reference/classes/wpdb/#select-a-variable
      global $wpdb;
      $wpdb->show_errors();
      return $wpdb->get_var($wpdb->prepare(
        'SELECT nonce FROM nonce WHERE author_ip= %s AND create_utc >= %s',
        array($ip, self::create_expires_datetime_str())));
    }

    static public function create_nonce(string $ip) {
      global $wpdb;
      $wpdb->show_errors();

      // 作成 or 更新
      $nonce = random_int(PHP_INT_MIN, PHP_INT_MAX);
      $wpdb->query($wpdb->prepare(
        "INSERT INTO nonce(author_ip, nonce) VALUES (%s, %d) 
            ON DUPLICATE KEY UPDATE nonce = %d, create_utc=UTC_TIMESTAMP()",
        array($ip, $nonce, $nonce)));
      return $nonce;
    }

    /**
     * 有効期限切れのnonceを削除
     */
    static public function delete_expired() {
      global $wpdb;
      $wpdb->show_errors();
      $wpdb->query($wpdb->prepare(
        "DELETE FROM nonce WHERE create_utc < %s", self::create_expires_datetime_str()
      ));
    }
    

    /*
    static public function remove(string $ip) {
      if(!array_key_exists($ip, self::$MAP)) {
          return null;
      }
      $nonce = self::$MAP[$ip];
      unset(self::$MAP[$ip]);
      return $nonce;
    }*/
    
    static public function redirect_if_invalid(string $ip, string $location, string $parameter = "") {
      $nonce = $_POST[SimpleComments_Constants::NONCE];
      $nonce_DB = SimpleComments_NonceManager::get_nonce($ip);
      if (is_null($nonce) || $nonce != $nonce_DB) {
        // 403 Forbidden
        $waitSeconds = 1;

        header("Location: " . $location, true, 403);
        echo "申し訳ありませんが、ページの有効期限が切れました。もう1度お試しください。
          <br>
          {$waitSeconds}秒後に自動的にリダイレクトされます。";

        echo "
          <script>
            setTimeout(() => {
              window.location = '$location" . "$parameter';
            }, {$waitSeconds}000);
            
          </script>
          ";
        exit;
      }
    }
  }
}
