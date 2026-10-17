<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/models/Item.php';

/**
 * Item repository : functions to manage items in the database.
 */

function getItemById(int $id): ?Item {
    $pdo = Database::getPdo();
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        return null;
    }
    return new Item(
        (int)$row['id'],
        $row['category'],
        (float)$row['hue'],
        (float)$row['saturation'],
        (float)$row['brightness'],
        (int)$row['season_id'],
        (int)$row['user_rate'],
        $row['img_url']
    );
}

function getItemsCountByUserId(int $userId): int {
    $pdo = Database::getPdo();
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM clothes WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    $row = $stmt->fetch();

    return (int)$row['count'];
}

function getItemsByCategoryAndUserId(string $category, int $userId): array {
    $pdo = Database::getPdo();
    if ($category !== '') {
        $stmt = $pdo->prepare("SELECT * FROM clothes WHERE category = :category AND user_id = :user_id");
        $stmt->execute(['category' => $category, 'user_id' => $userId]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM clothes WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
    }
    $rows = $stmt->fetchAll();

    $items = [];
    foreach ($rows as $row) {
        $items[] = new Item(
            (int)$row['id'],
            $row['category'],
            (float)$row['hue'],
            (float)$row['saturation'],
            (float)$row['brightness'],
            (int)$row['season_id'],
            (int)$row['user_rate'],
            $row['img_url']
        );
    }
    return $items;
}
