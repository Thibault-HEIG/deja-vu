<?php
require_once __DIR__ . '/Repository.php';
require_once __DIR__ . '/IDelete.php';
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../constants.php';
require_once __DIR__ . '/../models/Item.php';
require_once __DIR__ . '/../models/Season.php';
require_once __DIR__ . '/../models/Color.php';

/**
 * Item repository : functions to manage items in the database.
 */
class ItemRepository extends Repository implements IDelete {

    #[Override]
    public function getById(int $id): ?Item {
        $stmt = $this->pdo->prepare("SELECT clothes.*, seasons.name AS season_name, seasons.avg_temperature, seasons.icon_url
    FROM clothes
    JOIN seasons ON clothes.season_id = seasons.id
    WHERE clothes.id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }
        return new Item(
            $row['category'],
            new Season((int)$row['season_id'], $row['season_name'], (int)$row['avg_temperature'], $row['icon_url']),
            new Color($row['hue'], $row['saturation'], $row['brightness']),
            (int)$row['user_rate'],
            $row['img_url'],
            (int)$row['id'],
        );
    }

    public function getItemsCountByUserId(int $userId): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM clothes WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();

        return (int)$row['count'];
    }

    public function getAllByUserIdAndCategory(int $userId, string $category = ''): array {

        if ($category !== '') {
            $stmt = $this->pdo->prepare("SELECT clothes.*, seasons.name AS season_name, seasons.avg_temperature, seasons.icon_url
    FROM clothes
    JOIN seasons ON clothes.season_id = seasons.id
    WHERE clothes.category = :category AND clothes.user_id = :user_id");
            $stmt->execute(['category' => $category, 'user_id' => $userId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT clothes.*, seasons.name AS season_name, seasons.avg_temperature, seasons.icon_url
    FROM clothes
    JOIN seasons ON clothes.season_id = seasons.id
    WHERE clothes.user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
        }
        $rows = $stmt->fetchAll();

        $items = [];
        foreach ($rows as $row) {
            $items[] = new Item(
                $row['category'],
                new Season((int)$row['season_id'], $row['season_name'], (int)$row['avg_temperature'], $row['icon_url']),
                new Color($row['hue'], $row['saturation'], $row['brightness']),
                (int)$row['user_rate'],
                $row['img_url'],
                (int)$row['id'],
            );
        }
        return $items;
    }

    /*Ajoute un vêtement en base et retourne son id.*/
    public function insert(Item $item, int $userId): int {

        $stmt = $this->pdo->prepare(
            'INSERT INTO clothes (img_url, category, season_id, hue, saturation, brightness, user_rate, user_id)
         VALUES (:img_url, :category, :season_id, :hue, :saturation, :brightness, :user_rate, :user_id)'
        );
        $stmt->execute([
            ':img_url'    => $item->getImgUrl(),
            ':category'   => $item->getCategory(),
            ':season_id'  => $item->getSeason()->getId(),
            ':hue'        => $item->getColor()->getHue(),
            ':saturation' => $item->getColor()->getSaturation(),
            ':brightness' => $item->getColor()->getBrightness(),
            ':user_rate'  => $item->getRate(),
            ':user_id'    => $userId,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /*Modifie un vêtement en base avec de nouvelles données.*/
    public function update(Item $item): bool {
        return false; // Not implemented yet
    }

    /*Supprime un vêtement en base avec de nouvelles données.*/
    #[Override]
    public function deleteById(int $itemId): bool {
        return false; // Not implemented yet
    }

    /**
     * Update the temporary item image path into the user's upload directory.
     * Update the database with the new path.
     */
    public function moveToActualImgPath(string|array $tmpFile, string $userToken, int $itemId): bool {
        $uploadDir = __DIR__ . '/../../public/img/uploads/' . $userToken;

        if (!is_dir($uploadDir)) { // if user folder doesn't exist, we create it
            mkdir($uploadDir, 0775, true);
        }

        $isInRepo = false; // If the image already stands in the repo (img/)
        $sourcePath = '';
        $extension = '';

        // Case 1 : We got a response from the API
        if (is_string($tmpFile)) {
            // basename() strips any path traversal without needing complex prefix checks
            $tmpFilename = basename($tmpFile);
            $candidatePath = __DIR__ . '/../../public/img/tmp/' . $tmpFilename;
            if (file_exists($candidatePath) && $tmpFilename !== '') {
                $sourcePath = $candidatePath;
                $extension = 'png';
                $isInRepo = true;
            }
        }
        // Case 2 : We fallback on the raw file
        else if (empty($sourcePath) && is_array($tmpFile) && is_uploaded_file($tmpFile['tmp_name'])) {
            $mime = mime_content_type($tmpFile['tmp_name']);
            $sourcePath = $tmpFile['tmp_name'];
            $extension = ALLOWED_IMAGE_TYPES[$mime];
        }
        // Case 3 : Unknown Error
        else {
            return false;
        }

        // Relative path for database (ex: img/uploads/user_1/item_42.png)
        $dbPath = 'img/uploads/' . $userToken . '/item_' . $itemId . '.' . $extension;
        $destFullPath = $uploadDir . '/item_' . $itemId . '.' . $extension;

        $moved = $isInRepo ? rename($sourcePath, $destFullPath) : move_uploaded_file($sourcePath, $destFullPath);

        if (!$moved) {
            return false;
        }

        $stmt = $this->pdo->prepare('UPDATE clothes SET img_url = :img_url WHERE id = :id');
        return $stmt->execute([
            ':img_url' => $dbPath,
            ':id'      => $itemId,
        ]);
    }
}
