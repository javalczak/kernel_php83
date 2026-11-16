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
                // Bez emulacji: zapytanie i dane idą do serwera osobno, więc wartość
                // nigdy nie staje się częścią SQL-a. To jest cała obrona przed SQL injection.
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // Komunikat PDO potrafi zawierać login i nazwę bazy — na produkcji
            // ma to iść do logu, nie na ekran użytkownika.
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

        return $result ?: null; // false z PDO zamieniamy na null
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
        $kolumny = implode(', ', array_map($this->identifier(...), array_keys($data)));
        $znaki   = implode(', ', array_fill(0, count($data), '?'));

        $this->query(
            'INSERT INTO ' . $this->identifier($table) . " ($kolumny) VALUES ($znaki)",
            array_values($data)
        );

        return (int) $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, array $where): int
    {
        if ($where === []) {
            throw new \InvalidArgumentException('UPDATE bez WHERE zmieniłby całą tabelę.');
        }

        $set = implode(', ', array_map(fn (string $col): string => $this->identifier($col) . ' = ?', array_keys($data)));

        return $this->query(
            'UPDATE ' . $this->identifier($table) . " SET $set WHERE " . $this->whereClause($where),
            [...array_values($data), ...array_values($where)]
        )->rowCount();
    }

    public function delete(string $table, array $where): int
    {
        if ($where === []) {
            throw new \InvalidArgumentException('DELETE bez WHERE wyczyściłby całą tabelę.');
        }

        return $this->query(
            'DELETE FROM ' . $this->identifier($table) . ' WHERE ' . $this->whereClause($where),
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

    /**
     * Wszystko albo nic: wyjątek w callbacku cofa zmiany i leci dalej.
     * Używaj, gdy zapis dotyczy więcej niż jednej tabeli.
     */
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
    // Pomocnicze
    // -------------------------------------------------------------------------

    public function exists(string $table, array $where): bool
    {
        return $this->count($table, $where) > 0;
    }

    public function count(string $table, array $where = []): int
    {
        $sql = 'SELECT COUNT(*) FROM ' . $this->identifier($table);

        if ($where !== []) {
            $sql .= ' WHERE ' . $this->whereClause($where);
        }

        return (int) $this->fetchColumn($sql, array_values($where));
    }

    /**
     * Nazwa tabeli albo kolumny w SQL-u. Tego miejsca NIE da się sparametryzować
     * przez PDO, więc jedyne zabezpieczenie to biała lista znaków + backticki.
     * Nazwa przychodzi od programisty, ale raz wystarczy wpuścić tu $_GET i jest po bazie.
     */
    public function identifier(string $name): string
    {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name) !== 1) {
            throw new \InvalidArgumentException("Niedozwolona nazwa w SQL: {$name}");
        }

        return '`' . $name . '`';
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /** Warunki łączone przez AND, wartości zawsze jako ? */
    private function whereClause(array $where): string
    {
        return implode(' AND ', array_map(
            fn (string $col): string => $this->identifier($col) . ' = ?',
            array_keys($where)
        ));
    }
}
