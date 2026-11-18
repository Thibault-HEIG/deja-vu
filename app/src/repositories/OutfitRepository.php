<?php
require_once __DIR__ . '/Repository.php';
require_once __DIR__ . '/ItemRepository.php';
require_once __DIR__ . '/../models/Outfit.php';

class OutfitRepository extends Repository {
    private ItemRepository $itemRepo;

    public function __construct(?ItemRepository $itemRepo = null) {
        parent::__construct();
        $this->itemRepo = $itemRepo ?? new ItemRepository();
    }

    public function insert(Outfit $outfit): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO results (id_top, id_bottom, id_shoes, id_jacket, id_accessory) ' .
            'VALUES (:id_top, :id_bottom, :id_shoes, :id_jacket, :id_accessory)'
        );
        $stmt->execute([
            ':id_top'       => $outfit->getTop()->getId(),
            ':id_bottom'    => $outfit->getBottom()->getId(),
            ':id_shoes'     => $outfit->getShoes()->getId(),
            ':id_jacket'    => $outfit->getJacket()?->getId(),
            ':id_accessory' => $outfit->getAccessory()?->getId(),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    #[Override]
    public function getById(int $id): ?Outfit {
        $stmt = $this->pdo->prepare('SELECT * FROM results WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->createOutfitFromItemIds($row);
    }

    public function getLatestOutfitByUserId(int $userId): ?Outfit {
        $stmt = $this->pdo->prepare('
            SELECT results.*
            FROM results
            JOIN clothes ON results.id_top = clothes.id
            WHERE clothes.user_id = :user_id
            ORDER BY results.timestamp DESC, results.id DESC
            LIMIT 1
        ');
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->createOutfitFromItemIds($row);
    }

    /**
     * Creates an Outfit entity from a results table row.
     */
    private function createOutfitFromItemIds(array $row): ?Outfit {
        $top = $this->itemRepo->getById((int)$row['id_top']);
        $bottom = $this->itemRepo->getById((int)$row['id_bottom']);
        $shoes = $this->itemRepo->getById((int)$row['id_shoes']);

        if ($top === null || $bottom === null || $shoes === null) { // constraints
            return null;
        }

        $jacket = $row['id_jacket'] !== null ? $this->itemRepo->getById((int)$row['id_jacket']) : null;
        $accessory = $row['id_accessory'] !== null ? $this->itemRepo->getById((int)$row['id_accessory']) : null;

        return new Outfit($top, $bottom, $shoes, $accessory, $jacket);
    }
}
