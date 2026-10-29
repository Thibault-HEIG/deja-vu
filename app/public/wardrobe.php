<?php
require_once __DIR__ . '/../src/renderer.php';
require_once __DIR__ . '/../src/itemRepository.php';
require_once __DIR__ . '/../src/models/Item.php';

$currentUserId = 1; // ID de l'utilisateur actuel

$clothesCount = getItemsCountByUserId($currentUserId);

$category = $_GET['category'] ?? '';
?>

<!DOCTYPE html>
<html lang="fr">

<?php render('head', ['title' => "On porte quoi aujourd'hui ?", 'description' => "Déjà-vu - La bonne tenue au bon moment"]); ?>

<body>
    <header id="wardrobe-header">
        <a href="index.php">
            <span>← Retour</span>
        </a>
    </header>
    <section id="wardrobe">
        <div>
            <h2>Ma garde-robe</h2>
            <span><?= $clothesCount ?> pièces prêtes à être portées</span>
        </div>
        <div id="wardrobe-container">
            <nav>
                <a href="wardrobe.php#wardrobe" <?= $category === '' ? 'aria-current="true"' : '' ?>>Tout</a>
                <a href="?category=top#wardrobe" <?= $category === 'top' ? 'aria-current="true"' : '' ?>>Hauts</a>
                <a href="?category=bottom#wardrobe" <?= $category === 'bottom' ? 'aria-current="true"' : '' ?>>Bas</a>
                <a href="?category=jacket#wardrobe" <?= $category === 'jacket' ? 'aria-current="true"' : '' ?>>Vestes</a>
                <a href="?category=accessory#wardrobe" <?= $category === 'accessory' ? 'aria-current="true"' : '' ?>>Accessoires</a>
                <a href="?category=shoes#wardrobe" <?= $category === 'shoes' ? 'aria-current="true"' : '' ?>>Chaussures</a>
            </nav>
            <div id="wardrobe-items">
                <div class="wardrobe-item-container">
                    <a href="addItem.php" id="add-item" class="wardrobe-item">
                        <img src="img/icons/add.svg" alt="Ajouter un vêtement">
                    </a>
                </div>
                <?php
                $items = getItemsByCategoryAndUserId($category, $currentUserId);
                foreach ($items as $item) {
                    echo '<div class="wardrobe-item-container">';
                    echo '<img src="' . htmlspecialchars($item->getImgUrl()) . '" class="wardrobe-item" data-category="' . htmlspecialchars($item->getCategory()) . '">';
                    $rate = (int) $item->getRate();
                    echo '<span class="stars" style="--rate:' . $rate . '" role="img"></span>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </section>
</body>

<?php render('footer'); ?>

</html>
