<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/models/User.php';

/**
 * User repository : functions to manage users in the database.
 */

function getUserById(int $id): ?User {
    $pdo = Database::getPdo();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        return null;
    }
    return new User(
        (int)$row['id'],
        $row['name'],
        (float)$row['last_color_harmony_importance'],
        (float)$row['last_season_harmony_importance']
    );
}
