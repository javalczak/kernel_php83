<?php
declare(strict_types=1);

namespace Engine;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        $dsn = sprintf(
            'mysql:host=%s;%scharset=%s',
            $config['host'],
            !empty($config['dbname']) ? 'dbname=' . $config['dbname'] . ';' : '',
            $config['charset'] ?? 'utf8mb4'
        );

        try {
            $this->pdo = new PDO($dsn, $config['user'], $config['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException('Błąd połączenia z bazą: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Podstawowe operacje
    // -------------------------------------------------------------------------

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    public function fetchColumn(string $sql, array $params = []): mixed
    {
        return $this->query($sql, $params)->fetchColumn();
    }

    // -------------------------------------------------------------------------
    // Insert / Update / Delete
    // -------------------------------------------------------------------------

    public function insert(string $table, array $data): int
    {
        $columns      = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $this->query(
            "INSERT INTO $table ($columns) VALUES ($placeholders)",
            array_values($data)
        );

        return (int) $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, array $where): int
    {
        $set = implode(', ', array_map(
            fn($col) => "$col = ?",
            array_keys($data)
        ));

        $whereClause = implode(' AND ', array_map(
            fn($col) => "$col = ?",
            array_keys($where)
        ));

        $params = [...array_values($data), ...array_values($where)];

        return $this->query(
            "UPDATE $table SET $set WHERE $whereClause",
            $params
        )->rowCount();
    }

    public function delete(string $table, array $where): int
    {
        $whereClause = implode(' AND ', array_map(
            fn($col) => "$col = ?",
            array_keys($where)
        ));

        return $this->query(
            "DELETE FROM $table WHERE $whereClause",
            array_values($where)
        )->rowCount();
    }

    // -------------------------------------------------------------------------
    // Transakcje
    // -------------------------------------------------------------------------

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function exists(string $table, array $where): bool
    {
        $whereClause = implode(' AND ', array_map(
            fn($col) => "$col = ?",
            array_keys($where)
        ));

        return (bool) $this->fetchColumn(
            "SELECT COUNT(*) FROM $table WHERE $whereClause",
            array_values($where)
        );
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}