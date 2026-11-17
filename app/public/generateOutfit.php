<?php
require_once __DIR__ . '/../src/renderer.php';
require_once __DIR__ . '/../src/repositories/ItemRepository.php';
require_once __DIR__ . '/../src/repositories/SeasonRepository.php';
require_once __DIR__ . '/../src/models/User.php';

$seasonRepo = new SeasonRepository();
$itemRepo = new ItemRepository();

$seasons = $seasonRepo->getAll();

$user = new User("Utilisateur Test", 0.5, 0.5, 1);

$allItems = $itemRepo->getAllByUserIdAndCategory($user->getId());

// Default values
$seed_item_id = isset($_POST['seed_item_id']) ? (int) $_POST['seed_item_id'] : null;
$colorHarmony = isset($_POST['harmony']) ? (float) 1 - $_POST['harmony'] : $user->getLastSeasonHarmonyImportance();
$seasonHarmony = isset($_POST['harmony']) ? (float) $_POST['harmony'] : $user->getLastSeasonHarmonyImportance();
$season_id = isset($_POST['season_id']) ? (int) $_POST['season_id'] : null;

// Gestion de la soumission du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // $seed_item_id is ready to use here (null if none selected)
}
?>

<!DOCTYPE html>
<html lang="fr">

<?php render('head', ['title' => "Créer un outfit sur mesure", 'description' => "Clothes Match - La bonne tenue au bon moment"]); ?>

<body>
    <header id="generateOutfit-header">
        <a href="index.php">
            <span>← Retour</span>
        </a>
    </header>
    <section id="hero">
        <h1>Créer un outfit</h1>
        <!-- Affichage dynamique des erreurs -->
        <?php if ($_SERVER["REQUEST_METHOD"] === "POST") { ?>
            <?php if (!empty($errors)) { ?>
                <p>Le formulaire contient des erreurs :</p>
                <ul>
                    <?php foreach ($errors as $error) { ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php } ?>
                </ul>
            <?php } ?>
        <?php } ?>
        <form action="./generateOutfit.php" method="POST">
            <div class="seed-item">
                <span>Optionnel</span>
                <label>Vêtement de départ</label>
                <!-- Hidden input carrying the selected item ID -->
                <input type="hidden" id="seed-item-id" name="seed_item_id" value="">
                <!-- Button that opens the wardrobe popup -->
                <div class="seed-trigger">
                    <img src="" alt="Aperçu">
                    <span>Sélectionner</span>
                </div>
            </div>
            <div class="harmony-slider">
                <label for="harmony">Harmonie</label>
                <span>de couleur</span>
                <input type="range" id="harmony" name="harmony" min="0" max="1" step="0.01" value="<?= htmlspecialchars($seasonHarmony) ?>">
                <span>de saison</span>
            </div>
            <div class="season-select">
                <legend>Saison de préférence</legend>
                <?php foreach ($seasons as $season): ?>
                    <input type="radio" name="season_id" value="<?= $season->getId() ?>" required <?= $season_id === $season->getId() ? "checked" : "" ?>>
                    <label for="season_id"><?= htmlspecialchars($season->getName()) ?></label>
                <?php endforeach; ?>
            </div>
            <button type="submit">Créer un outfit</button>
        </form>
    </section>

    <!-- Wardrobe picker popup (hidden by default, toggled by JS) -->
    <div class="popup-overlay">
        <div class="popup-panel">
            <header>
                <h3>Ma garde-robe</h3>
                <button class="popup-close" type="button">✕</button>
            </header>
            <div class="popup-grid">
                <?php foreach ($allItems as $item): ?>
                    <img
                        src="<?= htmlspecialchars($item->getImgUrl()) ?>"
                        alt="<?= htmlspecialchars($item->getCategory()) ?>"
                        data-id="<?= $item->getId() ?>">
                <?php endforeach; ?>
            </div>
            <div class="popup-footer">
                <button class="popup-clear" type="button">Aucune sélection</button>
            </div>
        </div>
    </div>
    <?php render('footer'); ?>
    <script src="js/generateOutfit.js"></script>
</body>

</html>
