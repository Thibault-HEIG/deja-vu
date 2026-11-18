<?php
require_once __DIR__ . '/Repository.php';
require_once __DIR__ . '/IDelete.php';
require_once __DIR__ . '/../models/User.php';

/**
 * User repository : functions to manage users in the database.
 */
class UserRepository extends Repository implements IDelete {

    #[Override]
    public function getById(int $userId): ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }
        return new User(
            $row['name'],
            (float)$row['last_color_harmony_importance'],
            (float)$row['last_season_harmony_importance'],
            (int)$row['id']
        );
    }

    public function insert(User $user): int {
        // Try to generate the token from the email for reproducability
        $secretToken = bin2hex(random_bytes(16)); // Generate a random secret token
        
        return 0; // Not implemented yet
    }

    public function update(User $user): bool {
        return false; // Not implemented yet
    }

    #[Override]
    public function deleteById(int $userId): bool {
        return false; // Not implemented yet
    }

    public function getSecretToken(int $userId): ?string {
        $stmt = $this->pdo->prepare("SELECT secret_token FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }
        return $row['secret_token'];
    }
}
