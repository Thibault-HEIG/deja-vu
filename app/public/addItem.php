<?php
require_once __DIR__ . '/../src/renderer.php';
require_once __DIR__ . '/../src/itemRepository.php';
require_once __DIR__ . '/../src/seasonRepository.php';
require_once __DIR__ . '/../src/constants.php';

$currentUserId = 1; // ID de l'utilisateur actuel (à remplacer par la session plus tard)

$seasons = getAllSeasons();
$error   = null;

// ---------------------------------------------------------------------------
// POST handler — resolve image path, validate, persist
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // TODO valider les couleurs côté serveur
    $hue        = (int) $_POST['hue'];
    $saturation = $_POST['saturation'] / 100;
    $brightness = $_POST['brightness'] / 100;

    $imgUrl = saveItemImage($_POST['tmp_file'] ?? '', $_FILES['photo'] ?? [], $currentUserId);

    if ($imgUrl !== null) {
        addItem($imgUrl, $_POST['category'], (int) $_POST['season_id'], $hue, $saturation, $brightness, (int) $_POST['rate'], $currentUserId);
        header('Location: index.php#wardrobe');
        exit;
    }

    $error = 'Format de photo non accepté ou erreur lors du téléchargement.';
}

/**
 * Saves the item image into the user's upload directory.
 * Uses the API-processed transparent PNG if available, otherwise falls back to the raw upload.
 */
function saveItemImage(string $tmpFile, array $upload, int $userId): ?string {
    $uploadDir = 'img/uploads/user_' . $userId;
    if (!is_dir(__DIR__ . '/' . $uploadDir)) {
        mkdir(__DIR__ . '/' . $uploadDir, 0775, true);
    }

    // 1. Image processed by ClearBackdrop API
    // basename() strips any path traversal without needing complex prefix checks
    $tmpFilename = basename($tmpFile);
    $tmpPath     = __DIR__ . '/img/tmp/' . $tmpFilename;

    if ($tmpFilename !== '' && file_exists($tmpPath)) {
        $destUrl = $uploadDir . '/' . uniqid('item_') . '.png';
        rename($tmpPath, __DIR__ . '/' . $destUrl);
        return $destUrl;
    }

    // 2. Fallback: raw image uploaded through form
    if (empty($upload['tmp_name']) || $upload['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $mime = mime_content_type($upload['tmp_name']);
    if (!array_key_exists($mime, ALLOWED_IMAGE_TYPES)) {
        return null;
    }

    $destUrl = $uploadDir . '/' . uniqid('item_') . '.' . ALLOWED_IMAGE_TYPES[$mime];
    move_uploaded_file($upload['tmp_name'], __DIR__ . '/' . $destUrl);
    return $destUrl;
}

?>

<!DOCTYPE html>
<html lang="fr">

<?php render('head', ['title' => 'Ajouter un vêtement', 'description' => 'Deja-Vu - Ajouter un vêtement']); ?>

<body>
    <link rel="stylesheet" href="css/colorPicker.css">
    <header id="addItem-header">
        <a href="index.php">
            <span>← Annuler</span>
        </a>
    </header>
    <hero>
        <h1>Ajouter un vêtement</h1>

        <?php if ($error): ?>
            <p><?= $error ?></p>
        <?php endif; ?>
    </hero>

    <form method="post" enctype="multipart/form-data">

        <div class="photo-upload">
            <label for="photo">Photo</label><br>
            <input type="file" id="photo" name="photo" accept="image/*" required>
            <!-- Filled by addItem.js once the API returns the processed PNG path -->
            <input type="hidden" id="tmp_file" name="tmp_file">

            <!-- Preview + status shown after file selection -->
            <p id="photo-status" data-state="idle"></p>
            <img id="photo-preview" src="" alt="Aperçu" hidden>
        </div>

        <div class="category-select">
            <label for="category">Type</label><br>
            <select id="category" name="category" required>
                <option value="">-- Choisir --</option>
                <?php foreach (CATEGORIES as $value => $label): ?>
                    <option value="<?= $value ?>"><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="season-select">
            <label>Saison</label>
            <?php foreach ($seasons as $season):
                $default = $season->getName() == "Printemps" // Définir le printemps comme valeur par défaut ?>
                <input type="radio" name="season_id" value="<?= $season->getId() ?>" required <?= $default ? 'checked' : '' ?>>
                <span><?= htmlspecialchars($season->getName()) ?></span>
            <?php endforeach; ?>
        </div>

        <div class="color-select">
            <label>Couleur</label>
            <div class="hsv-picker">
                <div class="zone" id="zone">
                    <canvas id="carre" width="260" height="200"></canvas>
                    <div class="rond" id="rond"></div>
                </div>

                <input type="range" id="h" name="hue">
                <input type="hidden" id="s" name="saturation">
                <input type="hidden" id="v" name="brightness">

                <div class="apercu" id="apercu"></div>
            </div>
        </div>

        <div class="rate-select">
            <label>Note</label>
            <p class="tip">Plus la note est haute, plus le vêtement te sera proposé souvent</p>
            <?php for ($i = 1; $i <= 5; $i++):
                $default = $i === 3; // Définir 3 comme valeur par défaut ?>
                <input type="radio" name="rate" value="<?= $i ?>" required <?= $default ? 'checked' : '' ?>><span><?= $i ?>★</span>
            <?php endfor; ?>
        </div>

        <button type="submit">Ajouter</button>
    </form>
    <?php render('footer'); ?>
    <script src="js/colorPicker.js"></script>
    <script src="js/addItem.js"></script>
</body>

</html>
