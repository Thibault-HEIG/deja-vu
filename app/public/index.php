<?php
require_once __DIR__ . '/../src/renderer.php';
require_once __DIR__ . '/../src/userRepository.php';
require_once __DIR__ . '/../src/constants.php';

$currentUserId = 1; // ID de l'utilisateur actuel
$user = getUserById($currentUserId);
$name = $user->getName();
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
    <hero>
        <span class="date"><?= DAYS_FR[date('w')] . ' ' . date('j') . ' ' . MONTHS_FR[date('n')] ?></span>
        <h1>Bonjour <?= htmlspecialchars($name) ?></h1>
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
            <a href="index.php"><button id="reroll">Créer une tenue</button></a>
            <a href="generateOutfit.php"><button id="change-param">Changer les paramètres</button></a>
        </div>
    </section>
</body>

</html>
