<?php
declare(strict_types=1);

namespace App\Schema;

final class PropertyTypeSchema
{
    public const string TABLE = 'property_types';

    public static function createTableSql(): string
    {
        return "
            CREATE TABLE IF NOT EXISTS " . self::TABLE . " (
                id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                code        VARCHAR(60) NOT NULL,
                name        VARCHAR(100) NOT NULL,
                icon        VARCHAR(100) NULL,
                sort_order  INT NOT NULL DEFAULT 0,
                is_active   TINYINT(1) NOT NULL DEFAULT 1,
                created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_property_types_code (code)
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
