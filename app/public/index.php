<?php
require_once __DIR__ . '/../src/renderer.php';
require_once __DIR__ . '/../src/userRepository.php';
require_once __DIR__ . '/../src/itemRepository.php';
require_once __DIR__ . '/../src/models/Item.php';
require_once __DIR__ . '/../src/constants.php';

$currentUserId = 1; // ID de l'utilisateur actuel
$user = getUserById($currentUserId);
$name = $user->getName();

$clothesCount = getItemsCountByUserId($currentUserId);

$category = $_GET['category'] ?? '';
?>

<!DOCTYPE html>
<html lang="fr">

<?php render('head', ['title' => "On porte quoi aujourd'hui ?", 'description' => "Clothes Match - La bonne tenue au bon moment"]); ?>

<body>
    <header>
        <img src="img/icons/logo.svg" id="logo">
        <img src="img/icons/settings.svg" id="settings">
    </header>
    <hero>
        <span class="date"><?= DAYS_FR[date('w')] . ' ' . date('j') . ' ' . MONTHS_FR[date('n')] ?></span>
        <h1>Bonjour <?= $name ?></h1>
        <h2>On porte quoi aujourd'hui ?</h2>
        <p>Une tenue pensée à partir de ce que vous possédez déjà</p>
    </hero>
    <section id="outfit">
        <div id="outfit-container">
            <div class="items-top">
                <img src="" id="top" class="outfit-item">
                <img src="" id="jacket" class="outfit-item">
            </div>
            <div class="items-bottom">
                <img src="" id="bottom" class="outfit-item">
                <img src="" id="accessory" class="outfit-item">
            </div>
            <div class="items-shoes">
                <img src="" id="shoes" class="outfit-item">
            </div>
        </div>
        <div class="buttons">
            <a href="generateOutfit.php"><button>Changer les paramètres</button></a>
            <a href="index.php"><button>Créer une tenue</button></a>
        </div>
    </section>
    <section id="wardrobe">
        <div>
            <h2>Ma garde-robe</h2>
            <span><?= $clothesCount ?>pièces prêtes à être portées</span>
        </div>
        <div id="wardrobe-container">
            <nav>
                <a href="index.php#wardrobe" <?= $category === '' ? 'aria-current="true"' : '' ?>>Tout</a>
                <a href="?category=top#wardrobe" <?= $category === 'top' ? 'aria-current="true"' : '' ?>>Hauts</a>
                <a href="?category=bottom#wardrobe" <?= $category === 'bottom' ? 'aria-current="true"' : '' ?>>Bas</a>
                <a href="?category=jacket#wardrobe" <?= $category === 'jacket' ? 'aria-current="true"' : '' ?>>Vestes</a>
                <a href="?category=accessory#wardrobe" <?= $category === 'accessory' ? 'aria-current="true"' : '' ?>>Accessoires</a>
                <a href="?category=shoes#wardrobe" <?= $category === 'shoes' ? 'aria-current="true"' : '' ?>>Chaussures</a>
            </nav>
            <div id="wardrobe-items">
                <a href="addItem.php" id="add-item" class="wardrobe-item">
                    <img src="img/icons/add.svg" alt="Ajouter un vêtement">
                </a>
                <?php
                $items = getItemsByCategoryAndUserId($category, $currentUserId);
                foreach ($items as $item) {
                    echo '<img src="' . htmlspecialchars($item->getImgUrl()) . '" class="wardrobe-item" data-category="' . htmlspecialchars($item->getCategory()) . '" data-rate="' . htmlspecialchars($item->getRate()) . '">';
                }
                ?>
            </div>
        </div>
    </section>
</body>

<?php render('footer'); ?>

</html>
