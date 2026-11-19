<?php
require_once __DIR__ . '/../src/renderer.php';
require_once __DIR__ . '/../src/repositories/UserRepository.php';
require_once __DIR__ . '/../src/repositories/OutfitRepository.php';
require_once __DIR__ . '/../src/constants.php';

$userRepo = new UserRepository();
$outfitRepo = new OutfitRepository();

$currentUserId = 1; // ID de l'utilisateur actuel
$user = $userRepo->getById($currentUserId);
$name = $user->getName();

$latestOutfit = $outfitRepo->getLatestOutfitByUserId($user->getId());
$top = $latestOutfit->getTop();
$bottom = $latestOutfit->getBottom();
$shoes = $latestOutfit->getShoes();
$jacket = $latestOutfit->getJacket();
$accessory = $latestOutfit->getAccessory();
?>

<!DOCTYPE html>
<html lang="fr">

<?php render('head', ['title' => "On porte quoi aujourd'hui ?", 'description' => "Déjà-vu - La bonne tenue au bon moment"]); ?>

<body>
    <header>
        <img src="img/logo_light.svg" id="logo">
        <a href="wardrobe.php">
            <img src="img/icons/hanger.svg" id="wardrobe-icon">
            <span>Garde-robe</span>
        </a>
    </header>
    <main>
        <section id="hero">
            <span class="date"><?= DAYS_FR[date('w')] . ' ' . date('j') . ' ' . MONTHS_FR[date('n')] ?></span>
            <h1>Bonjour <?= htmlspecialchars($name) ?></h1>
            <h2>On porte quoi aujourd'hui?</h2>
            <p>Une tenue pensée à partir de ce que vous possédez déjà</p>
        </section>
        <section id="outfit">
            <div id="outfit-container">
                <div class="items-top">
                    <img src="<?= htmlspecialchars($top->getImgUrl()) ?>" id="top" class="outfit-item">
                    <?php if (isset($jacket)) { ?>
                        <img src="<?= htmlspecialchars($jacket->getImgUrl()) ?>" id="jacket" class="outfit-item">
                    <?php } ?>
                </div>
                <div class="items-bottom">
                    <img src="<?= htmlspecialchars($bottom->getImgUrl()) ?>" id="bottom" class="outfit-item">
                    <?php if (isset($accessory)) { ?>
                        <img src="<?= htmlspecialchars($accessory->getImgUrl()) ?>" id="accessory" class="outfit-item">
                    <?php } ?>
                </div>
                <div class="items-shoes">
                    <img src="<?= htmlspecialchars($shoes->getImgUrl()) ?>" id="shoes" class="outfit-item">
                </div>
            </div>
            <div class="buttons">
                <a href="index.php"><button id="reroll">Créer une tenue</button></a>
                <a href="generateOutfit.php"><button id="change-param">Changer les paramètres</button></a>
            </div>
        </section>
    </main>
    <?php render('footer'); ?>
</body>

</html>
