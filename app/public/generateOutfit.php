<?php
require_once __DIR__ . '/../src/renderer.php';
require_once __DIR__ . '/../src/repositories/ItemRepository.php';
require_once __DIR__ . '/../src/repositories/SeasonRepository.php';
require_once __DIR__ . '/../src/repositories/OutfitRepository.php';
require_once __DIR__ . '/../src/repositories/UserRepository.php';
require_once __DIR__ . '/../src/models/User.php';
require_once __DIR__ . '/../src/databaseSecurity.php';
require_once __DIR__ . '/../src/algorithm/OutfitGenerator.php';
require_once __DIR__ . '/../src/ErrorHandler.php';

$seasonRepo = new SeasonRepository();
$itemRepo = new ItemRepository();
$outfitRepo = new OutfitRepository();
$userRepo = new UserRepository();

$seasons = $seasonRepo->getAll();

$currentUserId = 4; // ID de l'utilisateur actuel
$user = $userRepo->getById($currentUserId);

$latestOutfit = $outfitRepo->getLatestOutfitByUserId($user->getId());
$errors   = [];

$allItems = $itemRepo->getAllByUserIdAndCategory($user->getId());

// Default values
$seedItemId = isset($_POST['seed_item_id']) && $_POST['seed_item_id'] !== '' ? (int) $_POST['seed_item_id'] : null;
$seasonHarmony = isset($_POST['harmony']) ? (float) $_POST['harmony'] : ($latestOutfit?->getSeasonHarmony() ?? 0.5);
$colorHarmony = (float) 1.0 - $seasonHarmony;
$seasonId = isset($_POST['season_id']) ? (int) $_POST['season_id'] : ($latestOutfit?->getSeasonWishId() ?? null);

function getErrors(?int $seedItemId, ?int $seasonId, float $colorHarmony, float $seasonHarmony): array {
    $itemRepo = new ItemRepository();
    $seasonRepo = new SeasonRepository();
    $errors = [];
    if (isset($seedItemId) && $itemRepo->getById($seedItemId) === null) {
        $errors[] = "Le vêtement n'a pas été trouvé dans votre garde-robe.";
    }
    if (isset($seasonId) && $seasonRepo->getById($seasonId) === null) {
        $errors[] = "La saison n'est pas valide.";
    }
    if ($colorHarmony > 1 || $colorHarmony < 0) {
        $errors[] = "L'harmonie de couleur n'est pas valide.";
    }
    if ($seasonHarmony > 1 || $seasonHarmony < 0) {
        $errors[] = "L'harmonie de saison n'est pas valide.";
    }
    return $errors;
}

// Gestion de la soumission du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $seedItem = isset($seedItemId) ? $itemRepo->getById($seedItemId) : null;
    $seasonWish = isset($seasonId) ? $seasonRepo->getById($seasonId)->getAvgTemperature() : null;

    $errors = getErrors($seedItemId, $seasonId, $colorHarmony, $seasonHarmony);

    if (empty($errors)) {
        $outfitGenerator = new OutfitGenerator(
            $user->getId(),
            $colorHarmony,
            $seasonHarmony,
            $seedItem,
            $seasonId
        );
        try {
            $outfit = $outfitGenerator->generateOutfit();
            $outfitRepo->insert($outfit);
            header('Location: index.php');
            exit;
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }
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
            <?php ErrorHandler::displayErrors($errors) ?>
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
                    <input type="radio" name="season_id" value="<?= $season->getId() ?>" required <?= $seasonId === $season->getId() ? "checked" : "" ?>>
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
