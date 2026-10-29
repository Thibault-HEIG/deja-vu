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

    $imgUrl = resolveImagePath($_POST['tmp_file'] ?? '', $_FILES['photo'] ?? [], $currentUserId, $error);

    addItem($imgUrl, $_POST['category'], (int) $_POST['season_id'], $hue, $saturation, $brightness, (int) $_POST['rate'], $currentUserId);
    header('Location: index.php#wardrobe');
    exit;
}

// ---------------------------------------------------------------------------
// Resolves the final image path by choosing between the API-processed temp
// file and the raw upload, validating each case, and moving the file into
// the uploads folder. Returns the relative path on success, null on failure
// (and sets $error by reference).
// ---------------------------------------------------------------------------
function resolveImagePath(string $tmpFile, array $upload, int $userId, ?string &$error): ?string {
    $uploadDir = 'img/uploads/user_' . $userId;
    $destPath  = fn(string $ext) => $uploadDir . '/' . uniqid('item_') . '.' . $ext;

    // Branch A — API already processed the image: validate and move the temp PNG.
    if (isValidTmpFile($tmpFile)) {
        $imgUrl = $destPath('png');
        rename(__DIR__ . '/' . $tmpFile, __DIR__ . '/' . $imgUrl);
        return $imgUrl;
    }

    // Branch B — Raw upload: check the PHP upload succeeded, then verify MIME type.
    if (empty($upload['tmp_name']) || $upload['error'] !== UPLOAD_ERR_OK) {
        $error = 'Erreur lors du téléchargement de la photo (fichier manquant ou trop volumineux).';
        return null;
    }

    $mime = mime_content_type($upload['tmp_name']);
    if (!array_key_exists($mime, ALLOWED_IMAGE_TYPES)) {
        $error = 'Format de photo non accepté (JPG, PNG ou WEBP).';
        return null;
    }

    $imgUrl = $destPath(ALLOWED_IMAGE_TYPES[$mime]);
    move_uploaded_file($upload['tmp_name'], __DIR__ . '/' . $imgUrl);
    return $imgUrl;
}

// Returns true only if $path is a safe, existing temp PNG produced by the API.
function isValidTmpFile(string $path): bool {
    return $path !== ''
        && str_starts_with($path, 'img/tmp/')
        && pathinfo($path, PATHINFO_EXTENSION) === 'png'
        && file_exists(__DIR__ . '/' . $path);
}

?>

<!DOCTYPE html>
<html lang="fr">

<?php render('head', ['title' => 'Ajouter un vêtement', 'description' => 'Deja-Vu - Ajouter un vêtement']); ?>

<body>
    <link rel="stylesheet" href="css/colorPicker.css">
    <h1>Ajouter un vêtement</h1>

    <?php if ($error): ?>
        <p><?= $error ?></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">

        <p>
            <label for="photo">Photo</label><br>
            <input type="file" id="photo" name="photo" accept="image/*" required>
            <!-- Filled by addItem.js once the API returns the processed PNG path -->
            <input type="hidden" id="tmp_file" name="tmp_file">
        </p>

        <!-- Preview + status shown after file selection -->
        <p id="photo-status" data-state="idle"></p>
        <img id="photo-preview" src="" alt="Aperçu" hidden>

        <p>
            <label for="category">Type</label><br>
            <select id="category" name="category" required>
                <option value="">-- Choisir --</option>
                <?php foreach (CATEGORIES as $value => $label): ?>
                    <option value="<?= $value ?>"><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            Saison<br>
            <?php foreach ($seasons as $season): ?>
                <label>
                    <input type="radio" name="season_id" value="<?= $season->getId() ?>" required>
                    <?= htmlspecialchars($season->getName()) ?>
                </label>
            <?php endforeach; ?>
        </p>

        <div>
            Couleur<br>
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

        <p>
            Note<br>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <label>
                    <input type="radio" name="rate" value="<?= $i ?>" required> <?= $i ?>★
                </label>
            <?php endfor; ?>
        </p>

        <button type="submit">Ajouter</button>
    </form>
    <?php render('footer'); ?>
    <script src="js/colorPicker.js"></script>
    <script src="js/addItem.js"></script>
</body>

</html>
