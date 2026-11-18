<?php
require_once __DIR__ . '/../src/renderer.php';
require_once __DIR__ . '/../src/repositories/ItemRepository.php';
require_once __DIR__ . '/../src/repositories/SeasonRepository.php';
require_once __DIR__ . '/../src/repositories/UserRepository.php';
require_once __DIR__ . '/../src/constants.php';
require_once __DIR__ . '/../src/databaseSecurity.php';
require_once __DIR__ . '/../src/ErrorHandler.php';

$currentUserId = 1; // ID de l'utilisateur actuel (à remplacer par la session plus tard)

$seasonRepo = new SeasonRepository();
$itemRepo  = new ItemRepository();
$userRepo  = new UserRepository();

$seasons = $seasonRepo->getAll();
$errors   = [];

$category = $_POST['category'] ?? '';

$season_id = isset($_POST['season_id']) ? (int) $_POST['season_id'] : 1; // Spring (default)

$hue        = isset($_POST['hue']) ? (int) $_POST['hue'] : 0;
$saturation = isset($_POST['saturation']) ? (int) $_POST['saturation'] : 0;
$brightness = isset($_POST['brightness']) ? (int) $_POST['brightness'] : 0;

$rate = isset($_POST['rate']) ? (int) $_POST['rate'] : 3; // default value

$rawImg = $_FILES['photo'] ?? [];
$tmpFile = $_POST['tmp_file'] ?? '';

// ---------------------------------------------------------------------------
// POST handler — resolve image path, validate, persist
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $color = new Color($hue, $saturation, $brightness);

    $errors = getInputErrors($rawImg, $tmpFile, $category, $season_id, $color, $rate);

    if (empty($errors)) {
        $itemToBeAdded = new Item($category, new Season($season_id), $color, $rate, '');
        $itemId = $itemRepo->insert($itemToBeAdded, $currentUserId);
        $userToken = $userRepo->getSecretToken($currentUserId);
        if ($itemRepo->moveToActualImgPath(!empty($tmpFile) ? $tmpFile : $rawImg, $userToken, $itemId)) {
            header('Location: wardrobe.php');
            exit;
        }
        $errors[] = "Erreur lors du déplacement de l'image vers le dossier final.";
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<?php render('head', ['title' => 'Ajouter un vêtement', 'description' => 'Deja-Vu - Ajouter un vêtement']); ?>

<body>
    <link rel="stylesheet" href="css/colorPicker.css">
    <header id="addItem-header">
        <a href="wardrobe.php">
            <span>← Annuler</span>
        </a>
    </header>
    <section id="hero">
        <h1>Ajouter un vêtement</h1>

        <!-- Affichage dynamique des erreurs -->
        <?php if ($_SERVER["REQUEST_METHOD"] === "POST") { ?>
            <?php ErrorHandler::displayErrors($errors) ?>
        <?php } ?>
    </section>

    <form method="post" enctype="multipart/form-data">

        <div class="photo-upload">
            <label for="photo">Photo</label>
            <input type="file" id="photo" name="photo" accept="image/*" <?= !empty($tmpFile) ? '' : 'required' ?>>
            <!-- Filled by addItem.js once the API returns the processed PNG path -->
            <input type="hidden" id="tmp_file" name="tmp_file" value="<?= htmlspecialchars($tmpFile) ?>">

            <!-- Preview + status shown after file selection -->
            <p id="photo-status" data-state="<?= !empty($tmpFile) ? 'done' : 'idle' ?>"><?= !empty($tmpFile) ? 'Fond supprimé ✓' : '' ?></p>
            <img id="photo-preview" src="<?= htmlspecialchars($tmpFile) ?>" alt="Aperçu" <?= !empty($tmpFile) ? '' : 'hidden' ?>>
        </div>

        <div class="category-select">
            <label for="category">Type</label>
            <select id="category" name="category" required>
                <option value="">-- Choisir --</option>
                <?php foreach (CATEGORIES as $value => $label): ?>
                    <option value="<?= htmlspecialchars($value) ?>" <?= $category === $value ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="season-select">
            <legend>Saison</legend>
            <?php foreach ($seasons as $season): ?>
                <input type="radio" name="season_id" value="<?= $season->getId() ?>" required <?= $season_id === $season->getId() ? "checked" : "" ?>>
                <label for="season_id"><?= htmlspecialchars($season->getName()) ?></label>
            <?php endforeach; ?>
        </div>

        <div class="color-select">
            <label>Couleur</label>
            <div class="hsv-picker">
                <div class="zone" id="zone">
                    <canvas id="carre" width="260" height="200"></canvas>
                    <div class="rond" id="rond"></div>
                </div>

                <input type="range" id="h" name="hue" value="<?= htmlspecialchars($hue) ?>">
                <input type="hidden" id="s" name="saturation" value="<?= htmlspecialchars($saturation) ?>">
                <input type="hidden" id="v" name="brightness" value="<?= htmlspecialchars($brightness) ?>">

                <div class=" apercu" id="apercu">
                </div>
            </div>
        </div>

        <div class="rate-select">
            <label>Note</label>
            <p class="tip">Plus la note est haute, plus le vêtement te sera proposé souvent</p>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <input type="radio" name="rate" value="<?= $i ?>" required <?= $rate === $i ? "checked" : "" ?>><span><?= $i ?>★</span>
            <?php endfor; ?>
        </div>

        <button type="submit">Ajouter</button>
    </form>
    <?php render('footer'); ?>
    <script src="js/colorPicker.js"></script>
    <script src="js/addItem.js"></script>
</body>

</html>
