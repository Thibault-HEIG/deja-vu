<?php
require_once __DIR__ . '/Repository.php';
require_once __DIR__ . '/../models/Season.php';

class SeasonRepository extends Repository {

    public function getAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM seasons ORDER BY id;");
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

    #[Override]
    public function getById(int $seasonId): ?Season {
        $stmt = $this->pdo->prepare("SELECT * FROM seasons WHERE id = :id");
        $stmt->execute(['id' => $seasonId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return new Season((int)$row['id'], $row['name'], (int)$row['avg_temperature'], $row['icon_url']);
    }

    public function getMaxTemperatureDifference(): int {
        $stmt = $this->pdo->query("SELECT MAX(avg_temperature) AS max, MIN(avg_temperature) AS min FROM seasons;");
        $results = $stmt->fetch();
        return (int) $results['max'] - (int) $results['min'];
    }
}
