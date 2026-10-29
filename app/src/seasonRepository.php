<?php

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
