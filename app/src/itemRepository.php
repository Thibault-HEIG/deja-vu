<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/models/Item.php';

/**
 * Item repository : functions to manage items in the database.
 */

function getItemById(int $id): ?Item {
    $pdo = Database::getPdo();
    $stmt = $pdo->prepare("SELECT clothes.*, seasons.name AS season_name, seasons.avg_temperature, seasons.icon_url
    FROM clothes
    JOIN seasons ON clothes.season_id = seasons.id
    WHERE clothes.id = :id");
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
        new Season((int)$row['season_id'], $row['season_name'], (int)$row['avg_temperature'], $row['icon_url']),
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
        $stmt = $pdo->prepare("SELECT clothes.*, seasons.name AS season_name, seasons.avg_temperature, seasons.icon_url
    FROM clothes
    JOIN seasons ON clothes.season_id = seasons.id
    WHERE clothes.category = :category AND clothes.user_id = :user_id");
        $stmt->execute(['category' => $category, 'user_id' => $userId]);
    } else {
        $stmt = $pdo->prepare("SELECT clothes.*, seasons.name AS season_name, seasons.avg_temperature, seasons.icon_url
    FROM clothes
    JOIN seasons ON clothes.season_id = seasons.id
    WHERE clothes.user_id = :user_id");
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
            new Season((int)$row['season_id'], $row['season_name'], (int)$row['avg_temperature'], $row['icon_url']),
            (int)$row['user_rate'],
            $row['img_url']
        );
    }
    return $items;
}

/* Ajout du vêtement*/

function getAllSeasons(): array {
    $pdo = Database::getPdo();
    $stmt = $pdo->query("SELECT * FROM seasons ORDER BY id");
    $rows = $stmt->fetchAll();

    $seasons = [];
    foreach ($rows as $row) {
        $seasons[] = new Season(
            (int)$row['id'],
            $row['name'],
            (int)$row['avg_temperature'],
            $row['icon_url']
        );
    }
    return $seasons;
}

/*Ajoute un vêtement en base et retourne son id.*/
function addItem(
    string $imgUrl,
    string $category,
    int $seasonId,
    float $hue,
    float $saturation,
    float $brightness,
    int $userRate,
    int $userId
): int {
    $pdo = Database::getPdo();
    $stmt = $pdo->prepare(
        'INSERT INTO clothes (img_url, category, season_id, hue, saturation, brightness, user_rate, user_id)
         VALUES (:img_url, :category, :season_id, :hue, :saturation, :brightness, :user_rate, :user_id)'
    );
    $stmt->execute([
        ':img_url'    => $imgUrl,
        ':category'   => $category,
        ':season_id'  => $seasonId,
        ':hue'        => $hue,
        ':saturation' => $saturation,
        ':brightness' => $brightness,
        ':user_rate'  => $userRate,
        ':user_id'    => $userId,
    ]);
    return (int) $pdo->lastInsertId();
}
