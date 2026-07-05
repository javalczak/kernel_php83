<?php
declare(strict_types=1);

namespace App\Schema;

final class UserSchema
{
    public const string TABLE = 'users';

    public static function createTableSql(): string
    {
        return "
            CREATE TABLE IF NOT EXISTS " . self::TABLE . " (
                id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                email          VARCHAR(190) NOT NULL,
                name           VARCHAR(150) NOT NULL,
                password_hash  VARCHAR(255) NULL,
                role           ENUM('host','guest') NOT NULL DEFAULT 'host',
                is_active      TINYINT(1) NOT NULL DEFAULT 1,
                is_bulk_account            TINYINT(1) NOT NULL DEFAULT 0,
                discount_override_percent  TINYINT UNSIGNED NULL,
                created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_users_email (email)
            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci
        ";
    }

    public static function dropTableSql(): string
    {
        return "DROP TABLE IF EXISTS " . self::TABLE;
    }
}
