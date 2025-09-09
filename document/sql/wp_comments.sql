-- 事前にこのSQLを実行してテーブルを作る必要がある

START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- テーブルの構造 `simple_comments`
-- 日時はUTC、日本（UTC+09:00）との違いに注意
CREATE TABLE `simple_comments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `author_ip` varchar(100) NOT NULL DEFAULT '',
  `date_utc` datetime NOT NULL DEFAULT UTC_TIMESTAMP(),
  `content` text NOT NULL,
  `agent` varchar(255) NOT NULL DEFAULT '',
  `parent` bigint(20) UNSIGNED,
    PRIMARY KEY (`id`, `post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- テーブルのインデックス `wp_comments`
--
ALTER TABLE `simple_comments`
  ADD INDEX `post_id` (`post_id`),
  ADD INDEX `date_utc` (`date_utc`);

COMMIT;

