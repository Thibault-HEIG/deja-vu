<?php
require_once __DIR__ . '/../src/renderer.php';
require_once __DIR__ . '/../src/itemRepository.php';
require_once __DIR__ . '/../src/seasonRepository.php';
require_once __DIR__ . '/../src/constants.php';

$currentUserId = 1; // ID de l'utilisateur actuel (à remplacer par la session plus tard)


$seasons = getAllSeasons();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // TODO valider les couleurs côté serveur
    // Plus généralement, sortir les méthodes de validation dans src/itemRepository.php ou src/dbSecurity.php
    $hue        = (int) $_POST['hue'];
    $saturation = $_POST['saturation'] / 100;
    $brightness = $_POST['brightness'] / 100;

    // Photo : on vérifie le vrai type du fichier (MIME)
    $mime = mime_content_type($_FILES['photo']['tmp_name']);

    if (!array_key_exists($mime, ALLOWED_IMAGE_TYPES)) {
        $error = 'Format de photo non accepté (JPG, PNG ou WEBP).';
    } else {
        $extension = ALLOWED_IMAGE_TYPES[$mime];
        $dossier = 'img/uploads/user_' . $currentUserId;

        if (!is_dir(__DIR__ . '/' . $dossier)) {
            mkdir(__DIR__ . '/' . $dossier, 0775, true);
        }

        $imgUrl = $dossier . '/' . uniqid('item_') . '.' . $extension;
        move_uploaded_file($_FILES['photo']['tmp_name'], __DIR__ . '/' . $imgUrl);

        addItem($imgUrl, $_POST['category'], (int) $_POST['season_id'], $hue, $saturation, $brightness, (int) $_POST['rate'], $currentUserId);

        header('Location: index.php#wardrobe');
        exit;
    }
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
        </p>

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
</body>
</html>
