-- 事前にこのSQLを実行してテーブルを作る必要がある

START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- テーブルの構造 `simple_comments`
-- 日時はUTC、日本（UTC+09:00）との違いに注意
CREATE TABLE `nonce` (
  `author_ip` varchar(100) NOT NULL DEFAULT '',
  `nonce` bigint(20) NOT NULL DEFAULT 0,
  `create_utc` datetime NOT NULL DEFAULT UTC_TIMESTAMP(),
    PRIMARY KEY (`author_ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- テーブルのインデックス `wp_comments`
--
ALTER TABLE `nonce`
  ADD INDEX `create_utc` (`create_utc`);

COMMIT;

