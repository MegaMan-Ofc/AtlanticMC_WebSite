CREATE DATABASE IF NOT EXISTS atlantic_store_test
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON atlantic_store_test.* TO 'atlantic_user'@'%';
FLUSH PRIVILEGES;
