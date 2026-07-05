<?php
declare(strict_types=1);

namespace App\Repository;

use Engine\Database;
use App\Schema\UserSchema;
use App\Schema\PropertySchema;

final class UserRepository
{
    private string $table = UserSchema::TABLE;

    public function __construct(
        private Database $db
    ) {}

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetchOne("SELECT * FROM {$this->table} WHERE email = ?", [$email]);
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }

    /**
     * Used by the admin "add property for a client" flow: reuse the existing
     * account if the email is already known, otherwise create a bare record
     * (no password yet — the client can't log in until /user exists).
     */
    public function findOrCreate(string $email, string $name): int
    {
        $existing = $this->findByEmail($email);
        if ($existing !== null) {
            return (int)$existing['id'];
        }

        return $this->db->insert($this->table, [
            'email' => $email,
            'name'  => $name,
            'role'  => 'host',
        ]);
    }

    /**
     * The public wizard's "finalize" step — this is the real self-service
     * account claim, unlike findOrCreate() above (admin-created hosts get
     * no password until they go through this themselves).
     */
    public function claimWithPassword(string $email, string $name, string $password): int
    {
        $existing = $this->findByEmail($email);
        $hash     = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        if ($existing !== null) {
            $this->db->update(
                $this->table,
                ['name' => $name, 'password_hash' => $hash],
                ['id' => $existing['id']]
            );
            return (int)$existing['id'];
        }

        return $this->db->insert($this->table, [
            'email'         => $email,
            'name'          => $name,
            'password_hash' => $hash,
            'role'          => 'host',
        ]);
    }

    /**
     * Standalone self-service signup (/register) — unlike claimWithPassword()
     * this isn't tied to a wizard draft, just "I want a host account".
     * Caller must check findByEmail() first; this always inserts.
     */
    public function register(string $email, string $name, string $password): int
    {
        return $this->db->insert($this->table, [
            'email'         => $email,
            'name'          => $name,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            'role'          => 'host',
        ]);
    }

    /**
     * Timing-safe login check — verifies against a fixed dummy hash when the
     * email isn't found, so response time doesn't leak whether an account
     * exists (same pattern as the admin login).
     */
    public function verifyPassword(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        $hash = $user['password_hash'] ?? '$2y$12$BsMFw3Qo8dl37furv0ECJOp0LSn3wh8NRXuIRgcAtSQkV3Zjos19K';

        if (!password_verify($password, $hash)) {
            return null;
        }

        return $user;
    }

    public function countProperties(int $userId): int
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM " . PropertySchema::TABLE . " WHERE user_id = ?",
            [$userId]
        );
    }

    /**
     * Admin-only knobs for the account-level pricing rules — see
     * App\Service\PricingCalculator.
     */
    public function setPricingFlags(int $userId, bool $isBulkAccount, ?int $overridePercent): void
    {
        $this->db->update(
            $this->table,
            ['is_bulk_account' => $isBulkAccount ? 1 : 0, 'discount_override_percent' => $overridePercent],
            ['id' => $userId]
        );
    }

    public function findAllHosts(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE role = 'host' ORDER BY created_at DESC"
        );
    }
}
