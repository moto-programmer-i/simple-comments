-- 非ログインユーザーに投稿を作らせるため、
-- IPアドレスとユーザーエージェント追加
START TRANSACTION;

ALTER TABLE `wp_posts` ADD COLUMN author_ip varchar(100) NOT NULL DEFAULT '';
ALTER TABLE `wp_posts` ADD COLUMN agent varchar(255) NOT NULL DEFAULT '';

COMMIT;